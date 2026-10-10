<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => true,
                    'message' => 'Your email address is already verified.',
                    'redirect' => $this->target($request->user()),
                ]);
            }

            return redirect()->intended($this->target($request->user()));
        }

        $request->user()->sendEmailVerificationNotification();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'A new verification link has been sent to your email address.',
            ]);
        }

        return back()->with('status', 'verification-link-sent');
    }

    private function target($user): string
    {
        return $user->isAdmin()
            ? route('admin.dashboard', absolute: false)
            : route('user.dashboard', absolute: false);
    }
}
