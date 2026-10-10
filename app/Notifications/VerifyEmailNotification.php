<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail
{
    /**
     * 🎀 Branded teal verification email — Cosmetics
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify Your Email — Cosmetics')
            ->view('emails.verify-email', [
                'url' => $verificationUrl,
                'user' => $notifiable,
            ])
            ->text('emails.text.verify-email', [
                'url' => $verificationUrl,
                'user' => $notifiable,
            ]);
    }
}
