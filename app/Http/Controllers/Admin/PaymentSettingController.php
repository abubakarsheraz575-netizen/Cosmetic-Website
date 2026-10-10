<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\PaystackSecretKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class PaymentSettingController extends Controller
{
    /* ---------- helpers ---------- */

    private function mask(string $plain): string
    {
        $plain = trim($plain);
        return substr($plain, 0, 8) . str_repeat('•', 6) . substr($plain, -4);
    }

    private function isActive(?string $pub, ?string $encSecret): bool
    {
        return !empty($pub) && !empty($encSecret);
    }

    /* ---------- PAGE ---------- */
    public function index()
    {
        $stripeKey      = Setting::get('stripe_key') ?? '';
        $stripeMask     = Setting::get('stripe_mask') ?? '';
        $stripeEnc      = Setting::get('stripe_secret') ?? '';
        $stripeActive   = $this->isActive($stripeKey, $stripeEnc);

        $paystackKey    = Setting::get('paystack_key') ?? '';
        $paystackMask   = Setting::get('paystack_mask') ?? '';
        $paystackEnc    = Setting::get('paystack_secret') ?? '';
        $paystackActive = $this->isActive($paystackKey, $paystackEnc);

        $paystackCurrency = Setting::get('paystack_currency') ?? 'AUTO';
        $paystackRate     = Setting::get('paystack_rate') ?? '';

        return view('admin.pages.payment-settings', compact(
            'stripeKey', 'stripeMask', 'stripeActive',
            'paystackKey', 'paystackMask', 'paystackActive',
            'paystackCurrency', 'paystackRate'
        ));
    }

    /* ---------- SAVE (PATCH spoof) ---------- */
    public function update(Request $request)
    {
        $request->validate([
            'stripe_key'        => 'nullable|string|max:255',
            'stripe_secret'     => 'nullable|string|max:255',
            'paystack_key'      => 'nullable|string|max:255',
            'paystack_secret'   => 'nullable|string|max:255',
           'paystack_currency' => 'nullable|in:AUTO,NGN,GHS,ZAR,KES,USD',
            'paystack_rate'     => 'nullable|numeric|min:0.01|max:1000000',
        ]);

        if ($request->filled('paystack_key') || $request->filled('paystack_secret')) {
            $publicKey = $request->filled('paystack_key')
                ? trim($request->input('paystack_key'))
                : trim((string) Setting::get('paystack_key'));
            $secretKey = $request->filled('paystack_secret')
                ? PaystackSecretKey::normalize($request->input('paystack_secret'))
                : $this->storedPaystackSecret();

            $publicMode = PaystackSecretKey::mode($publicKey, 'pk');
            $secretMode = PaystackSecretKey::mode($secretKey, 'sk');
            if (! $publicMode) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'paystack_key' => ['Enter a valid Paystack public key beginning with pk_test_ or pk_live_.'],
                ]);
            }
            if (! $secretMode) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'paystack_secret' => ['Enter a valid Paystack secret key beginning with sk_test_ or sk_live_.'],
                ]);
            }
            if ($publicMode !== $secretMode) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'paystack_secret' => ['The Paystack public and secret keys must both be from the same test or live mode.'],
                ]);
            }
        }

        /* ---------- STRIPE ---------- */
        if ($request->filled('stripe_key')) {
            Setting::set('stripe_key', trim($request->input('stripe_key')));
        }
        if ($request->filled('stripe_secret')) {
            $plain = trim($request->input('stripe_secret'));
            Setting::set('stripe_secret', Crypt::encryptString($plain));
            Setting::set('stripe_mask', $this->mask($plain));
        }

        /* ---------- PAYSTACK ---------- */
        if ($request->filled('paystack_key')) {
            Setting::set('paystack_key', trim($request->input('paystack_key')));
        }
        if ($request->filled('paystack_secret')) {
            $plain = PaystackSecretKey::normalize($request->input('paystack_secret'));
            if (! $plain) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'paystack_secret' => ['Enter the Paystack secret key after “Bearer”.'],
                ]);
            }
            Setting::set('paystack_secret', Crypt::encryptString($plain));
            Setting::set('paystack_mask', $this->mask($plain));
        }

        /* ⭐ CURRENCY + RATE (GBP Paystack support nahi karta) */
        if ($request->filled('paystack_currency')) {
            Setting::set('paystack_currency', $request->input('paystack_currency'));
        }
        if ($request->filled('paystack_rate')) {
            Setting::set('paystack_rate', (string) (float) $request->input('paystack_rate'));
        }

        return response()->json([
            'status'         => 'success',
            'message'        => 'Payment settings saved & encrypted 🔐',
            'stripeActive'   => $this->isActive(Setting::get('stripe_key'), Setting::get('stripe_secret')),
            'stripeMask'     => Setting::get('stripe_mask'),
            'paystackActive' => $this->isActive(Setting::get('paystack_key'), Setting::get('paystack_secret')),
            'paystackMask'   => Setting::get('paystack_mask'),
        ]);
    }

    private function storedPaystackSecret(): ?string
    {
        $secret = Setting::get('paystack_secret');
        if (! $secret) {
            return null;
        }

        try {
            $secret = Crypt::decryptString($secret);
        } catch (\Throwable) {
            // Legacy settings may contain an unencrypted key.
        }

        return PaystackSecretKey::normalize($secret);
    }
}