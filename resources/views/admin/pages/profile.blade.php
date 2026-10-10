{{-- ==========================================================
     ADMIN · MY PROFILE — v2.0 (profile photo upload + all editable)
========================================================== --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0e5a75">
    <title>NiceDash — My Profile</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}?v=3">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box }

        :root {
            --bg: #f3f5f7; --card: #ffffff; --text: #101828; --muted: #68788f;
            --border: #e8edf3; --forest: #0e5a75; --forest-2: #17739b;
            --lime: #3ec5e0; --lime-soft: #e4f6fb;
            --shadow: 0 1px 2px rgba(16, 24, 40, .04), 0 8px 24px rgba(16, 24, 40, .05);
            --radius: 16px
        }

        body.dark {
            --bg: #0b1220; --card: #111c2e; --text: #e7edf5; --muted: #8ba0b8;
            --border: #1e2c42; --lime-soft: rgba(62, 197, 224, .08);
            --shadow: 0 1px 2px rgba(0, 0, 0, .3), 0 10px 30px rgba(0, 0, 0, .35)
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg); color: var(--text); font-size: 14px;
            transition: background .4s, color .4s;
            -webkit-text-size-adjust: 100%;
            -webkit-tap-highlight-color: transparent;
            overflow-x: clip
        }

        ::-webkit-scrollbar { width: 9px; height: 9px }
        ::-webkit-scrollbar-thumb { background: #c7d2de; border-radius: 10px; border: 2px solid var(--bg) }
        body.dark ::-webkit-scrollbar-thumb { background: #24334d }

        button { font-family: inherit; user-select: none }

        .layout { min-height: 100vh }
        .main { margin-left: 268px; min-width: 0; transition: margin .35s cubic-bezier(.4, 0, .2, 1) }
        .layout.collapsed .main { margin-left: 84px }
        .sidebar { position: fixed; left: 0; top: 0; bottom: 0; width: 268px; background: var(--card); border-right: 1px solid var(--border); display: flex; flex-direction: column; z-index: 50; transition: width .35s cubic-bezier(.4, 0, .2, 1), transform .35s cubic-bezier(.4, 0, .2, 1) }
        .layout.collapsed .sidebar { width: 84px }
        .s-nav { flex: 1; overflow-y: auto; padding: 12px 14px }
        .backdrop { position: fixed; inset: 0; background: rgba(2, 6, 23, .55); z-index: 45; opacity: 0; visibility: hidden; pointer-events: none; transition: .3s }
        .backdrop.show { opacity: 1; visibility: visible; pointer-events: auto }

        .content { padding: 24px 26px calc(40px + env(safe-area-inset-bottom)); display: grid; gap: 22px; min-width: 0 }

        .pf-wrap { width: 100%; display: grid; gap: 22px; min-width: 0 }

        .page-head { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; width: 100% }
        .page-head h2 { font-size: 24px; font-weight: 800; letter-spacing: -.4px }
        .page-head p { color: var(--muted); font-size: 13px; margin-top: 3px }

        .chip { display: inline-flex; align-items: center; gap: 7px; padding: 7px 14px; border-radius: 20px; font-size: 12px; font-weight: 800; letter-spacing: .4px }
        .chip::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: currentColor; box-shadow: 0 0 8px currentColor }
        .chip.on { background: #dcfce7; color: #16a34a }
        body.dark .chip.on { background: rgba(74, 222, 128, .12); color: #4ade80 }

        .card { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; transition: background .4s; min-width: 0 }

        /* ══════════ ACCOUNT OVERVIEW + PHOTO ══════════ */
        .pf-head { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; padding: 22px 26px }
        .pf-id { display: flex; align-items: center; gap: 18px }

        .pf-av-wrap { position: relative; width: 78px; height: 78px; cursor: pointer; flex-shrink: 0 }
        .pf-av {
            width: 78px; height: 78px; border-radius: 50%;
            background: linear-gradient(135deg, var(--forest), var(--forest-2));
            color: #fff; display: grid; place-items: center;
            font: 800 24px 'Plus Jakarta Sans', sans-serif; letter-spacing: .5px;
            box-shadow: 0 10px 24px rgba(14, 90, 117, .35)
        }
        body.dark .pf-av { background: linear-gradient(135deg, var(--lime), #7edcf2); color: #073344 }
        .pf-img {
            width: 78px; height: 78px; border-radius: 50%;
            object-fit: cover; border: 3px solid var(--lime);
            box-shadow: 0 10px 24px rgba(14, 90, 117, .35)
        }
        .pf-cam {
            position: absolute; right: -3px; bottom: -3px;
            width: 30px; height: 30px; border-radius: 50%;
            background: var(--forest); color: #fff;
            display: grid; place-items: center; font-size: 15px;
            border: 3px solid var(--card); transition: .25s
        }
        body.dark .pf-cam { background: var(--lime); color: #073344 }
        .pf-av-wrap:hover .pf-cam { transform: scale(1.15); background: var(--forest-2) }
        body.dark .pf-av-wrap:hover .pf-cam { background: var(--lime) }

        .pf-id h3 { font-size: 17px; font-weight: 800; letter-spacing: -.3px }
        .pf-id p { font-size: 12.5px; color: var(--muted); margin-top: 2px }
        .pf-meta { display: flex; gap: 8px; flex-wrap: wrap; align-items: center }

        .btn-remove { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 20px; font-size: 11px; font-weight: 700; background: #fee2e2; color: #dc2626; border: 1px solid transparent; cursor: pointer; transition: .25s }
        .btn-remove:hover { background: #dc2626; color: #fff }
        body.dark .btn-remove { background: rgba(248, 113, 113, .12); color: #f87171 }
        body.dark .btn-remove:hover { background: #dc2626; color: #fff }

        /* ══════════ SECTION HEADERS ══════════ */
        .sec-head2 { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; padding: 20px 26px; border-bottom: 1px solid var(--border) }
        .sec-head2 h3 { font-size: 18px; font-weight: 800; letter-spacing: -.3px }
        .sec-head2 p { font-size: 12.5px; color: var(--muted); margin-top: 3px }

        .ps-body { padding: 26px 28px 28px; display: grid; gap: 20px; min-width: 0 }

        .fl label { display: flex; justify-content: space-between; align-items: center; gap: 10px; font-size: 11px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); margin-bottom: 8px }

        .frow2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; align-items: start }

        /* ══════════ SIMPLE INPUTS ══════════ */
        .in-wrap { position: relative }
        .in-wrap input {
            width: 100%; min-height: 50px;
            border: 1.5px solid var(--border); background: var(--bg);
            border-radius: 12px; padding: 13px 48px 13px 16px;
            font: 600 13.5px 'Plus Jakarta Sans', sans-serif;
            color: var(--text); outline: none;
            transition: border-color .25s, box-shadow .25s, background .25s
        }
        .in-wrap input::placeholder { color: #9fb0c0; font-weight: 500 }
        body.dark .in-wrap input::placeholder { color: #5c718c }
        .in-wrap input:hover { border-color: #d3deea }
        .in-wrap input:focus {
            border-color: var(--lime); background: var(--card);
            box-shadow: 0 0 0 3px rgba(62, 197, 224, .12)
        }
        .in-wrap .eye {
            position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
            width: 34px; height: 34px; border: none; background: transparent;
            border-radius: 9px; cursor: pointer; color: var(--muted); font-size: 16px;
            transition: .2s; display: grid; place-items: center
        }
        @media (hover:hover) { .in-wrap .eye:hover { background: var(--lime-soft); color: var(--forest) } }
        body.dark .in-wrap .eye:hover { color: var(--lime) }
        /* ══════════ END INPUTS ══════════ */

        .ks-item { display: inline-flex; align-items: center; gap: 7px; font-size: 11px; font-weight: 700; padding: 6px 12px; border-radius: 20px; background: var(--bg); border: 1px solid var(--border); color: var(--muted) }
        .ks-item i { font-size: 13px }
        .ks-item.ok { background: #dcfce7; border-color: transparent; color: #16a34a }
        body.dark .ks-item.ok { background: rgba(74, 222, 128, .12); color: #4ade80 }

        .hint { font-size: 11.5px; color: var(--muted); line-height: 1.65; background: var(--bg); border: 1px dashed var(--border); padding: 11px 15px; border-radius: 11px }
        .hint b { color: var(--text) }
        .hint i { vertical-align: -2px }

        .btn-save { display: inline-flex; align-items: center; justify-content: center; gap: 9px; width: 100%; background: linear-gradient(135deg, var(--forest), var(--forest-2)); color: #fff; border: none; padding: 15px 22px; border-radius: 13px; font-weight: 800; font-size: 13.5px; letter-spacing: .4px; cursor: pointer; transition: .25s; box-shadow: 0 8px 20px rgba(14, 90, 117, .35); position: relative; overflow: hidden }
        .btn-save:disabled { opacity: .6; cursor: wait; transform: none !important }
        .btn-save::after { content: ''; position: absolute; top: 0; left: -80%; width: 50%; height: 100%; background: linear-gradient(100deg, transparent, rgba(255, 255, 255, .25), transparent); transform: skewX(-20deg); transition: left .6s ease }
        @media (hover:hover) { .btn-save:hover::after { left: 130% } }
        body.dark .btn-save { background: linear-gradient(135deg, var(--lime), #7edcf2); color: #073344; box-shadow: 0 8px 20px rgba(62, 197, 224, .35) }
        @media (hover:hover) {
            .btn-save:hover { transform: translateY(-2px); box-shadow: 0 14px 28px rgba(14, 90, 117, .45) }
            body.dark .btn-save:hover { box-shadow: 0 14px 28px rgba(62, 197, 224, .45) }
        }
        .btn-save:active { transform: scale(.98) }

        .reveal { opacity: 0; transform: translateY(20px); transition: opacity .45s ease, transform .45s cubic-bezier(.22, 1, .36, 1) }
        .reveal.show { opacity: 1; transform: none }

        /* ================= RESPONSIVE ================= */
        @media(max-width:992px) {
            .sidebar { transform: translateX(-105%); width: 268px !important }
            .layout.mobile-open .sidebar { transform: none }
            .main { margin-left: 0 !important }
            .content { padding: 18px 14px calc(40px + env(safe-area-inset-bottom)) }
        }

        @media(max-width:680px) {
            .page-head h2 { font-size: 20px }
            .pf-head { padding: 18px 18px }
            .pf-id { gap: 14px }
            .pf-av-wrap, .pf-av, .pf-img { width: 64px; height: 64px }
            .pf-av { font-size: 19px }
            .pf-cam { width: 26px; height: 26px; font-size: 13px }
            .pf-id h3 { font-size: 15px }
            .sec-head2 { padding: 18px 18px }
            .sec-head2 h3 { font-size: 16px }
            .ps-body { padding: 20px 16px 22px }
            .in-wrap input { font-size: 16px; min-height: 52px }
            .frow2 { grid-template-columns: 1fr; gap: 14px }
            .btn-save { padding: 14px 18px }
        }

        @media(max-width:480px) {
            .chip { font-size: 11px; padding: 6px 12px }
            .ks-item { font-size: 10px; padding: 5px 10px }
            .hint { font-size: 11px; padding: 10px 12px }
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
                    $pInitials = collect(explode(' ', trim($admin->name)))->filter()->map(fn ($w) => strtoupper(substr($w, 0, 1)))->take(2)->implode('');
                    $pSince = $admin->created_at ? $admin->created_at->format('M d, Y') : '—';
                    $pPhotoUrl = $admin->profile_photo ? asset('storage/' . $admin->profile_photo) : null;
                @endphp

                <div class="page-head reveal">
                    <div>
                        <h2>My Profile</h2>
                        <p>Manage your administrator account details.</p>
                    </div>
                    <span class="chip on">{{ $admin->isAdmin() ? 'Administrator' : 'Account' }}</span>
                </div>

                <div class="pf-wrap">

                    <!-- ══════════ ACCOUNT OVERVIEW + PHOTO ══════════ -->
                    <div class="card reveal">
                        <div class="pf-head">
                            <div class="pf-id">
                                <div class="pf-av-wrap" id="pfAvWrap" onclick="document.getElementById('photoInput').click()" title="Change profile photo">
                                    @if ($pPhotoUrl)
                                    <img id="pfPhoto" class="pf-img" src="{{ $pPhotoUrl }}" alt="Profile photo">
                                    <span class="pf-av" id="pfInitials" style="display:none">{{ $pInitials }}</span>
                                    @else
                                    <span class="pf-av" id="pfInitials">{{ $pInitials }}</span>
                                    @endif
                                    <span class="pf-cam"><i class="ri-camera-fill"></i></span>
                                </div>
                                <div>
                                    <h3 id="pfName">{{ $admin->name }}</h3>
                                    <p id="pfEmail">{{ $admin->email }}</p>
                                </div>
                            </div>
                            <div class="pf-meta">
                                <span class="ks-item ok"><i class="ri-shield-user-line"></i>Role: {{ $admin->isAdmin() ? 'Administrator' : 'User' }}</span>
                                <span class="ks-item"><i class="ri-calendar-line"></i>Member since {{ $pSince }}</span>
                                <button type="button" class="btn-remove" id="pfRemove" onclick="removePhoto()" style="{{ $pPhotoUrl ? '' : 'display:none' }}">
                                    <i class="ri-delete-bin-6-line"></i>Remove Photo
                                </button>
                            </div>
                        </div>
                        <input type="file" id="photoInput" accept="image/png,image/jpeg,image/jpg,image/webp" hidden onchange="uploadPhoto(this)">
                    </div>

                    <!-- ══════════ ACCOUNT INFORMATION (editable) ══════════ -->
                    <div class="card reveal">
                        <div class="sec-head2">
                            <div>
                                <h3>Account Information</h3>
                                <p>Name, email aur phone — sab editable.</p>
                            </div>
                            <i class="ri-user-settings-line" style="font-size:22px;color:var(--lime)"></i>
                        </div>
                        <div class="ps-body">

                            <div class="frow2">
                                <div class="fl">
                                    <label><span>Full Name *</span></label>
                                    <div class="in-wrap">
                                        <input type="text" id="pName" value="{{ $admin->name }}" placeholder="Your full name">
                                    </div>
                                </div>
                                <div class="fl">
                                    <label><span>Phone</span></label>
                                    <div class="in-wrap">
                                        <input type="text" id="pPhone" value="{{ $admin->phone }}" placeholder="+92 300 1234567" inputmode="tel">
                                    </div>
                                </div>
                            </div>

                            <div class="fl">
                                <label><span>Email Address *</span></label>
                                <div class="in-wrap">
                                    <input type="email" id="pEmail" value="{{ $admin->email }}" placeholder="admin@store.com" spellcheck="false">
                                </div>
                            </div>

                            <button type="button" class="btn-save" id="btnInfo" onclick="saveInfo()"><i class="ri-save-3-line"></i>Save Profile</button>
                        </div>
                    </div>

                    <!-- ══════════ SECURITY — CHANGE PASSWORD ══════════ -->
                    <div class="card reveal">
                        <div class="sec-head2">
                            <div>
                                <h3>Security — Change Password</h3>
                                <p>Password update ke liye current password zaroori hai.</p>
                            </div>
                            <i class="ri-lock-password-line" style="font-size:22px;color:var(--lime)"></i>
                        </div>
                        <div class="ps-body">

                            <div class="frow2">
                                <div class="fl">
                                    <label><span>Current Password *</span></label>
                                    <div class="in-wrap">
                                        <input type="password" id="pCurrent" placeholder="Enter current password" autocomplete="current-password">
                                        <button type="button" class="eye" onclick="togglePw('pCurrent', this)"><i class="ri-eye-line"></i></button>
                                    </div>
                                </div>
                                <div class="fl">
                                    <label><span>New Password *</span></label>
                                    <div class="in-wrap">
                                        <input type="password" id="pNew" placeholder="Min 8 characters" autocomplete="new-password">
                                        <button type="button" class="eye" onclick="togglePw('pNew', this)"><i class="ri-eye-line"></i></button>
                                    </div>
                                </div>
                            </div>

                            <div class="fl">
                                <label><span>Confirm New Password *</span></label>
                                <div class="in-wrap">
                                    <input type="password" id="pConfirm" placeholder="Re-type new password" autocomplete="new-password">
                                    <button type="button" class="eye" onclick="togglePw('pConfirm', this)"><i class="ri-eye-line"></i></button>
                                </div>
                            </div>

                            <p class="hint"><i class="ri-shield-keyhole-line"></i> Strong password use karo — kam az kam <b>8 characters</b>, letters + numbers + symbols. Save ke baad agli baar login isi naye password se hoga.</p>

                            <button type="button" class="btn-save" id="btnPass" onclick="savePassword()"><i class="ri-key-2-line"></i>Update Password</button>
                        </div>
                    </div>

                </div>

            </main>
        </div>
    </div>

    <script>
        var pfCsrf = document.querySelector('meta[name="csrf-token"]').content;
        var PF_URL = "{{ route('admin.profile.update') }}";
        var PF_INITIALS = @json($pInitials);

        function togglePw(id, btn) {
            var inp = document.getElementById(id);
            var show = inp.type === 'password';
            inp.type = show ? 'text' : 'password';
            btn.innerHTML = '<i class="ri-eye' + (show ? '-off' : '') + '-line"></i>';
        }

        function pfSwal(icon, title, text) {
            Swal.fire({
                toast: true, position: 'top-end', showConfirmButton: false,
                timer: icon === 'error' ? 3600 : 2800, timerProgressBar: true,
                icon: icon, title: text || title,
                background: '#111c2e', color: '#e7edf5',
            });
        }

        function pfFail(status, d) {
            if (status === 419) throw new Error('Session expired — please refresh the page.');
            if (status === 422 && d.errors) {
                var first = Object.values(d.errors)[0];
                throw new Error(Array.isArray(first) ? first[0] : String(first));
            }
            throw new Error(d.message || 'Request failed (' + status + ')');
        }

        /* ══════════ 📸 PHOTO: upload / remove / render ══════════ */
        function setAvatar(url) {
            var wrap = document.getElementById('pfAvWrap');
            var img = document.getElementById('pfPhoto');
            var ini = document.getElementById('pfInitials');
            var rm = document.getElementById('pfRemove');

            if (url) {
                if (img) { img.src = url; img.style.display = 'block'; }
                else {
                    img = document.createElement('img');
                    img.id = 'pfPhoto';
                    img.className = 'pf-img';
                    img.alt = 'Profile photo';
                    img.src = url;
                    wrap.insertBefore(img, wrap.firstChild);
                }
                if (ini) ini.style.display = 'none';
                if (rm) rm.style.display = 'inline-flex';
                /* navbar avatar live update (SPA-safe — same DOM) */
                document.querySelectorAll('#profileDD .avatar').forEach(function (a) {
                    a.innerHTML = '<img src="' + url + '" alt="Profile" style="width:100%;height:100%;border-radius:inherit;object-fit:cover">';
                });
            } else {
                if (img) img.style.display = 'none';
                if (ini) { ini.style.display = 'grid'; ini.textContent = PF_INITIALS; }
                if (rm) rm.style.display = 'none';
                document.querySelectorAll('#profileDD .avatar').forEach(function (a) { a.textContent = PF_INITIALS; });
            }
        }

        function uploadPhoto(input) {
            var file = input.files && input.files[0];
            if (!file) return;
            if (!/^image\//.test(file.type)) { pfSwal('error', 'Invalid File', 'Sirf image files allowed hain (JPG, PNG, WEBP).'); input.value = ''; return; }
            if (file.size > 2 * 1024 * 1024) { pfSwal('error', 'Too Large', 'Photo max 2MB ka ho sakta hai.'); input.value = ''; return; }

            var fd = new FormData();
            fd.append('_method', 'PATCH');
            fd.append('_token', pfCsrf);
            fd.append('section', 'photo');
            fd.append('photo', file);

            fetch(PF_URL, {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-CSRF-TOKEN': pfCsrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function (r) {
                    return r.json().catch(function () { return {}; }).then(function (d) {
                        if (!r.ok) pfFail(r.status, d);
                        return d;
                    });
                })
                .then(function (d) {
                    setAvatar(d.photo_url);
                    pfSwal('success', 'Photo Updated 📸', d.message || 'Profile photo updated.');
                })
                .catch(function (err) { pfSwal('error', 'Error', err.message || 'Upload failed.'); })
                .finally(function () { input.value = ''; });
        }

        function removePhoto() {
            var fd = new FormData();
            fd.append('_method', 'PATCH');
            fd.append('_token', pfCsrf);
            fd.append('section', 'photo');
            fd.append('remove', '1');

            fetch(PF_URL, {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-CSRF-TOKEN': pfCsrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function (r) {
                    return r.json().catch(function () { return {}; }).then(function (d) {
                        if (!r.ok) pfFail(r.status, d);
                        return d;
                    });
                })
                .then(function (d) {
                    setAvatar(null);
                    pfSwal('success', 'Photo Removed 🗑️', d.message || 'Profile photo removed.');
                })
                .catch(function (err) { pfSwal('error', 'Error', err.message || 'Something went wrong.'); });
        }

        /* ---------- SAVE: Account info ---------- */
        function saveInfo() {
            var btn = document.getElementById('btnInfo');
            var name  = document.getElementById('pName').value.trim();
            var email = document.getElementById('pEmail').value.trim();
            var phone = document.getElementById('pPhone').value.trim();

            if (!name || !email) { pfSwal('info', 'Missing Fields', 'Name aur Email dono required hain.'); return; }

            var fd = new FormData();
            fd.append('_method', 'PATCH');
            fd.append('_token', pfCsrf);
            fd.append('section', 'info');
            fd.append('name', name);
            fd.append('email', email);
            fd.append('phone', phone);

            btn.disabled = true;
            fetch(PF_URL, {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-CSRF-TOKEN': pfCsrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function (r) {
                    return r.json().catch(function () { return {}; }).then(function (d) {
                        if (!r.ok) pfFail(r.status, d);
                        return d;
                    });
                })
                .then(function (d) {
                    var t = document.getElementById('pfName');
                    var e = document.getElementById('pfEmail');
                    if (t && d.user) t.textContent = d.user.name;
                    if (e && d.user) e.textContent = d.user.email;
                    if (d.user) {
                        PF_INITIALS = d.user.name.trim().split(/\s+/).slice(0, 2).map(function (part) { return part.charAt(0); }).join('').toUpperCase();
                        document.querySelectorAll('#pfInitials').forEach(function (el) { el.textContent = PF_INITIALS; });
                    }
                    pfSwal('success', 'Saved ✅', d.message || 'Profile updated.');
                })
                .catch(function (err) { pfSwal('error', 'Error', err.message || 'Something went wrong.'); })
                .finally(function () { btn.disabled = false; });
        }

        /* ---------- SAVE: Password ---------- */
        function savePassword() {
            var btn = document.getElementById('btnPass');
            var cur  = document.getElementById('pCurrent').value;
            var nw   = document.getElementById('pNew').value;
            var conf = document.getElementById('pConfirm').value;

            if (!cur || !nw || !conf) { pfSwal('info', 'Missing Fields', 'Teeno password fields bharna zaroori hai.'); return; }
            if (nw.length < 8) { pfSwal('info', 'Weak Password', 'New password kam az kam 8 characters ka hona chahiye.'); return; }

            var fd = new FormData();
            fd.append('_method', 'PATCH');
            fd.append('_token', pfCsrf);
            fd.append('section', 'password');
            fd.append('current_password', cur);
            fd.append('password', nw);
            fd.append('password_confirmation', conf);

            btn.disabled = true;
            fetch(PF_URL, {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-CSRF-TOKEN': pfCsrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function (r) {
                    return r.json().catch(function () { return {}; }).then(function (d) {
                        if (!r.ok) pfFail(r.status, d);
                        return d;
                    });
                })
                .then(function (d) {
                    document.getElementById('pCurrent').value = '';
                    document.getElementById('pNew').value = '';
                    document.getElementById('pConfirm').value = '';
                    document.querySelectorAll('.eye').forEach(function (b) { b.innerHTML = '<i class="ri-eye-line"></i>'; });
                    ['pCurrent', 'pNew', 'pConfirm'].forEach(function (id) { document.getElementById(id).type = 'password'; });
                    pfSwal('success', 'Password Changed 🔐', d.message || 'Password updated successfully.');
                })
                .catch(function (err) { pfSwal('error', 'Error', err.message || 'Something went wrong.'); })
                .finally(function () { btn.disabled = false; });
        }

        (function() {
            var io = ('IntersectionObserver' in window) ? new IntersectionObserver(function(es) {
                es.forEach(function(e) {
                    if (e.isIntersecting) { e.target.classList.add('show'); io.unobserve(e.target); }
                });
            }, { threshold: .08 }) : null;
            document.querySelectorAll('.reveal').forEach(function(el) { io ? io.observe(el) : el.classList.add('show'); });
            setTimeout(function() {
                document.querySelectorAll('.reveal:not(.show)').forEach(function(el) { el.classList.add('show'); });
            }, 1200);
        })();
    </script>
</body>

</html>