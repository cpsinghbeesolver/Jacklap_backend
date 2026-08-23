<?php

namespace App\Mail;

use App\Models\ContactUs;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactUsAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ContactUs $contact
    ) {
    }

    public function build()
    {
        return $this
            ->subject('New Contact Us Enquiry - ' . $this->contact->subject)
            ->view('emails.contact-us-admin');
    }
}