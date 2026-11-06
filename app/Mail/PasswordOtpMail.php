<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PasswordOtpMail extends Mailable
{
    public function __construct(
        public string $name,
        public string $code,
        public int $ttlMinutes,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Kode OTP reset password RAPI-Sekolah');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.password-otp');
    }
}
