{{-- ==========================================================
     ADMIN · USERS — v9 (namespaced JS · custom dropdowns ·
     live search · sort · seg filter · per-page · pagination ALWAYS ON)
========================================================== --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0e5a75">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Users — NiceDash Admin</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}?v=3">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box
        }

        :root {
            --bg: #f3f5f7;
            --card: #fff;
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
            --lime-soft: rgba(62, 197, 224, .08)
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            font-size: 14px;
            -webkit-text-size-adjust: 100%;
            -webkit-tap-highlight-color: transparent;
            overflow-x: clip
        }

        a {
            text-decoration: none;
            color: inherit
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

        .content {
            padding: 24px 26px calc(40px + env(safe-area-inset-bottom));
            display: grid;
            gap: 22px;
            min-width: 0
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
            transition: width .35s, transform .35s
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

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(16px)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        .fx {
            animation: fadeUp .55s cubic-bezier(.22, 1, .36, 1) both
        }

        .d1 {
            animation-delay: .05s
        }

        .d2 {
            animation-delay: .1s
        }

        .d3 {
            animation-delay: .15s
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            min-width: 0
        }

        .page-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px
        }

        .page-head h2 {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -.4px;
            display: flex;
            align-items: center;
            gap: 11px
        }

        .ph-ic {
            width: 42px;
            height: 42px;
            border-radius: 13px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, var(--forest), var(--forest-2));
            color: var(--lime);
            font-size: 21px;
            box-shadow: 0 10px 22px rgba(14, 90, 117, .3)
        }

        .page-head p {
            color: var(--muted);
            font-size: 13px;
            margin-top: 3px
        }

        .r-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 22px
        }

        .stat {
            position: relative;
            overflow: hidden;
            padding: 22px
        }

        .stat::after {
            content: '';
            position: absolute;
            right: -34px;
            top: -34px;
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(62, 197, 224, .14), transparent 70%)
        }

        .stat-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px
        }

        .stat .icon {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            font-size: 21px
        }

        .i-green {
            background: #e0f5fb;
            color: #0e8fb5
        }

        .i-blue {
            background: #e8f1fd;
            color: #2563eb
        }

        .i-red {
            background: #feecec;
            color: #dc2626
        }

        .i-violet {
            background: #f1ecfd;
            color: #7c3aed
        }

        body.dark .i-green {
            background: rgba(62, 197, 224, .12);
            color: #4ddcf5
        }

        body.dark .i-blue {
            background: rgba(96, 165, 250, .12);
            color: #60a5fa
        }

        body.dark .i-red {
            background: rgba(248, 113, 113, .12);
            color: #f87171
        }

        body.dark .i-violet {
            background: rgba(167, 139, 250, .12);
            color: #a78bfa
        }

        .big {
            font-size: 29px;
            font-weight: 800;
            letter-spacing: -.6px
        }

        .big.sm {
            font-size: 23px
        }

        .muted {
            color: var(--muted)
        }

        .small {
            font-size: 12.5px
        }

        .role-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--lime-soft);
            color: #0b5570;
            border: 1px solid rgba(62, 197, 224, .4);
            font-size: 11px;
            font-weight: 800;
            padding: 5px 11px;
            border-radius: 20px
        }

        body.dark .role-chip {
            color: var(--lime)
        }

        /* ===== ★ CUSTOM DROPDOWN (native select ka replacement) ===== */
        .dd {
            position: relative
        }

        .dd-btn {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            border: 1px solid var(--border);
            background: var(--bg);
            color: var(--text);
            font: 700 12.5px 'Plus Jakarta Sans', sans-serif;
            padding: 11px 16px;
            border-radius: 12px;
            cursor: pointer;
            transition: .25s;
            min-width: 140px;
            max-width: 100%
        }

        .dd-btn:hover {
            border-color: var(--lime)
        }

        .dd.open .dd-btn {
            border-color: var(--lime);
            box-shadow: 0 0 0 4px rgba(62, 197, 224, .15)
        }

        .dd-btn .dd-chev {
            margin-left: auto;
            transition: transform .3s;
            color: var(--muted);
            font-size: 16px
        }

        .dd.open .dd-chev {
            transform: rotate(180deg);
            color: var(--forest)
        }

        body.dark .dd.open .dd-chev {
            color: var(--lime)
        }

        .dd-menu {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            min-width: 100%;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: 0 18px 44px rgba(16, 24, 40, .18);
            padding: 6px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(8px) scale(.97);
            transform-origin: top left;
            transition: .28s cubic-bezier(.22, 1, .36, 1);
            z-index: 70
        }

        .dd.open .dd-menu {
            opacity: 1;
            visibility: visible;
            transform: none
        }

        .dd-item {
            display: flex;
            align-items: center;
            gap: 9px;
            width: 100%;
            text-align: left;
            padding: 10px 14px;
            border-radius: 10px;
            border: none;
            background: transparent;
            color: var(--text);
            font: 600 12.5px 'Plus Jakarta Sans', sans-serif;
            cursor: pointer;
            transition: .18s;
            white-space: nowrap
        }

        .dd-item:hover {
            background: var(--lime-soft);
            color: var(--forest)
        }

        body.dark .dd-item:hover {
            color: var(--lime)
        }

        .dd-item.on {
            background: linear-gradient(135deg, var(--forest), var(--forest-2));
            color: #fff;
            font-weight: 800
        }

        .dd-item.on::after {
            content: '✓';
            margin-left: auto;
            font-size: 11px
        }

        /* ===== TOOLBAR ===== */
        .table-card {
            overflow: hidden
        }

        .toolbar {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            padding: 18px 22px;
            border-bottom: 1px solid var(--border)
        }

        .t-search {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 11px 14px;
            width: 280px;
            max-width: 100%;
            transition: .3s;
            color: var(--muted)
        }

        .t-search:focus-within {
            border-color: var(--lime);
            box-shadow: 0 0 0 4px rgba(62, 197, 224, .15)
        }

        .t-search input {
            border: none;
            outline: none;
            background: transparent;
            color: var(--text);
            flex: 1;
            font: 600 14px 'Plus Jakarta Sans', sans-serif;
            min-width: 0
        }

        .seg {
            display: flex;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 11px;
            padding: 3px;
            margin-left: auto;
            max-width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none
        }

        .seg::-webkit-scrollbar {
            display: none
        }

        .seg-btn {
            border: none;
            background: transparent;
            color: var(--muted);
            font-weight: 700;
            font-size: 12.5px;
            padding: 7px 15px;
            border-radius: 9px;
            cursor: pointer;
            transition: .25s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
            font-family: inherit
        }

        .seg-btn.active {
            background: var(--forest);
            color: #fff;
            box-shadow: 0 4px 10px rgba(14, 90, 117, .3)
        }

        .seg-btn .cnt {
            font-size: 10.5px;
            background: rgba(62, 197, 224, .9);
            color: #08394b;
            border-radius: 20px;
            padding: 1px 7px;
            font-weight: 800
        }

        .seg-btn:not(.active) .cnt {
            background: var(--border);
            color: var(--muted)
        }

        /* ===== TABLE ===== */
        .table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 760px
        }

        th {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--muted);
            text-align: left;
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            background: rgba(243, 245, 247, .4);
            white-space: nowrap;
            user-select: none
        }

        body.dark th {
            background: rgba(11, 18, 32, .4)
        }

        th.sortable {
            cursor: pointer;
            transition: .2s
        }

        th.sortable:hover {
            color: var(--forest)
        }

        body.dark th.sortable:hover {
            color: var(--lime)
        }

        th .s-ar {
            font-size: 13px;
            opacity: .25;
            vertical-align: -1px;
            margin-left: 3px;
            transition: .2s
        }

        th.sorted {
            color: var(--forest)
        }

        body.dark th.sorted {
            color: var(--lime)
        }

        th.sorted .s-ar {
            opacity: 1
        }

        td {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            font-size: 13.5px;
            vertical-align: middle
        }

        tbody tr {
            transition: background .2s
        }

        @media(hover:hover) {
            tbody tr:hover {
                background: var(--lime-soft)
            }
        }

        tbody tr:last-child td {
            border-bottom: none
        }

        tbody tr.flash {
            animation: rowFlash 1.2s ease
        }

        @keyframes rowFlash {
            0% {
                background: rgba(62, 197, 224, .25)
            }

            100% {
                background: transparent
            }
        }

        .ucell {
            display: flex;
            align-items: center;
            gap: 13px;
            min-width: 220px
        }

        .u-av {
            width: 42px;
            height: 42px;
            min-width: 42px;
            border-radius: 13px;
            display: grid;
            place-items: center;
            font-size: 13px;
            font-weight: 800;
            color: #fff
        }

        .av-1 {
            background: linear-gradient(135deg, #0e5a75, #17739b)
        }

        .av-2 {
            background: linear-gradient(135deg, #2563eb, #60a5fa)
        }

        .av-3 {
            background: linear-gradient(135deg, #d97706, #fbbf24)
        }

        .av-4 {
            background: linear-gradient(135deg, #7c3aed, #a78bfa)
        }

        .av-5 {
            background: linear-gradient(135deg, #db2777, #f472b6)
        }

        .u-info strong {
            display: block;
            font-size: 13.5px
        }

        .u-info span {
            font-size: 12px;
            color: var(--muted)
        }

        .joined {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: var(--muted);
            font-size: 12.5px;
            white-space: nowrap
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 5px 13px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 700
        }

        .pill i {
            font-size: 14px
        }

        .pill.active {
            background: #dcfce7;
            color: #16a34a
        }

        .pill.blocked {
            background: #fee2e2;
            color: #dc2626
        }

        body.dark .pill.active {
            background: rgba(74, 222, 128, .12);
            color: #4ade80
        }

        body.dark .pill.blocked {
            background: rgba(248, 113, 113, .12);
            color: #f87171
        }

        .actions {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
            flex-wrap: wrap
        }

        .act {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 800;
            border: 1px solid transparent;
            cursor: pointer;
            transition: .25s
        }

        .act i {
            font-size: 15px
        }

        .act:disabled {
            opacity: .55;
            cursor: wait;
            transform: none !important
        }

        .act-block {
            background: rgba(239, 68, 68, .07);
            color: #ef4444;
            border-color: rgba(239, 68, 68, .25)
        }

        .act-block:hover {
            background: #ef4444;
            color: #fff;
            box-shadow: 0 10px 22px rgba(239, 68, 68, .3)
        }

        .act-active {
            background: rgba(22, 163, 74, .07);
            color: #16a34a;
            border-color: rgba(22, 163, 74, .25)
        }

        .act-active:hover {
            background: #16a34a;
            color: #fff;
            box-shadow: 0 10px 22px rgba(22, 163, 74, .3)
        }

        .tbl-hint {
            display: none;
            padding: 10px 22px;
            border-bottom: 1px solid var(--border);
            font-size: 11px;
            color: var(--muted);
            align-items: center;
            gap: 7px
        }

        /* ===== PAGINATION ===== */
        .t-foot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 22px;
            border-top: 1px solid var(--border);
            flex-wrap: wrap;
            gap: 12px
        }

        .t-foot .info {
            color: var(--muted);
            font-size: 12.5px
        }

        .pgn {
            display: flex;
            gap: 5px;
            align-items: center;
            flex-wrap: wrap
        }

        .pg-btn {
            min-width: 36px;
            height: 36px;
            padding: 0 8px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: var(--card);
            color: var(--text);
            font: 700 12.5px 'Plus Jakarta Sans', sans-serif;
            cursor: pointer;
            transition: .2s;
            display: inline-flex;
            align-items: center;
            justify-content: center
        }

        .pg-btn:hover:not(:disabled):not(.on) {
            border-color: var(--lime);
            background: var(--lime-soft)
        }

        .pg-btn.on {
            background: var(--forest);
            color: #fff;
            border-color: var(--forest);
            box-shadow: 0 4px 10px rgba(14, 90, 117, .3)
        }

        .pg-btn:disabled {
            opacity: .4;
            cursor: default
        }

        .pg-dots {
            color: var(--muted);
            padding: 0 4px;
            font-weight: 800
        }

        .empty {
            text-align: center;
            padding: 70px 20px
        }

        .empty .e-ic {
            width: 74px;
            height: 74px;
            margin: 0 auto 16px;
            border-radius: 22px;
            display: grid;
            place-items: center;
            font-size: 34px;
            background: var(--lime-soft);
            color: var(--forest)
        }

        body.dark .empty .e-ic {
            color: var(--lime)
        }

        .empty h4 {
            font-size: 16px;
            margin-bottom: 5px
        }

        .empty p {
            color: var(--muted);
            font-size: 13px
        }

        /* ===== SWAL ===== */
        .swal-popup {
            border-radius: 20px !important;
            background: #111c2e !important;
            color: #e7edf5 !important;
            border: 1px solid rgba(62, 197, 224, .18)
        }

        .swal-title {
            color: #fff !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-size: 20px !important
        }

        .swal-html-container {
            color: rgba(231, 237, 245, .65) !important;
            font-size: 13.5px !important
        }

        .swal-confirm {
            background: linear-gradient(135deg, #ef4444, #dc2626) !important;
            color: #fff !important;
            font-weight: 800;
            font-family: inherit;
            padding: 11px 22px !important;
            border-radius: 12px !important;
            border: none;
            cursor: pointer;
            font-size: 13px
        }

        .swal-cancel {
            background: transparent !important;
            color: rgba(231, 237, 245, .7) !important;
            border: 1px solid rgba(255, 255, 255, .18) !important;
            font-weight: 700;
            font-family: inherit;
            padding: 11px 22px !important;
            border-radius: 12px !important;
            cursor: pointer;
            font-size: 13px
        }

        .swal-toast {
            background: #111c2e !important;
            border: 1px solid rgba(62, 197, 224, .25) !important;
            color: #fff !important;
            border-radius: 14px !important
        }

        /* ===== RESPONSIVE ===== */
        @media(max-width:1250px) {
            .r-stats {
                grid-template-columns: 1fr 1fr
            }
        }

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

            .seg {
                margin-left: 0;
                width: 100%;
                justify-content: space-between
            }
        }

        @media(max-width:680px) {
            .r-stats {
                grid-template-columns: 1fr 1fr;
                gap: 14px
            }

            .content {
                padding: 16px 12px calc(40px + env(safe-area-inset-bottom));
                gap: 16px
            }

            .toolbar {
                padding: 14px 14px
            }

            .t-search {
                width: 100%
            }

            .t-search input {
                font-size: 16px
            }

            .tbl-hint {
                display: flex
            }

            .t-foot {
                padding: 13px 14px;
                justify-content: center
            }

            .stat {
                padding: 16px
            }

            .card {
                border-radius: 14px
            }

            .page-head h2 {
                font-size: 20px
            }
        }

        @media(max-width:480px) {
            .r-stats {
                grid-template-columns: 1fr
            }

            .content {
                padding: 14px 0 calc(40px + env(safe-area-inset-bottom))
            }

            .toolbar,
            .t-foot {
                padding-left: 12px;
                padding-right: 12px
            }

            .page-head h2 {
                font-size: 20px
            }

            .big {
                font-size: 24px
            }

            .big.sm {
                font-size: 20px
            }

            .act {
                padding: 7px 10px;
                font-size: 11px
            }
        }

        @media(max-width:360px) {
            .r-stats {
                grid-template-columns: 1fr
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

                <div class="page-head fx">
                    <div>
                        <h2><span class="ph-ic"><i class="ri-group-line"></i></span>Users</h2>
                        <p>Manage your customers — block or restore access anytime.</p>
                    </div>
                </div>

                <section class="r-stats">
                    <div class="card stat fx d1">
                        <div class="stat-top">
                            <div class="icon i-green"><i class="ri-group-line"></i></div>
                        </div>
                        <div class="big sm" id="statTotal">{{ number_format($stats['total']) }}</div>
                        <p class="muted small">Total registered users</p>
                    </div>
                    <div class="card stat fx d2">
                        <div class="stat-top">
                            <div class="icon i-blue"><i class="ri-user-follow-line"></i></div>
                        </div>
                        <div class="big sm" id="statActive">{{ number_format($stats['active']) }}</div>
                        <p class="muted small">Active — can log in</p>
                    </div>
                    <div class="card stat fx d3">
                        <div class="stat-top">
                            <div class="icon i-red"><i class="ri-forbid-line"></i></div>
                        </div>
                        <div class="big sm" id="statBlocked">{{ number_format($stats['blocked']) }}</div>
                        <p class="muted small">Blocked — login denied</p>
                    </div>
                    <div class="card stat fx d3">
                        <div class="stat-top">
                            <div class="icon i-violet"><i class="ri-user-add-line"></i></div>
                        </div>
                        <div class="big sm">{{ number_format($stats['new']) }}</div>
                        <p class="muted small">New this month</p>
                    </div>
                </section>

                <section class="card table-card fx d3">
                    <div class="toolbar">
                        <div class="t-search">
                            <i class="ri-search-line"></i>
                            <input type="text" id="usrSearch" oninput="usrGoPage(1)" placeholder="Search name or email...">
                        </div>
                        {{-- ★ CUSTOM PER-PAGE DROPDOWN --}}
                        <div class="dd" id="usrPer" data-value="10">
                            <button type="button" class="dd-btn" onclick="nxDdToggle('usrPer')">
                                <i class="ri-list-check"></i><span class="dd-label">10 rows</span><i class="ri-arrow-down-s-line dd-chev"></i>
                            </button>
                            <div class="dd-menu">
                                <button type="button" class="dd-item on" data-v="10" onclick="nxDdPick(this,'usrPer')">10 rows</button>
                                <button type="button" class="dd-item" data-v="25" onclick="nxDdPick(this,'usrPer')">25 rows</button>
                                <button type="button" class="dd-item" data-v="50" onclick="nxDdPick(this,'usrPer')">50 rows</button>
                                <button type="button" class="dd-item" data-v="999999" onclick="nxDdPick(this,'usrPer')">All rows</button>
                            </div>
                        </div>
                        <div class="seg" id="usrFilterSeg">
                            <button type="button" class="seg-btn active" data-filter="all" onclick="usrSetFilter(this)">All <span class="cnt" id="cntAll">{{ $stats['total'] }}</span></button>
                            <button type="button" class="seg-btn" data-filter="active" onclick="usrSetFilter(this)">Active <span class="cnt" id="cntActive">{{ $stats['active'] }}</span></button>
                            <button type="button" class="seg-btn" data-filter="blocked" onclick="usrSetFilter(this)">Blocked <span class="cnt" id="cntBlocked">{{ $stats['blocked'] }}</span></button>
                        </div>
                    </div>

                    <div class="tbl-hint"><i class="ri-drag-move-2-line"></i> Swipe the table sideways to see all columns</div>

                    <div class="table-wrap">
                        <table data-server-pagination="true">
                            <thead>
                                <tr>
                                    <th class="sortable" data-k="name" onclick="usrSort(this)">User<span class="s-ar">▼</span></th>
                                    <th>Role</th>
                                    <th class="sortable" data-k="ts" onclick="usrSort(this)">Joined<span class="s-ar">▼</span></th>
                                    <th class="sortable" data-k="active" onclick="usrSort(this)">Status<span class="s-ar">▼</span></th>
                                    <th style="text-align:right">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="usrBody">
                                @foreach($users as $u)
                                @php
                                $initials = collect(explode(' ', trim($u->name)))->filter()
                                ->map(fn ($w) => strtoupper(substr($w, 0, 1)))->take(2)->implode('');
                                $status = $u->is_blocked ? 'blocked' : 'active';
                                @endphp
                                <tr data-status="{{ $status }}"
                                    data-active="{{ $u->is_blocked ? 0 : 1 }}"
                                    data-ts="{{ $u->created_at->timestamp }}"
                                    data-hay="{{ strtolower($u->name.' '.$u->email) }}"
                                    data-block-url="{{ route('admin.users.block', $u) }}"
                                    data-unblock-url="{{ route('admin.users.unblock', $u) }}">
                                    <td>
                                        <div class="ucell">
                                            <div class="u-av av-{{ ($loop->index % 5) + 1 }}">{{ $initials }}</div>
                                            <div class="u-info">
                                                <strong>{{ $u->name }}</strong>
                                                <span>{{ $u->email }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="role-chip"><i class="ri-user-smile-line"></i>Customer</span></td>
                                    <td><span class="joined"><i class="ri-calendar-event-line"></i>{{ $u->created_at->format('M d, Y') }}</span></td>
                                    <td class="pill-cell">
                                        @if($u->is_blocked)
                                        <span class="pill blocked"><i class="ri-forbid-line"></i>Blocked</span>
                                        @else
                                        <span class="pill active"><i class="ri-checkbox-circle-line"></i>Active</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="actions">
                                            @if($u->is_blocked)
                                            <button type="button" class="act act-active" onclick="usrUnblock(this)" data-name="{{ $u->name }}"><i class="ri-check-double-line"></i>Set Active</button>
                                            @else
                                            <button type="button" class="act act-block" onclick="usrBlock(this)" data-name="{{ $u->name }}"><i class="ri-forbid-line"></i>Block</button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="t-foot">
                        <p class="info" id="usrInfo"></p>
                        <div class="pgn" id="usrPgn"></div>
                    </div>
                </section>

                <div class="card empty fx" id="usrEmpty" style="{{ $users->isEmpty() ? '' : 'display:none' }}">
                    <div class="e-ic"><i class="ri-user-search-line"></i></div>
                    <h4>No users yet</h4>
                    <p>Registered customers will appear here automatically.</p>
                </div>

            </main>
        </div>
    </div>

    <script>
        /* ═══ SHARED DROPDOWN ENGINE — har page pe same, EK dafa define (guarded) ═══ */
        if (!window.__nxDd) {
            window.__nxDd = true;
            window.nxDdToggle = function(id) {
                var dd = document.getElementById(id);
                if (!dd) return;
                var wasOpen = dd.classList.contains('open');
                nxDdCloseAll();
                if (!wasOpen) dd.classList.add('open');
            };
            window.nxDdCloseAll = function() {
                document.querySelectorAll('.dd.open').forEach(function(d) {
                    d.classList.remove('open');
                });
            };
            window.nxDdPick = function(item, ddId) {
                var dd = document.getElementById(ddId);
                if (!dd) return;
                dd.querySelectorAll('.dd-item').forEach(function(i) {
                    i.classList.remove('on');
                });
                item.classList.add('on');
                var lbl = dd.querySelector('.dd-label');
                if (lbl) lbl.textContent = item.textContent.trim();
                dd.dataset.value = item.dataset.v;
                dd.classList.remove('open');
                if (typeof window.nxDdChanged === 'function') window.nxDdChanged(ddId, item.dataset.v);
            };
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dd')) nxDdCloseAll();
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') nxDdCloseAll();
            });
        }

        /* ══════════ USERS PAGE JS — 100% namespaced (usr*) ══════════ */
        var usrCsrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
        var usrTbl = {
            q: '',
            filter: 'all',
            sortK: 'ts',
            sortD: -1,
            page: 1,
            per: 10
        };

        function usrEsc(s) {
            return (s === null || s === undefined ? '' : String(s))
                .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function usrToast(icon, msg) {
            if (window.Swal) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2600,
                    timerProgressBar: true,
                    icon: icon,
                    title: msg,
                    buttonsStyling: false,
                    customClass: {
                        popup: 'swal-toast'
                    }
                });
            } else {
                alert(msg);
            }
        }

        function usrConfirm(opts) {
            if (!window.Swal) return Promise.resolve(window.confirm(opts.title) ? {
                isConfirmed: true
            } : {
                isConfirmed: false
            });
            opts.buttonsStyling = false;
            opts.customClass = {
                popup: 'swal-popup',
                title: 'swal-title',
                htmlContainer: 'swal-html-container',
                confirmButton: 'swal-confirm',
                cancelButton: 'swal-cancel'
            };
            return Swal.fire(opts);
        }

        /* dropdown change hook — is page ka per-page */
        window.nxDdChanged = function(ddId, v) {
            if (ddId === 'usrPer') {
                usrTbl.per = parseInt(v, 10) || 10;
                usrGoPage(1);
            }
        };

        function usrRows() {
            return Array.prototype.slice.call(document.querySelectorAll('#usrBody tr'));
        }

        function usrGoPage(p) {
            usrTbl.page = p;
            usrApply();
        }

        function usrSort(th) {
            var k = th.dataset.k;
            if (usrTbl.sortK === k) usrTbl.sortD *= -1;
            else {
                usrTbl.sortK = k;
                usrTbl.sortD = 1;
            }
            document.querySelectorAll('th.sortable').forEach(function(t) {
                t.classList.remove('sorted');
                var ar = t.querySelector('.s-ar');
                if (ar) {
                    ar.textContent = '▼';
                    ar.style.opacity = '';
                }
            });
            th.classList.add('sorted');
            var a = th.querySelector('.s-ar');
            if (a) a.textContent = usrTbl.sortD === 1 ? '▲' : '▼';
            usrApply();
        }

        function usrSetFilter(btn) {
            document.querySelectorAll('#usrFilterSeg .seg-btn').forEach(function(b) {
                b.classList.remove('active');
            });
            btn.classList.add('active');
            usrTbl.filter = btn.dataset.filter;
            usrGoPage(1);
        }

        function usrApply() {
            var qEl = document.getElementById('usrSearch');
            var perDD = document.getElementById('usrPer');
            usrTbl.q = ((qEl && qEl.value) || '').toLowerCase().trim();
            usrTbl.per = parseInt((perDD && perDD.dataset.value) || '10', 10) || 10;

            var rows = usrRows().filter(function(tr) {
                var okQ = (tr.dataset.hay || '').indexOf(usrTbl.q) !== -1;
                var okF = usrTbl.filter === 'all' || tr.dataset.status === usrTbl.filter;
                return okQ && okF;
            });

            rows.sort(function(a, b) {
                var va = a.dataset[usrTbl.sortK] || '',
                    vb = b.dataset[usrTbl.sortK] || '';
                if (usrTbl.sortK === 'ts' || usrTbl.sortK === 'active') {
                    va = parseFloat(va) || 0;
                    vb = parseFloat(vb) || 0;
                    return (va - vb) * usrTbl.sortD;
                }
                return va.localeCompare(vb) * usrTbl.sortD;
            });

            var total = rows.length,
                tp = Math.max(1, Math.ceil(total / usrTbl.per));
            if (usrTbl.page > tp) usrTbl.page = tp;
            var start = (usrTbl.page - 1) * usrTbl.per,
                end = usrTbl.per >= total ? total : start + usrTbl.per;

            usrRows().forEach(function(tr) {
                tr.style.display = 'none';
            });
            rows.forEach(function(tr, i) {
                if (i >= start && i < end) tr.style.display = '';
            });

            var info = document.getElementById('usrInfo');
            if (info) info.textContent = total ?
                'Showing ' + (start + 1) + '–' + Math.min(end, total) + ' of ' + total + ' users' :
                'No users found';

            usrPgn(tp);

            var gridEmpty = !usrRows().length;
            var es = document.getElementById('usrEmpty');
            if (es) es.style.display = gridEmpty ? '' : 'none';
            var tc = document.querySelector('.table-card');
            if (tc) tc.style.display = gridEmpty ? 'none' : '';
        }

        /* ★ PAGINATION — HAMESHA visible (single page pe bhi "1" dikhega) */
        function usrPgn(tp) {
            var box = document.getElementById('usrPgn');
            if (!box || tp < 1) return;
            box.innerHTML = '';
            var mk = function(label, p, dis, on) {
                var b = document.createElement('button');
                b.type = 'button';
                b.className = 'pg-btn' + (on ? ' on' : '');
                b.innerHTML = label;
                if (dis) b.disabled = true;
                else b.setAttribute('onclick', 'usrGoPage(' + p + ')');
                box.appendChild(b);
            };
            mk('<i class="ri-arrow-left-double-line"></i>', 1, usrTbl.page === 1);
            mk('<i class="ri-arrow-left-s-line"></i>', usrTbl.page - 1, usrTbl.page === 1);
            if (tp <= 7) {
                for (var i = 1; i <= tp; i++) mk(i, i, false, i === usrTbl.page);
            } else {
                var s = Math.max(1, usrTbl.page - 2),
                    e = Math.min(tp, usrTbl.page + 2);
                if (s > 1) {
                    mk('1', 1);
                    if (s > 2) box.insertAdjacentHTML('beforeend', '<span class="pg-dots">…</span>');
                }
                for (var j = s; j <= e; j++) mk(j, j, false, j === usrTbl.page);
                if (e < tp) {
                    if (e < tp - 1) box.insertAdjacentHTML('beforeend', '<span class="pg-dots">…</span>');
                    mk(tp, tp);
                }
            }
            mk('<i class="ri-arrow-right-s-line"></i>', usrTbl.page + 1, usrTbl.page === tp);
            mk('<i class="ri-arrow-right-double-line"></i>', tp, usrTbl.page === tp);
        }

        function usrCounts() {
            var rows = usrRows();
            var set = function(id, v) {
                var el = document.getElementById(id);
                if (el) el.textContent = v;
            };
            set('cntAll', rows.length);
            set('cntActive', rows.filter(function(r) {
                return r.dataset.status === 'active';
            }).length);
            set('cntBlocked', rows.filter(function(r) {
                return r.dataset.status === 'blocked';
            }).length);
        }

        function usrBump(id, delta) {
            var el = document.getElementById(id);
            if (!el) return;
            var val = (parseInt(el.textContent.replace(/,/g, ''), 10) || 0) + delta;
            el.textContent = val.toLocaleString();
        }

        /* ═══ AJAX: block / unblock ═══ */
        function usrSend(url) {
            var fd = new FormData();
            fd.append('_method', 'PATCH');
            fd.append('_token', usrCsrf);
            return fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': usrCsrf,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: fd
            }).then(function(res) {
                return res.json().catch(function() {
                    return {};
                }).then(function(data) {
                    if (res.status === 419) throw new Error('Session expired — please refresh the page.');
                    if (!res.ok) throw new Error(data.message || 'Request failed (' + res.status + ')');
                    return data;
                });
            });
        }

        function usrPaint(tr, blocked, name) {
            var scrollY = window.scrollY;
            tr.dataset.status = blocked ? 'blocked' : 'active';
            tr.dataset.active = blocked ? 0 : 1;
            tr.classList.remove('flash');
            void tr.offsetWidth;
            tr.classList.add('flash');
            var pill = tr.querySelector('.pill-cell');
            if (pill) pill.innerHTML = blocked ?
                '<span class="pill blocked"><i class="ri-forbid-line"></i>Blocked</span>' :
                '<span class="pill active"><i class="ri-checkbox-circle-line"></i>Active</span>';
            var act = tr.querySelector('.actions');
            if (act) act.innerHTML = blocked ?
                '<button type="button" class="act act-active" onclick="usrUnblock(this)" data-name="' + usrEsc(name) + '"><i class="ri-check-double-line"></i>Set Active</button>' :
                '<button type="button" class="act act-block" onclick="usrBlock(this)" data-name="' + usrEsc(name) + '"><i class="ri-forbid-line"></i>Block</button>';
            usrCounts();
            usrApply();
            window.requestAnimationFrame(function() {
                window.scrollTo(0, scrollY);
            });
        }

        function usrBlock(btn) {
            var tr = btn.closest('tr');
            var name = btn.dataset.name || 'this user';
            usrConfirm({
                title: 'Block ' + name + '?',
                html: 'They will <b style="color:#f87171">immediately lose access</b> and won\'t be able to log in until you restore them.',
                icon: 'warning',
                iconColor: '#f87171',
                showCancelButton: true,
                reverseButtons: true,
                confirmButtonText: 'Yes, Block',
                cancelButtonText: 'Cancel',
                focusCancel: true
            }).then(function(r) {
                if (!r.isConfirmed) return;
                btn.disabled = true;
                usrSend(tr.dataset.blockUrl).then(function(data) {
                    usrPaint(tr, true, name);
                    usrBump('statActive', -1);
                    usrBump('statBlocked', 1);
                    usrToast('success', (data && data.message) || name + ' has been blocked.');
                }).catch(function(err) {
                    btn.disabled = false;
                    usrToast('error', err.message || 'Something went wrong.');
                });
            });
        }

        function usrUnblock(btn) {
            var tr = btn.closest('tr');
            var name = btn.dataset.name || 'this user';
            btn.disabled = true;
            usrSend(tr.dataset.unblockUrl).then(function(data) {
                usrPaint(tr, false, name);
                usrBump('statActive', 1);
                usrBump('statBlocked', -1);
                usrToast('success', (data && data.message) || name + ' is active again.');
            }).catch(function(err) {
                btn.disabled = false;
                usrToast('error', err.message || 'Something went wrong.');
            });
        }

        usrApply();
    </script>
</body>

</html>