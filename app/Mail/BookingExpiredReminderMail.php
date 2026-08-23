<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Booking;

class BookingExpiredReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Booking $booking
    ) {}

    public function build(): static
    {
        // Eager-load what the template needs (safe to call even if already loaded)
        $this->booking->loadMissing(['provider']);
        return $this
            ->to($this->booking->provider->email, $this->booking->provider->name)
            ->subject("Booking Expiry Reminder — #{$this->booking->booking_number}")
            ->view('emails.booking-expiry-reminder');
    }
}
