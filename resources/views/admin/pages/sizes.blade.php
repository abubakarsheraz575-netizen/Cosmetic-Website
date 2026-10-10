{{-- ==========================================================
     ADMIN · SIZES — v9 (categories-style order-popup modal ·
     ID width-lock · custom dropdowns · always-on pagination ·
     namespaced sz* JS · SPA-safe dropdown engine)
========================================================== --}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="theme-color" content="#0e5a75">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>ML / Litres — NiceDash Admin</title>
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
a{text-decoration:none;color:inherit}button{font-family:inherit;user-select:none}
.layout{min-height:100vh}.main{margin-left:268px;min-width:0;transition:margin .35s}.layout.collapsed .main{margin-left:84px}
.content{padding:24px 26px calc(40px + env(safe-area-inset-bottom));display:grid;gap:22px;min-width:0}
.sidebar{position:fixed;left:0;top:0;bottom:0;width:268px;background:var(--card);border-right:1px solid var(--border);display:flex;flex-direction:column;z-index:50;transition:width .35s,transform .35s}
.layout.collapsed .sidebar{width:84px}.s-nav{flex:1;overflow-y:auto;padding:12px 14px}
.backdrop{position:fixed;inset:0;background:rgba(2,6,23,.55);z-index:45;opacity:0;visibility:hidden;pointer-events:none;transition:.3s}
.backdrop.show{opacity:1;visibility:visible;pointer-events:auto}
@keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:none}}
.fx{animation:fadeUp .55s cubic-bezier(.22,1,.36,1) both}.d1{animation-delay:.05s}.d2{animation-delay:.1s}.d3{animation-delay:.15s}
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
.i-green{background:#e0f5fb;color:#0e8fb5}.i-blue{background:#e8f1fd;color:#2563eb}.i-red{background:#feecec;color:#dc2626}.i-violet{background:#f1ecfd;color:#7c3aed}
body.dark .i-green{background:rgba(62,197,224,.12);color:#4ddcf5}body.dark .i-blue{background:rgba(96,165,250,.12);color:#60a5fa}
body.dark .i-red{background:rgba(248,113,113,.12);color:#f87171}body.dark .i-violet{background:rgba(167,139,250,.12);color:#a78bfa}
.big{font-size:29px;font-weight:800;letter-spacing:-.6px}.big.sm{font-size:23px}
.muted{color:var(--muted)}.small{font-size:12.5px}
@keyframes statPop{0%{transform:scale(.6);opacity:.2}60%{transform:scale(1.18)}100%{transform:scale(1);opacity:1}}
.stat-pop{animation:statPop .45s cubic-bezier(.34,1.56,.64,1)}
/* ★ CUSTOM DROPDOWN */
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
table{width:100%;border-collapse:collapse;min-width:680px}
th{font-size:11px;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);text-align:left;padding:14px 20px;border-bottom:1px solid var(--border);background:rgba(243,245,247,.4);white-space:nowrap;user-select:none}
body.dark th{background:rgba(11,18,32,.4)}
th.sortable{cursor:pointer}th.sortable:hover{color:var(--forest)}body.dark th.sortable:hover{color:var(--lime)}
th .s-ar{font-size:13px;opacity:.25;margin-left:3px}th.sorted{color:var(--forest)}body.dark th.sorted{color:var(--lime)}th.sorted .s-ar{opacity:1}
td{padding:14px 20px;border-bottom:1px solid var(--border);font-size:13.5px;vertical-align:middle}
tbody tr{transition:background .2s}
@media(hover:hover){tbody tr:hover{background:var(--lime-soft)}}
tbody tr:last-child td{border-bottom:none}
tbody tr.flash{animation:rowFlash 1.2s ease}
@keyframes rowFlash{0%{background:rgba(62,197,224,.25)}100%{background:transparent}}
.scell{display:flex;align-items:center;gap:14px;min-width:170px}
.bottle{width:34px;height:54px;min-width:34px;border-radius:9px 9px 12px 12px;border:2px solid rgba(14,90,117,.35);position:relative;display:flex;align-items:flex-end;overflow:hidden;background:rgba(62,197,224,.06);transition:.3s}
.bottle::before{content:'';position:absolute;top:-9px;left:50%;transform:translateX(-50%);width:14px;height:8px;border-radius:4px 4px 0 0;background:rgba(14,90,117,.35)}
.b-fill{display:block;width:100%;background:linear-gradient(180deg,var(--lime),var(--forest-2));border-radius:0 0 9px 9px;transition:height .6s}
@media(hover:hover){tbody tr:hover .bottle{border-color:var(--lime);box-shadow:0 0 14px rgba(62,197,224,.35)}}
.s-info strong{display:block;font-size:13.5px}.s-info span{font-size:11.5px;color:var(--muted)}
.s-date{display:inline-flex;align-items:center;gap:6px;color:var(--muted);font-size:12.5px;white-space:nowrap}
.pill{display:inline-flex;align-items:center;gap:6px;padding:5px 13px;border-radius:20px;font-size:11px;font-weight:800;white-space:nowrap}
.pill::before{content:'';width:6px;height:6px;border-radius:50%;background:currentColor;box-shadow:0 0 7px currentColor}
.pill.active{background:#dcfce7;color:#16a34a}.pill.hidden{background:#fef3c7;color:#b45309}
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
.empty h4{font-size:16px;margin-bottom:5px}.empty p{color:var(--muted);font-size:13px}
/* ═══════════════════════════════════════════════════════════
   ★ MODAL — EXACT categories / order-popup reference style
   · teal gradient header, bold white title LEFT, icon+subtitle
   · dark navy rounded-square × button
   · body = light-gray rounded cards (inputs flat gray)
   · teal uppercase labels with tiny icon
═══════════════════════════════════════════════════════════ */
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
/* teal labels — order-popup reference ke "ITEMS (2)" jaisi */
.fld{margin-bottom:22px}
.fld>label{display:flex;align-items:center;gap:8px;font-size:11px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#2f98a1;margin-bottom:10px}
body.dark .fld>label{color:var(--lime)}
.lb-ic{width:22px;height:22px;min-width:22px;border-radius:7px;background:rgba(56,170,180,.13);color:#2f98a1;display:grid;place-items:center;font-size:11px}
body.dark .lb-ic{background:rgba(62,197,224,.12);color:var(--lime)}
/* gray-card inputs — reference ke info boxes jaise */
.fld input{width:100%;min-height:54px;border:1.5px solid transparent;background:#f4f7f8;border-radius:14px;padding:15px 18px;font:600 13.5px 'Plus Jakarta Sans',sans-serif;color:var(--text);outline:none;transition:.25s;max-width:100%}
body.dark .fld input{background:rgba(255,255,255,.05)}
.fld input::placeholder{color:#9fb0c0;font-weight:500}
body.dark .fld input::placeholder{color:#5c718c}
.fld input:focus{border-color:var(--lime);background:var(--card);box-shadow:0 0 0 4px rgba(62,197,224,.14)}
/* quick ml chips — gray-card style */
.quick-row{display:grid;grid-template-columns:repeat(4,1fr);gap:9px;margin-top:12px}
.quick-ml{padding:12px 8px;border-radius:12px;border:1.5px solid transparent;background:#f4f7f8;font-size:12.5px;font-weight:800;color:var(--muted);cursor:pointer;transition:.2s;font-family:inherit}
body.dark .quick-ml{background:rgba(255,255,255,.05)}
@media(hover:hover){.quick-ml:hover{transform:translateY(-3px);border-color:var(--lime);background:var(--card);color:var(--forest);box-shadow:0 8px 18px rgba(62,197,224,.18)}}
body.dark .quick-ml:hover{color:var(--lime)}
/* toggle — gray card */
.switch-row{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:16px 18px;border-radius:14px;background:#f4f7f8;margin-bottom:24px}
body.dark .switch-row{background:rgba(255,255,255,.05)}
.switch-row b{font-size:13.5px}.switch-row span{display:block;font-size:11.5px;color:var(--muted);font-weight:600;margin-top:2px}
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
/* ═══ DELETE MODAL — same style, red gradient header ═══ */
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
@media(max-width:480px){.r-stats{grid-template-columns:1fr}.content{padding:14px 0 calc(40px + env(safe-area-inset-bottom))}.toolbar,.t-foot{padding-left:12px;padding-right:12px}.page-head .btn{width:100%;justify-content:center}.nb-modal{padding:10px}.quick-row{grid-template-columns:repeat(3,1fr)}.nb-modal-actions{flex-direction:column-reverse}.nb-modal-box{width:calc(100vw - 16px);border-radius:16px}}
@media(max-width:360px){.r-stats{grid-template-columns:1fr}}
/* ★★★ MODAL WIDTH LOCK — ID specificity navbar override ko haraam karti hai (categories jaisa) ★★★ */
#szModal .nb-modal-box{width:50%;max-width:none}
@media(max-width:1100px){#szModal .nb-modal-box{width:min(640px,calc(100vw - 40px))}}
@media(max-width:680px){#szModal .nb-modal-box{width:calc(100vw - 20px)}}
@media(max-width:480px){#szModal .nb-modal-box{width:calc(100vw - 16px)}}
#szDelModal .nb-modal-box{width:min(460px,100%);max-width:none}
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
        <h2><span class="ph-ic"><i class="ri-flask-line"></i></span>ML / Litres</h2>
        <p>Packing sizes for your products — 10 ML to 1 Litre and beyond.</p>
    </div>
    <button type="button" class="btn btn-lime" onclick="szOpenModal()"><i class="ri-add-line"></i>Add Size</button>
</div>

<section class="r-stats">
    <div class="card stat fx d1"><div class="stat-top"><div class="icon i-green"><i class="ri-flask-line"></i></div></div><div class="big sm" id="statTotal">{{ $stats['total'] ?? 0 }}</div><p class="muted small">Total sizes</p></div>
    <div class="card stat fx d2"><div class="stat-top"><div class="icon i-blue"><i class="ri-eye-line"></i></div></div><div class="big sm" id="statActive">{{ $stats['active'] ?? 0 }}</div><p class="muted small">Visible on store</p></div>
    <div class="card stat fx d3"><div class="stat-top"><div class="icon i-red"><i class="ri-eye-off-line"></i></div></div><div class="big sm" id="statHidden">{{ $stats['hidden'] ?? 0 }}</div><p class="muted small">Hidden</p></div>
    <div class="card stat fx d3"><div class="stat-top"><div class="icon i-violet"><i class="ri-calendar-line"></i></div></div><div class="big sm" id="statNew">{{ $stats['new'] ?? 0 }}</div><p class="muted small">Added this month</p></div>
</section>

<section class="card table-card fx d3">
    <div class="toolbar">
        <div class="t-search">
            <i class="ri-search-line"></i>
            <input type="text" id="szSearch" oninput="szGoPage(1)" placeholder="Search sizes...">
        </div>
        <div class="dd" id="szFilter" data-value="all">
            <button type="button" class="dd-btn" onclick="nxDdToggle('szFilter')"><i class="ri-filter-3-line"></i><span class="dd-label">All statuses</span><i class="ri-arrow-down-s-line dd-chev"></i></button>
            <div class="dd-menu">
                <button type="button" class="dd-item on" data-v="all" onclick="nxDdPick(this,'szFilter')">All statuses</button>
                <button type="button" class="dd-item" data-v="active" onclick="nxDdPick(this,'szFilter')">Active only</button>
                <button type="button" class="dd-item" data-v="hidden" onclick="nxDdPick(this,'szFilter')">Hidden only</button>
            </div>
        </div>
        <div class="dd" id="szPer" data-value="10" style="margin-left:auto">
            <button type="button" class="dd-btn" onclick="nxDdToggle('szPer')"><i class="ri-list-check"></i><span class="dd-label">10 rows</span><i class="ri-arrow-down-s-line dd-chev"></i></button>
            <div class="dd-menu">
                <button type="button" class="dd-item on" data-v="10" onclick="nxDdPick(this,'szPer')">10 rows</button>
                <button type="button" class="dd-item" data-v="25" onclick="nxDdPick(this,'szPer')">25 rows</button>
                <button type="button" class="dd-item" data-v="50" onclick="nxDdPick(this,'szPer')">50 rows</button>
                <button type="button" class="dd-item" data-v="999999" onclick="nxDdPick(this,'szPer')">All rows</button>
            </div>
        </div>
    </div>

    <div class="tbl-hint"><i class="ri-drag-move-2-line"></i> Swipe the table sideways to see all columns</div>

    <div class="table-wrap">
        <table data-server-pagination="true">
            <thead><tr>
                <th class="sortable" data-k="name" onclick="szSort(this)">Size<span class="s-ar">▼</span></th>
                <th class="sortable" data-k="ml" onclick="szSort(this)">Volume<span class="s-ar">▼</span></th>
                <th class="sortable" data-k="active" onclick="szSort(this)">Status<span class="s-ar">▼</span></th>
                <th class="sortable" data-k="ts" onclick="szSort(this)">Created<span class="s-ar">▼</span></th>
                <th style="text-align:right">Actions</th>
            </tr></thead>
            <tbody id="szBody">
                @foreach(($sizes ?? collect()) as $s)
                <tr data-id="{{ $s->id }}" data-name="{{ $s->name }}" data-ml="{{ $s->ml }}"
                    data-active="{{ $s->is_active ? 1 : 0 }}" data-ts="{{ $s->created_at->timestamp }}"
                    data-hay="{{ strtolower($s->name.' '.$s->ml) }}">
                    <td>
                        <div class="scell">
                            <span class="bottle"><i class="b-fill" style="height: {{ max(14, round($s->ml / max($maxMl ?? 100, 1) * 100)) }}%"></i></span>
                            <div class="s-info"><strong>{{ $s->name }}</strong><span>Packing size</span></div>
                        </div>
                    </td>
                    <td><strong>{{ number_format($s->ml) }}</strong> <span class="muted small">ml</span></td>
                    <td class="pill-cell"><button type="button" class="pill status-toggle {{ $s->is_active ? 'active' : 'hidden' }}" aria-pressed="{{ $s->is_active ? 'true' : 'false' }}" aria-label="{{ $s->is_active ? 'Hide' : 'Activate' }} {{ $s->name }}" onclick="szToggleStatus(this)">{{ $s->is_active ? 'Active' : 'Hidden' }}</button></td>
                    <td><span class="s-date"><i class="ri-time-line"></i>{{ $s->created_at->format('M d, Y') }}</span></td>
                    <td>
                        <div class="actions">
                            <button type="button" class="act act-edit" onclick="szOpenModal(this.closest('tr'))" title="Edit"><i class="ri-pencil-line"></i></button>
                            <button type="button" class="act act-del" onclick="szAskDelete(this)" title="Delete"><i class="ri-delete-bin-6-line"></i></button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="t-foot">
        <p class="info" id="szInfo"></p>
        <div class="pgn" id="szPgn"></div>
    </div>
</section>

@if(($sizes ?? collect())->isEmpty())
<div class="card empty fx" id="szEmpty">
    <div class="e-ic"><i class="ri-flask-line"></i></div>
    <h4>No sizes yet</h4><p>Add packings like 50 ML, 100 ML, 1 Litre.</p>
</div>
@endif

</main>
</div>
</div>

{{-- ═══ ADD/EDIT MODAL — categories / order-popup reference style ═══ --}}
<div class="nb-modal" id="szModal" onclick="if(event.target===this)szCloseModal()">
    <div class="nb-modal-box">
        <div class="nb-modal-head">
            <button type="button" class="nb-close" onclick="szCloseModal()" aria-label="Close"><i class="ri-close-line"></i></button>
            <h4 id="modalTitle">Add Size</h4>
            <p><i class="ri-information-line"></i><span id="modalSub">Create a new packing size</span></p>
        </div>
        <div class="nb-modal-body">
            <form id="szForm" onsubmit="return szSave(event)">
                <div class="fld">
                    <label><span class="lb-ic"><i class="ri-price-tag-3-line"></i></span><span>Size Label *</span></label>
                    <input type="text" name="name" id="fName" placeholder="e.g. 50 ML" maxlength="50" required>
                </div>
                <div class="fld">
                    <label><span class="lb-ic"><i class="ri-flask-line"></i></span><span>Volume (ML) *</span></label>
                    <input type="number" name="ml" id="fMl" placeholder="e.g. 50" min="1" max="100000" inputmode="numeric" required>
                    <div class="quick-row">
                        <button type="button" class="quick-ml" onclick="szQuick(this)">10</button>
                        <button type="button" class="quick-ml" onclick="szQuick(this)">30</button>
                        <button type="button" class="quick-ml" onclick="szQuick(this)">50</button>
                        <button type="button" class="quick-ml" onclick="szQuick(this)">100</button>
                        <button type="button" class="quick-ml" onclick="szQuick(this)">250</button>
                        <button type="button" class="quick-ml" onclick="szQuick(this)">500</button>
                        <button type="button" class="quick-ml" onclick="szQuick(this)">1000</button>
                    </div>
                </div>
                <div class="switch-row">
                    <div><b>Visible on store</b><span>Customers can pick this size</span></div>
                    <label class="switch"><input type="checkbox" name="is_active" id="fActive" checked><span class="slider"></span></label>
                </div>
                <div class="nb-modal-actions">
                    <button type="button" class="nb-btn ghost" onclick="szCloseModal()"><i class="ri-close-line"></i>Cancel</button>
                    <button type="submit" class="nb-btn save" id="saveBtn"><i class="ri-save-3-line"></i>Save Size</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══ DELETE MODAL — same style, red gradient header ═══ --}}
<div class="nb-modal" id="szDelModal" onclick="if(event.target===this)szCloseDel()">
    <div class="nb-modal-box del-modal-box">
        <div class="nb-modal-head del-head">
            <button type="button" class="nb-close" onclick="szCloseDel()" aria-label="Close"><i class="ri-close-line"></i></button>
            <h4>Delete Size</h4>
            <p><i class="ri-error-warning-line"></i><span>This action cannot be undone</span></p>
        </div>
        <div class="del-body">
            <div class="del-emoji"><i class="ri-delete-bin-6-line"></i></div>
            <h4>Delete "<span id="szDelName">?</span>"?</h4>
            <p>This size will be <b>permanently removed</b>.<br>Products using it will lose their size.</p>
            <div class="nb-modal-actions">
                <button type="button" class="nb-btn ghost" onclick="szCloseDel()"><i class="ri-arrow-left-line"></i>Cancel</button>
                <button type="button" class="nb-btn save" id="szDelGo" onclick="szDoDelete()"><i class="ri-delete-bin-line"></i>Yes, Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
/* ═══ URLS — Blade-rendered INSIDE script (SPA-safe) ═══ */
var szCsrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
var szStore  = "{{ route('admin.sizes.store') }}";
var szUpdTpl = "{{ route('admin.sizes.update','__ID__') }}";
var szDelTpl = "{{ route('admin.sizes.destroy','__ID__') }}";
var szStatusTpl = "{{ route('admin.sizes.status','__ID__') }}";
var szMaxMl = {{ $maxMl ?? 100 }} || 100;
var szEditId = null, szDelId = null;
var szTbl = { q: '', filter: 'all', sortK: 'ts', sortD: -1, page: 1, per: 10 };

/* ═══ HELPERS ═══ */
function szEsc(s) {
    return (s === null || s === undefined ? '' : String(s))
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
function szToast(icon, msg) {
    if (window.Swal) {
        Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2400,
            timerProgressBar: true, icon: icon, title: msg, buttonsStyling: false, customClass: { popup: 'swal-toast' } });
        return;
    }
    var toast = document.getElementById('adminNativeToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'adminNativeToast';
        toast.className = 'admin-native-toast';
        toast.setAttribute('role', icon === 'error' ? 'alert' : 'status');
        document.body.appendChild(toast);
    }
    toast.dataset.type = icon;
    toast.textContent = msg;
    toast.classList.add('visible');
    window.clearTimeout(window.__sizeToastTimer);
    window.__sizeToastTimer = window.setTimeout(function () { toast.classList.remove('visible'); }, 2600);
}

function szToggleStatus(btn) {
    var tr = btn.closest('tr');
    if (!tr || btn.disabled) return;

    var nextActive = tr.dataset.active !== '1';
    btn.disabled = true;

    fetch(szStatusTpl.replace('__ID__', tr.dataset.id), {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': szCsrf,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ is_active: nextActive ? 1 : 0, _token: szCsrf })
    })
    .then(function (response) {
        return response.json().catch(function () { return {}; }).then(function (data) {
            if (response.status === 419) throw new Error('Session expired — please refresh the page.');
            if (!response.ok) throw new Error(data.message || 'Could not update size status.');
            return data;
        });
    })
    .then(function (data) {
        tr.outerHTML = szRowHTML(data.size);
        szRefreshStats(false);
        szApply();
        szToast('success', data.message || 'Size status updated.');
    })
    .catch(function (error) {
        btn.disabled = false;
        szToast('error', error.message || 'Could not update size status.');
    });
}

/* ═══ CUSTOM DROPDOWNS — categories jaisa shared engine (SPA-compatible) ═══ */
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
/* har page apna callback re-define karta hai — jo page active hai uska chalega */
window.nxDdOnChange = function(id) {
    if (id === 'szFilter') { szTbl.filter = document.getElementById('szFilter').dataset.value; szGoPage(1); }
    if (id === 'szPer')    { szTbl.per = parseInt(document.getElementById('szPer').dataset.value, 10) || 10; szGoPage(1); }
};

/* outside-click close — guarded (SPA re-run killer, categories wala hi guard) */
if (!window.__nxDdOut) {
    window.__nxDdOut = 1;
    document.addEventListener('click', function(e) {
        document.querySelectorAll('.dd.open').forEach(function(d){ if (!d.contains(e.target)) d.classList.remove('open'); });
    });
}

/* ═══ TABLE ENGINE ═══ */
function szRows() { return Array.prototype.slice.call(document.querySelectorAll('#szBody tr')); }
function szGoPage(p) { szTbl.page = p; szApply(); }

function szSort(th) {
    var k = th.dataset.k;
    if (szTbl.sortK === k) szTbl.sortD *= -1; else { szTbl.sortK = k; szTbl.sortD = 1; }
    document.querySelectorAll('th.sortable').forEach(function (t) {
        t.classList.remove('sorted');
        var ar = t.querySelector('.s-ar');
        if (ar) { ar.textContent = '▼'; ar.style.opacity = ''; }
    });
    th.classList.add('sorted');
    var a = th.querySelector('.s-ar');
    if (a) a.textContent = szTbl.sortD === 1 ? '▲' : '▼';
    szApply();
}

function szApply() {
    if (!document.getElementById('szBody')) return; /* SPA stale-callback safety */
    var qEl = document.getElementById('szSearch');
    szTbl.q = ((qEl && qEl.value) || '').toLowerCase().trim();

    var rows = szRows().filter(function (tr) {
        var okQ = (tr.dataset.hay || '').indexOf(szTbl.q) !== -1;
        var okF = szTbl.filter === 'all' || tr.dataset.active === (szTbl.filter === 'active' ? '1' : '0');
        return okQ && okF;
    });
    rows.sort(function (a, b) {
        var va = a.dataset[szTbl.sortK] || '', vb = b.dataset[szTbl.sortK] || '';
        if (szTbl.sortK === 'ml' || szTbl.sortK === 'active' || szTbl.sortK === 'ts') {
            va = parseFloat(va) || 0; vb = parseFloat(vb) || 0;
            return (va - vb) * szTbl.sortD;
        }
        return va.localeCompare(vb) * szTbl.sortD;
    });

    var total = rows.length, tp = Math.max(1, Math.ceil(total / szTbl.per));
    if (szTbl.page > tp) szTbl.page = tp;
    var start = (szTbl.page - 1) * szTbl.per, end = szTbl.per >= total ? total : start + szTbl.per;

    szRows().forEach(function (tr) { tr.style.display = 'none'; });
    rows.forEach(function (tr, i) { if (i >= start && i < end) tr.style.display = ''; });

    var info = document.getElementById('szInfo');
    if (info) info.textContent = total
        ? 'Showing ' + (start + 1) + '–' + Math.min(end, total) + ' of ' + total + ' sizes'
        : 'No sizes found';

    szPgn(tp);

    var gridEmpty = !szRows().length;
    var es = document.getElementById('szEmpty');
    if (es) es.style.display = gridEmpty ? '' : 'none';
    var tc = document.querySelector('.table-card');
    if (tc) tc.style.display = gridEmpty ? 'none' : '';
}

function szPgn(tp) {
    var box = document.getElementById('szPgn');
    if (!box || tp < 1) return;
    box.innerHTML = '';
    var mk = function (label, p, dis, on) {
        var b = document.createElement('button');
        b.type = 'button';
        b.className = 'pg-btn' + (on ? ' on' : '');
        b.innerHTML = label;
        if (dis) b.disabled = true; else b.setAttribute('onclick', 'szGoPage(' + p + ')');
        box.appendChild(b);
    };
    mk('<i class="ri-arrow-left-double-line"></i>', 1, szTbl.page === 1);
    mk('<i class="ri-arrow-left-s-line"></i>', szTbl.page - 1, szTbl.page === 1);
    if (tp <= 7) {
        for (var i = 1; i <= tp; i++) mk(i, i, false, i === szTbl.page);
    } else {
        var s = Math.max(1, szTbl.page - 2), e = Math.min(tp, szTbl.page + 2);
        if (s > 1) { mk('1', 1); if (s > 2) box.insertAdjacentHTML('beforeend', '<span class="pg-dots">…</span>'); }
        for (var j = s; j <= e; j++) mk(j, j, false, j === szTbl.page);
        if (e < tp) { if (e < tp - 1) box.insertAdjacentHTML('beforeend', '<span class="pg-dots">…</span>'); mk(tp, tp); }
    }
    mk('<i class="ri-arrow-right-s-line"></i>', szTbl.page + 1, szTbl.page === tp);
    mk('<i class="ri-arrow-right-double-line"></i>', tp, szTbl.page === tp);
}

/* ═══ MODAL ═══ */
function szQuick(btn) {
    var v = parseInt(btn.textContent, 10);
    document.getElementById('fMl').value = v;
    document.getElementById('fName').value = v >= 1000 ? (v / 1000) + ' Litre' : v + ' ML';
}
function szOpenModal(tr) {
    szEditId = tr ? tr.dataset.id : null;
    document.getElementById('fName').value = tr ? tr.dataset.name : '';
    document.getElementById('fMl').value = tr ? tr.dataset.ml : '';
    document.getElementById('fActive').checked = tr ? tr.dataset.active === '1' : true;
    document.getElementById('modalTitle').textContent = tr ? 'Edit Size' : 'Add Size';
    document.getElementById('modalSub').textContent = tr ? ('Update "' + tr.dataset.name + '"') : 'Create a new packing size';
    document.getElementById('saveBtn').innerHTML = tr ? '<i class="ri-save-3-line"></i>Update Size' : '<i class="ri-save-3-line"></i>Save Size';
    document.getElementById('szModal').classList.add('show');
    document.body.classList.add('nb-lock');
    setTimeout(function () { document.getElementById('fName').focus(); }, 260);
}
function szCloseModal() {
    var m = document.getElementById('szModal');
    if (m) m.classList.remove('show');
    document.body.classList.remove('nb-lock');
    var f = document.getElementById('szForm');
    if (f) f.reset();
    szEditId = null;
}

/* ═══ DELETE MODAL ═══ */
function szAskDelete(btn) {
    var tr = btn.closest('tr');
    szDelId = tr.dataset.id;
    document.getElementById('szDelName').textContent = tr.dataset.name;
    document.getElementById('szDelModal').classList.add('show');
    document.body.classList.add('nb-lock');
}
function szCloseDel() {
    var m = document.getElementById('szDelModal');
    if (m) m.classList.remove('show');
    document.body.classList.remove('nb-lock');
    szDelId = null;
}
function szDoDelete() {
    if (!szDelId) return;
    var btn = document.getElementById('szDelGo');
    var tr = document.querySelector('#szBody tr[data-id="' + szDelId + '"]');
    btn.disabled = true;
    var fd = new FormData();
    fd.append('_method', 'DELETE');
    fd.append('_token', szCsrf);
    fetch(szDelTpl.replace('__ID__', szDelId), {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': szCsrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: fd
    })
    .then(function (r) { if (r.status === 419) throw new Error('Session expired.'); if (!r.ok) throw new Error('failed'); return r.json(); })
    .then(function (data) {
        if (tr) {
            tr.style.transition = 'all .4s'; tr.style.opacity = '0'; tr.style.transform = 'scale(.95)';
            setTimeout(function () { tr.remove(); szRefreshStats(false); szApply(); }, 400);
        }
        szCloseDel();
        szToast('success', (data && data.message) || 'Deleted.');
    })
    .catch(function () { szToast('error', 'Something went wrong.'); })
    .finally(function () { btn.disabled = false; });
}

/* ═══ ROW HTML ═══ */
function szRowHTML(s) {
    var pct = Math.max(14, Math.round((parseInt(s.ml, 10) || 0) / szMaxMl * 100));
    var d = s.created_at ? new Date(s.created_at) : new Date();
    var ts = Math.floor(d.getTime() / 1000);
    var dateStr = d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    return '<tr data-id="' + s.id + '" data-name="' + szEsc(s.name) + '" data-ml="' + s.ml + '" data-active="' + (s.is_active ? 1 : 0) + '" data-ts="' + ts + '" data-hay="' + szEsc((s.name + ' ' + s.ml).toLowerCase()) + '">' +
        '<td><div class="scell"><span class="bottle"><i class="b-fill" style="height:' + pct + '%"></i></span>' +
        '<div class="s-info"><strong>' + szEsc(s.name) + '</strong><span>Packing size</span></div></div></td>' +
        '<td><strong>' + Number(s.ml).toLocaleString() + '</strong> <span class="muted small">ml</span></td>' +
        '<td class="pill-cell"><button type="button" class="pill status-toggle ' + (s.is_active ? 'active' : 'hidden') +
        '" aria-pressed="' + (s.is_active ? 'true' : 'false') + '" aria-label="' + (s.is_active ? 'Hide ' : 'Activate ') +
        szEsc(s.name) + '" onclick="szToggleStatus(this)">' + (s.is_active ? 'Active' : 'Hidden') + '</button></td>' +
        '<td><span class="s-date"><i class="ri-time-line"></i>' + dateStr + '</span></td>' +
        '<td><div class="actions">' +
        '<button type="button" class="act act-edit" onclick="szOpenModal(this.closest(\'tr\'))" title="Edit"><i class="ri-pencil-line"></i></button>' +
        '<button type="button" class="act act-del" onclick="szAskDelete(this)" title="Delete"><i class="ri-delete-bin-6-line"></i></button>' +
        '</div></td></tr>';
}

function szRefreshStats(created) {
    var rows = szRows();
    var act = rows.filter(function (r) { return r.dataset.active === '1'; }).length;
    var set = function (id, v) {
        var el = document.getElementById(id);
        if (!el || el.textContent == String(v)) return;
        el.textContent = v;
        el.classList.remove('stat-pop'); void el.offsetWidth; el.classList.add('stat-pop');
    };
    set('statTotal', rows.length);
    set('statActive', act);
    set('statHidden', rows.length - act);
    if (created) set('statNew', (parseInt(document.getElementById('statNew').textContent, 10) || 0) + 1);
}

/* ═══ SAVE (AJAX) ═══ */
function szSave(e) {
    e.preventDefault();
    var btn = document.getElementById('saveBtn');
    var fd = new FormData(document.getElementById('szForm'));
    fd.set('is_active', document.getElementById('fActive').checked ? '1' : '0');
    fd.append('_token', szCsrf);
    if (szEditId) fd.append('_method', 'PATCH');
    btn.disabled = true;
    fetch(szEditId ? szUpdTpl.replace('__ID__', szEditId) : szStore, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': szCsrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: fd
    })
    .then(function (r) {
        if (r.status === 419) throw new Error('Session expired — please refresh.');
        if (r.status === 422) return r.json().then(function (er) { throw new Error(Object.values(er.errors)[0][0]); });
        if (!r.ok) throw new Error('Request failed');
        return r.json();
    })
    .then(function (data) {
        if (szEditId) {
            var old = document.querySelector('#szBody tr[data-id="' + szEditId + '"]');
            if (old) old.outerHTML = szRowHTML(data.size);
        } else {
            document.getElementById('szBody').insertAdjacentHTML('afterbegin', szRowHTML(data.size));
        }
        szRefreshStats(!szEditId);
        szCloseModal();
        szApply();
        szToast('success', (data && data.message) || 'Saved.');
    })
    .catch(function (err) { szToast('error', err.message || 'Something went wrong.'); })
    .finally(function () { btn.disabled = false; });
    return false;
}

/* ESC closes modals + dropdowns — guarded */
if (!window.__szEsc) {
    window.__szEsc = 1;
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        if (!document.getElementById('szModal')) return;
        document.querySelectorAll('.dd.open').forEach(function(d){ d.classList.remove('open'); });
        szCloseModal(); szCloseDel();
    });
}

szApply();
</script>
</body>
</html>