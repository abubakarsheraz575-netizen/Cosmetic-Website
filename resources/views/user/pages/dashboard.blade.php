{{-- ============================================================
     USER DASHBOARD — Cosmetics Store (Light Glass Edition) v4
     ✅ RING blade var ONE-LINE fix (poora script mara hua tha!)
     ✅ PAID-ONLY + Shipping notices
     ✅ Orders table + wishlist FULL responsive (media queries)
     ✅ Beauty profile is populated from saved customer preferences
============================================================ --}}

@php
    $user = auth()->user();
    $firstName = explode(' ', trim($user->name))[0];
    $badgeMap = ['delivered' => 'ok', 'shipped' => 'ship', 'processing' => 'proc', 'pending' => 'proc', 'cancelled' => 'bad'];
    $statusLbl = ['pending' => 'Pending', 'processing' => 'Processing', 'shipped' => 'Shipped', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'];

    /* ⭐ PAID-ONLY */
    $paidOrders = \App\Models\Order::where('user_id', $user->id)
        ->where('payment_status', 'paid')
        ->with('items')
        ->latest()
        ->get();

    $totalOrders = $paidOrders->count();
    $deliveredN  = $paidOrders->where('status', 'delivered')->count();
    $inTransitN  = $paidOrders->whereIn('status', ['processing', 'shipped'])->count();
    $spend       = $paidOrders->where('status', '!=', 'cancelled')->sum('total');

    $recentOrders = $paidOrders->take(5);

    /* chart */
    $chart = collect();
    $maxChart = 1;
    for ($i = 7; $i >= 0; $i--) {
        $m = now()->subMonths($i);
        $v = $paidOrders->filter(fn ($o) => $o->created_at->format('Y-m') === $m->format('Y-m'))->count();
        $maxChart = max($maxChart, $v);
        $chart->push(['m' => $m->format('M'), 'v' => $v, 'h' => round($v / max(1, $maxChart) * 100)]);
    }

    /* rewards */
    $points   = (int) floor($spend);
    $nextGoal = 5000;
    $tierPct  = min(100, (int) round($points / $nextGoal * 100));
    $tierName = $points >= 5000 ? 'Platinum' : ($points >= 2500 ? 'Gold' : 'Silver');

    /* beauty profile */
    $beauty = [
        ['Skin Type', $user->skin_type ?: 'Not set'],
        ['Hair Type', $user->hair_type ?: 'Not set'],
        ['Shade Match', $user->shade_match ?: 'Not set'],
        ['Loves', $user->loves ?: 'Not set'],
    ];
    $filledBeautyFields = collect([$user->skin_type, $user->hair_type, $user->shade_match, $user->loves])
        ->filter(fn ($value) => $value !== null && trim((string) $value) !== '')
        ->count();
    $completion = (int) round($filledBeautyFields / count($beauty) * 100);

    /* Customer-specific shipping updates and product announcements. */
    $shippingNotices = \App\Models\ShippingNotice::where('is_active', true)
        ->where('user_id', $user->id)
        ->latest()
        ->take(5)
        ->get();
    $announcements = \App\Models\Announcement::forCustomer($user->id)->take(5)->get();

    /* wishlist */
    $wishlist = $user->wishlist()->with('product.images')->latest()->take(4)->get()
        ->filter(fn ($w) => $w->product)
        ->map(fn ($w) => [
            'id'    => $w->product->id,
            'name'  => $w->product->name,
            'price' => (float) $w->product->final_price,
            'emoji' => $w->product->emoji,
            'img'   => $w->product->images->first()->path ?? null,
            'url'   => route('product.show', $w->product->slug),
        ])->values();

    /* notifications */
    $notifs = $paidOrders->take(3)->map(fn ($o) => [
        'color' => '#1f9d63',
        'text'  => 'Order <b>' . $o->order_number . '</b> — ' . $statusLbl[$o->status] . ' · Paid ✓',
        'time'  => $o->created_at->diffForHumans(),
    ])->values();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Dashboard — Cosmetics Store</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}?v=3">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Allura&display=swap" rel="stylesheet">

    @include('user.include.base')

    <style>
        html { overflow-x: hidden; }
        html, body { max-width: 100%; }

        .page-head h1 { color: var(--ink); font-size: 21px; font-weight: 600; letter-spacing: .3px; }
        .page-head p { font-size: 12.5px; color: rgba(22, 35, 60, .5); }

        .topbar { display: flex; align-items: center; gap: 14px; margin-bottom: 26px; min-width: 0; }
        .page-head { min-width: 0; flex: 1; }

        .hamburger { display: none; background: var(--glass); border: 1px solid var(--glass-border); border-radius: 12px; width: 42px; height: 42px; color: var(--ink); place-items: center; backdrop-filter: blur(14px); flex-shrink: 0; }

        .bell-wrap { position: relative; flex-shrink: 0; }
        .icon-btn { width: 42px; height: 42px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--glass); color: rgba(22, 35, 60, .7); display: grid; place-items: center; backdrop-filter: blur(14px); transition: all .3s; }
        .icon-btn:hover { color: var(--ink); border-color: rgba(56, 170, 180, .6); }
        .dot { position: absolute; top: 9px; right: 10px; width: 8px; height: 8px; border-radius: 50%; background: #ff6b81; box-shadow: 0 0 8px rgba(255, 107, 129, .7); }

        .notif-panel { position: absolute; right: 0; top: 52px; width: 340px; max-width: calc(100vw - 40px); padding: 0; border-radius: 18px; background: rgba(255, 255, 255, .98); border: 1px solid rgba(22, 35, 60, .1); box-shadow: 0 24px 60px rgba(31, 51, 89, .2); opacity: 0; visibility: hidden; transform: translateY(-8px); transition: opacity .2s ease, visibility .2s ease, transform .2s ease; z-index: 80; overflow: hidden; }
        .notif-panel.open { opacity: 1; visibility: visible; transform: none; }
        .notif-panel-head { display:flex;align-items:center;justify-content:space-between;gap:12px;padding:15px 16px;border-bottom:1px solid rgba(22,35,60,.08);background:linear-gradient(135deg,rgba(56,170,180,.08),rgba(139,92,246,.04)); }
        .notif-panel-head h4 { color: var(--ink); font-size: 13px; margin:0; }
        .notif-panel-head p { margin-top:3px;color:rgba(22,35,60,.5);font-size:10px;line-height:1.4; }
        .notif-count { flex:none;padding:5px 9px;border:1px solid rgba(56,170,180,.18);border-radius:999px;background:rgba(56,170,180,.1);color:var(--teal-2);font-size:10px;font-weight:700; }
        .notif-list { max-height: min(340px, calc(100dvh - 230px));overflow:auto;overscroll-behavior:contain;padding:7px; }
        .notif { display: flex; gap: 11px; padding: 11px 10px; border-radius: 12px; }
        .notif:hover { background: rgba(56, 170, 180, .06); }
        .notif i { width: 8px; height: 8px; border-radius: 50%; margin-top: 6px; flex-shrink: 0; }
        .notif p { font-size: 12px; color: rgba(22, 35, 60, .85); line-height: 1.5;overflow-wrap:anywhere; }
        .notif span { display:block;margin-top:3px;font-size: 10.5px; color: rgba(22, 35, 60, .5); }
        .notif-panel-foot { display:flex;justify-content:center;align-items:center;gap:7px;padding:12px;border-top:1px solid rgba(22,35,60,.08);color:var(--teal-2);font-size:11px;font-weight:700; }
        .notif-panel-foot:hover { background:rgba(56,170,180,.07); }
        .notif-badge { position:absolute;top:4px;right:4px;min-width:16px;height:16px;padding:0 4px;display:grid;place-items:center;border:2px solid #fff;border-radius:999px;background:#e0566e;color:#fff;font-size:8px;font-weight:800; }
        .bp-completion-note { margin:0 24px 20px;padding:11px 13px;border:1px solid #fecaca;border-left:4px solid #dc2626;border-radius:10px;background:#fff1f2;color:#b91c1c;font-size:11.5px;line-height:1.6;font-weight:600; }
        .bp-completion-note[hidden] { display:none; }

        /* SHIPPING NOTICES */
        .ship-notices { display:grid; gap:12px; margin-bottom:26px; min-width:0; }
        .sn-item { display:flex; gap:14px; padding:16px 20px; align-items:flex-start; border-left:4px solid var(--teal); }
        .sn-delay { border-left-color:#f0b429; }
        .sn-good  { border-left-color:#2ec181; }
        .sn-ic { font-size:24px; flex-shrink:0; }
        .sn-body { min-width:0; flex:1; }
        .sn-body b { color:var(--ink); font-size:13.5px; display:block; }
        .sn-body p { font-size:12.5px; color:var(--txt); margin-top:3px; line-height:1.6; }
        .sn-time { font-size:10.5px; color:rgba(22,35,60,.45); margin-top:5px; display:block; }

        /* BANNER */
        .banner { position: relative; overflow: hidden; display: flex; align-items: center; justify-content: space-between; gap: 30px; padding: 34px 38px; margin-bottom: 26px; min-width: 0; }
        .banner::before { content: 'glow'; position: absolute; right: 30px; top: -32px; font-family: 'Allura', cursive; font-size: 120px; color: rgba(56, 170, 180, .14); pointer-events: none; }
        .banner > div:first-child { min-width: 0; }
        .banner h2 { color: var(--ink); font-size: clamp(20px, 2.6vw, 27px); font-weight: 600; }
        .banner h2 em { font-style: normal; background: linear-gradient(90deg, #17a2b0, #8b5cf6); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
        .banner p { margin-top: 7px; max-width: 460px; font-size: 13.5px; }
        .banner-cta { display: inline-flex; align-items: center; gap: 9px; margin-top: 18px; padding: 12px 24px; border-radius: 12px; font-size: 12px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; color: #fff; background: linear-gradient(135deg, var(--teal), var(--teal-2)); box-shadow: 0 10px 24px rgba(56, 170, 180, .35); transition: all .3s; position: relative; z-index: 2; }
        .banner-cta:hover { transform: translateY(-2px); box-shadow: 0 16px 30px rgba(56, 170, 180, .45); }

        .rewards { flex: 1; max-width: 360px; position: relative; z-index: 2; min-width: 0; }
        .rewards-top { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 10px; gap: 10px; }
        .rewards-top b { color: var(--ink); font-size: 13.5px; }
        .rewards-top span { color: var(--teal-2); font-size: 12px; font-weight: 600; white-space: nowrap; }
        .progress { height: 10px; border-radius: 20px; background: rgba(22, 35, 60, .08); overflow: hidden; }
        .progress i { display: block; height: 100%; width: 0; border-radius: 20px; background: linear-gradient(90deg, var(--teal), var(--teal-light)); box-shadow: 0 0 14px rgba(56, 170, 180, .5); transition: width 1.4s cubic-bezier(.22, 1, .36, 1); }
        .tiers { display: flex; justify-content: space-between; margin-top: 9px; font-size: 10.5px; letter-spacing: 1px; text-transform: uppercase; color: rgba(22, 35, 60, .45); }
        .tiers b { color: var(--teal-2); }

        /* STATS */
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 26px; min-width: 0; }
        .stat { padding: 24px; min-width: 0; width: 100%; transition: transform .35s, border-color .35s, box-shadow .35s; }
        .stat:hover { transform: translateY(-5px); border-color: rgba(56, 170, 180, .5); box-shadow: 0 24px 50px rgba(31, 51, 89, .12); }
        .stat-ic { width: 44px; height: 44px; border-radius: 13px; display: grid; place-items: center; color: var(--teal-2); background: rgba(56, 170, 180, .12); border: 1px solid rgba(56, 170, 180, .3); margin-bottom: 16px; }
        .stat-row { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .stat-num { color: var(--ink); font-size: 30px; font-weight: 700; line-height: 1; }
        .stat-label { margin-top: 8px; font-size: 12px; letter-spacing: .6px; text-transform: uppercase; color: rgba(22, 35, 60, .5); }

        /* GRID 2 */
        .grid-2 { display: grid; grid-template-columns: 1.35fr 1fr; align-items: stretch; gap: 20px; margin-bottom: 26px; min-width: 0; }
        .grid-2 > .glass { min-width: 0; }
        .card-head { display: flex; align-items: center; justify-content: space-between; padding: 22px 24px 0; gap: 10px; }
        .card-head h3 { color: var(--ink); font-size: 15px; font-weight: 600; }
        .card-link { display: inline-flex; align-items: center; gap: 6px; font-size: 11.5px; font-weight: 500; color: var(--teal-2); white-space: nowrap; }
        .card-link:hover { color: var(--ink); }

        /* CHART */
        .chart { display: flex; align-items: flex-end; gap: 10px; height: 200px; padding: 26px 20px 16px; min-width: 0; }
        .bar { flex: 1; display: flex; flex-direction: column; justify-content: flex-end; align-items: center; gap: 8px; height: 100%; min-width: 0; }
        .bar i { display: block; width: 100%; max-width: 34px; height: var(--h, 0%); border-radius: 8px 8px 4px 4px; background: linear-gradient(180deg, var(--teal-light), var(--teal-2)); box-shadow: 0 0 16px rgba(56, 170, 180, .3); transition: filter .3s; }
        .bar:hover i { filter: brightness(1.15); }
        .bar em { font-style: normal; font-size: 10px; color: rgba(22, 35, 60, .45); }
        .chart-empty { text-align: center; padding: 60px 20px; color: rgba(22, 35, 60, .45); font-size: 13px; }

        /* PROFILE CARD */
        .profile-card { display: flex; flex-direction: column; min-width: 0; }
        .bp-body { padding: 20px 24px 24px; display: flex; gap: 22px; align-items: center; flex: 1; flex-wrap: wrap; }
        .ring-wrap { position: relative; width: 118px; height: 118px; flex-shrink: 0; }
        .ring-wrap svg { transform: rotate(-90deg); }
        .ring-bg { fill: none; stroke: rgba(22, 35, 60, .08); stroke-width: 9; }
        .ring-val { fill: none; stroke: url(#ringGrad); stroke-width: 9; stroke-linecap: round; stroke-dasharray: 339.29; stroke-dashoffset: 339.29; transition: stroke-dashoffset 1.6s cubic-bezier(.22, 1, .36, 1); }
        .ring-txt { position: absolute; inset: 0; display: grid; place-items: center; text-align: center; }
        .ring-txt b { color: var(--ink); font-size: 22px; font-weight: 700; display: block; line-height: 1; }
        .ring-txt span { font-size: 9.5px; letter-spacing: 1.2px; text-transform: uppercase; color: rgba(22, 35, 60, .5); }
        .bp-list { flex: 1; align-self: stretch; display: flex; flex-direction: column; justify-content: space-evenly; min-width: 170px; }
        .bp-list li { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px dashed rgba(22, 35, 60, .12); font-size: 12.5px; gap: 10px; }
        .bp-list li:last-child { border-bottom: none; }
        .bp-list li span { color: rgba(22, 35, 60, .5); }
        .bp-list li b { color: var(--ink); font-weight: 500; text-align: right; }
        .bp-note { font-size: 11px; color: rgba(22, 35, 60, .45); padding: 0 24px 20px; }
        .bp-edit { display: inline-flex; align-items: center; gap: 7px; padding: 9px 17px; border: 0; border-radius: 11px; background: linear-gradient(135deg,var(--teal),var(--teal-2)); color: #fff; box-shadow: 0 8px 20px rgba(56,170,180,.35); font: inherit; font-size: 11px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; cursor: pointer; transition: .3s; }
        .bp-edit svg { width: 13px; height: 13px; }
        .bp-edit:hover { transform: translateY(-2px); box-shadow: 0 12px 26px rgba(56,170,180,.45); }
        .bp-modal { position: fixed; inset: 0; z-index: 300; display: grid; place-items: center; padding: 20px; background: rgba(22,35,60,.45); backdrop-filter: blur(6px); opacity: 0; visibility: hidden; transition: all .3s; }
        .bp-modal.open { opacity: 1; visibility: visible; }
        .bp-modal-card { position: relative; width: min(460px,100%); max-height: 90vh; overflow-y: auto; padding: 34px 32px 30px; border: 1px solid var(--glass-border); border-radius: 22px; background: rgba(255,255,255,.92); backdrop-filter: blur(22px) saturate(140%); box-shadow: 0 34px 80px rgba(31,51,89,.3); transform: translateY(24px) scale(.95); transition: transform .35s cubic-bezier(.22,1,.36,1); }
        .bp-modal.open .bp-modal-card { transform: none; }
        .bp-modal-close { position: absolute; top: 16px; right: 16px; width: 34px; height: 34px; display: grid; place-items: center; border: 0; border-radius: 10px; background: rgba(22,35,60,.06); color: rgba(22,35,60,.55); cursor: pointer; transition: .25s; }
        .bp-modal-close:hover { background: rgba(224,86,110,.12); color: #e0566e; transform: rotate(90deg); }
        .bp-modal-tag { color: var(--teal); font-size: 11px; font-weight: 600; letter-spacing: 3px; text-align: center; text-transform: uppercase; }
        .bp-modal-title { margin: 6px 0 24px; color: var(--ink); font-size: 21px; font-weight: 600; text-align: center; }
        .bp-modal-title::after { content: ''; display: block; width: 44px; height: 3px; margin: 12px auto 0; border-radius: 3px; background: linear-gradient(90deg,var(--teal),var(--teal-light)); }
        .bp-field { margin-bottom: 17px; }
        .bp-field label { display: block; margin-bottom: 7px; color: rgba(22,35,60,.55); font-size: 11px; font-weight: 600; letter-spacing: 1.4px; text-transform: uppercase; }
        .bp-field input, .bp-field select { width: 100%; min-height: 44px; padding: 12px 15px; border: 1px solid rgba(22,35,60,.12); border-radius: 12px; background: rgba(22,35,60,.03); color: var(--ink); font: inherit; font-size: 14px; transition: border-color .3s,box-shadow .3s,background .3s; }
        .bp-field select { padding-right: 42px; background-position: right 15px center; }
        .bp-field input:focus, .bp-field select:focus { outline: none; border-color: rgba(56,170,180,.7); background: #fff; box-shadow: 0 0 0 4px rgba(56,170,180,.14); }
        .bp-save { width: 100%; min-height: 48px; margin-top: 6px; display: flex; align-items: center; justify-content: center; gap: 10px; border: 0; border-radius: 13px; background: linear-gradient(135deg,var(--teal),var(--teal-2)); color: #fff; box-shadow: 0 10px 24px rgba(56,170,180,.4); font: inherit; font-size: 12px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; cursor: pointer; transition: .3s; }
        .bp-save:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 14px 30px rgba(56,170,180,.5); }
        .bp-save:disabled { opacity: .65; cursor: wait; }
        .bp-save .spin { display: none; width: 15px; height: 15px; border: 2px solid rgba(255,255,255,.35); border-top-color: #fff; border-radius: 50%; animation: mSpin .7s linear infinite; }
        .bp-save.loading .spin { display: inline-block; }
        @keyframes mSpin { to { transform: rotate(360deg); } }

        /* ORDERS TABLE */
        .orders-card { margin-bottom: 26px; overflow: hidden; min-width: 0; }
        .table-scroll { overflow-x: auto; padding: 10px 6px 6px; max-width: 100%; -webkit-overflow-scrolling: touch; }
        table { width: 100%; border-collapse: collapse; min-width: 640px; }
        th { text-align: left; font-size: 10.5px; font-weight: 600; letter-spacing: 1.6px; text-transform: uppercase; color: rgba(22, 35, 60, .45); padding: 14px 18px; border-bottom: 1px solid rgba(22, 35, 60, .1); white-space: nowrap; }
        td { padding: 15px 18px; font-size: 13px; color: rgba(22, 35, 60, .78); border-bottom: 1px solid rgba(22, 35, 60, .06); white-space: nowrap; }
        tbody tr:hover { background: rgba(56, 170, 180, .06); }
        tbody tr:last-child td { border-bottom: none; }
        td.oid { color: var(--teal-2); font-weight: 600; }
        td.tot { color: var(--ink); font-weight: 600; }
        .tbl-hint { display: none; text-align: center; font-size: 11px; color: rgba(22, 35, 60, .4); padding: 4px 10px 10px; }

        .badge { display: inline-flex; align-items: center; gap: 7px; padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; letter-spacing: .4px; }
        .badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; box-shadow: 0 0 8px currentColor; }
        .badge.ok { color: #1f9d63; background: rgba(46, 193, 129, .12); border: 1px solid rgba(46, 193, 129, .35); }
        .badge.ship { color: #178f9d; background: rgba(56, 170, 180, .12); border: 1px solid rgba(56, 170, 180, .4); }
        .badge.proc { color: #b5831f; background: rgba(240, 180, 41, .15); border: 1px solid rgba(240, 180, 41, .4); }
        .badge.bad { color: #e0566e; background: rgba(255, 107, 129, .12); border: 1px solid rgba(255, 107, 129, .4); }
        .view-link { font-size: 12px; color: var(--teal-2); }
        .view-link:hover { color: var(--ink); }

        /* WISHLIST */
        .wish-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; min-width: 0; }
        .wish-card { overflow: hidden; transition: transform .35s, box-shadow .35s, opacity .38s; min-width: 0; }
        .wish-card:hover { transform: translateY(-5px); box-shadow: 0 26px 55px rgba(31, 51, 89, .14); }
        .wish-card.removing-card { opacity: 0; transform: scale(.9); }
        .wish-thumb { position: relative; aspect-ratio: 3 / 3.2; overflow: hidden; }
        .wish-thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform .6s ease; }
        .wish-thumb.emo { display: grid; place-items: center; background: linear-gradient(135deg, #e9f6f8, #fdf3e7); }
        .emo-big { font-size: 54px; }
        .wish-card:hover .wish-thumb img { transform: scale(1.08); }
        .heart-btn { position: absolute; top: 12px; right: 12px; width: 36px; height: 36px; border-radius: 50%; border: none; background: rgba(255, 255, 255, .85); box-shadow: 0 4px 12px rgba(31, 51, 89, .15); color: #ff6b81; display: grid; place-items: center; transition: all .3s; }
        .heart-btn:hover { transform: scale(1.15); }
        .add-bag { position: absolute; left: 0; right: 0; bottom: 0; padding: 11px; border: none; background: #38aab4; color: #fff; font-size: 11px; font-weight: 600; letter-spacing: 1.6px; text-transform: uppercase; transform: translateY(100%); transition: transform .35s, background .3s; }
        @media (hover: hover) { .wish-card:hover .add-bag { transform: none; } }
        @media (hover: none) { .add-bag { transform: none; } }
        .wish-info { padding: 15px 17px 18px; }
        .wish-info h4 { color: var(--ink); font-size: 13.5px; font-weight: 500; margin-bottom: 5px; }
        .wish-info .price { color: var(--teal-2); font-size: 13.5px; font-weight: 600; }
        .wish-empty { grid-column: 1 / -1; text-align: center; padding: 44px 20px; font-size: 13.5px; }

        #udToast { position: fixed; left: 50%; bottom: 28px; transform: translate(-50%, 20px); background: #16233c; color: #fff; padding: 12px 22px; border-radius: 14px; font-size: 13px; opacity: 0; pointer-events: none; transition: all .35s; z-index: 200; box-shadow: 0 14px 30px rgba(22, 35, 60, .3); max-width: 90vw; text-align: center; }
        #udToast.show { opacity: 1; transform: translate(-50%, 0); }

        .foot-note { text-align: center; margin-top: 34px; font-size: 12px; color: rgba(22, 35, 60, .4); }

        /* ═════════ ⭐ FULL RESPONSIVE — table + wishlist + profile ═════════ */
        @media (max-width: 1100px) {
            .stats { grid-template-columns: repeat(2, 1fr); }
            .grid-2 { grid-template-columns: 1fr; }
            .wish-grid { grid-template-columns: repeat(2, 1fr); }
            .banner { flex-direction: column; align-items: flex-start; }
            .rewards { max-width: 100%; width: 100%; }
        }

        @media (max-width: 860px) {
            .main { padding: 20px 16px 50px;
                    width: 100%;
                    min-width: 0; }
            .banner { padding: 24px 20px; }
            .banner::before { font-size: 80px; }
            .hamburger { display: grid; }
        }

        /* ⭐ ORDERS TABLE — mobile COMPACT (media query AT ANY COST) */
        @media (max-width: 760px) {
            .orders-card .card-head { padding: 18px 16px 0; }
            .table-scroll { padding: 8px 2px 4px; }
            table { min-width: 540px; }
            th { padding: 11px 12px; font-size: 9.5px; letter-spacing: 1.2px; }
            td { padding: 12px 12px; font-size: 12px; }
            .badge { padding: 4px 9px; font-size: 10px; }
            td.oid, td.tot { font-size: 12px; }
        }

        @media (max-width: 560px) {
            .notif-panel { position:fixed;top:calc(env(safe-area-inset-top,0px) + 76px);left:12px;right:12px;width:auto;max-width:none;max-height:calc(100dvh - 92px);overflow-y:auto; }
            .notif-panel-head { align-items:flex-start;padding:13px 14px; }
            .notif-list { max-height:calc(100dvh - 220px); }
            .bp-completion-note { margin:0 16px 16px;font-size:11px; }
            .stats { grid-template-columns: 1fr; gap: 14px; }
            .stat { padding: 20px; }
            .stat-num { font-size: 26px; }
            .wish-grid { grid-template-columns: 1fr; }
            .page-head h1 { font-size: 17px; }
            .chart { gap: 6px; padding: 20px 10px 14px; height: 170px; }
            .bar em { font-size: 8.5px; }
            .tbl-hint { display: block; }
            /* ⭐ PROFILE CARD mobile — ring center + list full width */
            .bp-body { flex-direction: column; text-align: center; }
            .bp-list { width: 100%; }
            .bp-list li b { text-align: right; }
        }

        @media (max-width: 400px) {
            table { min-width: 480px; }
            td { padding: 10px 10px; font-size: 11.5px; }
        }

        @media (prefers-reduced-motion: reduce) {
            * { animation: none !important; transition: none !important; }
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

            <!-- Topbar -->
            <header class="topbar reveal show">
                <button class="hamburger" id="hamburger" aria-label="Open menu">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <div class="page-head">
                    <h1><span id="greetTime">Welcome back</span>, {{ $firstName }} ✨</h1>
                    <p>{{ now()->format('l, j F Y') }} · Member since {{ $user->created_at->format('F Y') }}</p>
                </div>

                <div class="bell-wrap">
                    <button class="icon-btn" id="bellBtn" aria-label="Notifications" aria-controls="notifPanel" aria-expanded="false">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
                        @if ($notifs->count())<span class="notif-badge" aria-hidden="true">{{ $notifs->count() }}</span>@endif
                    </button>
                    <div class="notif-panel" id="notifPanel" role="region" aria-label="Recent order notifications">
                        <div class="notif-panel-head"><div><h4>Notifications</h4><p>Updates from your paid orders</p></div><span class="notif-count">{{ $notifs->count() }} {{ \Illuminate\Support\Str::plural('update', $notifs->count()) }}</span></div>
                        <div class="notif-list">
                            @forelse ($notifs as $n)
                            <div class="notif"><i style="background:{{ $n['color'] }}"></i><div><p>{!! $n['text'] !!}</p><span>{{ $n['time'] }}</span></div></div>
                            @empty
                            <div class="notif"><i style="background:#178f9d"></i><div><p>No notifications yet</p><span>Paid order updates will appear here.</span></div></div>
                            @endforelse
                        </div>
                        <a href="{{ route('user.orders') }}" class="notif-panel-foot">View order history
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </header>

            {{-- PRODUCT ANNOUNCEMENTS --}}
            @if ($announcements->count())
            <section class="ship-notices" aria-label="Announcements">
                @foreach ($announcements as $announcement)
                <div class="sn-item glass reveal sn-{{ $announcement->type }}">
                    <span class="sn-ic">{{ $announcement->type === 'delay' ? '⚠️' : ($announcement->type === 'good' ? '✨' : '📢') }}</span>
                    <div class="sn-body">
                        <b>{{ $announcement->title }}</b>
                        <p>{{ $announcement->message }}</p>
                        <span class="sn-time">
                            For {{ $announcement->categories->pluck('name')->join(', ') }}
                            · {{ $announcement->created_at->diffForHumans() }}
                        </span>
                    </div>
                </div>
                @endforeach
            </section>
            @endif

            {{-- PERSONAL SHIPPING UPDATES --}}
            @if ($shippingNotices->count())
            <section class="ship-notices">
                @foreach ($shippingNotices as $sn)
                <div class="sn-item glass reveal sn-{{ $sn->type }}">
                    <span class="sn-ic">{{ $sn->type === 'delay' ? '🚚' : ($sn->type === 'good' ? '✨' : '📢') }}</span>
                    <div class="sn-body">
                        <b>{{ $sn->title }}</b>
                        <p>{{ $sn->message }}</p>
                        <span class="sn-time">{{ $sn->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                @endforeach
            </section>
            @endif

            <!-- Banner + Rewards -->
            <section class="banner glass reveal" style="--d:.05s">
                <div>
                    <h2>Your Beauty Hub, <em>{{ $firstName }}</em></h2>
                    <p>Track your paid orders, wishlist and glow rewards — all in one beautiful place.</p>
                    <a href="{{ url('/shop') }}" class="banner-cta">
                        Continue Shopping
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </a>
                </div>

                <div class="rewards">
                    <div class="rewards-top">
                        <b>Glow Rewards</b>
                        <span>{{ number_format($points) }} / {{ number_format($nextGoal) }} pts</span>
                    </div>
                    <div class="progress"><i id="rewardsBar" data-w="{{ $tierPct }}"></i></div>
                    <div class="tiers"><span>Silver</span><b>{{ $tierName }} — {{ $tierPct }}%</b><span>Platinum</span></div>
                </div>
            </section>

            <!-- Stats — PAID ONLY -->
            <section class="stats">
                <div class="stat glass reveal" style="--d:.1s">
                    <div class="stat-ic"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 7h12l1.5 13.5a1 1 0 0 1-1 1.1H5.5a1 1 0 0 1-1-1.1L6 7z"/><path d="M9 10V6a3 3 0 0 1 6 0v4"/></svg></div>
                    <div class="stat-row"><span class="stat-num">{{ $totalOrders }}</span></div>
                    <p class="stat-label">Paid Orders</p>
                </div>
                <div class="stat glass reveal" style="--d:.17s">
                    <div class="stat-ic" style="color:#178f9d;background:rgba(56,170,180,.12);border-color:rgba(56,170,180,.4)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg></div>
                    <div class="stat-row"><span class="stat-num">{{ $inTransitN }}</span></div>
                    <p class="stat-label">In Transit</p>
                </div>
                <div class="stat glass reveal" style="--d:.24s">
                    <div class="stat-ic" style="color:#1f9d63;background:rgba(46,193,129,.12);border-color:rgba(46,193,129,.3)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20 6 9 17l-5-5"/></svg></div>
                    <div class="stat-row"><span class="stat-num">{{ $deliveredN }}</span></div>
                    <p class="stat-label">Delivered</p>
                </div>
                <div class="stat glass reveal" style="--d:.31s">
                    <div class="stat-ic" style="color:#8b5cf6;background:rgba(139,92,246,.12);border-color:rgba(139,92,246,.3)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><line x1="12" y1="2" x2="12" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
                    <div class="stat-row"><span class="stat-num">£{{ number_format($spend, 2) }}</span></div>
                    <p class="stat-label">Total Spent</p>
                </div>
            </section>

            <!-- Chart + Beauty Profile -->
            <div class="grid-2">
                <div class="glass reveal" style="--d:.15s">
                    <div class="card-head">
                        <h3>Order Activity</h3>
                        <span>Last 8 months · paid orders</span>
                    </div>
                    @if ($totalOrders > 0)
                    <div class="chart">
                        @foreach ($chart as $c)
                        <div class="bar">
                            <i style="--h: {{ $c['h'] }}%"></i>
                            <em>{{ $c['m'] }}</em>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="chart-empty">No paid orders yet — your activity chart will bloom here 🌸</div>
                    @endif
                </div>

                <div class="glass profile-card reveal" style="--d:.22s">
                    <div class="card-head">
                        <h3>Beauty Profile</h3>
                        <button type="button" class="bp-edit" id="editBeautyBtn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 3a2.8 2.8 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                            Edit
                        </button>
                    </div>
                    <div class="bp-body">
                        <div class="ring-wrap">
                            <svg width="118" height="118" viewBox="0 0 118 118">
                                <defs>
                                    <linearGradient id="ringGrad" x1="0" y1="0" x2="1" y2="1">
                                        <stop offset="0%" stop-color="#38aab4"/>
                                        <stop offset="100%" stop-color="#2b7fb8"/>
                                    </linearGradient>
                                </defs>
                                <circle class="ring-bg" cx="59" cy="59" r="54"/>
                                <circle class="ring-val" id="ringVal" cx="59" cy="59" r="54"/>
                            </svg>
                            <div class="ring-txt"><div><b id="ringNum">{{ $completion }}%</b><span>Complete</span></div></div>
                        </div>
                        <ul class="bp-list">
                            @foreach ($beauty as $row)
                            <li><span>{{ $row[0] }}</span><b>{{ $row[1] }}</b></li>
                            @endforeach
                        </ul>
                    </div>
                    <p class="bp-completion-note" id="beautyCompletionNote" role="status" {{ $completion >= 100 ? 'hidden' : '' }}>Your <strong>Beauty Profile</strong> is currently <strong>{{ $completion }}% complete</strong>—please click the <strong>Edit</strong> button to set your skin type, hair type, shade match, and preferences so we can personalize your experience.</p>
                </div>
            </div>

            <!-- Recent Orders — PAID ONLY -->
            <section class="orders-card glass reveal" style="--d:.18s">
                <div class="card-head">
                    <h3>Recent Paid Orders</h3>
                    <a href="{{ route('user.orders') }}" class="card-link">View all
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>
                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentOrders as $o)
                            <tr>
                                <td class="oid">{{ $o->order_number }}</td>
                                <td>{{ $o->created_at->format('M d, Y') }}</td>
                                <td>{{ $o->items->count() }}</td>
                                <td class="tot">£{{ number_format($o->total, 2) }}</td>
                                <td><span class="badge {{ $badgeMap[$o->status] ?? 'proc' }}">{{ $statusLbl[$o->status] ?? ucfirst($o->status) }}</span></td>
                                <td><a href="{{ route('user.orders') }}" class="view-link">View →</a></td>
                            </tr>
                            @empty
                            <tr><td colspan="6" style="text-align:center; padding:34px;">No paid orders yet — complete a payment and it appears here 💫</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <p class="tbl-hint">← swipe to see more →</p>
            </section>

            <!-- Wishlist -->
            <section>
                <div class="card-head reveal" style="--d:.1s; padding: 0 4px 16px;">
                    <h3 style="color:var(--ink); font-size:16px;">My Wishlist</h3>
                    <a href="{{ route('user.wishlist') }}" class="card-link">View all
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>

                <div class="wish-grid" id="dashboardWishGrid">
                    @forelse ($wishlist as $i => $w)
                    <div class="wish-card glass reveal" style="--d:{{ 0.12 + $i * 0.07 }}s">
                        <div class="wish-thumb {{ $w['img'] ? '' : 'emo' }}" style="cursor:pointer" onclick="window.location='{{ $w['url'] }}'">
                            @if ($w['img'])
                            <img src="{{ asset('storage/' . $w['img']) }}" alt="{{ $w['name'] }}">
                            @else
                            <span class="emo-big">{{ $w['emoji'] ?: '🧴' }}</span>
                            @endif
                            <button class="heart-btn" onclick="udRemove(event, {{ $w['id'] }}, this)" aria-label="Remove from wishlist">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21.2l7.8-7.7 1-1.1a5.5 5.5 0 0 0 0-7.8z"/></svg>
                            </button>
                            <button class="add-bag" onclick="udAdd(event, {{ $w['id'] }})">Add to Bag</button>
                        </div>
                        <div class="wish-info">
                            <h4>{{ $w['name'] }}</h4>
                            <p class="price">£{{ number_format($w['price'], 2) }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="wish-empty glass">Your wishlist is empty — <a href="{{ url('/shop') }}" style="color:var(--teal-2); font-weight:600;">go find something gorgeous 💄</a></div>
                    @endforelse
                </div>
            </section>

            <p class="foot-note">Cosmetics Store · Beauty Pronounced 💫</p>
        </main>
    </div>

    <div id="udToast"></div>

    <div class="bp-modal" id="beautyModal" aria-hidden="true">
        <section class="bp-modal-card" role="dialog" aria-modal="true" aria-labelledby="beautyModalTitle">
            <button type="button" class="bp-modal-close" id="beautyModalClose" aria-label="Close">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
            <p class="bp-modal-tag">Beauty Profile</p>
            <h3 class="bp-modal-title" id="beautyModalTitle">Your Preferences</h3>
            <form id="beautyForm" action="{{ route('user.profile.prefs') }}">
                <div class="bp-field">
                    <label for="beautySkin">Skin Type</label>
                    <select id="beautySkin" name="skin_type">
                        <option value="" @selected(!$user->skin_type)>Choose skin type</option>
                        @foreach (['Combination', 'Dry', 'Oily', 'Normal', 'Sensitive'] as $option)
                            <option value="{{ $option }}" @selected($user->skin_type === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="bp-field">
                    <label for="beautyHair">Hair Type</label>
                    <select id="beautyHair" name="hair_type">
                        <option value="" @selected(!$user->hair_type)>Choose hair type</option>
                        @foreach (['Wavy', 'Straight', 'Curly', 'Coily'] as $option)
                            <option value="{{ $option }}" @selected($user->hair_type === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="bp-field">
                    <label for="beautyShade">Shade Match</label>
                    <input id="beautyShade" type="text" name="shade_match" maxlength="50" value="{{ $user->shade_match }}" placeholder="e.g. Warm Beige">
                </div>
                <div class="bp-field">
                    <label for="beautyLoves">Beauty Interests</label>
                    <input id="beautyLoves" type="text" name="loves" maxlength="50" value="{{ $user->loves }}" placeholder="e.g. Skincare">
                </div>
                <button type="submit" class="bp-save" id="beautySave">
                    <span class="spin"></span>
                    Save Preferences
                </button>
            </form>
        </section>
    </div>

    <script>
        (function () {
            var started = false;
            var CSRF = document.querySelector('meta[name="csrf-token"]').content;
            var WISH_REMOVE = @json(route('user.dashboard.wishlist.remove'));
            var CART_ADD = @json(route('cart.add'));
            /* ⭐⭐⭐ ONE LINE — pehle multi-line tha, POORA SCRIPT MARA HUA THA!
               Isi wajah se: rewards bar nahi chalti thi, hamburger dead tha, bell dead tha */
            var RING = @json((int) $completion);
            var BEAUTY_UPDATE = @json(route('user.profile.prefs'));

            function udToast(msg) {
                if (typeof window.showToast === 'function') {
                    window.showToast(msg, 'success');
                    return;
                }
                var t = document.getElementById('udToast');
                if (!t) return;
                t.textContent = msg;
                t.classList.add('show');
            }

            function updateWishBadge(count) {
                var badge = document.querySelector('[data-wishlist-count]');
                if (!badge) return;
                badge.textContent = count;
                badge.title = count + ' saved item' + (count === 1 ? '' : 's');
                badge.hidden = count === 0;
            }

            function setBeautyCompletion(value) {
                RING = value;
                var ring = document.getElementById('ringVal');
                var ringNum = document.getElementById('ringNum');
                if (ring) ring.style.strokeDashoffset = 339.29 - (339.29 * value / 100);
                if (ringNum) ringNum.textContent = value + '%';
                var completionNote = document.getElementById('beautyCompletionNote');
                if (completionNote) {
                    completionNote.hidden = value >= 100;
                    completionNote.innerHTML = 'Your <strong>Beauty Profile</strong> is currently <strong>' + value + '% complete</strong>—please click the <strong>Edit</strong> button to set your skin type, hair type, shade match, and preferences so we can personalize your experience.';
                }
            }

            var beautyModal = document.getElementById('beautyModal');
            var beautyForm = document.getElementById('beautyForm');
            var editBeautyBtn = document.getElementById('editBeautyBtn');
            var closeBeautyBtn = document.getElementById('beautyModalClose');
            function closeBeautyModal() {
                if (beautyModal) {
                    beautyModal.classList.remove('open');
                    beautyModal.setAttribute('aria-hidden', 'true');
                }
            }
            if (editBeautyBtn && beautyModal) editBeautyBtn.addEventListener('click', function () {
                beautyModal.classList.add('open');
                beautyModal.setAttribute('aria-hidden', 'false');
                var firstField = beautyForm.querySelector('select');
                if (firstField) firstField.focus();
            });
            if (closeBeautyBtn) closeBeautyBtn.addEventListener('click', closeBeautyModal);
            if (beautyModal) beautyModal.addEventListener('click', function (event) {
                if (event.target === beautyModal) closeBeautyModal();
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') closeBeautyModal();
            });
            if (beautyForm) beautyForm.addEventListener('submit', function (event) {
                event.preventDefault();
                var saveButton = document.getElementById('beautySave');
                saveButton.disabled = true;
                saveButton.classList.add('loading');
                var formData = new FormData(beautyForm);
                formData.append('_method', 'PATCH');
                fetch(BEAUTY_UPDATE, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData,
                    credentials: 'same-origin'
                }).then(function (response) {
                    return response.json().catch(function () { return {}; }).then(function (data) {
                        if (!response.ok) {
                            var validationMessage = data.errors && Object.keys(data.errors).length
                                ? data.errors[Object.keys(data.errors)[0]][0] : null;
                            throw new Error(validationMessage || data.message || 'Could not save your beauty profile.');
                        }
                        return data;
                    });
                }).then(function (data) {
                    var values = [
                        data.user.skin_type,
                        data.user.hair_type,
                        data.user.shade_match,
                        data.user.loves
                    ];
                    document.querySelectorAll('.bp-list li b').forEach(function (label, index) {
                        label.textContent = values[index] || 'Not set';
                    });
                    setBeautyCompletion(values.filter(function (value) {
                        return value && value.trim();
                    }).length * 25);
                    closeBeautyModal();
                    udToast(data.message || 'Beauty profile saved.');
                }).catch(function (error) {
                    udToast(error.message || 'Could not save your beauty profile.');
                }).finally(function () {
                    saveButton.disabled = false;
                    saveButton.classList.remove('loading');
                });
            });

            window.udRemove = function (e, pid, btn) {
                e.stopPropagation(); e.preventDefault();
                if (btn.disabled) return;
                btn.disabled = true;
                var fd = new FormData();
                fd.append('product_id', pid);
                fd.append('_token', CSRF);
                fetch(WISH_REMOVE, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd,
                    credentials: 'same-origin'
                })
                    .then(function (r) {
                        return r.json().catch(function () { return {}; }).then(function (data) {
                            if (!r.ok || !data.ok) throw new Error(data.message || 'Could not remove this item.');
                            return data;
                        });
                    })
                    .then(function (data) {
                        var card = btn.closest('.wish-card');
                        if (card) {
                            card.classList.add('removing-card');
                            setTimeout(function () {
                                card.remove();
                                var grid = document.getElementById('dashboardWishGrid');
                                if (grid && !grid.querySelector('.wish-card')) {
                                    var empty = document.createElement('div');
                                    empty.className = 'wish-empty glass';
                                    empty.textContent = data.count > 0
                                        ? 'No recent saved items remain. '
                                        : 'Your wishlist is empty — ';
                                    var link = document.createElement('a');
                                    link.href = "{{ route('user.wishlist') }}";
                                    link.textContent = data.count > 0 ? 'View your full wishlist' : 'Explore products';
                                    link.style.color = 'var(--teal-2)';
                                    link.style.fontWeight = '600';
                                    empty.appendChild(link);
                                    grid.appendChild(empty);
                                }
                            }, 400);
                        }
                        updateWishBadge(data.count);
                        udToast('Removed from wishlist 💔');
                    })
                    .catch(function (error) { udToast(error.message || 'Could not remove — try again'); })
                    .finally(function () { btn.disabled = false; });
            };

            window.udAdd = function (e, pid) {
                e.stopPropagation(); e.preventDefault();
                var fd = new FormData();
                fd.append('product_id', pid);
                fd.append('qty', 1);
                fd.append('_token', CSRF);
                fetch(CART_ADD, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }, body: fd })
                    .then(function (r) {
                        return r.json().catch(function () { return {}; }).then(function (data) {
                            if (!r.ok) throw new Error(data.message || 'Could not add this item to your bag.');
                            return data;
                        });
                    })
                    .then(function () { udToast('Added to bag 🛍️'); })
                    .catch(function (error) { udToast(error.message || 'Could not add to bag — try again.'); });
            };

            function initDashboard() {
                if (started) return;
                started = true;

                (function () {
                    var h = new Date().getHours();
                    var g = h < 12 ? 'Good morning' : h < 18 ? 'Good afternoon' : 'Good evening';
                    var el = document.getElementById('greetTime');
                    if (el) el.textContent = g;
                })();

                var sidebar = document.getElementById('sidebar');
                var overlay = document.getElementById('overlay');
                var hamburger = document.getElementById('hamburger');
                function closeSide() { if (sidebar) sidebar.classList.remove('open'); if (overlay) overlay.classList.remove('show'); }
                if (hamburger && sidebar && overlay) {
                    hamburger.addEventListener('click', function () { sidebar.classList.toggle('open'); overlay.classList.toggle('show'); });
                    overlay.addEventListener('click', closeSide);
                }

                var bell = document.getElementById('bellBtn');
                var panel = document.getElementById('notifPanel');
                if (bell && panel) {
                    bell.addEventListener('click', function (e) {
                        e.stopPropagation();
                        var open = !panel.classList.contains('open');
                        panel.classList.toggle('open', open);
                        bell.setAttribute('aria-expanded', String(open));
                    });
                }
                if (!window.__udDocBound) {
                    window.__udDocBound = true;
                    document.addEventListener('click', function (e) {
                        var openPanel = document.querySelector('.notif-panel.open');
                        if (openPanel && !openPanel.contains(e.target) && !e.target.closest('#bellBtn')) {
                            openPanel.classList.remove('open');
                            var trigger = document.getElementById('bellBtn');
                            if (trigger) trigger.setAttribute('aria-expanded', 'false');
                        }
                    });
                    document.addEventListener('keydown', function (e) {
                        if (e.key !== 'Escape') return;
                        var openPanel = document.querySelector('.notif-panel.open');
                        if (openPanel) openPanel.classList.remove('open');
                        var trigger = document.getElementById('bellBtn');
                        if (trigger) { trigger.setAttribute('aria-expanded', 'false'); trigger.focus(); }
                    });
                }

                /* ⭐ rewards bar — ab chalegi (script zinda hai) */
                var bar = document.getElementById('rewardsBar');
                if (bar) setTimeout(function () { bar.style.width = bar.dataset.w + '%'; }, 300);

                /* ⭐ ring — ab chalta hai */
                var ring = document.getElementById('ringVal');
                var ringNum = document.getElementById('ringNum');
                if (ring && RING > 0) {
                    setTimeout(function () {
                        ring.style.strokeDashoffset = 339.29 - (339.29 * RING / 100);
                        if (ringNum) {
                            var n = 0;
                            var iv = setInterval(function () { n++; ringNum.textContent = n + '%'; if (n >= RING) clearInterval(iv); }, Math.max(8, 1400 / Math.max(1, RING)));
                        }
                    }, 300);
                } else if (ringNum) ringNum.textContent = '0%';
            }

            if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initDashboard);
            else initDashboard();
        })();
    </script>
</body>

</html>