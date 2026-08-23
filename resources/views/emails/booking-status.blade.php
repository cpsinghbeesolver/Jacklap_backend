@extends('emails.layouts.master')

@section('title', 'Booking Update — #' . $booking->booking_number)

@section('content')

    <h2>📋 Booking Status Updated</h2>

    <p>Hi <strong>{{ $booking->user->name }}</strong>,</p>
    <p>
        There's an update on your booking
        <strong>#{{ $booking->booking_number }}</strong>.
    </p>

    {{-- ── Status highlight ── --}}
    <div style="
        text-align: center;
        margin: 24px 0;
        padding: 18px;
        background: {{ $statusBg }};
        border-radius: 8px;
        border: 1.5px solid {{ $statusBorder }};
    ">
        <div style="font-size:28px; margin-bottom:6px;">{{ $statusIcon }}</div>
        <div style="
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 2px;
            color: {{ $statusColor }};
            text-transform: uppercase;
        ">
            {{ str_replace('_', ' ', $booking->status) }}
        </div>
        <div style="font-size:13px; color:{{ $statusColor }}; margin-top:4px; opacity:0.8;">
            {{ $statusMessage }}
        </div>
    </div>

    {{-- ── Booking summary ── --}}
    <table class="detail-table">
        <tr>
            <td>Booking #</td>
            <td><strong>{{ $booking->booking_number }}</strong></td>
        </tr>
        <tr>
            <td>Category</td>
            <td>{{ $booking->serviceCategory->name ?? '—' }}</td>
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
        @endif

        <tr>
            <td>Total Hours</td>
            <td>{{ $booking->total_hours }} hrs</td>
        </tr>
        <tr>
            <td>Amount Paid</td>
            <td>₹{{ number_format($booking->payable_amount, 2) }}</td>
        </tr>
        <tr>
            <td>Payment Status</td>
            <td>{{ ucfirst($booking->payment_status ?? '—') }}</td>
        </tr>
        <tr>
            <td>Current Status</td>
            <td>
                <span class="status-badge badge-{{ $booking->status }}">
                    {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                </span>
            </td>
        </tr>
    </table>

    {{-- ── Status-specific messages ── --}}
    @if($booking->status === 'cancelled')
        <div class="alert alert-danger">
            ❌ <strong>Booking Cancelled.</strong>
            @if($booking->cancel_reason)
                <br>Reason: {{ $booking->cancel_reason }}
            @endif
            <br>If you believe this is a mistake, please contact support immediately.
        </div>

    @elseif($booking->status === 'confirmed')
        <div class="alert alert-success">
            ✅ <strong>Your booking has been confirmed by the provider.</strong>
            Please be ready at the scheduled time.
        </div>

    @elseif($booking->status === 'start_journey')
        <div class="alert alert-info">
            🚗 <strong>The provider is on their way!</strong>
            Please be available at your service address.
        </div>

    @elseif($booking->status === 'in_progress')
        <div class="alert alert-info">
            ⚙️ <strong>Your service is currently in progress.</strong>
            Thank you for your patience.
        </div>

    @elseif($booking->status === 'completed')
        <div class="alert alert-success">
            🎉 <strong>Service completed successfully!</strong>
            We hope you had a great experience. We'd love your feedback.
        </div>
    @endif
{{-- 
    <p>
        For any queries, reach us at
        <a href="mailto:support@yourcompany.com">support@yourcompany.com</a>.
    </p> --}}

@endsection