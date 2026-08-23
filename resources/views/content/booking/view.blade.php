@extends('layouts.contentNavbarLayout')
@section('title', 'View Booking')
@section('content')

<div class="card mb-4">
    <div class="justify-content-between d-flex">
        <h5 class="card-header">Booking Details</h5>
        <div class="m-4">
            <a href="{{ route('booking-list') }}" class="btn btn-sm btn-primary">
                <i class="ri-arrow-left-line me-1"></i> Go Back
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><b>Booking #:</b> {{ $booking->booking_number ?? 'N/A' }}</p>
                <p><b>Customer:</b> {{ $booking->user->name ?? 'N/A' }}</p>
                <p><b>Provider:</b> {{ $booking->provider->name ?? 'N/A' }}</p>
                <p><b>Status:</b>
                    @php
                        $colors = [
                            'pending'       => 'warning',
                            'confirmed'     => 'info',
                            'start_journey' => 'primary',
                            'in_progress'   => 'primary',
                            'completed'     => 'success',
                            'cancelled'     => 'danger',
                        ];
                        $color = $colors[$booking->status] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $color }}">{{ ucwords(str_replace('_', ' ', $booking->status)) }}</span>
                </p>
                <p><b>Payment Method:</b> {{ ucfirst($booking->payment_method ?? 'N/A') }}</p>
                <p><b>Duration Type:</b> {{ ucwords(str_replace('_', ' ', $booking->duration_type ?? 'N/A')) }}</p>
                <p><b>Is Recurring:</b> {{ $booking->is_recurring ? 'Yes' : 'No' }}</p>
            </div>
            <div class="col-md-6">
                <p><b>Start:</b> {{ $booking->start_datetime?->format('d M Y, h:i A') ?? 'N/A' }}</p>
                <p><b>End:</b> {{ $booking->end_datetime?->format('d M Y, h:i A') ?? 'N/A' }}</p>
                <p><b>Total Hours:</b> {{ $booking->total_hours ?? 'N/A' }}</p>
                <p><b>Total Amount:</b> {{ number_format($booking->total_amount, 2) }}</p>
                <p><b>Discount:</b> {{ number_format($booking->discount, 2) }}</p>
                <p><b>Tax:</b> {{ number_format($booking->tax, 2) }}</p>
                <p><b>Payable Amount:</b> <strong>{{ number_format($booking->payable_amount, 2) }}</strong></p>
            </div>
        </div>

        @if($booking->address_json)
        <hr>
        <p><b>Address:</b> {{ collect($booking->address_json)->filter()->implode(', ') }}</p>
        @endif

        @if($booking->cancel_reason)
        <p><b>Cancel Reason:</b> {{ $booking->cancel_reason }}</p>
        @endif
    </div>
</div>

{{-- Booking Items --}}
<div class="card">
    <h5 class="card-header fw-bold">Booking Items</h5>
    <div class="card-body">
        @if($booking->items->isEmpty())
            <p class="text-muted">No items found for this booking.</p>
        @else
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Service Name</th>
                        <th>Class</th>
                        <th>Type</th>
                        <th>Subject Type</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total Price</th>
                        <th>Min People</th>
                        <th>Max People</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($booking->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->service_name }}</td>
                        <td>{{ $item->class_name ?? 'N/A' }}</td>
                        <td>
                            @php
                                $typeColors = ['service' => 'primary', 'addon' => 'info', 'material' => 'warning'];
                                $typeColor  = $typeColors[$item->type] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $typeColor }}">{{ ucfirst($item->type ?? 'N/A') }}</span>
                        </td>
                        <td>{{ $item->subject_type ?? 'N/A' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->price, 2) }}</td>
                        <td>{{ number_format($item->total_price, 2) }}</td>
                        <td>{{ $item->min_people ?? 'N/A' }}</td>
                        <td>{{ $item->max_people ?? 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="7" class="text-end fw-bold">Grand Total</td>
                        <td class="fw-bold">{{ number_format($booking->items->sum('total_price'), 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
    </div>
</div>

@endsection