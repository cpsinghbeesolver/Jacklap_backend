<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingAddonRequest extends Model
{
    protected $fillable = [
        'booking_id',
        'requested_by',
        'items',
        'total_amount',
        'status',
        'reject_reason',
    ];

    protected $casts = [
        'items' => 'array',
        'total_amount' => 'float',
    ];

    public const STATUS_PENDING  = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}