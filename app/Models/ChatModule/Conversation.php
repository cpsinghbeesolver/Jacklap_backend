<?php

namespace App\Models\ChatModule;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Booking;
class Conversation extends Model
{
    protected $fillable = [
        'provider_id',
        'seeker_id',
        'booking_id',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function seeker()
    {
        return $this->belongsTo(User::class, 'seeker_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }
}