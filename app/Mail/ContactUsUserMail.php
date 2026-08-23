<?php

namespace App\Mail;

use App\Models\ContactUs;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactUsUserMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ContactUs $contact
    ) {
    }

    public function build()
    {
        return $this
            ->subject('We Received Your Enquiry - Cosmic Vedas')
            ->view('emails.contact-us-user');
    }
}