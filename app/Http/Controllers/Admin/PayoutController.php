<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Yajra\DataTables\Facades\DataTables;

class PayoutController extends Controller
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {

            $payouts = PayoutRequest::with('provider:id,name,email')->latest();

            return DataTables::eloquent($payouts)
                ->addIndexColumn()

                ->addColumn('provider', fn ($p) => $p->provider->name ?? 'N/A')

                ->editColumn('amount', fn ($p) => number_format($p->amount, 2) . ' ' . strtoupper($p->currency))

                ->editColumn('status', function ($p) {
                    $colors = [
                        'pending'     => 'warning',
                        'transferred' => 'info',
                        'processing'  => 'info',
                        'paid'        => 'success',
                        'rejected'    => 'danger',
                        'failed'      => 'danger',
                    ];
                    $color = $colors[$p->status] ?? 'secondary';

                    return '<span class="badge bg-'.$color.'">'.ucfirst($p->status).'</span>';
                })

                ->editColumn('created_at', fn ($p) => $p->created_at->format('d M Y, h:i A'))

                ->addColumn('actions', function ($p) {
                    $view = '<a href="'.route('view-payout', $p->id).'" class="btn btn-sm btn-outline-primary">
                                <i class="ri-eye-fill"></i>
                             </a>';

                    if ($p->status !== 'pending') {
                        return $view;
                    }

                    return $view . '
                        <button class="btn btn-sm btn-outline-success approve-payout" data-id="'.$p->id.'">
                            <i class="ri-check-line"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger reject-payout" data-id="'.$p->id.'">
                            <i class="ri-close-line"></i>
                        </button>
                    ';
                })

                ->rawColumns(['status', 'actions'])
                ->make(true);
        }

        return view('content.payout.list');
    }

    public function view($id)
    {
        $payoutRequest = PayoutRequest::with(['provider', 'processedBy'])->findOrFail($id);

        // Live remaining balance for context on the page, same ledger logic
        // used on the provider side.
        $ledger = $this->ledger($payoutRequest->provider_id);

        return view('content.payout.view', compact('payoutRequest', 'ledger'));
    }

    public function approve(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $payoutRequest = PayoutRequest::where('id', $id)->lockForUpdate()->firstOrFail();

            if ($payoutRequest->status !== 'pending') {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => "Request is already {$payoutRequest->status}.",
                ], 422);
            }

            $platformBalance = $this->stripe->balance->retrieve();
            $platformAvailable = collect($platformBalance->available)
                ->firstWhere('currency', $payoutRequest->currency);
            $platformAvailableAmount = $platformAvailable ? $platformAvailable->amount / 100 : 0;

            if ($payoutRequest->amount > $platformAvailableAmount) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Platform balance is currently insufficient to cover this transfer.',
                ], 422);
            }

            $amountCents = (int) round($payoutRequest->amount * 100);

            $transfer = $this->stripe->transfers->create(
                [
                    'amount'         => $amountCents,
                    'currency'       => $payoutRequest->currency,
                    'destination'    => $payoutRequest->stripe_account_id,
                    'transfer_group' => 'payout_request_' . $payoutRequest->id,
                ],
                ['idempotency_key' => 'transfer_payout_' . $payoutRequest->id]
            );

            $payoutRequest->update([
                'status'       => 'transferred',
                'transfer_id'  => $transfer->id,
                'processed_by' => auth()->id(),
                'processed_at' => now(),
                'admin_note'   => $request->input('note'),
            ]);

            try {
                $payout = $this->stripe->v1->payouts->create(
                    [
                        'amount'   => $amountCents,
                        'currency' => $payoutRequest->currency,
                        'metadata' => ['payout_request_id' => $payoutRequest->id],
                    ],
                    [
                        'stripe_account'  => $payoutRequest->stripe_account_id,
                        'idempotency_key' => 'bank_payout_' . $payoutRequest->id,
                    ]
                );

                $payoutRequest->update([
                    'status'           => 'processing',
                    'stripe_payout_id' => $payout->id,
                ]);

            } catch (\Throwable $payoutError) {
                Log::info('Bank payout deferred, will retry', [
                    'payout_request_id' => $payoutRequest->id,
                    'reason' => $payoutError->getMessage(),
                ]);
                // stays 'transferred' — picked up by the scheduled retry job
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Payout approved and initiated.',
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Payout approval failed', ['id' => $id, 'error' => $e->getMessage()]);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $payoutRequest = PayoutRequest::findOrFail($id);

        if ($payoutRequest->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => "Request is already {$payoutRequest->status}.",
            ], 422);
        }

        $payoutRequest->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->reason,
            'processed_by'     => auth()->id(),
            'processed_at'     => now(),
        ]);

        try {
            app(\App\Services\FirebaseNotificationService::class)->sendPushNotificationSync(
                [$payoutRequest->provider_id],
                'Payout Request Rejected',
                'Your payout request was rejected: ' . $request->reason,
                false,
                'payout_rejected',
                ['type' => 'payout_rejected', 'entity' => 'payout_request', 'entity_id' => $payoutRequest->id]
            );
        } catch (\Throwable $e) {
            Log::info('Payout rejection notification failed', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Payout request rejected.',
        ]);
    }

    private function ledger(int $providerId): array
    {
        $currency = strtolower(config('services.stripe.currency'));

        $totalEarned = \App\Models\Booking::where('provider_id', $providerId)
            ->whereNotNull('parent_booking_id')
            ->where('status', 'completed')
            ->whereIn('payment_method', ['online', 'wallet'])
            ->selectRaw('COALESCE(SUM(payable_amount - platform_fee), 0) as net')
            ->value('net');

        $locked = PayoutRequest::where('provider_id', $providerId)
            ->whereIn('status', PayoutRequest::LOCKED_STATUSES)
            ->sum('amount');

        return [
            'currency'     => $currency,
            'total_earned' => round((float) $totalEarned, 2),
            'locked'       => round((float) $locked, 2),
            'remaining'    => max(0, round($totalEarned - $locked, 2)),
        ];
    }
}