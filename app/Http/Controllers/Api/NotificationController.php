<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Mail\BookingCreatedMail;
use App\Mail\BookingStatusMail;
use App\Mail\OtpMail;
use App\Models\Booking;
use App\Models\User;
class NotificationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/notifications",
     *     tags={"Notifications"},
     *     summary="Get user notifications with counts",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Pagination page number",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notifications fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Notifications fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="counts",
     *                     type="object",
     *                     @OA\Property(property="total", type="integer", example=25),
     *                     @OA\Property(property="unread", type="integer", example=8),
     *                     @OA\Property(property="read", type="integer", example=17)
     *                 ),
     *                 @OA\Property(
     *                     property="notifications",
     *                     type="object",
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(property="per_page", type="integer", example=10),
     *                     @OA\Property(property="total", type="integer", example=25),
     *                     @OA\Property(
     *                         property="data",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(
     *                                 property="id",
     *                                 type="string",
     *                                 example="d5a1d4d4-1fdc-4e43-9e4d-2cb5a3bfb012"
     *                             ),
     *                             @OA\Property(
     *                                 property="type",
     *                                 type="string",
     *                                 example="App\Notifications\PushNotification"
     *                             ),
     *                             @OA\Property(
     *                                 property="data",
     *                                 type="object",
     *                                 @OA\Property(property="title", type="string", example="Booking Confirmed"),
     *                                 @OA\Property(property="body", type="string", example="Your booking has been confirmed."),
     *                                 @OA\Property(property="data", type="object")
     *                             ),
     *                             @OA\Property(
     *                                 property="read_at",
     *                                 type="string",
     *                                 format="date-time",
     *                                 nullable=true,
     *                                 example=null
     *                             ),
     *                             @OA\Property(
     *                                 property="created_at",
     *                                 type="string",
     *                                 format="date-time"
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function notificationList(Request $request)
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest()
            ->paginate(10);

        $totalCount = $user->notifications()->count();
        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'success' => true,
            'message' => 'Notifications fetched successfully',
            'counts' => [
                'total' => $totalCount,
                'unread' => $unreadCount,
                'read' => $totalCount - $unreadCount,
            ],
            'data' => $notifications
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/notifications/{notificationId}/read",
     *     tags={"Notifications"},
     *     summary="Mark a single notification as read",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="notificationId",
     *         in="path",
     *         required=true,
     *         description="Database notification UUID",
     *         @OA\Schema(
     *             type="string",
     *             example="d5a1d4d4-1fdc-4e43-9e4d-2cb5a3bfb012"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification marked as read successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Notification marked as read successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string"),
     *                 @OA\Property(property="read_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Notification not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function markAsRead(Request $request, string $notificationId)
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $notificationId)
            ->first();

        if (! $notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found.',
            ], 404);
        }

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read successfully.',
            'data' => $notification->fresh(),
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/notifications/mark-all-read",
     *     tags={"Notifications"},
     *     summary="Mark all unread notifications as read",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="All notifications marked as read successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="All notifications marked as read successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="updated_count", type="integer", example=8)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function markAllAsRead(Request $request)
    {
        $updatedCount = $request->user()
            ->unreadNotifications()
            ->update([
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read successfully.',
            'data' => [
                'updated_count' => $updatedCount,
            ],
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/notifications/{notificationId}",
     *     tags={"Notifications"},
     *     summary="Delete a single notification",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="notificationId",
     *         in="path",
     *         required=true,
     *         description="Database notification UUID",
     *         @OA\Schema(
     *             type="string",
     *             example="d5a1d4d4-1fdc-4e43-9e4d-2cb5a3bfb012"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Notification deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Notification not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function deleteNotification(Request $request, string $notificationId)
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $notificationId)
            ->first();

        if (! $notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found.',
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully.',
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/notifications",
     *     tags={"Notifications"},
     *     summary="Delete all notifications of logged-in user",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="All notifications deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="All notifications deleted successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="deleted_count", type="integer", example=25)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function deleteAllNotifications(Request $request)
    {
        $deletedCount = $request->user()
            ->notifications()
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'All notifications deleted successfully.',
            'data' => [
                'deleted_count' => $deletedCount,
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/user/test-notification",
     *     summary="Send Test Push Notification",
     *     tags={"Notifications"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_ids","title","description"},
     *             @OA\Property(
     *                 property="user_ids",
     *                 type="array",
     *                 @OA\Items(type="integer"),
     *                 example={1,2,3}
     *             ),
     *             @OA\Property(
     *                 property="title",
     *                 type="string",
     *                 example="Test Notification"
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 example="Testing For Jacklap Notifications"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification sent successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function testNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }
        $notification_type = 'test';
        try {
            $firebaseService = new \App\Services\FirebaseNotificationService();

            $firebaseService->sendPushNotificationSync(
                $request->user_ids,
                $request->title,
                $request->description,
                false,
                $notification_type
            );

            return response()->json([
                'success' => true,
                'message' => 'Notification sent successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

      // ─────────────────────────────────────────────────────────────────────────
    // OTP
    // ─────────────────────────────────────────────────────────────────────────
 
    /**
     * @OA\Post(
     *     path="/test/email/otp",
     *     tags={"Email Testing"},
     *     summary="Send a test OTP email",
     *     description="Sends the OTP email template to any email address with a custom or auto-generated OTP.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email",    type="string", format="email", example="test@example.com"),
     *             @OA\Property(property="name",     type="string", example="John Doe",
     *                          description="Recipient display name (default: Test User)"),
     *             @OA\Property(property="otp",      type="string", example="847291",
     *                          description="Custom OTP (default: random 6-digit code)")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Email sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string",  example="OTP email sent to test@example.com"),
     *             @OA\Property(property="data",    type="object",
     *                 @OA\Property(property="email", type="string", example="test@example.com"),
     *                 @OA\Property(property="otp",   type="string", example="847291")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Mail sending failed")
     * )
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'name'  => 'nullable|string|max:100',
            'otp'   => 'nullable|string|max:10',
        ]);
 
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }
 
        $otp  = $request->otp ?? str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $user = User::where('email', $request->email)->first();
 
        try {
            Mail::to($user->email)->send(new OtpMail($user, $otp));
 
            return $this->success(
                "OTP email sent to {$user->email}",
                ['email' => $user->email, 'otp' => $otp]
            );
        } catch (\Throwable $e) {
            return $this->mailError($e);
        }
    }
 
    // ─────────────────────────────────────────────────────────────────────────
    // BOOKING CREATED
    // ─────────────────────────────────────────────────────────────────────────
 
    /**
     * @OA\Post(
     *     path="/test/email/booking-created",
     *     tags={"Email Testing"},
     *     summary="Send a test Booking Created email",
     *     description="Fetches a real booking by ID (or auto-picks the latest one) and sends the booking-created email to the given address.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email",      type="string", format="email", example="test@example.com"),
     *             @OA\Property(property="name",       type="string", example="Jane Doe",
     *                          description="Override recipient name shown in the email"),
     *             @OA\Property(property="booking_id", type="integer", example=12,
     *                          description="Use a specific booking (default: latest booking in DB)")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Email sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string",  example="Booking-created email sent to test@example.com"),
     *             @OA\Property(property="data",    type="object",
     *                 @OA\Property(property="email",          type="string",  example="test@example.com"),
     *                 @OA\Property(property="booking_id",     type="integer", example=12),
     *                 @OA\Property(property="booking_number", type="string",  example="BK-20240001"),
     *                 @OA\Property(property="status",         type="string",  example="pending")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="No booking found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Mail sending failed")
     * )
     */
    public function sendBookingCreated(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'      => 'required|email',
            'name'       => 'nullable|string|max:100',
            'booking_id' => 'nullable|integer|exists:bookings,id',
        ]);
 
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }
 
        $booking = $this->resolveBooking($request->booking_id);
        if (!$booking) {
            return response()->json(['success' => false, 'message' => 'No bookings found in database.'], 404);
        }
 
        // Override the recipient so it goes to the tester, not the real user
        $booking->user->email = $request->email;
        $booking->user->name  = $request->name ?? $booking->user->name;
 
        try {
            Mail::to($request->email, $booking->user->name)
                ->send(new BookingCreatedMail($booking));
 
            return $this->success(
                "Booking-created email sent to {$request->email}",
                [
                    'email'          => $request->email,
                    'booking_id'     => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'status'         => $booking->status,
                ]
            );
        } catch (\Throwable $e) {
            return $this->mailError($e);
        }
    }
 
    // ─────────────────────────────────────────────────────────────────────────
    // BOOKING STATUS
    // ─────────────────────────────────────────────────────────────────────────
 
    /**
     * @OA\Post(
     *     path="/api/test/email/booking-status",
     *     tags={"Email Testing"},
     *     summary="Send a test Booking Status Update email",
     *     description="Sends the booking-status email for any status. Optionally override the booking's current status for preview purposes.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email",      type="string", format="email", example="test@example.com"),
     *             @OA\Property(property="name",       type="string", example="Jane Doe"),
     *             @OA\Property(property="booking_id", type="integer", example=12,
     *                          description="Booking to use (default: latest)"),
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="confirmed",
     *                 description="Override the booking status for this preview",
     *                 enum={"pending","confirmed","start_journey","in_progress","completed","cancelled"}
     *             ),
     *             @OA\Property(property="cancel_reason", type="string", example="Provider unavailable",
     *                          description="Only used when status=cancelled")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Email sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string",  example="Booking-status email (confirmed) sent to test@example.com"),
     *             @OA\Property(property="data",    type="object",
     *                 @OA\Property(property="email",          type="string",  example="test@example.com"),
     *                 @OA\Property(property="booking_id",     type="integer", example=12),
     *                 @OA\Property(property="booking_number", type="string",  example="BK-20240001"),
     *                 @OA\Property(property="status_sent",    type="string",  example="confirmed")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="No booking found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Mail sending failed")
     * )
     */
    public function sendBookingStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'         => 'required|email',
            'name'          => 'nullable|string|max:100',
            'booking_id'    => 'nullable|integer|exists:bookings,id',
            'status'        => 'nullable|in:pending,confirmed,start_journey,in_progress,completed,cancelled',
            'cancel_reason' => 'nullable|string|max:255',
        ]);
 
        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }
 
        $booking = $this->resolveBooking($request->booking_id);
        if (!$booking) {
            return response()->json(['success' => false, 'message' => 'No bookings found in database.'], 404);
        }
 
        // Apply overrides (does NOT persist to DB — only affects this email send)
        $booking->user->email = $request->email;
        $booking->user->name  = $request->name ?? $booking->user->name;
 
        if ($request->status) {
            $booking->status = $request->status;
        }
        if ($request->cancel_reason) {
            $booking->cancel_reason = $request->cancel_reason;
        }
 
        $statusSent = $booking->status;
 
        try {
            Mail::to($request->email, $booking->user->name)
                ->send(new BookingStatusMail($booking));
 
            return $this->success(
                "Booking-status email ({$statusSent}) sent to {$request->email}",
                [
                    'email'          => $request->email,
                    'booking_id'     => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'status_sent'    => $statusSent,
                ]
            );
        } catch (\Throwable $e) {
            return $this->mailError($e);
        }
    }
 
    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────
 
    /** Resolve booking by ID or fall back to the most recent one */
    private function resolveBooking(?int $id): ?Booking
    {
        $query = Booking::with(['user', 'serviceCategory', 'items']);
 
        return $id
            ? $query->find($id)
            : $query->latest()->first();
    }
 
    /** Build an in-memory fake User object (never saved to DB) */
    private function makeFakeUser(string $email, string $name): object
    {
        return (object) ['email' => $email, 'name' => $name];
    }
 
    private function success(string $message, array $data = []): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data], 200);
    }
 
    private function validationError($errors): JsonResponse
    {
        return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $errors], 422);
    }
 
    private function mailError(\Throwable $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Failed to send email.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}
