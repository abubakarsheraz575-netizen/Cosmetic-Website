<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        return $request->user()->hasVerifiedEmail()
            ? redirect()->intended($this->target($request->user()))
            : view('auth.verify-email');
    }

    private function target($user): string
    {
        return $user->isAdmin()
            ? route('admin.dashboard', absolute: false)
            : route('user.dashboard', absolute: false);
    }
}