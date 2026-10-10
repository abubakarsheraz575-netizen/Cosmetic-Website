{{-- ========================================================= --}}
{{-- NICEDASH · SELF-CONTAINED TOPBAR (navbar.blade.php)       --}}
{{-- Identical on every admin page · no external CSS needed    --}}
{{-- ========================================================= --}}
@php
 $user = auth()->user();
 $userName = $user->name ?? 'Admin';
 $userMail = $user->email ?? 'admin@example.com';
 $initials = collect(explode(' ', trim($userName)))->filter()->take(2)->map(fn ($w) => strtoupper($w[0]))->implode('');
 $userPhoto = ($user && $user->profile_photo) ? asset('storage/' . $user->profile_photo) : null;
@endphp
<header class="topbar">
    <button class="icon-btn" id="menuBtn" title="Toggle menu"><i class="ri-menu-2-line"></i></button>

    <div class="search">
        <i class="ri-search-line"></i>
        <input type="text" placeholder="Search anything..." id="nbSearch">
        <kbd>⌘K</kbd>
    </div>

    <div class="top-actions">

        {{-- ===== LIVE CLOCK ===== --}}
        <div class="nb-clock" title="Live time">
            <i class="ri-time-line"></i>
            <div class="nb-clock-txt">
                <span class="nb-time">
                    <span id="nbTime">--:--</span><span class="nb-sec" id="nbSec">:--</span>
                    <span class="nb-ampm" id="nbAmpm">--</span>
                </span>
                <span class="nb-date" id="nbDate">Loading…</span>
            </div>
        </div>

        <span class="nb-divider"></span>

        <button class="icon-btn" id="themeBtn" title="Dark mode"><i class="ri-moon-line"></i></button>

        {{-- ===== NOTIFICATIONS ===== --}}
        <div class="nb-dd" id="notifDD">
            <button class="icon-btn bell nb-trigger" title="Notifications" aria-label="Notifications" aria-controls="nbNotifList" aria-expanded="false">
                <i class="ri-notification-3-line"></i>
                <span class="dot" id="nbNotifDot" style="display:none"></span>
                <span class="count" id="nbNotifCount" style="display:none">0</span>
            </button>

            <div class="nb-panel nb-notif">
                <span class="nb-arrow"></span>
                <div class="nb-panel-head">
                    <div><strong>Notifications</strong><span class="nb-pill" id="nbUnreadPill">0 new</span></div>
                    <button type="button" class="nb-mark" id="nbMarkAll"><i class="ri-check-double-line"></i>Mark all read</button>
                </div>

                <div class="nb-notif-list" id="nbNotifList" aria-live="polite">
                    <div class="nb-item"><div class="nb-item-body"><span>Loading notifications…</span></div></div>
                </div>

                <a class="nb-panel-foot" href="{{ route('admin.notifications') }}">View all notifications<i class="ri-arrow-right-line"></i></a>
            </div>
        </div>

        <span class="nb-divider"></span>

        {{-- ===== ADMIN PROFILE DROPDOWN ===== --}}
        <div class="nb-dd" id="profileDD">
            <button class="profile nb-trigger" title="Account">
                <div class="avatar">@if ($userPhoto)<img src="{{ $userPhoto }}" alt="{{ $userName }}" style="width:100%;height:100%;border-radius:inherit;object-fit:cover">@else{{ $initials }}@endif</div>
                <div class="p-info"><strong>{{ $userName }}</strong><span>Administrator</span></div>
                <i class="ri-arrow-down-s-line chev" style="color:var(--muted)"></i>
            </button>

            <div class="nb-panel nb-profile">
                <span class="nb-arrow"></span>

                <div class="nb-profile-head">
                    <div class="np-av-wrap">
                        <div class="avatar">@if ($userPhoto)<img src="{{ $userPhoto }}" alt="{{ $userName }}" style="width:100%;height:100%;border-radius:inherit;object-fit:cover">@else{{ $initials }}@endif</div>
                        <span class="np-crown"><i class="ri-vip-crown-2-fill"></i></span>
                    </div>
                    <strong>{{ $userName }}</strong>
                    <span class="np-mail"><i class="ri-mail-line"></i>{{ $userMail }}</span>
                    <em><i class="ri-shield-check-fill"></i> Administrator</em>
                </div>

                <div class="nb-profile-body">
                    <a href="{{ route('admin.profile') }}" class="nb-menu">
                        <span class="nb-mi" style="--mi:#2563eb"><i class="ri-user-3-line"></i></span>
                        <span class="nb-mtxt">My Profile<small>View &amp; edit your details</small></span>
                        <i class="ri-arrow-right-s-line nb-go"></i>
                    </a>
                    <a href="{{ route('admin.profile') }}" class="nb-menu">
                        <span class="nb-mi" style="--mi:#7c3aed"><i class="ri-settings-4-line"></i></span>
                        <span class="nb-mtxt">Account Settings<small>Info &amp; password — sab editable</small></span>
                        <i class="ri-arrow-right-s-line nb-go"></i>
                    </a>
                    <div class="nb-sep"></div>
                    <button type="button" class="nb-menu danger" id="nbLogoutBtn">
                        <span class="nb-mi" style="--mi:#dc2626"><i class="ri-logout-box-r-line"></i></span>
                        <span class="nb-mtxt">Log Out<small>End your admin session</small></span>
                    </button>
                </div>

                <div class="nb-profile-foot"><i class="ri-shield-star-line"></i>NiceDash Admin · v2.0</div>
            </div>
        </div>
    </div>
</header>

{{-- ===== LOGOUT CONFIRM MODAL ===== --}}
<div class="nb-modal" id="nbLogoutModal">
    <div class="nb-modal-box">
        <div class="nb-modal-ic"><i class="ri-logout-box-r-line"></i></div>
        <h4>Log out of NiceDash?</h4>
        <p>You will be returned to the login screen. Make sure any unsaved work is saved.</p>
        <div class="nb-modal-actions">
            <button class="nb-btn ghost" id="nbCancelLogout"><i class="ri-close-line"></i>Cancel</button>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="nb-btn danger"><i class="ri-logout-box-r-line"></i>Yes, Log Out</button>
            </form>
        </div>
    </div>
</div>

{{-- ===== TOAST ===== --}}
<div class="nb-toast" id="nbToast"><i class="ri-checkbox-circle-line"></i><span id="nbToastMsg"></span></div>

