<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingAddonRequest;
use App\Models\AddonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\FirebaseNotificationService;

class BookingAddonController extends Controller
{
    // ────────────────────────────────────────────────────────────────────
    // REQUEST ADDON (User)
    // ────────────────────────────────────────────────────────────────────

    /**
     * @OA\Post(
     *     path="/booking/addon/request",
     *     summary="Request addon services for an ongoing booking",
     *     security={{ "bearerAuth": {} }},
     *     tags={"Booking Addon"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"booking_id","items"},
     *             @OA\Property(property="booking_id", type="integer", example=12),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"service_id","quantity"},
     *                     @OA\Property(property="service_id", type="integer", example=45),
     *                     @OA\Property(property="quantity", type="number", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Addon request submitted"),
     *     @OA\Response(response=404, description="Booking not found"),
     *     @OA\Response(response=422, description="Validation error / booking not eligible")
     * )
     */
    public function requestAddon(Request $request)
    {
        $request->validate([
            'booking_id'              => 'required|exists:bookings,id',
            'items'                   => 'required|array|min:1',
            'items.*.service_id'      => 'required|integer',
            'items.*.quantity'        => 'required|numeric|min:0.01',
            'items.*.service_item_id' => 'nullable|integer',
            'items.*.class_name'      => 'nullable|string',
            'items.*.type'            => 'nullable|string',
            'items.*.subject_type'    => 'nullable|integer',
            'items.*.min_people'      => 'nullable|integer|min:0',
            'items.*.max_people'      => 'nullable|integer|min:0',
        ]);

        $user = auth()->user();

        $booking = Booking::where('id', $request->booking_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        if (in_array($booking->status, ['completed', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => "Cannot request addons on a booking that is already {$booking->status}.",
            ], 422);
        }

        // Prevent multiple addon requests for the same booking
        // Pending and accepted requests cannot be requested again.
        // Rejected requests can be submitted again.
        $existingAddonRequest = BookingAddonRequest::where('booking_id', $booking->id)
            ->whereIn('status', [
                BookingAddonRequest::STATUS_PENDING,
                BookingAddonRequest::STATUS_ACCEPTED,
            ])
            ->latest()
            ->first();

        if ($existingAddonRequest) {
            $message = $existingAddonRequest->status === BookingAddonRequest::STATUS_PENDING
                ? 'You already have a pending addon request for this booking.'
                : 'Addon request has already been accepted for this booking.';

            return response()->json([
                'success' => false,
                'message' => $message,
                'data' => $existingAddonRequest,
            ], 422);
        }

        // Resolve real prices/names server-side — never trust client-sent price.
        $serviceIds = collect($request->items)->pluck('service_id')->unique();

        $addonServices = AddonService::whereIn('id', $serviceIds)
            ->get()
            ->keyBy('id');

        $resolvedItems = [];
        $totalAmount   = 0;

        foreach ($request->items as $entry) {
            $addonService = $addonServices->get($entry['service_id']);

            if (!$addonService) {
                continue; // unknown/inactive service id — skip silently
            }

            $quantity  = (float) $entry['quantity'];
            $price     = (float) $addonService->price;
            $itemTotal = round($price * $quantity, 2);

            // Same field set as BookingController::attachItems(), so
            // acceptance can write BookingItem rows identically.
            $resolvedItems[] = [
                'service_id'      => $addonService->id,
                'service_name'    => $addonService->name,
                'quantity'        => $quantity,
                'price'           => $price,
                'total_price'     => $itemTotal,

                // service_item_id is only meaningful for service_category_id == 1,
                // class_name only for == 3 — same rule attachItems() applies.
                // We store both raw here; respondAddon() applies the same
                // category gating at acceptance time, using the BOOKING's
                // category (source of truth), not whatever the client sent.
                'service_item_id' => $entry['service_item_id'] ?? null,
                'class_name'      => $entry['class_name'] ?? null,
                'type'            => $entry['type'] ?? null,
                'subject_type'    => $entry['subject_type'] ?? null,
                'min_people'      => $entry['min_people'] ?? null,
                'max_people'      => $entry['max_people'] ?? null,
            ];

            $totalAmount += $itemTotal;
        }

        if (empty($resolvedItems)) {
            return response()->json([
                'success' => false,
                'message' => 'None of the given services could be resolved.',
            ], 422);
        }

        $addonRequest = BookingAddonRequest::create([
            'booking_id'    => $booking->id,
            'requested_by'  => $user->id,
            'items'         => $resolvedItems,
            'total_amount'  => round($totalAmount, 2),
            'status'        => BookingAddonRequest::STATUS_PENDING,
        ]);

        try {
            app(FirebaseNotificationService::class)->sendPushNotificationSync(
                [$booking->provider_id],
                'New Addon Request',
                'The customer has requested addon services for booking No: ' . $booking->booking_number,
                false,
                'addon_requested',
                [
                    'type'              => 'addon_requested',
                    'entity'            => 'booking_addon_request',
                    'entity_id'         => $addonRequest->id,
                    'booking_id'        => $booking->id,
                    'booking_number'    => $booking->booking_number,
                    'addon_request_id'  => $addonRequest->id,
                ]
            );
        } catch (\Throwable $e) {
            Log::info('Addon request notification failed', [
                'booking_id' => $booking->id,
                'error'      => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Addon request submitted successfully',
            'data'    => $addonRequest,
        ], 201);
    }

    // ────────────────────────────────────────────────────────────────────
    // LIST ADDON REQUESTS
    // ────────────────────────────────────────────────────────────────────

    /**
     * @OA\Get(
     *     path="/booking/{booking}/addon/requests",
     *     summary="List addon requests for a booking",
     *     security={{ "bearerAuth": {} }},
     *     tags={"Booking Addon"},
     *     @OA\Parameter(
     *         name="booking",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Addon requests fetched successfully"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Booking not found")
     * )
     */
    public function listAddonRequests(int $bookingId)
    {
        $user = auth()->user();

        $booking = Booking::find($bookingId);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        if ($booking->user_id !== $user->id && $booking->provider_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $requests = BookingAddonRequest::where('booking_id', $bookingId)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Addon requests fetched successfully',
            'data'    => $requests,
        ]);
    }

    // ────────────────────────────────────────────────────────────────────
    // RESPOND TO ADDON (Provider)
    // ────────────────────────────────────────────────────────────────────

    /**
     * @OA\Post(
     *     path="/booking/addon/respond",
     *     summary="Provider accepts or rejects an addon request",
     *     description="On acceptance, addon items are added to the booking's items (service_type=1) and the booking totals are incremented.",
     *     security={{ "bearerAuth": {} }},
     *     tags={"Booking Addon"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"addon_request_id","action"},
     *             @OA\Property(property="addon_request_id", type="integer", example=3),
     *             @OA\Property(property="action", type="string", enum={"accepted","rejected"}, example="accepted"),
     *             @OA\Property(property="reject_reason", type="string", nullable=true, example="Not available")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Addon request updated"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Addon request not found"),
     *     @OA\Response(response=422, description="Already responded to")
     * )
     */
    public function respondAddon(Request $request)
    {
        $request->validate([
            'addon_request_id' => 'required|exists:booking_addon_requests,id',
            'action'            => 'required|in:accepted,rejected',
            'reject_reason'     => 'required_if:action,rejected|nullable|string|max:500',
        ]);

        $provider = auth()->user();

        $addonRequest = BookingAddonRequest::with('booking')
            ->find($request->addon_request_id);

        if (!$addonRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Addon request not found',
            ], 404);
        }

