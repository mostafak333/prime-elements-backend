<?php

namespace App\Mail;

use App\Models\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminPasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Admin $admin,
        public string $token,
        public int $expirationMinutes,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Admin Password Reset Request',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin.password-reset',
        );
    }
}
