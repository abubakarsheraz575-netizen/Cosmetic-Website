<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'App') }} — Forgot Password</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}?v=3">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box }

        html, body {
            height: 100%; overflow: hidden;
            font-family: 'Figtree', ui-sans-serif, system-ui, sans-serif;
            background: radial-gradient(ellipse at 50% 42%, #fbf9f4 0%, #f3eee4 55%, #e8f1f1 100%);
            color: #16233c
        }

        ::selection { background: rgba(56, 170, 180, .3) }

        #galaxy { position: fixed; inset: 0; z-index: 1 }

        .vignette {
            position: fixed; inset: 0; z-index: 2; pointer-events: none;
            background: radial-gradient(ellipse at center, transparent 45%, rgba(22, 35, 60, .16) 100%)
        }

        .stage {
            position: fixed; inset: 0; z-index: 3;
            display: flex; overflow-y: auto; padding: 32px 20px
        }

        .panel {
            position: relative; margin: auto; width: min(390px, 100%);
            padding: 38px 34px 28px; border-radius: 14px;
            background: rgba(255, 255, 255, .66);
            border: 1px solid rgba(255, 255, 255, .65);
            backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px);
            box-shadow: 0 30px 70px rgba(31, 51, 89, .16), inset 0 1px 0 rgba(255, 255, 255, .8);
            animation: panelIn .9s cubic-bezier(.2, .9, .3, 1) both .15s;
            text-align: center
        }

        @keyframes panelIn {
            from { opacity: 0; transform: translateY(26px) scale(.96); filter: blur(6px) }
            to { opacity: 1; transform: none; filter: none }
        }

        @property --a { syntax: '<angle>'; initial-value: 0deg; inherits: false }

        .panel::before {
            content: ''; position: absolute; inset: -1px; border-radius: 15px; padding: 1px;
            pointer-events: none;
            background: conic-gradient(from var(--a), rgba(56,170,180,0) 0deg, rgba(56,170,180,.65) 90deg, rgba(125,219,226,.55) 180deg, rgba(56,170,180,0) 300deg);
            -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
            -webkit-mask-composite: xor; mask-composite: exclude;
            animation: borderSpin 6s linear infinite
        }

        @keyframes borderSpin { to { --a: 360deg } }

        .key-logo {
            width: 64px; height: 64px; margin: 0 auto 16px; border-radius: 20px;
            display: grid; place-items: center; color: #fff;
            background: linear-gradient(135deg, #38aab4, #2b7fb8);
            box-shadow: 0 12px 28px rgba(56, 170, 180, .42), inset 0 0 0 1px rgba(255, 255, 255, .25);
            animation: float 5s ease-in-out infinite
        }

        .key-logo svg { width: 28px; height: 28px }

        @keyframes float { 0%, 100% { transform: translateY(0) } 50% { transform: translateY(-6px) } }

        .brand {
            font-size: 21px; font-weight: 800; letter-spacing: .5px;
            background: linear-gradient(90deg, #2b7fb8, #38aab4, #5cc9d2, #2b7fb8);
            background-size: 220% auto; -webkit-background-clip: text; background-clip: text;
            color: transparent; animation: shimmer 4.5s linear infinite
        }

        @keyframes shimmer { to { background-position: 220% center } }

        .heading {
            font-size: 11px; letter-spacing: 4px; text-transform: uppercase;
            color: rgba(22, 35, 60, .45); margin: 6px 0 14px
        }

        .sub { font-size: 13.5px; line-height: 1.7; color: rgba(22, 35, 60, .65); margin-bottom: 4px }

        .field { margin-top: 20px; text-align: left; animation: fieldIn .7s .35s cubic-bezier(.2, .9, .3, 1) both }

        @keyframes fieldIn {
            from { opacity: 0; transform: translateY(14px) }
            to { opacity: 1; transform: none }
        }

        .field label {
            display: block; font-size: 12px; font-weight: 600;
            color: rgba(22, 35, 60, .6); margin-bottom: 2px; letter-spacing: .3px
        }

        .input-wrap { position: relative; display: flex; align-items: center }

        .input-wrap .ic {
            position: absolute; left: 1px; width: 17px; height: 17px;
            color: rgba(22, 35, 60, .3); transition: .3s; pointer-events: none
        }

        .input-wrap:focus-within .ic { color: #38aab4; filter: drop-shadow(0 0 7px rgba(56, 170, 180, .55)) }

        .input-wrap input {
            width: 100%; padding: 10px 6px 9px 28px; font-size: 14.5px; font-family: inherit;
            color: #16233c; background: transparent; border: none; outline: none;
            border-bottom: 1px solid rgba(22, 35, 60, .2);
            transition: border-color .3s, box-shadow .3s
        }

        .input-wrap input::placeholder { color: rgba(22, 35, 60, .3) }

        .input-wrap input:focus {
            border-bottom-color: rgba(56, 170, 180, .9);
            box-shadow: 0 1px 0 0 rgba(56, 170, 180, .45)
        }

        .input-wrap input.has-error { border-bottom-color: rgba(224, 86, 110, .85) }

        .field-error { color: #d94560; font-size: 12px; margin-top: 6px }

        .btn {
            position: relative; overflow: hidden; width: 100%; margin-top: 26px;
            padding: 12px 16px; font-family: inherit; font-size: 12.5px; font-weight: 700;
            letter-spacing: 2px; text-transform: uppercase; color: #fff;
            background: linear-gradient(135deg, #38aab4, #2b7fb8); border: none;
            border-radius: 8px; cursor: pointer;
            box-shadow: 0 10px 24px rgba(56, 170, 180, .35);
            display: flex; align-items: center; justify-content: center; gap: 10px;
            transition: background .3s, box-shadow .3s, transform .2s, opacity .3s;
            animation: fieldIn .7s .5s cubic-bezier(.2, .9, .3, 1) both
        }

        .btn::before {
            content: ''; position: absolute; top: 0; left: -80%; width: 50%; height: 100%;
            background: linear-gradient(100deg, transparent, rgba(255, 255, 255, .35), transparent);
            transform: skewX(-20deg); transition: left .55s ease
        }

        .btn:hover {
            background: linear-gradient(135deg, #41bcc7, #3290c9);
            box-shadow: 0 16px 30px rgba(56, 170, 180, .45); transform: translateY(-1px)
        }

        .btn:hover::before { left: 130% }

        .btn:active { transform: scale(.98) }

        .btn.loading { pointer-events: none; opacity: .6 }

        .btn .spinner {
            display: none; width: 14px; height: 14px;
            border: 2px solid rgba(255, 255, 255, .35); border-top-color: #fff;
            border-radius: 50%; animation: spin .7s linear infinite
        }

        .btn.loading .spinner { display: inline-block }

        @keyframes spin { to { transform: rotate(360deg) } }

        .links {
            margin-top: 20px; display: flex; justify-content: center;
            font-size: 12px; color: rgba(22, 35, 60, .45);
            animation: fieldIn .7s .6s cubic-bezier(.2, .9, .3, 1) both
        }

        .links a {
            color: rgba(43, 127, 184, .9); text-decoration: none;
            font-weight: 600; transition: color .3s
        }

        .links a:hover { color: #16233c }

        /* success state */
        #okArea { display: none }

        .ok-circle { width: 74px; height: 74px; margin: 4px auto 18px }
        .ok-circle svg { width: 100%; height: 100% }
        .ok-circle circle {
            stroke: #2ec181; stroke-width: 3; fill: none;
            stroke-dasharray: 230; stroke-dashoffset: 230;
            animation: draw .8s ease forwards
        }
        .ok-circle path {
            stroke: #2ec181; stroke-width: 4; fill: none;
            stroke-linecap: round; stroke-linejoin: round;
            stroke-dasharray: 60; stroke-dashoffset: 60;
            animation: draw .5s ease .55s forwards
        }

        @keyframes draw { to { stroke-dashoffset: 0 } }

        .ok-area h3 { font-size: 19px; font-weight: 800; margin-bottom: 8px }
        .ok-area p { font-size: 13.5px; line-height: 1.7; color: rgba(22, 35, 60, .65) }
        .ok-area p b { word-break: break-all }
        .ok-mail { font-size: 34px; margin-top: 14px; animation: float 4s ease-in-out infinite }
    </style>
    <script src="{{ asset('js/galaxy-background.js') }}"></script>
</head>

<body>

    <canvas id="galaxy"></canvas>
    <div class="vignette"></div>

    <div class="stage">
        <div class="panel">

            <!-- FORM STATE -->
            <div id="formArea">
                <div class="key-logo">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="7.5" cy="15.5" r="4.5" />
                        <path d="m11 12 9-9M17 6l3 3M14 9l2 2" />
                    </svg>
                </div>

                <p class="brand">{{ config('app.name', 'App') }}</p>
                <p class="heading">Password Recovery</p>
                <p class="sub">Enter your account email and we'll send you a secure link to choose a new password.</p>

                <form method="POST" action="{{ route('password.email') }}" id="fpForm">
                    @csrf

                    <div class="field">
                        <label for="email">Email</label>
                        <div class="input-wrap">
                            <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="5" width="18" height="14" rx="3" />
                                <path d="m3 7 9 6 9-6" />
                            </svg>
                            <input id="email" type="email" name="email" value="{{ old('email') }}"
                                placeholder="you@example.com" required autofocus>
                        </div>
                    </div>

                    <button type="submit" class="btn" id="fpBtn">
                        <span class="spinner"></span>
                        <span>Send Reset Link</span>
                    </button>
                </form>

                <div class="links">
                    <a href="{{ route('login') }}">← Back to Login</a>
                </div>
            </div>

            <!-- SUCCESS STATE -->
            <div id="okArea" class="ok-area">
                <div class="ok-circle">
                    <svg viewBox="0 0 52 52">
                        <circle cx="26" cy="26" r="24" />
                        <path d="M15 27l7 7 15-15" />
                    </svg>
                </div>
                <h3>Check your inbox!</h3>
                <p>If an account exists for <b id="okEmail"></b>, a password reset link is on its way.</p>
                <div class="ok-mail">📬</div>
                <div class="links" style="margin-top:20px">
                    <a href="{{ route('login') }}">← Back to Login</a>
                </div>
            </div>

        </div>
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
                    cx = W / 2; cy = H / 2;
                    R = Math.min(W, H) * (o.radius ?? .44)
                };
                resize();
                window.addEventListener('resize', resize);

                const N = o.count ?? 850, pts = [], GA = Math.PI * (3 - Math.sqrt(5));
                for (let i = 0; i < N; i++) {
                    const y = 1 - (i / (N - 1)) * 2,
                        r = Math.sqrt(Math.max(0, 1 - y * y)),
                        t = GA * i, roll = Math.random();
                    pts.push({
                        x: Math.cos(t) * r * rand(.93, 1),
                        y: y * rand(.95, 1),
                        z: Math.sin(t) * r * rand(.93, 1),
                        r: rand(.5, 1.5),
                        c: roll < .78 ? '56,170,180' : roll < .93 ? '125,219,226' : '255,122,158',
                        tw: rand(0, TAU)
                    });
                }
                const TH = .23, TH2 = TH * TH;
                let pairs = [];
                for (let i = 0; i < N; i++)
                    for (let j = i + 1; j < N; j++) {
                        const dx = pts[i].x - pts[j].x, dy = pts[i].y - pts[j].y, dz = pts[i].z - pts[j].z;
                        if (dx * dx + dy * dy + dz * dz < TH2) pairs.push(i * N + j);
                    }
                for (let i = pairs.length - 1; i > 0; i--) {
                    const j = (Math.random() * (i + 1)) | 0;
                    [pairs[i], pairs[j]] = [pairs[j], pairs[i]]
                }
                pairs = pairs.slice(0, o.maxLines ?? 1100);

                const dust = [];
                for (let i = 0; i < (o.dust ?? 130); i++) dust.push({
                    x: Math.random(), y: Math.random(), r: rand(.3, 1.3),
                    a: rand(.05, .4), vx: rand(-.004, .004), vy: rand(-.004, .004)
                });

                const orbs = [{ rr: .5, tilt: .55, sp: .00042, ph: 1.1 }, { rr: .8, tilt: -.85, sp: -.00027, ph: 3.9 }];
                const FOV = 2.6;
                let rotY = rand(0, TAU), rotX = -.32;
                const mouse = { x: 0, y: 0 };
                window.addEventListener('mousemove', e => {
                    mouse.x = e.clientX / window.innerWidth - .5;
                    mouse.y = e.clientY / window.innerHeight - .5
                });

                const glow = (x, y, r, rgb, a) => {
                    if (r <= 0 || a <= 0) return;
                    const g = ctx.createRadialGradient(x, y, 0, x, y, r);
                    g.addColorStop(0, 'rgba(' + rgb + ',' + a + ')');
                    g.addColorStop(1, 'rgba(' + rgb + ',0)');
                    ctx.fillStyle = g; ctx.beginPath(); ctx.arc(x, y, r, 0, TAU); ctx.fill()
                };

                const proj = new Array(N);
                let raf, last = performance.now();
                let star = null, nextShoot = 2000;

                const frame = now => {
                    const dt = Math.min(50, now - last);
                    last = now;
                    rotY += .000105 * dt;
                    rotX += ((-.32 + mouse.y * .3) - rotX) * .04;
                    const yOff = mouse.x * .35, sy = Math.sin(rotY + yOff), cyc = Math.cos(rotY + yOff);
                    const sxr = Math.sin(rotX), cxr = Math.cos(rotX);
                    ctx.clearRect(0, 0, W, H);

                    for (const d of dust) {
                        d.x += d.vx * dt; d.y += d.vy * dt;
                        if (d.x < 0) d.x += 1; if (d.x > 1) d.x -= 1;
                        if (d.y < 0) d.y += 1; if (d.y > 1) d.y -= 1;
                        ctx.fillStyle = 'rgba(56,170,180,' + d.a + ')';
                        ctx.beginPath(); ctx.arc(d.x * W, d.y * H, d.r, 0, TAU); ctx.fill()
                    }

                    ctx.globalCompositeOperation = 'source-over';
                    glow(cx, cy, R * 1.3, '56,170,180', .1);
                    const pulse = 1 + Math.sin(now * .0011) * .1;
                    glow(cx, cy, R * .5 * pulse, '125,219,226', .14);
                    glow(cx, cy, R * .16 * pulse, '255,255,255', .5);

                    for (let i = 0; i < N; i++) {
                        const p = pts[i];
                        const x1 = p.x * cyc - p.z * sy, z1 = p.x * sy + p.z * cyc;
                        const y2 = p.y * cxr - z1 * sxr, z2 = p.y * sxr + z1 * cxr;
                        const f = FOV / (FOV + z2);
                        proj[i] = { x: cx + x1 * R * f, y: cy + y2 * R * f, b: (f - .68) / .98, tw: p.tw + now * .0016 }
                    }

                    ctx.lineWidth = .55;
                    for (const code of pairs) {
                        const i = (code / N) | 0, j = code % N, a = proj[i], b2 = proj[j];
                        const al = ((a.b + b2.b) / 2) * .17;
                        if (al < .01) continue;
                        ctx.strokeStyle = 'rgba(43,127,184,' + al.toFixed(3) + ')';
                        ctx.beginPath(); ctx.moveTo(a.x, a.y); ctx.lineTo(b2.x, b2.y); ctx.stroke()
                    }

                    for (let i = 0; i < N; i++) {
                        const q = proj[i], p = pts[i], tw = .7 + .3 * Math.sin(q.tw);
                        const al = (.1 + q.b * .8) * tw;
                        ctx.fillStyle = 'rgba(' + p.c + ',' + al.toFixed(3) + ')';
                        ctx.beginPath(); ctx.arc(q.x, q.y, p.r * (.45 + q.b), 0, TAU); ctx.fill()
                    }

                    for (const ob of orbs) {
                        const a = ob.ph + ob.sp * now;
                        let px = Math.cos(a) * ob.rr, py = 0, pz = Math.sin(a) * ob.rr;
                        let y1 = py * Math.cos(ob.tilt) - pz * Math.sin(ob.tilt),
                            z1 = py * Math.sin(ob.tilt) + pz * Math.cos(ob.tilt);
                        py = y1; pz = z1;
                        const x1 = px * cyc - pz * sy, z2 = px * sy + pz * cyc;
                        const y2 = py * cxr - z2 * sxr, z3 = py * sxr + z2 * cxr, f = FOV / (FOV + z3);
                        const ox = cx + x1 * R * f, oy = cy + y2 * R * f;
                        glow(ox, oy, 26 * f, '255,122,158', .3);
                        glow(ox, oy, 8 * f, '255,190,205', .65);
                        ctx.fillStyle = 'rgba(255,240,244,.95)';
                        ctx.beginPath(); ctx.arc(ox, oy, 2.1 * f, 0, TAU); ctx.fill()
                    }

                    nextShoot -= dt;
                    if (nextShoot < 0 && !star) {
                        star = {
                            x: rand(.15, .85) * W, y: rand(.05, .3) * H,
                            vx: (rand(6, 10)) * (Math.random() < .5 ? -1 : 1),
                            vy: rand(2.5, 4.5), l: 1
                        };
                        nextShoot = 3500 + Math.random() * 4500;
                    }
                    if (star) {
                        star.x += star.vx * dt * .06; star.y += star.vy * dt * .06;
                        star.l -= dt * .0011;
                        const g = ctx.createLinearGradient(star.x, star.y, star.x - star.vx * 11, star.y - star.vy * 11);
                        g.addColorStop(0, 'rgba(56,170,180,' + (.8 * Math.max(star.l, 0)).toFixed(3) + ')');
                        g.addColorStop(1, 'rgba(56,170,180,0)');
                        ctx.strokeStyle = g; ctx.lineWidth = 1.6;
                        ctx.beginPath(); ctx.moveTo(star.x, star.y);
                        ctx.lineTo(star.x - star.vx * 11, star.y - star.vy * 11); ctx.stroke();
                        if (star.l <= 0 || star.x < -100 || star.x > W + 100) star = null;
                    }

                    raf = requestAnimationFrame(frame);
                };
                raf = requestAnimationFrame(frame);
                return { stop: () => cancelAnimationFrame(raf) };
            }

            Galaxy(document.getElementById('galaxy'), { radius: .46 });

            /* ── AJAX forgot-password ── */
            const form = document.getElementById('fpForm');
            const btn = document.getElementById('fpBtn');

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                btn.classList.add('loading');
                form.querySelectorAll('.field-error').forEach(el => el.remove());
                form.querySelectorAll('.has-error').forEach(el => el.classList.remove('has-error'));

                try {
                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
                        },
                        body: new FormData(form),
                        credentials: 'same-origin'
                    });

                    if (res.status === 422) {
                        const data = await res.json().catch(() => ({}));
                        (Object.entries(data.errors || {})).forEach(([f, msgs]) => {
                            const input = form.querySelector(`[name="${f}"]`);
                            if (!input) return;
                            input.classList.add('has-error');
                            const p = document.createElement('p');
                            p.className = 'field-error';
                            p.textContent = Array.isArray(msgs) ? msgs[0] : msgs;
                            input.closest('.field').appendChild(p);
                        });
                        btn.classList.remove('loading');
                        return;
                    }

                    if (res.ok) {
                        /* success — swap to the "check your inbox" state */
                        document.getElementById('okEmail').textContent = form.email.value.trim();
                        document.getElementById('formArea').style.display = 'none';
                        document.getElementById('okArea').style.display = 'block';
                    }
                } catch (err) { /* keep silent, re-enable */ }

                btn.classList.remove('loading');
            });
        })();
    </script>
    <script src="{{ asset('js/ajax-navigation.js') }}"></script>
</body>

</html>