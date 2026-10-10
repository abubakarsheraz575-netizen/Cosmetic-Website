<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Shipping settings</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=3">
    <link rel="icon" href="{{ asset('favicon.ico') }}?v=3" sizes="any">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { box-sizing:border-box }
        body { margin:0; background:var(--bg,#f3f5f7); color:var(--text,#101828); font:14px 'Plus Jakarta Sans',sans-serif }
        .layout { min-height:100vh }
        .main { margin-left:268px; min-width:0 }
        .layout.collapsed .main { margin-left:84px }
        .content { max-width:1180px; margin:auto; padding:26px; display:grid; gap:20px }
        .head { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:14px }
        h1,h2,p { margin:0 }
        h1 { font-size:25px; letter-spacing:-.5px }
        .muted { color:var(--muted,#68788f) }
        .intro { margin-top:6px; line-height:1.6 }
        .badge { display:inline-flex; align-items:center; gap:7px; background:var(--lime-soft,#e4f6fb); color:var(--forest,#0e5a75); padding:8px 13px; border-radius:22px; font-size:11px; font-weight:800 }
        .badge i { font-size:15px }
        .card { background:var(--card,#fff); border:1px solid var(--border,#e8edf3); border-radius:17px; box-shadow:var(--shadow,0 8px 24px rgba(16,24,40,.05)); overflow:hidden }
        .card-head { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:21px 24px; border-bottom:1px solid var(--border,#e8edf3) }
        .card-head h2 { font-size:16px }
        .card-head p { color:var(--muted,#68788f); font-size:12px; margin-top:5px; line-height:1.5 }
        .card-head i { font-size:25px; color:#35bdd6 }
        .form { padding:24px; display:grid; gap:20px }
        .grid { display:grid; grid-template-columns:repeat(4,1fr); gap:15px }
        label { display:block; margin-bottom:8px; color:var(--muted,#68788f); font-size:11px; font-weight:800; letter-spacing:.06em; text-transform:uppercase }
        input { width:100%; min-height:48px; border:1px solid var(--border,#e8edf3); border-radius:11px; padding:12px 14px; background:var(--bg,#f3f5f7); color:var(--text,#101828); font:600 14px 'Plus Jakarta Sans',sans-serif; outline:none }
        input:focus { border-color:#35bdd6; box-shadow:0 0 0 3px rgba(53,189,214,.13) }
        .hint { padding:15px 17px; border:1px dashed var(--border,#e8edf3); border-radius:12px; color:var(--muted,#68788f); font-size:12px; line-height:1.65 }
        .hint strong { color:var(--text,#101828) }
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:46px; padding:11px 18px; border:0; border-radius:11px; color:#fff; background:linear-gradient(135deg,#0e5a75,#17739b); font:800 12px 'Plus Jakarta Sans',sans-serif; cursor:pointer; box-shadow:0 7px 16px rgba(14,90,117,.2) }
        .btn:disabled { opacity:.6; cursor:wait }
        .notes { display:grid; grid-template-columns:repeat(3,1fr); gap:14px }
        .shipping-toast { position:fixed;top:18px;right:18px;z-index:10000;max-width:min(420px,calc(100vw - 32px));padding:13px 17px;border:1px solid rgba(62,197,224,.3);border-radius:12px;background:#102a3a;color:#fff;box-shadow:0 16px 40px rgba(4,20,32,.25);font-weight:700;font-size:13px;opacity:0;transform:translateY(-8px);pointer-events:none;transition:.2s }
        .shipping-toast.visible { opacity:1;transform:none }
        .shipping-toast[data-type="error"] { background:#7f1d1d;border-color:rgba(255,255,255,.2) }
        .note { padding:18px; display:flex; gap:12px; align-items:flex-start; background:var(--card,#fff); border:1px solid var(--border,#e8edf3); border-radius:14px }
        .note i { color:#35bdd6; font-size:20px }
        .note b { display:block; font-size:12px; margin-bottom:4px }
        .note span { display:block; color:var(--muted,#68788f); font-size:11px; line-height:1.55 }
        @media(max-width:992px) { .main { margin-left:0 } }
        @media(max-width:760px) { .grid { grid-template-columns:repeat(2,1fr) } .notes { grid-template-columns:1fr } .content { padding:19px 14px } .form { padding:18px } .card-head { padding:18px } }
        @media(max-width:420px) { .grid { grid-template-columns:1fr } }
    </style>
    @include('admin.include.shell-layout')
</head>
<body>
    <div class="layout" id="layout">
    @include('admin.include.sidebar')
    <div class="main">
        @include('admin.include.navbar')
        <main class="content">
            <header class="head">
                <div>
                    <h1>Shipping settings</h1>
                    <p class="intro muted">Set delivery charges, the free-shipping threshold, and the estimated delivery window shown to shoppers.</p>
                </div>
                <span class="badge"><i class="ri-truck-line"></i> Store delivery</span>
            </header>
            <section class="card">
                <div class="card-head">
                    <div><h2>Checkout &amp; delivery estimates</h2><p>These values are used by checkout, including a surcharge for parcels above the included weight.</p></div>
                    <i class="ri-settings-3-line"></i>
                </div>
                <form class="form" id="shippingSettings">
                    <div class="grid">
                        <div><label for="setFree">Free shipping over (£)</label><input id="setFree" name="free_ship_over" type="number" min="0" max="100000" step="0.01" value="{{ $settings['free_over'] }}" required></div>
                        <div><label for="setCost">Shipping cost (£)</label><input id="setCost" name="ship_cost" type="number" min="0" max="10000" step="0.01" value="{{ $settings['cost'] }}" required></div>
                        <div><label for="setWeight">Included order weight (kg)</label><input id="setWeight" name="included_weight_kg" type="number" min="0" max="100000" step="0.001" value="{{ $settings['included_weight_kg'] }}" required></div>
                        <div><label for="setSurcharge">Per extra started kg (£)</label><input id="setSurcharge" name="surcharge_per_kg" type="number" min="0" max="10000" step="0.01" value="{{ $settings['surcharge_per_kg'] }}" required></div>
                        <div><label for="setMin">Minimum delivery (days)</label><input id="setMin" name="delivery_min_days" type="number" min="1" max="60" step="1" value="{{ $settings['min_days'] }}" required></div>
                        <div><label for="setMax">Maximum delivery (days)</label><input id="setMax" name="delivery_max_days" type="number" min="1" max="90" step="1" value="{{ $settings['max_days'] }}" required></div>
                    </div>
                    <div class="hint"><strong>How it works:</strong> orders reaching the free-shipping threshold waive standard delivery. The included weight applies to the whole cart; each started kg above it adds the per-kg surcharge. That surcharge still applies when standard delivery is free. The default surcharge is £0 until you set a rate. Maximum delivery days must be equal to or greater than minimum days.</div>
                    <div><button class="btn" id="saveButton" type="submit"><i class="ri-save-3-line"></i>Save shipping settings</button></div>
                </form>
            </section>
            <section class="notes">
                <article class="note"><i class="ri-shopping-bag-3-line"></i><div><b>Checkout pricing</b><span>Standard delivery and weight surcharges are calculated from the products and quantities in each new order.</span></div></article>
                <article class="note"><i class="ri-calendar-check-line"></i><div><b>Delivery estimates</b><span>Keep the window realistic so customers can plan around their order arrival.</span></div></article>
                <article class="note"><i class="ri-megaphone-line"></i><div><b>Product announcements</b><span>Send targeted product updates from the separate Announcements area.</span></div></article>
            </section>
        </main>
    </div>
    </div>
    <script>
        (function () {
        const form = document.getElementById('shippingSettings');
        const button = document.getElementById('saveButton');
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        function showToast(type, message) {
            if (window.Swal) {
                Swal.fire({toast:true,position:'top-end',icon:type,title:message,showConfirmButton:false,timer:type === 'error' ? 3500 : 2600,timerProgressBar:true});
                return;
            }
            let toast = document.getElementById('shippingToast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'shippingToast';
                toast.className = 'shipping-toast';
                document.body.appendChild(toast);
            }
            toast.dataset.type = type;
            toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
            toast.textContent = message;
            toast.classList.add('visible');
            window.clearTimeout(window.__shippingToastTimer);
            window.__shippingToastTimer = window.setTimeout(() => toast.classList.remove('visible'), 3000);
        }
        form.addEventListener('submit', async event => {
            event.preventDefault();
            if (!form.reportValidity()) return;
            button.disabled = true;
            const body = new FormData(form);
            body.append('_token', csrf);
            try {
                const response = await fetch(@json(route('admin.shipping.settings')), {
                    method:'POST',
                    headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':csrf},
                    body
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    const first = data.errors && Object.values(data.errors)[0];
                    throw new Error(first ? first[0] : (data.message || 'Unable to save settings.'));
                }
                showToast('success', data.message || 'Shipping settings saved.');
            } catch (error) {
                showToast('error', error.message);
            } finally {
                button.disabled = false;
            }
        });
        })();
    </script>
</body>
</html>
