<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking
    ) {}

    public function build(): static
    {
        // Eager-load what the template needs (safe to call even if already loaded)
        $this->booking->loadMissing(['user', 'serviceCategory', 'items']);

        return $this
            ->to($this->booking->user->email, $this->booking->user->name)
            ->subject("Booking Received — #{$this->booking->booking_number}")
            ->view('emails.booking-created');
    }
}