<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        dispatch(function () use ($user) {
            event(new Registered($user));
        })->afterResponse();

        Auth::login($user);

        $request->session()->regenerate();

        // ✅ Email not verified yet → everyone lands on the "verify your email" page.
        //    After clicking the link in the email, VerifyEmailController
        //    sends them to the right dashboard (admin → admin, user → user).
        $redirect = route('verification.notice', absolute: false);

        // ✅ AJAX register → JSON response
        if ($request->expectsJson()) {
            return response()->json([
                'ok'       => true,
                'redirect' => $redirect,
            ]);
        }

        return redirect($redirect);
    }
}