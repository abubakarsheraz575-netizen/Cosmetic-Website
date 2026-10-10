{{-- ==========================================================
     ADMIN · CATEGORIES — v10.5
     Category icons use the site's Remix Icon font and store icon classes.
     ★ 100% AJAX: add/edit/delete — no page refresh
========================================================== --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0e5a75">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Categories — NiceDash Admin</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}?v=3">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style data-page>
        *{margin:0;padding:0;box-sizing:border-box}
        :root{--bg:#f3f5f7;--card:#fff;--text:#101828;--muted:#68788f;--border:#e8edf3;--forest:#0e5a75;--forest-2:#17739b;--lime:#3ec5e0;--lime-soft:#e4f6fb;--shadow:0 1px 2px rgba(16,24,40,.04),0 8px 24px rgba(16,24,40,.05);--radius:16px}
        body.dark{--bg:#0b1220;--card:#111c2e;--text:#e7edf5;--muted:#8ba0b8;--border:#1e2c42;--lime-soft:rgba(62,197,224,.08)}
        body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);font-size:14px;-webkit-text-size-adjust:100%;-webkit-tap-highlight-color:transparent;overflow-x:clip}
        body.nb-lock{overflow:hidden}
        a{text-decoration:none;color:inherit}
        button{font-family:inherit;user-select:none}
        .layout{min-height:100vh}
        .main{margin-left:268px;min-width:0;transition:margin .35s cubic-bezier(.4,0,.2,1)}
        .layout.collapsed .main{margin-left:84px}
        .content{padding:24px 26px calc(40px + env(safe-area-inset-bottom));display:grid;gap:22px;min-width:0}
        .sidebar{position:fixed;left:0;top:0;bottom:0;width:268px;background:var(--card);border-right:1px solid var(--border);display:flex;flex-direction:column;z-index:50;transition:width .35s,transform .35s}
        .layout.collapsed .sidebar{width:84px}
        .s-nav{flex:1;overflow-y:auto;padding:12px 14px}
        .backdrop{position:fixed;inset:0;background:rgba(2,6,23,.55);z-index:45;opacity:0;visibility:hidden;pointer-events:none;transition:.3s}
        .backdrop.show{opacity:1;visibility:visible;pointer-events:auto}
        @keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:none}}
        .fx{animation:fadeUp .55s cubic-bezier(.22,1,.36,1) both}
        .d1{animation-delay:.05s}.d2{animation-delay:.1s}.d3{animation-delay:.15s}
        .card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow);min-width:0}
        .page-head{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px}
        .page-head h2{font-size:24px;font-weight:800;letter-spacing:-.4px;display:flex;align-items:center;gap:11px}
        .ph-ic{width:42px;height:42px;border-radius:13px;display:grid;place-items:center;background:linear-gradient(135deg,var(--forest),var(--forest-2));color:var(--lime);font-size:21px;box-shadow:0 10px 22px rgba(14,90,117,.3)}
        .page-head p{color:var(--muted);font-size:13px;margin-top:3px}
        .btn{padding:11px 20px;border-radius:12px;font-weight:800;font-size:13px;cursor:pointer;border:none;transition:.25s;display:inline-flex;align-items:center;gap:8px}
        .btn-lime{background:linear-gradient(135deg,var(--lime),#3fb0d8);color:#08394b;box-shadow:0 8px 20px rgba(62,197,224,.35)}
        .btn-lime:hover{transform:translateY(-2px)}
        .r-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:22px}
        .stat{position:relative;overflow:hidden;padding:22px}
        .stat::after{content:'';position:absolute;right:-34px;top:-34px;width:110px;height:110px;border-radius:50%;background:radial-gradient(circle,rgba(62,197,224,.14),transparent 70%)}
        .stat-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px}
        .stat .icon{width:46px;height:46px;border-radius:14px;display:grid;place-items:center;font-size:21px}
        .i-green{background:#e0f5fb;color:#0e8fb5}
        .i-blue{background:#e8f1fd;color:#2563eb}
        .i-red{background:#feecec;color:#dc2626}
        .i-violet{background:#f1ecfd;color:#7c3aed}
        body.dark .i-green{background:rgba(62,197,224,.12);color:#4ddcf5}
        body.dark .i-blue{background:rgba(96,165,250,.12);color:#60a5fa}
        body.dark .i-red{background:rgba(248,113,113,.12);color:#f87171}
        body.dark .i-violet{background:rgba(167,139,250,.12);color:#a78bfa}
        .big{font-size:29px;font-weight:800;letter-spacing:-.6px}
        .big.sm{font-size:23px}
        .muted{color:var(--muted)}
        .small{font-size:12.5px}
        @keyframes statPop{0%{transform:scale(.6);opacity:.2}60%{transform:scale(1.18)}100%{transform:scale(1);opacity:1}}
        .stat-pop{animation:statPop .45s cubic-bezier(.34,1.56,.64,1)}
        .dd{position:relative}
        .dd-btn{display:inline-flex;align-items:center;gap:9px;border:1px solid var(--border);background:var(--bg);color:var(--text);font:700 12.5px 'Plus Jakarta Sans',sans-serif;padding:11px 16px;border-radius:12px;cursor:pointer;transition:.25s;min-width:140px;max-width:100%}
        .dd-btn:hover{border-color:var(--lime)}
        .dd.open .dd-btn{border-color:var(--lime);box-shadow:0 0 0 4px rgba(62,197,224,.15)}
        .dd-btn .dd-chev{margin-left:auto;transition:transform .3s;color:var(--muted);font-size:16px}
        .dd.open .dd-chev{transform:rotate(180deg);color:var(--forest)}
        body.dark .dd.open .dd-chev{color:var(--lime)}
        .dd-menu{position:absolute;top:calc(100% + 8px);left:0;min-width:100%;background:var(--card);border:1px solid var(--border);border-radius:14px;box-shadow:0 18px 44px rgba(16,24,40,.18);padding:6px;opacity:0;visibility:hidden;transform:translateY(8px) scale(.97);transform-origin:top left;transition:.28s cubic-bezier(.22,1,.36,1);z-index:70}
        .dd.open .dd-menu{opacity:1;visibility:visible;transform:none}
        .dd-item{display:flex;align-items:center;gap:9px;width:100%;text-align:left;padding:10px 14px;border-radius:10px;border:none;background:transparent;color:var(--text);font:600 12.5px 'Plus Jakarta Sans',sans-serif;cursor:pointer;transition:.18s;white-space:nowrap}
        .dd-item:hover{background:var(--lime-soft);color:var(--forest)}
        body.dark .dd-item:hover{color:var(--lime)}
        .dd-item.on{background:linear-gradient(135deg,var(--forest),var(--forest-2));color:#fff;font-weight:800}
        .dd-item.on::after{content:'✓';margin-left:auto;font-size:11px}
        .table-card{overflow:hidden}
        .toolbar{display:flex;align-items:center;gap:12px;flex-wrap:wrap;padding:18px 22px;border-bottom:1px solid var(--border)}
        .t-search{display:flex;align-items:center;gap:10px;background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:11px 14px;width:280px;max-width:100%;transition:.3s;color:var(--muted)}
        .t-search:focus-within{border-color:var(--lime);box-shadow:0 0 0 4px rgba(62,197,224,.15)}
        .t-search input{border:none;outline:none;background:transparent;color:var(--text);flex:1;font:600 14px 'Plus Jakarta Sans',sans-serif;min-width:0}
        .table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
        table{width:100%;border-collapse:collapse;min-width:720px}
        th{font-size:11px;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);text-align:left;padding:14px 20px;border-bottom:1px solid var(--border);background:rgba(243,245,247,.4);white-space:nowrap;user-select:none}
        body.dark th{background:rgba(11,18,32,.4)}
        th.sortable{cursor:pointer;transition:.2s}
        th.sortable:hover{color:var(--forest)}
        body.dark th.sortable:hover{color:var(--lime)}
        th .s-ar{font-size:13px;opacity:.25;vertical-align:-1px;margin-left:3px;transition:.2s}
        th.sorted{color:var(--forest)}
        body.dark th.sorted{color:var(--lime)}
        th.sorted .s-ar{opacity:1}
        td{padding:14px 20px;border-bottom:1px solid var(--border);font-size:13.5px;vertical-align:middle}
        tbody tr{transition:background .2s}
        @media(hover:hover){tbody tr:hover{background:var(--lime-soft)}}
        tbody tr:last-child td{border-bottom:none}
        tbody tr.flash{animation:rowFlash 1.2s ease}
        @keyframes rowFlash{0%{background:rgba(62,197,224,.25)}100%{background:transparent}}
        .ccell{display:flex;align-items:center;gap:13px;min-width:200px}
        .c-ic{width:46px;height:46px;min-width:46px;border-radius:14px;display:grid;place-items:center;font-size:23px;transition:.3s;overflow:hidden}
        @media(hover:hover){tbody tr:hover .c-ic{transform:scale(1.08) rotate(-6deg)}}
        .cic-1{background:linear-gradient(135deg,#e0f5fb,#c2e9f5)}
        .cic-2{background:linear-gradient(135deg,#eef4e2,#d9f0c0)}
        .cic-3{background:linear-gradient(135deg,#fdeee0,#fbdcc0)}
        .cic-4{background:linear-gradient(135deg,#e8f1fd,#cfe3fb)}
        .cic-5{background:linear-gradient(135deg,#f4ecfd,#e2d3fb)}
        .cic-6{background:linear-gradient(135deg,#fdeef2,#fbd4de)}
        .nx-i{position:relative;display:grid;place-items:center;width:100%;height:100%}
        .nx-i i{font-size:24px;line-height:1;color:var(--forest)}
        body.dark .nx-i i{color:var(--lime)}
        .c-info strong{display:block;font-size:13.5px}
        .c-info span{font-size:11.5px;color:var(--muted)}
        .c-desc{max-width:280px;font-size:12.5px;color:var(--muted);line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
        .c-date{display:inline-flex;align-items:center;gap:6px;color:var(--muted);font-size:12.5px;white-space:nowrap}
        .pill{display:inline-flex;align-items:center;gap:6px;padding:5px 13px;border-radius:20px;font-size:11px;font-weight:800;white-space:nowrap}
        .pill::before{content:'';width:6px;height:6px;border-radius:50%;background:currentColor;box-shadow:0 0 7px currentColor}
        .pill.active{background:#dcfce7;color:#16a34a}
        .pill.hidden{background:#fef3c7;color:#b45309}
        body.dark .pill.active{background:rgba(74,222,128,.12);color:#4ade80}
        body.dark .pill.hidden{background:rgba(251,191,36,.12);color:#fbbf24}
        .status-toggle{border:0;font-family:inherit;cursor:pointer;transition:transform .2s,filter .2s}
        .status-toggle:hover:not(:disabled){transform:translateY(-1px);filter:brightness(.96)}
        .status-toggle:focus-visible{outline:3px solid rgba(62,197,224,.4);outline-offset:2px}
        .status-toggle:disabled{opacity:.6;cursor:wait}
        .actions{display:flex;gap:8px;justify-content:flex-end}
        .act{width:36px;height:36px;border-radius:11px;display:grid;place-items:center;font-size:16px;cursor:pointer;border:1px solid transparent;transition:.25s}
        .act-edit{background:rgba(37,99,235,.07);color:#2563eb;border-color:rgba(37,99,235,.2)}
        .act-edit:hover{background:#2563eb;color:#fff;transform:translateY(-2px)}
        .act-del{background:rgba(239,68,68,.07);color:#ef4444;border-color:rgba(239,68,68,.2)}
        .act-del:hover{background:#ef4444;color:#fff;transform:translateY(-2px)}
        .tbl-hint{display:none;padding:10px 22px;border-bottom:1px solid var(--border);font-size:11px;color:var(--muted);align-items:center;gap:7px}
        .t-foot{display:flex;justify-content:space-between;align-items:center;padding:15px 22px;border-top:1px solid var(--border);flex-wrap:wrap;gap:12px}
        .t-foot .info{color:var(--muted);font-size:12.5px}
        .pgn{display:flex;gap:5px;align-items:center;flex-wrap:wrap}
        .pg-btn{min-width:36px;height:36px;padding:0 8px;border-radius:10px;border:1px solid var(--border);background:var(--card);color:var(--text);font:700 12.5px 'Plus Jakarta Sans',sans-serif;cursor:pointer;transition:.2s;display:inline-flex;align-items:center;justify-content:center}
        .pg-btn:hover:not(:disabled):not(.on){border-color:var(--lime);background:var(--lime-soft)}
        .pg-btn.on{background:var(--forest);color:#fff;border-color:var(--forest);box-shadow:0 4px 10px rgba(14,90,117,.3)}
        .pg-btn:disabled{opacity:.4;cursor:default}
        .pg-dots{color:var(--muted);padding:0 4px;font-weight:800}
        .empty{text-align:center;padding:70px 20px}
        .empty .e-ic{width:74px;height:74px;margin:0 auto 16px;border-radius:22px;display:grid;place-items:center;font-size:34px;background:var(--lime-soft);color:var(--forest)}
        body.dark .empty .e-ic{color:var(--lime)}
        .empty h4{font-size:16px;margin-bottom:5px}
        .empty p{color:var(--muted);font-size:13px}
        .nb-modal{position:fixed;inset:0;z-index:100;display:grid;place-items:center;background:rgba(15,23,42,.62);opacity:0;visibility:hidden;transition:.3s;padding:20px}
        .nb-modal.show{opacity:1;visibility:visible}
        .nb-modal-box{width:50%;max-height:90vh;background:var(--card,#fff);border:1px solid var(--border,#e8edf3);border-radius:22px;text-align:center;box-shadow:0 30px 70px rgba(0,0,0,.35);transform:translateY(14px) scale(.95);transition:.35s cubic-bezier(.22,1,.36,1);position:relative;overflow:hidden;display:flex;flex-direction:column}
        .nb-modal-head{text-align:left}
        .nb-modal.show .nb-modal-box{transform:none}
        .nb-modal-head{flex-shrink:0;padding:22px 72px 20px 26px;background:linear-gradient(115deg,#0a3346 0%,#0e5a75 52%,#1a83ab 100%);position:relative;overflow:hidden}
        .nb-modal-head::before{content:'';position:absolute;right:-40px;top:-64px;width:160px;height:160px;border-radius:50%;background:rgba(255,255,255,.08);pointer-events:none}
        .nb-modal-head::after{content:'';position:absolute;right:70px;bottom:-52px;width:90px;height:90px;border-radius:50%;background:rgba(255,255,255,.05);pointer-events:none}
        .nb-modal-head h4{font-size:21px;font-weight:800;color:#fff;letter-spacing:-.3px;position:relative;z-index:1}
        .nb-modal-head p{font-size:12px;color:rgba(255,255,255,.78);margin-top:6px;position:relative;z-index:1;display:flex;align-items:center;gap:7px}
        .nb-modal-head p i{font-size:13px;color:var(--lime)}
        .nb-close{position:absolute;top:16px;right:16px;width:40px;height:40px;border-radius:12px;border:none;background:#0c2e3c;color:#fff;font-size:17px;cursor:pointer;display:grid;place-items:center;transition:.25s;z-index:2;box-shadow:0 6px 16px rgba(0,0,0,.25)}
        .nb-close:hover{background:#dc2626;transform:rotate(90deg)}
        .nb-modal-body{padding:26px 26px 24px;overflow-y:auto;overflow-x:hidden}
        .nb-modal-body::-webkit-scrollbar{width:6px}
        .nb-modal-body::-webkit-scrollbar-track{background:transparent}
        .nb-modal-body::-webkit-scrollbar-thumb{background:var(--border);border-radius:10px}
        .fld{margin-bottom:22px}
        .fld>label{display:flex;align-items:center;gap:8px;font-size:11px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#2f98a1;margin-bottom:10px}
        body.dark .fld>label{color:var(--lime)}
        .lb-ic{width:22px;height:22px;min-width:22px;border-radius:7px;background:rgba(56,170,180,.13);color:#2f98a1;display:grid;place-items:center;font-size:11px}
        body.dark .lb-ic{background:rgba(62,197,224,.12);color:var(--lime)}
        .fld input,.fld textarea{width:100%;min-height:54px;border:1.5px solid transparent;background:#f4f7f8;border-radius:14px;padding:15px 18px;font:600 13.5px 'Plus Jakarta Sans',sans-serif;color:var(--text);outline:none;transition:.25s;resize:none;max-width:100%}
        body.dark .fld input,body.dark .fld textarea{background:rgba(255,255,255,.05)}
        .fld input::placeholder,.fld textarea::placeholder{color:#9fb0c0;font-weight:500}
        body.dark .fld input::placeholder,body.dark .fld textarea::placeholder{color:#5c718c}
        .fld input:focus,.fld textarea:focus{border-color:var(--lime);background:var(--card);box-shadow:0 0 0 4px rgba(62,197,224,.14)}
        .icon-row{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-top:4px}
        .icon-pick{position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:7px;padding:15px 6px 12px;border-radius:16px;border:1.5px solid transparent;background:#f4f7f8;cursor:pointer;transition:.3s cubic-bezier(.22,1,.36,1);max-width:100%;box-shadow:0 3px 10px rgba(16,24,40,.05)}
        body.dark .icon-pick{background:rgba(255,255,255,.05)}
        .icon-pick .nx-i{width:46px;height:46px;border-radius:13px;background:var(--lime-soft);font-size:25px;color:var(--forest)}
        body.dark .icon-pick .nx-i{color:var(--lime)}
        .icon-pick small{font-size:8.5px;font-weight:800;letter-spacing:.5px;text-transform:uppercase;color:var(--muted);text-align:center}
        @media(hover:hover){.icon-pick:hover{border-color:var(--lime);transform:translateY(-4px);box-shadow:0 14px 28px rgba(62,197,224,.22)}}
        .icon-pick.sel{border-color:var(--lime);background:linear-gradient(160deg,var(--lime-soft),var(--card));box-shadow:0 0 0 4px rgba(62,197,224,.16),0 12px 26px rgba(62,197,224,.25)}
        .icon-pick.sel::after{content:'✓';position:absolute;top:7px;right:7px;width:20px;height:20px;border-radius:50%;background:linear-gradient(135deg,var(--lime),var(--forest-2));color:#fff;font-size:11px;font-weight:800;display:grid;place-items:center;animation:selPop .3s cubic-bezier(.34,1.56,.64,1)}
        @keyframes selPop{from{transform:scale(0)}to{transform:scale(1)}}
        .switch-row{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:16px 18px;border-radius:14px;background:#f4f7f8;margin-bottom:24px}
        body.dark .switch-row{background:rgba(255,255,255,.05)}
        .switch-row b{font-size:13.5px}
        .switch-row span{display:block;font-size:11.5px;color:var(--muted);font-weight:600;margin-top:2px}
        .switch{position:relative;width:50px;height:28px;flex-shrink:0}
        .switch input{opacity:0;width:0;height:0}
        .slider{position:absolute;inset:0;border-radius:30px;background:#cbd5e1;cursor:pointer;transition:.3s}
        .slider::before{content:'';position:absolute;width:22px;height:22px;border-radius:50%;left:3px;top:3px;background:#fff;transition:.3s cubic-bezier(.34,1.56,.64,1);box-shadow:0 2px 6px rgba(0,0,0,.25)}
        .switch input:checked+.slider{background:linear-gradient(135deg,var(--forest-2),var(--lime))}
        .switch input:checked+.slider::before{transform:translateX(22px)}
        .nb-modal-actions{display:flex;gap:12px}
        .nb-btn{padding:14px 22px;min-height:50px;border-radius:13px;font-weight:800;font-size:13.5px;cursor:pointer;border:none;transition:.25s;display:inline-flex;align-items:center;justify-content:center;gap:8px}
        .nb-btn.ghost{background:#f4f7f8;color:var(--text);border:1px solid var(--border);flex:1}
        body.dark .nb-btn.ghost{background:rgba(255,255,255,.05)}
        .nb-btn.ghost:hover{border-color:var(--muted)}
        .nb-btn.save{background:linear-gradient(135deg,var(--forest),var(--forest-2));color:#fff;flex:2;box-shadow:0 8px 20px rgba(14,90,117,.35)}
        .nb-btn.save:hover{transform:translateY(-2px)}
        .nb-btn.save:disabled{opacity:.6;cursor:wait;transform:none}
        .del-modal-box{width:min(460px,100%)}
        .del-head{background:linear-gradient(115deg,#7f1d1d 0%,#dc2626 55%,#ef4444 100%)}
        .del-head p i{color:#fecaca}
        .del-body{padding:28px 26px 24px;text-align:center;overflow-y:auto;overflow-x:hidden}
        .del-emoji{width:74px;height:74px;margin:0 auto 16px;border-radius:24px;background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.3);display:grid;place-items:center;font-size:34px;color:#f87171;animation:delShake .5s ease}
        @keyframes delShake{0%,100%{transform:rotate(0)}25%{transform:rotate(-8deg)}75%{transform:rotate(8deg)}}
        .del-body h4{font-size:17px;font-weight:800;margin-bottom:8px}
        .del-body p{font-size:13px;color:var(--muted);line-height:1.7;margin-bottom:22px}
        .del-body p b{color:#ef4444}
        .del-body .nb-btn.save{background:linear-gradient(135deg,#ef4444,#dc2626);box-shadow:0 8px 20px rgba(239,68,68,.35)}
        .swal-toast{background:#111c2e !important;border:1px solid rgba(62,197,224,.25) !important;color:#fff !important;border-radius:14px !important}
        .admin-native-toast{position:fixed;top:18px;right:18px;z-index:10000;max-width:min(420px,calc(100vw - 32px));padding:13px 17px;border:1px solid rgba(62,197,224,.3);border-radius:12px;background:#102a3a;color:#fff;box-shadow:0 16px 40px rgba(4,20,32,.25);font-weight:700;font-size:13px;opacity:0;transform:translateY(-8px);pointer-events:none;transition:.2s}
        .admin-native-toast.visible{opacity:1;transform:none}
        .admin-native-toast[data-type="error"]{background:#7f1d1d;border-color:rgba(255,255,255,.2)}
        @media(max-width:1250px){.r-stats{grid-template-columns:1fr 1fr}}
        @media(max-width:1100px){.nb-modal-box{width:min(640px,calc(100vw - 40px))}}
        @media(max-width:992px){.sidebar{transform:translateX(-105%);width:268px !important}.layout.mobile-open .sidebar{transform:none}.main{margin-left:0 !important}}
        @media(max-width:680px){.r-stats{grid-template-columns:1fr 1fr;gap:14px}.content{padding:16px 12px calc(40px + env(safe-area-inset-bottom));gap:16px}.toolbar{padding:14px 14px}.t-search{width:100%}.tbl-hint{display:flex}.t-foot{padding:13px 14px;justify-content:center}.stat{padding:16px}.card{border-radius:14px}.page-head h2{font-size:20px}.nb-modal{padding:10px}.nb-modal-body{padding:20px 18px}.nb-modal-box{width:calc(100vw - 20px);border-radius:18px}}
        @media(max-width:480px){.r-stats{grid-template-columns:1fr}.content{padding:14px 0 calc(40px + env(safe-area-inset-bottom))}.toolbar,.t-foot{padding-left:12px;padding-right:12px}.page-head .btn{width:100%;justify-content:center}.icon-row{grid-template-columns:repeat(2,1fr)}.nb-modal-actions{flex-direction:column-reverse}.nb-modal-box{width:calc(100vw - 16px);border-radius:16px}}
        @media(max-width:360px){.r-stats{grid-template-columns:1fr}}
        /* ★★★ MODAL WIDTH LOCK — ID specificity navbar override ko haraam karti hai ★★★ */
        #catModal .nb-modal-box{width:50%;max-width:none}
        @media(max-width:1100px){#catModal .nb-modal-box{width:min(640px,calc(100vw - 40px))}}
        @media(max-width:680px){#catModal .nb-modal-box{width:calc(100vw - 20px)}}
        @media(max-width:480px){#catModal .nb-modal-box{width:calc(100vw - 16px)}}
        #catDelModal .nb-modal-box{width:min(460px,100%);max-width:none}
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
                        <h2><span class="ph-ic"><i class="ri-apps-2-line"></i></span>Categories</h2>
                        <p>Organize your store — Perfumes, Lip Balms, Creams and more.</p>
                    </div>
                    <button type="button" class="btn btn-lime" onclick="catOpenModal()"><i class="ri-add-line"></i>Add Category</button>
                </div>

                <section class="r-stats">
                    <div class="card stat fx d1">
                        <div class="stat-top"><div class="icon i-green"><i class="ri-apps-2-line"></i></div></div>
                        <div class="big sm" id="statTotal">{{ $stats['total'] }}</div>
                        <p class="muted small">Total categories</p>
                    </div>
                    <div class="card stat fx d2">
                        <div class="stat-top"><div class="icon i-blue"><i class="ri-eye-line"></i></div></div>
                        <div class="big sm" id="statActive">{{ $stats['active'] }}</div>
                        <p class="muted small">Visible on store</p>
                    </div>
                    <div class="card stat fx d3">
                        <div class="stat-top"><div class="icon i-red"><i class="ri-eye-off-line"></i></div></div>
                        <div class="big sm" id="statHidden">{{ $stats['hidden'] }}</div>
                        <p class="muted small">Hidden</p>
                    </div>
                    <div class="card stat fx d3">
                        <div class="stat-top"><div class="icon i-violet"><i class="ri-calendar-line"></i></div></div>
                        <div class="big sm" id="statNew">{{ $stats['new'] }}</div>
                        <p class="muted small">Added this month</p>
                    </div>
                </section>

                <section class="card table-card fx d3" id="catTableCard">
                    <div class="toolbar">
                        <div class="t-search">
                            <i class="ri-search-line"></i>
                            <input type="text" id="catSearch" oninput="catGoPage(1)" placeholder="Search categories...">
                        </div>
                        <div class="dd" id="catFilter" data-value="all">
                            <button type="button" class="dd-btn" onclick="nxDdToggle('catFilter')"><i class="ri-filter-3-line"></i><span class="dd-label">All statuses</span><i class="ri-arrow-down-s-line dd-chev"></i></button>
                            <div class="dd-menu">
                                <button type="button" class="dd-item on" data-v="all" onclick="nxDdPick(this,'catFilter')">All statuses</button>
                                <button type="button" class="dd-item" data-v="active" onclick="nxDdPick(this,'catFilter')">Active only</button>
                                <button type="button" class="dd-item" data-v="hidden" onclick="nxDdPick(this,'catFilter')">Hidden only</button>
                            </div>
                        </div>
                        <div class="dd" id="catPer" data-value="10" style="margin-left:auto">
                            <button type="button" class="dd-btn" onclick="nxDdToggle('catPer')"><i class="ri-list-check"></i><span class="dd-label">10 rows</span><i class="ri-arrow-down-s-line dd-chev"></i></button>
                            <div class="dd-menu">
                                <button type="button" class="dd-item on" data-v="10" onclick="nxDdPick(this,'catPer')">10 rows</button>
                                <button type="button" class="dd-item" data-v="25" onclick="nxDdPick(this,'catPer')">25 rows</button>
                                <button type="button" class="dd-item" data-v="50" onclick="nxDdPick(this,'catPer')">50 rows</button>
                                <button type="button" class="dd-item" data-v="999999" onclick="nxDdPick(this,'catPer')">All rows</button>
                            </div>
                        </div>
                    </div>

                    <div class="tbl-hint"><i class="ri-drag-move-2-line"></i> Swipe the table sideways to see all columns</div>

                    <div class="table-wrap">
                        <table data-server-pagination="true">
                            <thead>
                                <tr>
                                    <th class="sortable" data-k="name" onclick="catSort(this)">Category<span class="s-ar">▼</span></th>
                                    <th>Description</th>
                                    <th class="sortable" data-k="active" onclick="catSort(this)">Status<span class="s-ar">▼</span></th>
                                    <th class="sortable" data-k="ts" onclick="catSort(this)">Created<span class="s-ar">▼</span></th>
                                    <th style="text-align:right">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="catBody">
                                @foreach($categories as $c)
                                <tr data-id="{{ $c->id }}" data-name="{{ $c->name }}" data-icon="{{ $c->icon }}"
                                    data-desc="{{ $c->description }}" data-active="{{ $c->is_active ? 1 : 0 }}"
                                    data-ts="{{ $c->created_at->timestamp }}"
                                    data-hay="{{ strtolower($c->name.' '.$c->description) }}">
                                    <td>
                                        <div class="ccell">
                                            <span class="c-ic cic-{{ ($loop->index % 6) + 1 }}">
                                                <span class="nx-i" aria-hidden="true"><i class="{{ $c->iconClass() }}"></i></span>
                                            </span>
                                            <div class="c-info"><strong>{{ $c->name }}</strong><span>#{{ $c->slug }}</span></div>
                                        </div>
                                    </td>
                                    <td><p class="c-desc">{{ $c->description ?: '—' }}</p></td>
                                    <td class="pill-cell"><button type="button" class="pill status-toggle {{ $c->is_active ? 'active' : 'hidden' }}" aria-pressed="{{ $c->is_active ? 'true' : 'false' }}" aria-label="{{ $c->is_active ? 'Hide' : 'Activate' }} {{ $c->name }}" onclick="catToggleStatus(this)">{{ $c->is_active ? 'Active' : 'Hidden' }}</button></td>
                                    <td><span class="c-date"><i class="ri-time-line"></i>{{ $c->created_at->format('M d, Y') }}</span></td>
                                    <td>
                                        <div class="actions">
                                            <button type="button" class="act act-edit" onclick="catOpenModal(this.closest('tr'))" title="Edit"><i class="ri-pencil-line"></i></button>
                                            <button type="button" class="act act-del" onclick="catAskDelete(this)" title="Delete"><i class="ri-delete-bin-6-line"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="empty" id="catNoMatch" style="display:none">
                        <div class="e-ic"><i class="ri-search-eye-line"></i></div>
                        <h4>No match found</h4>
                        <p>Try a different search or filter.</p>
                    </div>

                    <div class="t-foot">
                        <p class="info" id="catInfo"></p>
                        <div class="pgn" id="catPgn"></div>
                    </div>
                </section>

                <div class="card empty fx" id="catEmpty" @if(!$categories->isEmpty()) style="display:none" @endif>
                    <div class="e-ic"><i class="ri-apps-2-add-line"></i></div>
                    <h4>No categories yet</h4>
                    <p>Click <b>Add Category</b> to create Perfume, Lip Balm, Cream and more.</p>
                </div>

            </main>
        </div>
    </div>

    {{-- ═══ ADD/EDIT MODAL ═══ --}}
    <div class="nb-modal" id="catModal" onclick="if(event.target===this)catCloseModal()">
        <div class="nb-modal-box">
            <div class="nb-modal-head">
                <button type="button" class="nb-close" onclick="catCloseModal()" aria-label="Close"><i class="ri-close-line"></i></button>
                <h4 id="modalTitle">Add Category</h4>
                <p><i class="ri-information-line"></i><span id="modalSubText">Create a new product category</span></p>
            </div>
            <div class="nb-modal-body">
                <form id="catForm" onsubmit="return catSave(event)">
                    <div class="fld">
                        <label><span class="lb-ic"><i class="ri-price-tag-3-line"></i></span><span>Category Name *</span></label>
                        <input type="text" name="name" id="fName" placeholder="e.g. Perfume" maxlength="100" required>
                    </div>
                    <div class="fld">
                        <label><span class="lb-ic"><i class="ri-magic-line"></i></span><span>Icon — Pick One</span></label>
                        <input type="text" name="icon" id="fIcon" maxlength="100" hidden>
                        <div class="icon-row" id="iconRow">
                            @foreach($categoryIconChoices as $choice)
                            <button type="button" class="icon-pick" data-value="{{ $choice['value'] }}" onclick="catPickIcon(this)">
                                <span class="nx-i" aria-hidden="true"><i class="{{ $choice['value'] }}"></i></span>
                                <small>{{ $choice['label'] }}</small>
                            </button>
                            @endforeach
                        </div>
                    </div>
                    <div class="fld">
                        <label><span class="lb-ic"><i class="ri-file-list-3-line"></i></span><span>Description</span></label>
                        <textarea name="description" id="fDesc" rows="3" placeholder="Short description (optional)" maxlength="255"></textarea>
                    </div>
                    <div class="switch-row">
                        <div><b>Visible on store</b><span>Customers can see this category</span></div>
                        <label class="switch"><input type="checkbox" name="is_active" id="fActive" checked><span class="slider"></span></label>
                    </div>
                    <div class="nb-modal-actions">
                        <button type="button" class="nb-btn ghost" onclick="catCloseModal()"><i class="ri-close-line"></i>Cancel</button>
                        <button type="submit" class="nb-btn save" id="catSaveBtn"><i class="ri-save-3-line"></i>Save Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ═══ DELETE MODAL ═══ --}}
    <div class="nb-modal" id="catDelModal" onclick="if(event.target===this)catCloseDelete()">
        <div class="nb-modal-box del-modal-box">
            <div class="nb-modal-head del-head">
                <button type="button" class="nb-close" onclick="catCloseDelete()" aria-label="Close"><i class="ri-close-line"></i></button>
                <h4>Delete Category</h4>
                <p><i class="ri-error-warning-line"></i><span>This action cannot be undone</span></p>
            </div>
            <div class="del-body">
                <div class="del-emoji"><i class="ri-delete-bin-6-line"></i></div>
                <h4>Delete "<span id="delName">?</span>"?</h4>
                <p>The category <b id="delNameB">?</b> will be permanently removed from your store.</p>
                <div class="nb-modal-actions">
                    <button type="button" class="nb-btn ghost" onclick="catCloseDelete()"><i class="ri-arrow-left-line"></i>Cancel</button>
                    <button type="button" class="nb-btn save" id="catDelBtn" onclick="catDoDelete()"><i class="ri-delete-bin-line"></i>Yes, Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        /* ═══ URLS — Blade-rendered INSIDE script (SPA-safe) ═══ */
        var catStoreUrl = "{{ route('admin.categories.store') }}";
        var catUpdTpl = "{{ route('admin.categories.update', ['category' => ':id']) }}";
        var catDelTpl = "{{ route('admin.categories.destroy', ['category' => ':id']) }}";
        var catStatusTpl = "{{ route('admin.categories.status', ['category' => ':id']) }}";
        var catIconClasses = @json($categoryIconClasses);

        /* ═══ STATE ═══ */
        var catTbl = { q: '', filter: 'all', sortK: 'ts', sortD: -1, page: 1, per: 10 };
        var catEditId = null, catDelId = null;

        /* ═══ HELPERS ═══ */
        function catToken() { var m = document.querySelector('meta[name="csrf-token"]'); return m ? m.content : ''; }
        function escHtml(s) { return String(s == null ? '' : s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
        function catFmtDate(s) { if(!s) return ''; var d = new Date(s); if(isNaN(d)) return s; var M=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']; return M[d.getMonth()]+' '+('0'+d.getDate()).slice(-2)+', '+d.getFullYear(); }
        function catToast(msg, icon) {
            if (window.Swal) { Swal.fire({ toast:true, position:'top-end', icon:icon||'success', title:msg, showConfirmButton:false, timer:2400, timerProgressBar:true, customClass:{popup:'swal-toast'} }); return; }
            var toast = document.getElementById('adminNativeToast');
            if (!toast) { toast = document.createElement('div'); toast.id = 'adminNativeToast'; toast.className = 'admin-native-toast'; document.body.appendChild(toast); }
            toast.dataset.type = icon === 'error' ? 'error' : 'success';
            toast.setAttribute('role', icon === 'error' ? 'alert' : 'status');
            toast.textContent = msg;
            toast.classList.add('visible');
            window.clearTimeout(window.__categoryToastTimer);
            window.__categoryToastTimer = window.setTimeout(function(){ toast.classList.remove('visible'); },2600);
        }

        function catIconHtml(icon) {
            var iconClass = Object.prototype.hasOwnProperty.call(catIconClasses, icon) ?
                catIconClasses[icon] : @json(config('category_icons.fallback'));
            return '<span class="nx-i" aria-hidden="true"><i class="' + escHtml(iconClass) + '"></i></span>';
        }

        /* ═══ CUSTOM DROPDOWNS (shared helpers — guarded) ═══ */
        window.nxDdToggle = window.nxDdToggle || function(id) {
            var dd = document.getElementById(id); if (!dd) return;
            var was = dd.classList.contains('open');
            document.querySelectorAll('.dd.open').forEach(function(d){ d.classList.remove('open'); });
            if (!was) dd.classList.add('open');
        };
        window.nxDdPick = window.nxDdPick || function(btn, id) {
            var dd = document.getElementById(id); if (!dd) return;
            dd.querySelectorAll('.dd-item').forEach(function(i){ i.classList.remove('on'); });
            btn.classList.add('on'); dd.dataset.value = btn.dataset.v;
            var lb = dd.querySelector('.dd-label'); if (lb) lb.textContent = btn.textContent.trim();
            dd.classList.remove('open');
            if (typeof window.nxDdOnChange === 'function') window.nxDdOnChange(id);
        };
        window.nxDdOnChange = function(id) { if (id === 'catFilter' || id === 'catPer') catGoPage(1); };

        /* outside-click close — guarded (SPA re-run killer) */
        if (!window.__nxDdOut) {
            window.__nxDdOut = 1;
            document.addEventListener('click', function(e) {
                document.querySelectorAll('.dd.open').forEach(function(d){ if (!d.contains(e.target)) d.classList.remove('open'); });
            });
        }

        /* Esc close — guarded */
        if (!window.__catEsc) {
            window.__catEsc = 1;
            document.addEventListener('keydown', function(e) {
                if (e.key !== 'Escape') return;
                if (!document.getElementById('catModal')) return;
                document.querySelectorAll('.dd.open').forEach(function(d){ d.classList.remove('open'); });
                catCloseModal(); catCloseDelete();
            });
        }

        /* ═══ TABLE ENGINE ═══ */
        function catSyncHeads() {
            document.querySelectorAll('th.sortable').forEach(function(th) {
                var on = th.dataset.k === catTbl.sortK;
                th.classList.toggle('sorted', on);
                var ar = th.querySelector('.s-ar');
                if (ar) ar.textContent = on ? (catTbl.sortD === 1 ? '▲' : '▼') : '▼';
            });
        }

        function catApply() {
            var qEl = document.getElementById('catSearch'), fEl = document.getElementById('catFilter'), pEl = document.getElementById('catPer');
            var q = ((qEl && qEl.value) || '').toLowerCase().trim();
            var filter = (fEl && fEl.dataset.value) || 'all';
            catTbl.per = parseInt(pEl && pEl.dataset.value) || 10;
            var tbody = document.getElementById('catBody');
            var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
            var vis = rows.filter(function(r) {
                var okQ = !q || (r.dataset.hay || '').indexOf(q) !== -1;
                var okF = filter === 'all' || (filter === 'active' ? r.dataset.active === '1' : r.dataset.active === '0');
                return okQ && okF;
            });
            var k = catTbl.sortK, d = catTbl.sortD;
            vis.sort(function(a, b) {
                if (k === 'name') return d * (a.dataset.name || '').localeCompare(b.dataset.name || '');
                return d * ((parseFloat(a.dataset[k]) || 0) - (parseFloat(b.dataset[k]) || 0));
            });
            vis.forEach(function(r){ tbody.appendChild(r); });
            var tp = Math.max(1, Math.ceil(vis.length / catTbl.per));
            if (catTbl.page > tp) catTbl.page = tp;
            if (catTbl.page < 1) catTbl.page = 1;
            var start = (catTbl.page - 1) * catTbl.per;
            rows.forEach(function(r){ r.style.display = 'none'; });
            vis.slice(start, start + catTbl.per).forEach(function(r){ r.style.display = ''; });
            var info = document.getElementById('catInfo');
            if (info) info.textContent = vis.length ? 'Showing ' + (start + 1) + '–' + Math.min(start + catTbl.per, vis.length) + ' of ' + vis.length + ' categories' : 'No categories found';
            catSyncHeads(); catRenderPgn(tp);
            var tc = document.getElementById('catTableCard'), ec = document.getElementById('catEmpty'), nm = document.getElementById('catNoMatch');
            if (ec) { if (rows.length === 0) { tc.style.display = 'none'; ec.style.display = ''; } else { tc.style.display = ''; ec.style.display = 'none'; } }
            if (nm) nm.style.display = (rows.length > 0 && vis.length === 0) ? '' : 'none';
        }

        function catGoPage(p) { catTbl.page = p; catApply(); }

        function catSort(th) {
            var k = th.dataset.k;
            if (catTbl.sortK === k) { catTbl.sortD = -catTbl.sortD; } else { catTbl.sortK = k; catTbl.sortD = -1; }
            catGoPage(1);
        }

        function catPBtn(label, page, opts) {
            opts = opts || {};
            return '<button type="button" class="pg-btn' + (opts.on ? ' on' : '') + '"' + (opts.disabled ? ' disabled' : '') + ' onclick="catGoPage(' + page + ')">' + label + '</button>';
        }

        function catRenderPgn(tp) {
            var cp = catTbl.page, h = '';
            h += catPBtn('«', 1, { disabled: cp === 1 });
            h += catPBtn('‹', cp - 1, { disabled: cp === 1 });
            var pages = [];
            if (tp <= 7) { for (var i = 1; i <= tp; i++) pages.push(i); }
            else {
                pages.push(1);
                var s = Math.max(2, cp - 1), e = Math.min(tp - 1, cp + 1);
                if (s > 2) pages.push('...');
                for (var j = s; j <= e; j++) pages.push(j);
                if (e < tp - 1) pages.push('...');
                pages.push(tp);
            }
            pages.forEach(function(p) { if (p === '...') h += '<span class="pg-dots">…</span>'; else h += catPBtn(p, p, { on: p === cp }); });
            h += catPBtn('›', cp + 1, { disabled: cp === tp });
            h += catPBtn('»', tp, { disabled: cp === tp });
            document.getElementById('catPgn').innerHTML = h;
        }

        /* ═══ ROW BUILDERS (AJAX live update) ═══ */
        function catRowInner(c, cls) {
            var ic = catIconHtml(c.icon);
            var statusLabel = c.is_active ? 'Active' : 'Hidden';
            var pill = '<button type="button" class="pill status-toggle ' + (c.is_active ? 'active' : 'hidden') +
                '" aria-pressed="' + (c.is_active ? 'true' : 'false') + '" aria-label="' +
                (c.is_active ? 'Hide ' : 'Activate ') + escHtml(c.name) + '" onclick="catToggleStatus(this)">' +
                statusLabel + '</button>';
            return '<td><div class="ccell"><span class="c-ic ' + cls + '">' + ic + '</span><div class="c-info"><strong>' + escHtml(c.name) + '</strong><span>#' + escHtml(c.slug || '') + '</span></div></div></td>' +
                '<td><p class="c-desc">' + (c.description ? escHtml(c.description) : '—') + '</p></td>' +
                '<td class="pill-cell">' + pill + '</td>' +
                '<td><span class="c-date"><i class="ri-time-line"></i>' + catFmtDate(c.created_at) + '</span></td>' +
                '<td><div class="actions"><button type="button" class="act act-edit" onclick="catOpenModal(this.closest(\'tr\'))" title="Edit"><i class="ri-pencil-line"></i></button>' +
                '<button type="button" class="act act-del" onclick="catAskDelete(this)" title="Delete"><i class="ri-delete-bin-6-line"></i></button></div></td>';
        }

        function catSetRow(tr, c, cls) {
            var ts = Math.floor(new Date(c.created_at).getTime() / 1000) || 0;
            tr.dataset.id = c.id; tr.dataset.name = c.name || ''; tr.dataset.icon = c.icon || '';
            tr.dataset.desc = c.description || ''; tr.dataset.active = c.is_active ? '1' : '0';
            tr.dataset.ts = ts; tr.dataset.hay = ((c.name || '') + ' ' + (c.description || '')).toLowerCase();
            tr.innerHTML = catRowInner(c, cls);
        }

        function catInsertRow(c) {
            var tbody = document.getElementById('catBody');
            var tr = document.createElement('tr');
            catSetRow(tr, c, 'cic-' + ((tbody.children.length % 6) + 1));
            tr.classList.add('flash');
            tbody.insertBefore(tr, tbody.firstChild);
            setTimeout(function(){ tr.classList.remove('flash'); }, 1300);
            var tc = document.getElementById('catTableCard'); if (tc) tc.style.display = '';
            var ec = document.getElementById('catEmpty'); if (ec) ec.style.display = 'none';
        }

        function catUpdateRow(c) {
            var tr = document.querySelector('#catBody tr[data-id="' + c.id + '"]');
            if (!tr) return catInsertRow(c);
            var icEl = tr.querySelector('.c-ic'), cls = 'cic-1';
            if (icEl) { var m = icEl.className.match(/cic-\d/); if (m) cls = m[0]; }
            tr.classList.add('flash');
            catSetRow(tr, c, cls);
            setTimeout(function(){ tr.classList.remove('flash'); }, 1300);
        }

        /* ═══ STATS (DOM recount + pop animation) ═══ */
        function catSetStat(id, v) {
            var el = document.getElementById(id);
            if (!el || el.textContent == String(v)) return;
            el.textContent = v;
            el.classList.remove('stat-pop'); void el.offsetWidth; el.classList.add('stat-pop');
        }

        function catRefreshStats() {
            var rows = document.querySelectorAll('#catBody tr'), total = rows.length, active = 0, nw = 0;
            var mStart = new Date(new Date().getFullYear(), new Date().getMonth(), 1).getTime() / 1000;
            rows.forEach(function(r) { if (r.dataset.active === '1') active++; if (parseFloat(r.dataset.ts) >= mStart) nw++; });
            catSetStat('statTotal', total); catSetStat('statActive', active);
            catSetStat('statHidden', total - active); catSetStat('statNew', nw);
        }

        function catToggleStatus(btn) {
            var tr = btn.closest('tr');
            if (!tr || btn.disabled) return;

            var nextActive = tr.dataset.active !== '1';
            btn.disabled = true;

            fetch(catStatusTpl.replace(':id', tr.dataset.id), {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': catToken(),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ is_active: nextActive ? 1 : 0, _token: catToken() })
            }).then(function(response) {
                return response.json().catch(function() { return {}; }).then(function(data) {
                    if (!response.ok) throw new Error(data.message || 'Could not update category status.');
                    return data;
                });
            }).then(function(data) {
                var icon = tr.querySelector('.c-ic'), cls = 'cic-1';
                if (icon) {
                    var match = icon.className.match(/cic-\d/);
                    if (match) cls = match[0];
                }
                catSetRow(tr, data.category, cls);
                catRefreshStats();
                catApply();
                catToast(data.message || 'Category status updated.');
            }).catch(function(error) {
                btn.disabled = false;
                catToast(error.message || 'Could not update category status.', 'error');
            });
        }

        /* ═══ ADD/EDIT MODAL ═══ */
        function catSelectIcon(value) {
            document.getElementById('fIcon').value = value || '';
            document.querySelectorAll('#iconRow .icon-pick').forEach(function(t) { t.classList.toggle('sel', !!value && t.dataset.value === value); });
        }

        function catPickIcon(btn) {
            document.querySelectorAll('#iconRow .icon-pick').forEach(function(t){ t.classList.remove('sel'); });
            btn.classList.add('sel');
            document.getElementById('fIcon').value = btn.dataset.value;
        }

        function catOpenModal(row) {
            var m = document.getElementById('catModal');
            var isEdit = !!(row && row.dataset && row.dataset.id);
            catEditId = isEdit ? row.dataset.id : null;
            document.getElementById('modalTitle').textContent = isEdit ? 'Edit Category' : 'Add Category';
            document.getElementById('modalSubText').textContent = isEdit ? 'Update this category details' : 'Create a new product category';
            document.getElementById('fName').value = isEdit ? row.dataset.name : '';
            document.getElementById('fDesc').value = isEdit ? (row.dataset.desc || '') : '';
            document.getElementById('fActive').checked = isEdit ? row.dataset.active === '1' : true;
            var storedIcon = isEdit ? row.dataset.icon : '';
            catSelectIcon(catIconClasses[storedIcon] || storedIcon || @json(config('category_icons.fallback')));
            m.classList.add('show');
            document.body.classList.add('nb-lock');
            setTimeout(function(){ document.getElementById('fName').focus(); }, 260);
        }

        function catCloseModal() {
            var m = document.getElementById('catModal');
            if (m) m.classList.remove('show');
            document.body.classList.remove('nb-lock');
            catEditId = null;
        }

        /* ═══ SAVE — AJAX (edit pe _method spoof) ═══ */
        function catSave(ev) {
            ev.preventDefault();
            var name = document.getElementById('fName').value.trim();
            var icon = document.getElementById('fIcon').value;
            if (!name) { catToast('Category name is required', 'warning'); return false; }
            if (!icon) { catToast('Please choose a category icon', 'warning'); return false; }
            var fd = new FormData();
            fd.append('name', name);
            fd.append('icon', icon);
            fd.append('description', document.getElementById('fDesc').value.trim());
            fd.append('is_active', document.getElementById('fActive').checked ? '1' : '0');
            fd.append('_token', catToken());
            var isEdit = !!catEditId;
            if (isEdit) fd.append('_method', 'PATCH');
            var url = isEdit ? catUpdTpl.replace(':id', catEditId) : catStoreUrl;
            var btn = document.getElementById('catSaveBtn');
            btn.disabled = true;
            fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': catToken(), 'Accept': 'application/json' }, body: fd })
                .then(function(r) { return r.json().catch(function(){ return {}; }).then(function(j){ return { ok: r.ok, j: j }; }); })
                .then(function(res) {
                    btn.disabled = false;
                    if (!res.ok) throw res.j;
                    var c = (res.j && (res.j.category || res.j.data)) || res.j;
                    if (!c || !c.id) throw new Error('The server response did not include the saved category.');
                    if (isEdit) { catUpdateRow(c); catToast('Category updated'); }
                    else { catInsertRow(c); catToast('Category added'); }
                    catCloseModal(); catRefreshStats(); catGoPage(1);
                })
                .catch(function(err) {
                    btn.disabled = false;
                    var msg = 'Something went wrong';
                    if (err && err.errors) { var k = Object.keys(err.errors); if (k.length) msg = err.errors[k[0]][0]; }
                    else if (err && err.message) msg = err.message;
                    catToast(msg, 'error');
                });
            return false;
        }

        /* ═══ DELETE — AJAX (DELETE spoof) ═══ */
        function catAskDelete(btn) {
            var tr = btn.closest('tr'); if (!tr) return;
            catDelId = tr.dataset.id;
            document.getElementById('delName').textContent = tr.dataset.name;
            document.getElementById('delNameB').textContent = tr.dataset.name;
            document.getElementById('catDelModal').classList.add('show');
            document.body.classList.add('nb-lock');
        }

        function catCloseDelete() {
            var m = document.getElementById('catDelModal');
            if (m) m.classList.remove('show');
            document.body.classList.remove('nb-lock');
            catDelId = null;
        }

        function catDoDelete() {
            if (!catDelId) return;
            var btn = document.getElementById('catDelBtn');
            btn.disabled = true;
            var fd = new FormData();
            fd.append('_method', 'DELETE');
            fd.append('_token', catToken());
            fetch(catDelTpl.replace(':id', catDelId), { method: 'POST', headers: { 'X-CSRF-TOKEN': catToken(), 'Accept': 'application/json' }, body: fd })
                .then(function(r) { return r.json().catch(function(){ return {}; }).then(function(j){ return { ok: r.ok, j: j }; }); })
                .then(function(res) {
                    btn.disabled = false;
                    if (!res.ok) throw res.j;
                    var tr = document.querySelector('#catBody tr[data-id="' + catDelId + '"]');
                    catCloseDelete();
                    if (tr) {
                        tr.style.transition = 'opacity .3s, transform .3s';
                        tr.style.opacity = '0'; tr.style.transform = 'translateX(30px)';
                        setTimeout(function(){ tr.remove(); catRefreshStats(); catGoPage(1); }, 280);
                    }
                    catToast('Category deleted');
                })
                .catch(function(err) {
                    btn.disabled = false;
                    catToast((err && err.message) ? err.message : 'Delete failed', 'error');
                });
        }

        /* ═══ INIT — no DOMContentLoaded (SPA re-run safe) ═══ */
        catApply();
    </script>
</body>
</html>