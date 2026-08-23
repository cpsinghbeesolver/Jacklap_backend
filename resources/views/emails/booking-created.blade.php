@extends('emails.layouts.master')

@section('title', 'Booking Received — #' . $booking->booking_number)

@section('content')

    <h2>🕐 Booking Received!</h2>

    <p>Hi <strong>{{ $booking->user->name }}</strong>,</p>
    <p>
        Your booking has been placed successfully and is currently
        <strong>awaiting provider acceptance</strong>.
        You'll get a confirmation email once the provider accepts.
    </p>

    {{-- ── Core booking info ── --}}
    <table class="detail-table">
        <tr>
            <td>Booking #</td>
            <td><strong>{{ $booking->booking_number }}</strong></td>
        </tr>
        <tr>
            <td>Category</td>
            <td>{{ $booking->serviceCategory->name ?? '—' }}</td>
        </tr>
        <tr>
            <td>Duration Type</td>
            <td>{{ ucfirst(str_replace('_', ' ', $booking->duration_type)) }}</td>
        </tr>

        @if($booking->isSingleDay())
            <tr>
                <td>Date</td>
                <td>{{ \Carbon\Carbon::parse($booking->slot_date)->format('D, d M Y') }}</td>
            </tr>
            <tr>
                <td>Time</td>
                <td>
                    {{ \Carbon\Carbon::parse($booking->slot_start_time)->format('h:i A') }}
                    —
                    {{ \Carbon\Carbon::parse($booking->slot_end_time)->format('h:i A') }}
                </td>
            </tr>
        @else
            <tr>
                <td>Start</td>
                <td>{{ $booking->start_datetime?->format('D, d M Y h:i A') }}</td>
            </tr>
            <tr>
                <td>End</td>
                <td>{{ $booking->end_datetime?->format('D, d M Y h:i A') }}</td>
            </tr>
            @if($booking->is_recurring)
            <tr>
                <td>Recurring</td>
                <td>
                    {{ $booking->recurring_weeks }} week(s) ·
                    {{ collect($booking->selected_days)->join(', ') }}
                </td>
            </tr>
            @endif
        @endif

        <tr>
            <td>Total Hours</td>
            <td>{{ $booking->total_hours }} hrs</td>
        </tr>
        <tr>
            <td>Payment Method</td>
            <td>{{ ucfirst($booking->payment_method ?? '—') }}</td>
        </tr>
        <tr>
            <td>Status</td>
            <td>
                <span class="status-badge badge-{{ $booking->status }}">
                    {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                </span>
            </td>
        </tr>
    </table>

    {{-- ── Address ── --}}
    @if($booking->address_json)
    <p><strong>📍 Service Address:</strong><br>
        {{ $booking->address_json['address_line'] ?? '' }},
        {{ $booking->address_json['city'] ?? '' }},
        {{ $booking->address_json['state'] ?? '' }}
        {{ $booking->address_json['zip'] ?? '' }}
    </p>
    @endif

    {{-- ── Booked items ── --}}
    @if($booking->items && $booking->items->count())
    <hr class="divider">
    <p><strong>📋 Booked Services</strong></p>

    <table class="items-table">
        <thead>
            <tr>
                <td>Service</td>
                <td>Type</td>
                <td class="text-right">Qty</td>
                <td class="text-right">Price</td>
                <td class="text-right">Total</td>
            </tr>
        </thead>
        <tbody>
            @foreach($booking->items as $item)
            <tr>
                <td>{{ $item->service_name }}</td>
                <td>{{ ucfirst($item->type ?? '—') }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">₹{{ number_format($item->price, 2) }}</td>
                <td class="text-right">₹{{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- ── Payment summary ── --}}
    <hr class="divider">
    <table class="detail-table">
        <tr>
            <td>Subtotal</td>
            <td class="text-right">₹{{ number_format($booking->total_amount, 2) }}</td>
        </tr>
        @if($booking->discount > 0)
        <tr>
            <td>Discount</td>
            <td class="text-right" style="color:#28a745;">
                − ₹{{ number_format($booking->discount, 2) }}
            </td>
        </tr>
        @endif
        @if($booking->tax > 0)
        <tr>
            <td>Tax</td>
            <td class="text-right">₹{{ number_format($booking->tax, 2) }}</td>
        </tr>
        @endif
        <tr>
            <td><strong>Amount Payable</strong></td>
            <td class="text-right">
                <strong style="color:#1B5E20; font-size:15px;">
                    ₹{{ number_format($booking->payable_amount, 2) }}
                </strong>
            </td>
        </tr>
    </table>

    <div class="alert alert-warning">
        ⏳ Your booking is <strong>pending</strong> — waiting for the provider to accept.
        You will receive an email as soon as it is confirmed.
    </div>

    {{-- <p>
        Thank you for choosing <strong>{{ config('app.name') }}</strong>.
        If you have any questions, contact us at
        <a href="mailto:support@yourcompany.com">support@yourcompany.com</a>.
    </p> --}}

@endsection