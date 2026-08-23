<?php

namespace App\Events;

use App\Models\Booking;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewBookingCreated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $booking_id;

    public function __construct($booking_id)
    {
        $this->booking_id = $booking_id;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('booking.' . $this->booking_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'newBookingCreated';
    }

    public function broadcastWith(): array
    {
        return [
            'booking_id' => $this->booking_id,
        ];
    }
}