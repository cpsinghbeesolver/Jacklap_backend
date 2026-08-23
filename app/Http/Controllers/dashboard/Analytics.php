<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ServiceCategory;
use App\Models\Booking;
use Carbon\Carbon;

class Analytics extends Controller
{
    public function index()
    {
        $stats = [
            'total_users'        => User::count(),
            'total_providers'    => User::role('provider')->count(),
            'total_seekers'      => User::role('seeker')->count(),
            'total_categories'   => ServiceCategory::count(),
            'total_bookings'     => Booking::childOnly()->count(),
            'pending_bookings'   => Booking::childOnly()->byStatus('pending')->count(),
            'completed_bookings' => Booking::childOnly()->byStatus('completed')->count(),
            'cancelled_bookings' => Booking::childOnly()->byStatus('cancelled')->count(),
            'total_earnings'     => Booking::childOnly()->byStatus('completed')->sum('payable_amount'),
            'pending_earnings'   => Booking::childOnly()->byStatus('pending')->sum('payable_amount'),
        ];

        // Current week (Mon–Sun) child bookings grouped by day
        $weekStart = Carbon::now()->startOfWeek(); // Monday
        $weekEnd   = Carbon::now()->endOfWeek();   // Sunday

        $weeklyBookings = Booking::childOnly()
            ->whereBetween('slot_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->selectRaw('slot_date, COUNT(*) as total')
            ->groupBy('slot_date')
            ->orderBy('slot_date')
            ->pluck('total', 'slot_date');

        // Build full 7-day series (fill 0 for days with no bookings)
        $weeklyLabels = [];
        $weeklySeries = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $weekStart->copy()->addDays($i);
            $weeklyLabels[] = $date->format('D'); // Mon, Tue...
            $weeklySeries[] = (int) ($weeklyBookings[$date->toDateString()] ?? 0);
        }

        return view('content.dashboard.dashboards-analytics', compact('stats', 'weeklyLabels', 'weeklySeries'));
    }
}