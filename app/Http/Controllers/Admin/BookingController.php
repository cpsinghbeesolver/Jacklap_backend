<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use Yajra\DataTables\Facades\DataTables;
use Dompdf\Dompdf;
class BookingController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $bookings = Booking::with(['user', 'provider'])
                ->parentOnly()
                ->orderBy('id', 'DESC');

            return DataTables::eloquent($bookings)
                ->addIndexColumn()

                ->addColumn('customer', function ($booking) {
                    return $booking->user->name ?? 'N/A';
                })

                ->addColumn('provider', function ($booking) {
                    return $booking->provider->name ?? 'N/A';
                })

                ->editColumn('status', function ($booking) {
                    $colors = [
                        'pending'        => 'warning',
                        'confirmed'      => 'info',
                        'start_journey'  => 'primary',
                        'in_progress'    => 'primary',
                        'completed'      => 'success',
                        'cancelled'      => 'danger',
                    ];
                    $color = $colors[$booking->status] ?? 'secondary';
                    return '<span class="badge bg-' . $color . '">' . ucwords(str_replace('_', ' ', $booking->status)) . '</span>';
                })

                ->editColumn('payable_amount', function ($booking) {
                    return number_format($booking->payable_amount, 2);
                })

                ->editColumn('start_datetime', function ($booking) {
                    return $booking->start_datetime?->format('d M Y, h:i A') ?? 'N/A';
                })

                ->addColumn('actions', function ($booking) {
                    return '
                        <a href="' . route('view-booking', $booking->id) . '" class="btn btn-sm btn-outline-primary">
                            <i class="ri-eye-fill"></i>
                        </a>
                        <a href="' . route('booking-invoice', $booking->id) . '" class="btn btn-sm btn-outline-secondary" title="Invoice" target="_blank">
                            <i class="ri-file-pdf-line"></i>
                        </a>
                        <button class="btn btn-sm btn-outline-danger delete-booking"
                            data-id="' . $booking->id . '">
                            <i class="ri-delete-bin-6-line"></i>
                        </button>
                    ';
                })

                ->rawColumns(['status', 'actions'])
                ->make(true);
        }

        return view('content.booking.list');
    }

    public function view($id)
    {
        $booking = Booking::with(['user', 'provider', 'items.service'])->findOrFail($id);

        return view('content.booking.view', compact('booking'));
    }

    public function delete(Request $request)
    {
        $booking = Booking::findOrFail($request->id);
        $booking->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Booking deleted successfully',
        ]);
    }

    /**
     * Invoice — works for both admin panel and API (mobile app).
     * Admin : /admin/booking/{id}/invoice  (auth:sanctum or web, has admin role)
     * App   : /api/booking/{id}/invoice    (auth:sanctum, owns the booking)
     */
    public function downloadInvoice($id)
    {
        $booking = Booking::with(['user', 'provider', 'items'])->findOrFail($id);
 
        // App users can only download their own booking invoice
        if (!auth()->user()->hasRole('admin')) {
            abort_if($booking->user_id !== auth()->id(), 403, 'Unauthorized');
        }
 
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
 
        $pdf = new Dompdf($options);
        $pdf->loadHtml(
            view('content.booking.invoice', compact('booking'))->render()
        );
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();
 
        return $pdf->stream('booking-invoice-' . $booking->booking_number . '.pdf');
    }
}