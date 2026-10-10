<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(string $id, string $hash): RedirectResponse
    {
        $user = User::findOrFail($id);
        abort_unless(hash_equals(sha1($user->getEmailForVerification()), $hash), 403, 'Verification link is invalid.');

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->to($this->target($user) . '?verified=1');
    }

    private function target($user): string
    {
        return $user->isAdmin()
            ? route('admin.dashboard', absolute: false)
            : route('user.dashboard', absolute: false);
    }
}