<style>
    /* ========== BASE TOPBAR ========== */
    .topbar {
        position: sticky;
        top: 0;
        z-index: 40;
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 26px;
        background: var(--topbar, rgba(255, 255, 255, .85));
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-bottom: 1px solid var(--border, #e8edf3);
    }

    .search {
        display: flex;
        align-items: center;
        gap: 10px;
        background: var(--bg, #f3f5f7);
        border: 1px solid var(--border, #e8edf3);
        border-radius: 12px;
        padding: 9px 14px;
        width: 320px;
        transition: .3s;
        color: var(--muted, #68788f);
    }

    .search:focus-within {
        border-color: var(--lime);
        box-shadow: 0 0 0 4px rgba(62, 197, 224, .15);
        width: 380px
    }

    .search input {
        border: none;
        outline: none;
        background: transparent;
        color: var(--text, #101828);
        flex: 1;
        font: inherit;
        font-size: 13px
    }

    kbd {
        background: var(--card, #fff);
        border: 1px solid var(--border, #e8edf3);
        border-radius: 6px;
        padding: 2px 7px;
        font-size: 10.5px;
        color: var(--muted, #68788f);
        font-family: inherit
    }

    .top-actions {
        margin-left: auto;
        display: flex;
        align-items: center;
        gap: 6px
    }

    .icon-btn {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        background: transparent;
        border: none;
        color: var(--muted, #68788f);
        cursor: pointer;
        position: relative;
        overflow: visible;
        transition: .25s;
        font-size: 19px;
    }

    .icon-btn:hover {
        background: var(--bg, #f3f5f7);
        color: var(--text, #101828);
        transform: translateY(-2px)
    }

    .icon-btn .dot {
        position: absolute;
        top: 9px;
        right: 9px;
        width: 8px;
        height: 8px;
        background: #ef4444;
        border-radius: 50%;
        border: 2px solid var(--card, #fff);
        animation: nbBlink 1.6s infinite
    }

    @keyframes nbBlink {

        0%,
        100% {
            transform: scale(1);
            opacity: 1
        }

        50% {
            transform: scale(1.35);
            opacity: .55
        }
    }

    .icon-btn .count {
        position: absolute;
        top: 5px;
        right: 5px;
        background: var(--lime);
        color: #073344;
        font-size: 9.5px;
        font-weight: 800;
        min-width: 16px;
        height: 16px;
        border-radius: 9px;
        display: grid;
        place-items: center;
        padding: 0 4px;
        box-shadow: 0 3px 8px rgba(62, 197, 224, .45);
    }

    .profile {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 5px 10px;
        border-radius: 12px;
        cursor: pointer;
        transition: .25s;
        border: none;
        background: transparent;
        font-family: inherit
    }

    .profile:hover {
        background: var(--bg, #f3f5f7)
    }

    .avatar {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--forest, #0e5a75), var(--lime, #3ec5e0));
        color: #fff;
        font-weight: 800;
        font-size: 13px;
        display: grid;
        place-items: center;
        overflow: hidden;
    }

    .p-info {
        display: flex;
        flex-direction: column;
        line-height: 1.25;
        text-align: left
    }

    .p-info strong {
        font-size: 13px
    }

    .p-info span {
        font-size: 11px;
        color: var(--muted, #68788f)
    }

    .ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(62, 197, 224, .45);
        transform: scale(0);
        animation: rip .6s ease-out;
        pointer-events: none;
    }

    @keyframes rip {
        to {
            transform: scale(1);
            opacity: 0
        }
    }

    /* ========== nb enhancements ========== */
    .nb-divider {
        width: 1px;
        height: 26px;
        background: var(--border, #e8edf3);
        margin: 0 4px;
        flex-shrink: 0
    }

    .topbar .icon-btn {
        border: 1px solid transparent
    }

    .topbar .icon-btn:hover {
        border-color: var(--border, #e8edf3);
        background: linear-gradient(145deg, var(--bg, #f3f5f7), var(--card, #fff));
        color: var(--forest, #0e5a75);
        box-shadow: var(--shadow, 0 1px 2px rgba(16, 24, 40, .04))
    }

    body.dark .topbar .icon-btn:hover {
        color: var(--lime)
    }

    .topbar .icon-btn:active {
        transform: scale(.92)
    }

    .topbar .icon-btn i {
        display: block;
        line-height: 1;
        transition: transform .35s cubic-bezier(.34, 1.56, .64, 1)
    }

    .topbar .icon-btn:hover i {
        transform: scale(1.12) rotate(-8deg)
    }

    .topbar .icon-btn.bell:hover i {
        animation: nbRing .7s ease
    }

    @keyframes nbRing {

        0%,
        100% {
            transform: rotate(0)
        }

        20% {
            transform: rotate(14deg)
        }

        40% {
            transform: rotate(-12deg)
        }

        60% {
            transform: rotate(8deg)
        }

        80% {
            transform: rotate(-5deg)
        }
    }

    .icon-btn .dot::after {
        content: '';
        position: absolute;
        inset: -4px;
        border-radius: 50%;
        border: 2px solid #ef4444;
        opacity: 0;
        animation: nbPing 1.8s ease-out infinite
    }

    @keyframes nbPing {
        0% {
            transform: scale(.5);
            opacity: .9
        }

        80%,
        100% {
            transform: scale(1.7);
            opacity: 0
        }
    }

    .topbar .profile .avatar {
        box-shadow: 0 0 0 2px var(--card, #fff), 0 0 0 4px rgba(62, 197, 224, .5);
        transition: .3s
    }

    .topbar .profile:hover .avatar {
        box-shadow: 0 0 0 2px var(--card, #fff), 0 0 0 4px var(--lime)
    }

    .topbar .chev {
        transition: transform .3s
    }

    .nb-dd.open .chev {
        transform: rotate(180deg)
    }

    /* clock */
    .nb-clock {
        display: flex;
        align-items: center;
        gap: 10px;
        background: var(--bg, #f3f5f7);
        border: 1px solid var(--border, #e8edf3);
        border-radius: 12px;
        padding: 6px 14px;
        transition: .3s;
        cursor: default;
        flex-shrink: 0
    }

    .nb-clock:hover {
        border-color: var(--lime);
        box-shadow: 0 0 0 4px rgba(62, 197, 224, .12)
    }

    .nb-clock>i {
        font-size: 18px;
        color: var(--forest, #0e5a75);
        animation: nbTick 60s linear infinite
    }

    body.dark .nb-clock>i {
        color: var(--lime)
    }

    @keyframes nbTick {
        to {
            transform: rotate(360deg)
        }
    }

    .nb-clock-txt {
        display: flex;
        flex-direction: column;
        line-height: 1.3
    }

    .nb-time {
        font-weight: 800;
        font-size: 13.5px;
        font-variant-numeric: tabular-nums;
        letter-spacing: .5px;
        display: flex;
        align-items: baseline;
        gap: 3px
    }

    .nb-sec {
        font-size: 10px;
        color: var(--muted, #68788f);
        font-weight: 700
    }

    .nb-ampm {
        font-size: 9.5px;
        font-weight: 800;
        color: var(--forest, #0e5a75);
        background: var(--lime-soft, #e4f6fb);
        padding: 1px 6px;
        border-radius: 6px
    }

    body.dark .nb-ampm {
        color: var(--lime)
    }

    .nb-date {
        font-size: 10px;
        color: var(--muted, #68788f);
        font-weight: 600;
        white-space: nowrap
    }

    /* dropdown core */
    .nb-dd {
        position: relative
    }

    .nb-panel {
        position: absolute;
        top: calc(100% + 14px);
        right: 0;
        z-index: 60;
        min-width: 230px;
        background: var(--card, #fff);
        border: 1px solid var(--border, #e8edf3);
        border-radius: 18px;
        box-shadow: 0 24px 60px rgba(16, 24, 40, .18);
        opacity: 0;
        visibility: hidden;
        transform: translateY(12px) scale(.96);
        transform-origin: top right;
        transition: .3s cubic-bezier(.22, 1, .36, 1);
        overflow: hidden;
    }

    .nb-dd.open .nb-panel {
        opacity: 1;
        visibility: visible;
        transform: none
    }

    .nb-arrow {
        position: absolute;
        top: -6.5px;
        right: 22px;
        width: 12px;
        height: 12px;
        background: var(--card, #fff);
        border-left: 1px solid var(--border, #e8edf3);
        border-top: 1px solid var(--border, #e8edf3);
        transform: rotate(45deg)
    }

    /* notifications */
    .nb-notif {
        width: 390px;
        max-width: calc(100vw - 30px)
    }

    .nb-notif.nb-panel {
        border-color: rgba(14, 90, 117, .14);
        border-radius: 20px;
        box-shadow: 0 24px 64px rgba(16, 38, 55, .2), 0 4px 14px rgba(16, 38, 55, .06)
    }

    .nb-panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 18px;
        border-bottom: 1px solid rgba(14, 90, 117, .1);
        background: linear-gradient(135deg, rgba(62, 197, 224, .13), rgba(255, 255, 255, .35) 65%, rgba(139, 92, 246, .06))
    }

    .nb-panel-head strong {
        font-size: 14.5px
    }

    .nb-pill {
        font-size: 10.5px;
        font-weight: 800;
        background: var(--lime);
        color: #073344;
        padding: 2px 9px;
        border-radius: 20px;
        margin-left: 8px
    }

    .nb-mark {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border: none;
        background: transparent;
        color: var(--forest, #0e5a75);
        font-weight: 700;
        font-size: 12px;
        cursor: pointer;
        padding: 6px 10px;
        border-radius: 9px;
        transition: .25s;
        font-family: inherit
    }

    .nb-mark:hover {
        background: var(--lime-soft, #e4f6fb)
    }

    body.dark .nb-mark {
        color: var(--lime)
    }

    .nb-notif-list {
        max-height: 340px;
        overflow-y: auto
    }

    .nb-item {
        display: flex;
        gap: 12px;
        margin: 4px 7px;
        padding: 12px 11px;
        cursor: pointer;
        position: relative;
        transition: background .18s, transform .18s;
        border-radius: 13px;
        color: inherit;
        text-decoration: none
    }

    .nb-item:hover {
        background: rgba(14, 90, 117, .055);
        transform: translateX(2px)
    }

    .nb-item.unread {
        background: linear-gradient(105deg, rgba(62, 197, 224, .13), rgba(62, 197, 224, .045))
    }

    .nb-ic {
        width: 38px;
        height: 38px;
        min-width: 38px;
        border-radius: 11px;
        display: grid;
        place-items: center;
        font-size: 17px
    }

    .i-green {
        background: #e8f7ee;
        color: #16a34a
    }

    .i-blue {
        background: #e8f1fd;
        color: #2563eb
    }

    .i-amber {
        background: #fdf4e3;
        color: #d97706
    }

    .i-violet {
        background: #f1ecfd;
        color: #7c3aed
    }

    body.dark .i-green {
        background: rgba(74, 222, 128, .12);
        color: #4ade80
    }

    body.dark .i-blue {
        background: rgba(96, 165, 250, .12);
        color: #60a5fa
    }

    body.dark .i-amber {
        background: rgba(251, 191, 36, .12);
        color: #fbbf24
    }

    body.dark .i-violet {
        background: rgba(167, 139, 250, .12);
        color: #a78bfa
    }

    .nb-item-body {
        flex: 1;
        min-width: 0
    }

    .nb-item-body p,
    .nb-item-body span,
    .nb-item-body em {
        color: inherit;
        text-decoration: none
    }

    .nb-item-body span,
    .nb-item-body em {
        color: var(--muted, #68788f)
    }

    .nb-item-body p {
        font-size: 13px;
        font-weight: 600;
        line-height: 1.4
    }

    .nb-item-body p b {
        color: var(--forest, #0e5a75)
    }

    body.dark .nb-item-body p b {
        color: var(--lime)
    }

    .nb-item-body span {
        display: block;
        font-size: 11.5px;
        color: var(--muted, #68788f);
        margin-top: 2px
    }

    .nb-item-body em {
        display: block;
        font-size: 10.5px;
        color: var(--muted, #68788f);
        font-style: normal;
        margin-top: 4px;
        font-weight: 600
    }

    .nb-unread-dot {
        width: 8px;
        height: 8px;
        min-width: 8px;
        border-radius: 50%;
        background: #159a83;
        margin-top: 6px;
        box-shadow: 0 0 0 3px rgba(21, 154, 131, .15)
    }

    .nb-item:not(.unread) .nb-unread-dot {
        display: none
    }

    .nb-panel-foot {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 13px;
        border: none;
        border-top: 1px solid var(--border, #e8edf3);
        background: var(--bg, #f3f5f7);
        color: var(--forest, #0e5a75);
        font-weight: 700;
        font-size: 12.5px;
        cursor: pointer;
        transition: .25s;
        font-family: inherit;
        text-decoration: none
    }

    body.dark .nb-panel-foot {
        color: var(--lime)
    }

    .nb-panel-foot:hover {
        background: var(--lime-soft, #e4f6fb)
    }

    .nb-panel-foot i {
        transition: .25s
    }

    .nb-panel-foot:hover i {
        transform: translateX(4px)
    }

    .i-red {
        background: #fee2e2;
        color: #dc2626
    }

    body.dark .i-red {
        background: rgba(248, 113, 113, .12);
        color: #f87171
    }

    /* ========== ADMIN PROFILE ========== */
    .nb-profile {
        width: 290px
    }

    .nb-profile-head {
        padding: 24px 18px 20px;
        background: linear-gradient(145deg, #0d4257, #0e5a75 55%, #17739b);
        color: #fff;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .nb-profile-head::before {
        content: '';
        position: absolute;
        width: 150px;
        height: 150px;
        border-radius: 50%;
        top: -60px;
        right: -50px;
        background: radial-gradient(circle, rgba(62, 197, 224, .35), transparent 70%);
        animation: nbFloat 6s ease-in-out infinite;
    }

    .nb-profile-head::after {
        content: '';
        position: absolute;
        width: 110px;
        height: 110px;
        border-radius: 50%;
        bottom: -60px;
        left: -40px;
        background: radial-gradient(circle, rgba(62, 197, 224, .18), transparent 70%);
        animation: nbFloat 7s ease-in-out infinite reverse;
    }

    @keyframes nbFloat {

        0%,
        100% {
            transform: translateY(0)
        }

        50% {
            transform: translateY(-10px)
        }
    }

    .np-av-wrap {
        position: relative;
        width: 62px;
        margin: 0 auto 10px;
        z-index: 1
    }

    .nb-profile-head .avatar {
        width: 62px;
        height: 62px;
        min-width: 62px;
        font-size: 21px;
        border-radius: 18px;
        border: 2px solid rgba(62, 197, 224, .7);
        box-shadow: 0 10px 24px rgba(0, 0, 0, .35);
    }

    .np-crown {
        position: absolute;
        right: -6px;
        bottom: -4px;
        width: 24px;
        height: 24px;
        border-radius: 8px;
        display: grid;
        place-items: center;
        font-size: 13px;
        background: linear-gradient(135deg, #7edcf2, #3fb0d8);
        color: #062a38;
        border: 2px solid #0d4257;
        box-shadow: 0 6px 14px rgba(0, 0, 0, .3);
        animation: nbFloat 5s ease-in-out infinite;
    }

    .nb-profile-head strong {
        display: block;
        font-size: 15px;
        position: relative;
        z-index: 1
    }

    .np-mail {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11.5px;
        color: rgba(255, 255, 255, .75);
        margin-top: 4px;
        position: relative;
        z-index: 1;
        max-width: 100%;
        overflow: hidden;
        white-space: nowrap;
    }

    .np-mail i {
        font-size: 13px;
        color: var(--lime);
        flex-shrink: 0
    }

    .nb-profile-head em {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-style: normal;
        font-size: 10.5px;
        font-weight: 800;
        letter-spacing: .4px;
        background: rgba(62, 197, 224, .18);
        color: var(--lime);
        border: 1px solid rgba(62, 197, 224, .3);
        padding: 4px 12px;
        border-radius: 20px;
        margin-top: 10px;
        position: relative;
        z-index: 1;
    }

    .nb-profile-body {
        padding: 8px
    }

    .nb-menu {
        display: flex;
        align-items: center;
        gap: 11px;
        width: 100%;
        padding: 9px 10px;
        border-radius: 12px;
        color: var(--text, #101828);
        text-decoration: none;
        font-weight: 600;
        font-size: 13px;
        border: none;
        background: transparent;
        cursor: pointer;
        font-family: inherit;
        transition: .22s;
        text-align: left;
    }

    .nb-mi {
        width: 36px;
        height: 36px;
        min-width: 36px;
        border-radius: 11px;
        display: grid;
        place-items: center;
        font-size: 17px;
        color: var(--mi, var(--muted, #68788f));
        background: color-mix(in srgb, var(--mi, #68788f) 12%, transparent);
        transition: .22s;
    }

    body.dark .nb-mi {
        background: color-mix(in srgb, var(--mi, #8ba0b8) 16%, transparent)
    }

    .nb-mtxt {
        flex: 1;
        line-height: 1.25
    }

    .nb-mtxt small {
        display: block;
        font-size: 10.5px;
        color: var(--muted, #68788f);
        font-weight: 600;
        margin-top: 1px
    }

    .nb-go {
        color: var(--muted, #68788f);
        opacity: 0;
        transform: translateX(-5px);
        transition: .22s;
        font-size: 17px
    }

    .nb-menu:hover {
        background: var(--bg, #f3f5f7);
        transform: translateX(3px)
    }

    .nb-menu:hover .nb-mi {
        transform: scale(1.06)
    }

    .nb-menu:hover .nb-go {
        opacity: 1;
        transform: none;
        color: var(--forest, #0e5a75)
    }

    body.dark .nb-menu:hover .nb-go {
        color: var(--lime)
    }

    .nb-menu.danger {
        color: #dc2626
    }

    .nb-menu.danger .nb-mi {
        background: rgba(220, 38, 38, .08);
        color: #dc2626
    }

    .nb-menu.danger .nb-mtxt small {
        color: #dc2626;
        opacity: .65
    }

    .nb-menu.danger:hover {
        background: rgba(220, 38, 38, .07)
    }

    body.dark .nb-menu.danger:hover {
        background: rgba(248, 113, 113, .1)
    }

    .nb-sep {
        height: 1px;
        background: var(--border, #e8edf3);
        margin: 7px 8px
    }

    .nb-profile-foot {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 11px;
        border-top: 1px solid var(--border, #e8edf3);
        background: var(--bg, #f3f5f7);
        font-size: 10.5px;
        font-weight: 700;
        color: var(--muted, #68788f);
        letter-spacing: .4px;
    }

    .nb-profile-foot i {
        color: var(--lime)
    }

    /* ========== logout modal ========== */
    .nb-modal {
        position: fixed;
        inset: 0;
        z-index: 100;
        display: grid;
        place-items: center;
        background: rgba(2, 6, 23, .55);
        backdrop-filter: blur(5px);
        opacity: 0;
        visibility: hidden;
        transition: .3s
    }

    .nb-modal.show {
        opacity: 1;
        visibility: visible
    }

    .nb-modal-box {
        width: min(370px, calc(100vw - 40px));
        background: var(--card, #fff);
        border: 1px solid var(--border, #e8edf3);
        border-radius: 22px;
        padding: 30px 26px 24px;
        text-align: center;
        box-shadow: 0 30px 70px rgba(0, 0, 0, .35);
        transform: translateY(14px) scale(.95);
        transition: .35s cubic-bezier(.22, 1, .36, 1);
    }

    .nb-modal.show .nb-modal-box {
        transform: none
    }

    .nb-modal-ic {
        width: 64px;
        height: 64px;
        margin: 0 auto 16px;
        border-radius: 20px;
        background: #fee2e2;
        color: #dc2626;
        font-size: 28px;
        display: grid;
        place-items: center;
        animation: nbShake .6s ease .15s
    }

    body.dark .nb-modal-ic {
        background: rgba(248, 113, 113, .12);
        color: #f87171
    }

    @keyframes nbShake {

        0%,
        100% {
            transform: rotate(0)
        }

        25% {
            transform: rotate(-7deg)
        }

        50% {
            transform: rotate(6deg)
        }

        75% {
            transform: rotate(-4deg)
        }
    }

    .nb-modal-box h4 {
        font-size: 17px;
        font-weight: 800;
        margin-bottom: 6px
    }

    .nb-modal-box p {
        font-size: 12.5px;
        color: var(--muted, #68788f);
        line-height: 1.6;
        margin-bottom: 20px
    }

    .nb-modal-actions {
        display: flex;
        gap: 10px;
        justify-content: center
    }

    .nb-btn {
        padding: 11px 20px;
        border-radius: 12px;
        font-weight: 800;
        font-size: 13px;
        cursor: pointer;
        border: none;
        font-family: inherit;
        transition: .25s;
        display: inline-flex;
        align-items: center;
        gap: 7px
    }

    .nb-btn.ghost {
        background: var(--bg, #f3f5f7);
        color: var(--text, #101828);
        border: 1px solid var(--border, #e8edf3)
    }

    .nb-btn.ghost:hover {
        border-color: var(--muted, #68788f)
    }

    .nb-btn.danger {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: #fff;
        box-shadow: 0 8px 20px rgba(239, 68, 68, .35)
    }

    .nb-btn.danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 26px rgba(239, 68, 68, .45)
    }

    /* ========== toast ========== */
    .nb-toast {
        position: fixed;
        bottom: 26px;
        left: 50%;
        transform: translate(-50%, 80px);
        z-index: 110;
        display: flex;
        align-items: center;
        gap: 9px;
        background: var(--forest, #0e5a75);
        color: #fff;
        padding: 12px 20px;
        border-radius: 14px;
        font-weight: 700;
        font-size: 13px;
        box-shadow: 0 14px 34px rgba(14, 90, 117, .4);
        opacity: 0;
        transition: .4s cubic-bezier(.22, 1, .36, 1);
    }

    body.dark .nb-toast {
        background: var(--lime);
        color: #073344
    }

    .nb-toast i {
        font-size: 17px;
        color: var(--lime)
    }

    body.dark .nb-toast i {
        color: #073344
    }

    .nb-toast.show {
        transform: translate(-50%, 0);
        opacity: 1
    }

    @media(max-width:1150px) {
        .nb-clock {
            display: none
        }

        .nb-divider {
            display: none
        }
    }

    @media(max-width:992px) {
        .search {
            width: 190px
        }

        .search:focus-within {
            width: 220px
        }

        .p-info {
            display: none
        }
    }

    @media(max-width:680px) {
        .topbar {
            padding: 12px 14px
        }

        .search,
        .search:focus-within {
            flex: 1 1 0;
            width: auto;
            min-width: 0
        }

        .search input {
            min-width: 0
        }

        .nb-notif {
            position: fixed;
            top: calc(100% + 10px);
            left: 50%;
            right: auto;
            width: min(420px, calc(100vw - 24px));
            max-width: calc(100vw - 24px);
            max-height: calc(100dvh - 88px);
            overflow-y: auto;
            transform: translate(-50%, 12px) scale(.96);
            transform-origin: top center;
            box-shadow: 0 22px 58px rgba(16, 38, 55, .28), 0 4px 16px rgba(16, 38, 55, .1);
        }

        .nb-dd.open .nb-notif {
            transform: translateX(-50%);
        }

        .nb-notif .nb-panel-head {
            align-items: flex-start;
            gap: 8px;
            padding: 12px 14px;
        }

        .nb-notif .nb-panel-head > div {
            min-width: 0;
        }

        .nb-notif .nb-mark {
            flex: 0 0 auto;
            font-size: 11px;
            padding: 6px 7px;
        }

        .nb-notif-list {
            max-height: min(340px, calc(100dvh - 210px));
            overflow-y: auto;
            overscroll-behavior: contain;
        }

        .nb-notif .nb-item {
            gap: 10px;
            padding: 12px 14px;
        }

        .nb-notif .nb-item-body {
            overflow-wrap: anywhere;
        }

        .nb-profile {
            width: 260px
        }

        kbd {
            display: none
        }
    }
</style>

<script>
    /* ==========================================================
   NICEDASH ENGINE v4 — UI + sidebar + counters + AJAX
============================================================ */
    (function() {
        if (window.__ndBoot) return;
        window.__ndBoot = true;
        window.__adminSPA = true; /* neutralize OLD engines */
        window.__ndSidebar = true;
        window.__niceDashBoot = true;

        var PREFIX = '/admin';
        var SKIP = ['/login', '/register', '/forgot-password', '/reset-password', '/verify-email', '/confirm-password', '/logout'];

        /* ---------- theme ---------- */
        function ensureThemeVars() {
            if (getComputedStyle(document.body).getPropertyValue('--card').trim()) return;
            var s = document.createElement('style');
            s.id = 'ndThemeVars';
            s.textContent = 'body{--card:#fff;--bg:#f3f5f7;--border:#e8edf3;--text:#101828;--muted:#68788f;--forest:#0e5a75;--lime:#3ec5e0;--lime-soft:#e4f6fb;--topbar:rgba(255,255,255,.85)}' +
                'body.dark{--card:#111c2e;--bg:#0b1220;--border:#1e2c42;--text:#e7edf5;--muted:#8ba0b8;--lime-soft:rgba(62,197,224,.08);--topbar:rgba(17,28,46,.85)}';
            document.head.appendChild(s);
        }

        function syncThemeIcon() {
            var i = document.querySelector('#themeBtn i');
            if (i) i.className = document.body.classList.contains('dark') ? 'ri-sun-line' : 'ri-moon-line';
        }

        function applyTheme(dark) {
            document.body.classList.toggle('dark', dark);
            syncThemeIcon();
            try {
                localStorage.setItem('nb-theme', dark ? 'dark' : 'light');
            } catch (e) {}
            document.dispatchEvent(new CustomEvent('nd:theme'));
        }

        /* ---------- toast ---------- */
        window.nbToast = function(msg, icon) {
            if (typeof window.showToast === 'function') {
                var type = icon && /error|warning|alert/i.test(icon) ? (icon.indexOf('error') !== -1 ? 'error' : 'warning') : 'success';
                window.showToast(msg, type);
                return;
            }
            var t = document.getElementById('nbToast');
            var m = document.getElementById('nbToastMsg');
            if (!t || !m) return;
            m.textContent = msg;
            t.classList.add('show');
        };

        var notificationFeedUrl = @json(route('admin.notifications.feed'));
        var notificationReadUrl = @json(route('admin.notifications.read'));
        var notificationReadAllUrl = @json(route('admin.notifications.read-all'));
        var notificationCsrf = document.querySelector('meta[name="csrf-token"]');

        function escapeNotification(value) {
            return String(value == null ? '' : value).replace(/[&<>"']/g, function(character) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[character];
            });
        }

        function updateNotificationCount(count) {
            var value = Number(count) || 0;
            var badge = document.getElementById('nbNotifCount');
            var dot = document.getElementById('nbNotifDot');
            var pill = document.getElementById('nbUnreadPill');
            var markAll = document.getElementById('nbMarkAll');
            if (badge) {
                badge.textContent = value > 99 ? '99+' : String(value);
                badge.style.display = value ? '' : 'none';
            }
            if (dot) dot.style.display = value ? '' : 'none';
            if (pill) pill.textContent = value + ' new';
            if (markAll) markAll.disabled = value === 0;
        }

        function renderNotifications(notifications) {
            var list = document.getElementById('nbNotifList');
            if (!list) return;
            if (!notifications.length) {
                list.innerHTML = '<div class="nb-item"><div class="nb-item-body"><p>No notifications yet</p><span>New orders, customers, reviews and low-stock alerts will appear here.</span></div></div>';
                return;
            }

            list.innerHTML = notifications.map(function(item) {
                return '<a class="nb-item' + (item.read ? '' : ' unread') +
                    '" href="' + escapeNotification(item.url) +
                    '" data-notification-key="' + escapeNotification(item.key) + '">' +
                    '<span class="nb-ic ' + escapeNotification(item.icon_class) + '"><i class="' +
                    escapeNotification(item.icon) + '"></i></span>' +
                    '<span class="nb-item-body"><p>' + escapeNotification(item.title) + '</p>' +
                    '<span>' + escapeNotification(item.description) + '</span>' +
                    '<em>' + escapeNotification(item.time) + '</em></span>' +
                    '<span class="nb-unread-dot"></span></a>';
            }).join('');
        }

        function refreshNotifications() {
            return fetch(notificationFeedUrl, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            }).then(function(response) {
                if (!response.ok) throw new Error('Could not load notifications.');
                return response.json();
            }).then(function(data) {
                renderNotifications(data.notifications || []);
                updateNotificationCount(data.unread_count);
            }).catch(function(error) {
                var list = document.getElementById('nbNotifList');
                if (list) list.innerHTML = '<div class="nb-item"><div class="nb-item-body"><p>Notifications unavailable</p><span>' + escapeNotification(error.message) + '</span></div></div>';
            });
        }

        function postNotificationAction(url, body) {
            return fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': notificationCsrf ? notificationCsrf.content : '',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(body || {}),
                credentials: 'same-origin'
            }).then(function(response) {
                return response.json().catch(function() { return {}; }).then(function(data) {
                    if (!response.ok) throw new Error(data.message || 'Could not update notification status.');
                    return data;
                });
            });
        }

        /* ---------- live clock ---------- */
        function tickClock() {
            var t = document.getElementById('nbTime');
            if (!t) return;
            var n = new Date(),
                h = n.getHours(),
                m = n.getMinutes(),
                s = n.getSeconds(),
                h12 = h % 12 || 12;
            t.textContent = (h12 < 10 ? '0' : '') + h12 + ':' + (m < 10 ? '0' : '') + m;
            var sec = document.getElementById('nbSec');
            if (sec) sec.textContent = ':' + (s < 10 ? '0' : '') + s;
            var ap = document.getElementById('nbAmpm');
            if (ap) ap.textContent = h >= 12 ? 'PM' : 'AM';
            var d = document.getElementById('nbDate');
            if (d) d.textContent = n.toLocaleDateString(undefined, {
                weekday: 'short',
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });
        }
        if (!window.__navbarClockTimer) {
            window.__navbarClockTimer = setInterval(tickClock, 1000);
        }
        tickClock();

        /* ---------- reveal: INSTANT (no 900ms wait) ---------- */
        function initReveal() {
            document.querySelectorAll('.reveal:not(.show)').forEach(function(el, i) {
                el.style.transitionDelay = (i * 0.04) + 's';
                el.classList.add('show');
            });
        }

        /* ---------- counters: fast (650ms), rerun after AJAX ---------- */
        function runCounters() {
            document.querySelectorAll('[data-count]').forEach(function(el) {
                if (el.dataset.done) return;
                el.dataset.done = '1';
                var target = parseFloat(el.dataset.count) || 0,
                    dec = parseInt(el.dataset.decimals) || 0,
                    pre = el.dataset.prefix || '',
                    suf = el.dataset.suffix || '';

                function fmt(v) {
                    return pre + v.toLocaleString(undefined, {
                        minimumFractionDigits: dec,
                        maximumFractionDigits: dec
                    }) + suf;
                }
                var t0 = performance.now(),
                    dur = 650;
                (function step(now) {
                    var p = Math.min(((now || performance.now()) - t0) / dur, 1),
                        e = 1 - Math.pow(1 - p, 3);
                    el.textContent = fmt(target * e);
                    if (p < 1) requestAnimationFrame(step);
                })();
            });
        }

        function closeDD() {
            document.querySelectorAll('.nb-dd.open').forEach(function(dd) {
                dd.classList.remove('open');
                var trigger = dd.querySelector('.nb-trigger[aria-controls="nbNotifList"]');
                if (trigger) trigger.setAttribute('aria-expanded', 'false');
            });
        }

        /* ---------- sidebar helpers ---------- */
        function layoutEl() {
            return document.getElementById('layout') || document.querySelector('.layout');
        }

        function sidebarEl() {
            return document.getElementById('sidebar') || document.querySelector('.sidebar');
        }

        function isMobile() {
            return window.innerWidth <= 992;
        }

        function drawerOpen() {
            var s = sidebarEl();
            return !!(s && s.classList.contains('mobile-open'));
        }

        function openDrawer() {
            var l = layoutEl(),
                s = sidebarEl(),
                b = document.getElementById('backdrop');
            if (l) l.classList.add('mobile-open');
            if (s) s.classList.add('mobile-open');
            if (b) b.classList.add('show');
            document.body.classList.add('nb-lock');
        }

        function closeDrawer() {
            var l = layoutEl(),
                s = sidebarEl(),
                b = document.getElementById('backdrop');
            if (l) l.classList.remove('mobile-open');
            if (s) s.classList.remove('mobile-open');
            if (b) b.classList.remove('show');
            document.body.classList.remove('nb-lock');
        }

        /* ---------- all clicks (delegated) ---------- */
        document.addEventListener('click', function(e) {
            if (!e.target || !e.target.closest) return;

            var trig = e.target.closest('.nb-trigger');
            if (trig) {
                e.preventDefault();
                e.stopPropagation();
                var dd = trig.closest('.nb-dd'),
                    was = dd.classList.contains('open');
                closeDD();
                if (!was) {
                    dd.classList.add('open');
                    if (dd.id === 'notifDD') trig.setAttribute('aria-expanded', 'true');
                    if (dd.id === 'notifDD') refreshNotifications();
                }
                return;
            }
            if (!e.target.closest('.nb-dd')) closeDD();

            var markAll = e.target.closest('#nbMarkAll');
            if (markAll) {
                e.preventDefault();
                if (markAll.disabled) return;
                markAll.disabled = true;
                postNotificationAction(notificationReadAllUrl)
                    .then(function() {
                        document.querySelectorAll('#notifDD .nb-item.unread').forEach(function(el) {
                            el.classList.remove('unread');
                        });
                        updateNotificationCount(0);
                        nbToast('All notifications marked as read', 'ri-check-double-line');
                    })
                    .catch(function(error) {
                        nbToast(error.message || 'Could not mark notifications as read', 'ri-error-warning-line');
                    })
                    .finally(function() { markAll.disabled = false; });
                return;
            }

            var notificationLink = e.target.closest('#notifDD .nb-item[data-notification-key]');
            if (notificationLink) {
                e.preventDefault();
                var targetUrl = notificationLink.href;
                postNotificationAction(notificationReadUrl, {
                    notification_key: notificationLink.dataset.notificationKey
                }).then(function() {
                    go(targetUrl, true);
                }).catch(function(error) {
                    nbToast(error.message || 'Could not mark notification as read', 'ri-error-warning-line');
                });
                return;
            }
            if (e.target.closest('#themeBtn')) {
                applyTheme(!document.body.classList.contains('dark'));
                return;
            }
            if (e.target.closest('#nbLogoutBtn')) {
                closeDD();
                var mo = document.getElementById('nbLogoutModal');
                if (mo) mo.classList.add('show');
                return;
            }
            if (e.target.closest('#nbCancelLogout') || e.target.id === 'nbLogoutModal') {
                var mc = document.getElementById('nbLogoutModal');
                if (mc) mc.classList.remove('show');
                return;
            }

            /* hamburger */
            if (e.target.closest('#menuBtn')) {
                e.preventDefault();
                if (isMobile()) drawerOpen() ? closeDrawer() : openDrawer();
                else {
                    var l = layoutEl();
                    if (l) l.classList.toggle('collapsed');
                }
                return;
            }
            if (e.target.closest('#backdrop')) {
                closeDrawer();
                return;
            }

            /* products accordion */
            var pt = e.target.closest('#productsToggle');
            if (pt) {
                var lay = layoutEl();
                if (!isMobile() && lay && lay.classList.contains('collapsed')) {
                    lay.classList.remove('collapsed');
                    return;
                }
                pt.classList.toggle('open');
                var sub = document.getElementById('productsSub');
                if (sub) sub.classList.toggle('open');
                return;
            }
            if (isMobile() && drawerOpen() && e.target.closest('#sidebar a')) setTimeout(closeDrawer, 120);
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDD();
                closeDrawer();
                var m = document.getElementById('nbLogoutModal');
                if (m) m.classList.remove('show');
            }
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                var s = document.getElementById('nbSearch');
                if (s) {
                    e.preventDefault();
                    s.focus();
                }
            }
        });
        window.addEventListener('resize', function() {
            if (!isMobile() && drawerOpen()) closeDrawer();
        });

        /* ================= AJAX SPA ENGINE ================= */
        function shouldSkip(url) {
            try {
                var p = new URL(url, location.origin).pathname;
                return SKIP.some(function(s) {
                    return p.indexOf(s) === 0;
                });
            } catch (e) {
                return true;
            }
        }

        function ready(fn) {
            document.readyState !== 'loading' ? fn() : document.addEventListener('DOMContentLoaded', fn);
        }

        function runScripts(scope) {
            var originalDocumentAdd = document.addEventListener.bind(document);
            var originalWindowAdd = window.addEventListener.bind(window);
            var dcl = [], load = [];
            document.addEventListener = function(type, fn, opts) {
                if (type === 'DOMContentLoaded') {
                    dcl.push(fn);
                    return;
                }
                return originalDocumentAdd(type, fn, opts);
            };
            window.addEventListener = function(type, fn, opts) {
                if (type === 'load') {
                    load.push(fn);
                    return;
                }
                return originalWindowAdd(type, fn, opts);
            };
            scope.querySelectorAll('script').forEach(function(old) {
                if (old.textContent.indexOf('__ndBoot') !== -1) return;
                if (old.src && document.querySelector('script[src="' + old.getAttribute('src') + '"]')) return;
                var s = document.createElement('script');
                [].forEach.call(old.attributes, function(a) {
                    s.setAttribute(a.name, a.value);
                });
                s.textContent = old.textContent;
                old.parentNode.replaceChild(s, old);
            });
            function runReadyCallbacks(callbacks) {
                callbacks.forEach(function(fn) {
                    try {
                        fn({});
                    } catch (e) {
                        console.error('[ND] script error:', e);
                        showNavigationError(e);
                    }
                });
            }
            runReadyCallbacks(dcl);
            runReadyCallbacks(load);
            document.addEventListener = originalDocumentAdd;
            window.addEventListener = originalWindowAdd;
        }

        /* ⭐ load fetched page's HEAD scripts (Chart.js etc.) before swapping */
        function ensureHeadScripts(doc) {
            var jobs = [];
            doc.querySelectorAll('script[src]').forEach(function(sc) {
                var src = sc.getAttribute('src');
                if (!document.querySelector('script[src="' + src + '"]')) {
                    jobs.push(new Promise(function(resolve, reject) {
                        var s = document.createElement('script');
                        s.src = src;
                        s.onload = resolve;
                        s.onerror = function() { reject(new Error('Could not load page script: ' + src)); };
                        document.head.appendChild(s);
                    }));
                }
            });
            return Promise.all(jobs);
        }

        var pageStates = window.__adminPageStates || (window.__adminPageStates = new Map());

        function newPageStateId() {
            return Date.now().toString(36) + Math.random().toString(36).slice(2);
        }

        function ensurePageState() {
            var state = history.state || {};
            if (state.__adminPageId) return state.__adminPageId;
            var id = newPageStateId();
            history.replaceState(Object.assign({}, state, { __adminPageId: id }), '', location.href);
            return id;
        }

        function capturePageState() {
            return {
                fields: Array.prototype.map.call(document.querySelectorAll('input,textarea,select'), function(field, index) {
                    return {
                        index: index,
                        id: field.id || '',
                        type: field.type || '',
                        value: field.type === 'file' ? '' : field.value,
                        checked: !!field.checked,
                        selectedIndex: field.selectedIndex
                    };
                }),
                scrollY: window.scrollY
            };
        }

        function restorePageState(saved) {
            if (!saved || !saved.fields) return;
            var fields = document.querySelectorAll('input,textarea,select');
            saved.fields.forEach(function(item) {
                var field = item.id ? document.getElementById(item.id) : fields[item.index];
                if (!field || field.type === 'file' || field.type !== item.type) return;
                if (field.type === 'checkbox' || field.type === 'radio') field.checked = item.checked;
                else if (field.tagName === 'SELECT') field.selectedIndex = item.selectedIndex;
                else field.value = item.value;
                field.dispatchEvent(new Event(field.tagName === 'SELECT' || field.type === 'checkbox' ? 'change' : 'input', { bubbles: true }));
            });
            window.scrollTo(0, saved.scrollY || 0);
        }

        function showNavigationError(error) {
            var message = error && error.message ? error.message : 'Could not load this page. Please try again.';
            if (window.Swal) {
                window.Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: message,
                    showConfirmButton: false,
                    timer: 3500,
                    timerProgressBar: true
                });
            } else {
                window.alert(message);
            }
        }

        var navLock = false;

        function go(url, push, restoreStateId) {
            if (navLock) return;
            var target;
            try {
                target = new URL(url, location.origin);
            } catch (e) {
                return;
            }
            if (shouldSkip(target.href)) {
                window.location.href = target.href;
                return;
            }
            if (push) {
                pageStates.set(ensurePageState(), capturePageState());
            }
            navLock = true;
            fetch(target.href, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    },
                    credentials: 'same-origin'
                })
                .then(function(res) {
                    if (res.status === 401 || res.status === 419 || res.status === 403) {
                        window.location.href = target.href;
                        throw {
                            silent: true
                        };
                    }
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.text().then(function(html) {
                        return {
                            html: html,
                            finalUrl: res.url || target.href
                        };
                    });
                })
                .then(function(r) {
                    var fu = new URL(r.finalUrl, location.origin);
                    if (fu.pathname.indexOf(PREFIX) !== 0 || shouldSkip(r.finalUrl)) {
                        window.location.href = r.finalUrl;
                        return;
                    }
                    var doc = new DOMParser().parseFromString(r.html, 'text/html');

                    document.querySelectorAll('head style[data-page]').forEach(function(el) {
                        el.remove();
                    });
                    doc.querySelectorAll('head style').forEach(function(el) {
                        var c = document.createElement('style');
                        c.textContent = el.textContent;
                        c.setAttribute('data-page', '');
                        document.head.appendChild(c);
                    });
                    document.title = doc.title;

                    return ensureHeadScripts(doc).then(function() {
                        return {
                            doc: doc,
                            finalUrl: r.finalUrl,
                            push: push,
                            restoreStateId: restoreStateId
                        };
                    });
                })
                .then(function(p) {
                    if (!p) return;
                    document.body.innerHTML = p.doc.body.innerHTML;
                    window.scrollTo(0, 0);
                    runScripts(document.body);
                    initReveal();
                    runCounters();
                    tickClock();
                    closeDD();
                    syncThemeIcon();
                    ensureThemeVars();
                    refreshNotifications();
                    if (p.restoreStateId) restorePageState(pageStates.get(p.restoreStateId));
                    if (p.push) {
                        try {
                            var pageId = newPageStateId();
                            history.pushState({ __adminPageId: pageId }, '', p.finalUrl);
                        } catch (e) {}
                    }
                    navLock = false;
                })
                .catch(function(err) {
                    navLock = false;
                    if (err && err.silent) return;
                    showNavigationError(err);
                });
        }
        window.adminAjaxNavigate = function(url) {
            go(url, true);
        };

        /* ---------- boot ---------- */
        ready(function() {
            ensureThemeVars();
            try {
                if (localStorage.getItem('nb-theme') === 'dark') document.body.classList.add('dark');
            } catch (e) {}
            syncThemeIcon();
            tickClock();
            document.querySelectorAll('head style').forEach(function(el) {
                if (el.id !== 'ndThemeVars') el.setAttribute('data-page', '');
            });
            initReveal(); /* ← data visible IMMEDIATELY */
            runCounters();
            refreshNotifications();

            /* SPA link interception — /admin links only */
            document.addEventListener('click', function(e) {
                if (e.defaultPrevented) return;
                var a = e.target.closest('a');
                if (!a) return;
                var href = a.getAttribute('href');
                if (!href || href.charAt(0) === '#') return;
                if (/^(javascript:|mailto:|tel:)/.test(href)) return;
                if (a.target === '_blank' || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
                var u;
                try {
                    u = new URL(a.href, location.origin);
                } catch (err) {
                    return;
                }
                if (u.origin !== location.origin) return;
                if (u.pathname.indexOf(PREFIX) !== 0) return;
                if (u.pathname === location.pathname && u.hash) return;

                e.preventDefault();
                go(u.href, true);
            });

            if (!window.__ajaxNavigationRouter) window.addEventListener('popstate', function() {
                var state = history.state || {};
                go(location.href, false, state.__adminPageId || null);
            });
        });
    })();
</script>