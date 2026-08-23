<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Payment;

class PaymentSuccessful implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $bookingId;

    public function __construct(
        $bookingId
    ) {
        $this->bookingId = $bookingId;
    }


    public function broadcastOn(): array
    {
        return [new PrivateChannel('payment-successfull.' . $this->bookingId)];
    }

    public function broadcastAs() { return 'payment.successfull'; }
}
