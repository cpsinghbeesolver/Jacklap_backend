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
            'identity' => [
                'country' => 'us', // TODO: make dynamic per vendor if needed
                'entity_type' => 'individual',
            ],
            'configuration' => [
                'merchant' => [
                    'capabilities' => [
                        'card_payments' => ['requested' => true],
                    ],
                ],
                'recipient' => [
                    'capabilities' => [
                        'stripe_balance' => [
                            'stripe_transfers' => ['requested' => true],
                        ],
                    ],
                ],
            ],
            'defaults' => [
                'currency' => config('services.stripe.currency'),
                'responsibilities' => [
                    'fees_collector' => 'stripe',
                    'losses_collector' => 'stripe',
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

        return response()->json([
            'url' => $accountLink->url,
        ]);
    }

    /**
     * Fetch current status directly from Stripe and sync local flags.
     */
    public function status(Request $request)
    {
        $user = $request->user();

        if (!$user->stripe_account_id) {
            return response()->json([
                'message' => 'Stripe connected account not found.',
            ], 404);
        }

        $account = $this->stripe->v2->core->accounts->retrieve(
            $user->stripe_account_id,
            [
                'include' => [
                    'configuration.merchant',
                    'configuration.recipient',
                    'requirements',
                ],
            ]
        );

        [$chargesEnabled, $payoutsEnabled, $detailsSubmitted] = $this->extractStatusFlags($account);

        $user->update([
            'stripe_onboarding_complete' => $detailsSubmitted,
            'stripe_charges_enabled' => $chargesEnabled,
            'stripe_payouts_enabled' => $payoutsEnabled,
        ]);

        return response()->json([
            'stripe_account_id' => $account->id,
            'details_submitted' => $detailsSubmitted,
            'charges_enabled' => $chargesEnabled,
            'payouts_enabled' => $payoutsEnabled,
            'requirements' => $account->requirements ?? null,
        ]);
    }

    /**
     * Unchanged from v1 — PaymentIntents/transfers are not part of
     * Accounts v2; the `destination` still just needs a valid account ID,
     * which v2 accounts also have (acct_...).
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
    public function return(Request $request)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired link.');
        }

        $user = User::findOrFail($request->query('user'));

        $account = $this->stripe->v2->core->accounts->retrieve(
            $user->stripe_account_id,
            [
                'include' => [
                    'configuration.merchant',
                    'configuration.recipient',
                ],
            ]
        );

        [$chargesEnabled, $payoutsEnabled, $detailsSubmitted] = $this->extractStatusFlags($account);

        $user->update([
            'stripe_onboarding_complete' => $detailsSubmitted,
            'stripe_charges_enabled' => $chargesEnabled,
            'stripe_payouts_enabled' => $payoutsEnabled,
        ]);

        // Redirect to your frontend rather than returning raw JSON to a browser.
        $status = ($chargesEnabled && $payoutsEnabled) ? 'complete' : 'incomplete';
        return redirect(config('app.frontend_url') . '/stripe/onboarding-' . $status);
    }

    /**
     * Pull charges_enabled/payouts_enabled/details_submitted out of a v2
     * Account response. VERIFY these property paths against a real dumped
     * response before relying on this in production — v2's exact nested
     * shape should be confirmed with dd($account->toArray()) once.
     */
    private function extractStatusFlags($account): array
    {
        $merchant = $account->configuration->merchant ?? null;
        $recipient = $account->configuration->recipient ?? null;

        $chargesEnabled = $merchant
            && ($merchant->capabilities->card_payments->status ?? null) === 'active';

        $payoutsEnabled = $recipient
            && ($recipient->capabilities->stripe_balance->stripe_transfers->status ?? null) === 'active';

        // v2 doesn't have a single top-level details_submitted flag the same
        // way v1 did — approximate it as "no outstanding requirements".
        $detailsSubmitted = empty($account->requirements->entries ?? []);

        return [(bool) $chargesEnabled, (bool) $payoutsEnabled, (bool) $detailsSubmitted];
    }
}