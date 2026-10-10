<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'App') }} — Create Account</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">

    <style id="pageStyles">
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box
        }

        html,
        body {
            height: 100%;
            overflow: hidden;
            font-family: 'Figtree', ui-sans-serif, system-ui, sans-serif;
            background: radial-gradient(ellipse at 50% 42%, #fbf9f4 0%, #f3eee4 55%, #e8f1f1 100%);
            color: #16233c
        }

        ::selection {
            background: rgba(56, 170, 180, .3)
        }

        #galaxy {
            position: fixed;
            inset: 0;
            z-index: 1
        }

        .vignette {
            position: fixed;
            inset: 0;
            z-index: 2;
            pointer-events: none;
            background: radial-gradient(ellipse at center, transparent 45%, rgba(22, 35, 60, .16) 100%)
        }

        .stage {
            position: fixed;
            inset: 0;
            z-index: 3;
            display: flex;
            overflow-y: auto;
            padding: 32px 20px
        }

        .panel {
            position: relative;
            margin: auto;
            width: min(370px, 100%);
            padding: 30px 34px 24px;
            border-radius: 14px;
            background: rgba(255, 255, 255, .66);
            border: 1px solid rgba(255, 255, 255, .65);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            box-shadow: 0 30px 70px rgba(31, 51, 89, .16), inset 0 1px 0 rgba(255, 255, 255, .8);
            animation: panelIn .9s cubic-bezier(.2, .9, .3, 1) both .15s
        }

        @keyframes panelIn {
            from {
                opacity: 0;
                transform: translateY(26px) scale(.96);
                filter: blur(6px)
            }

            to {
                opacity: 1;
                transform: none;
                filter: none
            }
        }

        @property --a {
            syntax: '<angle>';
            initial-value: 0deg;
            inherits: false
        }

        .panel::before {
            content: '';
            position: absolute;
            inset: -1px;
            border-radius: 15px;
            padding: 1px;
            pointer-events: none;
            background: conic-gradient(from var(--a), rgba(56, 170, 180, 0) 0deg, rgba(56, 170, 180, .6) 90deg, rgba(125, 219, 226, .6) 180deg, rgba(56, 170, 180, 0) 300deg);
            -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            animation: borderSpin 6s linear infinite
        }

        @keyframes borderSpin {
            to {
                --a: 360deg
            }
        }

        .logo {
            width: 50px;
            height: 50px;
            margin: 0 auto 12px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #38aab4, #2b7fb8);
            border: 1px solid rgba(255, 255, 255, .4);
            box-shadow: 0 10px 26px rgba(56, 170, 180, .4), inset 0 0 0 1px rgba(255, 255, 255, .2);
            animation: logoFloat 5s ease-in-out infinite
        }

        @keyframes logoFloat {

            0%,
            100% {
                transform: translateY(0)
            }

            50% {
                transform: translateY(-6px)
            }
        }

        .core {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background: radial-gradient(circle at 35% 35%, #fff, #eafcff 55%, #bdeef2);
            box-shadow: 0 0 16px rgba(255, 255, 255, .95), 0 0 34px rgba(255, 255, 255, .5);
            animation: corePulse 2.4s ease-in-out infinite
        }

        @keyframes corePulse {

            0%,
            100% {
                transform: scale(1)
            }

            50% {
                transform: scale(.82);
                opacity: .75
            }
        }

        .brand {
            text-align: center;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: .5px;
            background: linear-gradient(90deg, #2b7fb8, #38aab4, #5cc9d2, #2b7fb8);
            background-size: 220% auto;
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: shimmer 4.5s linear infinite
        }

        @keyframes shimmer {
            to {
                background-position: 220% center
            }
        }

        .heading {
            text-align: center;
            font-size: 11px;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: rgba(22, 35, 60, .45);
            margin: 5px 0 2px
        }

        .alert {
            margin-top: 13px;
            padding: 10px 13px;
            border-radius: 9px;
            background: rgba(224, 86, 110, .08);
            border: 1px solid rgba(224, 86, 110, .45);
            animation: shake .5s cubic-bezier(.36, .07, .19, .97)
        }

        .alert p {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            color: #d94560;
            font-size: 12.5px;
            line-height: 1.45
        }

        .alert.ok {
            background: rgba(46, 193, 129, .08);
            border: 1px solid rgba(46, 193, 129, .45);
            animation: panelIn .5s cubic-bezier(.2, .9, .3, 1) both
        }

        .alert.ok p {
            color: #1f9d63
        }

        .alert .ico {
            flex-shrink: 0
        }

        @keyframes shake {

            10%,
            90% {
                transform: translateX(-1px)
            }

            20%,
            80% {
                transform: translateX(2px)
            }

            30%,
            50%,
            70% {
                transform: translateX(-3px)
            }

            40%,
            60% {
                transform: translateX(3px)
            }
        }

        .field {
            margin-top: 15px;
            animation: fieldIn .7s cubic-bezier(.2, .9, .3, 1) both
        }

        .d1 {
            animation-delay: .28s
        }

        .d2 {
            animation-delay: .38s
        }

        .d3 {
            animation-delay: .48s
        }

        .d4 {
            animation-delay: .58s
        }

        .d5 {
            animation-delay: .68s
        }

        @keyframes fieldIn {
            from {
                opacity: 0;
                transform: translateY(14px)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        .field label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            font-weight: 600;
            color: rgba(22, 35, 60, .6);
            margin-bottom: 2px;
            letter-spacing: .3px
        }

        .caps {
            display: none;
            align-items: center;
            gap: 5px;
            color: #b5831f;
            font-size: 10.5px;
            font-weight: 700
        }

        .caps.show {
            display: inline-flex;
            animation: blink 1.2s ease-in-out infinite
        }

        @keyframes blink {
            50% {
                opacity: .45
            }
        }

        .match-tag {
            font-size: 10.5px;
            font-weight: 800;
            letter-spacing: .3px;
            opacity: 0;
            transform: translateY(3px);
            transition: .3s
        }

        .match-tag.ok {
            opacity: 1;
            transform: none;
            color: #1f9d63
        }

        .match-tag.no {
            opacity: 1;
            transform: none;
            color: #d94560
        }

        .input-wrap {
            position: relative;
            display: flex;
            align-items: center
        }

        .input-wrap .ic {
            position: absolute;
            left: 1px;
            width: 17px;
            height: 17px;
            color: rgba(22, 35, 60, .3);
            transition: .3s;
            pointer-events: none
        }

        .input-wrap:focus-within .ic {
            color: #38aab4;
            filter: drop-shadow(0 0 7px rgba(56, 170, 180, .55))
        }

        .input-wrap input {
            width: 100%;
            padding: 9px 34px 8px 28px;
            font-size: 14px;
            font-family: inherit;
            color: #16233c;
            background: transparent;
            border: none;
            outline: none;
            border-bottom: 1px solid rgba(22, 35, 60, .2);
            transition: border-color .3s, box-shadow .3s
        }

        .input-wrap input::placeholder {
            color: rgba(22, 35, 60, .3)
        }

        .input-wrap input:focus {
            border-bottom-color: rgba(56, 170, 180, .9);
            box-shadow: 0 1px 0 0 rgba(56, 170, 180, .45)
        }

        .input-wrap input.has-error {
            border-bottom-color: rgba(224, 86, 110, .85)
        }

        .input-wrap.ok input {
            border-bottom-color: rgba(46, 193, 129, .8);
            box-shadow: 0 1px 0 0 rgba(46, 193, 129, .4)
        }

        input:-webkit-autofill {
            -webkit-text-fill-color: #16233c;
            transition: background-color 99999s
        }

        .field-error {
            color: #d94560;
            font-size: 12px;
            margin-top: 5px
        }

        .eye {
            position: absolute;
            right: 0;
            width: 30px;
            height: 30px;
            display: grid;
            place-items: center;
            background: none;
            border: none;
            color: rgba(22, 35, 60, .35);
            cursor: pointer;
            border-radius: 8px;
            transition: .25s
        }

        .eye:hover {
            color: #16233c;
            background: rgba(22, 35, 60, .06)
        }

        .eye svg {
            width: 17px;
            height: 17px
        }

        /* ══════════ STRENGTH METER ══════════ */
        .meter {
            display: flex;
            gap: 5px;
            margin-top: 9px;
            align-items: center
        }

        .meter i {
            height: 4px;
            flex: 1;
            border-radius: 9px;
            background: rgba(22, 35, 60, .1);
            transition: background .35s, box-shadow .35s
        }

        .meter i.on1 {
            background: #e0566e;
            box-shadow: 0 0 8px rgba(224, 86, 110, .5)
        }

        .meter i.on2 {
            background: #e0851f;
            box-shadow: 0 0 8px rgba(224, 133, 31, .5)
        }

        .meter i.on3 {
            background: #c99a1f;
            box-shadow: 0 0 8px rgba(201, 154, 31, .5)
        }

        .meter i.on4 {
            background: #1f9d63;
            box-shadow: 0 0 8px rgba(31, 157, 99, .5)
        }

        #pwLabel {
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .5px;
            text-transform: uppercase;
            color: rgba(22, 35, 60, .4);
            min-width: 62px;
            text-align: right;
            transition: .3s
        }

        .chips {
            display: flex;
            gap: 6px;
            margin-top: 8px;
            flex-wrap: wrap
        }

        .chip {
            font-size: 9.5px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 20px;
            color: rgba(22, 35, 60, .4);
            border: 1px solid rgba(22, 35, 60, .15);
            transition: .3s
        }

        .chip.ok {
            color: #1f9d63;
            border-color: rgba(46, 193, 129, .45);
            background: rgba(46, 193, 129, .08)
        }

        .terms {
            margin-top: 16px;
            display: flex;
            align-items: flex-start;
            gap: 9px;
            font-size: 11.5px;
            color: rgba(22, 35, 60, .55);
            line-height: 1.5;
            cursor: pointer;
            user-select: none
        }

        .terms input {
            display: none
        }

        .box {
            width: 16px;
            height: 16px;
            min-width: 16px;
            margin-top: 1px;
            border: 1px solid rgba(22, 35, 60, .3);
            border-radius: 5px;
            display: grid;
            place-items: center;
            transition: .25s
        }

        .box svg {
            width: 10px;
            height: 10px;
            color: #fff;
            opacity: 0;
            transform: scale(.3);
            transition: .25s cubic-bezier(.34, 1.56, .64, 1)
        }

        .terms:hover .box {
            border-color: rgba(56, 170, 180, .8)
        }

        .terms input:checked+.box {
            background: #38aab4;
            border-color: #38aab4;
            box-shadow: 0 0 12px rgba(56, 170, 180, .4)
        }

        .terms input:checked+.box svg {
            opacity: 1;
            transform: scale(1)
        }

        .terms a {
            color: rgba(43, 127, 184, .9);
            text-decoration: none
        }

        .terms a:hover {
            color: #16233c
        }

        .btn {
            position: relative;
            overflow: hidden;
            width: 100%;
            margin-top: 22px;
            padding: 11px 16px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #fff;
            background: linear-gradient(135deg, #38aab4, #2b7fb8);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 10px 24px rgba(56, 170, 180, .35);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: background .3s, box-shadow .3s, transform .2s, opacity .3s;
            animation: fieldIn .7s .76s cubic-bezier(.2, .9, .3, 1) both
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -80%;
            width: 50%;
            height: 100%;
            background: linear-gradient(100deg, transparent, rgba(255, 255, 255, .35), transparent);
            transform: skewX(-20deg);
            transition: left .55s ease
        }

        .btn:hover {
            background: linear-gradient(135deg, #41bcc7, #3290c9);
            box-shadow: 0 16px 30px rgba(56, 170, 180, .45), 0 8px 24px rgba(31, 51, 89, .15);
            transform: translateY(-1px)
        }

        .btn:hover::before {
            left: 130%
        }

        .btn:active {
            transform: scale(.98)
        }

        .btn .arr {
            width: 15px;
            height: 15px;
            transition: transform .3s
        }

        .btn:hover .arr {
            transform: translateX(4px)
        }

        .btn.loading {
            pointer-events: none;
            opacity: .6
        }

        .btn .spinner {
            display: none;
            width: 14px;
            height: 14px;
            border: 2px solid rgba(255, 255, 255, .35);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite
        }

        .btn.loading .spinner {
            display: inline-block
        }

        @keyframes spin {
            to {
                transform: rotate(360deg)
            }
        }

        .links {
            margin-top: 18px;
            text-align: center;
            font-size: 12px;
            color: rgba(22, 35, 60, .45);
            animation: fieldIn .7s .84s cubic-bezier(.2, .9, .3, 1) both
        }

        .links a {
            color: rgba(43, 127, 184, .9);
            text-decoration: none;
            font-weight: 600;
            transition: .3s
        }

        .links a:hover {
            color: #16233c;
            text-shadow: 0 0 12px rgba(56, 170, 180, .4)
        }
    </style>
    <script src="{{ asset('js/galaxy-background.js') }}"></script>
</head>

<body>

    <canvas id="galaxy"></canvas>
    <div class="vignette"></div>

    <div class="stage">
        <form method="POST" action="{{ route('register') }}" class="panel" id="registerForm">
            @csrf

            <div class="logo"><span class="core"></span></div>
            <p class="brand">{{ config('app.name', 'App') }}</p>
            <p class="heading">Create Account</p>

            <div id="alertBox"></div>

            @if ($errors->any())
            <div class="alert">
                @foreach ($errors->all() as $error)
                <p><span class="ico">✖</span><span>{{ $error }}</span></p>
                @endforeach
            </div>
            @endif

            <div class="field d1">
                <label for="name">Username</label>
                <div class="input-wrap">
                    <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="8" r="4" />
                        <path d="M4 20c1.5-3.5 4.5-5 8-5s6.5 1.5 8 5" />
                    </svg>
                    <input id="name" type="text" name="name" value="{{ old('name') }}"
                        placeholder="Your name" required autofocus autocomplete="name"
                        @error('name') class="has-error" @enderror>
                </div>
                @error('name')
                <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field d2">
                <label for="email">Email</label>
                <div class="input-wrap">
                    <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="5" width="18" height="14" rx="3" />
                        <path d="m3 7 9 6 9-6" />
                    </svg>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                        placeholder="you@example.com" required autocomplete="username"
                        @error('email') class="has-error" @enderror>
                </div>
                @error('email')
                <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field d3">
                <label for="password">Password <span class="caps" id="capsWarn">⚠ Caps Lock is ON</span></label>
                <div class="input-wrap">
                    <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="4.5" y="10.5" width="15" height="10" rx="3" />
                        <path d="M8 10.5V7a4 4 0 1 1 8 0v3.5" />
                    </svg>
                    <input id="password" type="password" name="password" placeholder="••••••••"
                        required autocomplete="new-password" style="padding-right:38px"
                        @error('password') class="has-error" @enderror>
                    <button type="button" class="eye" id="eyeBtn" aria-label="Toggle password"></button>
                </div>
                @error('password')
                <p class="field-error">{{ $message }}</p>
                @enderror
                <div class="meter">
                    <i></i><i></i><i></i><i></i>
                    <span id="pwLabel">strength</span>
                </div>
                <div class="chips">
                    <span class="chip" data-k="len">8+ chars</span>
                    <span class="chip" data-k="up">A-Z</span>
                    <span class="chip" data-k="num">0-9</span>
                    <span class="chip" data-k="sym">!@#$</span>
                </div>
            </div>

            <div class="field d4">
                <label for="password_confirmation">Confirm Password <span class="match-tag" id="matchTag"></span></label>
                <div class="input-wrap" id="confWrap">
                    <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="4.5" y="10.5" width="15" height="10" rx="3" />
                        <path d="M8 10.5V7a4 4 0 1 1 8 0v3.5" />
                    </svg>
                    <input id="password_confirmation" type="password" name="password_confirmation"
                        placeholder="••••••••" required autocomplete="new-password"
                        @error('password_confirmation') class="has-error" @enderror>
                </div>
                @error('password_confirmation')
                <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <label class="terms d5">
                <input type="checkbox" name="terms" required>
                <span class="box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 12.5 9.5 18 20 6.5" />
                    </svg></span>
                <span>I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></span>
            </label>

            <button type="submit" class="btn" id="registerBtn">
                <span class="spinner"></span>
                <span>Create Account</span>
                <svg class="arr" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14M13 6l6 6-6 6" />
                </svg>
            </button>

            <div class="links">
                Already have an account? <a href="{{ route('login') }}">Login →</a>
            </div>
        </form>
    </div>

    <script>
                /* ═══ SAFETY LAYOUT — AJAX binds FIRST (guarded), extras after ═══ */

        let galaxyAnim = null;

        const escape = (s) => {
            const d = document.createElement('div');
            d.textContent = s;
            return d.innerHTML;
        };

        /* ── smooth engine (hoisted) ── */
        async function smoothGo(url, push = true) {
            try {
                const res = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const html = await res.text();
                await smoothSwap(res.url || url, html, push);
            } catch (err) {
                console.log('[AJAX] smoothGo fallback →', err.message);
                window.location.href = url;
            }
        }

        async function smoothSwap(finalUrl, html, push) {
            try {
                galaxyAnim?.stop();

                const veil = document.createElement('div');
                veil.style.cssText = 'position:fixed;inset:0;background:#fbf9f4;opacity:0;transition:opacity .45s ease;z-index:99999;pointer-events:none';
                document.body.appendChild(veil);
                requestAnimationFrame(() => { veil.style.opacity = '1'; });
                await new Promise(r => setTimeout(r, 120));

                const doc = new DOMParser().parseFromString(html, 'text/html');

                document.getElementById('pageStyles')?.remove();
                document.querySelectorAll('head style[data-page], head link[data-page]').forEach(el => el.remove());
                doc.querySelectorAll('head style, head link[rel="stylesheet"]').forEach(el => {
                    let c;
                    if (el.tagName === 'LINK') {
                        c = document.createElement('link');
                        c.rel = 'stylesheet';
                        c.href = el.getAttribute('href');
                    } else {
                        c = document.createElement('style');
                        c.textContent = el.textContent;
                    }
                    c.setAttribute('data-page', '');
                    document.head.appendChild(c);
                });

                document.title = doc.title;
                document.body.innerHTML = doc.body.innerHTML;
                window.scrollTo(0, 0);

                (function () {
                    const orig = document.addEventListener.bind(document);
                    const dcl = [];
                    document.addEventListener = function (type, fn, opts) {
                        if (type === 'DOMContentLoaded') { dcl.push(fn); return; }
                        return orig(type, fn, opts);
                    };
                    doc.body.querySelectorAll('script').forEach(old => {
                        const s = document.createElement('script');
                        [...old.attributes].forEach(a => s.setAttribute(a.name, a.value));
                        s.textContent = old.textContent;
                        document.body.appendChild(s);
                    });
                    document.addEventListener = orig;
                    dcl.forEach(fn => { try { fn({}); } catch (e) { console.error(e); } });
                })();

                if (push) { try { history.pushState({}, '', finalUrl); } catch (e) {} }

                document.body.appendChild(veil);
                setTimeout(() => {
                    veil.style.opacity = '0';
                    setTimeout(() => veil.remove(), 500);
                }, 200);
            } catch (err) {
                console.log('[AJAX] smoothSwap fallback →', err);
                window.location.href = finalUrl;
            }
        }

        /* ══════════ 1. AJAX REGISTER — binds FIRST, guarded ══════════ */
        try {
            const form = document.getElementById('registerForm');
            const btn = document.getElementById('registerBtn');
            const alertBox = document.getElementById('alertBox');
            if (!form || !btn || !alertBox) throw new Error('register form elements missing');

            function showAlert(type, messages) {
                alertBox.innerHTML = `<div class="alert ${type}">${messages.map(m =>
                    `<p><span class="ico">${type === 'ok' ? '✔' : '✖'}</span><span>${escape(m)}</span></p>`
                ).join('')}</div>`;
            }
            function clearErrors() {
                alertBox.innerHTML = '';
                form.querySelectorAll('.field-error').forEach(el => el.remove());
                form.querySelectorAll('.has-error').forEach(el => el.classList.remove('has-error'));
            }
            function showFieldErrors(errors) {
                Object.entries(errors).forEach(([field, msgs]) => {
                    (Array.isArray(msgs) ? msgs : [msgs]).forEach(msg => {
                        const input = form.querySelector(`[name="${field}"]`);
                        if (!input || !input.closest('.field')) { showAlert('error', [msg]); return; }
                        input.classList.add('has-error');
                        const fieldEl = input.closest('.field');
                        if (!fieldEl.querySelector('.field-error')) {
                            const p = document.createElement('p');
                            p.className = 'field-error';
                            fieldEl.appendChild(p);
                        }
                        fieldEl.querySelector('.field-error').textContent = msg;
                    });
                });
            }

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                e.stopPropagation();
                btn.classList.add('loading');
                clearErrors();

                try {
                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': form.querySelector('input[name="_token"]')?.value || ''
                        },
                        body: new FormData(form),
                        credentials: 'same-origin'
                    });

                    const ctype = res.headers.get('content-type') || '';

                    if (ctype.includes('application/json')) {
                        const data = await res.json().catch(() => ({}));

                        if (res.ok && data.redirect) {
                            showAlert('ok', ['Account created! Loading email verification…']);
                            if (typeof window.ajaxNavigate === 'function') {
                                if (!await window.ajaxNavigate(data.redirect)) btn.classList.remove('loading');
                            } else {
                                await smoothGo(data.redirect);
                            }
                            return;
                        }
                        if (res.status === 419) {
                            showAlert('error', ['Session expired — refresh the page and try again.']);
                            btn.classList.remove('loading');
                            return;
                        }
                        if (res.status === 422) {
                            showFieldErrors(data.errors || {});
                            btn.classList.remove('loading');
                            return;
                        }
                        showAlert('error', [data.message || 'Something went wrong. Please try again.']);
                        btn.classList.remove('loading');
                        return;
                    }

                    const html = await res.text();
                    const regPath = new URL(form.action, location.origin).pathname;
                    const finalPath = new URL(res.url, location.origin).pathname;

                    if (res.ok && finalPath !== regPath) {
                        showAlert('ok', ['Account created! Loading email verification…']);
                        if (typeof window.ajaxNavigate === 'function') {
                            if (!await window.ajaxNavigate(res.url)) btn.classList.remove('loading');
                        } else {
                            await smoothSwap(res.url, html, true);
                        }
                        return;
                    }

                    showAlert('error', ['Registration failed. Please check the fields and try again.']);
                    btn.classList.remove('loading');

                } catch (err) {
                    console.log('[AJAX] network error →', err);
                    showAlert('error', ['Network error — please check your connection and try again.']);
                    btn.classList.remove('loading');
                }
            });

            form.querySelectorAll('input').forEach(i => i.addEventListener('input', () => {
                i.classList.remove('has-error');
                i.closest('.field')?.querySelector('.field-error')?.remove();
            }));
        } catch (e) {
            console.error('[register] AJAX bind failed:', e);
        }

        /* ══════════ 2. GALAXY (guarded) ══════════ */
        try {
            galaxyAnim = Galaxy(document.getElementById('galaxy'), { radius: .42 });
        } catch (e) { console.error('[galaxy]', e); }

        /* ══════════ 3. EYE + CAPS + STRENGTH + MATCH (guarded) ══════════ */
        try {
            const EYE = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-6.5 10-6.5S22 12 22 12s-3.5 6.5-10 6.5S2 12 2 12Z"/><circle cx="12" cy="12" r="2.8"/></svg>';
            const EYE_OFF = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-6.5 10-6.5S22 12 22 12s-3.5 6.5-10 6.5S2 12 2 12Z"/><circle cx="12" cy="12" r="2.8"/><path d="M4 4l16 16"/></svg>';
            const pw = document.getElementById('password'),
                conf = document.getElementById('password_confirmation');
            const eyeBtn = document.getElementById('eyeBtn');
            if (eyeBtn && pw) {
                eyeBtn.innerHTML = EYE;
                eyeBtn.addEventListener('click', () => {
                    const show = pw.type === 'password';
                    pw.type = show ? 'text' : 'password';
                    eyeBtn.innerHTML = show ? EYE_OFF : EYE;
                });

                const caps = document.getElementById('capsWarn');
                if (caps) {
                    ['keydown', 'keyup'].forEach(ev => pw.addEventListener(ev, e => {
                        if (e.getModifierState) caps.classList.toggle('show', e.getModifierState('CapsLock'));
                    }));
                }
            }

            /* strength meter */
            const bars = [...document.querySelectorAll('.meter i')],
                pwLabel = document.getElementById('pwLabel');
            const chips = [...document.querySelectorAll('.chip')];
            const NAMES = ['strength', 'weak', 'fair', 'good', 'strong'];
            const tag = document.getElementById('matchTag'),
                confWrap = document.getElementById('confWrap');

            function checkMatch() {
                if (!conf || !tag) return;
                if (!conf.value) {
                    tag.className = 'match-tag';
                    tag.textContent = '';
                    if (confWrap) confWrap.classList.remove('ok');
                    return;
                }
                const ok = pw.value === conf.value;
                tag.className = 'match-tag ' + (ok ? 'ok' : 'no');
                tag.textContent = ok ? '✓ Passwords match' : '✕ Not matching';
                if (confWrap) confWrap.classList.toggle('ok', ok);
            }

            if (pw) {
                pw.addEventListener('input', () => {
                    const v = pw.value;
                    const rules = {
                        len: v.length >= 8,
                        up: /[A-Z]/.test(v),
                        num: /[0-9]/.test(v),
                        sym: /[^A-Za-z0-9]/.test(v)
                    };
                    chips.forEach(c => c.classList.toggle('ok', rules[c.dataset.k]));
                    let score = Object.values(rules).filter(Boolean).length;
                    if (v.length === 0) score = 0;
                    if (v.length > 0 && v.length < 8) score = Math.min(score, 1);
                    if (v.length >= 12 && score < 4) score++;
                    score = Math.min(score, 4);
                    bars.forEach((b, i) => { b.className = i < score ? 'on' + score : ''; });
                    if (pwLabel) {
                        pwLabel.textContent = NAMES[score];
                        pwLabel.style.color = ['', '#e0566e', '#e0851f', '#c99a1f', '#1f9d63'][score];
                    }
                    checkMatch();
                });
            }
            if (conf) conf.addEventListener('input', checkMatch);
        } catch (e) { console.error('[register extras]', e); }
    </script>
    <script src="{{ asset('js/ajax-navigation.js') }}"></script>
</body>

</html>