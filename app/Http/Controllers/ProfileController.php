<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information (name, email, phone, beauty prefs).
     */
    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'phone'       => ['nullable', 'string', 'max:30'],
            'skin_type'   => ['nullable', 'string', 'max:50'],
            'hair_type'   => ['nullable', 'string', 'max:50'],
            'shade_match' => ['nullable', 'string', 'max:50'],
            'loves'       => ['nullable', 'string', 'max:50'],
        ]);

        $emailChanged = $validated['email'] !== $user->email;

        $user->fill($validated);

        if ($emailChanged) {
            $user->email_verified_at = null;   // must re-verify the new email
        }

        $user->save();

        if ($request->expectsJson()) {
            return response()->json([
                'ok'            => true,
                'message'       => $emailChanged
                    ? 'Profile saved — we sent a verification link to your new email!'
                    : 'Profile updated successfully!',
                'email_changed' => $emailChanged,
                'user' => [
                    'name'        => $user->name,
                    'email'       => $user->email,
                    'phone'       => $user->phone,
                    'skin_type'   => $user->skin_type,
                    'hair_type'   => $user->hair_type,
                    'shade_match' => $user->shade_match,
                    'loves'       => $user->loves,
                    'verified'    => (bool) $user->email_verified_at,
                ],
            ]);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): JsonResponse|RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'ok'      => true,
                'message' => 'Your account has been deleted.',
                'redirect' => url('/'),
            ]);
        }

        return redirect('/');
    }
}