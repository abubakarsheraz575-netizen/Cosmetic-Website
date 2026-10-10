{{-- ============================================================
     MY PROFILE — Cosmetics Store (Light Glass)
     INSTANT content · Dedicated JSON endpoints · Dynamic beauty completion
============================================================ --}}

@php
$user = auth()->user();
$firstName = explode(' ', trim($user->name))[0];
$initials = collect(explode(' ', trim($user->name)))
->filter()->map(fn ($w) => strtoupper(substr($w, 0, 1)))
->take(2)->implode('');

/* ── REAL stats ── */
$ordersCount = \App\Models\Order::where('user_id', $user->id)->count();
$wishCount = $user->wishlist()->count();
$reviewsCount = $user->reviews()->count();
$totalSpent = (float) \App\Models\Order::where('user_id', $user->id)->where('payment_status', 'paid')->sum('total');

/* ── Beauty profile: show only saved preferences ── */
$bpSkin = $user->skin_type;
$bpHair = $user->hair_type;
$bpShade = $user->shade_match;
$bpLoves = $user->loves;

$filled = collect([$bpSkin, $bpHair, $bpShade, $bpLoves])
->filter(fn ($value) => $value !== null && trim((string) $value) !== '')
->count();
$pct = (int) round($filled / 4 * 100);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}?v=3">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Profile — Cosmetics Store</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Allura&display=swap" rel="stylesheet">
    @include('user.include.base')
    <style>
        .page-title h1 {
            color: var(--ink);
            font-size: 22px;
            font-weight: 600;
        }

        .page-title p {
            font-size: 12.5px;
            color: rgba(22, 35, 60, .5);
            margin-top: 3px;
        }

        /* ⚡ INSTANT CONTENT — kill the reveal delay completely on this page */
        .reveal {
            opacity: 1 !important;
            transform: none !important;
            animation: none !important;
            transition: none !important;
        }

        /* ══════════ HERO ══════════ */
        .prof-hero {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 28px;
            padding: 34px 38px;
            margin-bottom: 26px;
        }

        .prof-hero::before {
            content: 'glow';
            position: absolute;
            right: 34px;
            top: -26px;
            font-family: 'Allura', cursive;
            font-size: 110px;
            line-height: 1;
            color: rgba(56, 170, 180, .12);
            pointer-events: none;
        }

        .prof-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(100deg, rgba(56, 170, 180, .09), transparent 55%);
            pointer-events: none;
        }

        .big-avatar {
            position: relative;
            width: 96px;
            height: 96px;
            flex-shrink: 0;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: 34px;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, var(--teal), var(--teal-2));
            box-shadow: 0 12px 30px rgba(56, 170, 180, .45);
            z-index: 2;
        }

        .big-avatar::before {
            content: '';
            position: absolute;
            inset: -7px;
            border-radius: 50%;
            border: 2px dashed rgba(56, 170, 180, .45);
            animation: ringSpin 14s linear infinite;
        }

        @keyframes ringSpin {
            to {
                transform: rotate(360deg);
            }
        }

        .big-avatar .online {
            position: absolute;
            right: 4px;
            bottom: 4px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #2ec181;
            border: 3px solid #fff;
            box-shadow: 0 0 12px rgba(46, 193, 129, .7);
        }

        .ph-info {
            position: relative;
            z-index: 2;
            min-width: 220px;
        }

        .ph-name {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .ph-name h2 {
            color: var(--ink);
            font-size: 24px;
            font-weight: 700;
            letter-spacing: .3px;
        }

        .member-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 13px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            background: rgba(56, 170, 180, .12);
            border: 1px solid rgba(56, 170, 180, .35);
            color: var(--teal-2);
        }

        .ph-mail {
            margin-top: 6px;
            font-size: 13.5px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .ph-mail svg {
            width: 15px;
            height: 15px;
            color: var(--teal-2);
        }

        .ph-since {
            margin-top: 4px;
            font-size: 12px;
            color: rgba(22, 35, 60, .5);
        }

        .ph-stats {
            position: relative;
            z-index: 2;
            margin-left: auto;
            display: flex;
            gap: 30px;
        }

        .phs {
            text-align: center;
        }

        .phs b {
            display: block;
            color: var(--ink);
            font-size: 21px;
            font-weight: 700;
            line-height: 1.15;
        }

        .phs span {
            font-size: 10px;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: rgba(22, 35, 60, .45);
        }

        /* ══════════ GRID + CARDS ══════════ */
        .prof-grid {
            display: grid;
            grid-template-columns: 1.2fr .8fr;
            align-items: stretch;
            gap: 20px;
            margin-bottom: 20px;
        }

        .prof-grid2 {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 22px 24px 0;
        }

        .card-head h3 {
            color: var(--ink);
            font-size: 15px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-head h3 svg {
            width: 17px;
            height: 17px;
            color: var(--teal-2);
        }

        .edit-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 17px;
            border-radius: 11px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            border: none;
            cursor: pointer;
            background: linear-gradient(135deg, var(--teal), var(--teal-2));
            color: #fff;
            box-shadow: 0 8px 20px rgba(56, 170, 180, .35);
            transition: all .3s;
        }

        .edit-btn svg {
            width: 13px;
            height: 13px;
        }

        .edit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 26px rgba(56, 170, 180, .45);
        }

        .info-list {
            padding: 18px 24px 24px;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 13px 0;
            border-bottom: 1px dashed rgba(22, 35, 60, .1);
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .ir-ic {
            width: 42px;
            height: 42px;
            flex-shrink: 0;
            border-radius: 12px;
            display: grid;
            place-items: center;
            background: rgba(56, 170, 180, .1);
            color: var(--teal-2);
            transition: all .3s;
        }

        .info-row:hover .ir-ic {
            background: linear-gradient(135deg, var(--teal), var(--teal-2));
            color: #fff;
            box-shadow: 0 8px 18px rgba(56, 170, 180, .35);
        }

        .ir-ic svg {
            width: 18px;
            height: 18px;
        }

        .ir-txt {
            flex: 1;
            min-width: 0;
        }

        .ir-txt span {
            display: block;
            font-size: 10.5px;
            letter-spacing: 1.4px;
            text-transform: uppercase;
            color: rgba(22, 35, 60, .42);
        }

        .ir-txt b {
            display: block;
            color: var(--ink);
            font-size: 13.5px;
            font-weight: 500;
            margin-top: 2px;
            word-break: break-all;
        }

        .ir-txt small {
            font-size: 11px;
            color: rgba(22, 35, 60, .4);
        }

        /* beauty profile */
        .prof-grid>section {
            min-width: 0;
        }

        .prof-grid>section:last-child {
            display: flex;
            flex-direction: column;
        }

        .bp-body {
            padding: 20px 24px 24px;
            display: flex;
            gap: 22px;
            align-items: center;
            flex: 1;
        }

        .ring-wrap {
            position: relative;
            width: 120px;
            height: 120px;
            flex-shrink: 0;
        }

        .ring-wrap svg {
            transform: rotate(-90deg);
        }

        .ring-bg {
            fill: none;
            stroke: rgba(22, 35, 60, .08);
            stroke-width: 9;
        }

        .ring-val {
            fill: none;
            stroke: url(#ringGrad);
            stroke-width: 9;
            stroke-linecap: round;
            stroke-dasharray: 314.16;
            transition: stroke-dashoffset 1.4s cubic-bezier(.22, 1, .36, 1);
        }

        .ring-txt {
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            text-align: center;
        }

        .ring-txt b {
            color: var(--ink);
            font-size: 22px;
            font-weight: 700;
            display: block;
            line-height: 1;
        }

        .ring-txt span {
            font-size: 9px;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: rgba(22, 35, 60, .5);
        }

        .bp-list {
            flex: 1;
            align-self: stretch;
            display: flex;
            flex-direction: column;
            justify-content: space-evenly;
            min-width: 0;
        }

        .bp-list li {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px dashed rgba(22, 35, 60, .12);
            font-size: 12.5px;
            gap: 12px;
        }

        .bp-list li:last-child {
            border-bottom: none;
        }

        .bp-list li span {
            color: rgba(22, 35, 60, .5);
        }

        .bp-list li b {
            color: var(--ink);
            font-weight: 500;
        }

        .m-field select {
            padding-right: 42px;
            background-position: right 15px center;
        }

        /* security rows */
        .sec-list {
            padding: 14px 24px 24px;
        }

        .sec-row {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px dashed rgba(22, 35, 60, .1);
        }

        .sec-row:last-child {
            border-bottom: none;
        }

        .sec-act {
            margin-left: auto;
            padding: 8px 16px;
            border-radius: 10px;
            border: 1px solid var(--glass-border);
            background: #fff;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .8px;
            text-transform: uppercase;
            color: rgba(22, 35, 60, .65);
            transition: all .3s;
            cursor: pointer;
        }

        .sec-act:hover {
            border-color: rgba(56, 170, 180, .55);
            color: var(--teal-2);
            transform: translateY(-2px);
        }

        .ok-dot {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            color: #1f9d63;
            font-weight: 600;
        }

        .ok-dot i {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #2ec181;
            box-shadow: 0 0 8px rgba(46, 193, 129, .7);
        }

        /* ══════════ MODALS ══════════ */
        .modal-ov {
            position: fixed;
            inset: 0;
            z-index: 200;
            display: grid;
            place-items: center;
            padding: 20px;
            background: rgba(22, 35, 60, .45);
            backdrop-filter: blur(6px);
            opacity: 0;
            visibility: hidden;
            transition: all .3s;
        }

        .modal-ov.open {
            opacity: 1;
            visibility: visible;
        }

        .modal-card {
            width: min(460px, 100%);
            background: rgba(255, 255, 255, .92);
            backdrop-filter: blur(22px) saturate(140%);
            border: 1px solid var(--glass-border);
            border-radius: 22px;
            padding: 34px 32px 30px;
            box-shadow: 0 34px 80px rgba(31, 51, 89, .3);
            transform: translateY(24px) scale(.95);
            transition: transform .35s cubic-bezier(.22, 1, .36, 1);
            position: relative;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-ov.open .modal-card {
            transform: none;
        }

        .m-close {
            position: absolute;
            top: 16px;
            right: 16px;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            border: none;
            background: rgba(22, 35, 60, .06);
            color: rgba(22, 35, 60, .55);
            display: grid;
            place-items: center;
            transition: all .25s;
            cursor: pointer;
        }

        .m-close:hover {
            background: rgba(224, 86, 110, .12);
            color: #e0566e;
            transform: rotate(90deg);
        }

        .m-tag {
            text-align: center;
            color: var(--teal);
            font-family: 'Montserrat', sans-serif;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        .m-title {
            text-align: center;
            color: var(--ink);
            font-size: 21px;
            font-weight: 600;
            margin: 6px 0 24px;
        }

        .m-title::after {
            content: '';
            display: block;
            width: 44px;
            height: 3px;
            margin: 12px auto 0;
            border-radius: 3px;
            background: linear-gradient(90deg, var(--teal), var(--teal-light));
        }

        .m-field {
            margin-bottom: 17px;
        }

        .m-field label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1.4px;
            text-transform: uppercase;
            color: rgba(22, 35, 60, .55);
            margin-bottom: 7px;
        }

        .m-field input,
        .m-field select {
            width: 100%;
            padding: 12px 15px;
            border-radius: 12px;
            border: 1px solid rgba(22, 35, 60, .12);
            background: rgba(22, 35, 60, .03);
            font-family: inherit;
            font-size: 14px;
            color: var(--ink);
            outline: none;
            transition: border-color .3s, box-shadow .3s, background .3s;
        }

        .m-field input:focus,
        .m-field select:focus {
            border-color: rgba(56, 170, 180, .7);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(56, 170, 180, .14);
        }

        .m-field input.has-err {
            border-color: rgba(224, 86, 110, .8);
        }

        .m-err {
            display: none;
            color: #d94560;
            font-size: 12px;
            margin-top: 5px;
        }

        .m-err.show {
            display: block;
        }

        .pw-eye {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(22, 35, 60, .4);
            font-size: 16px;
            display: grid;
            place-items: center;
            padding: 4px;
            cursor: pointer;
        }

        .pw-eye:hover {
            color: var(--ink);
        }

        .m-field .pw-wrap {
            position: relative;
        }

        .m-field .pw-wrap input {
            padding-right: 40px;
        }

        .strength {
            display: flex;
            gap: 5px;
            margin-top: 8px;
            align-items: center;
        }

        .strength i {
            height: 4px;
            flex: 1;
            border-radius: 9px;
            background: rgba(22, 35, 60, .1);
            transition: background .3s, box-shadow .3s;
        }

        .strength i.s1 {
            background: #e0566e;
            box-shadow: 0 0 8px rgba(224, 86, 110, .5);
        }

        .strength i.s2 {
            background: #e0851f;
            box-shadow: 0 0 8px rgba(224, 133, 31, .5);
        }

        .strength i.s3 {
            background: #c99a1f;
            box-shadow: 0 0 8px rgba(201, 154, 31, .5);
        }

        .strength i.s4 {
            background: #1f9d63;
            box-shadow: 0 0 8px rgba(31, 157, 99, .5);
        }

        .strength span {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: rgba(22, 35, 60, .4);
            min-width: 52px;
            text-align: right;
        }

        .m-save {
            width: 100%;
            margin-top: 6px;
            padding: 14px;
            border: none;
            border-radius: 13px;
            background: linear-gradient(135deg, var(--teal), var(--teal-2));
            color: #fff;
            font-family: inherit;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            box-shadow: 0 10px 24px rgba(56, 170, 180, .4);
            transition: all .3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .m-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 30px rgba(56, 170, 180, .5);
        }

        .m-save:disabled {
            opacity: .65;
            cursor: wait;
            transform: none;
        }

        .m-save .spin {
            display: none;
            width: 15px;
            height: 15px;
            border: 2px solid rgba(255, 255, 255, .35);
            border-top-color: #fff;
            border-radius: 50%;
            animation: mSpin .7s linear infinite;
        }

        .m-save.loading .spin {
            display: inline-block;
        }

        @keyframes mSpin {
            to {
                transform: rotate(360deg);
            }
        }

        .cos-toast {
            position: fixed;
            bottom: 28px;
            left: 50%;
            transform: translate(-50%, 90px);
            z-index: 300;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 22px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--teal), var(--teal-2));
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 16px 40px rgba(56, 170, 180, .5);
            opacity: 0;
            transition: all .45s cubic-bezier(.22, 1, .36, 1);
        }

        .cos-toast.show {
            transform: translate(-50%, 0);
            opacity: 1;
        }

        .cos-toast svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        .foot-note {
            text-align: center;
            margin-top: 34px;
            font-size: 12px;
            color: rgba(22, 35, 60, .4);
        }

        @media (max-width: 1100px) {
            .prof-grid {
                grid-template-columns: 1fr;
            }

            .ph-stats {
                margin-left: 0;
            }
        }

        @media (max-width: 560px) {
            .prof-hero {
                padding: 26px 22px;
            }

            .ph-stats {
                gap: 20px;
            }

            .modal-card {
                padding: 28px 22px 24px;
            }
        }
    </style>
</head>

<body>

    <div class="scene" aria-hidden="true">
        <span class="orb orb-1"></span><span class="orb orb-2"></span><span class="orb orb-3"></span>
    </div>

    <div class="layout">
        @include('user.include.sidebar')

        <main class="main">

            <header class="topbar reveal">
                <button class="hamburger" id="hamburger" aria-label="Open menu">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <line x1="3" y1="12" x2="21" y2="12" />
                        <line x1="3" y1="18" x2="21" y2="18" />
                    </svg>
                </button>
                <div class="page-title">
                    <h1>My Profile, {{ $firstName }} 👤</h1>
                    <p>Manage your personal details, preferences and security.</p>
                </div>
            </header>

            <!-- Hero -->
            <section class="prof-hero glass reveal">
                <div class="big-avatar">
                    <span id="avInitials">{{ $initials }}</span>
                    <span class="online"></span>
                </div>

                <div class="ph-info">
                    <div class="ph-name">
                        <h2 id="phName">{{ $user->name }}</h2>
                        <span class="member-chip">✦ Glow Member</span>
                    </div>
                    <p class="ph-mail">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="5" width="18" height="14" rx="3" />
                            <path d="m3 7 9 6 9-6" />
                        </svg>
                        <span id="phEmail">{{ $user->email }}</span>
                    </p>
                    <p class="ph-since">Member since {{ $user->created_at->format('F Y') }} · {{ $user->created_at->format('M d, Y') }}</p>
                </div>

                <div class="ph-stats">
                    <div class="phs"><b>{{ $ordersCount }}</b><span>Orders</span></div>
                    <div class="phs"><b>{{ $wishCount }}</b><span>Wishlist</span></div>
                    <div class="phs"><b>{{ $reviewsCount }}</b><span>Reviews</span></div>
                    <div class="phs"><b>£{{ number_format($totalSpent, 0) }}</b><span>Spent</span></div>
                </div>
            </section>

            <!-- Cards -->
            <div class="prof-grid">

                <!-- Personal info -->
                <section class="glass reveal">
                    <div class="card-head">
                        <h3>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                            Personal Information
                        </h3>
                        <button type="button" class="edit-btn" data-open="editModal">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 3a2.8 2.8 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z" />
                            </svg>
                            Edit Profile
                        </button>
                    </div>
                    <div class="info-list">
                        <div class="info-row">
                            <div class="ir-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg></div>
                            <div class="ir-txt"><span>Full Name</span><b id="infoName">{{ $user->name }}</b></div>
                        </div>
                        <div class="info-row">
                            <div class="ir-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                    <rect x="3" y="5" width="18" height="14" rx="3" />
                                    <path d="m3 7 9 6 9-6" />
                                </svg></div>
                            <div class="ir-txt"><span>Email Address</span><b id="infoEmail">{{ $user->email }}</b><small id="verifyNote">@if($user->email_verified_at)✓ Verified @else⚠ Not verified yet @endif</small></div>
                        </div>
                        <div class="info-row">
                            <div class="ir-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                    <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.9.5 2.8.7a2 2 0 0 1 1.7 2z" />
                                </svg></div>
                            <div class="ir-txt"><span>Phone</span><b id="infoPhone">{{ $user->phone ?: 'Not added yet' }}</b></div>
                        </div>
                        <div class="info-row">
                            <div class="ir-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                    <rect x="3" y="4" width="18" height="18" rx="2" />
                                    <line x1="16" y1="2" x2="16" y2="6" />
                                    <line x1="8" y1="2" x2="8" y2="6" />
                                    <line x1="3" y1="10" x2="21" y2="10" />
                                </svg></div>
                            <div class="ir-txt"><span>Member Since</span><b>{{ $user->created_at->format('F d, Y') }}</b></div>
                        </div>
                    </div>
                </section>

                <!-- Beauty profile — ring filled from display data (verified ≈ 88%) -->
                <section class="glass reveal">
                    <div class="card-head">
                        <h3>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2l2.4 6.8L21 11l-6.6 2.2L12 20l-2.4-6.8L3 11l6.6-2.2L12 2z" />
                            </svg>
                            Beauty Profile
                        </h3>
                        <button type="button" class="edit-btn" data-open="prefsModal">Edit</button>
                    </div>
                    <div class="bp-body">
                        <div class="ring-wrap">
                            <svg width="120" height="120" viewBox="0 0 120 120">
                                <defs>
                                    <linearGradient id="ringGrad" x1="0" y1="0" x2="1" y2="1">
                                        <stop offset="0%" stop-color="#38aab4" />
                                        <stop offset="100%" stop-color="#2b7fb8" />
                                    </linearGradient>
                                </defs>
                                <circle class="ring-bg" cx="60" cy="60" r="50" />
                                <circle class="ring-val" id="ringVal" cx="60" cy="60" r="50"
                                    style="stroke-dashoffset: {{ 314.16 * (1 - $pct / 100) }}" />
                            </svg>
                            <div class="ring-txt">
                                <div><b>{{ $pct }}%</b><span>Complete</span></div>
                            </div>
                        </div>
                        <ul class="bp-list" id="bpList">
                            <li><span>Skin Type</span><b>{{ $bpSkin ?: 'Not set' }}</b></li>
                            <li><span>Hair Type</span><b>{{ $bpHair ?: 'Not set' }}</b></li>
                            <li><span>Shade Match</span><b>{{ $bpShade ?: 'Not set' }}</b></li>
                            <li><span>Loves</span><b>{{ $bpLoves ?: 'Not set' }}</b></li>
                        </ul>
                    </div>
                </section>
            </div>

            <!-- Security -->
            <div class="prof-grid2">
                <section class="glass reveal">
                    <div class="card-head">
                        <h3>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                <rect x="4.5" y="10.5" width="15" height="10" rx="3" />
                                <path d="M8 10.5V7a4 4 0 1 1 8 0v3.5" />
                            </svg>
                            Account Security
                        </h3>
                    </div>
                    <div class="sec-list">
                        <div class="sec-row">
                            <div class="ir-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                    <rect x="4.5" y="10.5" width="15" height="10" rx="3" />
                                    <path d="M8 10.5V7a4 4 0 1 1 8 0v3.5" />
                                </svg></div>
                            <div class="ir-txt"><span>Password</span><b>••••••••</b></div>
                            <span class="ok-dot"><i></i>Strong</span>
                            <button type="button" class="sec-act" data-open="pwModal">Change</button>
                        </div>
                        <div class="sec-row">
                            <div class="ir-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                    <rect x="2" y="4" width="20" height="14" rx="2" />
                                    <line x1="8" y1="21" x2="16" y2="21" />
                                    <line x1="12" y1="18" x2="12" y2="21" />
                                </svg></div>
                            <div class="ir-txt"><span>Email Verification</span><b id="secVerify">{{ $user->email_verified_at ? 'Verified ✓' : 'Pending — check your inbox' }}</b></div>
                            <span class="ok-dot" id="secVerifyDot" @if(!$user->email_verified_at) style="display:none" @endif><i></i>Secure</span>
                        </div>
                        <div class="sec-row">
                            <div class="ir-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                </svg></div>
                            <div class="ir-txt"><span>Two-Factor Auth</span><b>Add an extra layer of protection</b></div>
                            <button type="button" class="sec-act" disabled style="opacity:.5;cursor:not-allowed">Coming Soon</button>
                        </div>
                    </div>
                </section>
            </div>

            <p class="foot-note">Cosmetics Store · Beauty Pronounced 💫</p>
        </main>
    </div>

    <!-- ══════════ EDIT PROFILE MODAL ══════════ -->
    <div class="modal-ov {{ $errors->hasAny(['name', 'email', 'phone']) ? 'open' : '' }}" id="editModal">
        <div class="modal-card">
            <button type="button" class="m-close" data-close aria-label="Close">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
            <p class="m-tag">Update Details</p>
            <h3 class="m-title">Edit Profile</h3>

            <form id="editForm" action="{{ route('user.profile.update') }}" method="post" novalidate>
                @csrf
                @method('PATCH')
                <div class="m-field">
                    <label for="eName">Full Name</label>
                    <input id="eName" type="text" name="name" value="{{ old('name', $user->name) }}" required>
                    <p class="m-err {{ $errors->has('name') ? 'show' : '' }}" data-err="name">{{ $errors->first('name') }}</p>
                </div>
                <div class="m-field">
                    <label for="eEmail">Email Address</label>
                    <input id="eEmail" type="email" name="email" value="{{ old('email', $user->email) }}" required>
                    <p class="m-err {{ $errors->has('email') ? 'show' : '' }}" data-err="email">{{ $errors->first('email') }}</p>
                </div>
                <div class="m-field">
                    <label for="ePhone">Phone (optional)</label>
                    <input id="ePhone" type="tel" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+44 7700 900000">
                    <p class="m-err {{ $errors->has('phone') ? 'show' : '' }}" data-err="phone">{{ $errors->first('phone') }}</p>
                </div>
                <button type="submit" class="m-save" id="editSave">
                    <span class="spin"></span>
                    Save Changes
                </button>
            </form>
        </div>
    </div>

    <!-- ══════════ BEAUTY PREFERENCES MODAL ══════════ -->
    <div class="modal-ov {{ $errors->hasAny(['skin_type', 'hair_type', 'shade_match', 'loves']) ? 'open' : '' }}" id="prefsModal">
        <div class="modal-card">
            <button type="button" class="m-close" data-close aria-label="Close">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
            <p class="m-tag">Beauty Profile</p>
            <h3 class="m-title">Your Preferences</h3>

            <form id="prefsForm" action="{{ route('user.profile.prefs') }}" method="post" novalidate>
                @csrf
                @method('PATCH')
                <div class="m-field">
                    <label for="eSkin">Skin Type</label>
                    <select id="eSkin" name="skin_type">
                        <option value="" {{ old('skin_type', $bpSkin) ? '' : 'selected' }}>Choose skin type</option>
                        @foreach (['Combination', 'Dry', 'Oily', 'Normal', 'Sensitive'] as $opt)
                        <option value="{{ $opt }}" {{ old('skin_type', $bpSkin) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                    <p class="m-err {{ $errors->has('skin_type') ? 'show' : '' }}" data-err="skin_type">{{ $errors->first('skin_type') }}</p>
                </div>
                <div class="m-field">
                    <label for="eHair">Hair Type</label>
                    <select id="eHair" name="hair_type">
                        <option value="" {{ old('hair_type', $bpHair) ? '' : 'selected' }}>Choose hair type</option>
                        @foreach (['Wavy', 'Straight', 'Curly', 'Coily'] as $opt)
                        <option value="{{ $opt }}" {{ old('hair_type', $bpHair) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                    <p class="m-err {{ $errors->has('hair_type') ? 'show' : '' }}" data-err="hair_type">{{ $errors->first('hair_type') }}</p>
                </div>
                <div class="m-field">
                    <label for="eShade">Shade Match</label>
                    <input id="eShade" type="text" name="shade_match" value="{{ old('shade_match', $bpShade) }}" maxlength="50">
                    <p class="m-err {{ $errors->has('shade_match') ? 'show' : '' }}" data-err="shade_match">{{ $errors->first('shade_match') }}</p>
                </div>
                <div class="m-field">
                    <label for="eLoves">Loves</label>
                    <input id="eLoves" type="text" name="loves" value="{{ old('loves', $bpLoves) }}" maxlength="50">
                    <p class="m-err {{ $errors->has('loves') ? 'show' : '' }}" data-err="loves">{{ $errors->first('loves') }}</p>
                </div>
                <button type="submit" class="m-save" id="prefsSave">
                    <span class="spin"></span>
                    Save Preferences
                </button>
            </form>
        </div>
    </div>

    <!-- ══════════ CHANGE PASSWORD MODAL ══════════ -->
    <div class="modal-ov {{ $errors->hasAny(['current_password', 'password', 'password_confirmation']) ? 'open' : '' }}" id="pwModal">
        <div class="modal-card">
            <button type="button" class="m-close" data-close aria-label="Close">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
            <p class="m-tag">Security</p>
            <h3 class="m-title">Change Password</h3>

            <form id="pwForm" action="{{ route('user.password.update') }}" method="post" novalidate>
                @csrf
                @method('PATCH')
                <div class="m-field">
                    <label for="pCurrent">Current Password</label>
                    <div class="pw-wrap">
                        <input id="pCurrent" type="password" name="current_password" placeholder="••••••••" required>
                        <button type="button" class="pw-eye" data-eye="pCurrent">👁</button>
                    </div>
                    <p class="m-err" data-err="current_password"></p>
                </div>
                <div class="m-field">
                    <label for="pNew">New Password</label>
                    <div class="pw-wrap">
                        <input id="pNew" type="password" name="password" placeholder="••••••••" required>
                        <button type="button" class="pw-eye" data-eye="pNew">👁</button>
                    </div>
                    <div class="strength">
                        <i></i><i></i><i></i><i></i>
                        <span id="pwLabel">strength</span>
                    </div>
                    <p class="m-err" data-err="password"></p>
                </div>
                <div class="m-field">
                    <label for="pConfirm">Confirm New Password</label>
                    <div class="pw-wrap">
                        <input id="pConfirm" type="password" name="password_confirmation" placeholder="••••••••" required>
                        <button type="button" class="pw-eye" data-eye="pConfirm">👁</button>
                    </div>
                    <p class="m-err" data-err="password_confirmation"></p>
                </div>
                <button type="submit" class="m-save" id="pwSave">
                    <span class="spin"></span>
                    Update Password
                </button>
            </form>
        </div>
    </div>

    <div class="cos-toast" id="cosToast">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 6 9 17l-5-5" />
        </svg>
        <span id="toastMsg">Saved!</span>
    </div>

    <div class="overlay" id="overlay"></div>

    <script>
        (function() {
            /* ══════ ring is server-rendered (instant) — helper for after-save updates ══════ */
            function setRing(pct) {
                var ring = document.getElementById('ringVal');
                if (ring) ring.style.strokeDashoffset = (314.16 * (1 - pct / 100)).toFixed(2);
                var txt = document.querySelector('.ring-txt b');
                if (txt) txt.textContent = pct + '%';
            }

            function recomputeRing(u) {
                var filled = 0;
                if ((u.skin_type || '').trim()) filled++;
                if ((u.hair_type || '').trim()) filled++;
                if ((u.shade_match || '').trim()) filled++;
                if ((u.loves || '').trim()) filled++;
                setRing(Math.round(filled / 4 * 100));
            }

            /* ══════ helpers ══════ */
            function toast(msg) {
                if (typeof window.showToast === 'function') {
                    window.showToast(msg, 'success');
                    return;
                }
                var t = document.getElementById('cosToast');
                var message = document.getElementById('toastMsg');
                if (!t || !message) return;
                message.textContent = msg;
                t.classList.add('show');
                clearTimeout(t.__timer);
                t.__timer = setTimeout(function() {
                    t.classList.remove('show');
                }, 3200);
            }

            @if(session('status') === 'profile-updated')
            toast('Profile updated successfully!');
            @elseif(session('status') === 'preferences-updated')
            toast('Beauty profile saved!');
            @elseif(session('status') === 'password-updated')
            toast('Password updated successfully!');
            @endif

            function showErrors(form, errors) {
                form.querySelectorAll('.m-err').forEach(function(el) {
                    el.classList.remove('show');
                });
                form.querySelectorAll('.has-err').forEach(function(el) {
                    el.classList.remove('has-err');
                });
                Object.keys(errors || {}).forEach(function(field) {
                    var msgs = errors[field];
                    var input = form.querySelector('[name="' + field + '"]');
                    var errEl = form.querySelector('[data-err="' + field + '"]');
                    if (input) input.classList.add('has-err');
                    if (errEl) {
                        errEl.textContent = Array.isArray(msgs) ? msgs[0] : msgs;
                        errEl.classList.add('show');
                    }
                });
            }

            function clearErrs(form) {
                form.querySelectorAll('.m-err').forEach(function(el) {
                    el.classList.remove('show');
                });
                form.querySelectorAll('.has-err').forEach(function(el) {
                    el.classList.remove('has-err');
                });
            }

            /* ══════ modal system ══════ */
            function closeModals() {
                document.querySelectorAll('.modal-ov.open').forEach(function(m) {
                    m.classList.remove('open');
                });
            }
            document.querySelectorAll('[data-open]').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var m = document.getElementById(btn.getAttribute('data-open'));
                    if (m) m.classList.add('open');
                });
            });
            document.querySelectorAll('[data-close]').forEach(function(btn) {
                btn.addEventListener('click', closeModals);
            });
            document.querySelectorAll('.modal-ov').forEach(function(m) {
                m.addEventListener('click', function(e) {
                    if (e.target === m) closeModals();
                });
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeModals();
            });

            /* ══════ mobile sidebar ══════ */
            var sidebar = document.getElementById('sidebar');
            var overlay = document.getElementById('overlay');
            var hamburger = document.getElementById('hamburger');

            function closeSide() {
                if (sidebar) sidebar.classList.remove('open');
                if (overlay) overlay.classList.remove('show');
            }
            if (hamburger && sidebar && overlay) {
                hamburger.addEventListener('click', function() {
                    sidebar.classList.toggle('open');
                    overlay.classList.toggle('show');
                });
                overlay.addEventListener('click', closeSide);
            }

            /* Forms retain standard POST + method-spoof fallback when JavaScript is unavailable. */
            function sendPatch(url, fd) {
                return fetch(url, {
                    method: 'POST',
                    body: fd,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                }).then(function(r) {
                    return r.json().catch(function() {
                        return {};
                    }).then(function(d) {
                        return {
                            ok: r.ok,
                            status: r.status,
                            data: d
                        };
                    });
                });
            }

            /* ══════ EDIT PROFILE ══════ */
            var editForm = document.getElementById('editForm');
            var editSave = document.getElementById('editSave');

            editForm.addEventListener('submit', function(e) {
                e.preventDefault();
                clearErrs(editForm);
                editSave.classList.add('loading');
                editSave.disabled = true;

                sendPatch(editForm.action, new FormData(editForm))
                    .then(function(res) {
                        if (res.ok && res.data.ok) {
                            var u = res.data.user;
                            document.getElementById('phName').textContent = u.name;
                            document.getElementById('phEmail').textContent = u.email;
                            document.getElementById('infoName').textContent = u.name;
                            document.getElementById('infoEmail').textContent = u.email;
                            document.getElementById('infoPhone').textContent = u.phone || 'Not added yet';
                            document.getElementById('verifyNote').textContent = u.verified ? '✓ Verified' : '⚠ Not verified yet';
                            document.getElementById('secVerify').textContent = u.verified ? 'Verified ✓' : 'Pending — check your inbox';
                            document.getElementById('secVerifyDot').style.display = u.verified ? '' : 'none';
                            document.getElementById('avInitials').textContent =
                                u.name.trim().split(/\s+/).slice(0, 2).map(function(w) {
                                    return w[0].toUpperCase();
                                }).join('');

                            closeModals();
                            toast(res.data.message);
                            recomputeRing(u, u.verified);
                        } else if (res.status === 422 || res.data.errors) {
                            showErrors(editForm, res.data.errors || {});
                        } else {
                            toast(res.data.message || 'Could not save. Please try again.');
                        }
                    })
                    .catch(function() {
                        toast('Network error — please try again.');
                    })
                    .finally(function() {
                        editSave.classList.remove('loading');
                        editSave.disabled = false;
                    });
            });

            /* ══════ BEAUTY PREFERENCES ══════ */
            var prefsForm = document.getElementById('prefsForm');
            var prefsSave = document.getElementById('prefsSave');

            prefsForm.addEventListener('submit', function(e) {
                e.preventDefault();
                clearErrs(prefsForm);
                prefsSave.classList.add('loading');
                prefsSave.disabled = true;

                sendPatch(prefsForm.action, new FormData(prefsForm))
                    .then(function(res) {
                        if (res.ok && res.data.ok) {
                            var u = res.data.user;
                            var bpList = document.getElementById('bpList');
                            bpList.textContent = '';
                            [
                                ['Skin Type', u.skin_type],
                                ['Hair Type', u.hair_type],
                                ['Shade Match', u.shade_match],
                                ['Loves', u.loves]
                            ]
                            .forEach(function(item) {
                                var li = document.createElement('li');
                                var label = document.createElement('span');
                                var value = document.createElement('b');
                                label.textContent = item[0];
                                value.textContent = item[1] || 'Not set';
                                li.appendChild(label);
                                li.appendChild(value);
                                bpList.appendChild(li);
                            });

                            /* Match the dashboard: each saved beauty preference is worth 25%. */
                            recomputeRing({
                                skin_type: u.skin_type,
                                hair_type: u.hair_type,
                                shade_match: u.shade_match,
                                loves: u.loves
                            });

                            closeModals();
                            toast(res.data.message);
                        } else if (res.status === 422 || res.data.errors) {
                            showErrors(prefsForm, res.data.errors || {});
                        } else {
                            toast(res.data.message || 'Could not save. Please try again.');
                        }
                    })
                    .catch(function() {
                        toast('Network error — please try again.');
                    })
                    .finally(function() {
                        prefsSave.classList.remove('loading');
                        prefsSave.disabled = false;
                    });
            });

            /* ══════ PASSWORD STRENGTH ══════ */
            var pNew = document.getElementById('pNew');
            var bars = document.querySelectorAll('.strength i');
            var pwLabel = document.getElementById('pwLabel');
            var NAMES = ['strength', 'weak', 'fair', 'good', 'strong'];
            var CLRS = ['', '#e0566e', '#e0851f', '#c99a1f', '#1f9d63'];

            pNew.addEventListener('input', function() {
                var v = pNew.value;
                var score = 0;
                if (v.length >= 8) score++;
                if (/[A-Z]/.test(v)) score++;
                if (/[0-9]/.test(v)) score++;
                if (/[^A-Za-z0-9]/.test(v)) score++;
                if (v.length > 0 && v.length < 8) score = Math.min(score, 1);
                bars.forEach(function(b, i) {
                    b.className = i < score ? 's' + score : '';
                });
                pwLabel.textContent = NAMES[score];
                pwLabel.style.color = CLRS[score];
            });

            /* ══════ CHANGE PASSWORD ══════ */
            var pwForm = document.getElementById('pwForm');
            var pwSave = document.getElementById('pwSave');

            pwForm.addEventListener('submit', function(e) {
                e.preventDefault();
                clearErrs(pwForm);

                if (pNew.value !== document.getElementById('pConfirm').value) {
                    showErrors(pwForm, {
                        password_confirmation: ['Passwords do not match.']
                    });
                    return;
                }

                pwSave.classList.add('loading');
                pwSave.disabled = true;

                sendPatch(pwForm.action, new FormData(pwForm))
                    .then(function(res) {
                        if (res.ok && res.data.ok) {
                            closeModals();
                            toast(res.data.message);
                            pwForm.reset();
                            bars.forEach(function(b) {
                                b.className = '';
                            });
                            pwLabel.textContent = 'strength';
                            pwLabel.style.color = '';
                        } else if (res.status === 422 || res.data.errors) {
                            showErrors(pwForm, res.data.errors || {});
                        } else {
                            toast(res.data.message || 'Could not update password.');
                        }
                    })
                    .catch(function() {
                        toast('Network error — please try again.');
                    })
                    .finally(function() {
                        pwSave.classList.remove('loading');
                        pwSave.disabled = false;
                    });
            });

            /* password eyes */
            document.querySelectorAll('[data-eye]').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var input = document.getElementById(btn.getAttribute('data-eye'));
                    input.type = input.type === 'password' ? 'text' : 'password';
                });
            });
        })();
    </script>
</body>

</html>