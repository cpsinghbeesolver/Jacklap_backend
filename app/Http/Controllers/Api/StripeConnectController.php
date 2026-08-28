<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Stripe\StripeClient;
use Stripe\Webhook;
use UnexpectedValueException;
use Stripe\Exception\SignatureVerificationException;

class StripeConnectController extends Controller
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(
            config('services.stripe.secret')
        );
    }

    /**
         * @OA\Post(
         *     path="/v2/stripe/connect/account",
         *     tags={"Stripe Connect"},
         *     summary="Create Stripe connected account",
         *     description="Creates a Stripe Express connected account for the authenticated user.",
         *     security={{ "bearerAuth": {} }},
         *
         *     @OA\Response(
         *         response=201,
         *         description="Stripe connected account created successfully",
         *         @OA\JsonContent(
         *             @OA\Property(
         *                 property="stripe_account_id",
         *                 type="string",
         *                 example="acct_123456789"
         *             )
         *         )
         *     ),
         *
         *     @OA\Response(
         *         response=200,
         *         description="Stripe account already exists"
         *     )
         * )
         */
        
    public function createAccount(Request $request)
    {
        $user = $request->user();

        if ($user->stripe_account_id) {
            return response()->json([
                'message' => 'Stripe account already exists.',
                'stripe_account_id' => $user->stripe_account_id,
            ]);
        }

        $account = $this->stripe->accounts->create([
            'type' => 'express',
            'email' => $user->email,
        ]);

        $user->update([
            'stripe_account_id' => $account->id,
        ]);

        return response()->json([
            'message' => 'Stripe connected account created successfully.',
            'stripe_account_id' => $account->id,
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/stripe/connect/onboarding-link",
     *     tags={"Stripe Connect"},
     *     summary="Generate Stripe onboarding link",
     *     description="Creates a Stripe onboarding URL for the authenticated user's connected account.",
     *     security={{ "bearerAuth": {} }},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Onboarding link created",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="url",
     *                 type="string",
     *                 example="https://connect.stripe.com/setup/..."
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Stripe account not found"
     *     )
     * )
     */

    public function onboardingLink(Request $request)
    {
        $user = $request->user();

        if (!$user->stripe_account_id) {
            return response()->json([
                'message' => 'Stripe account not found',
            ], 404);
        }

        $stripe = new StripeClient(config('services.stripe.secret'));

        $accountLink = $stripe->accountLinks->create([
            'account' => $user->stripe_account_id,

            'refresh_url' => route('stripe.connect.refresh'),

            'return_url' => route('stripe.connect.return'),

            'type' => 'account_onboarding',
        ]);

        return response()->json([
            'url' => $accountLink->url,
        ]);
    }


     /**
     * @OA\Get(
     *     path="/stripe/connect/status",
     *     tags={"Stripe Connect"},
     *     summary="Get Stripe Connect account status",
     *     description="Returns the onboarding, charges and payouts status of the authenticated user's Stripe account.",
     *     security={{ "bearerAuth": {} }},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Stripe account status",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="stripe_account_id",
     *                 type="string",
     *                 example="acct_123456789"
     *             ),
     *             @OA\Property(
     *                 property="details_submitted",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="charges_enabled",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="payouts_enabled",
     *                 type="boolean",
     *                 example=true
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Stripe account not found"
     *     )
     * )
     */
    public function status(Request $request)
    {
        $user = $request->user();

        if (!$user->stripe_account_id) {
            return response()->json([
                'message' => 'Stripe connected account not found.',
            ], 404);
        }

        $account = $this->stripe->accounts->retrieve(
            $user->stripe_account_id,
            []
        );

        $user->update([
            'stripe_onboarding_complete' => $account->details_submitted,
            'stripe_charges_enabled' => $account->charges_enabled,
            'stripe_payouts_enabled' => $account->payouts_enabled,
        ]);

        return response()->json([
            'stripe_account_id' => $account->id,
            'details_submitted' => $account->details_submitted,
            'charges_enabled' => $account->charges_enabled,
            'payouts_enabled' => $account->payouts_enabled,
            'requirements' => $account->requirements,
        ]);
    }


    /**
     * @OA\Post(
     *     path="/stripe/connect/payment-intent",
     *     tags={"Stripe Connect"},
     *     summary="Create payment intent with platform fee",
     *     description="Creates a Stripe PaymentIntent and transfers funds to a connected account while keeping a platform fee.",
     *     security={{ "bearerAuth": {} }},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount","connected_account_id"},
     *
     *             @OA\Property(
     *                 property="amount",
     *                 type="number",
     *                 format="float",
     *                 example=100
     *             ),
     *
     *             @OA\Property(
     *                 property="connected_account_id",
     *                 type="string",
     *                 example="acct_123456789"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Payment intent created successfully"
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'connected_account_id' => 'required|string',
        ]);

        $amount = (int) ($request->amount * 100);

        // Example: 10% platform fee
        $platformFee = (int) ($amount * 0.10);

        $paymentIntent = $this->stripe->paymentIntents->create([
            'amount' => $amount,
            'currency' => 'usd',

            'automatic_payment_methods' => [
                'enabled' => true,
            ],

            'application_fee_amount' => $platformFee,

            'transfer_data' => [
                'destination' => $request->connected_account_id,
            ],
        ]);

        return response()->json([
            'message' => 'Payment intent created successfully.',

            'payment_intent_id' => $paymentIntent->id,

            'client_secret' => $paymentIntent->client_secret,

            'amount' => $request->amount,

            'platform_fee' => $platformFee / 100,
        ]);
    }


    /**
     * @OA\Get(
     *     path="/stripe/connect/refresh",
     *     tags={"Stripe Connect"},
     *     summary="Refresh Stripe onboarding link",
     *     description="Generates a new Stripe onboarding link when the previous link expires.",
     *
     *     @OA\Response(
     *         response=302,
     *         description="Redirects to a new Stripe onboarding URL"
     *     )
     * )
     */
    public function refresh(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->stripe_account_id) {
            return response()->json([
                'message' => 'Stripe account not found.',
            ], 404);
        }

        $accountLink = $this->stripe->accountLinks->create([
            'account' => $user->stripe_account_id,

            'refresh_url' => route('stripe.connect.refresh'),

            'return_url' => route('stripe.connect.return'),

            'type' => 'account_onboarding',
        ]);

        return redirect($accountLink->url);
    }


    /**
     * @OA\Get(
     *     path="/stripe/connect/return",
     *     tags={"Stripe Connect"},
     *     summary="Stripe onboarding return URL",
     *     description="Called after the user completes or exits Stripe onboarding.",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Stripe onboarding status returned successfully"
     *     )
     * )
     */

    public function return(Request $request)
    {
        $user = $request->user();

        $stripe = new StripeClient(config('services.stripe.secret'));

        $account = $stripe->accounts->retrieve(
            $user->stripe_account_id,
            []
        );

        $user->update([
            'stripe_onboarding_complete' => $account->details_submitted,
        ]);

        return response()->json([
            'details_submitted' => $account->details_submitted,
            'charges_enabled' => $account->charges_enabled,
            'payouts_enabled' => $account->payouts_enabled,
        ]);
    }


    
}