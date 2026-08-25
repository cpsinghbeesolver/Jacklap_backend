<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Cart;
use App\Models\BookingItem;
use App\Models\BookingImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Events\BookingStatusUpdated;
use App\Events\NewBookingCreated;
use App\Events\cancelBooking;
use Illuminate\Support\Facades\Log;
use App\Services\FirebaseNotificationService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Mail;
use App\Mail\{BookingCreatedMail, BookingStatusMail};

class BookingController extends Controller
{
    // ────────────────────────────────────────────────────────────────────
    // STORE BOOKING
    // ────────────────────────────────────────────────────────────────────

    /**
     * @OA\Post(
     *     path="/booking/store",
     *     summary="Create booking(s) from cart",
     *     security={{ "bearerAuth": {} }},
     *     tags={"Booking"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"payment_method"},
     *             @OA\Property(
     *                 property="payment_method",
     *                 type="string",
     *                 enum={"cod","online","wallet"},
     *                 example="cod"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Booking created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Recurring booking created with 8 slots"),
     *             @OA\Property(property="parent_booking", type="object"),
     *             @OA\Property(property="slot_bookings", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or empty cart",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Cart is empty")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Something went wrong")
     *         )
     *     )
     * )
     */
    public function storeBooking(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:cod,online,wallet',
        ]);

        $user = auth()->user();

        $cart = Cart::with('serviceItems')
            ->where('user_id', $user->id)
            ->first();

        if (!$cart || $cart->serviceItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty',
            ], 422);
        }

        $timeSlots = $cart->time_slots ?? [];

        if (empty($timeSlots)) {
            return response()->json([
                'success' => false,
                'message' => 'No time slots in cart. Please re-add items.',
            ], 422);
        }

        DB::beginTransaction();

        try {
            $slotHours  = $this->computeSlotHours($timeSlots);
            $totalHours = array_sum($slotHours);

            $itemsCount      = $cart->serviceItems->count();
            $hoursPerService = $itemsCount > 0 ? $totalHours / $itemsCount : $totalHours;
            if(in_array($cart->service_category_id, [1,2,3])) {
                $totalHours = $itemsCount; // Total hours equals number of items for teaching category
            }
            if(in_array($cart->service_category_id, [5,1,2,3])) {
                $hoursPerService = 1; // For teaching category, each service is considered 1 hour
            }
            $totalAmount = 0;
            foreach ($cart->serviceItems as $item) {
                $totalAmount += $item->price * $item->quantity * $hoursPerService;
            }
            $totalAmount = round($totalAmount, 2);
            // $otp = rand(100000, 999999);
            // ── Parent booking ───────────────────────────────────────────
            $parentBooking = Booking::create([
                'user_id'             => $user->id,
                'provider_id'         => $cart->provider_id,
                'service_category_id' => $cart->service_category_id,
                'booking_number'      => $this->generateBookingNumber(),
                'parent_booking_id'   => null,
                'transmission_type'   => $cart->transmission_type,
                'start_datetime'      => $cart->start_datetime,
                'end_datetime'        => $cart->end_datetime,

                'slot_date'           => null,
                'slot_start_time'     => null,
                'slot_end_time'       => null,
                'slot_index'          => null,
                // 'otp'                 => $otp,
                'duration_type'       => $cart->duration_type,
                'is_recurring'        => $cart->is_recurring,
                'recurring_weeks'     => $cart->recurring_weeks,
                'selected_days'       => $cart->selected_days,
                'time_slots'          => $timeSlots,

                'total_hours'         => $totalHours,
                'total_amount'        => $cart->total_amount,
                'discount'            => 0,
                'tax'                 => 0,
                'payable_amount'      => $cart->total_amount,

                'address_id'          => $cart->address_id,
                'address_json'        => $cart->address_json,

                'license_types'       => $cart->license_types,
                'material_ids'        => $cart->material_ids,
                'service_use_cases'   => $cart->service_use_cases,
                'service_requirements' => $cart->service_requirements??null,
                'status'              => 'pending',
                'payment_method'      => $request->payment_method,
            ]);

            $this->attachItems($parentBooking, $cart->serviceItems, $totalHours);

            // ── Child slot bookings ──────────────────────────────────────
            $childBookings = [];

            foreach ($timeSlots as $index => $slot) {

                $slotDuration = $slotHours[$index];
                $slotHoursPerService = $itemsCount > 0 ? $slotDuration / $itemsCount : $slotDuration;
                
                if(in_array($cart->service_category_id, [1,2,3])) {
                    $slotDuration = $itemsCount; // For teaching category, each service is considered 1 hour
                }
                if(in_array($cart->service_category_id, [5,1,2,3])) {
                    $slotHoursPerService = 1; // For teaching category, each service is considered 1 hour
                }
                $slotPayable = 0;
                foreach ($cart->serviceItems as $item) {
                    $slotPayable += $item->price * $item->quantity * $slotHoursPerService;
                }
                $slotPayable = round($slotPayable, 2);
                // $otp = rand(100000, 999999);
                $child = Booking::create([
                    'user_id'             => $user->id,
                    'provider_id'         => $cart->provider_id,
                    'service_category_id' => $cart->service_category_id,
                    'booking_number'      => $this->generateBookingNumber('SLOT'),
                    'parent_booking_id'   => $parentBooking->id,
                    'transmission_type'   => $cart->transmission_type,
                    'start_datetime'      => $slot['date'] . ' ' . $slot['start_time'],
                    'end_datetime'        => $slot['date'] . ' ' . $slot['end_time'] ?? null,
                    'service_requirements' => $cart->service_requirements??null,
                    'slot_date'           => $slot['date'],
                    'slot_start_time'     => $slot['start_time'],
                    'slot_end_time'       => $slot['end_time'] ?? null,
                    'slot_index'          => $index + 1,

                    'duration_type'       => $cart->duration_type,
                    'is_recurring'        => $cart->is_recurring,
                    'recurring_weeks'     => $cart->recurring_weeks,
                    'selected_days'       => $cart->selected_days,
                    'time_slots'          => null,

                    'total_hours'         => $slotDuration,
                    'total_amount'        => $slotPayable,
                    'discount'            => 0,
                    'tax'                 => 0,
                    'payable_amount'      => $slotPayable,
                    // 'otp'                 => $otp,
                    'address_id'          => $cart->address_id,
                    'address_json'        => $cart->address_json,

                    'license_types'       => $cart->license_types,
                    'service_use_cases'   => $cart->service_use_cases,
                    'material_ids'        => $cart->material_ids,
                    'status'              => 'pending',
                    'payment_method'      => $request->payment_method,
                ]);

                $this->attachItems($child, $cart->serviceItems, $slotDuration);
                $childBookings[] = $child;
            }

            // ── Clear cart ───────────────────────────────────────────────
            $cart->serviceItems()->delete();
            $cart->delete();

            DB::commit();

            //Passing child bookings to send notification and email
            try {
                if(!empty($childBookings)){
                    foreach($childBookings as $childBooking){
                        $this->sendNewBookingNotification($childBooking);
                        Mail::send(new BookingCreatedMail($childBooking));

                        //New booking Socket
                        event(new NewBookingCreated($childBooking->id));
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error sending booking notifications: ' . $e->getMessage());
            }

            return response()->json([
                'success'        => true,
                'message'        => count($childBookings) > 1
                    ? 'Recurring booking created with ' . count($childBookings) . ' slots'
                    : 'Booking created successfully',
                'parent_booking' => $parentBooking->load('items', 'childBookings'),
                'slot_bookings'  => collect($childBookings)->map->load('items'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/booking/update",
     *     summary="Update a booking's schedule and/or per-item pricing (in-place, no delete/recreate)",
     *     description="Reschedules an existing parent booking and its child slot bookings using new time_slots. For service_category_id 1 and 3, pricing is recalculated from the `items` array (booking_item_id + minutes, referencing the PARENT's own items only) and the resulting per-service minutes are propagated to every related child booking by service_id. For all other categories, pricing is derived from time_slots as before.",
     *     security={{ "bearerAuth": {} }},
     *     tags={"Booking"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"parent_booking_id","duration_type","start_datetime","end_datetime","time_slots"},
     *             @OA\Property(property="parent_booking_id", type="integer", example=12),
     *             @OA\Property(property="duration_type", type="string", enum={"single_day","multiple_days"}, example="single_day"),
     *             @OA\Property(property="start_datetime", type="string", format="date-time", example="2026-08-18T10:00:00.000Z"),
     *             @OA\Property(property="end_datetime", type="string", format="date-time", example="2026-08-18T14:00:00.000Z"),
     *             @OA\Property(
     *                 property="time_slots",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"date","start_time","end_time"},
     *                     @OA\Property(property="date", type="string", format="date", example="2026-08-18"),
     *                     @OA\Property(property="start_time", type="string", example="10:00:00"),
     *                     @OA\Property(property="end_time", type="string", example="14:00:00")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 description="Only used/required when the parent booking's service_category_id is 1 or 3. Each booking_item_id may belong to the parent OR any of its child bookings — it is resolved to a service_id and the given minutes are applied to the matching item on the parent AND every child booking.",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"booking_item_id","minutes"},
     *                     @OA\Property(property="booking_item_id", type="integer", example=1824, description="Belongs to the parent booking or any of its child bookings."),
     *                     @OA\Property(property="minutes", type="integer", example=30, description="Duration in minutes for this service. Item price is treated as an hourly rate.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Booking updated successfully"),
     *     @OA\Response(response=404, description="Booking not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function updateBooking(Request $request)
    {
        $request->validate([
            'parent_booking_id'         => 'required|exists:bookings,id',
            'duration_type'             => 'required|in:single_day,multiple_days',
            'start_datetime'            => 'required|date',
            'end_datetime'              => 'required|date|after:start_datetime',
            'time_slots'                => 'required|array|min:1',
            'time_slots.*.date'         => 'required|date',
            'time_slots.*.start_time'   => 'required',
            'time_slots.*.end_time'     => 'required',

            // Only enforced/used when service_category_id is 1 or 3 (checked below,
            // since we don't know the category until we've loaded the booking).
            // booking_item_id here must belong to the PARENT booking only.
            'items'                     => 'sometimes|array',
            'items.*.booking_item_id'   => 'required_with:items|integer',
            'items.*.minutes'           => 'required_with:items|integer|min:1',
        ]);

        $user = auth()->user();

        $parentBooking = Booking::with(['childBookings.items', 'serviceItems'])
            ->where('id', $request->parent_booking_id)
            ->where('provider_id', $user->id)
            ->whereNull('parent_booking_id')
            ->first();

        if (!$parentBooking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        if (in_array($parentBooking->status, ['completed', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => "Cannot update a booking that is already {$parentBooking->status}.",
            ], 422);
        }

        // Cart is gone — the booking's own stored items are the only surviving source of truth
        $items = $parentBooking->serviceItems;

        if ($items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No service items found on this booking.',
            ], 422);
        }

        $timeSlots         = $request->time_slots;
        $useMinutesPricing = in_array($parentBooking->service_category_id, [1, 3]);

        // service_id => minutes, resolved from the PARENT's own booking_item_id values.
        // This is what gets propagated to every child booking.
        $minutesByServiceId = [];

        if ($useMinutesPricing) {
            if (empty($request->items)) {
                return response()->json([
                    'success' => false,
                    'message' => 'items array (booking_item_id + minutes) is required for this service category.',
                ], 422);
            }

            // No ownership check here — booking_item_id can belong to the parent
            // or any child booking (e.g. the first child), we just need its
            // service_id. Whatever service_ids resolve get priced on the parent
            // AND every child booking that carries that service_id.
            $bookingItemIds = collect($request->items)->pluck('booking_item_id')->unique();

            $resolvedItems = BookingItem::whereIn('id', $bookingItemIds)
                ->get()
                ->keyBy('id');

            foreach ($request->items as $entry) {
                $resolvedItem = $resolvedItems->get($entry['booking_item_id']);

                if (!$resolvedItem) {
                    continue; // unknown id — nothing to resolve, skip silently
                }

                $minutesByServiceId[$resolvedItem->service_id] = (int) $entry['minutes'];
            }
        }

        DB::beginTransaction();

        try {
            if ($useMinutesPricing) {
                /*
                |--------------------------------------------------------------------------
                | NEW PATH (category 1 & 3): pricing driven by service_id + minutes,
                | resolved from the parent/child items and applied everywhere.
                |
                | Parent's own item rows are priced per-occurrence here (same formula
                | as every child), but total_hours/total_amount on the PARENT are not
                | set yet — those represent the WHOLE booking (summed across every
                | child session) and are only known once the slot loop below has run.
                |--------------------------------------------------------------------------
                */

                $this->applyMinutesPricingByServiceId($parentBooking, $minutesByServiceId);

                $parentBooking->update([
                    'start_datetime' => $request->start_datetime,
                    'end_datetime'   => $request->end_datetime,
                    'duration_type'  => $request->duration_type,
                    'time_slots'     => $timeSlots,
                ]);

            } else {
                /*
                |--------------------------------------------------------------------------
                | ORIGINAL PATH: hours derived from time_slots, split evenly across items
                |--------------------------------------------------------------------------
                */

                $slotHours  = $this->computeSlotHours($timeSlots);
                $totalHours = array_sum($slotHours);

                $itemsCount      = $items->count();
                $hoursPerService = $itemsCount > 0 ? $totalHours / $itemsCount : $totalHours;

                if (in_array($parentBooking->service_category_id, [5, 2])) {
                    $hoursPerService = 1; // teaching category: each service = 1 hour
                }

                $totalAmount = 0;
                foreach ($items as $item) {
                    $totalAmount += $item->price * $item->quantity * $hoursPerService;
                }
                $totalAmount = round($totalAmount, 2);

                $parentBooking->update([
                    'start_datetime' => $request->start_datetime,
                    'end_datetime'   => $request->end_datetime,
                    'duration_type'  => $request->duration_type,
                    'time_slots'     => $timeSlots,

                    'total_hours'    => $totalHours,
                    'total_amount'   => $totalAmount,
                    'payable_amount' => $totalAmount,
                ]);

                $this->updateItemsPricingLegacy($parentBooking, $items, $totalHours);
            }

            /*
            |--------------------------------------------------------------------------
            | Update Existing Child Slot Bookings In Place
            |--------------------------------------------------------------------------
            */

            $existingChildren = $parentBooking->childBookings->keyBy('slot_index');
            $childBookings     = [];

            // Only used in the useMinutesPricing branch — summed across every
            // child session to become the PARENT's total_hours/total_amount.
            $wholeTotalHours  = 0;
            $wholeTotalAmount = 0;

            foreach ($timeSlots as $index => $slot) {

                $slotIndex = $index + 1;
                $child     = $existingChildren->get($slotIndex);

                if ($child) {
                    // ── Matching slot already exists — update schedule + pricing in place ──

                    if ($useMinutesPricing) {
                        [$slotHoursTotal, $slotPayable] = $this->applyMinutesPricingByServiceId(
                            $child,
                            $minutesByServiceId
                        );

                        $wholeTotalHours  += $slotHoursTotal;
                        $wholeTotalAmount += $slotPayable;
                    } else {
                        $slotDuration        = $slotHours[$index];
                        $slotHoursPerService = $itemsCount > 0 ? $slotDuration / $itemsCount : $slotDuration;

                        if (in_array($parentBooking->service_category_id, [2])) {
                            $slotDuration = $itemsCount;
                        }
                        if (in_array($parentBooking->service_category_id, [5, 1, 2, 3])) {
                            $slotHoursPerService = 1;
                        }

                        $slotPayable = 0;
                        foreach ($items as $item) {
                            $slotPayable += $item->price * $item->quantity * $slotHoursPerService;
                        }
                        $slotPayable    = round($slotPayable, 2);
                        $slotHoursTotal = $slotDuration;
                    }

                    $child->update([
                        'start_datetime'  => $slot['date'] . ' ' . $slot['start_time'],
                        'end_datetime'    => $slot['date'] . ' ' . $slot['end_time'] ?? null,
                        'slot_date'       => $slot['date'],
                        'slot_start_time' => $slot['start_time'],
                        'slot_end_time'   => $slot['end_time'] ?? null,
                        'duration_type'   => $request->duration_type,

                        'total_hours'     => $slotHoursTotal,
                        'total_amount'    => $slotPayable,
                        'payable_amount'  => $slotPayable,
                    ]);

                    if (!$useMinutesPricing) {
                        $this->updateItemsPricingLegacy($child, $items, $slotHoursTotal);
                    }

                } else {
                    // ── New slot added compared to before — create it, attach items, then price ──

                    // Placeholder duration for creation; recalculated properly right after.
                    $slotDuration = $useMinutesPricing
                        ? array_sum($minutesByServiceId) / 60
                        : $slotHours[$index];

                    if (!$useMinutesPricing) {
                        $slotHoursPerService = $itemsCount > 0 ? $slotDuration / $itemsCount : $slotDuration;
                        $slotPayable = 0;
                        foreach ($items as $item) {
                            $slotPayable += $item->price * $item->quantity * $slotHoursPerService;
                        }
                        $slotPayable = round($slotPayable, 2);
                    }

                    $child = Booking::create([
                        'user_id'              => $user->id,
                        'provider_id'          => $parentBooking->provider_id,
                        'service_category_id'  => $parentBooking->service_category_id,
                        'booking_number'       => $this->generateBookingNumber('SLOT'),
                        'parent_booking_id'    => $parentBooking->id,
                        'transmission_type'    => $parentBooking->transmission_type,

                        'start_datetime'       => $slot['date'] . ' ' . $slot['start_time'],
                        'end_datetime'         => $slot['date'] . ' ' . $slot['end_time'],
                        'service_requirements' => $parentBooking->service_requirements,

                        'slot_date'            => $slot['date'],
                        'slot_start_time'      => $slot['start_time'],
                        'slot_end_time'        => $slot['end_time'],
                        'slot_index'           => $slotIndex,

                        'duration_type'        => $request->duration_type,
                        'is_recurring'         => $parentBooking->is_recurring,
                        'recurring_weeks'      => $parentBooking->recurring_weeks,
                        'selected_days'        => $parentBooking->selected_days,
                        'time_slots'           => null,

                        'total_hours'          => $slotDuration,
                        'total_amount'         => $useMinutesPricing ? 0 : $slotPayable,
                        'discount'             => 0,
                        'tax'                  => 0,
                        'payable_amount'       => $useMinutesPricing ? 0 : $slotPayable,

                        'address_id'           => $parentBooking->address_id,
                        'address_json'         => $parentBooking->address_json,

                        'license_types'        => $parentBooking->license_types,
                        'material_ids'         => $parentBooking->material_ids,
                        'service_use_cases'    => $parentBooking->service_use_cases,

                        'status'               => 'pending',
                        'payment_method'       => $parentBooking->payment_method,
                    ]);

                    // attachItems() clones the parent's items (same service_ids) onto
                    // the new child. Once they exist, price them the same way as
                    // every other related booking, by service_id.
                    $this->attachItems($child, $items, $slotDuration);

                    if ($useMinutesPricing) {
                        [$slotHoursTotal, $slotPayable] = $this->applyMinutesPricingByServiceId(
                            $child,
                            $minutesByServiceId
                        );

                        $child->update([
                            'total_hours'    => $slotHoursTotal,
                            'total_amount'   => $slotPayable,
                            'payable_amount' => $slotPayable,
                        ]);

                        $wholeTotalHours  += $slotHoursTotal;
                        $wholeTotalAmount += $slotPayable;
                    }
                }

                $childBookings[] = $child;
                $existingChildren->forget($slotIndex);
            }

            // Any leftover children with no matching slot anymore → cancel (never delete)
            foreach ($existingChildren as $leftoverChild) {
                if (!in_array($leftoverChild->status, ['completed', 'cancelled'])) {
                    $leftoverChild->update([
                        'status'        => 'cancelled',
                        'cancel_reason' => 'Slot removed during booking update',
                    ]);
                }
            }

            if ($useMinutesPricing) {
                // Parent totals = sum across every child session (the whole booking),
                // not just the parent's own single set of item rows.
                $parentBooking->update([
                    'total_hours'    => $wholeTotalHours,
                    'total_amount'   => round($wholeTotalAmount, 2),
                    'payable_amount' => round($wholeTotalAmount, 2),
                ]);
            }

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | Notifications — only for newly created slots
            |--------------------------------------------------------------------------
            */

            try {
                foreach ($childBookings as $childBooking) {
                    if ($childBooking->wasRecentlyCreated) {
                        $this->sendNewBookingNotification($childBooking);
                        Mail::send(new BookingCreatedMail($childBooking));
                        event(new NewBookingCreated($childBooking->id));
                    } else {
                        broadcast(new BookingStatusUpdated(
                            $childBooking->id,
                            $childBooking->status,
                            $childBooking->provider_id,
                            $childBooking->user_id
                        ));
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error sending booking update notifications: ' . $e->getMessage());
            }

            $parentBooking->refresh();

            return response()->json([
                'success' => true,
                'message' => count($childBookings) > 1
                    ? 'Recurring booking updated with ' . count($childBookings) . ' slots'
                    : 'Booking updated successfully',
                'parent_booking' => $parentBooking->load('items', 'childBookings'),
                'slot_bookings'  => collect($childBookings)->map->load('items'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Booking update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * NEW: Prices a booking's items (parent OR any child) by matching on
     * service_id against $minutesByServiceId (resolved once, from the parent's
     * own items). Every related booking gets the SAME minutes per service —
     * that's the propagation. Each item's stored `price` is an hourly rate.
     *
     * Returns [totalHours, totalAmount] for the given booking.
     */
    private function applyMinutesPricingByServiceId(Booking $booking, array $minutesByServiceId)
    {
        if (empty($minutesByServiceId)) {
            return [0, 0];
        }

        $bookingItems = $booking->serviceItems()->get()->keyBy('service_id');

        $totalHours  = 0;
        $totalAmount = 0;

        foreach ($minutesByServiceId as $serviceId => $minutes) {
            $bookingItem = $bookingItems->get($serviceId);

            // This booking doesn't carry that service — nothing to price.
            if (!$bookingItem) {
                continue;
            }

            $hours        = $minutes / 60;
            $pricePerHour = $bookingItem->price; // already-stored default per-hour price
            $itemTotal    = round($pricePerHour * $bookingItem->quantity * $hours, 2);

            $bookingItem->update([
                'total_price' => $itemTotal,
            ]);

            $totalHours  += $hours;
            $totalAmount += $itemTotal;
        }

        return [$totalHours, round($totalAmount, 2)];
    }

    /**
     * ORIGINAL logic, renamed to _Legacy and fixed (was referencing an
     * out-of-scope $parentBooking->service_category_id before). Still used
     * for every service_category_id NOT in [1, 3].
     */
    private function updateItemsPricingLegacy(Booking $booking, $items, float $hours)
    {
        $itemsCount      = count($items);
        $hoursPerService = $itemsCount > 0 ? $hours / $itemsCount : $hours;

        if (in_array($booking->service_category_id, [5, 2])) {
            $hoursPerService = 1; // teaching category: each service = 1 hour
        }

        $bookingItems = $booking->serviceItems()->get()->keyBy('service_id');

        foreach ($items as $item) {
            $bookingItem = $bookingItems->get($item->service_id);

            if ($bookingItem) {
                $bookingItem->update([
                    'quantity'    => $item->quantity,
                    'price'       => $item->price,
                    'total_price' => round($item->price * $item->quantity * $hoursPerService, 2),
                ]);
            }
        }
    }

    // ────────────────────────────────────────────────────────────────────
    // LIST SLOTS
    // ────────────────────────────────────────────────────────────────────

    /**
     * @OA\Get(
     *     path="/booking/{id}/slots",
     *     summary="List all slot bookings under a parent booking",
     *     security={{ "bearerAuth": {} }},
     *     tags={"Booking"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Parent booking ID"
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         description="Filter slots by status",
     *         @OA\Schema(type="string", enum={"pending","in_progress","confirmed","start_journey","completed","cancelled"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Slot list returned successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="parent_booking", type="object"),
     *             @OA\Property(property="total_slots", type="integer", example=8),
     *             @OA\Property(property="slots", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Booking not found")
     *         )
     *     )
     * )
     */
    public function listSlots(Request $request, int $id)
    {
        $user = auth()->user();

        $parent = Booking::where('id', $id)
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('provider_id', $user->id);
            })
            ->firstOrFail();

        $query = Booking::where('parent_booking_id', $parent->id)
                        ->orderBy('slot_index');

        if ($request->filled('status')) {
            $request->validate([
                'status' => 'in:pending,confirmed,start_journey,in_progress,completed,cancelled',
            ]);
            $query->where('status', $request->status);
        }

        $slots = $query->with('items')->get();

        return response()->json([
            'success'        => true,
            'parent_booking' => $parent,
            'total_slots'    => $slots->count(),
            'slots'          => $slots->map(fn($s) => [
                'id'              => $s->id,
                'slot_index'      => $s->slot_index,
                'booking_number'  => $s->booking_number,
                'slot_date'       => $s->slot_date,
                'slot_start_time' => $s->slot_start_time,
                'slot_end_time'   => $s->slot_end_time,
                'status'          => $s->status,
                'cancel_reason'   => $s->cancel_reason,
                'total_hours'     => $s->total_hours,
                'payable_amount'  => $s->payable_amount,
                'items'           => $s->items,
            ]),
        ]);
    }

    // ────────────────────────────────────────────────────────────────────
    // START SLOT
    // ────────────────────────────────────────────────────────────────────

    /**
     * @OA\Post(
     *     path="/booking/{id}/start",
     *     summary="Provider starts a specific slot booking",
     *     security={{ "bearerAuth": {} }},
     *     tags={"Booking"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Child slot booking ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Slot started successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Slot started successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Cannot start booking",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Booking is already in_progress")
     *         )
     *     )
     * )
     */
    public function startSlot(int $id)
    {
        $provider = auth()->user();

        $booking = Booking::where('id', $id)
            ->where('provider_id', $provider->id)
            ->firstOrFail();

        if ($booking->isParent() && $booking->isRecurringBooking()) {
            return response()->json([
                'success' => false,
                'message' => 'Start individual slot bookings, not the parent.',
            ], 422);
        }

        if (!$booking->canBeStarted()) {
            return response()->json([
                'success' => false,
                'message' => "Booking is already {$booking->status}.",
            ], 422);
        }

        $booking->update(['status' => 'in_progress']);

        if ($booking->parent_booking_id) {
            $parent = Booking::find($booking->parent_booking_id);
            if ($parent && $parent->status === 'pending') {
                $parent->update(['status' => 'in_progress']);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Slot started successfully',
            'data'    => $booking->fresh()->load('items'),
        ]);
    }

    // ────────────────────────────────────────────────────────────────────
    // COMPLETE SLOT
    // ────────────────────────────────────────────────────────────────────

    /**
     * @OA\Post(
     *     path="/booking/{id}/complete",
     *     summary="Provider marks a slot booking as completed",
     *     security={{ "bearerAuth": {} }},
     *     tags={"Booking"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Child slot booking ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Slot completed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Slot completed"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Cannot complete booking",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Only in-progress slots can be completed")
     *         )
     *     )
     * )
     */
    public function completeSlot(int $id)
    {
        $provider = auth()->user();

        $booking = Booking::where('id', $id)
            ->where('provider_id', $provider->id)
            ->firstOrFail();

        if (!$booking->canBeCompleted()) {
            return response()->json([
                'success' => false,
                'message' => 'Only in-progress slots can be completed.',
            ], 422);
        }

        $booking->update(['status' => 'completed']);

        if ($booking->parent_booking_id) {
            $this->syncParentStatus($booking->parent_booking_id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Slot completed',
            'data'    => $booking->fresh()->load('items'),
        ]);
    }

    // ────────────────────────────────────────────────────────────────────
    // CANCEL BOOKING
    // ────────────────────────────────────────────────────────────────────

    /**
     * @OA\Post(
     *     path="/booking/{id}/cancel",
     *     summary="Cancel a specific slot or entire parent booking",
     *     security={{ "bearerAuth": {} }},
     *     tags={"Booking"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Booking ID (parent or child slot)"
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="cancel_all", type="boolean", example=false, description="Pass true to cancel all slots under a parent"),
     *             @OA\Property(property="reason", type="string", example="Not available this day")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cancelled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Slot booking cancelled"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Cannot cancel booking",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Cannot cancel a slot that is already in progress")
     *         )
     *     )
     * )
     */
    public function cancelBooking(Request $request, int $id)
    {
        $request->validate([
            'cancel_all' => 'sometimes|boolean',
            'reason'     => 'sometimes|string|max:500',
        ]);

        $user    = auth()->user();
        $booking = Booking::where('id', $id)->firstOrFail();

        if ($booking->user_id !== $user->id && $booking->provider_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if (in_array($booking->status, ['completed', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => "Booking is already {$booking->status}.",
            ], 422);
        }

        if ($request->boolean('cancel_all') || $booking->isParent()) {
            $this->cancelParentAndChildren($booking, $request->reason);

            return response()->json([
                'success' => true,
                'message' => 'All slot bookings cancelled',
                'data'    => $booking->fresh()->load('childBookings'),
            ]);
        }

        if ($booking->status === 'in_progress') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel a slot that is already in progress.',
            ], 422);
        }

        $booking->update([
            'status'        => 'cancelled',
            'cancel_reason' => $request->reason,
        ]);

        if ($booking->parent_booking_id) {
            $this->syncParentStatus($booking->parent_booking_id);
        }

        //Add Notification
        $this->sendCancelBookingNotification($booking);

        //New booking Socket
        //event(new cancelBooking($booking)); 
        broadcast(new BookingStatusUpdated(
            $booking->id,
            $booking->status,
            $booking->provider_id,
            $booking->user_id
        ));

        return response()->json([
            'success' => true,
            'message' => 'Slot booking cancelled',
            'data'    => $booking->fresh(),
        ]);
    }

    // ────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ────────────────────────────────────────────────────────────────────

    private function attachItems(Booking $booking, $items, float $hours)
    {
        $itemsCount      = count($items);  // or $items->count() if it's a collection
        $hoursPerService = $itemsCount > 0 ? $hours / $itemsCount : $hours;
        if($booking->service_category_id == 5) {
            $hoursPerService = 1; // For teaching category, each service is considered 1 hour
        }
        foreach ($items as $item) {
            $booking->serviceItems()->create([
                'service_id'      => $item->service_id,
                'service_name'    => $item->service_name,
                'service_type'    => $item->service_type,
                'quantity'        => $item->quantity,
                'price'           => $item->price,
                'service_item_id' => $booking->service_category_id == 1
                                        ? ($item->service_item_id ?? null) : null,
                'class_name'      => $booking->service_category_id == 3
                                        ? ($item->class_name ?? null) : null,
                'type'            => $item->type ?? null,
                'subject_type'    => $item->subject_type ?? null,
                'min_people'      => $item->min_people ?? null,
                'max_people'      => $item->max_people ?? null,
                // ✅ each item gets its share of the slot hours
                'total_price'     => $item->price * $item->quantity * $hoursPerService,
            ]);
        }
    }

    private function computeSlotHours(array $timeSlots): array
    {
        return array_map(function ($slot) {
            $s = Carbon::parse($slot['date'] . ' ' . $slot['start_time']);
            $e = Carbon::parse($slot['date'] . ' ' . $slot['end_time']);

            if ($e->lessThan($s)) {
                $e->addDay();
            }

            return round($s->diffInMinutes($e) / 60, 2);
        }, $timeSlots);
    }

    private function cancelParentAndChildren(Booking $parent, ?string $reason)
    {
        Booking::where('parent_booking_id', $parent->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->update([
                'status'        => 'cancelled',
                'cancel_reason' => $reason,
            ]);

        $parent->update([
            'status'        => 'cancelled',
            'cancel_reason' => $reason,
        ]);
    }

    private function syncParentStatus(int $parentId)
    {
        $parent = Booking::find($parentId);
        if (!$parent) return;

        $children = Booking::where('parent_booking_id', $parentId)->get();
        if ($children->isEmpty()) return;

        $statuses = $children->pluck('status')->unique()->values()->toArray();

        if ($statuses === ['completed']) {
            $parent->update(['status' => 'completed']);

        } elseif ($statuses === ['cancelled']) {
            $parent->update(['status' => 'cancelled']);

        } elseif (in_array('in_progress', $statuses)) {
            $parent->update(['status' => 'in_progress']);

        }elseif (in_array('start_journey', $statuses)) {
            $parent->update(['status' => 'start_journey']);

        } elseif (
            !in_array('pending', $statuses) &&
            !in_array('in_progress', $statuses)
        ) {
            $parent->update(['status' => 'completed']);
        }
    }

    private function generateBookingNumber(string $prefix = 'BOOK'): string
    {
        do {
            $number = $prefix . '-' . date('Ymd') . '-' . rand(10000, 99999);
        } while (Booking::where('booking_number', $number)->exists());

        return $number;
    }

    /**
     * @OA\Get(
     *     path="/booking",
     *     summary="Get user bookings (filter by status)",
     *     security={{"bearerAuth":{}}},
     *     tags={"Booking"},
     *
     *     @OA\Parameter(
     *         name="status[]",
     *         in="query",
     *         required=false,
     *         description="Filter bookings by status",
     *         style="form",
     *         explode=true,
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items(
     *                 type="string",
     *                 enum={"pending","confirmed","start_journey","in_progress","completed","cancelled"}
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Bookings fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function getBookings(Request $request)
    {
        $user = auth()->user();

        $statuses = [];

        if ($request->filled('status') && !is_array($request->status)) {
            $statuses = explode(',', $request->status);
        }else{
            $statuses = $request->status;
        }

        validator(
            ['status' => $statuses],
            [
                'status' => 'nullable|array',
                'status.*' => 'string|in:pending,confirmed,start_journey,in_progress,completed,cancelled'
            ]
        )->validate();

        $query = Booking::with(['serviceCategory','items.service:id,name,is_default,type','addonItems.addonService:id,name,type,price','user:id,name,country_code,phone,image', 'provider:id,name,image'])->whereNotNull('parent_booking_id')
            ->latest();

        if (!empty($statuses)) {
            if (in_array('in_progress', $statuses)) {
                $statuses[] = 'start_journey';
            }
            $query->whereIn('status', $statuses);
        }

        if ($user->hasRole('provider')) {
            $query->where('provider_id', $user->id);
        } else {
            $query->where('user_id', $user->id);
        }

        $bookings = $query->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }

    /**
     * @OA\Get(
     *     path="/booking/{id}",
     *     summary="Get booking detail",
     *     description="Fetch single booking detail by ID",
     *     security={{ "bearerAuth": {} }},
     *     tags={"Booking"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Booking ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Booking detail fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found"
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access"
     *     )
     * )
     */
    public function getBookingDetail($id)
    {
        $user = auth()->user();

        $booking = Booking::with([
                'items',
                'addonItems',
                'provider:id,name,image',
                'user:id,name,country_code,phone,image'
            ])
            ->find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        // Authorization check
        if (
            $user->hasRole('provider') && $booking->provider_id != $user->id ||
            !$user->hasRole('provider') && $booking->user_id != $user->id
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $booking
        ]);
    }

    /**
     * @OA\Post(
     *     path="/booking/update-status",
     *     summary="Update booking status by provider",
     *     description="Update booking status based on action. Booking lifecycle: pending → confirmed → in_progress → completed → cancelled. Actions mapping:
     *                 - confirmed → pending to confirmed
     *                 - cancelled → pending to cancelled
     *                 - start_journey -> confirmed to start journey
     *                 - in_progress → confirmed to in_progress
     *                 - completed → in_progress to completed
     *                 - cancelled → pending/confirmed to cancelled",
     *     security={{ "bearerAuth": {} }},
     *     tags={"Booking"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"booking_id","action"},
     *
     *             @OA\Property(property="booking_id", type="integer", example=12),
     *
     *             @OA\Property(
     *                 property="action",
     *                 type="string",
     *                 enum={"pending","confirmed","start_journey","in_progress","completed","cancelled"},
     *                 example="confirmed",
     *                 description=""
     *             ),
     *              @OA\Property(
     *                  property="otp",
*                       type="string",
     *                  nullable=true,
     *                  example="123456",
     *                  description="Required when action is in_progress"
     *              )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Booking status updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Booking confirmed successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=12),
     *                 @OA\Property(
     *                     property="status",
     *                     type="string",
     *                     example="confirmed",
     *                     description="Possible values: pending, confirmed, in_progress, completed, cancelled"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation / Invalid transition"
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function updateBookingStatus(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'action' => 'required|in:pending,confirmed,start_journey, cancelled,in_progress,completed',
            'otp' => 'required_if:action,in_progress',
        ]);

        $user = auth()->user();

        if (!$user->hasRole('provider')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        DB::beginTransaction();

        try {
            $booking = Booking::where('id', $request->booking_id)
                ->where('provider_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (!$booking) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found'
                ], 404);
            }
            
            if($booking->service_category_id == '5' && ($request->action == 'completed' || $request->action == 'in_progress')){
                if (!$request->hasFile('before_images') && !$request->hasFile('after_images')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please upload images'
                    ], 404);
                }else{
                    if($request->hasFile('before_images')){
                        foreach ($request->file('before_images') as $file) {
                            $path = $file->store('booking_images', 's3');
                            BookingImage::create([
                                'booking_id' => $request->booking_id,
                                'type' => 'before',
                                'path' => $path,
                            ]);
                        }
                    }
                    if($request->hasFile('after_images')){
                        foreach ($request->file('after_images') as $file) {
                            $path = $file->store('booking_images', 's3');
                            BookingImage::create([
                                'booking_id' => $request->booking_id,
                                'type' => 'after',
                                'path' => $path,
                            ]);
                        }
                    }
                }
            }

            $message = '';
            $newStatus = null;

            switch ($request->action) {

                case 'confirmed':
                    if ($booking->status !== 'pending') {
                        throw new \Exception('Only pending booking can be accepted');
                    }
                    $newStatus = 'confirmed';
                    $message = 'Booking confirmed successfully';
                    break;
                case 'start_journey':
                    if ($booking->status !== 'confirmed') {
                        throw new \Exception('Only confirmed booking can be updated to start journey');
                    }

                    // Booking scheduled start time
                    $bookingTime = \Carbon\Carbon::parse($booking->start_datetime);

                    // Allow only within 1 hour before booking start time
                    if (now()->lt($bookingTime->copy()->subHours(2))) {
                        throw new \Exception('You can start journey for booking only within 2 hours of booking start time');
                    }

                    $booking->otp = rand(100000, 999999);
                    $newStatus = 'start_journey';
                    $message = 'Booking journey started successfully';
                    break;
                case 'in_progress':
                    if ($booking->status !== 'start_journey') {
                        throw new \Exception('Only start journey booking can be started');
                    }
                    if ($booking->otp != $request->otp) {
                        throw new \Exception('Invalid OTP');
                    }


                    $booking->booking_start_time = now();
                    $newStatus = 'in_progress';
                    $message = 'Job started successfully';
                    break;

                case 'completed':
                    if ($booking->status !== 'in_progress') {
                        throw new \Exception('Only in-progress booking can be completed');
                    }
                    $booking->booking_end_time = now();
                    $newStatus = 'completed';
                    $message = 'Job completed successfully';
                    break;

                case 'cancelled':
                    if (!in_array($booking->status, ['pending','confirmed'])) {
                        throw new \Exception('Only pending or confirmed booking can be cancelled');
                    }
                    $newStatus = 'cancelled';
                    $message = 'Booking cancelled successfully';
                    break;
            }

            $booking->status = $newStatus;
            $booking->save();

            DB::commit();

            broadcast(new BookingStatusUpdated(
                $booking->id,
                $booking->status,
                $booking->provider_id,
                $booking->user_id
            ));
            Mail::send(new BookingStatusMail($booking));

            $this->sendBookingStatusNotification($booking);

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $booking
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    private function sendBookingStatusNotification(Booking $booking): void
    {
        $title = 'Booking Status Updated';

        $descriptions = [
            'confirmed'     => 'Your booking has been confirmed by the provider.',
            'start_journey' => 'Your provider has started the journey to your location.',
            'in_progress'   => 'Your booking service has started.',
            'completed'     => 'Your booking has been completed successfully.',
            'cancelled'     => 'Your booking has been cancelled by the provider.',
        ];

        $description = $descriptions[$booking->status]
            ?? 'Your booking status has been updated.';

        $notification_type = $booking->status ?? 'booking_updated';

        try {
            app(FirebaseNotificationService::class)->sendPushNotificationSync(
                [$booking->user_id],
                $title,
                $description,
                false,
                $notification_type,
                [
                    'type' => 'booking_status_updated',
                    'entity' => 'booking',
                    'entity_id' => $booking->id,
                    'booking_id' => $booking->id,
                    'parent_booking_id' => $booking->parent_booking_id ?? $booking->id,
                    'booking_number' => $booking->booking_number,
                    'status' => $booking->status,
                ]
            );
        } catch (\Throwable $e) {
            Log::info('Booking status notification failed', [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'status' => $booking->status,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function sendNewBookingNotification(Booking $booking): void
    {
        $title = 'New Booking Request';

        $description = 'You have received a new booking request. Booking No: ' .
            $booking->booking_number;
        $notification_type = "new_booking";             
        try {
            app(FirebaseNotificationService::class)
                ->sendPushNotificationSync(
                    [$booking->provider_id],
                    $title,
                    $description,
                    false,
                    $notification_type,
                    [
                        'type' => 'new_booking',
                        'entity' => 'booking',
                        'entity_id' => $booking->id,
                        'booking_id' => $booking->id,
                        'parent_booking_id' => $booking->id,
                        'booking_number' => $booking->booking_number,
                    ]
                );
        } catch (\Throwable $e) {
            Log::info('New booking provider notification failed', [
                'booking_id' => $booking->id,
                'provider_id' => $booking->provider_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function sendCancelBookingNotification(Booking $booking): void
    {
        $title = 'Booking Cancelled';

        $description = 'Booking No: ' .
            $booking->booking_number.' has been cancelled';
        $notification_type = "cancel_booking";             
        try {
            app(FirebaseNotificationService::class)
                ->sendPushNotificationSync(
                    [$booking->provider_id],
                    $title,
                    $description,
                    false,
                    $notification_type,
                    [
                        'type' => 'cancel_booking',
                        'entity' => 'booking',
                        'entity_id' => $booking->id,
                        'booking_id' => $booking->id,
                        'parent_booking_id' => $booking->id,
                        'booking_number' => $booking->booking_number,
                    ]
                );
        } catch (\Throwable $e) {
            Log::info('Cancel booking provider notification failed', [
                'booking_id' => $booking->id,
                'provider_id' => $booking->provider_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function downloadInvoice($id,Request $request)
    {
        $booking = Booking::with(['user', 'provider', 'items'])->findOrFail($id);
        
        if ($request->filled('timezone')) {
            $booking->start_datetime = Carbon::parse($booking->start_datetime)->setTimezone($request->timezone)->format('Y-m-d H:i:s');
            $booking->end_datetime = Carbon::parse($booking->end_datetime)->setTimezone($request->timezone)->format('Y-m-d H:i:s');
            $booking->slot_start_time = Carbon::parse($booking->slot_start_time)->setTimezone($request->timezone)->format('H:i:s');
            $booking->slot_end_time = Carbon::parse($booking->slot_end_time)->setTimezone($request->timezone)->format('H:i:s');
        }

        if (!auth()->user()->hasRole('admin')) {
            abort_if($booking->user_id !== auth()->id(), 403, 'Unauthorized');
        }

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $pdf = new Dompdf($options);

        $pdf->loadHtml(
            view('content.booking.invoice', compact('booking'))->render()
        );

        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        $fileName = 'booking-invoice-' . $booking->booking_number . '.pdf';
        $pdfOutput = $pdf->output();

        return response($pdfOutput, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }
}