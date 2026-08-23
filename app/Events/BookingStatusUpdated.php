<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $bookingId;
    public $status;
    public $providerId;
    public $customerId;

    public function __construct(
        $bookingId,
        $status,
        $providerId,
        $customerId
    ) {
        $this->bookingId = $bookingId;
        $this->status = $status;
        $this->providerId = $providerId;
        $this->customerId = $customerId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('booking-status.' . $this->customerId),
            new PrivateChannel('booking-status.' . $this->providerId)
        ];
    }

    public function broadcastAs(): string
    {
        return 'booking.status.updated';
    }
}