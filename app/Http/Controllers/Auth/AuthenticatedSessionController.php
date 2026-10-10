<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * 🎯 REDIRECT RULES (v3 — regenerate-proof):
     * · Intended (shop/product + #reviews) PEHLE pakda jata hai —
     *   session regenerate ke BAAD wapas save kiya jata hai
     *   (regenerate session data uda deta hai — yehi bug tha!)
     * · Intended safe hai → DONO roles wahan wapas
     * · Nahi hai → ADMIN: admin dashboard · USER: user dashboard
     */
    public function store(LoginRequest $request): RedirectResponse|JsonResponse
    {
        /* ⭐⭐⭐ STEP 1: intended ko REGENERATE SE PEHLE pakdo!
           LoginRequest::authenticate() ke andar session regenerate
           hota hai — url.intended usme ud sakta hai. */
        $intended = session('url.intended');

        $request->authenticate();

        $request->session()->regenerate();

        /* ⭐⭐⭐ STEP 2: agar regenerate ne uda diya to WAPAS daal do */
        if ($intended && !session()->has('url.intended')) {
            session(['url.intended' => $intended]);
        }

        /* ---------- ⭐ INTENDED — dono roles ke liye (safety-checked) ---------- */
        $safeIntended = false;

        if ($intended) {
            $intendedParts = parse_url($intended);
            $ipath = (string) (is_array($intendedParts) ? ($intendedParts['path'] ?? '') : '');
            $host = is_array($intendedParts) ? ($intendedParts['host'] ?? null) : null;
            $scheme = is_array($intendedParts) ? ($intendedParts['scheme'] ?? $request->getScheme()) : '';
            $port = is_array($intendedParts)
                ? ($intendedParts['port'] ?? ($scheme === 'https' ? 443 : 80))
                : null;
            $relative = str_starts_with($intended, '/') && !str_starts_with($intended, '//');
            $sameOrigin = $relative || ($host
                && strcasecmp($host, $request->getHost()) === 0
                && $scheme === $request->getScheme()
                && $port === $request->getPort());
            /* sirf shop/product URLs honor — kisi aur page pe redirect safe nahi */
            $safeIntended = $sameOrigin && ($ipath === '/shop' || str_starts_with($ipath, '/product/'));
        }

              if ($safeIntended) {
                  /* 🛍️👑 Review / shop flow — DONO roles wahan wapas */
                  session()->forget('url.intended');
                  $target = $intended;
        } elseif ($request->user()->isAdmin()) {
            /* 👑 ADMIN: koi safe intended nahi → dashboard */
            session()->forget('url.intended');
            $target = route('admin.dashboard', absolute: false);
        } else {
            /* 🛍️ USER: koi safe intended nahi → user dashboard */
            session()->forget('url.intended');
            $target = route('user.dashboard', absolute: false);
        }

        // ✅ AJAX login (galaxy page) → JSON response
        if ($request->expectsJson()) {
            return response()->json([
                'ok'       => true,
                'redirect' => $target,
            ]);
        }

        // ✅ Normal POST login
        return redirect()->to($target);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}