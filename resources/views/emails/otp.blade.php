@extends('emails.layouts.master')

@php
    $mailContent = match ($purpose) {
        'forgot_password' => [
            'title' => 'Reset Your Password',
            'heading' => '🔐 Reset Your Password',
            'message' => 'We received a request to reset your password. Use the one-time password below to continue.',
            'note' => 'If you did not request a password reset, please ignore this email. Your password will remain unchanged.',
        ],
        'resend_signup' => [
            'title' => 'New Verification OTP',
            'heading' => '🔐 Verify Your Email Address',
            'message' => 'You requested a new verification code. Use the one-time password below to verify your account.',
            'note' => 'If you did not create this account or request this code, please ignore this email.',
        ],
        default => [
            'title' => 'Verify Your Email Address',
            'heading' => '🔐 Verify Your Identity',
            'message' => 'Thank you for signing up. Use the one-time password below to verify your email address and activate your account.',
            'note' => 'If you did not create this account, please ignore this email.',
        ],
    };
@endphp

@section('title', $mailContent['title'])

@section('content')
    <h2>{{ $mailContent['heading'] }}</h2>

    <p>Hi <strong>{{ $user->name ?? 'User' }}</strong>,</p>

    <p>{{ $mailContent['message'] }}</p>

    <div class="otp-box">
        <div class="otp-code">{{ $otp }}</div>
        <div class="otp-note">
            ⏱ Valid for <strong>2 minutes</strong>. Do not share this code with anyone.
        </div>
    </div>

    <div class="alert alert-warning">
        🔒 <strong>Security notice:</strong> {{ $mailContent['note'] }}
    </div>
@endsection