{{-- ==========================================================
     ADMIN · PAYMENT SETTINGS — v5.7 (Stripe + Paystack + Currency/Rate · FULL-WIDTH)
========================================================== --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0e5a75">
    <title>NiceDash — Payment Settings</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}?v=3">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box
        }

        :root {
            --bg: #f3f5f7;
            --card: #ffffff;
            --text: #101828;
            --muted: #68788f;
            --border: #e8edf3;
            --forest: #0e5a75;
            --forest-2: #17739b;
            --lime: #3ec5e0;
            --lime-soft: #e4f6fb;
            --shadow: 0 1px 2px rgba(16, 24, 40, .04), 0 8px 24px rgba(16, 24, 40, .05);
            --radius: 16px
        }

        body.dark {
            --bg: #0b1220;
            --card: #111c2e;
            --text: #e7edf5;
            --muted: #8ba0b8;
            --border: #1e2c42;
            --lime-soft: rgba(62, 197, 224, .08);
            --shadow: 0 1px 2px rgba(0, 0, 0, .3), 0 10px 30px rgba(0, 0, 0, .35)
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            font-size: 14px;
            transition: background .4s, color .4s;
            -webkit-text-size-adjust: 100%;
            -webkit-tap-highlight-color: transparent;
            overflow-x: clip
        }

        ::-webkit-scrollbar {
            width: 9px;
            height: 9px
        }

        ::-webkit-scrollbar-thumb {
            background: #c7d2de;
            border-radius: 10px;
            border: 2px solid var(--bg)
        }

        body.dark ::-webkit-scrollbar-thumb {
            background: #24334d
        }

        button {
            font-family: inherit;
            user-select: none
        }

        .layout {
            min-height: 100vh
        }

        .main {
            margin-left: 268px;
            min-width: 0;
            transition: margin .35s cubic-bezier(.4, 0, .2, 1)
        }

        .layout.collapsed .main {
            margin-left: 84px
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 268px;
            background: var(--card);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            z-index: 50;
            transition: width .35s cubic-bezier(.4, 0, .2, 1), transform .35s cubic-bezier(.4, 0, .2, 1)
        }

        .layout.collapsed .sidebar {
            width: 84px
        }

        .s-nav {
            flex: 1;
            overflow-y: auto;
            padding: 12px 14px
        }

        .backdrop {
            position: fixed;
            inset: 0;
            background: rgba(2, 6, 23, .55);
            z-index: 45;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: .3s
        }

        .backdrop.show {
            opacity: 1;
            visibility: visible;
            pointer-events: auto
        }

        .content {
            padding: 24px 26px calc(40px + env(safe-area-inset-bottom));
            display: grid;
            gap: 22px;
            min-width: 0
        }

        /* ★ FULL WIDTH */
        .ps-wrap {
            width: 100%;
            display: grid;
            gap: 22px;
            min-width: 0
        }

        .page-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
            width: 100%
        }

        .page-head h2 {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -.4px
        }

        .page-head p {
            color: var(--muted);
            font-size: 13px;
            margin-top: 3px
        }

        .chip {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .4px
        }

        .chip::before {
            content: '';
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: currentColor;
            box-shadow: 0 0 8px currentColor
        }

        .chip.on {
            background: #dcfce7;
            color: #16a34a
        }

        .chip.offchip {
            background: #fee2e2;
            color: #dc2626
        }

        body.dark .chip.on {
            background: rgba(74, 222, 128, .12);
            color: #4ade80
        }

        body.dark .chip.offchip {
            background: rgba(248, 113, 113, .12);
            color: #f87171
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: background .4s;
            min-width: 0
        }

        .ps-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            padding: 20px 26px;
            border-bottom: 1px solid var(--border)
        }

        .ps-head h3 {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -.3px
        }

        .ps-head p {
            font-size: 12.5px;
            color: var(--muted);
            margin-top: 3px
        }

        .ps-body {
            padding: 26px 28px 28px;
            display: grid;
            gap: 20px;
            min-width: 0
        }

        .fl label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 8px
        }

        .frow2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            align-items: start
        }

        .in-wrap {
            position: relative
        }

        .in-wrap input {
            width: 100%;
            min-height: 50px;
            border: 1.5px solid var(--border);
            background: var(--bg);
            border-radius: 12px;
            padding: 13px 48px 13px 16px;
            font: 600 13px 'SFMono-Regular', Consolas, monospace;
            letter-spacing: .4px;
            color: var(--text);
            outline: none;
            transition: border-color .25s, box-shadow .25s, background .25s;
            white-space: nowrap;
            overflow-x: auto
        }

        .in-wrap input::placeholder {
            color: #9fb0c0;
            font-weight: 500;
            letter-spacing: 0
        }

        body.dark .in-wrap input::placeholder {
            color: #5c718c
        }

        .in-wrap input:hover {
            border-color: #d3deea
        }

        .in-wrap input:focus {
            border-color: var(--lime);
            background: var(--card);
            box-shadow: 0 0 0 3px rgba(62, 197, 224, .12)
        }

        /* ⭐ SELECT (currency) */
        .in-wrap select {
            width: 100%;
            min-height: 50px;
            border: 1.5px solid var(--border);
            background: var(--bg);
            border-radius: 12px;
            padding: 13px 16px;
            font: 600 13px 'Plus Jakarta Sans', sans-serif;
            color: var(--text);
            outline: none;
            cursor: pointer
        }

        .in-wrap select:focus {
            border-color: var(--lime);
            background: var(--card);
            box-shadow: 0 0 0 3px rgba(62, 197, 224, .12)
        }

        .in-wrap .eye {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            width: 34px;
            height: 34px;
            border: none;
            background: transparent;
            border-radius: 9px;
            cursor: pointer;
            color: var(--muted);
            font-size: 16px;
            transition: .2s;
            display: grid;
            place-items: center
        }

        @media (hover:hover) {
            .in-wrap .eye:hover {
                background: var(--lime-soft);
                color: var(--forest)
            }
        }

        body.dark .in-wrap .eye:hover {
            color: var(--lime)
        }

        .mask-chip {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: var(--lime-soft);
            color: var(--forest);
            font: 700 10.5px 'SFMono-Regular', Consolas, monospace;
            padding: 5px 11px;
            border-radius: 9px;
            max-width: 300px;
            overflow: hidden;
            white-space: nowrap
        }

        body.dark .mask-chip {
            color: var(--lime)
        }

        .mask-chip i {
            font-size: 13px;
            flex-shrink: 0
        }

        .key-status {
            display: flex;
            gap: 8px;
            flex-wrap: wrap
        }

        .ks-item {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 11px;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 20px;
            background: var(--bg);
            border: 1px solid var(--border);
            color: var(--muted);
            transition: .3s
        }

        .ks-item i {
            font-size: 13px
        }

        .ks-item.ok {
            background: #dcfce7;
            border-color: transparent;
            color: #16a34a
        }

        body.dark .ks-item.ok {
            background: rgba(74, 222, 128, .12);
            color: #4ade80
        }

        .hint {
            font-size: 11.5px;
            color: var(--muted);
            line-height: 1.65;
            background: var(--bg);
            border: 1px dashed var(--border);
            padding: 11px 15px;
            border-radius: 11px
        }

        .hint b {
            color: var(--text)
        }

        .hint i {
            vertical-align: -2px
        }

        .btn-save {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            width: 100%;
            background: linear-gradient(135deg, var(--forest), var(--forest-2));
            color: #fff;
            border: none;
            padding: 15px 22px;
            border-radius: 13px;
            font-weight: 800;
            font-size: 13.5px;
            letter-spacing: .4px;
            cursor: pointer;
            transition: .25s;
            box-shadow: 0 8px 20px rgba(14, 90, 117, .35);
            position: relative;
            overflow: hidden
        }

        .btn-save:disabled {
            opacity: .6;
            cursor: wait;
            transform: none !important
        }

        .btn-save::after {
            content: '';
            position: absolute;
            top: 0;
            left: -80%;
            width: 50%;
            height: 100%;
            background: linear-gradient(100deg, transparent, rgba(255, 255, 255, .25), transparent);
            transform: skewX(-20deg);
            transition: left .6s ease
        }

        @media (hover:hover) {
            .btn-save:hover::after {
                left: 130%
            }
        }

        body.dark .btn-save {
            background: linear-gradient(135deg, var(--lime), #7edcf2);
            color: #073344;
            box-shadow: 0 8px 20px rgba(62, 197, 224, .35)
        }

        @media (hover:hover) {
            .btn-save:hover {
                transform: translateY(-2px);
                box-shadow: 0 14px 28px rgba(14, 90, 117, .45)
            }

            body.dark .btn-save:hover {
                box-shadow: 0 14px 28px rgba(62, 197, 224, .45)
            }
        }

        .btn-save:active {
            transform: scale(.98)
        }

        .reveal {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity .45s ease, transform .45s cubic-bezier(.22, 1, .36, 1)
        }

        .reveal.show {
            opacity: 1;
            transform: none;
            margin-top: 25px;
        }

        /* ================= RESPONSIVE ================= */
        @media(max-width:992px) {
            .sidebar {
                transform: translateX(-105%);
                width: 268px !important
            }

            .layout.mobile-open .sidebar {
                transform: none
            }

            .main {
                margin-left: 0 !important
            }

            .content {
                padding: 18px 14px calc(40px + env(safe-area-inset-bottom))
            }
        }

        @media(max-width:680px) {
            .frow2 {
                grid-template-columns: 1fr;
                gap: 14px
            }

            .page-head h2 {
                font-size: 20px
            }

            .ps-head {
                padding: 18px 18px
            }

            .ps-body {
                padding: 20px 16px 22px
            }

            .in-wrap input {
                font-size: 16px;
                min-height: 52px
            }

            .in-wrap select {
                font-size: 16px;
                min-height: 52px
            }

            .mask-chip {
                max-width: 200px
            }

            .btn-save {
                padding: 14px 18px
            }
        }

        @media(max-width:480px) {
            .chip {
                font-size: 11px;
                padding: 6px 12px
            }

            .hint {
                font-size: 11px;
                padding: 10px 12px
            }
        }
    </style>
</head>

<body>
    <div class="layout" id="layout">
        @include('admin.include.sidebar')
        <div class="main">
            @include('admin.include.navbar')
            <main class="content">

                <div class="page-head reveal">
                    <div>
                        <h2>Payment Settings</h2>
                        <p>Stripe &amp; Paystack API keys — saved encrypted in the database, never in .env</p>
                    </div>
                    <span class="chip {{ ($stripeActive || $paystackActive) ? 'on' : 'offchip' }}">Payment Gateway</span>
                </div>

                <div class="ps-wrap">

                    <!-- ================= STRIPE ================= -->
                    <div class="card reveal">
                        <div class="ps-head">
                            <div>
                                <h3>Stripe</h3>
                                <p>Credit &amp; debit cards — Visa, Mastercard, Amex</p>
                            </div>
                            <span class="chip {{ $stripeActive ? 'on' : 'offchip' }}" id="chipStripe2">{{ $stripeActive ? 'Connected' : 'Offline' }}</span>
                        </div>
                        <div class="ps-body">

                            <div class="frow2">
                                <div class="fl">
                                    <label><span>Publishable Key</span></label>
                                    <div class="in-wrap">
                                        <input type="text" id="stripeKey" value="{{ $stripeKey }}" placeholder="pk_test_…" spellcheck="false">
                                    </div>
                                </div>
                                <div class="fl">
                                    <label><span>Secret Key</span>@if($stripeMask)<span class="mask-chip"><i class="ri-shield-keyhole-line"></i>{{ $stripeMask }}</span>@endif</label>
                                    <div class="in-wrap">
                                        <input type="password" id="stripeSecret" placeholder="{{ $stripeMask ? 'Type new key to replace…' : 'sk_test_…' }}" autocomplete="new-password" spellcheck="false">
                                        <button type="button" class="eye" onclick="togglePw('stripeSecret', this)"><i class="ri-eye-line"></i></button>
                                    </div>
                                </div>
                            </div>

                            <div class="key-status">
                                <span class="ks-item {{ $stripeKey ? 'ok' : '' }}" id="ksPub">
                                    <i class="{{ $stripeKey ? 'ri-checkbox-circle-fill' : 'ri-close-circle-line' }}"></i>
                                    {{ $stripeKey ? 'Publishable key saved' : 'No publishable key' }}
                                </span>
                                <span class="ks-item {{ $stripeMask ? 'ok' : '' }}" id="ksSec">
                                    <i class="{{ $stripeMask ? 'ri-checkbox-circle-fill' : 'ri-close-circle-line' }}"></i>
                                    {{ $stripeMask ? 'Secret key saved · encrypted' : 'No secret key' }}
                                </span>
                            </div>

                            <p class="hint"><i class="ri-information-line"></i> Get them from <b>dashboard.stripe.com → Developers → API keys</b>. Leaving a field empty keeps the existing key.</p>
                            <button type="button" class="btn-save" onclick="saveKeys()"><i class="ri-save-3-line"></i>Save Stripe Keys</button>
                        </div>
                    </div>

                    <!-- ================= ⭐ PAYSTACK ================= -->
                    <div class="card reveal">
                        <div class="ps-head">
                            <div>
                                <h3>Paystack</h3>
                                <p>Card, bank transfer &amp; more — Visa, Mastercard, Verve</p>
                            </div>
                            <span class="chip {{ $paystackActive ? 'on' : 'offchip' }}" id="chipPs">{{ $paystackActive ? 'Connected' : 'Offline' }}</span>
                        </div>
                        <div class="ps-body">

                            <div class="frow2">
                                <div class="fl">
                                    <label><span>Public Key</span></label>
                                    <div class="in-wrap">
                                        <input type="text" id="psKey" value="{{ $paystackKey }}" placeholder="pk_test_…" spellcheck="false">
                                    </div>
                                </div>
                                <div class="fl">
                                    <label><span>Secret Key</span>@if($paystackMask)<span class="mask-chip"><i class="ri-shield-keyhole-line"></i>{{ $paystackMask }}</span>@endif</label>
                                    <div class="in-wrap">
                                        <input type="password" id="psSecret" placeholder="{{ $paystackMask ? 'Type new key to replace…' : 'sk_test_…' }}" autocomplete="new-password" spellcheck="false">
                                        <button type="button" class="eye" onclick="togglePw('psSecret', this)"><i class="ri-eye-line"></i></button>
                                    </div>
                                </div>
                            </div>

                            <!-- ⭐ CURRENCY + RATE (GBP Paystack support nahi karta) -->
                            <div class="frow2">
                                <div class="fl">
                                    <label><span>Charge Currency</span></label>
                                    <div class="in-wrap">
                                        <select id="psCurrency">
                                            <option value="AUTO" {{ ($paystackCurrency ?? 'AUTO') === 'AUTO' ? 'selected' : '' }}>AUTO — Merchant Default (recommended)</option>
                                            @foreach(['NGN' => 'NGN — Nigerian Naira ₦', 'GHS' => 'GHS — Ghana Cedi ₵', 'ZAR' => 'ZAR — SA Rand R', 'KES' => 'KES — Kenyan Shilling KSh', 'USD' => 'USD — US Dollar $'] as $code => $label)
                                            <option value="{{ $code }}" {{ $paystackCurrency === $code ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="fl">
                                    <label><span>Rate (1 £ = ? currency)</span></label>
                                    <div class="in-wrap">
                                        <input type="number" id="psRate" step="0.01" min="0.01" value="{{ $paystackRate }}" placeholder="e.g. 2000 for NGN · 1.27 for USD">
                                    </div>
                                </div>
                            </div>

                            <div class="key-status">
                                <span class="ks-item {{ $paystackKey ? 'ok' : '' }}" id="ksPsPub">
                                    <i class="{{ $paystackKey ? 'ri-checkbox-circle-fill' : 'ri-close-circle-line' }}"></i>
                                    {{ $paystackKey ? 'Public key saved' : 'No public key' }}
                                </span>
                                <span class="ks-item {{ $paystackMask ? 'ok' : '' }}" id="ksPsSec">
                                    <i class="{{ $paystackMask ? 'ri-checkbox-circle-fill' : 'ri-close-circle-line' }}"></i>
                                    {{ $paystackMask ? 'Secret key saved · encrypted' : 'No secret key' }}
                                </span>
                            </div>

                            <p class="hint"><i class="ri-information-line"></i> Get keys from <b>dashboard.paystack.com Settings - API Keys &amp; Webhooks</b>. Pair <code>pk_test_...</code> with <code>sk_test_...</code>, or use both keys in live mode. Enter the complete actual keys, not setup instructions such as &quot;Authorization Bearer [secret key]&quot;. The server creates the authorization header and checks that both keys use the same mode before saving. Paystack does not support GBP; set the charge currency and conversion rate. The secret key is encrypted in the database. Leaving a field empty keeps its existing value.</p>
                            <button type="button" class="btn-save" id="btnPs" onclick="savePaystack()"><i class="ri-save-3-line"></i>Save Paystack Settings</button>
                        </div>
                    </div>

                </div>

            </main>
        </div>
    </div>

    <script>
        var payCsrf = document.querySelector('meta[name="csrf-token"]').content;
        var PUB_SAVED = @json(!empty($stripeKey));
        var PS_PUB_SAVED = @json(!empty($paystackKey));
        var PS_CURRENCY_SAVED = @json($paystackCurrency ?? 'AUTO');

        function togglePw(id, btn) {
            var inp = document.getElementById(id);
            var show = inp.type === 'password';
            inp.type = show ? 'text' : 'password';
            btn.innerHTML = '<i class="ri-eye' + (show ? '-off' : '') + '-line"></i>';
        }

        function saveSwal(icon, title, text) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: icon === 'error' ? 3600 : 2800,
                timerProgressBar: true,
                icon: icon,
                title: text || title,
                background: '#111c2e',
                color: '#e7edf5',
            });
        }

        /* ---------- STRIPE SAVE ---------- */
        function saveKeys() {
            var btn = document.querySelector('.btn-save');
            var k = document.getElementById('stripeKey').value.trim();
            var s = document.getElementById('stripeSecret').value.trim();

            if (!k && !s) {
                saveSwal('info', 'Nothing to Save', 'Type at least one key first.');
                return;
            }

            var fd = new FormData();
            fd.append('_method', 'PATCH');
            fd.append('_token', payCsrf);
            if (k) fd.append('stripe_key', k);
            if (s) fd.append('stripe_secret', s);

            btn.disabled = true;
            fetch("{{ route('admin.payments.update') }}", {
                    method: 'POST',
                    body: fd,
                    headers: {
                        'X-CSRF-TOKEN': payCsrf,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(function(r) {
                    return r.json().catch(function() {
                        return {};
                    }).then(function(d) {
                        if (r.status === 419) throw new Error('Session expired — please refresh the page.');
                        if (!r.ok) throw new Error(d.message || 'Request failed (' + r.status + ')');
                        return d;
                    });
                })
                .then(function(d) {
                    if (d.status === 'success') {
                        var chip2 = document.getElementById('chipStripe2');
                        chip2.textContent = d.stripeActive ? 'Connected' : 'Offline';
                        chip2.className = 'chip ' + (d.stripeActive ? 'on' : 'offchip');

                        var pub = document.getElementById('ksPub');
                        var sec = document.getElementById('ksSec');
                        var hasPub = k.length > 0 || PUB_SAVED;
                        var hasSec = d.stripeMask || null;
                        pub.className = 'ks-item' + (hasPub ? ' ok' : '');
                        pub.innerHTML = '<i class="' + (hasPub ? 'ri-checkbox-circle-fill' : 'ri-close-circle-line') + '"></i>' + (hasPub ? 'Publishable key saved' : 'No publishable key');
                        sec.className = 'ks-item' + (hasSec ? ' ok' : '');
                        sec.innerHTML = '<i class="' + (hasSec ? 'ri-checkbox-circle-fill' : 'ri-close-circle-line') + '"></i>' + (hasSec ? 'Secret key saved · encrypted' : 'No secret key');

                        PUB_SAVED = PUB_SAVED || k.length > 0;
                        document.getElementById('stripeSecret').value = '';
                        saveSwal('success', 'Saved & Encrypted 🔐', d.message);
                    } else {
                        saveSwal('error', 'Not Saved', d.message || 'Could not save keys.');
                    }
                })
                .catch(function(err) {
                    saveSwal('error', 'Error', err.message || 'Something went wrong.');
                })
                .finally(function() {
                    btn.disabled = false;
                });
        }

        /* ---------- ⭐ PAYSTACK SAVE (keys + currency + rate) ---------- */
        function savePaystack() {
            var btn = document.getElementById('btnPs');
            var k = document.getElementById('psKey').value.trim();
            var s = document.getElementById('psSecret').value.trim();
            var cur = document.getElementById('psCurrency').value;
            var rate = document.getElementById('psRate').value.trim();

            if (!k && !s && !rate && cur === PS_CURRENCY_SAVED) {
                saveSwal('info', 'Nothing to Save', 'Type at least one key or rate first.');
                return;
            }

            var fd = new FormData();
            fd.append('_method', 'PATCH');
            fd.append('_token', payCsrf);
            fd.append('paystack_currency', cur);
            if (k) fd.append('paystack_key', k);
            if (s) fd.append('paystack_secret', s);
            if (rate) fd.append('paystack_rate', rate);

            btn.disabled = true;
            fetch("{{ route('admin.payments.update') }}", {
                    method: 'POST',
                    body: fd,
                    headers: {
                        'X-CSRF-TOKEN': payCsrf,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(function(r) {
                    return r.json().catch(function() {
                        return {};
                    }).then(function(d) {
                        if (r.status === 419) throw new Error('Session expired — please refresh the page.');
                        if (!r.ok) throw new Error(d.message || 'Request failed (' + r.status + ')');
                        return d;
                    });
                })
                .then(function(d) {
                    if (d.status === 'success') {
                        var chip = document.getElementById('chipPs');
                        chip.textContent = d.paystackActive ? 'Connected' : 'Offline';
                        chip.className = 'chip ' + (d.paystackActive ? 'on' : 'offchip');

                        var pub = document.getElementById('ksPsPub');
                        var sec = document.getElementById('ksPsSec');
                        var hasPub = k.length > 0 || PS_PUB_SAVED;
                        var hasSec = d.paystackMask || null;
                        pub.className = 'ks-item' + (hasPub ? ' ok' : '');
                        pub.innerHTML = '<i class="' + (hasPub ? 'ri-checkbox-circle-fill' : 'ri-close-circle-line') + '"></i>' + (hasPub ? 'Public key saved' : 'No public key');
                        sec.className = 'ks-item' + (hasSec ? ' ok' : '');
                        sec.innerHTML = '<i class="' + (hasSec ? 'ri-checkbox-circle-fill' : 'ri-close-circle-line') + '"></i>' + (hasSec ? 'Secret key saved · encrypted' : 'No secret key');

                        PS_PUB_SAVED = PS_PUB_SAVED || k.length > 0;
                        PS_CURRENCY_SAVED = cur;
                        document.getElementById('psSecret').value = '';
                        saveSwal('success', 'Saved & Encrypted 🔐', d.message + ' — charging in ' + cur);
                    } else {
                        saveSwal('error', 'Not Saved', d.message || 'Could not save settings.');
                    }
                })
                .catch(function(err) {
                    saveSwal('error', 'Error', err.message || 'Something went wrong.');
                })
                .finally(function() {
                    btn.disabled = false;
                });
        }

        (function() {
            var io = ('IntersectionObserver' in window) ? new IntersectionObserver(function(es) {
                es.forEach(function(e) {
                    if (e.isIntersecting) {
                        e.target.classList.add('show');
                        io.unobserve(e.target);
                    }
                });
            }, {
                threshold: .08
            }) : null;
            document.querySelectorAll('.reveal').forEach(function(el) {
                io ? io.observe(el) : el.classList.add('show');
            });
            setTimeout(function() {
                document.querySelectorAll('.reveal:not(.show)').forEach(function(el) {
                    el.classList.add('show');
                });
            }, 1200);
        })();
    </script>
</body>

</html>