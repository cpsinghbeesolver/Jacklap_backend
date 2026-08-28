<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Stripe\Webhook;
use UnexpectedValueException;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/stripe/webhook",
     *     tags={"Stripe Webhook"},
     *     summary="Handle Stripe webhook events",
     *     description="Receives and processes Stripe webhook events.",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Webhook processed successfully"
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Invalid webhook payload or signature"
     *     )
     * )
     */
    public function handle(Request $request)
    {
        $payload = $request->getContent();

        $signature = $request->header('Stripe-Signature');

        try {

            $event = Webhook::constructEvent(
                $payload,
                $signature,
                config('services.stripe.webhook_secret')
            );

        } catch (UnexpectedValueException $e) {

            return response()->json([
                'message' => 'Invalid payload.',
            ], 400);

        } catch (SignatureVerificationException $e) {

            return response()->json([
                'message' => 'Invalid signature.',
            ], 400);
        }

        switch ($event->type) {

            /*
            |--------------------------------------------------------------------------
            | Connected Account Updated
            |--------------------------------------------------------------------------
            */

            case 'account.updated':

                $account = $event->data->object;

                User::where(
                    'stripe_account_id',
                    $account->id
                )->update([
                    'stripe_onboarding_complete' => $account->details_submitted,
                    'stripe_charges_enabled' => $account->charges_enabled,
                    'stripe_payouts_enabled' => $account->payouts_enabled,
                ]);

                break;


            /*
            |--------------------------------------------------------------------------
            | Payment Successful
            |--------------------------------------------------------------------------
            */

            case 'payment_intent.succeeded':

                $paymentIntent = $event->data->object;

                // Update your order/payment here

                break;


            /*
            |--------------------------------------------------------------------------
            | Payment Failed
            |--------------------------------------------------------------------------
            */

            case 'payment_intent.payment_failed':

                $paymentIntent = $event->data->object;

                // Update your order/payment status here

                break;


            /*
            |--------------------------------------------------------------------------
            | Charge Refunded
            |--------------------------------------------------------------------------
            */

            case 'charge.refunded':

                $charge = $event->data->object;

                // Handle refund

                break;
        }

        return response()->json([
            'message' => 'Webhook processed successfully.',
        ]);
    }
}