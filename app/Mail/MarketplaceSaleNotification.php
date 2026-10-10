<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MarketplaceSaleNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $vendorName,
        public string $orderNumber,
        public array $items,
        public string $payoutUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your marketplace product sold — order '.$this->orderNumber);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.marketplace-sale',
            text: 'emails.text.marketplace-sale',
        );
    }
}
