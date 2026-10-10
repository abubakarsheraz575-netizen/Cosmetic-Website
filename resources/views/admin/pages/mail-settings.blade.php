{{-- ==========================================================
     ADMIN · MAIL SETTINGS — v5.5 (FULL-WIDTH layout · simple header · simple inputs)
========================================================== --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0e5a75">
    <title>NiceDash — Mail Settings</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}?v=3">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box }
        :root { --bg:#f3f5f7; --card:#ffffff; --text:#101828; --muted:#68788f; --border:#e8edf3; --forest:#0e5a75; --forest-2:#17739b; --lime:#3ec5e0; --lime-soft:#e4f6fb; --shadow:0 1px 2px rgba(16,24,40,.04),0 8px 24px rgba(16,24,40,.05); --radius:16px }
        body.dark { --bg:#0b1220; --card:#111c2e; --text:#e7edf5; --muted:#8ba0b8; --border:#1e2c42; --lime-soft:rgba(62,197,224,.08); --shadow:0 1px 2px rgba(0,0,0,.3),0 10px 30px rgba(0,0,0,.35) }
        body { font-family:'Plus Jakarta Sans',sans-serif; background:var(--bg); color:var(--text); font-size:14px; transition:background .4s,color .4s; -webkit-text-size-adjust:100%; -webkit-tap-highlight-color:transparent; overflow-x:clip }
        ::-webkit-scrollbar { width:9px; height:9px }
        ::-webkit-scrollbar-thumb { background:#c7d2de; border-radius:10px; border:2px solid var(--bg) }
        body.dark ::-webkit-scrollbar-thumb { background:#24334d }
        button { font-family:inherit; user-select:none }

        .layout { min-height:100vh }
        .main { margin-left:268px; min-width:0; transition:margin .35s cubic-bezier(.4,0,.2,1) }
        .layout.collapsed .main { margin-left:84px }
        .sidebar { position:fixed; left:0; top:0; bottom:0; width:268px; background:var(--card); border-right:1px solid var(--border); display:flex; flex-direction:column; z-index:50; transition:width .35s cubic-bezier(.4,0,.2,1),transform .35s cubic-bezier(.4,0,.2,1) }
        .layout.collapsed .sidebar { width:84px }
        .s-nav { flex:1; overflow-y:auto; padding:12px 14px }
        .backdrop { position:fixed; inset:0; background:rgba(2,6,23,.55); z-index:45; opacity:0; visibility:hidden; pointer-events:none; transition:.3s }
        .backdrop.show { opacity:1; visibility:visible; pointer-events:auto }
        .content { padding:24px 26px calc(40px + env(safe-area-inset-bottom)); display:grid; gap:22px; min-width:0 }

        /* ★ FULL WIDTH — koi 760px center-limit nahi */
        .ms-wrap { width:100%; display:grid; gap:22px; min-width:0 }
        .page-head { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:14px; width:100% }

        .page-head h2 { font-size:24px; font-weight:800; letter-spacing:-.4px; display:flex; align-items:center; gap:11px }
        .page-head p { color:var(--muted); font-size:13px; margin-top:3px }
        .chip { display:inline-flex; align-items:center; gap:7px; padding:7px 14px; border-radius:20px; font-size:12px; font-weight:800; letter-spacing:.4px }
        .chip::before { content:''; width:7px; height:7px; border-radius:50%; background:currentColor; box-shadow:0 0 8px currentColor }
        .chip.on { background:#dcfce7; color:#16a34a }
        .chip.offchip { background:#fee2e2; color:#dc2626 }
        body.dark .chip.on { background:rgba(74,222,128,.12); color:#4ade80 }
        body.dark .chip.offchip { background:rgba(248,113,113,.12); color:#f87171 }
        .card { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow); overflow:hidden; transition:background .4s; min-width:0 }

        /* ══════════ SIMPLE HEADER (payment page se match) ══════════ */
        .ms-head {
            display:flex; justify-content:space-between; align-items:center;
            flex-wrap:wrap; gap:12px;
            padding:20px 26px; border-bottom:1px solid var(--border)
        }
        .ms-head h3 { font-size:18px; font-weight:800; letter-spacing:-.3px }
        .ms-head p { font-size:12.5px; color:var(--muted); margin-top:3px }

        .ms-body { padding:26px 28px 28px; display:grid; gap:20px; min-width:0 }
        .sec-t { font-size:10.5px; font-weight:800; letter-spacing:.12em; text-transform:uppercase; color:var(--muted); display:flex; align-items:center; gap:7px; margin-top:2px }
        .sec-t i { font-size:14px; color:var(--lime) }
        .frow2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; align-items:start }
        .preset-row { display:flex; gap:8px; flex-wrap:wrap }
        .preset { display:inline-flex; align-items:center; gap:7px; padding:9px 15px; border-radius:11px; border:1px solid var(--border); background:var(--bg); font-size:12px; font-weight:800; color:var(--muted); cursor:pointer; transition:.25s }
        .preset i { font-size:15px }
        @media (hover:hover) { .preset:hover { border-color:var(--lime); background:var(--lime-soft); color:var(--forest); transform:translateY(-2px) } }
        .preset:active { transform:scale(.96) }
        .fl label { display:flex; justify-content:space-between; align-items:center; gap:10px; font-size:11px; font-weight:800; letter-spacing:.08em; text-transform:uppercase; color:var(--muted); margin-bottom:8px }

        /* ══════════ SIMPLE INPUTS (no icons inside — payment jaisa) ══════════ */
        .in-wrap { position:relative }
        .in-wrap input, .in-wrap select {
            width:100%; min-height:50px;
            border:1.5px solid var(--border); background:var(--bg);
            border-radius:12px; padding:13px 16px;
            font:600 13.5px 'Plus Jakarta Sans',sans-serif;
            color:var(--text); outline:none; appearance:none;
            transition:border-color .25s, box-shadow .25s, background .25s
        }
        .in-wrap input::placeholder { color:#9fb0c0; font-weight:500 }
        body.dark .in-wrap input::placeholder { color:#5c718c }
        .in-wrap input:hover, .in-wrap select:hover { border-color:#d3deea }
        .in-wrap input:focus, .in-wrap select:focus {
            border-color:var(--lime); background:var(--card);
            box-shadow:0 0 0 3px rgba(62,197,224,.12)
        }
        .in-wrap select { cursor:pointer; padding-right:42px }
        .in-wrap .sel-arrow { position:absolute; right:14px; top:50%; transform:translateY(-50%); color:var(--muted); pointer-events:none }
        .in-wrap .eye {
            position:absolute; right:8px; top:50%; transform:translateY(-50%);
            width:34px; height:34px; border:none; background:transparent;
            border-radius:9px; cursor:pointer; color:var(--muted); font-size:16px;
            transition:.2s; display:grid; place-items:center
        }
        @media (hover:hover) { .in-wrap .eye:hover { background:var(--lime-soft); color:var(--forest) } }
        body.dark .in-wrap .eye:hover { color:var(--lime) }
        /* ══════════ END INPUTS ══════════ */

        .mask-chip { display:inline-flex; align-items:center; gap:7px; background:var(--lime-soft); color:var(--forest); font:700 10.5px 'SFMono-Regular',Consolas,monospace; padding:5px 11px; border-radius:9px; max-width:260px; overflow:hidden; white-space:nowrap }
        body.dark .mask-chip { color:var(--lime) }
        .mask-chip i { font-size:13px; flex-shrink:0 }
        .hint { font-size:11.5px; color:var(--muted); line-height:1.65; background:var(--bg); border:1px dashed var(--border); padding:11px 15px; border-radius:11px }
        .hint b { color:var(--text) }
        .hint i { vertical-align:-2px }
        .btn-save { display:inline-flex; align-items:center; justify-content:center; gap:9px; width:100%; min-height:52px; background:linear-gradient(135deg,var(--forest),var(--forest-2)); color:#fff; border:none; padding:15px 22px; border-radius:13px; font-weight:800; font-size:13.5px; letter-spacing:.4px; cursor:pointer; transition:.25s; box-shadow:0 8px 20px rgba(14,90,117,.35); position:relative; overflow:hidden }
        .btn-save:disabled { opacity:.6; cursor:wait; transform:none !important }
        .btn-save::after { content:''; position:absolute; top:0; left:-80%; width:50%; height:100%; background:linear-gradient(100deg,transparent,rgba(255,255,255,.25),transparent); transform:skewX(-20deg); transition:left .6s ease }
        @media (hover:hover) { .btn-save:hover::after { left:130% } }
        body.dark .btn-save { background:linear-gradient(135deg,var(--lime),#7edcf2); color:#073344; box-shadow:0 8px 20px rgba(62,197,224,.35) }
        @media (hover:hover) { .btn-save:hover { transform:translateY(-2px); box-shadow:0 14px 28px rgba(14,90,117,.45) } }
        .btn-save:active { transform:scale(.98) }
        .status-strip { display:flex; gap:8px; flex-wrap:wrap }
        .ks-item { display:inline-flex; align-items:center; gap:7px; font-size:11px; font-weight:700; padding:6px 12px; border-radius:20px; background:var(--bg); border:1px solid var(--border); color:var(--muted) }
        .ks-item i { font-size:13px }
        .ks-item.ok { background:#dcfce7; border-color:transparent; color:#16a34a }
        body.dark .ks-item.ok { background:rgba(74,222,128,.12); color:#4ade80 }
        .reveal { opacity:0; transform:translateY(20px); transition:opacity .45s ease,transform .45s cubic-bezier(.22,1,.36,1) }
        .reveal.show { opacity:1; transform:none }

        /* ================= RESPONSIVE ================= */
        @media(max-width:992px) {
            .sidebar { transform:translateX(-105%); width:268px !important }
            .layout.mobile-open .sidebar { transform:none }
            .main { margin-left:0 !important }
            .content { padding:18px 14px calc(40px + env(safe-area-inset-bottom)) }
        }

        @media(max-width:680px) {
            .content { padding:16px 0 calc(40px + env(safe-area-inset-bottom)); gap:16px }
            .page-head { padding:0 14px }
            .page-head h2 { font-size:20px }
            .ms-head { padding:18px 18px }
            .ms-body { padding:20px 16px 22px; gap:16px }
            .in-wrap input, .in-wrap select { font-size:16px; min-height:52px }
            .frow2 { grid-template-columns:1fr; gap:14px }
            .preset-row { display:grid; grid-template-columns:1fr 1fr; gap:9px }
            .preset { justify-content:center; padding:12px 8px; font-size:11.5px }
            .mask-chip { max-width:150px }
            .btn-save { padding:14px 18px }
            .hint { font-size:11px; padding:10px 12px }
        }

        @media(max-width:480px) {
            .chip { font-size:11px; padding:6px 12px }
            .fl label { font-size:10.5px; letter-spacing:.05em }
        }
    </style>
</head>
<body>
    <div class="layout" id="layout">
        @include('admin.include.sidebar')
        <div class="main">
            @include('admin.include.navbar')
            <main class="content">

                @php
                    $vHost = $mail['host'] ?? '';
                    $vPort = $mail['port'] ?? '';
                    $vUser = $mail['username'] ?? '';
                    $vEnc  = $mail['encryption'] ?? 'none';
                    $vFrom = $mail['from_addr'] ?? '';
                    $vName = $mail['from_name'] ?? '';
                    $smtpActive = $isActive ?? (!empty($vHost) && !empty($vUser));
                    $userMask = $vUser ? substr($vUser, 0, 3) . '••••••' : '';
                @endphp

                <div class="page-head reveal">
                    <div>
                        <h2>Mail Settings</h2>
                        <p>SMTP server used for every email your store sends.</p>
                    </div>
                    <span class="chip {{ $smtpActive ? 'on' : 'offchip' }}" id="chipMail">{{ $smtpActive ? 'Custom SMTP Active' : 'SMTP Not Configured' }}</span>
                </div>

                <div class="ms-wrap">

                    <div class="card reveal">
                        <div class="ms-head">
                            <div>
                                <h3>SMTP Configuration</h3>
                                <p>Saved encrypted in the database — never in .env</p>
                            </div>
                        </div>
                        <div class="ms-body">

                            <!-- QUICK SETUP -->
                            <div class="fl">
                                <label><span>Quick Setup</span></label>
                                <div class="preset-row">
                                    <button type="button" class="preset" onclick="applyPreset('sandbox.smtp.mailtrap.io','2525','')"><i class="ri-inbox-archive-line"></i>Mailtrap</button>
                                    <button type="button" class="preset" onclick="applyPreset('smtp.gmail.com','587','tls')"><i class="ri-google-line"></i>Gmail</button>
                                    <button type="button" class="preset" onclick="applyPreset('smtp-relay.brevo.com','587','tls')"><i class="ri-send-plane-line"></i>Brevo</button>
                                    <button type="button" class="preset" onclick="applyPreset('smtp.mailgun.org','587','tls')"><i class="ri-rocket-2-line"></i>Mailgun</button>
                                </div>
                            </div>

                            <!-- SMTP SERVER -->
                            <p class="sec-t"><i class="ri-server-line"></i>SMTP Server</p>

                            <div class="frow2">
                                <div class="fl">
                                    <label><span>Mail Host *</span></label>
                                    <div class="in-wrap">
                                        <input type="text" id="mHost" value="{{ $vHost }}" placeholder="sandbox.smtp.mailtrap.io" spellcheck="false">
                                    </div>
                                </div>
                                <div class="fl">
                                    <label><span>Encryption</span></label>
                                    <div class="in-wrap">
                                        <select id="mEnc">
                                            <option value="" {{ ($vEnc === null || $vEnc === '') ? 'selected' : '' }}>None</option>
                                            <option value="tls" {{ strtolower($vEnc ?? '') === 'tls' ? 'selected' : '' }}>TLS</option>
                                            <option value="ssl" {{ strtolower($vEnc ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                                        </select>
                                        <i class="ri-arrow-down-s-line sel-arrow"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="frow2">
                                <div class="fl">
                                    <label><span>Port *</span></label>
                                    <div class="in-wrap">
                                        <input type="number" id="mPort" value="{{ $vPort }}" placeholder="2525" min="1" max="65535" inputmode="numeric">
                                    </div>
                                </div>
                                <div class="fl">
                                    <label><span>Username *</span>@if($userMask)<span class="mask-chip"><i class="ri-user-line"></i>{{ $userMask }}</span>@endif</label>
                                    <div class="in-wrap">
                                        <input type="text" id="mUser" value="{{ $vUser }}" placeholder="SMTP username" spellcheck="false">
                                    </div>
                                </div>
                            </div>

                            <div class="fl">
                                <label><span>Password</span></label>
                                <div class="in-wrap">
                                    <input type="password" id="mPass" placeholder="{{ $vUser ? 'Type new password to replace…' : 'SMTP password' }}" autocomplete="new-password" spellcheck="false">
                                    <button type="button" class="eye" onclick="togglePw('mPass', this)"><i class="ri-eye-line"></i></button>
                                </div>
                            </div>

                            <!-- From Address + From Name side-by-side -->
                            <div class="frow2">
                                <div class="fl">
                                    <label><span>From Address</span></label>
                                    <div class="in-wrap">
                                        <input type="email" id="mFrom" value="{{ $vFrom }}" placeholder="noreply@store.com" spellcheck="false">
                                    </div>
                                </div>
                                <div class="fl">
                                    <label><span>From Name</span></label>
                                    <div class="in-wrap">
                                        <input type="text" id="mFromName" value="{{ $vName }}" placeholder="Cosmetics Store" spellcheck="false">
                                    </div>
                                </div>
                            </div>

                            <div class="status-strip">
                                <span class="ks-item {{ $vHost ? 'ok' : '' }}" id="ksHost">
                                    <i class="{{ $vHost ? 'ri-checkbox-circle-fill' : 'ri-close-circle-line' }}"></i>{{ $vHost ? 'Host saved' : 'No host' }}
                                </span>
                                <span class="ks-item {{ $vUser ? 'ok' : '' }}" id="ksUser">
                                    <i class="{{ $vUser ? 'ri-checkbox-circle-fill' : 'ri-close-circle-line' }}"></i>{{ $vUser ? 'Credentials saved' : 'No username' }}
                                </span>
                            </div>

                            <p class="hint"><i class="ri-information-line"></i> Leave the password empty to keep the saved one. Mailtrap is best for local testing — check inbox at <b>mailtrap.io</b>.</p>

                            <button type="button" class="btn-save" onclick="saveMail()"><i class="ri-save-3-line"></i>Save SMTP Settings</button>
                        </div>
                    </div>

                </div>

            </main>
        </div>
    </div>

    <script>
        var mailCsrf = document.querySelector('meta[name="csrf-token"]').content;
        var MAIL_URL = "{{ route('admin.mail.update') }}";

        function applyPreset(host, port, enc) {
            document.getElementById('mHost').value = host;
            document.getElementById('mPort').value = port;
            document.getElementById('mEnc').value = enc;
            if (window.Swal) Swal.fire({ toast:true, position:'top-end', showConfirmButton:false, timer:1600, timerProgressBar:true, icon:'info', title:host + ' preset loaded', customClass:{ popup:'swal-toast' } });
        }

        function togglePw(id, btn) {
            var inp = document.getElementById(id);
            var show = inp.type === 'password';
            inp.type = show ? 'text' : 'password';
            btn.innerHTML = '<i class="ri-eye' + (show ? '-off' : '') + '-line"></i>';
        }

        function saveMail() {
            if (!MAIL_URL) { _msSwal('error', 'Route Missing', 'admin.mail.update route exists nahi karta.'); return; }

            var btn = document.querySelector('.btn-save');
            var host = document.getElementById('mHost').value.trim();
            var port = document.getElementById('mPort').value.trim();
            var user = document.getElementById('mUser').value.trim();
            var pass = document.getElementById('mPass').value.trim();
            var enc  = document.getElementById('mEnc').value;
            var from = document.getElementById('mFrom').value.trim();
            var fromName = document.getElementById('mFromName').value.trim();

            if (!host || !port || !user || !from || !fromName) {
                _msSwal('info', 'Missing Fields', 'Host, port, username, From Address aur From Name required hain.');
                return;
            }

            var fd = new FormData();
            fd.append('_token', mailCsrf);
            fd.append('host', host);
            fd.append('port', port);
            fd.append('username', user);
            fd.append('encryption', enc);
            fd.append('password', pass);
            fd.append('from_addr', from);
            fd.append('from_name', fromName);

            btn.disabled = true;
            fetch(MAIL_URL, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': mailCsrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: fd
            })
            .then(function (r) {
                return r.json().catch(function () { return {}; }).then(function (d) {
                    if (r.status === 419) throw new Error('Session expired — please refresh the page.');
                    if (!r.ok) throw new Error(d.message || 'Request failed (' + r.status + ')');
                    return d;
                });
            })
            .then(function (d) {
                var ok = d.status === 'success' || d.ok;
                if (ok) {
                    var chip = document.getElementById('chipMail');
                    chip.textContent = 'Custom SMTP Active';
                    chip.className = 'chip on';
                    var kh = document.getElementById('ksHost'), ku = document.getElementById('ksUser');
                    kh.className = 'ks-item ok'; kh.innerHTML = '<i class="ri-checkbox-circle-fill"></i>Host saved';
                    ku.className = 'ks-item ok'; ku.innerHTML = '<i class="ri-checkbox-circle-fill"></i>Credentials saved';
                    document.getElementById('mPass').value = '';
                    _msSwal('success', 'Saved & Encrypted 🔐', d.message || 'SMTP settings updated.');
                } else {
                    _msSwal('error', 'Not Saved', d.message || 'Could not save settings.');
                }
            })
            .catch(function (err) { _msSwal('error', 'Error', err.message || 'Something went wrong.'); })
            .finally(function () { btn.disabled = false; });
        }

        function _msSwal(icon, title, text) {
            Swal.fire({ toast:true, position:'top-end', showConfirmButton:false,
                timer:icon === 'error' ? 3600 : 2800, timerProgressBar:true,
                icon:icon, title:text || title, background:'#111c2e', color:'#e7edf5' });
        }

        (function() {
            var io = ('IntersectionObserver' in window) ? new IntersectionObserver(function(es) {
                es.forEach(function(e) { if (e.isIntersecting) { e.target.classList.add('show'); io.unobserve(e.target); } });
            }, { threshold:.08 }) : null;
            document.querySelectorAll('.reveal').forEach(function(el) { io ? io.observe(el) : el.classList.add('show'); });
            setTimeout(function() {
                document.querySelectorAll('.reveal:not(.show)').forEach(function(el) { el.classList.add('show'); });
            }, 1200);
        })();
    </script>
</body>
</html>