@extends('emails.layouts.master')

@section('title', 'New Contact Us Enquiry')

@section('content')

    <h2>📩 New Contact Us Enquiry</h2>

    <p>
        You have received a new enquiry through the Cosmic Vedas website.
    </p>

    <p>
        <strong>Name:</strong> {{ $contact->name }}
    </p>

    <p>
        <strong>Email:</strong> {{ $contact->email }}
    </p>

    <p>
        <strong>Subject:</strong> {{ $contact->subject }}
    </p>

    @if($contact->profile)
        <p>
            <strong>Profile:</strong> {{ $contact->profile }}
        </p>
    @endif

    <p>
        <strong>Message:</strong>
    </p>

    <p>
        {{ $contact->message }}
    </p>

    <p>
        Please review the enquiry and respond to the user accordingly.
    </p>

@endsection