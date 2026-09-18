<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Services\FirebaseNotificationService;
use Illuminate\Support\Facades\Log;

class CancelUnacceptedBookings extends Command
{
    protected $signature = 'bookings:cancel-unaccepted';

    protected $description = 'Cancel bookings that are not accepted within 30 minutes';

    public function handle(): int
    {
        $cutoff = now()->subMinutes(30);

        $bookings = Booking::where('status', Booking::STATUS_PENDING)
            ->where('created_at', '<=', $cutoff)
            ->get();

        foreach ($bookings as $booking) {

            // Cancel booking
            $booking->update([
                'status' => Booking::STATUS_CANCELLED,
                'cancel_reason' => 'Booking was automatically cancelled because it was not accepted within 30 minutes.',
            ]);

            // Send notification to user/seeker
            try {
                app(FirebaseNotificationService::class)
                    ->sendPushNotificationSync(
                        [$booking->user_id],
                        'Booking Cancelled',
                        'Your booking No: ' . $booking->booking_number .
                            ' was automatically cancelled because it was not accepted within 30 minutes.',
                        false,
                        'booking_cancelled',
                        [
                            'type' => 'booking_cancelled',
                            'entity' => 'booking',
                            'entity_id' => $booking->id,
                            'booking_id' => $booking->id,
                            'parent_booking_id' => $booking->parent_booking_id ?? $booking->id,
                            'booking_number' => $booking->booking_number,
                            'status' => Booking::STATUS_CANCELLED,
                        ]
                    );

            } catch (\Throwable $e) {
                Log::error('Automatic booking cancellation notification failed', [
                    'booking_id' => $booking->id,
                    'user_id' => $booking->user_id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Send socket update to user/provider
            try {
                broadcast(new \App\Events\BookingStatusUpdated(
                    $booking->id,
                    $booking->status,
                    $booking->provider_id,
                    $booking->user_id
                ));
            } catch (\Throwable $e) {
                Log::error('Automatic booking cancellation broadcast failed', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $this->info(
                "Booking {$booking->booking_number} cancelled and user notified."
            );
        }

        $this->info("Total cancelled: {$bookings->count()}");

        return self::SUCCESS;
    }
}