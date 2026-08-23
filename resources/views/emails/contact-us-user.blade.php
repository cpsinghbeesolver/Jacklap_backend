@extends('emails.layouts.master')

@section('title', 'We Received Your Enquiry')

@section('content')

    <h2>🙏 Thank You for Contacting Cosmic Vedas</h2>

    <p>
        Hi <strong>{{ $contact->name }}</strong>,
    </p>

    <p>
        Thank you for reaching out to <strong>Cosmic Vedas</strong>.
        We have successfully received your enquiry.
    </p>

    <p>
        Our team will review your message and get back to you shortly.
    </p>

    <p>
        <strong>Your Enquiry:</strong>
    </p>

    <p>
        <strong>Subject:</strong> {{ $contact->subject }}
    </p>

    <p>
        <strong>Message:</strong><br>
        {{ $contact->message }}
    </p>

    <p>
        We appreciate your interest in Cosmic Vedas and look forward to assisting you.
    </p>

    <p>
        Regards,<br>
        <strong>Cosmic Vedas Team</strong>
    </p>

@endsection