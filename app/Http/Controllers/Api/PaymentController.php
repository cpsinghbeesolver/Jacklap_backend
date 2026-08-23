<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;
use App\Events\PaymentSuccessful;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * @OA\Post(
     *     path="/booking/{id}/payment/intent",
     *     tags={"Payments"},
     *     summary="Create a Stripe PaymentIntent",
     *     description="Creates a PaymentIntent and returns client_secret.
     *     React Native uses client_secret to open Stripe Payment Sheet (no card details needed here).
     *     Next.js uses client_secret + pm_xxxx via /confirm endpoint.
     *     Webhook is the source of truth for final booking status.",
     *     operationId="createPaymentIntent",
     *     security={{ "bearerAuth": {} }},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Booking ID",
     *         @OA\Schema(type="integer", example=12)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"payment_method"},
     *             @OA\Property(
     *                 property="payment_method",
     *                 type="string",
     *                 enum={"card", "wallet", "bank_transfer"},
     *                 example="card"
     *             ),
     *             @OA\Property(
     *                 property="amount",
     *                 type="number",
     *                 format="float",
     *                 example=150.00,
     *                 description="Optional — uses booking payable_amount if omitted"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="PaymentIntent created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="payment_intent_id", type="string", example="pi_3PxABC123xyz", description="Save this for verify call"),
     *             @OA\Property(property="client_secret", type="string", example="pi_3PxABC123xyz_secret_xxx", description="React Native: pass to initPaymentSheet. Next.js: pass to confirmIntent endpoint"),
     *             @OA\Property(property="return_url", type="string", example="https://yourapp.com/payment/return?booking_id=12&signature=xxx", description="Signed URL — used for 3DS redirect"),
     *             @OA\Property(property="amount", type="number", format="float", example=150.00),
     *             @OA\Property(property="currency", type="string", example="usd"),
     *             @OA\Property(property="payment_id", type="integer", example=42)
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Booking not found"),
     *     @OA\Response(
     *         response=422,
     *         description="Validation or business rule error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment is only allowed for in-progress bookings.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Stripe error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Stripe error: Your card was declined.")
     *         )
     *     )
     * )
     */
    public function createIntent(Request $request, $id)
    {
        $request->validate([
            'payment_method' => 'required|string|in:card,wallet,bank_transfer',
            'amount'         => 'nullable|numeric|min:1',
            // ✅ No stripe_payment_method_id here
            // React Native → Stripe Sheet collects card internally
            // Next.js      → sends pm_xxxx to /confirm endpoint instead
        ]);

        $booking = Booking::with('user')->findOrFail($id);

        if ($booking->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($booking->status !== 'in_progress') {
            return response()->json([
                'message' => 'Payment is only allowed for in-progress bookings.'
            ], 422);
        }

        if ($booking->is_paid) {
            return response()->json(['message' => 'Booking is already paid.'], 422);
        }

        $amount   = $request->amount ?? $booking->payable_amount;
        $currency = config('services.stripe.currency', 'usd');

        // ✅ Signed URL — no auth session needed, works for both platforms
        $returnUrl = URL::temporarySignedRoute(
            'payment.return',
            now()->addHours(2),
            [
                'booking_id' => $booking->id,
                'user_id'    => auth()->id(),
            ]
        );

        try {
            // ✅ No confirm:true, no payment_method — just create the intent
            $intent = PaymentIntent::create([
                'amount'               => (int) round($amount * 100),
                'currency'             => $currency,
                'payment_method_types' => $this->stripeMethodTypes($request->payment_method),
               // 'return_url'           => $returnUrl,
                'metadata'             => [
                    'booking_id'     => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'user_id'        => auth()->id(),
                ],
                'description' => 'Payment for Booking #' . $booking->booking_number,
            ]);
        } catch (ApiErrorException $e) {
            return response()->json(['message' => 'Stripe error: ' . $e->getMessage()], 500);
        }

        $payment = Payment::updateOrCreate(
            ['booking_id' => $booking->id, 'status' => 'pending'],
            [
                'user_id'                  => auth()->id(),
                'stripe_payment_intent_id' => $intent->id,
                'payment_method'           => $request->payment_method,
                'amount'                   => $amount,
                'currency'                 => $currency,
                'status'                   => 'pending',
                'stripe_response'          => $intent->toArray(),
            ]
        );

        $booking->update(['payment_method' => 'stripe']);

        return response()->json([
            'payment_intent_id' => $intent->id,
            'client_secret'     => $intent->client_secret, // RN: initPaymentSheet | Next.js: /confirm
            'return_url'        => $returnUrl,
            'amount'            => $amount,
            'currency'          => $currency,
            'payment_id'        => $payment->id,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/booking/{id}/payment/confirm",
     *     tags={"Payments"},
     *     summary="Confirm PaymentIntent (Next.js only)",
     *     description="Next.js calls this after collecting card details via Stripe Elements (pm_xxxx). Confirms the PaymentIntent server-side. React Native does NOT call this — Stripe Payment Sheet handles confirmation internally. After this, poll /verify until is_paid is true.",
     *     operationId="confirmPaymentIntent",
     *     security={{ "bearerAuth": {} }},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Booking ID",
     *         @OA\Schema(type="integer", example=12)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"payment_intent_id", "stripe_payment_method_id"},
     *             @OA\Property(property="payment_intent_id", type="string", example="pi_3PxABC123xyz", description="From createIntent response"),
     *             @OA\Property(property="stripe_payment_method_id", type="string", example="pm_1OxxxxxABC", description="pm_xxxx from Stripe.js Elements")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="PaymentIntent confirmed",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={"succeeded", "requires_action"},
     *                 example="succeeded",
     *                 description="succeeded = poll verify. requires_action = call stripe.handleNextAction(client_secret) for 3DS"
     *             ),
     *             @OA\Property(
     *                 property="client_secret",
     *                 type="string",
     *                 example="pi_3PxABC123xyz_secret_xxx",
     *                 description="Pass to stripe.handleNextAction() if status is requires_action"
     *             ),
     *             @OA\Property(
     *                 property="next_action",
     *                 type="object",
     *                 nullable=true,
     *                 description="Only present when status is requires_action"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Booking or payment not found"),
     *     @OA\Response(
     *         response=500,
     *         description="Stripe error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Stripe error: ...")
     *         )
     *     )
     * )
     */
    public function confirmIntent(Request $request, $id)
    {
        $request->validate([
            'payment_intent_id'        => 'required|string',
            'stripe_payment_method_id' => 'required|string', // pm_xxxx from Stripe.js
        ]);

        $booking = Booking::findOrFail($id);

        if ($booking->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $payment = Payment::where('stripe_payment_intent_id', $request->payment_intent_id)
            ->where('booking_id', $booking->id)
            ->firstOrFail();

        try {
            $intent = PaymentIntent::retrieve($request->payment_intent_id);
            $intent->confirm([
                'payment_method' => $request->stripe_payment_method_id,
                'return_url'     => URL::temporarySignedRoute(
                    'payment.return',
                    now()->addHours(2),
                    [
                        'booking_id' => $booking->id,
                        'user_id'    => auth()->id(),
                    ]
                ),
            ]);
            $intent = PaymentIntent::retrieve($request->payment_intent_id); // fresh status
        } catch (ApiErrorException $e) {
            return response()->json(['message' => 'Stripe error: ' . $e->getMessage()], 500);
        }

        $localStatus = match ($intent->status) {
            'succeeded'                                  => 'succeeded',
            'requires_action', 'requires_source_action' => 'requires_action',
            'canceled', 'payment_failed'                 => 'failed',
            default                                      => 'pending',
        };

        $payment->update([
            'status'          => $localStatus,
            'stripe_response' => $intent->toArray(),
        ]);

        if($localStatus == 'succeeded'){
            //Trigger event
            broadcast(new PaymentSuccessful(
                $booking->id
            ));
        }

        return response()->json([
            'status'        => $intent->status,
            'client_secret' => $intent->client_secret,
            'next_action'   => $intent->next_action ?? null,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/booking/{id}/payment/verify/{paymentIntentId}",
     *     tags={"Payments"},
     *     summary="Verify and sync payment status",
     *     description="Poll this after payment sheet succeeds (React Native) or after /confirm returns succeeded/3DS completes (Next.js). Checks Stripe for latest status and syncs DB. Webhook is primary updater — this is a fallback. Poll every 2-3 seconds until is_paid is true.",
     *     operationId="verifyPayment",
     *     security={{ "bearerAuth": {} }},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Booking ID",
     *         @OA\Schema(type="integer", example=12)
     *     ),
     *
     *     @OA\Parameter(
     *         name="paymentIntentId",
     *         in="path",
     *         required=true,
     *         description="Stripe PaymentIntent ID from createIntent",
     *         @OA\Schema(type="string", example="pi_3PxABC123xyz")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Payment status",
     *         @OA\JsonContent(
     *             @OA\Property(property="stripe_status", type="string", example="succeeded", description="Raw Stripe status"),
     *             @OA\Property(property="payment_status", type="string", example="succeeded", description="Local DB status: pending | succeeded | failed | requires_action"),
     *             @OA\Property(property="is_paid", type="boolean", example=true, description="Stop polling when true")
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Booking or payment not found"),
     *     @OA\Response(
     *         response=500,
     *         description="Stripe error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Stripe error: ...")
     *         )
     *     )
     * )
     */
    public function verifyPayment(Request $request, $id, $paymentIntentId)
    {
        $booking = Booking::findOrFail($id);

        if ($booking->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $payment = Payment::where('stripe_payment_intent_id', $paymentIntentId)
            ->where('booking_id', $booking->id)
            ->firstOrFail();

        try {
            $intent = PaymentIntent::retrieve($paymentIntentId);
        } catch (ApiErrorException $e) {
            return response()->json(['message' => 'Stripe error: ' . $e->getMessage()], 500);
        }

        // Fallback: webhook may not have fired yet
        if ($intent->status === 'succeeded' && !$booking->is_paid) {
            $this->markBookingPaid($booking, $payment, $intent->toArray());
        } elseif (in_array($intent->status, ['canceled', 'payment_failed'])) {
            $this->markPaymentFailed($payment, $intent->toArray());
        }

        $booking->refresh();
        $payment->refresh();

        return response()->json([
            'stripe_status'  => $intent->status,
            'payment_status' => $payment->status,
            'is_paid'        => $booking->is_paid,
        ]);
    }

    // Webhook — Stripe calls this, no auth middleware
    public function webhook(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $intent  = $event->data->object;
        $payment = Payment::where('stripe_payment_intent_id', $intent->id)->first();

        if (!$payment) {
            return response()->json(['received' => true]);
        }

        $booking = $payment->booking;

        match ($event->type) {
            'payment_intent.succeeded'      => $this->markBookingPaid($booking, $payment, (array) $intent),
            'payment_intent.payment_failed' => $this->markPaymentFailed($payment, (array) $intent),
            default                         => null,
        };

        return response()->json(['received' => true]);
    }

    // 3DS return page — signed URL, no auth needed
    public function returnPage(Request $request)
    {
        $paymentIntentId = $request->get('payment_intent');
        $bookingId       = $request->get('booking_id');
        $userId          = $request->get('user_id');

        $booking = Booking::where('id', $bookingId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $payment = Payment::where('stripe_payment_intent_id', $paymentIntentId)
            ->where('booking_id', $booking->id)
            ->firstOrFail();

        try {
            $intent = PaymentIntent::retrieve($paymentIntentId);
        } catch (ApiErrorException $e) {
            return view('payment.return', ['status' => 'failed', 'booking' => $booking, 'isPaid' => false]);
        }

        if ($intent->status === 'succeeded' && !$booking->is_paid) {
            $this->markBookingPaid($booking, $payment, $intent->toArray());
        } elseif (in_array($intent->status, ['canceled', 'payment_failed'])) {
            $this->markPaymentFailed($payment, $intent->toArray());
        }

        $booking->refresh();

        return view('payment.return', [
            'status'  => $booking->is_paid ? 'success' : 'failed',
            'booking' => $booking,
            'isPaid'  => $booking->is_paid,
        ]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function markBookingPaid(Booking $booking, Payment $payment, array $intentData): void
    {
        $payment->update(['status' => 'succeeded', 'stripe_response' => $intentData]);
        $booking->update(['is_paid' => true, 'payment_status' => 'paid']);
    }

    private function markPaymentFailed(Payment $payment, array $intentData): void
    {
        $payment->update(['status' => 'failed', 'stripe_response' => $intentData]);
    }

    private function stripeMethodTypes(string $method): array
    {
        return match ($method) {
            'bank_transfer' => ['us_bank_account'],
            default         => ['card'],
        };
    }
}