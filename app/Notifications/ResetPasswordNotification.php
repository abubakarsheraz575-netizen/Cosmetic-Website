<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    /**
     * 🎀 Branded teal password-reset email — Cosmetics
     */
    public function toMail($notifiable)
    {
        $url = $this->resetUrl($notifiable);

        return (new MailMessage)
            ->subject('Reset Your Password — Cosmetics')
            ->view('emails.reset-password', [
                'url' => $url,
                'user' => $notifiable,
                'expire' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60),
            ])
            ->text('emails.text.reset-password', [
                'url' => $url,
                'user' => $notifiable,
                'expire' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60),
            ]);
    }
}
