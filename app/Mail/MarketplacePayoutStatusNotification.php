<?php

namespace App\Mail;

use App\Models\MarketplaceWithdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MarketplacePayoutStatusNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public MarketplaceWithdrawal $withdrawal) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Marketplace payout '.$this->withdrawal->status.' — '.$this->withdrawal->order_number);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.marketplace-payout-status',
            text: 'emails.text.marketplace-payout-status',
        );
    }
}
