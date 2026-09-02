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
    public $provider_id;

    public function __construct($booking_id)
    {
        $this->booking_id  = $booking_id;
        $this->provider_id = Booking::where('id', $booking_id)->value('provider_id');
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.' . $this->provider_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'newBookingCreated';
    }

    public function broadcastWith(): array
    {
        $booking = Booking::find($this->booking_id);

        return [
            'booking_id'          => $this->booking_id,
            'booking_number'      => $booking->booking_number ?? null,
            'parent_booking_id'   => $booking->parent_booking_id ?? null,
            'service_category_id' => $booking->service_category_id ?? null,
            'slot_date'           => $booking->slot_date ?? null,
            'slot_start_time'     => $booking->slot_start_time ?? null,
            'payable_amount'      => $booking->payable_amount ?? null,
            'status'              => $booking->status ?? null,
        ];
    }
}