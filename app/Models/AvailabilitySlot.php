<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailabilitySlot extends Model
{
    use HasFactory;

    protected $table = 'availability_slots';

    protected $fillable = [
        'user_id',
        'day',
        'opening_time',
        'closing_time',
        'status',
    ];

    /**
     * Relationship: Each slot belongs to a seller (user)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
