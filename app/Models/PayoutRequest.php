<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayoutRequest extends Model
{
    protected $fillable = [
        'provider_id', 'stripe_account_id', 'amount', 'currency',
        'available_balance_snapshot', 'status', 'transfer_id',
        'stripe_payout_id', 'admin_note', 'rejection_reason',
        'processed_by', 'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'amount' => 'decimal:2',
        'available_balance_snapshot' => 'decimal:2',
    ];

    // Statuses where the amount is still "locked" against the provider's balance.
    const LOCKED_STATUSES = ['pending', 'transferred', 'processing', 'paid'];

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}