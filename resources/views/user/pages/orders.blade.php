{{-- ============================================================
     MY ORDERS — REAL DATA from orders table
     ⭐ SIRF PAID ORDERS — unpaid / failed user ko NAHI dikhte
============================================================ --}}
@include('user.include.base')

@php
    $user      = auth()->user();
    $firstName = explode(' ', trim($user->name))[0];

    /* ⭐ PAID-ONLY FILTER — unpaid / failed orders yahan hi drop ho jate hain */
    $orders = $orders->filter(fn ($o) => $o->payment_status === 'paid')->values();

    $stepMap  = ['pending' => 0, 'processing' => 1, 'shipped' => 2, 'delivered' => 3, 'cancelled' => -1];
    $badgeMap = ['delivered' => 'ok', 'shipped' => 'ship', 'processing' => 'proc', 'pending' => 'proc', 'cancelled' => 'bad'];
    $labelMap = ['pending' => 'Pending', 'processing' => 'Processing', 'shipped' => 'Shipped', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'];

    $spend      = $orders->where('status', '!=', 'cancelled')->sum('total');
    $inTransit  = $orders->whereIn('status', ['processing', 'shipped'])->count();
    $deliveredN = $orders->where('status', 'delivered')->count();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}?v=3">
    <title>My Orders — Cosmetics Store</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Allura&display=swap" rel="stylesheet">
    @include('user.include.base')
    <style>
        .page-title h1 { color: var(--ink); font-size: 22px; font-weight: 600; }
        .page-title p { font-size: 12.5px; color: rgba(22, 35, 60, .5); margin-top: 3px; }

        .sum-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 26px; }
        .sum-chip { padding: 18px 20px; display: flex; align-items: center; gap: 14px; }
        .sum-ic { width: 44px; height: 44px; flex-shrink: 0; border-radius: 13px; display: grid; place-items: center; color: var(--teal-2); background: rgba(56, 170, 180, .12); border: 1px solid rgba(56, 170, 180, .3); }
        .sum-chip b { display: block; color: var(--ink); font-size: 20px; font-weight: 700; line-height: 1.1; }
        .sum-chip span { font-size: 11px; letter-spacing: .6px; text-transform: uppercase; color: rgba(22, 35, 60, .5); }

        .filters { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 22px; }
        .order-search { flex: 1 1 240px; min-width: 200px; margin-left: auto; padding: 10px 14px; border: 1px solid var(--glass-border); border-radius: 20px; background: var(--glass); color: var(--ink); font: inherit; font-size: 12px; outline: none; }
        .order-search:focus { border-color: rgba(56, 170, 180, .7); box-shadow: 0 0 0 4px rgba(56, 170, 180, .1); }
        .f-tab { display: inline-flex; align-items: center; padding: 9px 18px; border-radius: 20px; border: 1px solid var(--glass-border); background: var(--glass); font-size: 12px; font-weight: 600; color: rgba(22, 35, 60, .6); text-decoration: none; transition: all .3s; cursor: pointer; }
        .f-tab:hover { color: var(--ink); border-color: rgba(56, 170, 180, .5); }
        .f-tab.active { color: #fff; background: linear-gradient(135deg, var(--teal), var(--teal-2)); border-color: transparent; box-shadow: 0 8px 20px rgba(56, 170, 180, .35); }
        .f-tab .n { opacity: .75; font-weight: 500; margin-left: 4px; }

        .orders-list { display: grid; gap: 20px; }
        .order-card { overflow: hidden; transition: transform .35s, box-shadow .35s; }
        .order-card:hover { transform: translateY(-3px); box-shadow: 0 26px 55px rgba(31, 51, 89, .13); }

        .oc-head { display: flex; align-items: center; flex-wrap: wrap; gap: 14px; padding: 20px 24px; border-bottom: 1px solid var(--glass-border); background: rgba(255, 255, 255, .35); }
        .oc-id { color: var(--teal-2); font-weight: 700; font-size: 15px; letter-spacing: .3px; }
        .oc-date { font-size: 12px; color: rgba(22, 35, 60, .5); display: inline-flex; align-items: center; gap: 6px; }
        .oc-head .spacer { flex: 1; }

        .badge { display: inline-flex; align-items: center; gap: 7px; padding: 5px 13px; border-radius: 20px; font-size: 11px; font-weight: 600; letter-spacing: .4px; }
        .badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; box-shadow: 0 0 8px currentColor; }
        .badge.ok   { color: #1f9d63; background: rgba(46, 193, 129, .12);  border: 1px solid rgba(46, 193, 129, .35); }
        .badge.ship { color: #178f9d; background: rgba(56, 170, 180, .12);  border: 1px solid rgba(56, 170, 180, .4); }
        .badge.proc { color: #b5831f; background: rgba(240, 180, 41, .15);  border: 1px solid rgba(240, 180, 41, .4); }
        .badge.bad  { color: #e0566e; background: rgba(255, 107, 129, .12); border: 1px solid rgba(255, 107, 129, .4); }

        .oc-body { padding: 22px 24px; display: grid; grid-template-columns: 1fr auto; gap: 26px; align-items: start; }
        .oc-items { display: grid; gap: 12px; }
        .oc-item { display: flex; align-items: center; gap: 13px; }
        .oc-thumb { width: 52px; height: 52px; flex-shrink: 0; border-radius: 12px; overflow: hidden; border: 1px solid var(--glass-border); box-shadow: 0 6px 14px rgba(31, 51, 89, .1); display: grid; place-items: center; background: #f6f9f9; font-size: 22px; }
        .oc-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .oc-item b { display: block; color: var(--ink); font-size: 13px; font-weight: 500; }
        .oc-item span { font-size: 11.5px; color: rgba(22, 35, 60, .5); }
        .oc-item .p { margin-left: auto; color: var(--teal-2); font-weight: 600; font-size: 13px; white-space: nowrap; }
        .oc-more { font-size: 12px; color: var(--teal-2); font-weight: 500; padding-left: 65px; }
        @keyframes help-dialog-enter { from { opacity:0; transform:translate(-50%,-48%) scale(.98); } to { opacity:1; transform:translate(-50%,-50%) scale(1); } }
        .help-dialog { position:fixed; inset:50% auto auto 50%; transform:translate(-50%,-50%); margin:0; width:min(560px,calc(100vw - 32px)); max-width:calc(100vw - 32px); max-height:min(88vh,760px); overflow:auto; overscroll-behavior:contain; padding:0; border:1px solid rgba(22,35,60,.1); border-radius:22px; color:var(--ink); background:linear-gradient(145deg,#fff,#f8fbfc); box-shadow:0 30px 90px rgba(20,42,67,.28); }
        .help-dialog[open] { animation:help-dialog-enter .22s cubic-bezier(.2,.75,.25,1) both; }
        .help-dialog::backdrop { background:rgba(14,25,39,.56); backdrop-filter:blur(5px); }
        .help-dialog-head { display:flex; align-items:flex-start; justify-content:space-between; gap:15px; padding:22px 24px 17px; border-bottom:1px solid #edf1f3; }
        .help-dialog-title { display:flex; align-items:center; gap:12px; min-width:0; }
        .help-dialog-icon { display:grid; place-items:center; flex:none; width:42px; height:42px; border:1px solid rgba(56,170,180,.22); border-radius:14px; background:linear-gradient(135deg,rgba(56,170,180,.16),rgba(125,219,226,.18)); color:var(--teal-2); font-size:20px; }
        .help-dialog svg { width:16px; height:16px; fill:none; stroke:currentColor; stroke-width:1.8; stroke-linecap:round; stroke-linejoin:round; }
        .help-dialog-icon svg { width:20px; height:20px; }.help-dialog-close svg { width:18px; height:18px; }.help-item-icon svg { width:16px; height:16px; }
        .help-dialog-kicker { display:block; margin-bottom:4px; color:var(--teal-2); font-size:9px; font-weight:700; letter-spacing:1.4px; text-transform:uppercase; }
        .help-dialog-head h2 { margin:0; color:var(--ink); font-size:17px; line-height:1.35; }
        .help-dialog-close { display:grid; place-items:center; flex:none; width:34px; height:34px; border:1px solid #e9eef0; border-radius:11px; background:#f4f7f8; color:#647181; font-size:20px; cursor:pointer; transition:background .18s,color .18s,transform .18s; }
        .help-dialog-close:hover { transform:rotate(90deg); background:#eaf4f5; color:var(--teal-2); }
        .help-dialog-description { margin:0; padding:16px 24px 5px; color:#68778a; font-size:11px; line-height:1.6; }
        .help-items-heading { padding:11px 24px 8px; color:#8994a2; font-size:9px; font-weight:700; letter-spacing:1.1px; text-transform:uppercase; }
        .help-items { display:grid; gap:8px; padding:0 24px; }
        .help-item-link { display:flex; align-items:center; justify-content:space-between; gap:12px; min-width:0; padding:11px 12px; border:1px solid #e8eef0; border-radius:13px; background:#fff; color:var(--ink); text-decoration:none; font-size:11px; box-shadow:0 3px 10px rgba(20,42,67,.025); transition:border-color .18s,box-shadow .18s,transform .18s; }
        .help-item-link:hover { transform:translateY(-1px); border-color:rgba(56,170,180,.55); color:var(--teal-2); box-shadow:0 8px 18px rgba(56,170,180,.1); }
        .help-item-product { display:flex; align-items:center; gap:9px; min-width:0; }
        .help-item-icon { display:grid; place-items:center; flex:none; width:32px; height:32px; border-radius:10px; background:#f0f7f8; color:var(--teal-2); font-size:15px; }
        .help-product-name { overflow:hidden; font-weight:600; text-overflow:ellipsis; white-space:nowrap; }
        .help-item-action { display:flex; align-items:center; gap:5px; flex:none; color:var(--teal-2); font-size:10px; font-weight:700; white-space:nowrap; }
        .help-return-link { display:flex; align-items:center; justify-content:space-between; gap:12px; margin:14px 24px 17px; padding:11px 12px; border:1px solid rgba(56,170,180,.18); border-radius:12px; background:rgba(56,170,180,.06); color:var(--teal-2); font-size:11px; font-weight:700; text-decoration:none; transition:background .18s,border-color .18s; }
        .help-return-link:hover { border-color:rgba(56,170,180,.45); background:rgba(56,170,180,.11); }
        .help-dialog-note { display:flex; align-items:center; gap:7px; padding:11px 24px 16px; border-top:1px solid #edf1f3; color:#8793a1; font-size:9px; line-height:1.5; }
        .help-dialog-note i { color:var(--teal-2); font-size:14px; }
        @media (max-width:560px) { .help-dialog { width:calc(100vw - 24px); max-width:calc(100vw - 24px); max-height:calc(100dvh - 24px); border-radius:19px; } .help-dialog-head { padding:18px 18px 14px; } .help-dialog-description { padding:14px 18px 4px; } .help-items-heading { padding:10px 18px 7px; } .help-items { padding:0 18px; } .help-return-link { margin:12px 18px 15px; } .help-dialog-note { padding:10px 18px 14px; } }
        @media (prefers-reduced-motion:reduce) { .help-dialog[open] { animation:none; } .help-item-link,.help-dialog-close,.help-return-link { transition:none; } }

        .oc-side { min-width: 190px; display: grid; gap: 12px; align-content: start; }
        .pay-chip { display: inline-flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 12px; background: rgba(56, 170, 180, .07); border: 1px dashed rgba(56, 170, 180, .35); font-size: 12px; color: var(--ink); font-weight: 500; }
        .pay-chip svg { width: 18px; height: 18px; flex-shrink: 0; color: var(--teal-2); }
        .pay-lbl { font-size: 9.5px; letter-spacing: 1.6px; text-transform: uppercase; color: rgba(22, 35, 60, .45); display: block; margin-bottom: -6px; }
        .oc-total { text-align: left; }
        .oc-total b { color: var(--ink); font-size: 21px; font-weight: 700; }

        .tracker-wrap { padding: 0 24px 22px; }
        .eta-chip { display: inline-flex; align-items: center; gap: 8px; margin-bottom: 16px; padding: 8px 15px; border-radius: 20px; background: linear-gradient(135deg, rgba(56, 170, 180, .14), rgba(125, 219, 226, .12)); border: 1px solid rgba(56, 170, 180, .35); color: var(--teal-2); font-size: 12px; font-weight: 600; }
        .eta-chip svg { width: 15px; height: 15px; }
        .eta-chip b { color: var(--ink); }

        .tracker { position: relative; display: flex; justify-content: space-between; max-width: 620px; }
        .tracker::before { content: ''; position: absolute; top: 15px; left: 8%; right: 8%; height: 3px; border-radius: 3px; background: rgba(22, 35, 60, .1); }
        .tracker .line { position: absolute; top: 15px; left: 8%; height: 3px; border-radius: 3px; background: linear-gradient(90deg, var(--teal), var(--teal-light)); box-shadow: 0 0 12px rgba(56, 170, 180, .55); transition: width 1.2s cubic-bezier(.22, 1, .36, 1); width: 0; }
        .t-step { position: relative; z-index: 2; text-align: center; flex: 1; }
        .t-dot { width: 33px; height: 33px; margin: 0 auto 8px; border-radius: 50%; display: grid; place-items: center; background: #fff; border: 2px solid rgba(22, 35, 60, .15); color: rgba(22, 35, 60, .35); transition: all .3s; }
        .t-dot svg { width: 14px; height: 14px; }
        .t-step span { font-size: 10px; letter-spacing: .8px; text-transform: uppercase; color: rgba(22, 35, 60, .4); font-weight: 500; }
        .t-step.done .t-dot { background: linear-gradient(135deg, var(--teal), var(--teal-2)); border-color: transparent; color: #fff; box-shadow: 0 6px 16px rgba(56, 170, 180, .4); }
        .t-step.done span { color: var(--teal-2); font-weight: 600; }
        .t-step.current .t-dot { border-color: var(--teal); color: var(--teal-2); animation: tPulse 1.8s infinite; }
        @keyframes tPulse { 0%, 100% { box-shadow: 0 0 0 0 rgba(56, 170, 180, .35); } 50% { box-shadow: 0 0 0 8px rgba(56, 170, 180, .08); } }

        .order-card.cancelled .oc-body, .order-card.cancelled .tracker-wrap { opacity: .55; }
        .cancel-note { display: inline-flex; align-items: center; gap: 8px; padding: 8px 15px; border-radius: 20px; background: rgba(255, 107, 129, .1); border: 1px solid rgba(255, 107, 129, .35); color: #e0566e; font-size: 12px; font-weight: 500; }

        .oc-foot { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; padding: 16px 24px; border-top: 1px solid var(--glass-border); background: rgba(255, 255, 255, .3); }
        .oc-btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 11px; font-size: 11.5px; font-weight: 600; letter-spacing: .8px; text-transform: uppercase; transition: all .3s; border: 1px solid var(--glass-border); background: #fff; color: rgba(22, 35, 60, .65); cursor: pointer; }
        .oc-btn svg { width: 14px; height: 14px; }
        .oc-btn:hover { color: var(--ink); border-color: rgba(56, 170, 180, .5); transform: translateY(-2px); }
        .oc-btn.primary { background: linear-gradient(135deg, var(--teal), var(--teal-2)); color: #fff; border-color: transparent; box-shadow: 0 8px 20px rgba(56, 170, 180, .35); }
        .oc-btn.primary:hover { box-shadow: 0 12px 26px rgba(56, 170, 180, .45); color: #fff; }
        .oc-btn.return-product { color:#087b70;background:rgba(42,177,160,.08);border-color:rgba(42,177,160,.3); }
        .oc-btn.return-product:hover { color:#075c55;background:rgba(42,177,160,.15);border-color:rgba(42,177,160,.55); }

        .invoice-dialog { width: min(640px, calc(100vw - 32px)); max-height: min(88vh, 860px); margin: auto; padding: 0; border: 1px solid rgba(22,35,60,.1); border-radius: 22px; color: var(--ink); background: #fff; box-shadow: 0 30px 90px rgba(22,35,60,.24); overflow: auto; }
        .invoice-dialog::backdrop { background: rgba(14,25,39,.52); backdrop-filter: blur(4px); }
        .invoice-head { display: flex; justify-content: space-between; align-items: flex-start; padding: 24px 26px 18px; border-bottom: 1px solid #edf0f2; }
        .invoice-kicker { color: var(--teal-2); font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; }
        .invoice-head h2 { margin-top: 5px; color: var(--ink); font-size: 22px; }
        .invoice-close { border: 0; background: #f2f5f6; color: #566273; border-radius: 10px; width: 36px; height: 36px; font-size: 24px; line-height: 1; cursor: pointer; }
        .invoice-meta { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; padding: 18px 26px; }
        .invoice-meta div, .invoice-customer { display: grid; gap: 4px; }
        .invoice-meta span, .invoice-customer b { color: #7d8793; font-size: 10px; text-transform: uppercase; letter-spacing: .7px; }
        .invoice-meta b { color: var(--ink); font-size: 12px; }
        .invoice-customer { padding: 15px 26px; background: #f7f9f9; color: #526071; font-size: 12px; }
        .invoice-customer b { color: var(--teal-2); }
        .invoice-items { padding: 12px 26px; }
        .invoice-item, .invoice-totals div { display: flex; justify-content: space-between; gap: 16px; padding: 10px 0; border-bottom: 1px solid #edf0f2; color: #526071; font-size: 12px; }
        .invoice-item-main { display:flex; align-items:center; gap:10px; min-width:0; }
        .invoice-item-image { width:42px; height:42px; flex:0 0 42px; display:grid; place-items:center; border-radius:9px; overflow:hidden; background:#f1f7f7; color:var(--teal-2); font-size:18px; }
        .invoice-item-image img { width:100%; height:100%; object-fit:cover; }
        .invoice-item-info { display: grid; gap: 3px; }
        .invoice-item-info small { color: #87919c; }
        .invoice-item small { color: #87919c; }
        .invoice-item b, .invoice-totals b { color: var(--ink); white-space: nowrap; }
        .invoice-totals { padding: 4px 26px 18px; }
        .invoice-totals div { border-bottom: 0; padding: 5px 0; }
        .invoice-totals .invoice-grand-total { margin-top: 8px; padding-top: 12px; border-top: 1px solid #dfe5e7; color: var(--ink); font-size: 14px; font-weight: 700; }
        .invoice-actions { display: flex; justify-content: flex-end; gap: 9px; padding: 14px 26px 20px; }
        .invoice-actions button { border: 1px solid #dce4e6; border-radius: 10px; padding: 9px 14px; background: #fff; color: var(--ink); font: inherit; font-size: 11px; font-weight: 600; cursor: pointer; }
        .invoice-actions .invoice-print { border-color: transparent; background: linear-gradient(135deg, var(--teal), var(--teal-2)); color: #fff; }
        @media (max-width: 520px) { .invoice-meta { grid-template-columns: 1fr; } .invoice-head, .invoice-meta, .invoice-customer, .invoice-items, .invoice-totals { padding-left: 18px; padding-right: 18px; } }
        @media print { body.invoice-printing > *:not(.layout) { display: none !important; } body.invoice-printing .layout * { visibility: hidden !important; } body.invoice-printing .invoice-dialog[open], body.invoice-printing .invoice-dialog[open] * { visibility: visible !important; } body.invoice-printing .invoice-dialog[open] { position: fixed; inset: 0; width: 100%; max-height: none; border: 0; box-shadow: none; overflow: visible; } body.invoice-printing .invoice-actions { display: none !important; } }

        .empty-state { text-align: center; padding: 80px 20px; }
        .empty-state .e-ic { width: 84px; height: 84px; margin: 0 auto 20px; border-radius: 26px; display: grid; place-items: center; background: rgba(56, 170, 180, .1); color: var(--teal); }
        .empty-state h3 { color: var(--ink); font-size: 18px; margin-bottom: 8px; }
        .empty-state p { font-size: 13px; margin-bottom: 24px; }

        .foot-note { text-align: center; margin-top: 34px; font-size: 12px; color: rgba(22, 35, 60, .4); }

        @media (max-width: 1100px) { .sum-row { grid-template-columns: repeat(2, 1fr); } .oc-body { grid-template-columns: 1fr; } .oc-side { min-width: 0; } }
        @media (max-width: 560px) { .sum-row { grid-template-columns: 1fr; } .t-step span { font-size: 8.5px; } }
    </style>
</head>
<body>

    <div class="scene" aria-hidden="true">
        <span class="orb orb-1"></span><span class="orb orb-2"></span><span class="orb orb-3"></span>
    </div>

    <div class="layout">
        @include('user.include.sidebar')

        <main class="main">

            <header class="topbar reveal show">
                <button class="hamburger" id="hamburger" aria-label="Open menu">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <div class="page-title">
                    <h1>My Orders, {{ $firstName }} 📦</h1>
                    <p>Track deliveries, view payments and reorder your favourites.</p>
                </div>
            </header>

            @if (session('success'))
            <div class="glass" role="status" style="padding:13px 16px;color:#126d4a">{{ session('success') }}</div>
            @endif
            @if ($errors->has('return'))
            <div class="glass" role="alert" style="padding:13px 16px;color:#b83232">{{ $errors->first('return') }}</div>
            @endif

            <section class="sum-row">
                <div class="sum-chip glass reveal">
                    <div class="sum-ic"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M6 7h12l1.5 13.5a1 1 0 0 1-1 1.1H5.5a1 1 0 0 1-1-1.1L6 7z"/><path d="M9 10V6a3 3 0 0 1 6 0v4"/></svg></div>
                    <div><b>{{ $orders->count() }}</b><span>Total Orders</span></div>
                </div>
                <div class="sum-chip glass reveal" style="--d:.06s">
                    <div class="sum-ic" style="color:#1f9d63;background:rgba(46,193,129,.12);border-color:rgba(46,193,129,.3)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20 6 9 17l-5-5"/></svg></div>
                    <div><b>{{ $deliveredN }}</b><span>Delivered</span></div>
                </div>
                <div class="sum-chip glass reveal" style="--d:.12s">
                    <div class="sum-ic" style="color:#b5831f;background:rgba(240,180,41,.14);border-color:rgba(240,180,41,.35)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg></div>
                    <div><b>{{ $inTransit }}</b><span>In Transit</span></div>
                </div>
                <div class="sum-chip glass reveal" style="--d:.18s">
                    <div class="sum-ic" style="color:#8b5cf6;background:rgba(139,92,246,.12);border-color:rgba(139,92,246,.3)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><line x1="12" y1="2" x2="12" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
                    <div><b>£{{ number_format($spend, 2) }}</b><span>Total Spent</span></div>
                </div>
            </section>

            <div class="filters reveal" style="--d:.1s" id="filters">
                <button class="f-tab active" data-f="all">All <span class="n">({{ $orders->count() }})</span></button>
                <button class="f-tab" data-f="pending">Pending <span class="n">({{ $orders->where('status','pending')->count() }})</span></button>
                <button class="f-tab" data-f="processing">Processing <span class="n">({{ $orders->where('status','processing')->count() }})</span></button>
                <button class="f-tab" data-f="shipped">Shipped <span class="n">({{ $orders->where('status','shipped')->count() }})</span></button>
                <button class="f-tab" data-f="delivered">Delivered <span class="n">({{ $deliveredN }})</span></button>
                <button class="f-tab" data-f="cancelled">Cancelled <span class="n">({{ $orders->where('status','cancelled')->count() }})</span></button>
                <input type="search" class="order-search" id="orderSearch" aria-label="Search orders" placeholder="Search order numbers or items...">
            </div>

            <section class="orders-list">
                @forelse ($orders as $o)
                @php
                    $step    = $stepMap[$o->status];
                    $items   = $o->items;
                    $shown   = $o->status === 'delivered' ? $items : $items->take(2);
                    $hidden  = max($items->count() - $shown->count(), 0);
                    $payLbl  = $o->payment_method === 'stripe' ? 'Card (Stripe)' : ($o->payment_method === 'paystack' ? 'Card (Paystack)' : ucfirst($o->payment_method));
                    $eta     = in_array($o->status, ['processing', 'shipped']) ? $o->created_at->copy()->addDays(7) : null;
                    $firstP  = $items->first()?->product;
                @endphp
                <article class="order-card glass reveal {{ $o->status === 'cancelled' ? 'cancelled' : '' }}" data-status="{{ $o->status }}">
                    <div class="oc-head">
                        <span class="oc-id">Order {{ $o->order_number }}</span>
                        <span class="oc-date">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            {{ $o->created_at->format('M d, Y') }}
                        </span>
                        <span class="spacer"></span>
                        <span class="badge {{ $badgeMap[$o->status] }}">{{ $labelMap[$o->status] }}</span>
                        <span class="badge ok">Paid</span>
                    </div>

                    <div class="oc-body">
                        <div class="oc-items">
                            @foreach ($shown as $it)
                            <div class="oc-item">
                                <div class="oc-thumb">
                                    @if ($it->product && $it->product->images->first())
                                    <img src="{{ asset('storage/' . $it->product->images->first()->path) }}" alt="{{ $it->product_name }}">
                                    @else
                                    <span>{{ $it->emoji ?: '🧴' }}</span>
                                    @endif
                                </div>
                                <div>
                                    <b>{{ $it->product_name }}</b>
                                    <span>Qty: {{ $it->qty }} × £{{ number_format($it->product_price, 2) }}</span>
                                </div>
                                <span class="p">£{{ number_format($it->line_total, 2) }}</span>
                            </div>
                            @endforeach
                            @if ($hidden > 0)
                            <p class="oc-more">+ {{ $hidden }} more item{{ $hidden > 1 ? 's' : '' }} in this order</p>
                            @endif
                        </div>

                        <div class="oc-side">
                            <span class="pay-lbl">Payment Method</span>
                            <div class="pay-chip">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                                {{ $payLbl }}
                            </div>
                            <div class="oc-total">
                                <span class="pay-lbl">Order Total</span>
                                <b>£{{ number_format($o->total, 2) }}</b>
                            </div>
                        </div>
                    </div>

                    @if ($step >= 0)
                    <div class="tracker-wrap">
                        @if ($eta)
                        <div class="eta-chip">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                            Arriving by <b>{{ $eta->format('D, M d') }}</b>
                        </div>
                        @endif

                        <div class="tracker" data-step="{{ $step }}">
                            <span class="line"></span>
                            <div class="t-step done">
                                <div class="t-dot"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6 9 17l-5-5"/></svg></div>
                                <span>Placed</span>
                            </div>
                            <div class="t-step {{ $step === 0 ? 'current' : ($step >= 1 ? ($step === 1 ? 'current done' : 'done') : '') }}">
                                <div class="t-dot">
                                    @if ($step > 1)<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6 9 17l-5-5"/></svg>
                                    @else<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 7h12l1.5 13.5a1 1 0 0 1-1 1.1H5.5a1 1 0 0 1-1-1.1L6 7z"/></svg>@endif
                                </div>
                                <span>Processing</span>
                            </div>
                            <div class="t-step {{ $step >= 2 ? ($step === 2 ? 'current done' : 'done') : '' }}">
                                <div class="t-dot">
                                    @if ($step > 2)<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6 9 17l-5-5"/></svg>
                                    @else<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 6h13v10H1z"/><path d="M14 9h4l3 3v4h-7"/><circle cx="6" cy="17.5" r="1.6"/><circle cx="17.5" cy="17.5" r="1.6"/></svg>@endif
                                </div>
                                <span>Shipped</span>
                            </div>
                            <div class="t-step {{ $step >= 3 ? 'current done' : '' }}">
                                <div class="t-dot"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 10c0 7-9 12-9 12s-9-5-9-12a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="2.6"/></svg></div>
                                <span>Delivered</span>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="tracker-wrap">
                        <span class="cancel-note">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><line x1="9" y1="9" x2="15" y2="15"/><line x1="15" y1="9" x2="9" y2="15"/></svg>
                            This order was cancelled — refund issued to {{ $payLbl }}
                        </span>
                    </div>
                    @endif

                    <div class="oc-foot">
                        <a href="{{ $firstP ? route('product.show', $firstP->slug) : route('shop') }}" class="oc-btn primary">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 7h12l1.5 13.5a1 1 0 0 1-1 1.1H5.5a1 1 0 0 1-1-1.1L6 7z"/><path d="M9 10V6a3 3 0 0 1 6 0v4"/></svg>
                            Buy Again
                        </a>
                        <button class="oc-btn invoice-open" type="button" data-invoice="invoice-{{ $o->id }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            Invoice
                        </button>
                        <button class="oc-btn help-open" type="button" data-help="help-{{ $o->id }}">Need Help?</button>
                        @if ($o->status === 'delivered' && $o->payment_status === 'paid')
                        <a class="oc-btn return-product" href="{{ route('user.returns', ['order' => $o->id]) }}#return-order-{{ $o->id }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 12a9 9 0 1 0 2.64-6.36L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l3 2"/></svg>
                            Return the product
                        </a>
                        @endif
                    </div>

                    <dialog class="help-dialog" id="help-{{ $o->id }}" aria-labelledby="help-title-{{ $o->id }}">
                        <div class="help-dialog-head">
                            <div class="help-dialog-title"><span class="help-dialog-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 13v-2a8 8 0 0 1 16 0v2"/><path d="M4 13h2a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zM20 13h-2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1a1 1 0 0 0 1-1z"/><path d="M16 20a4 4 0 0 1-4 2"/></svg></span><div><span class="help-dialog-kicker">Order support</span><h2 id="help-title-{{ $o->id }}">Need help with this order?</h2></div></div>
                            <button type="button" class="help-dialog-close" aria-label="Close help"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
                        </div>
                        <p class="help-dialog-description">Choose the item you need help with and we’ll connect your request to the right order details.</p>
                        <div class="help-items-heading">Select an item</div>
                        <div class="help-items">
                            @foreach($items as $helpItem)
                                <a class="help-item-link" data-order-item="{{ $helpItem->id }}" href="{{ route('user.support', ['order' => $o->id, 'item' => $helpItem->id]) }}">
                                    <span class="help-item-product"><span class="help-item-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 7 9-4 9 4v10l-9 4-9-4z"/><path d="m3 7 9 5 9-5M12 12v9"/></svg></span><span class="help-product-name">{{ $helpItem->product_name }}</span></span><span class="help-item-action">Contact support <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></span>
                                </a>
                            @endforeach
                        </div>
                        <a class="help-return-link" href="{{ route('user.returns') }}"><span>Request or track a return</span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></a>
                        <div class="help-dialog-note"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 20 6v5c0 5-3.4 8-8 10-4.6-2-8-5-8-10V6z"/><path d="m8 12 2.5 2.5L16 9"/></svg><span>Your request stays linked to this order so our team can help faster.</span></div>
                    </dialog>

                    <dialog class="invoice-dialog" id="invoice-{{ $o->id }}" aria-labelledby="invoice-title-{{ $o->id }}">
                        <div class="invoice-head">
                            <div><span class="invoice-kicker">Cosmetics Store</span><h2 id="invoice-title-{{ $o->id }}">Order invoice</h2></div>
                            <button type="button" class="invoice-close" aria-label="Close invoice">&times;</button>
                        </div>
                        <div class="invoice-meta">
                            <div><span>Order number</span><b>{{ $o->order_number }}</b></div>
                            <div><span>Purchase date</span><b>{{ $o->created_at->format('M d, Y') }}</b></div>
                            <div><span>Payment</span><b>{{ $payLbl }} · {{ ucfirst($o->payment_status) }}</b></div>
                        </div>
                        <div class="invoice-customer">
                            <b>Delivered to</b>
                            <span>{{ $o->name }} · {{ $o->email }}</span>
                            <span>{{ $o->address }}, {{ $o->city }} {{ $o->postal_code }}, {{ $o->country }}</span>
                        </div>
                        <div class="invoice-items">
                            @foreach ($items as $invoiceItem)
                            <div class="invoice-item">
                                <div class="invoice-item-main">
                                    <span class="invoice-item-image">
                                        @if ($invoiceItem->product?->images->first())
                                        <img src="{{ asset('storage/' . $invoiceItem->product->images->first()->path) }}" alt="">
                                        @else
                                        {{ $invoiceItem->emoji ?: '🧴' }}
                                        @endif
                                    </span>
                                    <div class="invoice-item-info">
                                        <b>{{ $invoiceItem->product_name }}</b>
                                        <small>Qty {{ $invoiceItem->qty }} × £{{ number_format($invoiceItem->product_price, 2) }}</small>
                                    </div>
                                </div>
                                <b>£{{ number_format($invoiceItem->line_total, 2) }}</b>
                            </div>
                            @endforeach
                        </div>
                        <div class="invoice-totals">
                            <div><span>Subtotal</span><b>£{{ number_format($o->subtotal, 2) }}</b></div>
                            <div><span>Shipping</span><b>£{{ number_format($o->shipping, 2) }}</b></div>
                            <div class="invoice-grand-total"><span>Total paid</span><b>£{{ number_format($o->total, 2) }}</b></div>
                        </div>
                        <div class="invoice-actions">
                            <button type="button" class="invoice-print">Print invoice</button>
                            <button type="button" class="invoice-close secondary">Close</button>
                        </div>
                    </dialog>
                </article>
                @empty
                <div class="empty-state glass">
                    <div class="e-ic">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 7h12l1.5 13.5a1 1 0 0 1-1 1.1H5.5a1 1 0 0 1-1-1.1L6 7z"/><path d="M9 10V6a3 3 0 0 1 6 0v4"/></svg>
                    </div>
                    <h3>No paid orders yet</h3>
                    <p>Your beauty hauls will appear here once you complete your first payment.</p>
                    <a href="{{ route('shop') }}" class="oc-btn primary" style="display:inline-flex">Start Shopping</a>
                </div>
                @endforelse
            </section>
            @if ($orders->isNotEmpty())
            <div class="empty-state glass" id="orderNoResults" role="status" style="display:none;padding:34px 20px">
                <h3>No matching orders</h3>
                <p>Try another search or status filter.</p>
            </div>
            @endif

            <p class="foot-note">Cosmetics Store · Beauty Pronounced 💫</p>
        </main>
    </div>

    <div class="overlay" id="overlay"></div>

    <script>
        (function () {
            function initOrders() {
                var sidebar = document.getElementById('sidebar');
                var overlay = document.getElementById('overlay');
                var hamburger = document.getElementById('hamburger');
                function closeSide() { sidebar && sidebar.classList.remove('open'); overlay && overlay.classList.remove('show'); }
                if (hamburger && sidebar && overlay) {
                    hamburger.addEventListener('click', function () { sidebar.classList.toggle('open'); overlay.classList.toggle('show'); });
                    overlay.addEventListener('click', closeSide);
                }

                var els = document.querySelectorAll('.reveal');
                if ('IntersectionObserver' in window) {
                    var io = new IntersectionObserver(function (entries) {
                        entries.forEach(function (en) {
                            if (en.isIntersecting) { en.target.classList.add('show'); io.unobserve(en.target); }
                        });
                    }, { threshold: .08 });
                    els.forEach(function (el) { io.observe(el); });
                } else { els.forEach(function (el) { el.classList.add('show'); }); }
                setTimeout(function () { document.querySelectorAll('.reveal:not(.show)').forEach(function (el) { el.classList.add('show'); }); }, 1200);

                document.querySelectorAll('.tracker').forEach(function (t, i) {
                    var step = parseInt(t.dataset.step, 10);
                    setTimeout(function () {
                        t.querySelector('.line').style.width = (step / 3) * 84 + '%';
                    }, 400 + i * 150);
                });

                var tabs = document.querySelectorAll('.f-tab[data-f]');
                var cards = document.querySelectorAll('.order-card');
                var ordersList = document.querySelector('.orders-list');
                var orderSearch = document.getElementById('orderSearch');
                var noResults = document.getElementById('orderNoResults');
                var selectedStatus = 'all';

                function applyOrderFilters() {
                    var query = orderSearch ? orderSearch.value.trim().toLowerCase() : '';
                    var visible = 0;
                    cards.forEach(function (card) {
                        var matchesStatus = selectedStatus === 'all' || card.dataset.status === selectedStatus;
                        var matchesQuery = !query || card.textContent.toLowerCase().includes(query);
                        card.style.display = matchesStatus && matchesQuery ? '' : 'none';
                        if (matchesStatus && matchesQuery) visible++;
                    });
                    if (noResults) noResults.style.display = visible ? 'none' : 'block';
                }

                tabs.forEach(function (tab) {
                    tab.addEventListener('click', function () {
                        tabs.forEach(function (t) { t.classList.remove('active'); });
                        tab.classList.add('active');
                        selectedStatus = tab.dataset.f;
                        applyOrderFilters();
                    });
                });
                if (orderSearch) orderSearch.addEventListener('input', applyOrderFilters);

                    document.querySelectorAll('.help-open').forEach(function (button) {
                        button.addEventListener('click', function () {
                            var helpDialog = document.getElementById(button.dataset.help);
                            if (helpDialog && typeof helpDialog.showModal === 'function') helpDialog.showModal();
                        });
                    });
                    document.querySelectorAll('.help-dialog').forEach(function (dialog) {
                        dialog.querySelectorAll('.help-dialog-close').forEach(function (button) {
                            button.addEventListener('click', function () { dialog.close(); });
                        });
                        dialog.addEventListener('click', function (event) {
                            if (event.target === dialog) dialog.close();
                        });
                    });

                    document.querySelectorAll('.invoice-open').forEach(function (button) {
                        button.addEventListener('click', function () {
                            var invoice = document.getElementById(button.dataset.invoice);
                            if (invoice && typeof invoice.showModal === 'function') invoice.showModal();
                        });
                    });
                    document.querySelectorAll('.invoice-dialog').forEach(function (invoice) {
                        invoice.querySelectorAll('.invoice-close').forEach(function (button) {
                            button.addEventListener('click', function () { invoice.close(); });
                        });
                        var printButton = invoice.querySelector('.invoice-print');
                        if (printButton) printButton.addEventListener('click', function () {
                            document.body.classList.add('invoice-printing');
                            window.print();
                            document.body.classList.remove('invoice-printing');
                        });
                        invoice.addEventListener('click', function (event) {
                            if (event.target === invoice) invoice.close();
                        });
                    });
                }

            if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initOrders);
            else initOrders();
        })();
    </script>
</body>
</html>
