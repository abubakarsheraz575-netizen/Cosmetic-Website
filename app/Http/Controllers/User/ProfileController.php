<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /* ── Edit Profile: name + email + phone ── */
    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $emailChanged = $data['email'] !== $user->email;

        $user->name  = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'] ?? null;

        if ($emailChanged) {
            $user->email_verified_at = null;   // new email must be re-verified
        }

        $user->save();

        if (! $request->expectsJson()) {
            return redirect()->route('user.profile')->with('status', 'profile-updated');
        }

        return response()->json([
            'ok'      => true,
            'message' => $emailChanged
                ? 'Profile saved! Please verify your new email.'
                : 'Profile updated successfully!',
            'user' => [
                'name'     => $user->name,
                'email'    => $user->email,
                'phone'    => $user->phone,
                'verified' => (bool) $user->email_verified_at,
            ],
        ]);
    }

    /* ── Beauty Preferences ── */
    public function updatePrefs(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'skin_type'   => ['nullable', 'string', 'max:50'],
            'hair_type'   => ['nullable', 'string', 'max:50'],
            'shade_match' => ['nullable', 'string', 'max:50'],
            'loves'       => ['nullable', 'string', 'max:50'],
        ]);

        $user = $request->user();
        $user->fill($data)->save();

        if (! $request->expectsJson()) {
            return redirect()->route('user.profile')->with('status', 'preferences-updated');
        }

        return response()->json([
            'ok'      => true,
            'message' => 'Beauty profile saved!',
            'user' => [
                'skin_type'   => $user->skin_type,
                'hair_type'   => $user->hair_type,
                'shade_match' => $user->shade_match,
                'loves'       => $user->loves,
            ],
        ]);
    }

    /* ── Change Password ── */
    public function updatePassword(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($data['password']),
        ]);

        if (! $request->expectsJson()) {
            return redirect()->route('user.profile')->with('status', 'password-updated');
        }

        return response()->json([
            'ok'      => true,
            'message' => 'Password updated successfully!',
        ]);
    }
}