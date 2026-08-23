<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $otp,
        public string $purpose = 'signup'
    ) {
    }

    public function build(): static
    {
        $subjects = [
            'signup' => 'Verify Your Email Address — ' . config('app.name'),
            'resend_signup' => 'Your New Verification OTP — ' . config('app.name'),
            'forgot_password' => 'Reset Your Password OTP — ' . config('app.name'),
        ];

        return $this
            ->subject($subjects[$this->purpose] ?? 'Your OTP Code — ' . config('app.name'))
            ->view('emails.otp')
            ->with([
                'user' => $this->user,
                'otp' => $this->otp,
                'purpose' => $this->purpose,
            ]);
    }
}