<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0e5a75">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NiceDash — Dashboard</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}?v=3">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box }

        :root {
            --bg: #f3f5f7; --card: #ffffff; --text: #101828; --muted: #68788f;
            --border: #e8edf3; --forest: #0e5a75; --forest-2: #17739b;
            --lime: #3ec5e0; --lime-soft: #e4f6fb;
            --topbar: rgba(255, 255, 255, .85);
            --shadow: 0 1px 2px rgba(16, 24, 40, .04), 0 8px 24px rgba(16, 24, 40, .05);
            --radius: 16px;
        }

        body.dark {
            --bg: #0b1220; --card: #111c2e; --text: #e7edf5; --muted: #8ba0b8;
            --border: #1e2c42; --lime-soft: rgba(62, 197, 224, .08);
            --topbar: rgba(17, 28, 46, .85);
            --shadow: 0 1px 2px rgba(0, 0, 0, .3), 0 10px 30px rgba(0, 0, 0, .35);
        }

        html { scroll-behavior: smooth }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--text);
            font-size: 14px; transition: background .4s, color .4s;
            -webkit-text-size-adjust: 100%; -webkit-tap-highlight-color: transparent; overflow-x: clip
        }

        canvas, img { max-width: 100% }

        ::-webkit-scrollbar { width: 9px; height: 9px }
        ::-webkit-scrollbar-thumb { background: #c7d2de; border-radius: 10px; border: 2px solid var(--bg) }
        body.dark ::-webkit-scrollbar-thumb { background: #24334d }

        button { font-family: inherit; user-select: none }
        h3 { font-size: 15.5px; font-weight: 700 }

        /* ===== LAYOUT ===== */
        .layout { min-height: 100vh }
        .main { margin-left: 268px; min-width: 0; transition: margin .35s cubic-bezier(.4, 0, .2, 1) }
        .layout.collapsed .main { margin-left: 84px }

        .sidebar {
            position: fixed; left: 0; top: 0; bottom: 0; right: auto; width: 268px;
            background: var(--card); border-right: 1px solid var(--border);
            display: flex; flex-direction: column; z-index: 50;
            will-change: transform; backface-visibility: hidden; -webkit-backface-visibility: hidden;
            transition: width .35s cubic-bezier(.4, 0, .2, 1), transform .35s cubic-bezier(.4, 0, .2, 1)
        }

        .layout.collapsed .sidebar { width: 84px }
        .s-nav { flex: 1; overflow-y: auto; padding: 12px 14px }

        .layout.collapsed .logo-text,
        .layout.collapsed .s-label,
        .layout.collapsed .s-badge,
        .layout.collapsed .s-sec,
        .layout.collapsed .pro-card { opacity: 0; visibility: hidden; transition: opacity .2s }

        .layout.collapsed .nav-link { justify-content: center; padding: 12px }

        /* ===== CONTENT ===== */
        .content { padding: 24px 26px 40px; display: grid; gap: 22px; min-width: 0 }

        .page-head { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px }
        .page-head h2 { font-size: 24px; font-weight: 800; letter-spacing: -.4px }
        .page-head p { color: var(--muted); font-size: 13px; margin-top: 3px }
        .head-actions { display: flex; gap: 10px; flex-wrap: wrap }
        .live-status { align-self: center; color: var(--muted); font-size: 10px; white-space: nowrap }

        .card {
            background: var(--card); border: 1px solid var(--border); border-radius: var(--radius);
            box-shadow: var(--shadow); padding: 22px; min-width: 0;
            transition: transform .3s, box-shadow .3s, background .4s
        }

        .card-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 14px; flex-wrap: wrap }

        .r-hero { display: grid; grid-template-columns: 1.55fr 1.3fr 1fr; gap: 22px }
        .r-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 22px }
        .r-charts { display: grid; grid-template-columns: 1.1fr 1.4fr 1.1fr; gap: 22px }
        .r-bottom { display: grid; grid-template-columns: 1fr 1.15fr 1fr; gap: 22px }

        /* ===== HERO CARDS ===== */
        .hero-card { display: flex; justify-content: space-between; align-items: center; gap: 18px; flex-wrap: wrap }
        .s-small { font-size: 12px; color: var(--muted); font-weight: 600 }
        .big { font-size: 29px; font-weight: 800; letter-spacing: -.6px; margin: 8px 0 6px }
        .big.sm { font-size: 23px }
        .light { color: #fff }
        .dark-txt { color: #0b3140 }
        .muted-light { color: rgba(255, 255, 255, .72) }
        .small { font-size: 12.5px }
        .muted { color: var(--muted) }
        .up { color: #0e8fb5; font-weight: 800 }
        body.dark .up { color: #4ddcf5 }
        .spark-box { width: 175px; height: 105px; flex-shrink: 0; position: relative }

        .forest-card { background: linear-gradient(150deg, #0d4257, #1a78a0); border: none; color: #fff; min-height: 172px }
        .f-top { display: flex; justify-content: space-between; align-items: center }
        .f-mid { display: flex; justify-content: space-between; align-items: center; gap: 14px; margin: 12px 0 }
        .mini-bars { height: 78px; width: 44%; min-width: 120px; position: relative }

        .lime-card { background: linear-gradient(150deg, #66d6ef, #3fb0d8); border: none; color: #0b3140; min-height: 172px }

        .round-btn {
            width: 36px; height: 36px; border-radius: 50%; background: rgba(255, 255, 255, .9);
            border: none; color: var(--forest); font-size: 18px; cursor: pointer;
            display: grid; place-items: center; transition: transform .4s
        }

        /* ===== STAT CARDS ===== */
        .stat-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px }
        .stat .icon { width: 44px; height: 44px; border-radius: 13px; display: grid; place-items: center; font-size: 20px }
        .stat .icon > i { display: block; line-height: 1 }
        .i-green { background: #e0f5fb; color: #0e8fb5 }
        .i-blue { background: #e8f1fd; color: #2563eb }
        .i-amber { background: #fdf4e3; color: #d97706 }
        .i-violet { background: #f1ecfd; color: #7c3aed }

        .badge-up {
            display: inline-flex; align-items: center; gap: 2px; background: #dcf5fb; color: #0b7a99;
            font-size: 11.5px; font-weight: 800; padding: 4px 9px; border-radius: 9px
        }
        .badge-down {
            display: inline-flex; align-items: center; gap: 2px; background: #fee2e2; color: #dc2626;
            font-size: 11.5px; font-weight: 800; padding: 4px 9px; border-radius: 9px
        }

        body.dark .badge-up { background: rgba(62, 197, 224, .12); color: #4ddcf5 }
        body.dark .badge-down { background: rgba(248, 113, 113, .12); color: #f87171 }
        body.dark .i-green { background: rgba(62, 197, 224, .12); color: #4ddcf5 }
        body.dark .i-blue { background: rgba(96, 165, 250, .12); color: #60a5fa }
        body.dark .i-amber { background: rgba(251, 191, 36, .12); color: #fbbf24 }
        body.dark .i-violet { background: rgba(167, 139, 250, .12); color: #a78bfa }

        /* ===== CHARTS ===== */
        .chart-box { height: 275px; position: relative }
        .chart-empty { position: absolute; inset: 0; display: grid; place-items: center; padding: 20px; color: var(--muted); font-size: 12px; text-align: center; pointer-events: none }
        .chart-empty[hidden] { display: none }
        .donut-box { height: 185px; position: relative; margin-top: 6px }
        .legend { display: flex; flex-direction: column; gap: 6px; margin-top: 16px }
        .legend-row { display: flex; align-items: center; gap: 10px; font-size: 13px; padding: 5px 0 }
        .lg-dot { width: 9px; height: 9px; border-radius: 3px; flex-shrink: 0 }
        .lg-name { color: var(--muted) }
        .legend-row b { margin-left: auto }
        .lg-pct { font-size: 11px; font-weight: 800; padding: 3px 8px; border-radius: 8px; background: var(--bg); color: var(--muted) }
        .ov-numbers { display: flex; gap: 26px; margin-top: 8px }

        .seg { display: flex; background: var(--bg); border: 1px solid var(--border); border-radius: 10px; padding: 3px }
        .seg-btn {
            border: none; background: transparent; color: var(--muted); font-weight: 700;
            font-size: 12.5px; padding: 7px 16px; border-radius: 8px; cursor: pointer; transition: .25s
        }
        .seg-btn.active { background: var(--forest); color: #fff; box-shadow: 0 4px 10px rgba(14, 90, 117, .3) }
        body.dark .seg-btn.active { background: var(--lime); color: #073344 }

        /* CSS fallback bars — sirf tab jab Chart.js bilkul load na ho */
        .cssbars { display: flex; align-items: stretch; gap: 10px; width: 100%; height: 100%; padding: 14px 6px 4px }

        /* ===== TABLE ===== */
        .table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch }
        table { width: 100%; border-collapse: collapse; min-width: 780px }
        .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0 }
        .dt-controls, .dt-footer { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; padding: 12px 0 }
        .dt-controls { margin-bottom: 8px; border-bottom: 1px solid var(--border) }
        .dt-search { display: flex; align-items: center; gap: 8px; min-width: 0; color: var(--muted); font-size: 12px }
        .dt-search > i { display: grid; flex: 0 0 16px; place-items: center; line-height: 1 }
        .dt-search input { box-sizing: border-box; width: min(280px, 48vw); min-width: 0; padding: 9px 11px; border: 1px solid var(--border); border-radius: 9px; background: var(--card); color: var(--text); font: inherit }
        .dt-length { display: inline-flex; align-items: center; gap: 8px; color: var(--muted); font-size: 12px; white-space: nowrap }
        .dt-length .site-select-wrap { width: 76px; min-width: 76px; flex: 0 0 76px }
        .dt-length .site-select-trigger { min-height: 38px; padding: 8px 10px; border-radius: 9px; font-size: 12px }
        .dt-length .site-select-chevron { font-size: 15px }
        .dt-info { color: var(--muted); font-size: 12px }
        .dt-pages { display: flex; align-items: center; flex-wrap: wrap; gap: 5px }
        .dt-page { min-width: 34px; height: 34px; padding: 0 9px; border: 1px solid var(--border); border-radius: 8px; background: var(--card); color: var(--text); font: inherit; font-size: 12px; font-weight: 600; cursor: pointer }
        .dt-page:hover, .dt-page.active { border-color: var(--forest); background: var(--forest); color: #fff }
        .dt-page:disabled { opacity: .45; cursor: not-allowed }
        th[data-sort-key] { cursor: pointer; user-select: none }
        th[data-sort-key]::after { content: ' ↕'; color: var(--muted); font-size: 10px }
        th[data-sort-direction="asc"]::after { content: ' ↑'; color: var(--forest) }
        th[data-sort-direction="desc"]::after { content: ' ↓'; color: var(--forest) }
        th {
            font-size: 11px; text-transform: uppercase; letter-spacing: .07em; color: var(--muted);
            text-align: left; padding: 10px 12px; border-bottom: 1px solid var(--border)
        }
        td { padding: 13px 12px; border-bottom: 1px solid var(--border); font-size: 13.5px }
        tbody tr { transition: background .2s }
        tbody tr:hover { background: var(--lime-soft) }
        tbody tr:last-child td { border-bottom: none }
        .t-right { text-align: right }
        .empty { text-align: center; padding: 34px 10px; color: var(--muted); font-size: 13px }

        .prod { display: flex; align-items: center; gap: 12px; min-width: 230px }
        .prod strong { display: block; font-size: 13px }
        .prod span { font-size: 11.5px; color: var(--muted) }

        .thumb {
            width: 44px; height: 44px; min-width: 44px; border-radius: 11px;
            display: grid; place-items: center; font-size: 20px; overflow: hidden
        }
        .thumb img { width: 100%; height: 100%; object-fit: cover; border-radius: inherit }

        .t1 { background: linear-gradient(135deg, #e0f5fb, #c2e9f5) }
        .t2 { background: linear-gradient(135deg, #eef4e2, #d9f0c0) }
        .t3 { background: linear-gradient(135deg, #fdeee0, #fbdcc0) }
        .t4 { background: linear-gradient(135deg, #e8f1fd, #cfe3fb) }
        .t5 { background: linear-gradient(135deg, #f4ecfd, #e2d3fb) }

        .cust { display: flex; align-items: center; gap: 10px }
        .c-av {
            width: 30px; height: 30px; min-width: 30px; border-radius: 50%;
            display: grid; place-items: center; font-size: 10.5px; font-weight: 800
        }

        .pill {
            display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px;
            border-radius: 20px; font-size: 11.5px; font-weight: 700
        }
        .pill::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor }
        .pill.refunded { background: #fee2e2; color: #dc2626 }
        .pill.shipped { background: #fef3c7; color: #b45309 }
        .pill.delivered { background: #dcfce7; color: #16a34a }
        .pill.pending { background: #fef3c7; color: #b45309 }
        .pill.processing { background: #e0f2fe; color: #0284c7 }
        .pill.cancelled { background: #fee2e2; color: #dc2626 }
        .pill.mini { padding: 3px 9px; font-size: 10.5px; margin-left: 6px }
        .pill.paid { background: #dcfce7; color: #16a34a }
        .pill.unpaid { background: #fef3c7; color: #b45309 }

        body.dark .pill.refunded, body.dark .pill.cancelled { background: rgba(248, 113, 113, .12); color: #f87171 }
        body.dark .pill.shipped, body.dark .pill.pending, body.dark .pill.unpaid { background: rgba(251, 191, 36, .12); color: #fbbf24 }
        body.dark .pill.delivered, body.dark .pill.paid { background: rgba(74, 222, 128, .12); color: #4ade80 }
        body.dark .pill.processing { background: rgba(56, 189, 248, .12); color: #7dd3fc }

        .pay { display: inline-flex; align-items: center; gap: 7px; color: var(--muted) }

        .table-foot { display: flex; justify-content: space-between; align-items: center; padding-top: 16px; flex-wrap: wrap; gap: 10px }

        .btn-ghost {
            padding: 9px 14px; border-radius: 10px; border: 1px solid var(--border); background: var(--card);
            color: var(--text); font-weight: 700; font-size: 12.5px; cursor: pointer;
            display: inline-flex; align-items: center; gap: 7px; transition: .25s; text-decoration: none
        }

        .link { color: var(--forest); font-weight: 700; font-size: 12.5px; text-decoration: none }
        body.dark .link { color: var(--lime) }

        /* ===== BOTTOM ROW ===== */
        .lp-row {
            display: flex; align-items: center; gap: 12px; padding: 10px 8px;
            border-radius: 12px; transition: .2s
        }
        .lp-info { flex: 1; display: flex; flex-direction: column; line-height: 1.35; min-width: 0 }
        .lp-info strong { font-size: 13px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap }
        .lp-info span { font-size: 11.5px; color: var(--muted) }

        .dl-row {
            display: flex; align-items: center; gap: 10px; font-size: 13px;
            padding: 8px 0; border-bottom: 1px dashed var(--border)
        }
        .dl-row:last-child { border-bottom: none }
        .dl-row b { margin-left: auto }

        /* ===== PROMO ===== */
        .promo {
            background: linear-gradient(150deg, #0d4257, #0e5a75 55%, #17739b);
            border-radius: var(--radius); padding: 26px; color: #fff; position: relative;
            overflow: hidden; min-height: 280px; display: flex; flex-direction: column;
            justify-content: flex-end; gap: 6px
        }
        .promo::before {
            content: ''; position: absolute; width: 230px; height: 230px; border-radius: 50%;
            top: -70px; right: -70px; background: radial-gradient(circle, rgba(62, 197, 224, .35), transparent 70%);
            animation: float 6s ease-in-out infinite
        }
        .promo::after {
            content: ''; position: absolute; width: 180px; height: 180px; border-radius: 50%;
            bottom: -80px; left: -60px; background: radial-gradient(circle, rgba(62, 197, 224, .18), transparent 70%);
            animation: float 7s ease-in-out infinite reverse
        }
        .promo-laptop { font-size: 58px; align-self: flex-end; margin-bottom: 10px; animation: float 5s ease-in-out infinite; filter: drop-shadow(0 14px 24px rgba(0, 0, 0, .4)); position: relative; z-index: 1 }
        .promo h3 { font-size: 19px; line-height: 1.45; position: relative; z-index: 1 }
        .promo p { font-size: 12.5px; color: rgba(255, 255, 255, .72); position: relative; z-index: 1; margin-bottom: 12px }

        .btn-lime {
            background: var(--lime); color: #08394b; font-weight: 800; font-size: 13px;
            padding: 11px 20px; border-radius: 12px; border: none; cursor: pointer;
            display: inline-flex; gap: 8px; align-items: center; transition: .3s;
            position: relative; overflow: hidden; align-self: flex-start; text-decoration: none
        }
        .btn-lime i { transition: transform .3s }

        /* ===== HOVER — sirf desktop ===== */
        @media(hover:hover) and (pointer:fine) {
            .card:hover { transform: translateY(-4px); box-shadow: 0 14px 34px rgba(16, 24, 40, .10) }
            .round-btn:hover { transform: rotate(180deg) scale(1.08) }
            .btn-ghost:hover { border-color: var(--forest); color: var(--forest); transform: translateY(-1px) }
            body.dark .btn-ghost:hover { border-color: var(--lime); color: var(--lime) }
            .link:hover { text-decoration: underline }
            .lp-row:hover { background: var(--bg); transform: translateX(5px) }
            .btn-lime:hover { transform: translateY(-2px); box-shadow: 0 12px 26px rgba(62, 197, 224, .4) }
            .btn-lime:hover i { transform: translateX(4px) }
        }

        /* ===== ANIMATIONS ===== */
        .reveal { opacity: 0; transform: translateY(20px); transition: opacity .45s ease, transform .45s cubic-bezier(.22, 1, .36, 1) }
        .reveal.show { opacity: 1; transform: none }

        .spin { display: inline-block; animation: rot 1s linear infinite }
        @keyframes rot { to { transform: rotate(360deg) } }

        @keyframes float { 0%, 100% { transform: translateY(0) } 50% { transform: translateY(-14px) } }

        /* ===== RESPONSIVE ===== */
        @media(max-width:1250px) {
            .r-hero { grid-template-columns: 1fr 1fr }
            .r-charts { grid-template-columns: 1fr 1fr }
            .r-bottom { grid-template-columns: 1fr 1fr }
        }

        @media(max-width:992px) {
            .sidebar { transform: translateX(-105%); width: 268px !important }
            .layout.mobile-open .sidebar { transform: none; box-shadow: 0 0 60px rgba(0, 0, 0, .3) }
            .main { margin-left: 0 !important }
            .layout.collapsed .logo-text,
            .layout.collapsed .s-label,
            .layout.collapsed .s-badge,
            .layout.collapsed .s-sec,
            .layout.collapsed .pro-card { opacity: 1; visibility: visible }
            .layout.collapsed .nav-link { justify-content: flex-start; padding: 11px 12px }
            .r-stats { grid-template-columns: 1fr 1fr }
        }

        @media(max-width:680px) {
            .r-hero, .r-charts, .r-bottom { grid-template-columns: 1fr }
            .content { padding: 18px 14px calc(40px + env(safe-area-inset-bottom)) }
            .dt-controls, .dt-footer { align-items: flex-start }
            .dt-search { flex: 1 1 220px }
            .dt-search input { width: 100% }
            .dt-length { margin-left: auto }
            .dt-footer { flex-direction: column; gap: 8px }
        }

        @media(max-width:480px) {
            .content { padding: 16px 12px calc(36px + env(safe-area-inset-bottom)); gap: 16px }
            .page-head h2 { font-size: 20px }
            .head-actions { width: 100% }
            .head-actions .btn-ghost, .head-actions .btn-lime { flex: 1 1 auto; justify-content: center }
            .card { padding: 16px; border-radius: 14px }
            .big { font-size: 23px }
            .big.sm { font-size: 19px }
            .r-stats { gap: 12px }
            .stat .icon { width: 40px; height: 40px; font-size: 18px }
            .hero-card { flex-direction: column; align-items: stretch; gap: 12px }
            .spark-box { width: 100%; height: 90px }
            .forest-card, .lime-card { min-height: 150px }
            .mini-bars { min-width: 100px }
            .chart-box { height: 215px }
            .donut-box { height: 165px }
            .promo { padding: 20px 18px; min-height: 220px }
            .promo h3 { font-size: 16px }
            .promo-laptop { font-size: 42px }
            .thumb { width: 38px; height: 38px; min-width: 38px; font-size: 17px }
            .dt-search { flex-basis: 100% }
            .dt-length { margin-left: 0 }
        }

        @media(max-width:360px) {
            .r-stats { grid-template-columns: 1fr }
            .seg { width: 100% }
            .seg-btn { flex: 1 }
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
                    $avs = [['#e8f1fd','#2563eb'],['#fdeee0','#d97706'],['#e0f5fb','#0e8fb5'],['#f1ecfd','#7c3aed'],['#fdeef2','#db2777']];
                @endphp

                <!-- Page head -->
                <div class="page-head reveal">
                    <div>
                        <h2>Dashboard</h2>
                        <p>Welcome back, {{ auth()->user()->name ?? 'Admin' }}! Here's what's happening with your store today.</p>
                    </div>
                    <div class="head-actions">
                        <button class="btn-ghost" id="rangeLbl"><i class="ri-calendar-line"></i>{{ $d['range'] }}</button>
                        <button class="btn-ghost" id="refreshBtn"><i class="ri-refresh-line"></i>Refresh</button>
                        <span class="live-status" id="dashboardLiveStatus" role="status">Auto-updates every 30 seconds</span>
                        <button class="btn-lime" id="exportBtn"><i class="ri-download-2-line"></i>Export Report</button>
                    </div>
                </div>

                <!-- Hero cards -->
                <section class="r-hero">
                    <div class="card hero-card reveal">
                        <div>
                            <p class="s-small">Store Overview</p>
                            <h3 style="margin-top:4px">Cosmetics Store</h3>
                            <div class="big" id="revBig" data-count="{{ $d['revenue'] }}" data-prefix="£" data-decimals="2">£0.00</div>
                            <p class="muted small">Paid order revenue · live</p>
                            <p class="muted small">vs last month <span class="up" id="revTrend">{{ $d['revGrowth'] >= 0 ? '+' : '' }}{{ $d['revGrowth'] }}%</span></p>
                        </div>
                        <div class="spark-box"><canvas id="sparkline"></canvas></div>
                    </div>

                    <div class="card forest-card reveal" style="transition-delay:.08s">
                        <div class="f-top">
                            <h3 class="light">Monthly Sales</h3>
                            <button class="round-btn" id="refreshRound" title="Refresh"><i class="ri-refresh-line"></i></button>
                        </div>
                        <div class="f-mid">
                            <div>
                                <p class="s-small muted-light">Sales</p>
                                <div class="big light" id="monthBig" data-count="{{ $d['revNow'] }}" data-prefix="£">£0</div>
                            </div>
                            <div class="mini-bars"><canvas id="miniBars"></canvas></div>
                        </div>
                        <p class="small muted-light">Net Income: <strong style="color:#fff" id="netIncome">£{{ number_format($d['revNow'], 2) }}</strong></p>
                    </div>

                    <div class="card lime-card reveal" style="transition-delay:.16s">
                        <div class="f-top">
                            <p class="s-small" style="color:#10556d;font-weight:700">Revenue — Growth</p>
                            <button class="round-btn" title="Refresh" data-act="refresh"><i class="ri-refresh-line"></i></button>
                        </div>
                        <div class="big dark-txt" id="growthBig" data-count="{{ $d['revGrowth'] }}" data-prefix="{{ $d['revGrowth'] >= 0 ? '+' : '' }}" data-suffix="%">+0%</div>
                        <p class="small" style="color:#135e78" id="growthTxt">
                            {{ $d['revGrowth'] >= 0 ? 'Better' : 'Lower' }} than last month <strong>({{ $d['revGrowth'] >= 0 ? '+' : '' }}{{ $d['revGrowth'] }}%)</strong>
                        </p>
                    </div>
                </section>

                <!-- Stat cards -->
                <section class="r-stats">
                    <div class="card stat reveal">
                        <div class="stat-top">
                            <div class="icon i-green"><i class="ri-shopping-bag-2-line"></i></div>
                            <span class="{{ $d['ordGrowth'] >= 0 ? 'badge-up' : 'badge-down' }}" id="ordBadge"><i class="ri-arrow-{{ $d['ordGrowth'] >= 0 ? 'up' : 'down' }}-line"></i>{{ abs($d['ordGrowth']) }}%</span>
                        </div>
                        <div class="big sm" id="statOrders" data-count="{{ $d['ordersCount'] }}">0</div>
                        <p class="muted small" id="ordSub">{{ $d['pendingCount'] }} pending right now</p>
                    </div>

                    <div class="card stat reveal" style="transition-delay:.06s">
                        <div class="stat-top">
                            <div class="icon i-blue"><i class="ri-truck-line"></i></div>
                            <span class="badge-up" id="shipBadge"><i class="ri-arrow-up-line"></i>{{ $d['shipPct'] }}%</span>
                        </div>
                        <div class="big sm" id="statShipped" data-count="{{ $d['shippedCount'] }}">0</div>
                        <p class="muted small" id="shipSub">Shipped + delivered orders</p>
                    </div>

                    <div class="card stat reveal" style="transition-delay:.12s">
                        <div class="stat-top">
                            <div class="icon i-amber"><i class="ri-money-pound-circle-line"></i></div>
                            <span class="{{ $d['revGrowth'] >= 0 ? 'badge-up' : 'badge-down' }}" id="revBadge"><i class="ri-arrow-{{ $d['revGrowth'] >= 0 ? 'up' : 'down' }}-line"></i>{{ abs($d['revGrowth']) }}%</span>
                        </div>
                        <div class="big sm" id="statRevenue" data-count="{{ $d['revenue'] }}" data-prefix="£">£0</div>
                        <p class="muted small" id="revSub">This month: £{{ number_format($d['revNow'], 2) }}</p>
                    </div>

                    <div class="card stat reveal" style="transition-delay:.18s">
                        <div class="stat-top">
                            <div class="icon i-violet"><i class="ri-star-smile-line"></i></div>
                            <span class="badge-up" id="ratBadge"><i class="ri-star-line"></i>{{ $d['reviewsCount'] > 0 ? $d['reviewsCount'] . ' reviews' : 'No reviews' }}</span>
                        </div>
                        <div class="big sm" id="statRating" data-count="{{ $d['avgRating'] }}" data-decimals="1" data-suffix="/5.0">0</div>
                        <p class="muted small" id="ratSub">Customer Satisfaction · {{ $d['reviewsCount'] > 0 ? $d['reviewsCount'] . ' approved reviews' : 'no reviews yet' }}</p>
                    </div>
                </section>

                <!-- Charts -->
                <section class="r-charts">
                    <div class="card reveal">
                        <div class="card-head"><h3>Order Status</h3></div>
                        <div class="donut-box"><canvas id="segChart"></canvas></div>
                        <div class="legend" id="segLegend">
                            @forelse($d['statusCounts'] as $s)
                            <div class="legend-row">
                                <span class="lg-dot" style="background:{{ $s['color'] }}"></span>
                                <span class="lg-name">{{ $s['label'] }}</span>
                                <b>{{ number_format($s['count']) }}</b>
                                <span class="lg-pct">{{ $d['ordersCount'] > 0 ? round($s['count'] / $d['ordersCount'] * 100) : 0 }}%</span>
                            </div>
                            @empty
                            <div class="legend-row"><span class="lg-name">No orders yet</span></div>
                            @endforelse
                        </div>
                    </div>

                    <div class="card reveal" style="transition-delay:.08s">
                        <div class="card-head">
                            <div>
                                <h3>Order Overview</h3>
                                <div class="ov-numbers">
                                    <div><span class="big sm" style="margin:0" id="ovRev" data-count="{{ $d['revenue'] }}" data-prefix="£">£0</span><p class="muted small">Revenue</p></div>
                                    <div><span class="big sm" style="margin:0" id="ovItems" data-count="{{ $d['itemsSold'] }}">0</span><p class="muted small">Items sold</p></div>
                                </div>
                            </div>
                            <div class="seg">
                                <button class="seg-btn active" data-set="sales">Sales</button>
                                <button class="seg-btn" data-set="orders">Orders</button>
                            </div>
                        </div>
                        <div class="chart-box"><canvas id="orderChart"></canvas></div>
                    </div>

                    <div class="card reveal" style="transition-delay:.16s">
                        <div class="card-head"><h3>New Customers</h3><span class="lg-pct">7 days</span></div>
                        <div class="chart-box"><canvas id="usersChart" role="img" aria-label="Daily new customer registrations for the last seven days"></canvas></div>
                        <div class="legend" id="usersLegend">
                            <div class="legend-row"><span class="lg-dot" style="background:var(--forest)"></span><span class="lg-name">Total Customers</span><b>{{ number_format($d['totalCustomers']) }}</b></div>
                            <div class="legend-row"><span class="lg-dot" style="background:var(--lime)"></span><span class="lg-name">New This Week</span><b>{{ number_format($d['newThisWeek']) }}</b></div>
                        </div>
                    </div>
                </section>

                <!-- Recent orders -->
                <section class="card reveal">
                    <div class="card-head">
                        <div>
                            <h3>Recent Orders</h3>
                            <p class="muted small" style="margin-top:3px">Latest 6 orders from your store</p>
                        </div>
                        <div class="head-actions">
                            <a class="btn-ghost" href="{{ route('admin.orders') }}"><i class="ri-arrow-right-up-line"></i>View All</a>
                        </div>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Customer</th>
                                    <th>Qty</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th class="t-right">Total</th>
                                </tr>
                            </thead>
                            <tbody id="recentBody">
                                @forelse($d['recent'] as $i => $o)
                                <tr>
                                    <td>
                                        <div class="prod">
                                            <div class="thumb t{{ ($i % 5) + 1 }}">@if($o['img'])<img src="{{ asset('storage/' . $o['img']) }}" alt="">@else{{ $o['emoji'] }}@endif</div>
                                            <div><strong>{{ Str::limit($o['product'], 34) }}</strong><span>{{ $o['cat'] ?: 'Cosmetics' }}</span></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="cust"><span class="c-av" style="background:{{ $avs[$i % 5][0] }};color:{{ $avs[$i % 5][1] }}">{{ $o['initials'] }}</span>{{ $o['customer'] }}</div>
                                    </td>
                                    <td>{{ $o['qty'] }} Pcs</td>
                                    <td><span class="pill {{ $o['status'] }}">{{ ucfirst($o['status']) }}</span></td>
                                    <td>
                                        <span class="pay"><i class="ri-bank-card-line"></i>{{ $o['payment'] }}</span>
                                        <span class="pill mini {{ $o['paid'] ? 'paid' : 'unpaid' }}">{{ $o['paid'] ? 'Paid' : 'Unpaid' }}</span>
                                    </td>
                                    <td class="t-right"><b>£{{ number_format($o['total'], 2) }}</b></td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="empty">No orders yet — orders will appear here 💫</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="table-foot">
                        <p class="muted small" id="ordersFoot">Showing {{ min(6, $d['ordersCount']) }} of {{ number_format($d['ordersCount']) }} orders</p>
                        <a href="{{ route('admin.orders') }}" class="link">Manage all orders →</a>
                    </div>
                </section>

                <!-- Bottom row -->
                <section class="r-bottom">
                    <div class="card reveal">
                        <div class="card-head"><h3>Latest Products</h3><a href="{{ route('admin.products.index') }}" class="link">View All</a></div>
                        <div id="latestBox">
                            @forelse($d['latest'] as $i => $p)
                            <div class="lp-row">
                                <div class="thumb t{{ ($i % 5) + 1 }}">@if($p['img'])<img src="{{ asset('storage/' . $p['img']) }}" alt="">@else{{ $p['emoji'] }}@endif</div>
                                <div class="lp-info"><strong>{{ Str::limit($p['name'], 26) }}</strong><span>{{ $p['cat'] ?: 'Cosmetics' }} · {{ $p['time'] }}</span></div>
                                <b>£{{ number_format($p['price'], 2) }}</b>
                            </div>
                            @empty
                            <div class="empty">No products yet</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="card reveal" style="transition-delay:.08s">
                        <div class="card-head"><h3>Top Categories</h3><span class="lg-pct">By products</span></div>
                        <div id="catsBox">
                            @forelse($d['cats'] as $c)
                            <div class="dl-row"><i class="ri-folder-2-line" style="color:var(--lime)"></i><span class="lg-name">{{ $c['name'] }}</span><b>{{ $c['count'] }} products</b></div>
                            @empty
                            <div class="empty">No categories yet</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="promo reveal" style="transition-delay:.16s">
                        <div class="promo-laptop">💅</div>
                        <h3>Grow your beauty store.<br>Add something gorgeous today.</h3>
                        <p>Keep your shelves fresh — new products bring customers back.</p>
                        <a href="{{ route('admin.products.create') }}" class="btn-lime"><i class="ri-add-line"></i>Add New Product</a>
                    </div>
                </section>

            </main>
        </div>
    </div>

    <script>
        (function () {
            /* Blade vars — ONE line each. JSON ko Blade ke raw json directive se
               print kiya gaya hai taake quotes escape na hon aur script zinda rahe. */
            var DASH = @json($d);
            var URLS = { data: "{{ route('admin.dashboard.data') }}", orders: "{{ route('admin.orders') }}" };
            var assetBase = '{{ asset("storage/") }}/';
            var charts = {};
            var chartMode = 'none';   /* 'none' | 'chart' | 'css' */
            var segMode = 'sales';
            var boxes = {};
            var booted = false;

            /* Chart.js — 3 CDNs, ek fail ho to agla try hota hai */
            var CHART_SRCS = [
                'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js',
                'https://unpkg.com/chart.js@4.4.1/dist/chart.umd.min.js',
                'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js'
            ];

            function cssVar(n) { return getComputedStyle(document.body).getPropertyValue(n).trim() || '#0e5a75'; }
            function fmt(n) { return '£' + Number(n || 0).toLocaleString('en-GB', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }

            function setNum(id, val, prefix, decimals, suffix) {
                var el = document.getElementById(id); if (!el) return;
                var neg = Number(val) < 0, v = Math.abs(Number(val) || 0);
                var body = decimals > 0 ? v.toLocaleString('en-GB', { minimumFractionDigits: decimals, maximumFractionDigits: decimals }) : Math.round(v).toLocaleString('en-GB');
                el.textContent = (neg ? '-' : '') + (prefix || '') + body + (suffix || '');
                el.dataset.done = '1';
            }

            function badge(id, val) {
                var el = document.getElementById(id); if (!el) return;
                var up = Number(val) >= 0;
                el.className = up ? 'badge-up' : 'badge-down';
                el.innerHTML = '<i class="ri-arrow-' + (up ? 'up' : 'down') + '-line"></i>' + Math.abs(val) + '%';
            }

            /* ============ CHART.JS LOADER (multi-CDN + fail-safe) ============ */
            var chartLoading = false, chartPending = [];

            function loadChart(ok, fail) {
                if (window.Chart) { ok(); return; }
                chartPending.push({ ok: ok, fail: fail });
                if (chartLoading) return;
                chartLoading = true;
                (function trySrc(i) {
                    if (window.Chart) return finish('ok');
                    if (i >= CHART_SRCS.length) return finish('fail');
                    var s = document.createElement('script');
                    s.src = CHART_SRCS[i];
                    s.onload = function () { window.Chart ? finish('ok') : trySrc(i + 1); };
                    s.onerror = function () { console.warn('[NiceDash] CDN fail:', CHART_SRCS[i]); trySrc(i + 1); };
                    document.head.appendChild(s);
                })(0);
                function finish(res) {
                    chartLoading = false;
                    var list = chartPending; chartPending = [];
                    list.forEach(function (p) { res === 'ok' ? p.ok() : p.fail(); });
                }
            }

            function destroyCharts() {
                ['sparkline', 'miniBars', 'segChart', 'orderChart', 'usersChart'].forEach(function (id) {
                    if (window.Chart && Chart.getChart(id)) Chart.getChart(id).destroy();
                });
            }

            if (!window.__admDashLifecycleBound) {
                window.__admDashLifecycleBound = true;
                window.addEventListener('ajax:navigating', function () {
                    destroyCharts();
                    if (window.__admDashRefreshTimer) {
                        window.clearInterval(window.__admDashRefreshTimer);
                        window.__admDashRefreshTimer = null;
                    }
                });
            }

            /* ============ REAL CHARTS (Chart.js) ============ */
            function buildReal(d) {
                if (!window.Chart || !document.getElementById('orderChart')) { buildFallback(d); return; }
                destroyCharts();
                var forest = cssVar('--forest'), lime = cssVar('--lime'), muted = cssVar('--muted'), border = cssVar('--border');
                Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
                chartMode = 'chart';

                var sp = document.getElementById('sparkline');
                if (sp) charts.sparkline = new Chart(sp, {
                    type: 'line',
                    data: { labels: d.months, datasets: [{ data: d.revSeries, borderColor: forest, borderWidth: 2.5, tension: .45, pointRadius: 0, fill: true, backgroundColor: function (c) { var g = c.chart.ctx.createLinearGradient(0, 0, 0, c.chart.height || 100); g.addColorStop(0, 'rgba(14,90,117,.25)'); g.addColorStop(1, 'rgba(14,90,117,0)'); return g; } }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: function (c) { return fmt(c.parsed.y); } } } }, scales: { x: { display: false }, y: { display: false } } }
                });

                var mb = document.getElementById('miniBars');
                if (mb) charts.miniBars = new Chart(mb, {
                    type: 'bar',
                    data: { labels: d.dayLabels, datasets: [{ data: d.dayVals, backgroundColor: 'rgba(255,255,255,.85)', borderRadius: 4, barPercentage: .6 }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { display: false } } }
                });

                var sg = document.getElementById('segChart');
                if (sg) charts.segChart = new Chart(sg, {
                    type: 'doughnut',
                    data: { labels: d.statusCounts.map(function (s) { return s.label; }), datasets: [{ data: d.statusCounts.map(function (s) { return s.count; }), backgroundColor: d.statusCounts.map(function (s) { return s.color; }), borderWidth: 0, cutout: '68%' }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                });

                var oc = document.getElementById('orderChart');
                if (oc) charts.orderChart = new Chart(oc, {
                    type: 'line',
                    data: { labels: d.months, datasets: [
                        { label: 'Revenue', data: d.revSeries, borderColor: forest, tension: .42, pointRadius: 3, fill: true, hidden: segMode !== 'sales', backgroundColor: function (c) { var g = c.chart.ctx.createLinearGradient(0, 0, 0, c.chart.height || 220); g.addColorStop(0, 'rgba(14,90,117,.22)'); g.addColorStop(1, 'rgba(14,90,117,0)'); return g; } },
                        { label: 'Orders', data: d.ordSeries, borderColor: lime, tension: .42, pointRadius: 3, fill: false, hidden: segMode !== 'orders', yAxisID: 'y1' }
                    ] },
                    options: {
                        responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
                        plugins: { legend: { display: false }, tooltip: { callbacks: { label: function (c) { return c.dataset.label + ': ' + (c.dataset.label === 'Revenue' ? fmt(c.parsed.y) : c.parsed.y); } } } },
                        scales: {
                            x: { grid: { color: border }, ticks: { color: muted } },
                            y: { grid: { color: border }, ticks: { color: muted, callback: function (v) { return '£' + (v >= 1000 ? (v / 1000) + 'k' : v); } } },
                            y1: { position: 'right', grid: { display: false }, ticks: { color: muted, precision: 0 } }
                        }
                    }
                });

                var uc = document.getElementById('usersChart');
                if (uc) charts.usersChart = new Chart(uc, {
                    type: 'bar',
                    data: { labels: d.uLabels, datasets: [{ data: d.uVals, backgroundColor: lime, borderRadius: 6, maxBarThickness: 26 }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: function (c) { return c.parsed.y + (c.parsed.y === 1 ? ' new customer' : ' new customers'); } } } }, scales: { x: { grid: { display: false }, ticks: { color: muted } }, y: { beginAtZero: true, suggestedMax: 1, grid: { color: border }, ticks: { color: muted, precision: 0, stepSize: 1 } } } }
                });
                setCustomerChartEmpty(d);

                console.log('[NiceDash] Charts ready (Chart.js)');
            }

            /* ============ CSS FALLBACK (Chart.js na mile to bhi charts dikhein) ============ */
            function cssBars(canvasId, vals, labels, color) {
                var c = document.getElementById(canvasId);
                var box = boxes[canvasId] || (c && c.parentElement);
                if (!box) return;
                boxes[canvasId] = box;
                vals = vals && vals.length ? vals : [0, 0, 0];
                var max = Math.max.apply(null, vals.concat([1]));
                var h = '<div class="cssbars">';
                for (var i = 0; i < vals.length; i++) {
                    var pct = vals[i] > 0 ? Math.max(Math.round(vals[i] / max * 100), 4) : 0;
                    h += '<div style="flex:1;display:flex;flex-direction:column;justify-content:flex-end;align-items:center;gap:6px;height:100%">' +
                        '<div style="width:68%;max-width:32px;height:' + pct + '%;border-radius:6px 6px 3px 3px;background:' + color + '"></div>' +
                        (labels ? '<em style="font-style:normal;font-size:10px;color:var(--muted)">' + labels[i] + '</em>' : '') +
                        '</div>';
                }
                h += '</div>';
                box.innerHTML = h;
            }

            function buildFallback(d) {
                chartMode = 'css';
                console.warn('[NiceDash] Chart.js unavailable — CSS fallback bars used');

                var ocVals = segMode === 'sales' ? d.revSeries : d.ordSeries;
                cssBars('orderChart', ocVals, d.months, 'var(--forest)');
                cssBars('usersChart', d.uVals, d.uLabels, 'var(--lime)');
                cssBars('sparkline', d.revSeries.slice(-5), null, 'var(--forest)');
                cssBars('miniBars', d.dayVals, null, 'rgba(255,255,255,.9)');
                setCustomerChartEmpty(d);

                var c = document.getElementById('segChart');
                var box = boxes.segChart || (c && c.parentElement);
                if (box) {
                    boxes.segChart = box;
                    var total = d.statusCounts.reduce(function (a, s) { return a + s.count; }, 0);
                    var inner = d.statusCounts.map(function (s) {
                        return '<span style="flex:' + Math.max(s.count, 0) + ';background:' + s.color + '"></span>';
                    }).join('');
                    box.innerHTML = '<div style="display:flex;flex-direction:column;justify-content:center;height:100%;padding:10px 4px">' +
                        '<div style="display:flex;height:14px;border-radius:10px;overflow:hidden;background:var(--bg)">' + (total ? inner : '') + '</div>' +
                        '<p style="margin-top:8px;font-size:11px;color:var(--muted);text-align:center">' + (total ? 'Order status distribution' : 'No orders yet') + '</p></div>';
                }
            }

            function buildCharts(d) {
                if (chartMode === 'chart') { try { buildReal(d); } catch (e) { console.error('[NiceDash charts]', e); buildFallback(d); } return; }
                if (chartMode === 'css') { buildFallback(d); return; }
                loadChart(
                    function () { try { buildReal(d); } catch (e) { console.error('[NiceDash charts]', e); buildFallback(d); } },
                    function () { buildFallback(d); }
                );
            }

            function setCustomerChartEmpty(d) {
                var canvas = document.getElementById('usersChart');
                var box = boxes.usersChart || (canvas && canvas.parentElement);
                if (!box) return;
                boxes.usersChart = box;
                var message = box.querySelector('.chart-empty');
                if (!message) {
                    message = document.createElement('p');
                    message.className = 'chart-empty';
                    message.setAttribute('role', 'status');
                    box.appendChild(message);
                }
                var hasSignups = Array.isArray(d.uVals) && d.uVals.some(function (value) { return Number(value) > 0; });
                message.textContent = hasSignups ? '' : 'No new customer registrations in the last 7 days';
                message.hidden = hasSignups;
            }

            function setSeg(mode) {
                segMode = mode;
                if (chartMode === 'chart' && charts.orderChart) {
                    charts.orderChart.data.datasets[0].hidden = (mode !== 'sales');
                    charts.orderChart.data.datasets[1].hidden = (mode !== 'orders');
                    charts.orderChart.update();
                } else if (chartMode === 'css') {
                    cssBars('orderChart', mode === 'sales' ? DASH.revSeries : DASH.ordSeries, DASH.months, 'var(--forest)');
                }
            }

            /* ============ RENDERERS (AJAX refresh) ============ */
            function thumb(o, cls) {
                return '<div class="thumb ' + cls + '">' + (o.img ? '<img src="' + assetBase + o.img + '" alt="">' : o.emoji) + '</div>';
            }

            function renderAll(d) {
                setNum('revBig', d.revenue, '£', 2);
                setNum('monthBig', d.revNow, '£', 0);
                setNum('growthBig', d.revGrowth, d.revGrowth >= 0 ? '+' : '', 0, '%');
                var ni = document.getElementById('netIncome'); if (ni) ni.textContent = fmt(d.revNow);
                var gt = document.getElementById('growthTxt'); if (gt) gt.innerHTML = (d.revGrowth >= 0 ? 'Better' : 'Lower') + ' than last month <strong>(' + (d.revGrowth >= 0 ? '+' : '') + d.revGrowth + '%)</strong>';
                var rt = document.getElementById('revTrend'); if (rt) rt.textContent = (d.revGrowth >= 0 ? '+' : '') + d.revGrowth + '%';

                setNum('statOrders', d.ordersCount);
                setNum('statShipped', d.shippedCount);
                setNum('statRevenue', d.revenue, '£', 0);
                setNum('statRating', d.avgRating, '', 1, '/5.0');
                badge('ordBadge', d.ordGrowth); badge('revBadge', d.revGrowth);
                var sb = document.getElementById('shipBadge'); if (sb) { sb.className = 'badge-up'; sb.innerHTML = '<i class="ri-arrow-up-line"></i>' + d.shipPct + '%'; }
                var rb = document.getElementById('ratBadge'); if (rb) rb.innerHTML = '<i class="ri-star-line"></i>' + (d.reviewsCount > 0 ? d.reviewsCount + ' reviews' : 'No reviews');
                var rSub = document.getElementById('ratSub'); if (rSub) rSub.textContent = 'Customer Satisfaction · ' + (d.reviewsCount > 0 ? d.reviewsCount + ' approved reviews' : 'no reviews yet');
                var os = document.getElementById('ordSub'); if (os) os.textContent = d.pendingCount + ' pending right now';
                var rs = document.getElementById('revSub'); if (rs) rs.textContent = 'This month: ' + fmt(d.revNow);
                setNum('ovRev', d.revenue, '£', 0);
                setNum('ovItems', d.itemsSold);

                var segL = document.getElementById('segLegend');
                if (segL) segL.innerHTML = d.statusCounts.length ? d.statusCounts.map(function (s) {
                    var pct = d.ordersCount > 0 ? Math.round(s.count / d.ordersCount * 100) : 0;
                    return '<div class="legend-row"><span class="lg-dot" style="background:' + s.color + '"></span><span class="lg-name">' + s.label + '</span><b>' + s.count.toLocaleString() + '</b><span class="lg-pct">' + pct + '%</span></div>';
                }).join('') : '<div class="legend-row"><span class="lg-name">No orders yet</span></div>';

                var uL = document.getElementById('usersLegend');
                if (uL) uL.innerHTML =
                    '<div class="legend-row"><span class="lg-dot" style="background:var(--forest)"></span><span class="lg-name">Total Customers</span><b>' + d.totalCustomers.toLocaleString() + '</b></div>' +
                    '<div class="legend-row"><span class="lg-dot" style="background:var(--lime)"></span><span class="lg-name">New This Week</span><b>' + d.newThisWeek.toLocaleString() + '</b></div>';

                var body = document.getElementById('recentBody');
                if (body) body.innerHTML = d.recent.length ? d.recent.map(function (o, i) {
                    var av = [['#e8f1fd', '#2563eb'], ['#fdeee0', '#d97706'], ['#e0f5fb', '#0e8fb5'], ['#f1ecfd', '#7c3aed'], ['#fdeef2', '#db2777']][i % 5];
                    return '<tr>' +
                        '<td><div class="prod">' + thumb(o, 't' + ((i % 5) + 1)) + '<div><strong>' + o.product + '</strong><span>' + (o.cat || 'Cosmetics') + '</span></div></div></td>' +
                        '<td><div class="cust"><span class="c-av" style="background:' + av[0] + ';color:' + av[1] + '">' + o.initials + '</span>' + o.customer + '</div></td>' +
                        '<td>' + o.qty + ' Pcs</td>' +
                        '<td><span class="pill ' + o.status + '">' + o.status.charAt(0).toUpperCase() + o.status.slice(1) + '</span></td>' +
                        '<td><span class="pay"><i class="ri-bank-card-line"></i>' + o.payment + '</span><span class="pill mini ' + (o.paid ? 'paid' : 'unpaid') + '">' + (o.paid ? 'Paid' : 'Unpaid') + '</span></td>' +
                        '<td class="t-right"><b>' + fmt(o.total) + '</b></td></tr>';
                }).join('') : '<tr><td colspan="6" class="empty">No orders yet — orders will appear here 💫</td></tr>';

                var foot = document.getElementById('ordersFoot');
                if (foot) foot.textContent = 'Showing ' + Math.min(6, d.ordersCount) + ' of ' + d.ordersCount.toLocaleString() + ' orders';

                var lb = document.getElementById('latestBox');
                if (lb) lb.innerHTML = d.latest.length ? d.latest.map(function (p, i) {
                    return '<div class="lp-row">' + thumb(p, 't' + ((i % 5) + 1)) + '<div class="lp-info"><strong>' + p.name + '</strong><span>' + (p.cat || 'Cosmetics') + ' · ' + p.time + '</span></div><b>' + fmt(p.price) + '</b></div>';
                }).join('') : '<div class="empty">No products yet</div>';

                var cb = document.getElementById('catsBox');
                if (cb) cb.innerHTML = d.cats.length ? d.cats.map(function (c) {
                    return '<div class="dl-row"><i class="ri-folder-2-line" style="color:var(--lime)"></i><span class="lg-name">' + c.name + '</span><b>' + c.count + ' products</b></div>';
                }).join('') : '<div class="empty">No categories yet</div>';

                buildCharts(d);
            }

            /* ============ AJAX ============ */
            var refreshInFlight = false;
            function refreshDash(silent) {
                if (refreshInFlight) return;
                refreshInFlight = true;
                var btn = document.getElementById('refreshBtn');
                var liveStatus = document.getElementById('dashboardLiveStatus');
                if (!silent && btn) btn.innerHTML = '<i class="ri-refresh-line spin"></i>Refreshing';
                fetch(URLS.data, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                    .then(function (r) {
                        if (!r.ok) throw new Error('Dashboard refresh failed.');
                        return r.json();
                    })
                    .then(function (j) {
                        if (!j || !j.ok || !j.d) throw new Error('Dashboard refresh returned invalid data.');
                        DASH = j.d;
                        renderAll(DASH);
                        if (liveStatus) liveStatus.textContent = 'Updated ' + new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) + ' · auto-updates every 30 seconds';
                        if (!silent && window.nbToast) nbToast('Dashboard refreshed ✨');
                    })
                    .catch(function () {
                        if (liveStatus) liveStatus.textContent = 'Live update failed · will retry automatically';
                        if (!silent && window.nbToast) nbToast('Refresh failed — check connection');
                    })
                    .finally(function () {
                        refreshInFlight = false;
                        if (!silent && btn) btn.innerHTML = '<i class="ri-refresh-line"></i>Refresh';
                    });
            }

            function exportCsv() {
                var rows = [['Order', 'Date', 'Customer', 'Items', 'Status', 'Payment', 'Total']];
                (DASH.recent || []).forEach(function (o) { rows.push([o.code, o.date, o.customer, o.qty, o.status, o.payment, Number(o.total).toFixed(2)]); });
                var csv = rows.map(function (r) { return r.join(','); }).join('\n');
                var a = document.createElement('a');
                a.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv' }));
                a.download = 'orders-report.csv';
                a.click();
                if (window.nbToast) nbToast('Report downloaded 📄');
            }

            /* ============ BINDINGS (delegated — SPA-safe, guarded) ============ */
            if (!window.__admDashBound) {
                window.__admDashBound = true;
                document.addEventListener('click', function (e) {
                    if (!e.target || !e.target.closest) return;
                    var seg = e.target.closest('.seg-btn');
                    if (seg && seg.dataset.set) {
                        document.querySelectorAll('.seg-btn').forEach(function (x) { x.classList.remove('active'); });
                        seg.classList.add('active');
                        setSeg(seg.dataset.set);
                    }
                    if (e.target.closest('#refreshBtn') || e.target.closest('#refreshRound') || e.target.closest('[data-act="refresh"]')) refreshDash();
                    if (e.target.closest('#exportBtn')) exportCsv();
                });
                document.addEventListener('nd:theme', function () { setTimeout(function () { buildCharts(DASH); }, 60); });
            }

            /* ============ BOOT ============ */
            function boot() {
                if (booted) return;
                booted = true;
                buildCharts(DASH);
            }
            boot();
            if (window.__admDashRefreshTimer) window.clearInterval(window.__admDashRefreshTimer);
            window.__admDashRefreshTimer = window.setInterval(function () {
                if (!document.hidden && document.getElementById('revBig')) refreshDash(true);
            }, 30000);
            if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
            window.addEventListener('load', function () { if (chartMode === 'none') { booted = false; boot(); } });
        })();
    </script>
</body>

</html>