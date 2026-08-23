<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingExpiredReminderMail;
use Illuminate\Support\Facades\Log;

class BookingExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark expired bookings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        {
            // 15 minutes after slot_start_time - Send email
            $emailBookings = Booking::whereIn('status', ['confirmed', 'pending'])
                ->where('expired_email_sent', false)
                ->where('start_datetime', '<=', now()->subMinutes(15))
                ->where('start_datetime', '>', now()->subMinutes(30))
                ->with('provider')
                ->get();


            Log::info('Email bookings found', [
                'timezone' => config('app.timezone'),
                'count' => $emailBookings->count(),
                'bookings' => $emailBookings->toArray(),
            ]);    
            // dd($emailBookings);
            foreach ($emailBookings as $booking) {
                if($booking->provider && $booking->provider->email){
                    Mail::to($booking->provider->email)
                        ->queue(new BookingExpiredReminderMail($booking));

                    $booking->update([
                        'expired_email_sent' => true,
                    ]);
                }
            }

            // 30 minutes after slot_start_time - Expire booking
            Booking::whereIn('status', ['confirmed', 'pending'])
                //->where('expired_email_sent', true)
                ->where('start_datetime', '<=', now()->subMinutes(30))
                ->update([
                    'status' => 'expired',
                ]);

            return self::SUCCESS;
        }
    }
}
