@extends('emails.layouts.master')

@section('title', 'Booking Expired Reminder — #' . $booking->booking_number)

@section('content')

    <h2>🕐 Booking Expired Reminder!</h2>

    <p>Hi <strong>{{ $booking->provider->name }}</strong>,</p>
    <p>
        This is a reminder that the booking <strong>#{{ $booking->booking_number }}</strong> is going to expire.
        Please take the necessary actions to address this booking.
    </p>

@endsection