<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\PayoutRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProviderPayoutController extends Controller
{
    /**
     * @OA\Get(
     *     path="/provider/payout/balance",
     *     summary="Get provider's total earned, locked, and remaining withdrawable balance",
     *     security={{ "bearerAuth": {} }},
     *     tags={"Payout"},
     *     @OA\Response(
     *         response=200,
     *         description="Balance summary",
     *         @OA\JsonContent(
     *             @OA\Property(property="currency", type="string", example="usd"),
     *             @OA\Property(property="total_earned", type="number", example=500.00),
     *             @OA\Property(property="locked", type="number", example=150.00),
     *             @OA\Property(property="remaining", type="number", example=350.00)
     *         )
     *     ),
     *     @OA\Response(response=404, description="Stripe account not found")
     * )
     */
    public function balance(Request $request)
    {
        $user = $request->user();

        if (!$user->stripe_account_id) {
            return response()->json([
                'message' => 'Stripe account not found.',
            ], 404);
        }

        return response()->json($this->ledger($user->id));
    }

    /**
     * @OA\Post(
     *     path="/provider/payout/request",
     *     summary="Request a payout — full or partial amount of remaining balance",
     *     security={{ "bearerAuth": {} }},
     *     tags={"Payout"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount"},
     *             @OA\Property(
     *                 property="amount",
     *                 type="number",
     *                 example=200.00,
     *                 description="Pass the exact value of 'remaining' from /provider/payout/balance to request the full amount, or any lesser value for a partial request."
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Payout request submitted"),
     *     @OA\Response(response=422, description="Amount exceeds remaining balance, or payouts not enabled")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $userId = $request->user()->id;

        DB::beginTransaction();

        try {
            // Lock the provider row so two simultaneous requests can't both
            // pass the same "remaining balance" check at once.
            $user = User::where('id', $userId)->lockForUpdate()->first();

            if (!$user->stripe_account_id || !$user->stripe_payouts_enabled) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Stripe payouts are not enabled on your account yet.',
                ], 422);
            }

            $ledger = $this->ledger($user->id);

            if ($request->amount > $ledger['remaining']) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Requested amount exceeds your remaining balance.',
                    'remaining' => $ledger['remaining'],
                ], 422);
            }

            $payoutRequest = PayoutRequest::create([
                'provider_id'                => $user->id,
                'stripe_account_id'          => $user->stripe_account_id,
                'amount'                     => $request->amount,
                'currency'                   => $ledger['currency'],
                'available_balance_snapshot' => $ledger['total_earned'],
                'status'                     => 'pending',
            ]);

            DB::commit();

            return response()->json([
                'success'         => true,
                'message'         => 'Payout request submitted.',
                'data'            => $payoutRequest,
                'remaining_after' => round($ledger['remaining'] - $request->amount, 2),
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Payout request creation failed', [
                'user_id' => $userId,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Unable to create payout request.',
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/provider/payout/requests",
     *     summary="List the authenticated provider's own payout requests",
     *     security={{ "bearerAuth": {} }},
     *     tags={"Payout"},
     *     @OA\Response(response=200, description="Paginated list of payout requests")
     * )
     */
    public function index(Request $request)
    {
        $requests = PayoutRequest::where('provider_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data'    => $requests,
        ]);
    }

    /**
     * Total earned = net (payable_amount - platform_fee) across completed
     * bookings that were actually charged through Stripe. Locked = sum of
     * amounts sitting in requests that haven't resolved to rejected/failed.
     * Remaining = what's genuinely free to request right now.
     */
    private function ledger(int $providerId): array
    {
        $currency = strtolower(config('services.stripe.currency'));

        $totalEarned = Booking::where('provider_id', $providerId)
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