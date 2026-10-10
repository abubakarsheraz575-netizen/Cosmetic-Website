<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        try {
            if (Schema::hasTable('settings')) {
                $host = Setting::get('mail_host');

                if ($host) {
                    $encrypted = Setting::get('mail_password');

                    config([
                        'mail.default'                 => 'smtp',
                        'mail.mailers.smtp.host'       => $host,
                        'mail.mailers.smtp.port'       => Setting::get('mail_port', 2525),
                        'mail.mailers.smtp.username'   => Setting::get('mail_username'),
                        'mail.mailers.smtp.password'   => $encrypted ? Crypt::decryptString($encrypted) : null,
                        'mail.mailers.smtp.scheme'     => Setting::get('mail_encryption') === 'ssl' ? 'smtps' : 'smtp',
                        'mail.from.address'            => Setting::get('mail_from_address', 'hello@example.com'),
                        'mail.from.name'               => Setting::get('mail_from_name', config('app.name')),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // DB not ready yet → silently fall back to .env
        }
    }
}