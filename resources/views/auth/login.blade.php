<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'App') }} — Login</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}?v=3">

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

        /* ══════════ PANEL ══════════ */
        .panel {
            position: relative;
            margin: auto;
            width: min(360px, 100%);
            padding: 34px 34px 26px;
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
            background: conic-gradient(from var(--a), rgba(56, 170, 180, 0) 0deg, rgba(56, 170, 180, .65) 90deg, rgba(125, 219, 226, .55) 180deg, rgba(56, 170, 180, 0) 300deg);
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

        /* ══════════ LOGO ══════════ */
        .logo {
            width: 54px;
            height: 54px;
            margin: 0 auto 14px;
            border-radius: 17px;
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
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: radial-gradient(circle at 35% 35%, #fff, #eafcff 55%, #bdeef2);
            box-shadow: 0 0 16px rgba(255, 255, 255, .95), 0 0 34px rgba(255, 255, 255, .5);
            animation: corePulse 2.4s ease-in-out infinite
        }

        @keyframes corePulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 1
            }

            50% {
                transform: scale(.82);
                opacity: .75
            }
        }

        .brand {
            text-align: center;
            font-size: 21px;
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
            margin: 6px 0 4px
        }

        /* ══════════ ALERTS ══════════ */
        .alert {
            margin-top: 14px;
            padding: 10px 13px;
            border-radius: 9px;
            font-size: 13px;
            line-height: 1.5
        }

        .alert.error {
            background: rgba(224, 86, 110, .08);
            border: 1px solid rgba(224, 86, 110, .45);
            color: #d94560;
            animation: shake .5s cubic-bezier(.36, .07, .19, .97)
        }

        .alert.ok {
            background: rgba(46, 193, 129, .08);
            border: 1px solid rgba(46, 193, 129, .45);
            color: #1f9d63;
            animation: panelIn .5s cubic-bezier(.2, .9, .3, 1) both
        }

        .alert p {
            display: flex;
            align-items: flex-start;
            gap: 8px
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

        /* ══════════ FIELDS ══════════ */
        .field {
            margin-top: 18px;
            animation: fieldIn .7s cubic-bezier(.2, .9, .3, 1) both
        }

        .d1 {
            animation-delay: .3s
        }

        .d2 {
            animation-delay: .42s
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
            font-weight: 700;
            letter-spacing: 0
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
            padding: 10px 34px 9px 28px;
            font-size: 14.5px;
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

        input:-webkit-autofill {
            -webkit-text-fill-color: #16233c;
            transition: background-color 99999s
        }

        .field-error {
            color: #d94560;
            font-size: 12px;
            margin-top: 6px
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

        /* ══════════ ROW ══════════ */
        .row {
            margin-top: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            animation: fieldIn .7s .62s cubic-bezier(.2, .9, .3, 1) both
        }

        .check {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            font-size: 12px;
            color: rgba(22, 35, 60, .55);
            cursor: pointer;
            user-select: none
        }

        .check input {
            display: none
        }

        .box {
            width: 17px;
            height: 17px;
            border: 1px solid rgba(22, 35, 60, .3);
            border-radius: 5.5px;
            display: grid;
            place-items: center;
            transition: .25s
        }

        .box svg {
            width: 11px;
            height: 11px;
            color: #fff;
            opacity: 0;
            transform: scale(.3) rotate(-30deg);
            transition: .25s cubic-bezier(.34, 1.56, .64, 1)
        }

        .check:hover .box {
            border-color: rgba(56, 170, 180, .8);
            box-shadow: 0 0 10px rgba(56, 170, 180, .25)
        }

        .check input:checked+.box {
            background: #38aab4;
            border-color: #38aab4;
            box-shadow: 0 0 14px rgba(56, 170, 180, .45)
        }

        .check input:checked+.box svg {
            opacity: 1;
            transform: scale(1) rotate(0)
        }

        .fp {
            font-size: 12px;
            color: rgba(43, 127, 184, .85);
            text-decoration: none;
            position: relative;
            transition: .3s
        }

        .fp::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -2px;
            width: 0;
            height: 1px;
            background: #38aab4;
            transition: width .3s
        }

        .fp:hover {
            color: #16233c
        }

        .fp:hover::after {
            width: 100%
        }

        /* ══════════ BUTTON ══════════ */
        .btn {
            position: relative;
            overflow: hidden;
            width: 100%;
            margin-top: 24px;
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
            animation: fieldIn .7s .7s cubic-bezier(.2, .9, .3, 1) both
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

        /* ══════════ LINKS ══════════ */
        .links {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: rgba(22, 35, 60, .45);
            animation: fieldIn .7s .78s cubic-bezier(.2, .9, .3, 1) both
        }

        .links a {
            color: rgba(43, 127, 184, .9);
            text-decoration: none;
            font-weight: 600;
            transition: color .3s
        }

        .links a:hover {
            color: #16233c
        }
    </style>
    <script src="{{ asset('js/galaxy-background.js') }}"></script>
</head>

<body>

    <canvas id="galaxy"></canvas>
    <div class="vignette"></div>

    <div class="stage">
        <form method="POST" action="{{ route('login') }}" class="panel" id="loginForm">
            @csrf

            <div class="logo"><span class="core"></span></div>
            <p class="brand">{{ config('app.name', 'App') }}</p>
            <p class="heading">Welcome Back</p>

            <div id="alertBox"></div>

            @if (session('status'))
            <div class="alert ok">
                <p><span class="ico">✔</span><span>{{ session('status') }}</span></p>
            </div>
            @endif

            @if ($errors->any())
            <div class="alert error">
                @foreach ($errors->all() as $error)
                <p><span class="ico">✖</span><span>{{ str_contains(strtolower($error), 'credential') ? __('User does not exist or the password is incorrect.') : $error }}</span></p>
                @endforeach
            </div>
            @endif

            <div class="field d1">
                <label for="email">Email</label>
                <div class="input-wrap">
                    <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="5" width="18" height="14" rx="3" />
                        <path d="m3 7 9 6 9-6" />
                    </svg>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                        placeholder="you@example.com" required autofocus autocomplete="username"
                        @error('email') class="has-error" @enderror>
                </div>
                @error('email')
                @if (!str_contains(strtolower($message), 'credential'))
                <p class="field-error">{{ $message }}</p>
                @endif
                @enderror
            </div>

            <div class="field d2">
                <label for="password">Password <span class="caps" id="capsWarn">⚠ Caps Lock is ON</span></label>
                <div class="input-wrap">
                    <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="4.5" y="10.5" width="15" height="10" rx="3" />
                        <path d="M8 10.5V7a4 4 0 1 1 8 0v3.5" />
                    </svg>
                    <input id="password" type="password" name="password" placeholder="••••••••"
                        required autocomplete="current-password" style="padding-right:38px"
                        @error('password') class="has-error" @enderror>
                    <button type="button" class="eye" id="eyeBtn" aria-label="Toggle password"></button>
                </div>
                @error('password')
                <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="row">
                <label class="check">
                    <input type="checkbox" name="remember">
                    <span class="box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 12.5 9.5 18 20 6.5" />
                        </svg></span>
                    Remember me
                </label>
                @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="fp">Forgot password?</a>
                @endif
            </div>

            <button type="submit" class="btn" id="loginBtn">
                <span class="spinner"></span>
                <span>Login</span>
                <svg class="arr" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14M13 6l6 6-6 6" />
                </svg>
            </button>

            <div class="links">
                New here? @if (Route::has('register')) <a href="{{ route('register') }}">Create an account →</a> @endif
            </div>
        </form>
    </div>

    <script>
        (() => {
            const TAU = Math.PI * 2;
            const rand = (a, b) => a + Math.random() * (b - a);

            function Galaxy(canvas, o = {}) {
                const ctx = canvas.getContext('2d');
                let W, H, cx, cy, R;
                const resize = () => {
                    W = canvas.width = window.innerWidth;
                    H = canvas.height = window.innerHeight;
                    cx = W / 2;
                    cy = H / 2;
                    R = Math.min(W, H) * (o.radius ?? .44)
                };
                resize();
                window.addEventListener('resize', resize);

                const N = o.count ?? 260,
                    pts = [],
                    GA = Math.PI * (3 - Math.sqrt(5));
                for (let i = 0; i < N; i++) {
                    const y = 1 - (i / (N - 1)) * 2,
                        r = Math.sqrt(Math.max(0, 1 - y * y)),
                        t = GA * i,
                        roll = Math.random();
                    pts.push({
                        x: Math.cos(t) * r * rand(.93, 1),
                        y: y * rand(.95, 1),
                        z: Math.sin(t) * r * rand(.93, 1),
                        r: rand(.5, 1.5),
                        c: roll < .78 ? '56,170,180' : roll < .93 ? '125,219,226' : '255,122,158',
                        tw: rand(0, TAU)
                    });
                }
                const TH = .23,
                    TH2 = TH * TH;
                let pairs = [];
                for (let i = 0; i < N; i++)
                    for (let j = i + 1; j < N; j++) {
                        const dx = pts[i].x - pts[j].x,
                            dy = pts[i].y - pts[j].y,
                            dz = pts[i].z - pts[j].z;
                        if (dx * dx + dy * dy + dz * dz < TH2) pairs.push(i * N + j);
                    }
                for (let i = pairs.length - 1; i > 0; i--) {
                    const j = (Math.random() * (i + 1)) | 0;
                    [pairs[i], pairs[j]] = [pairs[j], pairs[i]]
                }
                pairs = pairs.slice(0, o.maxLines ?? 500);

                const dust = [];
                for (let i = 0; i < (o.dust ?? 70); i++) dust.push({
                    x: Math.random(),
                    y: Math.random(),
                    r: rand(.3, 1.3),
                    a: rand(.05, .4),
                    vx: rand(-.004, .004),
                    vy: rand(-.004, .004)
                });

                const orbs = [{
                    rr: .5,
                    tilt: .55,
                    sp: .00042,
                    ph: 1.1
                }, {
                    rr: .8,
                    tilt: -.85,
                    sp: -.00027,
                    ph: 3.9
                }];
                const FOV = 2.6;
                let rotY = rand(0, TAU),
                    rotX = -.32;
                const mouse = {
                    x: 0,
                    y: 0
                };
                const moveMouse = e => {
                    mouse.x = e.clientX / window.innerWidth - .5;
                    mouse.y = e.clientY / window.innerHeight - .5
                };
                window.addEventListener('mousemove', moveMouse);

                const glow = (x, y, r, rgb, a) => {
                    if (r <= 0 || a <= 0) return;
                    const g = ctx.createRadialGradient(x, y, 0, x, y, r);
                    g.addColorStop(0, 'rgba(' + rgb + ',' + a + ')');
                    g.addColorStop(1, 'rgba(' + rgb + ',0)');
                    ctx.fillStyle = g;
                    ctx.beginPath();
                    ctx.arc(x, y, r, 0, TAU);
                    ctx.fill()
                };

                const proj = new Array(N);
                let raf, last = performance.now(), stopped = false;
                let star = null,
                    nextShoot = 2000;

                const frame = now => {
                    if (stopped) return;
                    const dt = Math.min(50, now - last);
                    last = now;
                    rotY += .000105 * dt;
                    rotX += ((-.32 + mouse.y * .3) - rotX) * .04;
                    const yOff = mouse.x * .35,
                        sy = Math.sin(rotY + yOff),
                        cyc = Math.cos(rotY + yOff);
                    const sxr = Math.sin(rotX),
                        cxr = Math.cos(rotX);
                    ctx.clearRect(0, 0, W, H);

                    for (const d of dust) {
                        d.x += d.vx * dt;
                        d.y += d.vy * dt;
                        if (d.x < 0) d.x += 1;
                        if (d.x > 1) d.x -= 1;
                        if (d.y < 0) d.y += 1;
                        if (d.y > 1) d.y -= 1;
                        ctx.fillStyle = 'rgba(56,170,180,' + d.a + ')';
                        ctx.beginPath();
                        ctx.arc(d.x * W, d.y * H, d.r, 0, TAU);
                        ctx.fill()
                    }

                    ctx.globalCompositeOperation = 'source-over';
                    glow(cx, cy, R * 1.3, '56,170,180', .1);
                    const pulse = 1 + Math.sin(now * .0011) * .1;
                    glow(cx, cy, R * .5 * pulse, '125,219,226', .14);
                    glow(cx, cy, R * .16 * pulse, '255,255,255', .5);

                    for (let i = 0; i < N; i++) {
                        const p = pts[i];
                        const x1 = p.x * cyc - p.z * sy,
                            z1 = p.x * sy + p.z * cyc;
                        const y2 = p.y * cxr - z1 * sxr,
                            z2 = p.y * sxr + z1 * cxr;
                        const f = FOV / (FOV + z2);
                        proj[i] = {
                            x: cx + x1 * R * f,
                            y: cy + y2 * R * f,
                            b: (f - .68) / .98,
                            tw: p.tw + now * .0016
                        }
                    }

                    ctx.lineWidth = .55;
                    for (const code of pairs) {
                        const i = (code / N) | 0,
                            j = code % N,
                            a = proj[i],
                            b2 = proj[j];
                        const al = ((a.b + b2.b) / 2) * .17;
                        if (al < .01) continue;
                        ctx.strokeStyle = 'rgba(43,127,184,' + al.toFixed(3) + ')';
                        ctx.beginPath();
                        ctx.moveTo(a.x, a.y);
                        ctx.lineTo(b2.x, b2.y);
                        ctx.stroke()
                    }

                    for (let i = 0; i < N; i++) {
                        const q = proj[i],
                            p = pts[i],
                            tw = .7 + .3 * Math.sin(q.tw);
                        const al = (.1 + q.b * .8) * tw;
                        ctx.fillStyle = 'rgba(' + p.c + ',' + al.toFixed(3) + ')';
                        ctx.beginPath();
                        ctx.arc(q.x, q.y, p.r * (.45 + q.b), 0, TAU);
                        ctx.fill()
                    }

                    for (const ob of orbs) {
                        const a = ob.ph + ob.sp * now;
                        let px = Math.cos(a) * ob.rr,
                            py = 0,
                            pz = Math.sin(a) * ob.rr;
                        let y1 = py * Math.cos(ob.tilt) - pz * Math.sin(ob.tilt),
                            z1 = py * Math.sin(ob.tilt) + pz * Math.cos(ob.tilt);
                        py = y1;
                        pz = z1;
                        const x1 = px * cyc - pz * sy,
                            z2 = px * sy + pz * cyc;
                        const y2 = py * cxr - z2 * sxr,
                            z3 = py * sxr + z2 * cxr,
                            f = FOV / (FOV + z3);
                        const ox = cx + x1 * R * f,
                            oy = cy + y2 * R * f;
                        glow(ox, oy, 26 * f, '255,122,158', .3);
                        glow(ox, oy, 8 * f, '255,190,205', .65);
                        ctx.fillStyle = 'rgba(255,240,244,.95)';
                        ctx.beginPath();
                        ctx.arc(ox, oy, 2.1 * f, 0, TAU);
                        ctx.fill()
                    }

                    nextShoot -= dt;
                    if (nextShoot < 0 && !star) {
                        star = {
                            x: rand(.15, .85) * W,
                            y: rand(.05, .3) * H,
                            vx: (rand(6, 10)) * (Math.random() < .5 ? -1 : 1),
                            vy: rand(2.5, 4.5),
                            l: 1
                        };
                        nextShoot = 3500 + Math.random() * 4500;
                    }
                    if (star) {
                        star.x += star.vx * dt * .06;
                        star.y += star.vy * dt * .06;
                        star.l -= dt * .0011;
                        const g = ctx.createLinearGradient(star.x, star.y, star.x - star.vx * 11, star.y - star.vy * 11);
                        g.addColorStop(0, 'rgba(56,170,180,' + (.8 * Math.max(star.l, 0)).toFixed(3) + ')');
                        g.addColorStop(1, 'rgba(56,170,180,0)');
                        ctx.strokeStyle = g;
                        ctx.lineWidth = 1.6;
                        ctx.beginPath();
                        ctx.moveTo(star.x, star.y);
                        ctx.lineTo(star.x - star.vx * 11, star.y - star.vy * 11);
                        ctx.stroke();
                        if (star.l <= 0 || star.x < -100 || star.x > W + 100) star = null;
                    }

                    raf = requestAnimationFrame(frame);
                };
                raf = requestAnimationFrame(frame);
                return {
                    stop: () => {
                        if (stopped) return;
                        stopped = true;
                        cancelAnimationFrame(raf);
                        window.removeEventListener('resize', resize);
                        window.removeEventListener('mousemove', moveMouse);
                    }
                };
            }

            /* ════════════════════════════════════════════════════════════
               SAFETY LAYOUT — AJAX binds FIRST (guarded). Galaxy / eye /
               caps run AFTER (each guarded) so they can NEVER kill the form.
            ════════════════════════════════════════════════════════════ */

            let galaxyAnim = null;

            const escape = (s) => {
                const d = document.createElement('div');
                d.textContent = s;
                return d.innerHTML;
            };

            /* ── smooth engine (hoisted — callable from the handler below) ── */
            async function smoothGo(url, push = true) {
                try {
                    const res = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
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
                    requestAnimationFrame(() => {
                        veil.style.opacity = '1';
                    });
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

                    (function() {
                        const orig = document.addEventListener.bind(document);
                        const dcl = [];
                        document.addEventListener = function(type, fn, opts) {
                            if (type === 'DOMContentLoaded') {
                                dcl.push(fn);
                                return;
                            }
                            return orig(type, fn, opts);
                        };
                        doc.body.querySelectorAll('script').forEach(old => {
                            const s = document.createElement('script');
                            [...old.attributes].forEach(a => s.setAttribute(a.name, a.value));
                            s.textContent = old.textContent;
                            document.body.appendChild(s);
                        });
                        document.addEventListener = orig;
                        dcl.forEach(fn => {
                            try {
                                fn({});
                            } catch (e) {
                                console.error(e);
                            }
                        });
                    })();

                    if (push) {
                        try {
                            history.pushState({}, '', finalUrl);
                        } catch (e) {}
                    }

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

            /* ══════════ 1. AJAX LOGIN — binds FIRST, guarded ══════════ */
            try {
                const form = document.getElementById('loginForm');
                const btn = document.getElementById('loginBtn');
                const alertBox = document.getElementById('alertBox');
                const CREDENTIAL_MSG = 'User does not exist or the password is incorrect.';
                if (!form || !btn || !alertBox) throw new Error('login form elements missing');

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
                            if (msg.toLowerCase().includes('credential')) {
                                showAlert('error', [CREDENTIAL_MSG]);
                                return;
                            }
                            const input = form.querySelector(`[name="${field}"]`);
                            if (!input || !input.closest('.field')) {
                                showAlert('error', [msg]);
                                return;
                            }
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

                        /* ──── PATH A: JSON ──── */
                        if (ctype.includes('application/json')) {
                            const data = await res.json().catch(() => ({}));

                            if (res.ok && data.redirect) {
                                showAlert('ok', ['Login successful! Loading your account…']);
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
                            showAlert('error', [data.message || CREDENTIAL_MSG]);
                            btn.classList.remove('loading');
                            return;
                        }

                        /* ──── PATH B: HTML fallback ──── */
                        const html = await res.text();
                        const loginPath = new URL(form.action, location.origin).pathname;
                        const finalPath = new URL(res.url, location.origin).pathname;

                        if (res.ok && finalPath !== loginPath) {
                            showAlert('ok', ['Login successful! Loading your account…']);
                            if (typeof window.ajaxNavigate === 'function') {
                                if (!await window.ajaxNavigate(res.url)) btn.classList.remove('loading');
                            } else {
                                await smoothSwap(res.url, html, true);
                            }
                            return;
                        }

                        showAlert('error', [CREDENTIAL_MSG]);
                        btn.classList.remove('loading');

                    } catch (err) {
                        console.log('[AJAX] network error →', err);
                        showAlert('error', ['Network error — please check your connection and try again.']);
                        btn.classList.remove('loading');
                    }
                });

                /* typing par errors live clear */
                form.querySelectorAll('input').forEach(i => i.addEventListener('input', () => {
                    i.classList.remove('has-error');
                    i.closest('.field')?.querySelector('.field-error')?.remove();
                }));
            } catch (e) {
                console.error('[login] AJAX bind failed:', e);
            }

            /* ══════════ 2. GALAXY (guarded — can never break AJAX) ══════════ */
            try {
                galaxyAnim = Galaxy(document.getElementById('galaxy'), {
                    radius: .46
                });
                const stopGalaxy = () => galaxyAnim?.stop();
                window.addEventListener('ajax:navigating', stopGalaxy, { once: true });
                window.addEventListener('pagehide', stopGalaxy, { once: true });
            } catch (e) {
                console.error('[galaxy]', e);
            }

            /* ══════════ 3. EYE + CAPS (guarded) ══════════ */
            try {
                const EYE = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-6.5 10-6.5S22 12 22 12s-3.5 6.5-10 6.5S2 12 2 12Z"/><circle cx="12" cy="12" r="2.8"/></svg>';
                const EYE_OFF = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-6.5 10-6.5S22 12 22 12s-3.5 6.5-10 6.5S2 12 2 12Z"/><circle cx="12" cy="12" r="2.8"/><path d="M4 4l16 16"/></svg>';
                const eyeBtn = document.getElementById('eyeBtn'),
                    pw = document.getElementById('password');
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
            } catch (e) {
                console.error('[login extras]', e);
            }
        })();
    </script>
    <script src="{{ asset('js/ajax-navigation.js') }}"></script>
</body>

</html>