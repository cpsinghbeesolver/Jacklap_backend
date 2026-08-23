<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    // Exposed to the Blade view
    public string $statusColor;
    public string $statusBg;
    public string $statusBorder;
    public string $statusIcon;
    public string $statusMessage;

    public function __construct(
        public Booking $booking
    ) {
        $this->resolveStatusStyle($booking->status);
    }

    public function build(): static
    {
        $this->booking->loadMissing(['user', 'serviceCategory']);

        $label = ucfirst(str_replace('_', ' ', $this->booking->status));

        return $this
            ->to($this->booking->user->email, $this->booking->user->name)
            ->subject("Booking {$label} — #{$this->booking->booking_number}")
            ->view('emails.booking-status');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Map each Booking::STATUS_* constant to colours + copy
    // ─────────────────────────────────────────────────────────────────────────

    private function resolveStatusStyle(string $status): void
    {
        $map = [
            Booking::STATUS_PENDING => [
                'color'   => '#856404',
                'bg'      => '#fff9e6',
                'border'  => '#ffc107',
                'icon'    => '⏳',
                'message' => 'Your booking is awaiting provider confirmation.',
            ],
            Booking::STATUS_CONFIRMED => [
                'color'   => '#0c5460',
                'bg'      => '#e8f6f8',
                'border'  => '#17a2b8',
                'icon'    => '✅',
                'message' => 'Your booking has been confirmed by the provider.',
            ],
            Booking::STATUS_START_JOURNEY => [
                'color'   => '#1a5276',
                'bg'      => '#ebf5fb',
                'border'  => '#2980b9',
                'icon'    => '🚗',
                'message' => 'The provider is heading to your location.',
            ],
            Booking::STATUS_IN_PROGRESS => [
                'color'   => '#004085',
                'bg'      => '#e8f5e9',
                'border'  => '#1B5E20',
                'icon'    => '⚙️',
                'message' => 'Your service is currently being carried out.',
            ],
            Booking::STATUS_COMPLETED => [
                'color'   => '#155724',
                'bg'      => '#d4edda',
                'border'  => '#28a745',
                'icon'    => '🎉',
                'message' => 'Your service has been completed successfully!',
            ],
            Booking::STATUS_CANCELLED => [
                'color'   => '#721c24',
                'bg'      => '#f8d7da',
                'border'  => '#dc3545',
                'icon'    => '❌',
                'message' => 'Your booking has been cancelled.',
            ],
        ];

        $style = $map[$status] ?? [
            'color'   => '#333333',
            'bg'      => '#f4f4f4',
            'border'  => '#cccccc',
            'icon'    => 'ℹ️',
            'message' => 'Your booking status has been updated.',
        ];

        $this->statusColor   = $style['color'];
        $this->statusBg      = $style['bg'];
        $this->statusBorder  = $style['border'];
        $this->statusIcon    = $style['icon'];
        $this->statusMessage = $style['message'];
    }
}