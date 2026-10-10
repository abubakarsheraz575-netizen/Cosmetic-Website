<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageReply extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $customerName,
        public string $reply,
        public string $originalMessage,
        public ?string $originalSubject = null,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->originalSubject ?: 'Your message to '.config('app.name', 'Cosmetics');

        return new Envelope(
            subject: str_starts_with(strtolower($subject), 're:') ? $subject : 'Re: '.$subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-reply',
            text: 'emails.text.contact-reply',
        );
    }
}
