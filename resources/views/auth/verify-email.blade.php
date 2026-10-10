<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify your email — {{ config('app.name', 'Cosmetics') }}</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}?v=3">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700&family=playfair-display:500,600&display=swap" rel="stylesheet">
    <style>
        :root { color-scheme: light; --forest: #153e38; --teal: #287c70; --gold: #d6a976; --ink: #203b37; --muted: #74817d; }
        * { box-sizing: border-box }
        body {
            min-height: 100vh; margin: 0; padding: 32px 18px; display: grid; place-items: center;
            color: var(--ink); font-family: 'DM Sans', Arial, sans-serif;
            background: radial-gradient(ellipse at 15% 8%, #e5efe9 0, transparent 40%),
                radial-gradient(ellipse at 90% 90%, #f2e6d7 0, transparent 38%), #f6f4ef;
        }
        .wrap { width: min(100%, 520px); animation: arrive .55s ease both }
        @keyframes arrive { from { opacity: 0; transform: translateY(14px) } to { opacity: 1; transform: none } }
        .brand { display: flex; justify-content: center; align-items: center; gap: 11px; margin-bottom: 22px; color: var(--forest); text-decoration: none }
        .brand-mark { display: grid; place-items: center; width: 38px; height: 38px; border-radius: 13px; background: var(--gold); color: var(--forest); font: 600 24px Georgia, serif }
        .brand-name { font: 600 17px 'Playfair Display', Georgia, serif; letter-spacing: 2px; text-transform: uppercase }
        .card { overflow: hidden; border: 1px solid rgba(21, 62, 56, .08); border-radius: 22px; background: rgba(255, 255, 255, .94); box-shadow: 0 26px 75px rgba(25, 53, 47, .12) }
        .card-top { padding: 39px 42px 30px; text-align: center; background: linear-gradient(145deg, #153e38, #1d554a); color: #fff }
        .mail-icon { width: 70px; height: 70px; margin: 0 auto 22px; display: grid; place-items: center; border: 1px solid rgba(255,255,255,.24); border-radius: 22px; background: rgba(255,255,255,.1); color: #f1d8b6 }
        .mail-icon svg { width: 32px; height: 32px }
        .eyebrow { margin: 0 0 8px; color: #e2c49e; font-size: 10px; font-weight: 700; letter-spacing: 2.4px; text-transform: uppercase }
        h1 { margin: 0; font: 500 clamp(27px, 7vw, 34px)/1.2 'Playfair Display', Georgia, serif; letter-spacing: -.3px }
        .lead { max-width: 370px; margin: 13px auto 0; color: rgba(255,255,255,.77); font-size: 14px; line-height: 1.75 }
        .card-body { padding: 30px 42px 34px }
        .email-chip { display: flex; align-items: center; gap: 12px; margin: 0 0 20px; padding: 14px 16px; border: 1px solid #e6ece7; border-radius: 12px; background: #f8faf7; color: var(--forest); font-size: 14px; font-weight: 600; overflow-wrap: anywhere }
        .email-chip svg { flex: none; width: 19px; height: 19px; color: var(--teal) }
        .instructions { margin: 0; color: #63726d; font-size: 13px; line-height: 1.8 }
        .notice { margin: 18px 0 0; padding: 13px 15px; border-radius: 11px; background: #eef7f1; color: #2c7155; font-size: 13px; line-height: 1.6 }
        .notice.error { background: #fff1ee; color: #a94435 }
        .notice[hidden] { display: none }
        .actions { display: grid; gap: 11px; margin-top: 24px }
        .button { min-height: 48px; display: inline-flex; justify-content: center; align-items: center; gap: 10px; padding: 13px 18px; border: 1px solid transparent; border-radius: 10px; font: 700 11px 'DM Sans', Arial, sans-serif; letter-spacing: 1.2px; text-decoration: none; text-transform: uppercase; cursor: pointer; transition: transform .2s, box-shadow .2s, background .2s }
        .button-primary { background: var(--teal); color: #fff; box-shadow: 0 9px 20px rgba(40,124,112,.19) }
        .button-primary:hover { transform: translateY(-1px); background: #21695f; box-shadow: 0 12px 25px rgba(40,124,112,.25) }
        .button-secondary { background: #fff; border-color: #e1e8e3; color: #54645f }
        .button-secondary:hover { background: #f7f9f6 }
        .button:focus-visible { outline: 3px solid rgba(40,124,112,.26); outline-offset: 3px }
        .button:disabled { cursor: wait; opacity: .72 }
        .spinner { display: none; width: 15px; height: 15px; border: 2px solid rgba(255,255,255,.4); border-top-color: #fff; border-radius: 50%; animation: spin .7s linear infinite }
        .button.loading .spinner { display: inline-block }
        @keyframes spin { to { transform: rotate(360deg) } }
        .hint { margin: 19px 0 0; color: #8a9590; font-size: 11.5px; line-height: 1.65; text-align: center }
        .footer { margin-top: 18px; color: #8a9590; font-size: 11px; text-align: center }
        @media (max-width: 520px) { .card-top { padding: 32px 24px 26px } .card-body { padding: 25px 23px 27px } }
        @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation-duration: .01ms !important; animation-iteration-count: 1 !important; scroll-behavior: auto !important; transition-duration: .01ms !important } }
    </style>
</head>
<body>
    <main class="wrap">
        <a class="brand" href="{{ route('home') }}" aria-label="{{ config('app.name', 'Cosmetics') }} home">
            <span class="brand-mark" aria-hidden="true">C</span>
            <span class="brand-name">{{ config('app.name', 'Cosmetics') }}</span>
        </a>
        <section class="card" aria-labelledby="verification-title">
            <div class="card-top">
                <div class="mail-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="5" width="18" height="14" rx="3"></rect>
                        <path d="m4 7 8 6 8-6"></path>
                    </svg>
                </div>
                <p class="eyebrow">One last little step</p>
                <h1 id="verification-title">Check your inbox</h1>
                <p class="lead">Confirm your email to activate your account and make your next visit feel like yours.</p>
            </div>
            <div class="card-body">
                <div class="email-chip">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="5" width="18" height="14" rx="3"></rect><path d="m4 7 8 6 8-6"></path>
                    </svg>
                    <span>{{ auth()->user()->email }}</span>
                </div>
                <p class="instructions">We’ve sent a secure verification link to this address. Open the email and follow the link to finish setting up your account. If it’s not in your inbox, take a quick look in your spam folder.</p>

                <div id="verificationNotice" class="notice" role="status" aria-live="polite" @if(session('status') != 'verification-link-sent') hidden @endif>
                    @if(session('status') == 'verification-link-sent')
                        A fresh verification link has been sent to your inbox.
                    @endif
                </div>

                <div class="actions">
                    <button type="button" class="button button-primary" id="resendBtn">
                        <span class="spinner" aria-hidden="true"></span>
                        <span class="button-label">Resend verification email</span>
                    </button>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="button button-secondary" style="width:100%">Sign out</button>
                    </form>
                </div>
                <p class="hint">For your security, verification links are unique to your account and expire automatically.</p>
            </div>
        </section>
        <p class="footer">&copy; {{ date('Y') }} {{ config('app.name', 'Cosmetics') }}. Made for your everyday glow.</p>
    </main>
    <script>
        (function () {
            var button = document.getElementById('resendBtn');
            var notice = document.getElementById('verificationNotice');
            var label = button.querySelector('.button-label');

            function showNotice(message, isError) {
                notice.textContent = message;
                notice.classList.toggle('error', isError);
                notice.hidden = false;
            }

            button.addEventListener('click', async function () {
                button.disabled = true;
                button.classList.add('loading');
                label.textContent = 'Sending secure link…';

                try {
                    var response = await fetch("{{ route('verification.send') }}", {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        credentials: 'same-origin'
                    });
                    var result = await response.json();

                    if (!response.ok || !result.ok) {
                        throw new Error(result.message || 'We couldn’t send the email right now. Please try again shortly.');
                    }

                    showNotice(result.message, false);
                    if (result.redirect) {
                        window.location.assign(result.redirect);
                    }
                } catch (error) {
                    showNotice(error.message || 'We couldn’t reach the server. Please try again.', true);
                } finally {
                    button.disabled = false;
                    button.classList.remove('loading');
                    label.textContent = 'Resend verification email';
                }
            });
        })();
    </script>
</body>
</html>
