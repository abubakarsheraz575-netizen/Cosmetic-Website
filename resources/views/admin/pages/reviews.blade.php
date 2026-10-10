{{-- ==========================================================
     ADMIN · REVIEWS MODERATION — v1.0 (approve / block / delete)
     Ocean/Aqua · full-width · dark mode · SPA-safe (onclick only)
========================================================== --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0e5a75">
    <title>NiceDash — Reviews</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}?v=3">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box }
        :root { --bg:#f3f5f7; --card:#ffffff; --text:#101828; --muted:#68788f; --border:#e8edf3; --forest:#0e5a75; --forest-2:#17739b; --lime:#3ec5e0; --lime-soft:#e4f6fb; --shadow:0 1px 2px rgba(16,24,40,.04),0 8px 24px rgba(16,24,40,.05); --radius:16px }
        body.dark { --bg:#0b1220; --card:#111c2e; --text:#e7edf5; --muted:#8ba0b8; --border:#1e2c42; --lime-soft:rgba(62,197,224,.08); --shadow:0 1px 2px rgba(0,0,0,.3),0 10px 30px rgba(0,0,0,.35) }
        body { font-family:'Plus Jakarta Sans',sans-serif; background:var(--bg); color:var(--text); font-size:14px; transition:background .4s,color .4s; -webkit-text-size-adjust:100%; -webkit-tap-highlight-color:transparent; overflow-x:clip }
        ::-webkit-scrollbar { width:9px; height:9px }
        ::-webkit-scrollbar-thumb { background:#c7d2de; border-radius:10px; border:2px solid var(--bg) }
        body.dark ::-webkit-scrollbar-thumb { background:#24334d }
        button { font-family:inherit; user-select:none }

        .layout { min-height:100vh }
        .main { margin-left:268px; min-width:0; transition:margin .35s cubic-bezier(.4,0,.2,1) }
        .layout.collapsed .main { margin-left:84px }
        .sidebar { position:fixed; left:0; top:0; bottom:0; width:268px; background:var(--card); border-right:1px solid var(--border); display:flex; flex-direction:column; z-index:50; transition:width .35s cubic-bezier(.4,0,.2,1),transform .35s cubic-bezier(.4,0,.2,1) }
        .layout.collapsed .sidebar { width:84px }
        .s-nav { flex:1; overflow-y:auto; padding:12px 14px }
        .backdrop { position:fixed; inset:0; background:rgba(2,6,23,.55); z-index:45; opacity:0; visibility:hidden; pointer-events:none; transition:.3s }
        .backdrop.show { opacity:1; visibility:visible; pointer-events:auto }
        .content { padding:24px 26px calc(40px + env(safe-area-inset-bottom)); display:grid; gap:20px; min-width:0 }

        .rv-wrap { width:100%; display:grid; gap:20px; min-width:0 }
        .page-head { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:14px; width:100% }
        .page-head h2 { font-size:24px; font-weight:800; letter-spacing:-.4px }
        .page-head p { color:var(--muted); font-size:13px; margin-top:3px }

        .chip { display:inline-flex; align-items:center; gap:7px; padding:7px 14px; border-radius:20px; font-size:12px; font-weight:800; letter-spacing:.4px }
        .chip::before { content:''; width:7px; height:7px; border-radius:50%; background:currentColor; box-shadow:0 0 8px currentColor }
        .chip.warn { background:#fef3c7; color:#b45309 }
        .chip.ok { background:#dcfce7; color:#16a34a }
        body.dark .chip.warn { background:rgba(251,191,36,.12); color:#fbbf24 }
        body.dark .chip.ok { background:rgba(74,222,128,.12); color:#4ade80 }

        /* ─── FILTER TABS ─── */
        .ftabs { display:flex; gap:8px; flex-wrap:wrap }
        .ftab { display:inline-flex; align-items:center; gap:8px; padding:9px 16px; border-radius:20px; border:1px solid var(--border); background:var(--card); font-size:12.5px; font-weight:800; color:var(--muted); transition:.2s; text-decoration:none }
        .ftab b { background:var(--bg); border:1px solid var(--border); padding:1px 9px; border-radius:12px; font-size:11px }
        .ftab.active { background:linear-gradient(135deg,var(--forest),var(--forest-2)); color:#fff; border-color:transparent; box-shadow:0 8px 20px rgba(14,90,117,.3) }
        .ftab.active b { background:rgba(255,255,255,.2); color:#fff; border-color:transparent }
        @media (hover:hover) { .ftab:hover:not(.active) { border-color:var(--lime); color:var(--forest); transform:translateY(-2px) } }
        body.dark .ftab:hover:not(.active) { color:var(--lime) }
        .rv-toolbar { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap }
        .rv-search { min-height:44px; width:min(100%,360px); border:1.5px solid var(--border); border-radius:12px; background:var(--card); color:var(--text); padding:10px 14px; font:600 13px 'Plus Jakarta Sans',sans-serif; outline:none }
        .rv-search:focus { border-color:var(--lime); box-shadow:0 0 0 3px rgba(62,197,224,.12) }
        .rv-page-info { color:var(--muted); font-size:12px; font-weight:700 }
        .rv-pages { display:flex; align-items:center; gap:6px; flex-wrap:wrap }
        .rv-pages button { min-width:34px; height:34px; border:1px solid var(--border); border-radius:9px; background:var(--card); color:var(--muted); font-weight:800; cursor:pointer }
        .rv-pages button.active { background:var(--forest); border-color:var(--forest); color:#fff }
        .rv-pages button:disabled { opacity:.45; cursor:not-allowed }

        /* ─── REVIEW CARDS ─── */
        .rgrid { display:grid; grid-template-columns:repeat(auto-fill,minmax(360px,1fr)); gap:16px }
        .rcard { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:18px; display:grid; gap:11px; align-content:start; border-left:4px solid var(--border); transition:transform .25s, box-shadow .25s, opacity .35s, border-left-color .3s; min-width:0 }
        @media (hover:hover) { .rcard:hover { transform:translateY(-3px); box-shadow:0 16px 36px rgba(16,24,40,.10) } }
        .rcard[data-status="pending"]  { border-left-color:#f59e0b }
        .rcard[data-status="approved"] { border-left-color:#22c55e }
        .rcard[data-status="blocked"]  { border-left-color:#ef4444 }
        .rcard.removing { opacity:0; transform:scale(.94); pointer-events:none }

        .rc-top { display:flex; align-items:center; gap:12px }
        .rc-av { width:40px; height:40px; min-width:40px; border-radius:12px; display:grid; place-items:center; font-size:15px; font-weight:800; color:#062a38; background:linear-gradient(135deg,#7edcf2,#3fb0d8) }
        .rc-who { min-width:0; line-height:1.3 }
        .rc-who b { display:block; font-size:13.5px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap }
        .rc-who span { font-size:11px; color:var(--muted) }
        .st-pill { margin-left:auto; flex-shrink:0; font-size:10px; font-weight:800; letter-spacing:.6px; text-transform:uppercase; padding:5px 12px; border-radius:20px }
        .st-pill.pending  { background:#fef3c7; color:#b45309 }
        .st-pill.approved { background:#dcfce7; color:#16a34a }
        .st-pill.blocked  { background:#fee2e2; color:#dc2626 }
        body.dark .st-pill.pending  { background:rgba(251,191,36,.12); color:#fbbf24 }
        body.dark .st-pill.approved { background:rgba(74,222,128,.12); color:#4ade80 }
        body.dark .st-pill.blocked  { background:rgba(248,113,113,.12); color:#f87171 }

        .rs { color:#dfe3e5; font-size:15px; letter-spacing:2px }
        .rs.on { color:#f0b429 }

        .rc-prod { display:inline-flex; align-items:center; gap:7px; font-size:11.5px; font-weight:700; color:var(--forest); background:var(--lime-soft); padding:5px 12px; border-radius:20px; align-self:start; max-width:100%; text-decoration:none }
        body.dark .rc-prod { color:var(--lime) }
        .rc-prod i { font-size:13px }
        .rc-prod.none { color:var(--muted); background:var(--bg) }
        .rc-prod span { overflow:hidden; text-overflow:ellipsis; white-space:nowrap }
        .rc-product { display:flex;align-items:center;gap:12px;min-width:0;padding:9px;border:1px solid var(--border);border-radius:12px;background:var(--bg);text-decoration:none;color:inherit; }
        .rc-product-image { width:68px;height:68px;flex:none;display:grid;place-items:center;object-fit:cover;border-radius:9px;background:var(--lime-soft);color:var(--forest);font-size:22px; }
        .rc-product-meta { min-width:0;display:grid;gap:4px; }
        .rc-product-meta strong { overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12.5px; }
        .rc-product-meta span { color:var(--muted);font-size:11px;line-height:1.4; }
        .rc-customer-meta { display:grid;gap:2px;margin-left:52px;margin-top:-5px;color:var(--muted);font-size:11px; }
        .rc-customer-meta span { overflow-wrap:anywhere; }

        .rc-text { font-size:13px; line-height:1.7; color:var(--text); opacity:.9; word-break:break-word }

        .rc-actions { display:flex; gap:8px; flex-wrap:wrap; padding-top:4px; border-top:1px dashed var(--border) }
        .ra { display:inline-flex; align-items:center; gap:6px; padding:8px 14px; border-radius:10px; font-size:11.5px; font-weight:800; border:1px solid var(--border); background:var(--bg); color:var(--muted); cursor:pointer; transition:.2s }
        .ra i { font-size:14px }
        @media (hover:hover) { .ra:hover { transform:translateY(-2px) } }
        .ra.ok   { color:#16a34a; border-color:rgba(34,197,94,.35); background:rgba(34,197,94,.08) }
        .ra.bad  { color:#dc2626; border-color:rgba(239,68,68,.35); background:rgba(239,68,68,.08) }
        .ra.warn { color:#b45309; border-color:rgba(245,158,11,.35); background:rgba(245,158,11,.08) }
        body.dark .ra.ok   { color:#4ade80 } body.dark .ra.bad { color:#f87171 } body.dark .ra.warn { color:#fbbf24 }
        @media (hover:hover) { .ra.ok:hover { background:#16a34a; color:#fff } .ra.bad:hover { background:#dc2626; color:#fff } .ra.warn:hover { background:#d97706; color:#fff } }
        .ra.del { margin-left:auto }
        .ra.del:hover { background:#dc2626; color:#fff; border-color:#dc2626 }

        /* status ke hisaab se irrelevant buttons hide (JS sirf data-status badalta hai) */
        .rcard[data-status="approved"] .ra-approve { display:none }
        .rcard[data-status="blocked"]  .ra-block   { display:none }
        .rcard[data-status="pending"]  .ra-pend    { display:none }

        .empty { text-align:center; padding:70px 20px; background:var(--card); border:1px dashed var(--border); border-radius:var(--radius) }
        .empty .e-ic { font-size:52px; margin-bottom:12px }
        .empty h3 { font-size:17px; font-weight:800; margin-bottom:5px }
        .empty p { color:var(--muted); font-size:13px }

        .reveal { opacity:0; transform:translateY(20px); transition:opacity .45s ease,transform .45s cubic-bezier(.22,1,.36,1) }
        .reveal.show { opacity:1; transform:none }

        @media(max-width:992px) {
            .sidebar { transform:translateX(-105%); width:268px !important }
            .layout.mobile-open .sidebar { transform:none }
            .main { margin-left:0 !important }
            .content { padding:18px 14px calc(40px + env(safe-area-inset-bottom)) }
        }
        @media(max-width:680px) {
            .page-head h2 { font-size:20px }
            .rgrid { grid-template-columns:1fr }
        }
    </style>
</head>

<body>
    <div class="layout" id="layout">
        @include('admin.include.sidebar')
        <div class="main">
            @include('admin.include.navbar')
            <main class="content">

                <div class="page-head reveal">
                    <div>
                        <h2>Reviews</h2>
                        <p>Approve karo to Testimonials pe live hoga — block karo to website se hide.</p>
                    </div>
                    <span class="chip {{ $stats['pending'] ? 'warn' : 'ok' }}" id="chipPend">
                        {{ $stats['pending'] }} Pending Approval
                    </span>
                </div>

                <div class="rv-wrap">

                    <!-- FILTER TABS -->
                    <div class="ftabs reveal">
                        <a href="{{ route('admin.reviews') }}" class="ftab {{ $filter === 'all' ? 'active' : '' }}">All <b id="cAll">{{ $stats['total'] }}</b></a>
                        <a href="{{ route('admin.reviews', ['filter' => 'pending']) }}" class="ftab {{ $filter === 'pending' ? 'active' : '' }}">⏳ Pending <b id="cPending">{{ $stats['pending'] }}</b></a>
                        <a href="{{ route('admin.reviews', ['filter' => 'approved']) }}" class="ftab {{ $filter === 'approved' ? 'active' : '' }}">✅ Approved <b id="cApproved">{{ $stats['approved'] }}</b></a>
                        <a href="{{ route('admin.reviews', ['filter' => 'blocked']) }}" class="ftab {{ $filter === 'blocked' ? 'active' : '' }}">🚫 Blocked <b id="cBlocked">{{ $stats['blocked'] }}</b></a>
                    </div>
                    <div class="rv-toolbar reveal">
                        <input class="rv-search" id="rvSearch" type="search" placeholder="Search reviews, customers, products…" oninput="rvSearch(this.value)">
                        <span class="rv-page-info" id="rvPageInfo"></span>
                        <div class="rv-pages" id="rvPages" aria-label="Review pages"></div>
                    </div>

                    <!-- REVIEW CARDS -->
                    @forelse ($reviews as $r)
                    @php
                        $p = $r->product;
                        $productImage = $p?->images->first();
                        $initial = strtoupper(substr(trim($r->name), 0, 1));
                        $searchEmail = $r->user?->email ?? '';
                    @endphp
                    <div class="rcard reveal"
                         data-id="{{ $r->id }}"
                         data-status="{{ $r->status }}"
                         data-status-url="{{ route('admin.reviews.status', $r->id) }}"
                         data-del-url="{{ route('admin.reviews.destroy', $r->id) }}"
                         data-search="{{ strtolower($r->name . ' ' . $searchEmail . ' ' . ($p->name ?? '') . ' ' . $r->comment) }}">

                        <div class="rc-top">
                            <span class="rc-av">{{ $initial }}</span>
                            <div class="rc-who">
                                <b>{{ $r->name }}</b>
                                <span>{{ $r->created_at->format('M d, Y · h:i A') }}</span>
                            </div>
                            <span class="st-pill {{ $r->status }}">{{ $r->status }}</span>
                        </div>
                        @if($r->user)
                        <div class="rc-customer-meta">
                            <span><i class="ri-mail-line" aria-hidden="true"></i> {{ $r->user->email }}</span>
                            <span>Customer account #{{ $r->user->id }}</span>
                        </div>
                        @endif

                        <div>
                            @for ($i = 1; $i <= 5; $i++)
                            <span class="rs {{ $i <= $r->rating ? 'on' : '' }}">★</span>
                            @endfor
                        </div>

                        @if ($p)
                        <a class="rc-product" href="{{ route('admin.products.show', $p->id) }}">
                            @if($productImage)
                            <img class="rc-product-image" src="{{ asset('storage/' . $productImage->path) }}" alt="{{ $p->name }}" loading="lazy">
                            @else
                            <span class="rc-product-image" aria-hidden="true">{{ $p->emoji ?: '🧴' }}</span>
                            @endif
                            <span class="rc-product-meta">
                                <strong>{{ $p->name }}</strong>
                                <span>{{ $p->category?->name ?? 'Uncategorized' }} · SKU GL-{{ str_pad($p->id, 4, '0', STR_PAD_LEFT) }}</span>
                                <span>£{{ number_format($p->final_price, 2) }} · {{ $p->stock }} in stock</span>
                                <span>View product details <i class="ri-arrow-right-line" aria-hidden="true"></i></span>
                            </span>
                        </a>
                        @else
                        <span class="rc-prod none"><i class="ri-shopping-bag-3-line"></i><span>Product removed</span></span>
                        @endif

                        <p class="rc-text">{!! nl2br(e($r->comment)) !!}</p>

                        <div class="rc-actions">
                            <button type="button" class="ra ok ra-approve" onclick="rvSet({{ $r->id }}, 'approved')"><i class="ri-check-line"></i>Approve</button>
                            <button type="button" class="ra bad ra-block" onclick="rvSet({{ $r->id }}, 'blocked')"><i class="ri-forbid-line"></i>Block</button>
                            <button type="button" class="ra warn ra-pend" onclick="rvSet({{ $r->id }}, 'pending')"><i class="ri-time-line"></i>Pending</button>
                            <button type="button" class="ra del" onclick="rvDel({{ $r->id }})"><i class="ri-delete-bin-6-line"></i></button>
                        </div>
                    </div>
                    @empty
                    <div class="empty reveal">
                        <div class="e-ic">⭐</div>
                        <h3>No reviews here yet</h3>
                        <p>Jaise hi customers review denge, wo yahan moderation ke liye aayenge.</p>
                    </div>
                    @endforelse
                    <div class="empty" id="rvNoResults" style="display:none">
                        <div class="e-ic">🔎</div>
                        <h3>No matching reviews</h3>
                        <p>Try another search or review status.</p>
                    </div>

                </div>

            </main>
        </div>
    </div>

    <script>
        var rvCsrf = document.querySelector('meta[name="csrf-token"]').content;
        var CUR_FILTER = "{{ $filter }}";
        var RV_LIST = { query:'', page:1, perPage:9 };

        function rvToast(icon, title) {
            Swal.fire({ toast:true, position:'top-end', showConfirmButton:false, timer:2400,
                timerProgressBar:true, icon:icon, title:title,
                background:'#111c2e', color:'#e7edf5', confirmButtonColor:'#3ec5e0' });
        }

        function bumpCount(id, delta) {
            var el = document.getElementById(id);
            if (!el) return;
            var v = (parseInt(el.textContent, 10) || 0) + delta;
            el.textContent = v < 0 ? 0 : v;
        }

        function syncChip() {
            var chip = document.getElementById('chipPend');
            if (!chip) return;
            var pend = parseInt((document.getElementById('cPending') || {}).textContent || '0', 10);
            chip.textContent = pend + ' Pending Approval';
            chip.className = 'chip ' + (pend ? 'warn' : 'ok');
        }

        function rvSearch(value) {
            RV_LIST.query = value.trim().toLowerCase();
            RV_LIST.page = 1;
            applyRvList();
        }

        function applyRvList() {
            var cards = Array.prototype.slice.call(document.querySelectorAll('.rcard'));
            var noCards = cards.length === 0;
            var matches = cards.filter(function(card) {
                return (CUR_FILTER === 'all' || card.dataset.status === CUR_FILTER) &&
                    (!RV_LIST.query || (card.dataset.search || '').indexOf(RV_LIST.query) !== -1);
            });
            var pages = Math.max(1, Math.ceil(matches.length / RV_LIST.perPage));
            RV_LIST.page = Math.min(RV_LIST.page, pages);
            var start = (RV_LIST.page - 1) * RV_LIST.perPage;
            cards.forEach(function(card) { card.style.display = 'none'; });
            matches.forEach(function(card, index) {
                card.style.display = index >= start && index < start + RV_LIST.perPage ? '' : 'none';
            });
            var info = document.getElementById('rvPageInfo');
            if (info) info.textContent = matches.length
                ? 'Showing ' + (start + 1) + '–' + Math.min(start + RV_LIST.perPage, matches.length) + ' of ' + matches.length + ' reviews'
                : 'No reviews found';
            var empty = document.getElementById('rvNoResults');
            if (empty) empty.style.display = matches.length || noCards ? 'none' : '';
            var box = document.getElementById('rvPages');
            if (!box) return;
            box.innerHTML = '';
            function addPage(label, page, disabled, active) {
                var button = document.createElement('button');
                button.type = 'button';
                button.textContent = label;
                button.disabled = disabled;
                if (active) button.className = 'active';
                button.addEventListener('click', function() { RV_LIST.page = page; applyRvList(); });
                box.appendChild(button);
            }
            addPage('‹', RV_LIST.page - 1, RV_LIST.page === 1, false);
            for (var p = 1; p <= pages; p++) addPage(String(p), p, false, RV_LIST.page === p);
            addPage('›', RV_LIST.page + 1, RV_LIST.page === pages, false);
        }

        document.querySelectorAll('.ftab').forEach(function(tab) {
            tab.addEventListener('click', function(event) {
                event.preventDefault();
                CUR_FILTER = new URL(tab.href).searchParams.get('filter') || 'all';
                document.querySelectorAll('.ftab').forEach(function(item) { item.classList.remove('active'); });
                tab.classList.add('active');
                RV_LIST.page = 1;
                history.pushState({}, '', tab.href);
                applyRvList();
            });
        });
        window.addEventListener('popstate', function() {
            CUR_FILTER = new URL(window.location.href).searchParams.get('filter') || 'all';
            document.querySelectorAll('.ftab').forEach(function(tab) {
                var filter = new URL(tab.href).searchParams.get('filter') || 'all';
                tab.classList.toggle('active', filter === CUR_FILTER);
            });
            RV_LIST.page = 1;
            applyRvList();
        });

        function fadeRemove(card) {
            card.classList.add('removing');
            setTimeout(function () { card.remove(); applyRvList(); }, 360);
        }

        function rvSet(id, st) {
            var card = document.querySelector('.rcard[data-id="' + id + '"]');
            if (!card) return;
            var old = card.dataset.status;
            if (old === st) return;

            var fd = new FormData();
            fd.append('_method', 'PATCH');
            fd.append('_token', rvCsrf);
            fd.append('status', st);

            fetch(card.dataset.statusUrl, {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-CSRF-TOKEN': rvCsrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function (r) {
                    return r.json().catch(function () { return {}; }).then(function (d) {
                        if (!r.ok) throw new Error(d.message || 'Failed (' + r.status + ')');
                        return d;
                    });
                })
                .then(function (d) {
                    card.dataset.status = st;
                    var pill = card.querySelector('.st-pill');
                    if (pill) { pill.className = 'st-pill ' + st; pill.textContent = st; }
                    bumpCount('c' + old.charAt(0).toUpperCase() + old.slice(1), -1);
                    bumpCount('c' + st.charAt(0).toUpperCase() + st.slice(1), 1);
                    syncChip();
                    RV_LIST.page = 1;
                    applyRvList();
                    rvToast('success', d.message);
                })
                .catch(function (err) { rvToast('error', err.message || 'Something went wrong.'); });
        }

        function rvDel(id) {
            var card = document.querySelector('.rcard[data-id="' + id + '"]');
            if (!card) return;

            Swal.fire({
                title: 'Delete this review?',
                text: 'Ye action undo nahi ho sakta.',
                icon: 'warning', showCancelButton: true,
                confirmButtonColor: '#dc2626', cancelButtonColor: '#475569',
                confirmButtonText: 'Yes, delete', cancelButtonText: 'Cancel',
                background: '#111c2e', color: '#e7edf5'
            }).then(function (res) {
                if (!res.isConfirmed) return;
                var fd = new FormData();
                fd.append('_method', 'DELETE');
                fd.append('_token', rvCsrf);

                fetch(card.dataset.delUrl, {
                        method: 'POST',
                        body: fd,
                        headers: { 'X-CSRF-TOKEN': rvCsrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(function (r) {
                        return r.json().catch(function () { return {}; }).then(function (d) {
                            if (!r.ok) throw new Error(d.message || 'Failed (' + r.status + ')');
                            return d;
                        });
                    })
                    .then(function (d) {
                        bumpCount('c' + card.dataset.status.charAt(0).toUpperCase() + card.dataset.status.slice(1), -1);
                        bumpCount('cAll', -1);
                        syncChip();
                        fadeRemove(card);
                        rvToast('success', d.message);
                    })
                    .catch(function (err) { rvToast('error', err.message || 'Something went wrong.'); });
            });
        }

        (function() {
            applyRvList();
            var io = ('IntersectionObserver' in window) ? new IntersectionObserver(function(es) {
                es.forEach(function(e) { if (e.isIntersecting) { e.target.classList.add('show'); io.unobserve(e.target); } });
            }, { threshold:.06 }) : null;
            document.querySelectorAll('.reveal').forEach(function(el) { io ? io.observe(el) : el.classList.add('show'); });
            setTimeout(function() {
                document.querySelectorAll('.reveal:not(.show)').forEach(function(el) { el.classList.add('show'); });
            }, 1200);
        })();
    </script>
</body>

</html>