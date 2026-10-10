<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminActivityNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $activityTitle,
        public array $details,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->activityTitle);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-activity',
            text: 'emails.text.admin-activity',
        );
    }
}
