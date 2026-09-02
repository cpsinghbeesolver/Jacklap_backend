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
            return response()->json(['message' => 'Invalid payload.'], 400);
        } catch (SignatureVerificationException $e) {
            return response()->json(['message' => 'Invalid signature.'], 400);
        }

        switch ($event->type) {

            /*
            |--------------------------------------------------------------
            | v1 Connected Account Updated (kept for backward compat
            | with any v1 accounts still in your database)
            |--------------------------------------------------------------
            */
            case 'account.updated':
                $account = $event->data->object;

                User::where('stripe_account_id', $account->id)->update([
                    'stripe_onboarding_complete' => $account->details_submitted,
                    'stripe_charges_enabled' => $account->charges_enabled,
                    'stripe_payouts_enabled' => $account->payouts_enabled,
                ]);
                break;

            /*
            |--------------------------------------------------------------
            | v2 Account Updated — fired for accounts created via
            | v2->core->accounts->create(). Payload shape differs from v1.
            |--------------------------------------------------------------
            */
            case 'v2.core.account.updated':
                $accountId = $event->data->object->id ?? null;

                if ($accountId) {
                    $this->syncV2AccountStatus($accountId);
                }
                break;

            /*
            |--------------------------------------------------------------
            | v2 capability-specific updates — fired when a specific
            | capability's status changes (e.g. card_payments approved).
            |--------------------------------------------------------------
            */
            case 'v2.core.account.including_configuration.merchant.capability_status_updated':
            case 'v2.core.account.including_configuration.recipient.capability_status_updated':
                $accountId = $event->data->object->id ?? null;

                if ($accountId) {
                    $this->syncV2AccountStatus($accountId);
                }
                break;

            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                // Update your order/payment here
                break;

            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;
                // Update your order/payment status here
                break;

            case 'charge.refunded':
                $charge = $event->data->object;
                // Handle refund
                break;
        }

        return response()->json([
            'message' => 'Webhook processed successfully.',
        ]);
    }

    /**
     * Re-fetch the full v2 account and sync local flags — safer than
     * trusting the partial payload on the event itself, since webhook
     * event payloads for v2 capability updates may only include the
     * changed field, not the full account snapshot.
     */
    private function syncV2AccountStatus(string $accountId): void
    {
        $user = User::where('stripe_account_id', $accountId)->first();

        if (!$user) {
            return;
        }

        $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

        $account = $stripe->v2->core->accounts->retrieve($accountId, [
            'include' => ['configuration.merchant', 'configuration.recipient', 'requirements'],
        ]);

        $merchant = $account->configuration->merchant ?? null;
        $recipient = $account->configuration->recipient ?? null;

        $chargesEnabled = $merchant
            && ($merchant->capabilities->card_payments->status ?? null) === 'active';

        $payoutsEnabled = $recipient
            && ($recipient->capabilities->stripe_balance->stripe_transfers->status ?? null) === 'active';

        $detailsSubmitted = empty($account->requirements->entries ?? []);

        $user->update([
            'stripe_onboarding_complete' => (bool) $detailsSubmitted,
            'stripe_charges_enabled' => (bool) $chargesEnabled,
            'stripe_payouts_enabled' => (bool) $payoutsEnabled,
        ]);
    }
}