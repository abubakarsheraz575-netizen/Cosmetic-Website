{{-- ==========================================================
     ADMIN · ALL PRODUCTS — v7 DATA TABLE
     search · sort · filter · per-page · pagination · 780px spacious modal
========================================================== --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0e5a75">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>All Products — NiceDash Admin</title>
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
        body.nb-lock{overflow:hidden}

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

        .btn {
            padding: 11px 20px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 13px;
            cursor: pointer;
            border: none;
            transition: .25s;
            display: inline-flex;
            align-items: center;
            gap: 8px
        }

        .btn-lime {
            background: linear-gradient(135deg, var(--lime), #3fb0d8);
            color: #08394b;
            box-shadow: 0 8px 20px rgba(62, 197, 224, .35)
        }

        .btn-lime:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 26px rgba(62, 197, 224, .45)
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

        .i-amber {
            background: #fdf4e3;
            color: #d97706
        }

        .i-red {
            background: #feecec;
            color: #dc2626
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

        body.dark .i-red {
            background: rgba(248, 113, 113, .12);
            color: #f87171
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
            width: 300px;
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

        .t-sel {
            position: relative
        }

        .t-sel select {
            appearance: none;
            border: 1px solid var(--border);
            background: var(--bg);
            color: var(--text);
            font: 700 12.5px 'Plus Jakarta Sans', sans-serif;
            padding: 11px 38px 11px 16px;
            border-radius: 12px;
            cursor: pointer;
            outline: none;
            transition: .25s
        }

        .t-sel select:focus {
            border-color: var(--lime);
            box-shadow: 0 0 0 4px rgba(62, 197, 224, .15)
        }

        .t-sel i {
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            color: var(--muted);
            pointer-events: none
        }

        /* ===== TABLE ===== */
        .table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 980px
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

        .pcell {
            display: flex;
            align-items: center;
            gap: 13px;
            min-width: 230px
        }

        .p-emoji {
            width: 46px;
            height: 46px;
            min-width: 46px;
            border-radius: 13px;
            display: grid;
            place-items: center;
            font-size: 23px;
            background: linear-gradient(135deg, #e0f5fb, #c2e9f5);
            overflow: hidden;
            transition: .3s
        }

        .p-emoji img {
            width: 100%;
            height: 100%;
            object-fit: cover
        }
        .p-emoji i{font-size:23px;color:var(--forest)}
        body.dark .p-emoji i{color:var(--lime)}
        .cat-chip i{margin-right:6px;color:var(--forest)}
        body.dark .cat-chip i{color:var(--lime)}

        @media(hover:hover) {
            tbody tr:hover .p-emoji {
                transform: scale(1.08) rotate(-6deg)
            }
        }

        .p-info strong {
            display: block;
            font-size: 13.5px
        }

        .p-info span {
            font-size: 11.5px;
            color: var(--muted)
        }

        .cat-chip {
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
            white-space: nowrap
        }

        .price-final {
            font-weight: 800;
            color: var(--forest);
            font-size: 14px;
            white-space: nowrap
        }

        body.dark .price-final {
            color: var(--lime)
        }

        .price-orig {
            font-size: 12px;
            color: var(--muted);
            text-decoration: line-through;
            margin-left: 7px
        }

        .off-badge {
            display: inline-flex;
            margin-left: 8px;
            background: linear-gradient(135deg, #ff6b81, #ef4444);
            color: #fff;
            font-size: 10px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 20px;
            vertical-align: middle
        }

        .stock-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11.5px;
            font-weight: 800;
            padding: 5px 12px;
            border-radius: 20px;
            white-space: nowrap
        }

        .stock-ok {
            background: #dcfce7;
            color: #16a34a
        }

        .stock-low {
            background: #fef3c7;
            color: #b45309
        }

        .stock-out {
            background: #fee2e2;
            color: #dc2626
        }

        body.dark .stock-ok {
            background: rgba(74, 222, 128, .12);
            color: #4ade80
        }

        body.dark .stock-low {
            background: rgba(251, 191, 36, .12);
            color: #fbbf24
        }

        body.dark .stock-out {
            background: rgba(248, 113, 113, .12);
            color: #f87171
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 13px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 700
        }

        .pill::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
            box-shadow: 0 0 7px currentColor
        }

        .pill.active {
            background: #dcfce7;
            color: #16a34a
        }

        .pill.hidden {
            background: #fef3c7;
            color: #b45309
        }

        body.dark .pill.active {
            background: rgba(74, 222, 128, .12);
            color: #4ade80
        }

        body.dark .pill.hidden {
            background: rgba(251, 191, 36, .12);
            color: #fbbf24
        }

        .actions {
            display: flex;
            gap: 8px;
            justify-content: flex-end
        }

        .act {
            width: 36px;
            height: 36px;
            border-radius: 11px;
            display: grid;
            place-items: center;
            font-size: 16px;
            cursor: pointer;
            border: 1px solid transparent;
            transition: .25s
        }

        .act-view {
            background: rgba(14, 90, 117, .07);
            color: var(--forest);
            border-color: rgba(14, 90, 117, .2)
        }

        .act-view:hover {
            background: var(--forest);
            color: var(--lime);
            transform: translateY(-2px)
        }

        .act-edit {
            background: rgba(37, 99, 235, .07);
            color: #2563eb;
            border-color: rgba(37, 99, 235, .2)
        }

        .act-edit:hover {
            background: #2563eb;
            color: #fff;
            transform: translateY(-2px)
        }

        .act-del {
            background: rgba(239, 68, 68, .07);
            color: #ef4444;
            border-color: rgba(239, 68, 68, .2)
        }

        .act-del:hover {
            background: #ef4444;
            color: #fff;
            transform: translateY(-2px)
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

        /* ═══════════ SPACIOUS 780px EDIT MODAL ═══════════ */
        .nb-modal {
            position: fixed;
            inset: 0;
            z-index: 100;
            display: grid;
            place-items: center;
            background: rgba(15, 23, 42, .62);
            backdrop-filter:blur(5px);
            opacity: 0;
            visibility: hidden;
            transition: .3s;
            padding: 20px
        }

        .nb-modal.show {
            opacity: 1;
            visibility: visible
        }

        .nb-modal-box {
            width: min(780px, 100%);
            max-height: 90vh;
            overflow: hidden;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, .35);
            transform: translateY(16px) scale(.95);
            transition: .35s cubic-bezier(.22, 1, .36, 1);
            position: relative;
            display:flex;
            flex-direction:column
        }

        .nb-modal.show .nb-modal-box {
            transform: none
        }

        .nb-close {
            position: absolute;
            top: 16px;
            right: 16px;
            z-index:2;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            border: none;
            background: #0c2e3c;
            color: #fff;
            font-size: 17px;
            cursor: pointer;
            display: grid;
            place-items: center;
            transition: .25s;
            box-shadow:0 6px 16px rgba(0,0,0,.25)
        }

        .nb-close:hover {
            background: #dc2626;
            transform: rotate(90deg)
        }

        .nb-modal-head {
            flex-shrink:0;
            padding:22px 72px 20px 26px;
            margin:0;
            background:linear-gradient(115deg,#0a3346 0%,#0e5a75 52%,#1a83ab 100%);
            position:relative;
            overflow:hidden
        }
        .nb-modal-head::before,.nb-modal-head::after{content:'';position:absolute;border-radius:50%;pointer-events:none}
        .nb-modal-head::before{right:-40px;top:-64px;width:160px;height:160px;background:rgba(255,255,255,.08)}
        .nb-modal-head::after{right:70px;bottom:-52px;width:90px;height:90px;background:rgba(255,255,255,.05)}
        .nb-modal-body{padding:26px 26px 24px;overflow-y:auto;overflow-x:hidden}
        .nb-modal-body::-webkit-scrollbar{width:6px}
        .nb-modal-body::-webkit-scrollbar-track{background:transparent}
        .nb-modal-body::-webkit-scrollbar-thumb{background:var(--border);border-radius:10px}
        .nb-modal-head h4,.nb-modal-head p{position:relative;z-index:1}
        .nb-modal-head p{display:flex;align-items:center;gap:7px}
        .nb-modal-head p i{color:var(--lime);font-size:13px}
        .nb-modal-head h4{color:#fff}
        .nb-modal-head p{color:rgba(255,255,255,.78)}

        .nb-modal-head h4 {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -.3px
        }

        .nb-modal-head p {font-size:12px;color:rgba(255,255,255,.78);margin-top:6px}

        .frow {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px
        }

        .fld {
            margin-bottom: 20px
        }

        .fld>label {
            display: flex;
            align-items: center;
            gap: 9px;
            font-size: 11.5px;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #2f98a1;
            margin-bottom: 9px
        }

        .lb-ic {
            width: 24px;
            height: 24px;
            min-width: 24px;
            border-radius: 8px;
            background: var(--lime-soft);
            color: var(--forest);
            display: grid;
            place-items: center;
            font-size: 12px
        }

        body.dark .lb-ic {
            color: var(--lime)
        }

        .fld input,
        .fld select,
        .fld textarea {
            width: 100%;
            min-height: 54px;
            border: 1.5px solid transparent;
            background: #f4f7f8;
            border-radius: 14px;
            padding: 14px 18px;
            font: 600 13.5px 'Plus Jakarta Sans', sans-serif;
            color: var(--text);
            outline: none;
            transition: .25s;
            resize: vertical
        }

        .fld select {
            cursor: pointer
        }

        .fld input::placeholder,
        .fld textarea::placeholder {
            color: #9fb0c0;
            font-weight: 500
        }

        body.dark .fld input::placeholder,
        body.dark .fld textarea::placeholder {
            color: #5c718c
        }

        .fld input:focus,
        .fld select:focus,
        .fld textarea:focus {
            border-color: var(--lime);
            background: var(--card);
            box-shadow: 0 0 0 4px rgba(62, 197, 224, .14)
        }
        body.dark .fld input,body.dark .fld select,body.dark .fld textarea{background:rgba(255,255,255,.05)}
        body.dark .fld>label{color:var(--lime)}

        .f-preview {
            margin: -8px 0 18px;
            font-size: 12.5px;
            color: var(--muted);
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 0 2px;
            flex-wrap: wrap
        }

        .f-preview b {
            color: var(--forest);
            font-size: 14px
        }

        body.dark .f-preview b {
            color: var(--lime)
        }

        .chip-row {
            display: flex;
            gap: 7px;
            flex-wrap: wrap;
            margin-top: 10px
        }

        .chip {
            padding: 8px 14px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: var(--bg);
            font-size: 11.5px;
            font-weight: 800;
            color: var(--muted);
            cursor: pointer;
            transition: .2s;
            font-family: inherit
        }

        .chip:hover {
            border-color: var(--lime);
            background: var(--lime-soft);
            color: var(--forest)
        }

        .prod-icon-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-top:4px}
        .prod-icon-pick{position:relative;min-width:0;min-height:95px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:7px;padding:12px 6px;border:1.5px solid transparent;border-radius:16px;background:#f4f7f8;color:var(--text);cursor:pointer;transition:.25s;box-shadow:0 3px 10px rgba(16,24,40,.05)}
        body.dark .prod-icon-pick{background:rgba(255,255,255,.05)}
        .prod-icon-pick .prod-icon{width:46px;height:46px;display:grid;place-items:center;border-radius:13px;background:var(--lime-soft);color:var(--forest)}
        body.dark .prod-icon-pick .prod-icon{color:var(--lime)}
        .prod-icon-pick .prod-icon i{font-size:24px}
        .prod-icon-pick small{color:var(--muted);font-size:8.5px;font-weight:800;letter-spacing:.5px;text-align:center;text-transform:uppercase}
        .prod-icon-pick:hover,.prod-icon-pick.sel{border-color:var(--lime);background:linear-gradient(160deg,var(--lime-soft),var(--card));box-shadow:0 0 0 4px rgba(62,197,224,.16),0 12px 26px rgba(62,197,224,.2)}
        .prod-icon-pick.sel::after{content:'✓';position:absolute;top:6px;right:6px;width:18px;height:18px;border-radius:50%;background:linear-gradient(135deg,var(--lime),var(--forest-2));color:#fff;font-size:11px;font-weight:800;display:grid;place-items:center}

        .switch-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 16px 18px;
            border-radius: 14px;
            background: var(--bg);
            border: 1px solid var(--border);
            margin-bottom: 24px
        }

        .switch-row b {
            font-size: 13.5px
        }

        .switch-row span {
            display: block;
            font-size: 11.5px;
            color: var(--muted);
            font-weight: 600;
            margin-top: 2px
        }

        .switch {
            position: relative;
            width: 50px;
            height: 28px;
            flex-shrink: 0
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0
        }

        .slider {
            position: absolute;
            inset: 0;
            border-radius: 30px;
            background: #cbd5e1;
            cursor: pointer;
            transition: .3s
        }

        .slider::before {
            content: '';
            position: absolute;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            left: 3px;
            top: 3px;
            background: #fff;
            transition: .3s cubic-bezier(.34, 1.56, .64, 1);
            box-shadow: 0 2px 6px rgba(0, 0, 0, .25)
        }

        .switch input:checked+.slider {
            background: linear-gradient(135deg, var(--forest-2), var(--lime))
        }

        .switch input:checked+.slider::before {
            transform: translateX(22px)
        }

        .nb-modal-actions {
            display: flex;
            gap: 12px
        }

        .nb-btn {
            padding: 14px 22px;
            min-height: 52px;
            border-radius: 13px;
            font-weight: 800;
            font-size: 13.5px;
            cursor: pointer;
            border: none;
            transition: .25s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px
        }

        .nb-btn.ghost {
            background: var(--bg);
            color: var(--text);
            border: 1px solid var(--border);
            flex: 1
        }

        .nb-btn.ghost:hover {
            border-color: var(--muted)
        }

        .nb-btn.save {
            background: linear-gradient(135deg, var(--forest), var(--forest-2));
            color: #fff;
            flex: 2;
            box-shadow: 0 8px 20px rgba(14, 90, 117, .35)
        }

        .nb-btn.save:hover {
            transform: translateY(-2px)
        }

        .nb-btn.save:disabled {
            opacity: .6;
            cursor: wait;
            transform: none
        }

        /* ===== SWAL THEME ===== */
        .swal-popup {
            border-radius: 24px !important;
            background: #111c2e !important;
            color: #e7edf5 !important;
            border: 1px solid rgba(62, 197, 224, .18);
            padding: 30px 26px !important
        }

        .swal-title {
            color: #fff !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-size: 20px !important
        }

        .swal-html-container {
            color: rgba(231, 237, 245, .7) !important;
            font-size: 13.5px !important
        }

        .del-ic {
            width: 74px;
            height: 74px;
            margin: 0 auto 18px;
            border-radius: 24px;
            background: rgba(239, 68, 68, .12);
            border: 1px solid rgba(239, 68, 68, .3);
            display: grid;
            place-items: center;
            font-size: 34px;
            color: #f87171;
            animation: delShake .5s ease
        }

        @keyframes delShake {

            0%,
            100% {
                transform: rotate(0)
            }

            25% {
                transform: rotate(-8deg)
            }

            75% {
                transform: rotate(8deg)
            }
        }

        .swal-confirm {
            background: linear-gradient(135deg, #ef4444, #dc2626) !important;
            color: #fff !important;
            font-weight: 800;
            font-family: inherit;
            padding: 13px 26px !important;
            border-radius: 13px !important;
            border: none;
            cursor: pointer;
            font-size: 13.5px
        }

        .swal-cancel {
            background: transparent !important;
            color: rgba(231, 237, 245, .7) !important;
            border: 1px solid rgba(255, 255, 255, .18) !important;
            font-weight: 700;
            font-family: inherit;
            padding: 13px 26px !important;
            border-radius: 13px !important;
            cursor: pointer;
            font-size: 13.5px
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

            .t-sel {
                flex: 1
            }

            .t-sel select {
                width: 100%
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

            .frow {
                grid-template-columns: 1fr
            }

            .fld input,
            .fld select,
            .fld textarea {
                font-size: 16px !important
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

            .page-head .btn {
                width: 100%;
                justify-content: center
            }

            .nb-modal {
                padding: 10px
            }

            .nb-modal-body {
                padding: 20px 18px
            }

            .prod-icon-grid {
                grid-template-columns: repeat(2,minmax(0,1fr))
            }

            .nb-modal-actions {
                flex-direction: column-reverse
            }

            .actions {
                gap: 5px
            }

            .act {
                width: 32px;
                height: 32px;
                font-size: 14px
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
    @php
    $productIconChoices = config('product_icons.choices');
    $productIconClasses = array_column($productIconChoices, 'class', 'value');
    $productIconFallback = config('product_icons.fallback');
    @endphp
    <div class="layout" id="layout">
        @include('admin.include.sidebar')
        <div class="main">
            @include('admin.include.navbar')
            <main class="content">

                <div class="page-head fx">
                    <div>
                        <h2><span class="ph-ic"><i class="ri-shopping-bag-4-line"></i></span>All Products</h2>
                        <p>Full catalogue — prices, discounts, stock and more.</p>
                    </div>
                    <a href="{{ route('admin.products.create') }}" class="btn btn-lime"><i class="ri-add-line"></i>Add New Product</a>
                </div>

                <section class="r-stats">
                    <div class="card stat fx d1">
                        <div class="stat-top">
                            <div class="icon i-green"><i class="ri-shopping-bag-4-line"></i></div>
                        </div>
                        <div class="big sm" id="statTotal">{{ $stats['total'] }}</div>
                        <p class="muted small">Total products</p>
                    </div>
                    <div class="card stat fx d2">
                        <div class="stat-top">
                            <div class="icon i-blue"><i class="ri-eye-line"></i></div>
                        </div>
                        <div class="big sm" id="statActive">{{ $stats['active'] }}</div>
                        <p class="muted small">Published</p>
                    </div>
                    <div class="card stat fx d3">
                        <div class="stat-top">
                            <div class="icon i-amber"><i class="ri-percent-line"></i></div>
                        </div>
                        <div class="big sm" id="statDisc">{{ $stats['discount'] }}</div>
                        <p class="muted small">On discount</p>
                    </div>
                    <div class="card stat fx d3">
                        <div class="stat-top">
                            <div class="icon i-red"><i class="ri-error-warning-line"></i></div>
                        </div>
                        <div class="big sm" id="statOut">{{ $stats['stockout'] }}</div>
                        <p class="muted small">Out of stock</p>
                    </div>
                </section>

                <section class="card table-card fx d3">
                    <div class="toolbar">
                        <div class="t-search">
                            <i class="ri-search-line"></i>
                            <input type="text" id="searchInput" oninput="goPage(1)" placeholder="Search product, category, size...">
                        </div>
                        <div class="t-sel">
                            <select id="filterSel" onchange="goPage(1)">
                                <option value="all">All products</option>
                                <option value="active">Published only</option>
                                <option value="discount">On discount</option>
                                <option value="out">Out of stock</option>
                            </select>
                            <i class="ri-arrow-down-s-line"></i>
                        </div>
                        <div class="t-sel" style="margin-left:auto">
                            <select id="perSel" onchange="goPage(1)">
                                <option value="10">10 rows</option>
                                <option value="25">25 rows</option>
                                <option value="50">50 rows</option>
                                <option value="999999">All rows</option>
                            </select>
                            <i class="ri-arrow-down-s-line"></i>
                        </div>
                    </div>

                    <div class="tbl-hint"><i class="ri-drag-move-2-line"></i> Swipe the table sideways to see all columns</div>

                    <div class="table-wrap">
                        <table data-server-pagination="true">
                            <thead>
                                <tr>
                                    <th class="sortable" data-k="name" onclick="sortBy(this)">Product<span class="s-ar">▼</span></th>
                                    <th class="sortable" data-k="cat" onclick="sortBy(this)">Category<span class="s-ar">▼</span></th>
                                    <th class="sortable" data-k="size" onclick="sortBy(this)">Size<span class="s-ar">▼</span></th>
                                    <th class="sortable" data-k="final" onclick="sortBy(this)">Price<span class="s-ar">▼</span></th>
                                    <th class="sortable" data-k="stock" onclick="sortBy(this)">Stock<span class="s-ar">▼</span></th>
                                    <th class="sortable" data-k="active" onclick="sortBy(this)">Status<span class="s-ar">▼</span></th>
                                    <th style="text-align:right">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="prodBody">
                                @foreach($products as $p)
                                @php
                                $final = $p->final_price;
                                $imgUrls = $p->images->map(fn ($i) => asset('storage/' . $i->path))->values();
                                @endphp
                                <tr data-id="{{ $p->id }}"
                                    data-name="{{ strtolower($p->name) }}"
                                    data-cat="{{ strtolower(optional($p->category)->name ?? '') }}"
                                    data-size="{{ strtolower(optional($p->size)->name ?? '') }}"
                                    data-final="{{ number_format($final, 2, '.', '') }}"
                                    data-hay="{{ strtolower($p->name.' '.optional($p->category)->name.' '.optional($p->size)->name) }}"
                                    data-active="{{ $p->is_active ? 1 : 0 }}"
                                    data-disc="{{ $p->discount }}"
                                    data-stock="{{ $p->stock }}"
                                    data-has-sku-variants="{{ $p->skuVariants->isNotEmpty() ? 1 : 0 }}"
                                    data-name-full="{{ $p->name }}"
                                    data-emoji="{{ $p->emoji }}"
                                    data-slug="{{ $p->slug }}"
                                    data-cat-id="{{ $p->category_id }}"
                                    data-size-id="{{ $p->size_id }}"
                                    data-brand-id="{{ $p->brand_id }}"
                                    data-cat-icon="{{ optional($p->category)->icon }}"
                                    data-cat-name="{{ optional($p->category)->name ?? '' }}"
                                    data-size-name="{{ optional($p->size)->name ?? '' }}"
                                    data-price="{{ $p->price }}"
                                    data-discount="{{ $p->discount }}"
                                    data-discount-type="{{ $p->discount_type }}"
                                    data-desc="{{ $p->description }}"
                                    data-ts="{{ $p->created_at->timestamp }}"
                                    data-created="{{ $p->created_at->format('M d, Y') }}"
                                    data-images='@json($imgUrls)'>
                                    <td>
                                        <div class="pcell">
                                            <div class="p-emoji">
                                                @if($p->images->first())
                                                <img src="{{ asset('storage/' . $p->images->first()->path) }}" alt="">
                                                @else
                                                <i class="{{ $productIconClasses[$p->emoji] ?? $productIconFallback }}" aria-hidden="true"></i>
                                                @endif
                                            </div>
                                            <div class="p-info">
                                                <strong>{{ $p->name }}</strong>
                                                <span>#{{ $p->slug }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    @php $productCategoryIcon = $productIconClasses[optional($p->category)->icon] ?? 'ri-price-tag-3-line'; @endphp
                                    <td><span class="cat-chip"><i class="{{ $productCategoryIcon }}" aria-hidden="true"></i>{{ optional($p->category)->name ?? '—' }}</span></td>
                                    <td>{{ optional($p->size)->name ?? '—' }}</td>
                                    <td>
                                        <span class="price-final">£{{ number_format($final, 2) }}</span>
                                        @if($p->discount > 0)
                                        <span class="price-orig">£{{ number_format($p->price, 2) }}</span>
                                        <span class="off-badge">-{{ rtrim(rtrim(number_format($p->discount, 1), '0'), '.') }}%</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($p->stock == 0)
                                        <span class="stock-badge stock-out"><i class="ri-close-circle-line"></i>Out</span>
                                        @elseif($p->stock <= 5)
                                            <span class="stock-badge stock-low"><i class="ri-error-warning-line"></i>{{ $p->stock }} left</span>
                                            @else
                                            <span class="stock-badge stock-ok"><i class="ri-checkbox-circle-line"></i>{{ $p->stock }}</span>
                                            @endif
                                    </td>
                                    <td class="pill-cell">
                                        <span class="pill {{ $p->is_active ? 'active' : 'hidden' }}">{{ $p->is_active ? 'Published' : 'Hidden' }}</span>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <a href="{{ route('admin.products.show', $p) }}" class="act act-view" title="View"><i class="ri-eye-line"></i></a>
                                            <a href="{{ route('admin.products.edit', $p) }}" class="act act-edit" title="Edit"><i class="ri-pencil-line"></i></a>
                                            <button type="button" class="act act-del" onclick="deleteProduct(this)" title="Delete"><i class="ri-delete-bin-6-line"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="t-foot">
                        <p class="info" id="tblInfo"></p>
                        <div class="pgn" id="pgn"></div>
                    </div>
                </section>

                <div class="card empty fx" id="emptyState" style="{{ $products->isEmpty() ? '' : 'display:none' }}">
                    <div class="e-ic"><i class="ri-shopping-bag-3-line"></i></div>
                    <h4>No products yet</h4>
                    <p>Click <b>Add New Product</b> to create your first item.</p>
                </div>

            </main>
        </div>
    </div>

    {{-- ═══ EDIT PRODUCT MODAL ═══ --}}
    <div class="nb-modal" id="prodModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle" onclick="if(event.target===this)closeProductModal()">
        <div class="nb-modal-box" style="width : 50%">
            <div class="nb-modal-head">
                <button type="button" class="nb-close" onclick="closeProductModal()" aria-label="Close"><i class="ri-close-line"></i></button>
                <h4 id="modalTitle">Edit Product</h4>
                <p><i class="ri-information-line"></i><span id="modalSub">Update product details</span></p>
            </div>
            <div class="nb-modal-body">
              <form id="prodForm" onsubmit="return saveProduct(event)">
                <div class="fld">
                    <label><span class="lb-ic"><i class="ri-shopping-bag-3-line"></i></span><span>Product Name *</span></label>
                    <input type="text" name="name" id="eName" maxlength="150" placeholder="e.g. Rose Petal Shower Gel" required>
                </div>
                <div class="fld">
                    <label><span class="lb-ic"><i class="ri-price-tag-3-line"></i></span><span>Brand</span></label>
                    <select name="brand_id" id="eBrand"><option value="">No brand</option>@foreach($brands as $brand)<option value="{{ $brand->id }}">{{ $brand->name }}</option>@endforeach</select>
                </div>
                <div class="frow">
                    <div class="fld">
                        <label><span class="lb-ic"><i class="ri-apps-2-line"></i></span><span>Category *</span></label>
                        <div class="qt-select-row">
                            <select name="category_id" id="eCat" data-quick-category required>
                                @foreach($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="qt-add-btn" onclick="quickTaxonomyOpen('category', 'eCat')" title="Add category" aria-label="Add category"><i class="ri-add-line"></i></button>
                        </div>
                    </div>
                    <div class="fld">
                        <label><span class="lb-ic"><i class="ri-flask-line"></i></span><span>Size / Volume</span></label>
                        <div class="qt-select-row">
                            <select name="size_id" id="eSize" data-quick-size>
                                <option value="">Not specified</option>
                                @foreach($sizes as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} ({{ number_format($s->ml) }} ml)</option>
                                @endforeach
                            </select>
                            <button type="button" class="qt-add-btn" onclick="quickTaxonomyOpen('size', 'eSize')" title="Add size" aria-label="Add size"><i class="ri-add-line"></i></button>
                        </div>
                    </div>
                </div>
                <div class="frow">
                    <div class="fld">
                        <label><span class="lb-ic"><i class="ri-wallet-3-line"></i></span><span>Price (£) *</span></label>
                        <input type="number" name="price" id="ePrice" step="0.01" min="0" placeholder="0.00" required oninput="updFinal()">
                    </div>
                    <div class="fld">
                        <label><span class="lb-ic"><i class="ri-percent-line"></i></span><span>Discount type</span></label>
                        <select name="discount_type" id="eDiscountType" onchange="updateEditDiscountInput();updFinal()"><option value="percent">Percentage (%)</option><option value="fixed">Fixed amount (£)</option></select>
                        <label for="eDiscount" id="eDiscountLabel" style="margin-top:8px">Discount (%)</label>
                        <input type="number" name="discount" id="eDiscount" step="0.01" min="0" max="100" value="0" oninput="updFinal()">
                        <div class="chip-row">
                            <button type="button" class="chip" onclick="setEdisc(5)">5%</button>
                            <button type="button" class="chip" onclick="setEdisc(10)">10%</button>
                            <button type="button" class="chip" onclick="setEdisc(20)">20%</button>
                            <button type="button" class="chip" onclick="setEdisc(50)">50%</button>
                            <button type="button" class="chip" onclick="setEdisc(0)">Clear</button>
                        </div>
                    </div>
                </div>
                <p class="f-preview"><i class="ri-coupon-3-line"></i>Customer pays <b id="fPreviewVal">£0.00</b>&nbsp;·&nbsp;saves <b id="fSaveVal">£0.00</b></p>
                <div class="fld">
                    <label><span class="lb-ic"><i class="ri-archive-stack-line"></i></span><span>Stock *</span></label>
                    <input type="number" name="stock" id="eStock" min="0" placeholder="e.g. 25" required>
                    <small id="eStockHelp">Set the available stock for this product.</small>
                </div>
                <div class="fld">
                    <label><span class="lb-ic"><i class="ri-sparkling-2-line"></i></span><span>Fallback Icon — Pick One</span></label>
                    <input type="hidden" name="emoji" id="eEmoji">
                    <div class="prod-icon-grid" id="prodIconGrid">
                        @foreach($productIconChoices as $choice)
                        <button type="button" class="prod-icon-pick" data-value="{{ $choice['value'] }}" onclick="pickProdIcon(this)">
                            <span class="prod-icon"><i class="{{ $choice['class'] }}" aria-hidden="true"></i></span>
                            <small>{{ $choice['label'] }}</small>
                        </button>
                        @endforeach
                    </div>
                </div>
                <div class="fld">
                    <label><span class="lb-ic"><i class="ri-file-list-3-line"></i></span><span>Description</span></label>
                    <textarea name="description" id="eDesc" rows="3" maxlength="1000" placeholder="Short product description..."></textarea>
                </div>
                <div class="switch-row">
                    <div><b>Published</b><span>Visible on the store</span></div>
                    <label class="switch"><input type="checkbox" name="is_active" id="eActive" checked><span class="slider"></span></label>
                </div>
                <div class="nb-modal-actions">
                    <button type="button" class="nb-btn ghost" onclick="closeProductModal()"><i class="ri-close-line"></i>Cancel</button>
                    <button type="submit" class="nb-btn save" id="saveBtn"><i class="ri-save-3-line"></i>Update Product</button>
                </div>
              </form>
            </div>
        </div>
    </div>

    @include('admin.include.quick-taxonomy-modals')

    <script>
        /* 🛡️ SPA-SAFE URLs — Blade-rendered INTO script (one-line) */
        var prodCsrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
        var prodUpdTpl = "{{ route('admin.products.update', '__ID__') }}";
        var prodDelTpl = "{{ route('admin.products.destroy', '__ID__') }}";
        var prodShowTpl = "{{ route('admin.products.show', '__ID__') }}";
        var prodEditTpl = "{{ route('admin.products.edit', '__ID__') }}";
        var storageBase = "{{ asset('storage') }}/";
        var prodEditId = null;
        var prodIconClasses = @json($productIconClasses);
        var prodIconFallback = @json($productIconFallback);

        function prodUpdUrl(id) {
            return prodUpdTpl.replace('__ID__', id)
        }

        function prodDelUrl(id) {
            return prodDelTpl.replace('__ID__', id)
        }

        function prodShowUrl(id) {
            return prodShowTpl.replace('__ID__', id)
        }

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
                })
            } else {
                alert(msg)
            }
        }

        function _esc(s) {
            return (s === null || s === undefined ? '' : String(s)).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;')
        }

        function productIconHtml(value, fallback) {
            var iconClass = Object.prototype.hasOwnProperty.call(prodIconClasses, value) ?
                prodIconClasses[value] : (fallback || prodIconFallback);
            return '<i class="' + iconClass + '" aria-hidden="true"></i>';
        }

        /* ═══ DATA TABLE ENGINE ═══ */
        var tbl = {
            q: '',
            filter: 'all',
            sortK: 'ts',
            sortD: -1,
            page: 1,
            per: 10
        };

        function allRows() {
            return Array.prototype.slice.call(document.querySelectorAll('#prodBody tr'))
        }

        function goPage(p) {
            tbl.page = p;
            applyTable()
        }

        function sortBy(th) {
            var k = th.dataset.k;
            if (tbl.sortK === k) tbl.sortD *= -1;
            else {
                tbl.sortK = k;
                tbl.sortD = 1
            }
            document.querySelectorAll('th.sortable').forEach(function(t) {
                t.classList.remove('sorted');
                t.querySelector('.s-ar').textContent = '▼';
                t.querySelector('.s-ar').style.opacity = '';
            });
            th.classList.add('sorted');
            th.querySelector('.s-ar').textContent = tbl.sortD === 1 ? '▲' : '▼';
            applyTable();
        }

        function applyTable() {
            tbl.q = (document.getElementById('searchInput').value || '').toLowerCase().trim();
            tbl.filter = document.getElementById('filterSel').value;
            tbl.per = parseInt(document.getElementById('perSel').value, 10);
            var rows = allRows().filter(function(tr) {
                var okQ = (tr.dataset.hay || '').indexOf(tbl.q) !== -1;
                var okF = true;
                if (tbl.filter === 'active') okF = tr.dataset.active === '1';
                if (tbl.filter === 'discount') okF = parseFloat(tr.dataset.disc) > 0;
                if (tbl.filter === 'out') okF = parseInt(tr.dataset.stock, 10) === 0;
                return okQ && okF;
            });
            rows.sort(function(a, b) {
                var va = a.dataset[tbl.sortK] || '',
                    vb = b.dataset[tbl.sortK] || '';
                if (tbl.sortK === 'ts' || tbl.sortK === 'active' || tbl.sortK === 'stock' || tbl.sortK === 'final') {
                    va = parseFloat(va) || 0;
                    vb = parseFloat(vb) || 0;
                    return (va - vb) * tbl.sortD
                }
                return va.localeCompare(vb) * tbl.sortD;
            });
            var total = rows.length,
                tp = Math.max(1, Math.ceil(total / tbl.per));
            if (tbl.page > tp) tbl.page = tp;
            var start = (tbl.page - 1) * tbl.per,
                end = tbl.per >= total ? total : start + tbl.per;
            rows.forEach(function(tr, i) {
                tr.style.display = (i >= start && i < end) ? '' : 'none'
            });
            document.getElementById('tblInfo').textContent = total ? 'Showing ' + (start + 1) + '–' + Math.min(end, total) + ' of ' + total + ' products' : 'No products found';
            renderPgn(tp);
            var gridEmpty = !allRows().length;
            var es = document.getElementById('emptyState');
            if (es) es.style.display = gridEmpty ? '' : 'none';
            document.querySelector('.table-card').style.display = gridEmpty ? 'none' : '';
        }

        function renderPgn(tp) {
            var box = document.getElementById('pgn');
            box.innerHTML = '';
            var mk = function(label, p, dis, on) {
                var b = document.createElement('button');
                b.type = 'button';
                b.className = 'pg-btn' + (on ? ' on' : '');
                b.innerHTML = label;
                if (dis) b.disabled = true;
                else b.setAttribute('onclick', 'goPage(' + p + ')');
                box.appendChild(b);
            };
            mk('<i class="ri-arrow-left-double-line"></i>', 1, tbl.page === 1);
            mk('<i class="ri-arrow-left-s-line"></i>', tbl.page - 1, tbl.page === 1);
            var s = Math.max(1, tbl.page - 2),
                e = Math.min(tp, tbl.page + 2);
            if (s > 1) {
                mk('1', 1);
                if (s > 2) box.insertAdjacentHTML('beforeend', '<span class="pg-dots">…</span>')
            }
            for (var i = s; i <= e; i++) mk(i, i, false, i === tbl.page);
            if (e < tp) {
                if (e < tp - 1) box.insertAdjacentHTML('beforeend', '<span class="pg-dots">…</span>');
                mk(tp, tp)
            }
            mk('<i class="ri-arrow-right-s-line"></i>', tbl.page + 1, tbl.page === tp);
            mk('<i class="ri-arrow-right-double-line"></i>', tp, tbl.page === tp);
        }

        /* ═══ EDIT MODAL ═══ */
        function editProduct(btn) {
            var tr = btn.closest('tr');
            prodEditId = tr.dataset.id;
            document.getElementById('eName').value = tr.dataset.nameFull;
            document.getElementById('eCat').value = tr.dataset.catId;
            document.getElementById('eSize').value = tr.dataset.sizeId || '';
            document.getElementById('eBrand').value = tr.dataset.brandId || '';
            document.getElementById('ePrice').value = tr.dataset.price;
            document.getElementById('eDiscountType').value = tr.dataset.discountType || 'percent';
            document.getElementById('eDiscount').value = parseFloat(tr.dataset.discount) || 0;
            updateEditDiscountInput();
            document.getElementById('eStock').value = tr.dataset.stock;
            document.getElementById('eStock').readOnly = tr.dataset.hasSkuVariants === '1';
            document.getElementById('eStockHelp').textContent = tr.dataset.hasSkuVariants === '1'
                ? 'This is the combined stock total. Edit per-option quantities in Ecommerce → Inventory.'
                : 'Set the available stock for this product.';
            document.getElementById('eEmoji').value = tr.dataset.emoji || '';
            document.getElementById('eDesc').value = tr.dataset.desc || '';
            document.getElementById('eActive').checked = tr.dataset.active === '1';
            document.getElementById('modalSub').textContent = 'Update "' + tr.dataset.nameFull + '"';
            updFinal();
            syncProductIconGrid();
            document.getElementById('prodModal').classList.add('show');
            document.body.classList.add('nb-lock');
        }

        function closeProductModal() {
            var modal = document.getElementById('prodModal');
            var form = document.getElementById('prodForm');
            if (modal) modal.classList.remove('show');
            if (form) form.reset();
            var quickModal = document.getElementById('quickTaxonomyModal');
            if (!quickModal || !quickModal.classList.contains('show')) document.body.classList.remove('nb-lock');
            prodEditId = null
        }

        function setEdisc(v) {
            document.getElementById('eDiscount').value = v;
            updFinal()
        }

        function updateEditDiscountInput() {
            var fixed = document.getElementById('eDiscountType').value === 'fixed';
            var input = document.getElementById('eDiscount');
            input.max = fixed ? '' : '100';
            input.step = fixed ? '0.01' : '1';
            document.getElementById('eDiscountLabel').textContent = fixed ? 'Discount amount (£)' : 'Discount (%)';
        }

        function updFinal() {
            var pr = parseFloat(document.getElementById('ePrice').value) || 0;
            var di = parseFloat(document.getElementById('eDiscount').value) || 0;
            var f = Math.max(0, pr - (document.getElementById('eDiscountType').value === 'fixed' ? di : pr * di / 100));
            var a = document.getElementById('fPreviewVal'),
                b = document.getElementById('fSaveVal');
            if (a) a.textContent = '£' + f.toFixed(2);
            if (b) b.textContent = '£' + (pr - f).toFixed(2);
        }

        function pickProdIcon(button) {
            document.getElementById('eEmoji').value = button.dataset.value;
            syncProductIconGrid()
        }

        function syncProductIconGrid() {
            var v = (document.getElementById('eEmoji').value || '').trim();
            document.querySelectorAll('#prodIconGrid .prod-icon-pick').forEach(function(b) {
                b.classList.toggle('sel', b.dataset.value === v)
            });
        }
        if (!window.__prodEsc) {
            window.__prodEsc = 1;
            document.addEventListener('keydown', function(e) {
                var modal = document.getElementById('prodModal');
                var quickModal = document.getElementById('quickTaxonomyModal');
                if (e.key === 'Escape' && modal && modal.classList.contains('show') &&
                    (!quickModal || !quickModal.classList.contains('show'))) closeProductModal()
            })
        }

        /* ═══ ROW HTML (AJAX add/edit ke baad) ═══ */
        function stockBadge(stock) {
            if (stock == 0) return '<span class="stock-badge stock-out"><i class="ri-close-circle-line"></i>Out</span>';
            if (stock <= 5) return '<span class="stock-badge stock-low"><i class="ri-error-warning-line"></i>' + stock + ' left</span>';
            return '<span class="stock-badge stock-ok"><i class="ri-checkbox-circle-line"></i>' + stock + '</span>';
        }

        function rowHTML(p) {
            var discount = Number(p.discount) || 0;
            var final = p.discount_type === 'fixed' ? p.price - discount : p.price - (p.price * discount / 100);
            var priceCell = '<span class="price-final">£' + Number(final).toFixed(2) + '</span>';
            if (p.discount > 0) {
                var discountLabel = p.discount_type === 'fixed' ? '-£' + discount.toFixed(2) : '-' + discount + '%';
                priceCell += '<span class="price-orig">£' + Number(p.price).toFixed(2) + '</span><span class="off-badge">' + discountLabel + '</span>'
            }
            var catName = p.category ? p.category.name : '—';
            var catIcon = p.category ? p.category.icon : '';
            var sizeName = p.size ? p.size.name : '—';
            var imgs = (p.images || []).map(function(i) {
                return storageBase + i.path
            });
            var thumb = imgs.length ? '<img src="' + _esc(imgs[0]) + '" alt="">' : productIconHtml(p.emoji);
            var d = p.created_at ? new Date(p.created_at) : new Date();
            var ts = Math.floor(d.getTime() / 1000);
            var dateStr = d.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
            return '<tr data-id="' + p.id + '"' +
                ' data-name="' + _esc(p.name.toLowerCase()) + '"' +
                ' data-cat="' + _esc(p.category ? p.category.name.toLowerCase() : '') + '"' +
                ' data-size="' + _esc(p.size ? p.size.name.toLowerCase() : '') + '"' +
                ' data-final="' + final.toFixed(2) + '"' +
                ' data-hay="' + _esc((p.name + ' ' + catName + ' ' + sizeName).toLowerCase()) + '"' +
                ' data-active="' + (p.is_active ? 1 : 0) + '" data-disc="' + p.discount + '" data-stock="' + p.stock + '"' +
                ' data-name-full="' + _esc(p.name) + '" data-emoji="' + _esc(p.emoji || '') + '" data-slug="' + _esc(p.slug) + '"' +
                ' data-cat-id="' + (p.category_id || '') + '" data-size-id="' + (p.size_id || '') + '" data-brand-id="' + (p.brand_id || '') + '"' +
                ' data-cat-icon="' + _esc(catIcon || '') + '" data-cat-name="' + _esc(p.category ? p.category.name : '') + '"' +
                ' data-size-name="' + _esc(sizeName) + '" data-price="' + p.price + '" data-discount="' + p.discount + '" data-discount-type="' + (p.discount_type || 'percent') + '"' +
                ' data-desc="' + _esc(p.description || '') + '" data-ts="' + ts + '" data-created="' + dateStr + '"' +
                ' data-images="' + _esc(JSON.stringify(imgs)) + '">' +
                '<td><div class="pcell"><div class="p-emoji">' + thumb + '</div><div class="p-info"><strong>' + _esc(p.name) + '</strong><span>#' + _esc(p.slug) + '</span></div></div></td>' +
                '<td><span class="cat-chip">' + productIconHtml(catIcon, 'ri-price-tag-3-line') + _esc(catName) + '</span></td>' +
                '<td>' + _esc(sizeName) + '</td>' +
                '<td>' + priceCell + '</td>' +
                '<td>' + stockBadge(p.stock) + '</td>' +
                '<td class="pill-cell"><span class="pill ' + (p.is_active ? 'active' : 'hidden') + '">' + (p.is_active ? 'Published' : 'Hidden') + '</span></td>' +
                '<td><div class="actions">' +
                '<a href="' + prodShowUrl(p.id) + '" class="act act-view" title="View"><i class="ri-eye-line"></i></a>' +
                '<a href="' + prodEditTpl.replace('__ID__', p.id) + '" class="act act-edit" title="Edit"><i class="ri-pencil-line"></i></a>' +
                '<button type="button" class="act act-del" onclick="deleteProduct(this)" title="Delete"><i class="ri-delete-bin-6-line"></i></button>' +
                '</div></td></tr>';
        }

        /* ═══ SAVE (POST + _method=PATCH spoof) ═══ */
        function saveProduct(e) {
            e.preventDefault();
            var btn = document.getElementById('saveBtn');
            var fd = new FormData(document.getElementById('prodForm'));
            fd.set('is_active', document.getElementById('eActive').checked ? '1' : '0');
            fd.append('_token', prodCsrf);
            if (prodEditId) fd.append('_method', 'PATCH');
            btn.disabled = true;
            fetch(prodUpdUrl(prodEditId), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': prodCsrf,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: fd
                })
                .then(function(r) {
                    if (r.status === 419) throw new Error('Session expired — please refresh the page.');
                    if (r.status === 422) return r.json().then(function(er) {
                        throw new Error(Object.values(er.errors)[0][0])
                    });
                    if (!r.ok) throw new Error('Request failed');
                    return r.json();
                })
                .then(function(data) {
                    var old = document.querySelector('#prodBody tr[data-id="' + prodEditId + '"]');
                    if (old) old.outerHTML = rowHTML(data.product);
                    var fresh = document.querySelector('#prodBody tr[data-id="' + data.product.id + '"]');
                    if (fresh) {
                        fresh.classList.remove('flash');
                        void fresh.offsetWidth;
                        fresh.classList.add('flash')
                    }
                    refreshStats();
                    applyTable();
                    closeProductModal();
                    _toast('success', (data && data.message) || 'Updated.');
                })
                .catch(function(err) {
                    _toast('error', err.message || 'Something went wrong.')
                })
                .finally(function() {
                    btn.disabled = false
                });
            return false;
        }

        /* ═══ DELETE (beautiful confirm + spoof) ═══ */
        function deleteProduct(btn) {
            var tr = btn.closest('tr'),
                name = tr.dataset.nameFull;
            Swal.fire({
                title: 'Delete "' + name + '"?',
                html: '<div class="del-ic"><i class="ri-delete-bin-6-line"></i></div>' +
                    'This product and its photos will be <b style="color:#f87171">permanently removed</b>.',
                icon: null,
                showCancelButton: true,
                reverseButtons: true,
                focusCancel: true,
                confirmButtonText: '<i class="ri-delete-bin-6-line" style="vertical-align:-2px"></i> Yes, Delete',
                cancelButtonText: 'Cancel',
                buttonsStyling: false,
                customClass: {
                    popup: 'swal-popup',
                    title: 'swal-title',
                    htmlContainer: 'swal-html-container',
                    confirmButton: 'swal-confirm',
                    cancelButton: 'swal-cancel'
                }
            }).then(function(r) {
                if (!r.isConfirmed) return;
                var fd = new FormData();
                fd.append('_method', 'DELETE');
                fd.append('_token', prodCsrf);
                fetch(prodDelUrl(tr.dataset.id), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': prodCsrf,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: fd
                    })
                    .then(function(r) {
                        if (r.status === 419) throw new Error('Session expired.');
                        if (!r.ok) throw new Error('failed');
                        return r.json()
                    })
                    .then(function(data) {
                        tr.style.transition = 'all .4s';
                        tr.style.opacity = '0';
                        tr.style.transform = 'scale(.95)';
                        setTimeout(function() {
                            tr.remove();
                            refreshStats();
                            applyTable()
                        }, 400);
                        _toast('success', (data && data.message) || 'Deleted.');
                    })
                    .catch(function() {
                        _toast('error', 'Something went wrong.')
                    });
            });
        }

        /* ═══ LIVE STATS ═══ */
        function refreshStats() {
            var rows = allRows();
            document.getElementById('statTotal').textContent = rows.length;
            document.getElementById('statActive').textContent = rows.filter(function(r) {
                return r.dataset.active === '1'
            }).length;
            document.getElementById('statDisc').textContent = rows.filter(function(r) {
                return parseFloat(r.dataset.disc) > 0
            }).length;
            document.getElementById('statOut').textContent = rows.filter(function(r) {
                return parseInt(r.dataset.stock, 10) === 0
            }).length;
        }

        applyTable();
        if (!window.__prodReveal) {
            window.__prodReveal = 1;
            setTimeout(function() {
                document.querySelectorAll('.reveal:not(.show)').forEach(function(el) {
                    el.classList.add('show')
                })
            }, 1200)
        }
    </script>
</body>

</html>