{{-- ==========================================================
     ADMIN · ORDERS — v7 (🚚 MOVING CAR TRACKER RESTORED · responsive)
     Car drives on road per status · status change = car drives there
========================================================== --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0e5a75">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Orders — NiceDash Admin</title>
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

        html,
        body {
            max-width: 100vw;
            overflow-x: clip
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            font-size: 14px;
            -webkit-text-size-adjust: 100%;
            -webkit-tap-highlight-color: transparent
        }

        img,
        canvas,
        svg {
            max-width: 100%
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
            max-width: 100%;
            transition: margin .35s cubic-bezier(.4, 0, .2, 1)
        }

        .layout.collapsed .main {
            margin-left: 84px
        }

        .content {
            padding: 24px 26px calc(40px + env(safe-area-inset-bottom));
            display: grid;
            gap: 22px;
            min-width: 0;
            max-width: 100%
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

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 22px;
            min-width: 0;
            max-width: 100%;
            transition: transform .3s, box-shadow .3s, background .4s
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
            overflow: hidden
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

        .i-amber {
            background: #fdf4e3;
            color: #d97706
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

        body.dark .i-amber {
            background: rgba(251, 191, 36, .12);
            color: #fbbf24
        }

        body.dark .i-violet {
            background: rgba(167, 139, 250, .12);
            color: #a78bfa
        }

        /* clamp = lamba number bhi chhoti screen pe fit */
        .big {
            font-size: clamp(20px, 3.2vw, 29px);
            font-weight: 800;
            letter-spacing: -.6px;
            overflow-wrap: anywhere
        }

        .big.sm {
            font-size: clamp(18px, 2.6vw, 23px)
        }

        .muted {
            color: var(--muted)
        }

        .small {
            font-size: 12.5px
        }

        .toolbar {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap
        }

        .t-search {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 11px 14px;
            width: 300px;
            max-width: 100%;
            transition: .3s;
            color: var(--muted);
            box-shadow: var(--shadow)
        }

        .t-search:focus-within {
            border-color: var(--lime);
            box-shadow: 0 0 0 4px rgba(62, 197, 224, .15);
            width: 360px
        }

        .t-search input {
            border: none;
            outline: none;
            background: transparent;
            color: var(--text);
            flex: 1;
            font: inherit;
            font-size: 16px;
            min-width: 0
        }

        .seg {
            display: flex;
            background: var(--card);
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

        .ord-pager {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
            margin-top: 2px
        }

        .ord-pager-info {
            color: var(--muted);
            font-size: 12px;
            font-weight: 700
        }

        .ord-pager-controls {
            display: flex;
            align-items: center;
            gap: 7px
        }

        #ordersPagination {
            display: flex;
            align-items: center;
            gap: 5px
        }

        .ord-pager select,
        .ord-page-btn {
            height: 36px;
            border: 1px solid var(--border);
            border-radius: 9px;
            background: var(--card);
            color: var(--text);
            font: 700 12px 'Plus Jakarta Sans', sans-serif
        }

        .ord-pager select {
            padding: 0 9px;
            margin-right: 5px
        }

        .ord-page-btn {
            min-width: 36px;
            padding: 0 10px;
            cursor: pointer
        }

        .ord-page-btn:hover:not(:disabled),
        .ord-page-btn.on {
            background: var(--forest);
            border-color: var(--forest);
            color: #fff
        }

        .ord-page-btn:disabled {
            opacity: .45;
            cursor: default
        }

        .ord-no-match {
            display: none;
            padding: 28px 18px;
            text-align: center;
            color: var(--muted);
            background: var(--card);
            border: 1px dashed var(--border);
            border-radius: var(--radius);
            font-weight: 700
        }

        .seg-btn {
            border: none;
            background: transparent;
            color: var(--muted);
            font-weight: 700;
            font-size: 12.5px;
            padding: 7px 14px;
            border-radius: 9px;
            cursor: pointer;
            transition: .25s;
            white-space: nowrap;
            font-family: inherit
        }

        .seg-btn.active {
            background: var(--forest);
            color: #fff;
            box-shadow: 0 4px 10px rgba(14, 90, 117, .3)
        }

        /* ===== ORDER CARDS ===== */
        .ord-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(min(100%, 350px), 1fr));
            gap: 22px
        }

        .ord-card {
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            gap: 12px;
            animation: fadeUp .5s cubic-bezier(.22, 1, .36, 1) both;
            min-width: 0
        }

        .ord-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3.5px;
            background: linear-gradient(90deg, var(--forest), var(--lime))
        }

        .ord-card.flash {
            animation: cardFlash 1.2s ease
        }

        @keyframes cardFlash {
            0% {
                background: rgba(62, 197, 224, .22)
            }

            100% {
                background: var(--card)
            }
        }

        .ord-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
            flex-wrap: wrap
        }

        .ord-id {
            display: flex;
            align-items: center;
            gap: 11px;
            min-width: 0
        }

        .ric {
            width: 44px;
            height: 44px;
            min-width: 44px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-size: 20px;
            background: linear-gradient(135deg, #e0f5fb, #c2e9f5);
            color: #0e5a75;
            overflow: hidden
        }

        body.dark .ric {
            background: rgba(62, 197, 224, .12);
            color: #4ddcf5
        }

        .ric img {
            width: 100%;
            height: 100%;
            object-fit: cover
        }

        .ord-id b {
            display: block;
            font-size: 15px;
            letter-spacing: -.2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis
        }

        .ord-date {
            font-size: 11px;
            color: var(--muted);
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-weight: 600
        }

        .ord-cust {
            min-width: 0
        }

        .ord-cust strong {
            display: block;
            font-size: 13.5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis
        }

        .ord-cust span {
            font-size: 12px;
            color: var(--muted);
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis
        }

        .ord-meta {
            display: flex;
            gap: 8px;
            flex-wrap: wrap
        }

        .ord-actions { display:flex; flex-wrap:wrap; gap:8px }
        .ord-actions a, .ord-actions button { padding:8px 11px; border:1px solid var(--border); border-radius:9px; background:var(--card); color:var(--text); font-family:inherit; font-size:11px; font-weight:600; cursor:pointer }
        .ord-actions a { text-decoration:none }
        .ord-actions form { display:inline }
        .ord-actions .delete { color:#bd3030; border-color:#f3caca }

        .m-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--bg);
            border: 1px solid var(--border);
            font-size: 11px;
            font-weight: 700;
            color: var(--muted);
            padding: 4px 11px;
            border-radius: 20px;
            white-space: nowrap;
            max-width: 100%;
            overflow: hidden
        }

        /* ============================================
   ★★★ THE MOVING CAR ROAD TRACKER ★★★
   ============================================ */
        .road-zone {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 14px 12px 6px;
            margin-top: 2px
        }

        .road {
            position: relative;
            height: 34px
        }

        .road-track {
            position: absolute;
            left: 4px;
            right: 4px;
            top: 26px;
            height: 4px;
            border-radius: 4px;
            background: var(--border)
        }

        .road-fill {
            position: absolute;
            left: 4px;
            top: 26px;
            height: 4px;
            border-radius: 4px;
            background: linear-gradient(90deg, var(--forest-2), var(--lime));
            transition: width 1.2s cubic-bezier(.22, 1, .36, 1)
        }

        .road-dashes {
            position: absolute;
            left: 4px;
            right: 4px;
            top: 27.5px;
            height: 1.5px;
            background: repeating-linear-gradient(90deg, rgba(255, 255, 255, .7) 0 8px, transparent 8px 16px);
            background-size: 16px 1.5px;
            opacity: .6
        }

        .stop {
            position: absolute;
            top: 28px;
            width: 11px;
            height: 11px;
            border-radius: 50%;
            background: var(--card);
            border: 3px solid var(--border);
            transform: translate(-50%, -50%);
            transition: .4s;
            z-index: 2
        }

        .stop.done {
            border-color: var(--forest-2);
            background: var(--lime)
        }

        .stop-lbl {
            position: absolute;
            top: 36px;
            transform: translateX(-50%);
            font-size: 8.5px;
            font-weight: 800;
            letter-spacing: .03em;
            color: var(--muted);
            white-space: nowrap
        }

        .stop-lbl.done {
            color: var(--forest)
        }

        body.dark .stop-lbl.done {
            color: var(--lime)
        }

        /* car — white circle + truck icon, sits ON the road */
        .car {
            position: absolute;
            top: 15px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--card);
            border: 1px solid var(--border);
            box-shadow: 0 6px 14px rgba(14, 90, 117, .22);
            display: grid;
            place-items: center;
            font-size: 16px;
            color: var(--forest);
            transform: translate(-50%, 0);
            transition: left 1.2s cubic-bezier(.22, 1, .36, 1);
            z-index: 3
        }

        body.dark .car {
            color: var(--lime)
        }

        .car .ex {
            position: absolute;
            left: -7px;
            width: 6px;
            height: 2px;
            border-radius: 2px;
            background: rgba(14, 90, 117, .35);
            opacity: 0
        }

        .car .ex.x2 {
            left: -11px
        }

        /* in-transit = road dashes move + car bounces + exhaust puffs */
        .st-processing .road-dashes,
        .st-shipped .road-dashes {
            animation: roadMove .7s linear infinite
        }

        @keyframes roadMove {
            from {
                background-position: 0 0
            }

            to {
                background-position: -16px 0
            }
        }

        .st-processing .car,
        .st-shipped .car {
            animation: carBob .45s ease-in-out infinite alternate
        }

        @keyframes carBob {
            from {
                transform: translate(-50%, 0)
            }

            to {
                transform: translate(-50%, -2.5px)
            }
        }

        .st-processing .car .ex,
        .st-shipped .car .ex {
            animation: puff .9s linear infinite
        }

        .st-processing .car .ex.x2,
        .st-shipped .car .ex.x2 {
            animation-delay: .25s
        }

        @keyframes puff {
            0% {
                opacity: 0;
                transform: translateX(0)
            }

            30% {
                opacity: .9
            }

            100% {
                opacity: 0;
                transform: translateX(-9px)
            }
        }

        /* car position per status */
        .st-pending .car {
            left: 6%
        }

        .st-processing .car {
            left: 38%
        }

        .st-shipped .car {
            left: 68%
        }

        .st-delivered .car {
            left: 94%
        }

        .st-cancelled .car {
            left: 6%;
            color: #ef4444;
            border-color: #fecaca
        }

        /* road fill width per status */
        .st-pending .road-fill {
            width: 2%
        }

        .st-processing .road-fill {
            width: 34%
        }

        .st-shipped .road-fill {
            width: 64%
        }

        .st-delivered .road-fill {
            width: 90%
        }

        .st-cancelled .road-fill {
            width: 2%;
            background: #ef4444
        }

        /* stops done per status (4 stops: Pending 6% · Processing 38% · Shipped 68% · Delivered 94%) */
        .st-processing .stop[data-s="1"],
        .st-shipped .stop[data-s="1"],
        .st-shipped .stop[data-s="2"],
        .st-delivered .stop[data-s="1"],
        .st-delivered .stop[data-s="2"],
        .st-delivered .stop[data-s="3"] {
            border-color: var(--forest-2);
            background: var(--lime)
        }

        .st-processing .stop-lbl[data-s="1"],
        .st-shipped .stop-lbl[data-s="1"],
        .st-shipped .stop-lbl[data-s="2"],
        .st-delivered .stop-lbl[data-s="1"],
        .st-delivered .stop-lbl[data-s="2"],
        .st-delivered .stop-lbl[data-s="3"] {
            color: var(--forest)
        }

        body.dark .st-processing .stop-lbl[data-s="1"],
        body.dark .st-shipped .stop-lbl[data-s="1"],
        body.dark .st-shipped .stop-lbl[data-s="2"],
        body.dark .st-delivered .stop-lbl[data-s="1"],
        body.dark .st-delivered .stop-lbl[data-s="2"],
        body.dark .st-delivered .stop-lbl[data-s="3"] {
            color: var(--lime)
        }

        .st-cancelled .stop,
        .st-cancelled .stop-lbl {
            opacity: .45
        }

        .ord-foot {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            border-top: 1px dashed var(--border);
            padding-top: 12px;
            flex-wrap: wrap
        }

        .ord-left {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            min-width: 0
        }

        .ord-total {
            font-weight: 800;
            color: var(--forest);
            font-size: 18px;
            letter-spacing: -.4px;
            white-space: nowrap
        }

        body.dark .ord-total {
            color: var(--lime)
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 11px;
            border-radius: 20px;
            font-size: 10.5px;
            font-weight: 800
        }

        .pill i {
            font-size: 13px
        }

        .pill.paid {
            background: #dcfce7;
            color: #16a34a
        }

        .pill.unpaid {
            background: #fee2e2;
            color: #dc2626
        }

        body.dark .pill.paid {
            background: rgba(74, 222, 128, .12);
            color: #4ade80
        }

        body.dark .pill.unpaid {
            background: rgba(248, 113, 113, .12);
            color: #f87171
        }

        .eta-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: var(--lime-soft);
            color: var(--forest);
            font-size: 10.5px;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 20px;
            white-space: nowrap
        }

        body.dark .eta-chip {
            color: var(--lime)
        }

        .ord-right {
            display: flex;
            align-items: center;
            gap: 8px
        }

        .act-view {
            width: 36px;
            height: 36px;
            border-radius: 11px;
            display: grid;
            place-items: center;
            font-size: 16px;
            background: rgba(14, 90, 117, .07);
            color: var(--forest);
            border: 1px solid rgba(14, 90, 117, .2);
            transition: .25s;
            flex-shrink: 0
        }

        .act-view:hover {
            background: var(--forest);
            color: var(--lime);
            transform: translateY(-2px)
        }

        .st-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 12px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 800;
            border: none;
            cursor: pointer;
            transition: .25s;
            font-family: inherit;
            white-space: nowrap
        }

        .st-btn:disabled {
            opacity: .55;
            cursor: wait
        }

        .st-btn .st-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: currentColor;
            box-shadow: 0 0 7px currentColor
        }

        .st-btn i {
            font-size: 14px
        }

        .tint-pending {
            background: #fef3c7;
            color: #b45309
        }

        .tint-processing {
            background: #e8f1fd;
            color: #2563eb
        }

        .tint-shipped {
            background: #f1ecfd;
            color: #7c3aed
        }

        .tint-delivered {
            background: #dcfce7;
            color: #16a34a
        }

        .tint-cancelled {
            background: #fee2e2;
            color: #dc2626
        }

        body.dark .tint-pending {
            background: rgba(251, 191, 36, .12);
            color: #fbbf24
        }

        body.dark .tint-processing {
            background: rgba(96, 165, 250, .12);
            color: #60a5fa
        }

        body.dark .tint-shipped {
            background: rgba(167, 139, 250, .12);
            color: #a78bfa
        }

        body.dark .tint-delivered {
            background: rgba(74, 222, 128, .12);
            color: #4ade80
        }

        body.dark .tint-cancelled {
            background: rgba(248, 113, 113, .12);
            color: #f87171
        }

        .st-menu {
            position: fixed;
            z-index: 120;
            min-width: 180px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: 0 24px 50px rgba(2, 6, 23, .25);
            padding: 8px;
            display: none
        }

        .st-menu.open {
            display: block;
            animation: stPop .18s cubic-bezier(.22, 1, .36, 1)
        }

        @keyframes stPop {
            from {
                opacity: 0;
                transform: translateY(6px) scale(.96)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        .st-menu-t {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: var(--muted);
            font-weight: 800;
            padding: 4px 10px 8px;
            display: flex;
            gap: 6px;
            align-items: center
        }

        .st-item {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 9px 10px;
            border: none;
            background: transparent;
            border-radius: 10px;
            font: 700 12.5px 'Plus Jakarta Sans', sans-serif;
            color: var(--text);
            cursor: pointer;
            transition: .15s;
            text-align: left
        }

        .st-item:hover,
        .st-item.on {
            background: var(--lime-soft)
        }

        .st-item .st-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: currentColor;
            flex-shrink: 0
        }

        /* ===== VIEW MODAL ===== */
        .nb-modal {
            position: fixed;
            inset: 0;
            z-index: 110;
            display: grid;
            place-items: center;
            background: rgba(2, 6, 23, .55);
            opacity: 0;
            visibility: hidden;
            transition: .3s;
            padding: 14px
        }

        .nb-modal.show {
            opacity: 1;
            visibility: visible
        }

        .vw-box {
            width: min(720px, 100%);
            max-height: 92vh;
            overflow-y: auto;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow: 0 40px 90px rgba(0, 0, 0, .4);
            position: relative;
            transform: translateY(18px) scale(.96);
            transition: .35s cubic-bezier(.22, 1, .36, 1);
            min-width: 0
        }

        .nb-modal.show .vw-box {
            transform: none
        }

        .vw-close {
            position: absolute;
            top: 16px;
            right: 16px;
            z-index: 5;
            width: 38px;
            height: 38px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            background: rgba(2, 6, 23, .55);
            color: #fff;
            font-size: 18px;
            display: grid;
            place-items: center;
            transition: .25s
        }

        .vw-close:hover {
            background: #ef4444;
            transform: rotate(90deg)
        }

        .vw-head {
            padding: 24px 26px 20px;
            color: #fff;
            background: linear-gradient(120deg, #0d4257, #17739b);
            position: relative;
            overflow: hidden
        }

        .vw-head::after {
            content: '';
            position: absolute;
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .08);
            top: -60px;
            right: -40px
        }

        .vw-head .vw-num {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -.3px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            position: relative;
            z-index: 1;
            overflow-wrap: anywhere
        }

        .vw-head .vw-date {
            font-size: 12px;
            opacity: .85;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 6px;
            position: relative;
            z-index: 1
        }

        .vw-body {
            padding: 22px 24px 26px;
            display: grid;
            gap: 18px;
            min-width: 0
        }

        .vw-sec-t {
            font-size: 10.5px;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: 7px
        }

        .vw-sec-t i {
            font-size: 14px;
            color: var(--lime)
        }

        .vw-grid2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px
        }

        .vw-info {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 14px 16px;
            min-width: 0
        }

        .vw-info b {
            display: block;
            font-size: 13.5px;
            overflow-wrap: anywhere
        }

        .vw-info span {
            display: block;
            font-size: 12px;
            color: var(--muted);
            margin-top: 3px;
            line-height: 1.6;
            overflow-wrap: anywhere
        }

        .vw-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 0;
            border-bottom: 1px dashed var(--border);
            min-width: 0
        }

        .vw-item:last-child {
            border-bottom: none
        }

        .vw-item .im {
            width: 46px;
            height: 46px;
            min-width: 46px;
            border-radius: 12px;
            background: linear-gradient(135deg, #e0f5fb, #c2e9f5);
            display: grid;
            place-items: center;
            font-size: 20px;
            overflow: hidden
        }

        .vw-item .im img {
            width: 100%;
            height: 100%;
            object-fit: cover
        }

        .vw-item .nm {
            flex: 1;
            min-width: 0
        }

        .vw-item .nm b {
            display: block;
            font-size: 13px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis
        }

        .vw-item .nm span {
            font-size: 11.5px;
            color: var(--muted)
        }

        .vw-item .ln {
            font-weight: 800;
            font-size: 13.5px;
            color: var(--forest);
            white-space: nowrap
        }

        body.dark .vw-item .ln {
            color: var(--lime)
        }

        .vw-tot {
            background: var(--bg);
            border: 1px dashed var(--border);
            border-radius: 14px;
            padding: 14px 16px;
            display: grid;
            gap: 8px
        }

        .vw-trow {
            display: flex;
            justify-content: space-between;
            font-size: 12.5px;
            color: var(--muted);
            font-weight: 600;
            gap: 10px
        }

        .vw-trow.grand {
            border-top: 1px solid var(--border);
            padding-top: 9px;
            font-size: 15px;
            font-weight: 800;
            color: var(--forest)
        }

        body.dark .vw-trow.grand {
            color: var(--lime)
        }

        .vw-pay,
        .vw-eta {
            display: flex;
            gap: 8px;
            flex-wrap: wrap
        }

        .swal-toast {
            background: #111c2e !important;
            border: 1px solid rgba(62, 197, 224, .25) !important;
            color: #fff !important;
            border-radius: 14px !important
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

        /* ================= RESPONSIVE ================= */
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
                width: 84%
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

            .t-search {
                width: 86%
            }

            .t-search:focus-within {
                width: 100%
            }

            .ord-grid {
                grid-template-columns: 1fr;
                gap: 14px
            }

            .card {
                padding: 16px;
                border-radius: 14px;
                width: 100%;
            }

            .page-head h2 {
                font-size: 20px
            }

            .vw-grid2 {
                grid-template-columns: 1fr
            }

            .vw-head {
                padding: 20px 18px 18px
            }

            .vw-body {
                padding: 18px 16px 22px
            }

            .road-zone {
                padding: 12px 10px 4px
            }
        }

        @media(max-width:480px) {
            .content {
                padding: 14px 10px calc(40px + env(safe-area-inset-bottom))
            }

            .r-stats {
                grid-template-columns: 1fr
            }

            .ord-total {
                font-size: 16px
            }

            .st-btn {
                padding: 6px 10px;
                font-size: 11px
            }

            .m-chip {
                font-size: 10.5px;
                padding: 4px 9px
            }

            .act-view {
                width: 34px;
                height: 34px
            }

            .vw-num {
                font-size: 17px
            }

            .eta-chip {
                font-size: 10px;
                padding: 4px 9px
            }

            .stop-lbl {
                font-size: 7.5px
            }
        }

        @media(max-width:360px) {
            .ord-top {
                gap: 8px
            }

            .ord-id {
                gap: 9px
            }

            .ric {
                width: 40px;
                height: 40px;
                min-width: 40px;
                font-size: 17px
            }

            .stop-lbl {
                display: none
            }

            /* bahut chhota screen — sirf road + car */
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
                $ordList = collect($orders ?? []);
                $stTotal = (int) ($stats['total'] ?? $ordList->count());
                $stPending = (int) ($stats['pending'] ?? 0);
                $stDelivered= (int) ($stats['delivered'] ?? 0);
                $stRevenue = (float) ($stats['revenue'] ?? 0);
                @endphp

                <div class="page-head fx">
                    <div>
                        <h2><span class="ph-ic"><i class="ri-shopping-bag-4-line"></i></span>Orders</h2>
                        <p>Track delivery of every order — the truck drives as it moves 🚚</p>
                    </div>
                </div>

                <section class="r-stats">
                    <div class="card stat fx d1">
                        <div class="stat-top">
                            <div class="icon i-blue"><i class="ri-receipt-line"></i></div>
                        </div>
                        <div class="big sm" id="statTotal">{{ number_format($stTotal) }}</div>
                        <p class="muted small">Total orders</p>
                    </div>
                    <div class="card stat fx d2">
                        <div class="stat-top">
                            <div class="icon i-amber"><i class="ri-time-line"></i></div>
                        </div>
                        <div class="big sm" id="statPending">{{ number_format($stPending) }}</div>
                        <p class="muted small">Pending</p>
                    </div>
                    <div class="card stat fx d3">
                        <div class="stat-top">
                            <div class="icon i-green"><i class="ri-checkbox-circle-line"></i></div>
                        </div>
                        <div class="big sm" id="statDelivered">{{ number_format($stDelivered) }}</div>
                        <p class="muted small">Delivered</p>
                    </div>
                    <div class="card stat fx d3">
                        <div class="stat-top">
                            <div class="icon i-violet"><i class="ri-wallet-3-line"></i></div>
                        </div>
                        <div class="big sm" id="statRevenue">£{{ number_format($stRevenue, 2) }}</div>
                        <p class="muted small">Paid revenue</p>
                    </div>
                </section>

                <div class="toolbar fx d3">
                    <div class="t-search">
                        <i class="ri-search-line"></i>
                        <input type="text" id="searchInput" oninput="applyOrderFilter()" placeholder="Search order #, customer, email...">
                    </div>
                    <div class="seg" id="filterSeg">
                        <button type="button" class="seg-btn active" data-filter="all" onclick="setOrderFilter(this)">All</button>
                        <button type="button" class="seg-btn" data-filter="pending" onclick="setOrderFilter(this)">Pending</button>
                        <button type="button" class="seg-btn" data-filter="processing" onclick="setOrderFilter(this)">Processing</button>
                        <button type="button" class="seg-btn" data-filter="shipped" onclick="setOrderFilter(this)">Shipped</button>
                        <button type="button" class="seg-btn" data-filter="delivered" onclick="setOrderFilter(this)">Delivered</button>
                        <button type="button" class="seg-btn" data-filter="cancelled" onclick="setOrderFilter(this)">Cancelled</button>
                    </div>
                </div>

                @if ($ordList->isEmpty())
                <div class="card empty fx">
                    <div class="e-ic"><i class="ri-shopping-bag-3-line"></i></div>
                    <h4>No orders yet</h4>
                    <p>Customer orders will appear here automatically.</p>
                </div>
                @else
                <section class="ord-grid" id="ordersGrid">
                    @foreach ($ordList as $o)
                    @php
                    $st = strtolower($o->status ?? 'pending');
                    $paid = strtolower($o->payment_status ?? '') === 'paid';
                    $total = (float) $o->total;
                    $iCnt = $o->items->count();
                    $first = $o->items->first();
                    $img = ($first && $first->product && $first->product->images->first())
                    ? asset('storage/' . $first->product->images->first()->path) : null;
                    $eta = $o->created_at ? $o->created_at->copy()->addDays(7)->format('M d') : '—';
                    @endphp
                    <div class="card ord-card st-{{ $st }}"
                        data-id="{{ $o->id }}" data-status="{{ $st }}"
                        data-paid="{{ $paid ? 1 : 0 }}" data-total="{{ $total }}"
                        data-hay="{{ strtolower(($o->order_number ?? '#'.$o->id).' '.($o->name ?? '').' '.($o->email ?? '').' '.$st) }}"
                        style="animation-delay: {{ 0.05 + ($loop->index % 8) * 0.05 }}s">
                        <div class="ord-top">
                            <div class="ord-id">
                                <span class="ric">
                                    @if ($img)<img src="{{ $img }}" alt="">
                                    @else<i class="ri-receipt-line"></i>@endif
                                </span>
                                <div>
                                    <b>{{ $o->order_number ?? ('#'.$o->id) }}</b>
                                    <span class="ord-date"><i class="ri-time-line"></i>{{ $o->created_at?->format('M d, Y') }}</span>
                                </div>
                            </div>
                            <button type="button" class="st-btn tint-{{ $st }}" data-id="{{ $o->id }}" data-curr="{{ $st }}"
                                data-url="{{ route('admin.orders.status', $o) }}"
                                onclick="toggleStatus(this)">
                                <span class="st-dot"></span><span class="st-lbl">{{ ucfirst($st) }}</span><i class="ri-arrow-down-s-line"></i>
                            </button>
                        </div>
                        <div class="ord-cust">
                            <strong>{{ $o->name ?? 'Guest' }}</strong>
                            <span>{{ $o->email ?? '' }}</span>
                        </div>
                        <div class="ord-meta">
                            <span class="m-chip"><i class="ri-shopping-bag-3-line"></i>{{ $iCnt }} item{{ $iCnt == 1 ? '' : 's' }}</span>
                            <span class="m-chip"><i class="ri-bank-card-line"></i>{{ strtoupper($o->payment_method ?? '—') }}</span>
                        </div>
                        <div class="ord-actions">
                            <a href="{{ route('admin.ecommerce.orders.edit', $o) }}"><i class="ri-edit-line"></i> Edit order / invoice</a>
                            <form method="POST" action="{{ route('admin.orders.destroy', $o) }}" data-confirm-modal data-confirm-title="Delete this order?" data-confirm-message="This order and its items will be permanently removed.">
                                @csrf @method('DELETE')
                                <button class="delete" type="submit"><i class="ri-delete-bin-line"></i> Delete order</button>
                            </form>
                        </div>

                        {{-- ★★★ MOVING CAR TRACKER ★★★ --}}
                        <div class="road-zone">
                            <div class="road">
                                <div class="road-track"></div>
                                <div class="road-fill"></div>
                                <div class="road-dashes"></div>
                                <span class="stop" data-s="1" style="left:6%"></span>
                                <span class="stop" data-s="2" style="left:38%"></span>
                                <span class="stop" data-s="3" style="left:68%"></span>
                                <span class="stop" data-s="4" style="left:94%"></span>
                                <span class="stop-lbl" data-s="1" style="left:6%">Pending</span>
                                <span class="stop-lbl" data-s="2" style="left:38%">Packed</span>
                                <span class="stop-lbl" data-s="3" style="left:68%">Shipped</span>
                                <span class="stop-lbl" data-s="4" style="left:94%">Delivered</span>
                                <span class="car"><i class="ri-truck-line"></i><span class="ex"></span><span class="ex x2"></span></span>
                            </div>
                        </div>

                        <div class="ord-foot">
                            <div class="ord-left">
                                <span class="ord-total">£{{ number_format($total, 2) }}</span>
                                <span class="pill {{ $paid ? 'paid' : 'unpaid' }}"><i class="ri-{{ $paid ? 'checkbox-circle' : 'error-warning' }}-line"></i>{{ $paid ? 'Paid' : 'Unpaid' }}</span>
                                <span class="eta-chip"><i class="ri-truck-line"></i>ETA {{ $eta }}</span>
                            </div>
                            <div class="ord-right">
                                <button type="button" class="act-view" title="View Details"
                                    data-url="{{ route('admin.orders.show', $o) }}"
                                    onclick="openOrderView(this)"><i class="ri-eye-line"></i></button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </section>
                <div class="ord-no-match" id="ordersNoMatch">No orders match your search or filter.</div>
                <div class="ord-pager" id="ordersPager">
                    <span class="ord-pager-info" id="ordersInfo"></span>
                    <div class="ord-pager-controls">
                        <label for="ordersPerPage" class="ord-pager-info">Rows</label>
                        <select id="ordersPerPage" onchange="ordSetPerPage(this.value)" aria-label="Orders per page">
                            <option value="6" selected>6</option>
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="all">All</option>
                        </select>
                        <div id="ordersPagination" aria-label="Orders pagination"></div>
                    </div>
                </div>
                @endif

            </main>
        </div>
    </div>

    {{-- status menu --}}
    <div class="st-menu" id="statusMenu">
        <p class="st-menu-t"><i class="ri-refresh-line"></i> Update Status</p>
        <button type="button" class="st-item" data-s="pending" onclick="pickStatus('pending')"><span class="st-dot" style="color:#b45309"></span>Pending</button>
        <button type="button" class="st-item" data-s="processing" onclick="pickStatus('processing')"><span class="st-dot" style="color:#2563eb"></span>Processing</button>
        <button type="button" class="st-item" data-s="shipped" onclick="pickStatus('shipped')"><span class="st-dot" style="color:#7c3aed"></span>Shipped</button>
        <button type="button" class="st-item" data-s="delivered" onclick="pickStatus('delivered')"><span class="st-dot" style="color:#16a34a"></span>Delivered</button>
        <button type="button" class="st-item" data-s="cancelled" onclick="pickStatus('cancelled')"><span class="st-dot" style="color:#dc2626"></span>Cancelled</button>
    </div>

    {{-- order details modal --}}
    <div class="nb-modal" id="viewModal" onclick="if (event.target === this) closeOrderView()">
        <div class="vw-box">
            <button type="button" class="vw-close" onclick="closeOrderView()"><i class="ri-close-line"></i></button>
            <div id="vwLoading" style="padding:60px;text-align:center;color:var(--muted)"><i class="ri-loader-4-line" style="font-size:28px;animation:spin 1s linear infinite;display:inline-block"></i>
                <p style="margin-top:10px;font-size:13px">Loading order…</p>
            </div>
            <div id="vwContent" style="display:none"></div>
        </div>
    </div>
    <style>
        @keyframes spin {
            to {
                transform: rotate(360deg)
            }
        }
    </style>

    <script>
        /* 🛡️ v7 — car tracker + SPA-safe globals */
        var ordCsrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
        var ordFilter = 'all';
        var ordPage = 1;
        var ordPerPage = 6;

        function _toast(icon, msg) {
            if (window.Swal) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2400,
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

        function _esc(s) {
            return (s === null || s === undefined || s === '' ? '—' : String(s))
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        /* ================= SEARCH + FILTER ================= */
        function applyOrderFilter() {
            var grid = document.getElementById('ordersGrid');
            if (!grid) return;
            var q = (document.getElementById('searchInput').value || '').toLowerCase().trim();
            var cards = Array.prototype.slice.call(grid.querySelectorAll('.ord-card'));
            var matches = cards.filter(function(c) {
                var okQ = (c.dataset.hay || '').indexOf(q) !== -1;
                var okF = (ordFilter === 'all') || (c.dataset.status === ordFilter);
                return okQ && okF;
            });
            var per = ordPerPage === 'all' ? Math.max(matches.length, 1) : parseInt(ordPerPage, 10);
            var pages = Math.max(1, Math.ceil(matches.length / per));
            ordPage = Math.min(Math.max(ordPage, 1), pages);
            var first = (ordPage - 1) * per;
            var end = Math.min(first + per, matches.length);
            cards.forEach(function(c) { c.style.display = 'none'; });
            matches.slice(first, end).forEach(function(c) { c.style.display = ''; });

            var info = document.getElementById('ordersInfo');
            var noMatch = document.getElementById('ordersNoMatch');
            if (info) info.textContent = matches.length ? 'Showing ' + (first + 1) + '–' + end + ' of ' + matches.length + ' orders' : 'No orders found';
            if (noMatch) noMatch.style.display = matches.length ? 'none' : 'block';
            renderOrderPagination(pages);
        }

        function ordGoPage(page) {
            ordPage = page;
            applyOrderFilter();
        }

        function ordSetPerPage(value) {
            ordPerPage = value === 'all' ? 'all' : (parseInt(value, 10) || 10);
            ordPage = 1;
            applyOrderFilter();
        }

        function renderOrderPagination(pageCount) {
            var box = document.getElementById('ordersPagination');
            if (!box) return;
            box.innerHTML = '';
            function add(label, page, disabled, active) {
                var button = document.createElement('button');
                button.type = 'button';
                button.className = 'ord-page-btn' + (active ? ' on' : '');
                button.textContent = label;
                button.disabled = disabled;
                if (!disabled) button.addEventListener('click', function() { ordGoPage(page); });
                box.appendChild(button);
            }
            add('‹', ordPage - 1, ordPage === 1, false);
            var start = Math.max(1, ordPage - 2);
            var finish = Math.min(pageCount, ordPage + 2);
            for (var page = start; page <= finish; page++) add(String(page), page, false, page === ordPage);
            add('›', ordPage + 1, ordPage === pageCount, false);
        }

        function setOrderFilter(btn) {
            document.querySelectorAll('#filterSeg .seg-btn').forEach(function(b) {
                b.classList.remove('active');
            });
            btn.classList.add('active');
            ordFilter = btn.dataset.filter;
            ordPage = 1;
            applyOrderFilter();
        }

        /* ================= STATUS DROPDOWN ================= */
        function closeStatusMenu() {
            var m = document.getElementById('statusMenu');
            if (m) m.classList.remove('open');
            window.__stBtn = null;
            window.__stId = null;
        }

        function toggleStatus(btn) {
            var menu = document.getElementById('statusMenu');
            if (!menu) return;
            if (window.__stBtn === btn && menu.classList.contains('open')) {
                closeStatusMenu();
                return;
            }
            window.__stBtn = btn;
            window.__stId = btn.dataset.id;
            menu.querySelectorAll('.st-item').forEach(function(b) {
                b.classList.toggle('on', b.dataset.s === btn.dataset.curr);
            });
            menu.classList.add('open');
            menu.style.visibility = 'hidden';
            var r = btn.getBoundingClientRect();
            var mw = menu.offsetWidth,
                mh = menu.offsetHeight;
            var left = Math.min(Math.max(8, r.right - mw), window.innerWidth - mw - 8);
            var top = r.bottom + 6;
            if (top + mh > window.innerHeight - 8) top = r.top - mh - 6;
            if (top < 8) top = 8;
            menu.style.left = left + 'px';
            menu.style.top = top + 'px';
            menu.style.visibility = 'visible';
        }

        function pickStatus(s) {
            var id = window.__stId,
                btn = window.__stBtn;
            closeStatusMenu();
            if (!id || !btn) return;
            if (btn.dataset.curr === s) return;

            var card = document.querySelector('#ordersGrid .ord-card[data-id="' + id + '"]');
            var fd = new FormData();
            fd.append('_method', 'PATCH');
            fd.append('_token', ordCsrf);
            fd.append('status', s);
            btn.disabled = true;

            fetch(btn.dataset.url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': ordCsrf,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: fd
                })
                .then(function(res) {
                    return res.json().catch(function() {
                        return {};
                    }).then(function(data) {
                        if (res.status === 419) throw new Error('Session expired — please refresh the page.');
                        if (!res.ok) throw new Error(data.message || 'Request failed (' + res.status + ')');
                        return data;
                    });
                })
                .then(function(data) {
                    btn.dataset.curr = s;
                    btn.className = 'st-btn tint-' + s;
                    btn.querySelector('.st-lbl').textContent = s.charAt(0).toUpperCase() + s.slice(1);

                    /* 🚚 THE MAGIC — swap st-* class → car DRIVES to its new spot (CSS transition) */
                    if (card) {
                        var oldSt = Array.prototype.find.call(card.classList, function(c) {
                            return c.indexOf('st-') === 0;
                        });
                        if (oldSt) card.classList.remove(oldSt);
                        card.classList.add('st-' + s);
                        card.dataset.status = s;
                        card.classList.remove('flash');
                        void card.offsetWidth;
                        card.classList.add('flash');
                    }
                    refreshStats();
                    applyOrderFilter();
                    _toast('success', (data && data.message) || 'Status updated to ' + s + '.');
                })
                .catch(function(err) {
                    _toast('error', err.message || 'Something went wrong.');
                })
                .finally(function() {
                    btn.disabled = false;
                });
        }

        /* ================= LIVE STATS ================= */
        function refreshStats() {
            var grid = document.getElementById('ordersGrid');
            if (!grid) return;
            var cards = grid.querySelectorAll('.ord-card');
            var p = 0,
                d = 0,
                rev = 0;
            cards.forEach(function(c) {
                if (c.dataset.status === 'pending') p++;
                if (c.dataset.status === 'delivered') d++;
                if (c.dataset.paid === '1') rev += parseFloat(c.dataset.total) || 0;
            });
            document.getElementById('statTotal').textContent = cards.length;
            document.getElementById('statPending').textContent = p;
            document.getElementById('statDelivered').textContent = d;
            document.getElementById('statRevenue').textContent = '£' + rev.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        applyOrderFilter();

        /* ================= VIEW MODAL (AJAX) ================= */
        function openOrderView(btn) {
            var modal = document.getElementById('viewModal');
            var loading = document.getElementById('vwLoading');
            var content = document.getElementById('vwContent');
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
            loading.style.display = 'block';
            content.style.display = 'none';

            fetch(btn.dataset.url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(function(r) {
                    return r.json().catch(function() {
                        return {};
                    }).then(function(d) {
                        if (!r.ok) throw new Error(d.message || 'Failed to load order');
                        return d;
                    });
                })
                .then(function(d) {
                    renderOrderView(d.order);
                })
                .catch(function(err) {
                    closeOrderView();
                    _toast('error', err.message || 'Could not load order.');
                });
        }

        function closeOrderView() {
            document.getElementById('viewModal').classList.remove('show');
            document.body.style.overflow = '';
        }

        function renderOrderView(o) {
            var payPaid = (o.payStatus || '').toLowerCase() === 'paid';
            var itemsHTML = (o.items || []).map(function(it) {
                var thumb = it.img ? '<img src="' + _esc(it.img) + '" alt="">' : _esc(it.emoji || '🧴');
                return '<div class="vw-item">' +
                    '<span class="im">' + thumb + '</span>' +
                    '<span class="nm"><b>' + _esc(it.name) + '</b><span>' + (it.qty || 0) + ' × £' + Number(it.price || 0).toFixed(2) + '</span></span>' +
                    '<span class="ln">£' + Number(it.line || 0).toFixed(2) + '</span>' +
                    '</div>';
            }).join('');

            var html =
                '<div class="vw-head">' +
                '<div class="vw-num">' + _esc(o.number) +
                '<span class="pill tint-' + _esc(o.status) + '" style="font-size:11px"><span class="st-dot"></span>' + _esc(o.status ? o.status.charAt(0).toUpperCase() + o.status.slice(1) : '—') + '</span>' +
                '<span class="pill ' + (payPaid ? 'paid' : 'unpaid') + '"><i class="ri-' + (payPaid ? 'checkbox-circle' : 'error-warning') + '-line"></i>' + (payPaid ? 'Paid' : 'Unpaid') + '</span>' +
                '</div>' +
                '<div class="vw-date"><i class="ri-time-line"></i>' + _esc(o.date) + '</div>' +
                '</div>' +
                '<div class="vw-body">' +
                '<div class="vw-grid2">' +
                '<div class="vw-info"><b>' + _esc(o.name) + '</b><span><i class="ri-mail-line"></i> ' + _esc(o.email) + '<br><i class="ri-phone-line"></i> ' + _esc(o.phone) + '</span></div>' +
                '<div class="vw-info"><b><i class="ri-map-pin-2-line"></i> Delivery Address</b><span>' + _esc(o.address) + '<br>' + _esc(o.city) + ', ' + _esc(o.postal) + '<br>' + _esc(o.country) +
                ((o.notes && o.notes !== '—') ? '<br><i class="ri-sticky-note-line"></i> ' + _esc(o.notes) : '') + '</span></div>' +
                '</div>' +
                '<div><p class="vw-sec-t"><i class="ri-shopping-bag-3-line"></i> Items (' + (o.items || []).length + ')</p><div style="margin-top:6px">' + itemsHTML + '</div></div>' +
                '<div class="vw-tot">' +
                '<div class="vw-trow"><span>Subtotal</span><span>£' + Number(o.subtotal || 0).toFixed(2) + '</span></div>' +
                (Number(o.discount || 0) > 0 ? '<div class="vw-trow"><span>Discount' + (o.couponCode ? ' (' + _esc(o.couponCode) + ')' : '') + '</span><span>−£' + Number(o.discount).toFixed(2) + '</span></div>' : '') +
                '<div class="vw-trow"><span>Shipping</span><span>' + (Number(o.shipping || 0) > 0 ? '£' + Number(o.shipping).toFixed(2) : 'FREE') + '</span></div>' +
                (o.taxBreakdown || []).map(function (tax) {
                    return '<div class="vw-trow"><span>' + _esc(tax.name) + ' (' + Number(tax.percentage).toString() + '%)</span><span>£' + Number(tax.amount || 0).toFixed(2) + '</span></div>';
                }).join('') +
                '<div class="vw-trow grand"><span>Total</span><span>£' + Number(o.total || 0).toFixed(2) + '</span></div>' +
                '</div>' +
                '<div class="vw-pay">' +
                '<span class="m-chip"><i class="ri-bank-card-line"></i>' + _esc((o.method || '—').toUpperCase()) + '</span>' +
                (o.tx && o.tx !== '—' ? '<span class="m-chip"><i class="ri-hashtag"></i>' + _esc(o.tx) + '</span>' : '') +
                '</div>' +
                '<div class="vw-eta">' +
                '<span class="eta-chip"><i class="ri-truck-line"></i> Fast delivery: ' + _esc(o.eta5) + '</span>' +
                '<span class="eta-chip"><i class="ri-calendar-check-line"></i> Standard: ' + _esc(o.eta7) + '</span>' +
                '</div>' +
                '</div>';

            document.getElementById('vwContent').innerHTML = html;
            document.getElementById('vwLoading').style.display = 'none';
            document.getElementById('vwContent').style.display = 'block';
        }

        /* 🛡️ document-level listeners bind ONCE */
        if (!window.__ordV7Bound) {
            window.__ordV7Bound = 1;
            document.addEventListener('click', function(e) {
                var menu = document.getElementById('statusMenu');
                if (menu && menu.classList.contains('open') && !e.target.closest('.st-menu') && !e.target.closest('.st-btn')) closeStatusMenu();
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeStatusMenu();
                    closeOrderView();
                }
            });
            window.addEventListener('resize', closeStatusMenu);
        }

        /* reveal safety net */
        if (!window.__ordV7Reveal) {
            window.__ordV7Reveal = 1;
            setTimeout(function() {
                document.querySelectorAll('.fx').forEach(function(el) {
                    el.style.opacity = 1;
                });
            }, 1200);
        }
    </script>
</body>

</html>