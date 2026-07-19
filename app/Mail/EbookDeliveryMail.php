<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EbookDeliveryMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $productName,
        public string $downloadUrl,
        public string $expiresAt,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Ebook is Ready for Download',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user.ebook-delivery',
        );
    }
}
