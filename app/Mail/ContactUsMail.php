<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactUsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $fullName,
        public string $email,
        public string $subjectText,
        public string $message,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Contact Us: '.$this->subjectText,
            replyTo: [new Address($this->email, $this->fullName)],
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->buildBody(),
        );
    }

    public function buildBody(): string
    {
        return 'Contact Us Message'.PHP_EOL.PHP_EOL
            .'Full name: '.$this->fullName.PHP_EOL
            .'Email: '.$this->email.PHP_EOL
            .'Subject: '.$this->subjectText.PHP_EOL.PHP_EOL
            .$this->message;
    }
}
