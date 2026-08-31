<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Stripe\StripeClient;

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
     * Create a Stripe v2 Account with merchant + recipient configurations
     * (replaces the v1 `type=express` account).
     */
    /**
     * @OA\Post(
     *     path="/v2/stripe/connect/account",
     *     tags={"Stripe Connect"},
     *     summary="Create Stripe Connect Account",
     *     description="Creates a Stripe Accounts v2 connected account with merchant and recipient configurations for the authenticated user.",
     *     security={{ "bearerAuth": {} }},
     *
     *     @OA\Response(
     *         response=201,
     *         description="Stripe connected account created successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Stripe connected account created successfully."
     *             ),
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
     *         description="Stripe account already exists.",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Stripe account already exists."
     *             ),
     *             @OA\Property(
     *                 property="stripe_account_id",
     *                 type="string",
     *                 example="acct_123456789"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated."
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Stripe validation error."
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

        $account = $this->stripe->v2->core->accounts->create([
            'contact_email' => $user->email,
            'display_name' => $user->name,

            'dashboard' => 'express',

            'identity' => [
                'country' => 'ca',
                'entity_type' => 'individual',
            ],

            'configuration' => [
                'merchant' => [
                    'capabilities' => [
                        'card_payments' => [
                            'requested' => true,
                        ],
                    ],
                ],

                'recipient' => [
                    'capabilities' => [
                        'stripe_balance' => [
                            'stripe_transfers' => [
                                'requested' => true,
                            ],
                        ],
                    ],
                ],
            ],

            'defaults' => [
                'currency' => config('services.stripe.currency'),

                'responsibilities' => [
                    'fees_collector' => 'application',
                    'losses_collector' => 'application',
                ],

                'locales' => ['en-US'],
            ],

            'include' => [
                'configuration.merchant',
                'configuration.recipient',
                'identity',
                'requirements',
            ],
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
     * Generate an onboarding link (Account Links v2).
     * refresh_url/return_url are SIGNED because Stripe redirects the
     * user's raw browser here — there is no Bearer token on that request.
     */
    /**
     * @OA\Post(
     *     path="/v2/stripe/connect/onboarding-link",
     *     tags={"Stripe Connect"},
     *     summary="Generate Stripe onboarding link",
     *     description="Generates a Stripe Accounts v2 onboarding link for the authenticated user's connected account.",
     *     security={{ "bearerAuth": {} }},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Onboarding link generated successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="url",
     *                 type="string",
     *                 format="uri",
     *                 example="https://connect.stripe.com/setup/..."
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Stripe account not found.",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Stripe account not found"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated."
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

        $accountLink = $this->stripe->v2->core->accountLinks->create([
            'account' => $user->stripe_account_id,
            'use_case' => [
                'type' => 'account_onboarding',
                'account_onboarding' => [
                    'configurations' => [
                        'merchant',
                        'recipient',
                    ],

                    /*
                    * IMPORTANT:
                    *
                    * Collect all requirements that are currently
                    * required, including requirements that may become
                    * required later.
                    */
                    'collection_options' => [
                        'fields' => 'eventually_due',
                        'future_requirements' => 'include',
                    ],
                    'refresh_url' => URL::temporarySignedRoute(
                        'stripe.connect.refresh',
                        now()->addHours(2),
                        ['user' => $user->id]
                    ),
                    'return_url' => URL::temporarySignedRoute(
                        'stripe.connect.return',
                        now()->addHours(2),
                        ['user' => $user->id]
                    ),
                ],
            ],
        ]);

        return response()->json([
            'url' => $accountLink->url,
        ]);
    }

    /**
     * Fetch current status directly from Stripe and sync local flags.
     */
    /**
     * @OA\Get(
     *     path="/v2/stripe/connect/status",
     *     tags={"Stripe Connect"},
     *     summary="Get Stripe Connect account status",
     *     description="Retrieves the current Stripe Accounts v2 status and synchronizes the connected account status with the authenticated user's record.",
     *     security={{ "bearerAuth": {} }},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Stripe account status.",
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
     *             ),
     *             @OA\Property(
     *                 property="requirements",
     *                 type="object",
     *                 nullable=true,
     *                 additionalProperties=true
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Stripe connected account not found.",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Stripe connected account not found."
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated."
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

        try {
            $account = $this->stripe->v2->core->accounts->retrieve(
                $user->stripe_account_id,
                [
                    'include' => [
                        'configuration.merchant',
                        'configuration.recipient',
                        'requirements',
                        'identity',
                    ],
                ]
            );

            $data = $account->toArray();

            $cardPaymentsStatus = data_get(
                $data,
                'configuration.merchant.capabilities.card_payments.status'
            );

            $stripeTransfersStatus = data_get(
                $data,
                'configuration.recipient.capabilities.stripe_balance.stripe_transfers.status'
            );

            $payoutsStatus = data_get(
                $data,
                'configuration.merchant.capabilities.stripe_balance.payouts.status'
            );

            $chargesEnabled = $cardPaymentsStatus === 'active';

            $payoutsEnabled =
                $payoutsStatus === 'active' &&
                $stripeTransfersStatus === 'active';

            $onboardingComplete =
                $chargesEnabled &&
                $payoutsEnabled;

            $user->update([
                'stripe_onboarding_complete' => $onboardingComplete,
                'stripe_charges_enabled' => $chargesEnabled,
                'stripe_payouts_enabled' => $payoutsEnabled,
            ]);

            return response()->json([
                'stripe_account_id' => $account->id,

                'onboarding_complete' => $onboardingComplete,

                'charges_enabled' => $chargesEnabled,

                'payouts_enabled' => $payoutsEnabled,

                'capabilities' => [
                    'card_payments' => $cardPaymentsStatus,
                    'stripe_transfers' => $stripeTransfersStatus,
                    'payouts' => $payoutsStatus,
                ],

                'requirements' => $account->requirements ?? null,
            ]);

        } catch (\Throwable $e) {

            \Log::error('Stripe Connect status failed', [
                'user_id' => $user->id,
                'stripe_account_id' => $user->stripe_account_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Unable to retrieve Stripe account status.',
            ], 500);
        }
    }

    /**
     * Unchanged from v1 — PaymentIntents/transfers are not part of
     * Accounts v2; the `destination` still just needs a valid account ID,
     * which v2 accounts also have (acct_...).
     */
    /**
     * @OA\Post(
     *     path="/v2/stripe/connect/payment-intent",
     *     tags={"Stripe Connect"},
     *     summary="Create payment intent for connected account",
     *     description="Creates a Stripe PaymentIntent using destination charges. A 10 percent application fee is retained by the platform and the remaining amount is transferred to the connected account.",
     *     security={{ "bearerAuth": {} }},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount","connected_account_id"},
     *             @OA\Property(
     *                 property="amount",
     *                 type="number",
     *                 format="float",
     *                 minimum=1,
     *                 example=100
     *             ),
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
     *         description="Payment intent created successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Payment intent created successfully."
     *             ),
     *             @OA\Property(
     *                 property="payment_intent_id",
     *                 type="string",
     *                 example="pi_123456789"
     *             ),
     *             @OA\Property(
     *                 property="client_secret",
     *                 type="string",
     *                 example="pi_123456789_secret_abcdef"
     *             ),
     *             @OA\Property(
     *                 property="amount",
     *                 type="number",
     *                 example=100
     *             ),
     *             @OA\Property(
     *                 property="platform_fee",
     *                 type="number",
     *                 example=10
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated."
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error.",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The amount field is required."
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 additionalProperties=true
     *             )
     *         )
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
        $platformFee = (int) ($amount * 0.10);

        $paymentIntent = $this->stripe->paymentIntents->create([
            'amount' => $amount,
            'currency' => config('services.stripe.currency'),
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
     * Hit by a raw browser redirect from Stripe when a link expired/was
     * exited early — user is identified via the signed `user` param,
     * NOT via $request->user(), since there's no auth token on this request.
     */
    /**
     * @OA\Get(
     *     path="/v2/stripe/connect/refresh",
     *     tags={"Stripe Connect"},
     *     summary="Refresh Stripe onboarding link",
     *     description="Regenerates a Stripe Accounts v2 onboarding link when the previous onboarding link has expired or is no longer valid.",
     *
     *     @OA\Parameter(
     *         name="user",
     *         in="query",
     *         required=true,
     *         description="Application user ID used to identify the Stripe connected account.",
     *         @OA\Schema(
     *             type="integer",
     *             example=123
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="expires",
     *         in="query",
     *         required=true,
     *         description="Laravel signed URL expiration timestamp.",
     *         @OA\Schema(
     *             type="integer",
     *             example=1788192000
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="signature",
     *         in="query",
     *         required=true,
     *         description="Laravel URL signature.",
     *         @OA\Schema(
     *             type="string",
     *             example="abcdef123456"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=302,
     *         description="Redirects to a newly generated Stripe onboarding URL."
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Invalid or expired signed URL."
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Stripe account not found."
     *     )
     * )
     */
    public function refresh(Request $request)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired link.');
        }

        $user = User::findOrFail($request->query('user'));

        if (!$user->stripe_account_id) {
            return response()->json(['message' => 'Stripe account not found.'], 404);
        }

        $accountLink = $this->stripe->v2->core->accountLinks->create([
            'account' => $user->stripe_account_id,
            'use_case' => [
                'type' => 'account_onboarding',
                'account_onboarding' => [
                    'configurations' => ['merchant', 'recipient'],
                    'refresh_url' => URL::temporarySignedRoute(
                        'stripe.connect.refresh',
                        now()->addHours(2),
                        ['user' => $user->id]
                    ),
                    'return_url' => URL::temporarySignedRoute(
                        'stripe.connect.return',
                        now()->addHours(2),
                        ['user' => $user->id]
                    ),
                ],
            ],
        ]);

        return redirect($accountLink->url);
    }

    /**
     * Hit by a raw browser redirect after the user completes/exits
     * onboarding. Same signed-URL identification as refresh().
     */
    /**
     * @OA\Get(
     *     path="/v2/stripe/connect/return",
     *     tags={"Stripe Connect"},
     *     summary="Handle Stripe onboarding return",
     *     description="Handles the browser redirect after the user completes or exits Stripe onboarding and redirects the user to the frontend onboarding status page.",
     *
     *     @OA\Parameter(
     *         name="user",
     *         in="query",
     *         required=true,
     *         description="Application user ID.",
     *         @OA\Schema(
     *             type="integer",
     *             example=123
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="expires",
     *         in="query",
     *         required=true,
     *         description="Laravel signed URL expiration timestamp.",
     *         @OA\Schema(
     *             type="integer",
     *             example=1788192000
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="signature",
     *         in="query",
     *         required=true,
     *         description="Laravel URL signature.",
     *         @OA\Schema(
     *             type="string",
     *             example="abcdef123456"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=302,
     *         description="Redirects to the frontend onboarding status page."
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Invalid or expired signed URL."
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="User or Stripe account not found."
     *     )
     * )
     */
    // public function return(Request $request)
    // {
    //     if (!$request->hasValidSignature()) {
    //         abort(403, 'Invalid or expired link.');
    //     }

    //     $user = User::findOrFail($request->query('user'));

    //     $account = $this->stripe->v2->core->accounts->retrieve(
    //         $user->stripe_account_id,
    //         [
    //             'include' => [
    //                 'configuration.merchant',
    //                 'configuration.recipient',
    //             ],
    //         ]
    //     );

    //     [$chargesEnabled, $payoutsEnabled, $detailsSubmitted] = $this->extractStatusFlags($account);

    //     $user->update([
    //         'stripe_onboarding_complete' => $detailsSubmitted,
    //         'stripe_charges_enabled' => $chargesEnabled,
    //         'stripe_payouts_enabled' => $payoutsEnabled,
    //     ]);

    //     // Redirect to your frontend rather than returning raw JSON to a browser.
    //     $status = ($chargesEnabled && $payoutsEnabled) ? 'complete' : 'incomplete';
    //     return redirect(config('app.frontend_url') . '/stripe/onboarding-' . $status);
    // }
    public function return(Request $request)
    {
        if (!$request->hasValidSignature()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired link.',
            ], 403);
        }

        $user = User::findOrFail($request->query('user'));

        if (!$user->stripe_account_id) {
            return response()->json([
                'success' => false,
                'message' => 'Stripe account not found.',
                'status' => 'incomplete',
                'onboarding_complete' => false,
                'charges_enabled' => false,
                'payouts_enabled' => false,
            ]);
        }

        try {
            $account = $this->stripe->v2->core->accounts->retrieve(
                $user->stripe_account_id,
                [
                    'include' => [
                        'configuration.merchant',
                        'configuration.recipient',
                        'requirements',
                        'identity',
                    ],
                ]
            );

            [$chargesEnabled, $payoutsEnabled, $detailsSubmitted] =
                $this->extractStatusFlags($account);

            $onboardingComplete =
                $chargesEnabled &&
                $payoutsEnabled;

            $user->update([
                'stripe_onboarding_complete' => $onboardingComplete,
                'stripe_charges_enabled' => $chargesEnabled,
                'stripe_payouts_enabled' => $payoutsEnabled,
            ]);

            return response()->json([
                'success' => true,
                'message' => $onboardingComplete
                    ? 'Stripe onboarding completed successfully.'
                    : 'Stripe onboarding is incomplete.',

                'status' => $onboardingComplete
                    ? 'complete'
                    : 'incomplete',

                'onboarding_complete' => $onboardingComplete,
                'charges_enabled' => $chargesEnabled,
                'payouts_enabled' => $payoutsEnabled,
                'details_submitted' => $detailsSubmitted,

                'stripe_account_id' => $user->stripe_account_id,
            ]);

        } catch (\Throwable $e) {

            \Log::error('Stripe Connect onboarding return failed', [
                'user_id' => $user->id,
                'stripe_account_id' => $user->stripe_account_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to verify Stripe onboarding status.',
                'status' => 'incomplete',
                'onboarding_complete' => false,
                'charges_enabled' => false,
                'payouts_enabled' => false,
            ], 500);
        }
    }

    /**
     * Pull charges_enabled/payouts_enabled/details_submitted out of a v2
     * Account response. VERIFY these property paths against a real dumped
     * response before relying on this in production — v2's exact nested
     * shape should be confirmed with dd($account->toArray()) once.
     */
    private function extractStatusFlags($account): array
    {
        $data = $account->toArray();

        // Merchant -> card payments
        $cardPaymentsStatus = data_get(
            $data,
            'configuration.merchant.capabilities.card_payments.status'
        );

        // Merchant -> payouts
        $payoutsStatus = data_get(
            $data,
            'configuration.merchant.capabilities.stripe_balance.payouts.status'
        );

        // Recipient -> transfers
        $stripeTransfersStatus = data_get(
            $data,
            'configuration.recipient.capabilities.stripe_balance.stripe_transfers.status'
        );

        $chargesEnabled = $cardPaymentsStatus === 'active';

        $payoutsEnabled =
            $payoutsStatus === 'active' &&
            $stripeTransfersStatus === 'active';

        $detailsSubmitted =
            $chargesEnabled &&
            $payoutsEnabled;

        return [
            (bool) $chargesEnabled,
            (bool) $payoutsEnabled,
            (bool) $detailsSubmitted,
        ];
    }
}