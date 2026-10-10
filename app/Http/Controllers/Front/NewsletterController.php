<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use App\Support\AdminEmailNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request): JsonResponse|RedirectResponse
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);
        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:254'],
            'consent' => ['required', 'accepted'],
            'source' => ['nullable', 'string', 'max:100'],
        ]);

        $subscriber = NewsletterSubscriber::firstOrNew(['email' => $data['email']]);
        $subscriber->fill([
            'status' => 'subscribed',
            'source' => $data['source'] ?? 'storefront',
            'consented_at' => now(),
        ])->save();

        $adminNotified = AdminEmailNotifier::send('New newsletter subscriber', [
            'Email' => $subscriber->email,
            'Source' => $subscriber->source ?: 'Storefront',
            'Subscribed at' => $subscriber->updated_at?->format('F j, Y H:i'),
        ]);
        $message = 'You are on the list. Watch your inbox for a little more beauty.'
            .($adminNotified ? '' : ' (The admin email alert could not be delivered.)');
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => $message,
                'admin_notified' => $adminNotified,
            ]);
        }

        return back()->with('success', $message);
    }
}