        $booking = $addonRequest->booking;

        if (!$booking || $booking->provider_id !== $provider->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($addonRequest->status !== BookingAddonRequest::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => "This addon request has already been {$addonRequest->status}.",
            ], 422);
        }

        DB::beginTransaction();

        try {
            if ($request->action === 'accepted') {

                // ── Add the addon items directly to the booking's items ──
                // Field set + category gating mirrors
                // BookingController::attachItems() exactly, so addon-created
                // rows are indistinguishable in shape from cart-created ones.
                foreach ($addonRequest->items as $item) {
                    $booking->serviceItems()->create([
                        'service_id'      => $item['service_id'],
                        'service_name'    => $item['service_name'],
                        'service_type'    => 1, // 1 = addon, matches addonItems() relation
                        'quantity'        => $item['quantity'],
                        'price'           => $item['price'],
                        'service_item_id' => $booking->service_category_id == 1
                                                ? ($item['service_item_id'] ?? null) : null,
                        'class_name'      => $booking->service_category_id == 3
                                                ? ($item['class_name'] ?? null) : null,
                        'type'            => $item['type'] ?? null,
                        'subject_type'    => $item['subject_type'] ?? null,
                        'min_people'      => $item['min_people'] ?? null,
                        'max_people'      => $item['max_people'] ?? null,
                        'total_price'     => $item['total_price'],
                    ]);
                }

                // ── Increment (not overwrite) the booking totals ──
                $booking->update([
                    'total_amount'   => round($booking->total_amount + $addonRequest->total_amount, 2),
                    'payable_amount' => round($booking->payable_amount + $addonRequest->total_amount, 2),
                ]);

                $addonRequest->update([
                    'status' => BookingAddonRequest::STATUS_ACCEPTED,
                ]);

                $notifTitle = 'Addon Request Accepted';
                $notifBody  = 'Your addon request for booking No: ' . $booking->booking_number . ' has been accepted.';

            } else {

                $addonRequest->update([
                    'status'        => BookingAddonRequest::STATUS_REJECTED,
                    'reject_reason' => $request->reject_reason,
                ]);

                $notifTitle = 'Addon Request Rejected';
                $notifBody  = 'Your addon request for booking No: ' . $booking->booking_number . ' was rejected.';
            }

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Addon respond error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Unable to process addon response',
                'error'   => $e->getMessage(),
            ], 500);
        }

        try {
            app(FirebaseNotificationService::class)->sendPushNotificationSync(
                [$booking->user_id],
                $notifTitle,
                $notifBody,
                false,
                'addon_' . $addonRequest->status,
                [
                    'type'             => 'addon_' . $addonRequest->status,
                    'entity'           => 'booking_addon_request',
                    'entity_id'        => $addonRequest->id,
                    'booking_id'       => $booking->id,
                    'booking_number'   => $booking->booking_number,
                    'addon_request_id' => $addonRequest->id,
                ]
            );
        } catch (\Throwable $e) {
            Log::info('Addon response notification failed', [
                'booking_id' => $booking->id,
                'error'      => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Addon request ' . $addonRequest->status,
            'data'    => $addonRequest->fresh(),
            'booking' => $booking->fresh()->load('items', 'addonItems'),
        ]);
    }
}