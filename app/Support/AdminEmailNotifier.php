<?php

namespace App\Support;

use App\Mail\AdminActivityNotification;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminEmailNotifier
{
    public static function recipientAddress(): ?string
    {
        $email = Setting::get('admin_email')
            ?: User::query()->where('role', 'admin')->orderBy('id')->value('email')
            ?: Setting::get('site_email')
            ?: config('mail.from.address');

        return is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL)
            ? $email
            : null;
    }

    public static function send(string $subject, array $details): bool
    {
        $recipient = self::recipientAddress();
        if (! $recipient) {
            Log::error('Admin activity email could not be sent because no valid admin email address is configured.', [
                'subject' => $subject,
            ]);

            return false;
        }

        try {
            Mail::to($recipient)->send(new AdminActivityNotification($subject, $details));

            return true;
        } catch (\Throwable $exception) {
            report($exception);

            return false;
        }
    }
}
