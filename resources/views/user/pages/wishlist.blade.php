{{-- ============================================================
     MY WISHLIST — REAL DATA
     · Whole product image click → add to bag + drawer opens
     · Floating bag button (bottom-right) always visible
============================================================ --}}
@include('user.include.base')

@php
    $user      = auth()->user();
    $firstName = explode(' ', trim($user->name))[0];
    $wishValue = $items->sum(fn ($w) => (float) $w->product->final_price);
    $inStockN  = $items->filter(fn ($w) => $w->product->stock > 0)->count();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}?v=3">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Wishlist — Cosmetics Store</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Allura&display=swap" rel="stylesheet">
    @include('user.include.base')
    <style>
        .page-title h1 { color: var(--ink); font-size: 22px; font-weight: 600; }
        .page-title p { font-size: 12.5px; color: rgba(22, 35, 60, .5); margin-top: 3px; }
        .reveal { opacity: 1 !important; transform: none !important; animation: none !important; }

        .wish-head { display: flex; align-items: center; flex-wrap: wrap; gap: 20px; padding: 24px 28px; margin-bottom: 26px; }
        .wish-head .ghost { font-family: 'Allura', cursive; font-size: 64px; line-height: 1; color: rgba(56, 170, 180, .14); pointer-events: none; }
        .wish-head h2 { color: var(--ink); font-size: 17px; font-weight: 600; }
        .wish-head p { font-size: 12.5px; }
        .wish-stats { margin-left: auto; display: flex; gap: 26px; }
        .ws { text-align: center; }
        .ws b { display: block; color: var(--teal-2); font-size: 22px; font-weight: 700; line-height: 1.1; }
        .ws span { font-size: 10px; letter-spacing: 1.2px; text-transform: uppercase; color: rgba(22, 35, 60, .45); }

        .wish-tools { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 24px; }
        .w-search { position: relative; flex: 1; min-width: 220px; max-width: 340px; }
        .w-search input { width: 100%; padding: 12px 14px 12px 42px; border-radius: 13px; border: 1px solid var(--glass-border); background: var(--glass); color: var(--ink); font-family: inherit; font-size: 13px; outline: none; backdrop-filter: blur(14px); transition: .3s; }
        .w-search input:focus { border-color: rgba(56, 170, 180, .7); box-shadow: 0 0 0 4px rgba(56, 170, 180, .16); }
        .w-search svg { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: rgba(22, 35, 60, .45); }
        .w-sort { margin-left: auto; padding: 12px 16px; border-radius: 13px; border: 1px solid var(--glass-border); background: var(--glass); color: var(--ink); font-family: inherit; font-size: 13px; outline: none; cursor: pointer; }

        .wish-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
        .wish-card { overflow: hidden; position: relative; transition: transform .35s, box-shadow .35s; }
        .wish-card:hover { transform: translateY(-6px); box-shadow: 0 26px 55px rgba(31, 51, 89, .14); }
        .wish-card.hiding { transition: all .4s; opacity: 0; transform: scale(.9); }

        .wish-thumb { position: relative; aspect-ratio: 3 / 3.2; overflow: hidden; background: #f6f9f9; cursor: pointer; }
        .wish-thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform .6s ease; }
        .wish-card:hover .wish-thumb img { transform: scale(1.08); }
        .w-emoji { width: 100%; height: 100%; display: grid; place-items: center; font-size: 56px; }

        .wish-thumb::after {
            content: '🛒 Add to Bag';
            position: absolute; inset: 0;
            display: flex; align-items: center; justify-content: center;
            background: rgba(13, 59, 64, .45);
            backdrop-filter: blur(2px);
            color: #fff; font: 600 12px 'Poppins', sans-serif;
            letter-spacing: 1.6px; text-transform: uppercase;
            opacity: 0; transition: opacity .3s;
            pointer-events: none;
        }
        .wish-card:hover .wish-thumb::after { opacity: 1; }
        .wish-card.touch .wish-thumb::after { opacity: 0; }

        .stock-badge { position: absolute; top: 12px; left: 12px; padding: 4px 11px; border-radius: 20px; font-size: 9.5px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; backdrop-filter: blur(8px); z-index: 2; }
        .stock-badge.in { background: rgba(46, 193, 129, .92); color: #fff; }
        .stock-badge.out { background: rgba(22, 35, 60, .75); color: #ffb3be; }

        .heart-btn { position: absolute; top: 12px; right: 12px; width: 36px; height: 36px; border-radius: 50%; border: none; background: rgba(255, 255, 255, .85); box-shadow: 0 4px 12px rgba(31, 51, 89, .15); backdrop-filter: blur(8px); color: #ff6b81; display: grid; place-items: center; transition: all .3s; cursor: pointer; z-index: 3; }
        .heart-btn:hover { transform: scale(1.15); }
        .heart-btn.busy { pointer-events: none; opacity: .6; }

        .add-bag { position: absolute; left: 0; right: 0; bottom: 0; padding: 11px; border: none; background: #38aab4; color: #fff; font-size: 11px; font-weight: 600; letter-spacing: 1.6px; text-transform: uppercase; transform: translateY(100%); transition: transform .35s, background .3s; cursor: pointer; z-index: 3; }
        .wish-card:hover .add-bag { transform: none; }
        .add-bag:hover { background: var(--teal-2); }
        .add-bag:disabled { background: rgba(22, 35, 60, .5); cursor: not-allowed; }
        .wish-card.touch .add-bag { transform: none; }

        .wish-info { padding: 15px 17px 18px; }
        .wish-info .cat { font-size: 10.5px; letter-spacing: 1px; text-transform: uppercase; color: rgba(22, 35, 60, .4); }
        .wish-info h4 { color: var(--ink); font-size: 13.5px; font-weight: 500; margin: 4px 0 5px; }
        .wish-info h4 a { color: inherit; }
        .wish-info h4 a:hover { color: var(--teal-2); }
        .wish-info .row { display: flex; align-items: center; justify-content: space-between; }
        .wish-info .price { color: var(--teal-2); font-size: 14px; font-weight: 600; }
        .wish-info .price del { color: rgba(22, 35, 60, .35); font-weight: 400; font-size: 11.5px; margin-left: 5px; }
        .wish-info .added { font-size: 10.5px; color: rgba(22, 35, 60, .4); }

        .wish-float {
            position: fixed; right: 26px; bottom: 26px; z-index: 900;
            width: 58px; height: 58px; border-radius: 50%;
            background: linear-gradient(135deg, var(--teal), var(--teal-2));
            color: #fff; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 12px 30px rgba(56, 170, 180, .5);
            cursor: pointer; border: none; transition: transform .3s;
        }
        .wish-float:hover { transform: translateY(-4px); }
        .wish-float .cart-badge {
            position: absolute; top: -4px; left: -4px;
            width: 18px; height: 18px; border-radius: 50%;
            background: #2bc155; color: #fff;
            font: 700 10px 'Poppins', sans-serif;
            display: flex; align-items: center; justify-content: center;
            border: 2px solid #fff; box-shadow: 0 3px 8px rgba(0, 0, 0, .2);
        }

        .empty-state { text-align: center; padding: 80px 20px; display: none; }
        .empty-state .e-ic { width: 84px; height: 84px; margin: 0 auto 20px; border-radius: 26px; display: grid; place-items: center; background: rgba(255, 107, 129, .1); color: #ff6b81; }
        .empty-state h3 { color: var(--ink); font-size: 18px; margin-bottom: 8px; }
        .empty-state p { font-size: 13px; margin-bottom: 24px; }
        .foot-note { text-align: center; margin-top: 34px; font-size: 12px; color: rgba(22, 35, 60, .4); }

        @media (max-width: 1100px) { .wish-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 560px) { .wish-grid { grid-template-columns: 1fr; } .wish-stats { margin-left: 0; } .wish-float { right: 16px; bottom: 16px; } }
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
                    <h1>My Wishlist, {{ $firstName }} 💖</h1>
                    <p>Click any product to add it straight to your bag.</p>
                </div>
            </header>

            <section class="wish-head glass reveal" style="--d:.05s">
                <span class="ghost" aria-hidden="true">favourites</span>
                <div>
                    <h2>Saved with Love</h2>
                    <p>Items you've hearted across the store.</p>
                </div>
                <div class="wish-stats">
                    <div class="ws"><b id="wsCount">{{ $items->count() }}</b><span>Items</span></div>
                    <div class="ws"><b id="wsValue">£{{ number_format($wishValue, 2) }}</b><span>Total Value</span></div>
                    <div class="ws"><b id="wsStock">{{ $inStockN }}</b><span>In Stock</span></div>
                </div>
            </section>

            <div class="wish-tools reveal" style="--d:.1s">
                <div class="w-search">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.5" y2="16.5"/></svg>
                    <input type="text" id="wishSearch" placeholder="Search your favourites...">
                </div>
                <select class="w-sort" id="wishSort">
                    <option value="new">Newest first</option>
                    <option value="low">Price: Low → High</option>
                    <option value="high">Price: High → Low</option>
                    <option value="name">Name A → Z</option>
                </select>
            </div>

            <section class="wish-grid" id="wishGrid">
                @foreach ($items as $i => $w)
                @php $p = $w->product; @endphp
                <div class="wish-card glass reveal" style="--d:{{ .12 + ($i % 4) * 0.07 }}s"
                     data-name="{{ strtolower($p->name) }} {{ strtolower($p->category?->name ?? '') }}"
                     data-price="{{ $p->final_price }}"
                     data-stock="{{ $p->stock > 0 ? 1 : 0 }}"
                     data-added="{{ $i }}">
                    <div class="wish-thumb"
                         @if ($p->stock > 0)
                         onclick="addToBag({{ (int) $p->id }}, 1, event, { open: true })"
                         @else
                         onclick="showToast('This item is sold out', 'error')"
                         @endif>
                        @if ($p->images->first())
                        <img src="{{ asset('storage/' . $p->images->first()->path) }}" alt="{{ $p->name }}">
                        @else
                        <div class="w-emoji">{{ $p->emoji ?: '🧴' }}</div>
                        @endif

                        <span class="stock-badge {{ $p->stock > 0 ? 'in' : 'out' }}">{{ $p->stock > 0 ? 'In Stock' : 'Sold Out' }}</span>

                        <button type="button" class="heart-btn" onclick="removeWish(this, {{ $p->id }}); event.stopPropagation();" aria-label="Remove from wishlist">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21.2l7.8-7.7 1-1.1a5.5 5.5 0 0 0 0-7.8z"/></svg>
                        </button>

                        <button type="button" class="add-bag" {{ $p->stock <= 0 ? 'disabled' : '' }} onclick="addToBag({{ $p->id }}, 1, event, { open: true }); event.stopPropagation();">
                            {{ $p->stock > 0 ? 'Add to Bag' : 'Notify Me' }}
                        </button>
                    </div>
                    <div class="wish-info">
                        <p class="cat">{{ $p->category?->name ?? '—' }}</p>
                        <h4><a href="{{ route('product.show', $p->slug) }}">{{ $p->name }}</a></h4>
                        <div class="row">
                            <span class="price">£{{ number_format($p->final_price, 2) }}@if($p->discount > 0)<del>£{{ number_format($p->price, 2) }}</del>@endif</span>
                            <span class="added">♥ {{ $w->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </section>

            <div class="empty-state glass" id="emptyState">
                <div class="e-ic">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21.2l7.8-7.7 1-1.1a5.5 5.5 0 0 0 0-7.8z"/></svg>
                </div>
                <h3 id="wishEmptyTitle">Your wishlist is empty</h3>
                <p id="wishEmptyMessage">Tap the ♥ on any product to save it here for later.</p>
                <a href="{{ route('shop') }}" class="add-bag" style="position:static;transform:none;display:inline-block;border-radius:11px;padding:13px 28px">Discover Products</a>
            </div>

            <p class="foot-note">Cosmetics Store · Beauty Pronounced 💫</p>
        </main>
    </div>

    <div class="overlay" id="overlay"></div>

    <!-- 🛒 FLOATING BAG — always visible, opens the drawer -->
    <button type="button" class="wish-float floating-cart" aria-label="Open your bag">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" />
        </svg>
        <span class="cart-badge">{{ collect(session('cart', []))->sum('qty') }}</span>
    </button>

    {{-- 🛒 CART DRAWER — provides addToBag(), showToast(), csrfToken() + the slide-in bag --}}
    @include('front-end.include.cart-drawer')

    <script>
        (function () {
            var sidebar = document.getElementById('sidebar');
            var overlay = document.getElementById('overlay');
            var hamburger = document.getElementById('hamburger');
            function closeSide() { if (sidebar) sidebar.classList.remove('open'); if (overlay) overlay.classList.remove('show'); }
            if (hamburger && sidebar && overlay) {
                hamburger.addEventListener('click', function () { sidebar.classList.toggle('open'); overlay.classList.toggle('show'); });
                overlay.addEventListener('click', closeSide);
            }

            var grid  = document.getElementById('wishGrid');
            var empty = document.getElementById('emptyState');

            window.refreshStats = function () {
                var visible = Array.prototype.filter.call(grid.querySelectorAll('.wish-card'), function (c) {
                    return !c.dataset.removed && c.style.display !== 'none';
                });
                var remaining = grid.querySelectorAll('.wish-card:not([data-removed])').length;
                var value = 0, stock = 0;
                visible.forEach(function (c) {
                    value += parseFloat(c.dataset.price) || 0;
                    if (c.dataset.stock === '1') stock++;
                });
                document.getElementById('wsCount').textContent = visible.length;
                document.getElementById('wsValue').textContent = '£' + value.toFixed(2);
                document.getElementById('wsStock').textContent = stock;
                empty.style.display = visible.length ? 'none' : 'block';
                document.getElementById('wishEmptyTitle').textContent = remaining
                    ? 'No matching favourites'
                    : 'Your wishlist is empty';
                document.getElementById('wishEmptyMessage').textContent = remaining
                    ? 'Try another search to find a saved product.'
                    : 'Tap the ♥ on any product to save it here for later.';
            };

            /* touch devices: first tap reveals buttons, doesn't add */
            grid.addEventListener('click', function (e) {
                if (window.matchMedia('(hover: none)').matches) {
                    var card = e.target.closest('.wish-card');
                    if (card && !e.target.closest('button') && !e.target.closest('.wish-thumb')) {
                        card.classList.toggle('touch');
                    }
                }
            });

            document.getElementById('wishSearch').addEventListener('input', function () {
                var q = this.value.toLowerCase().trim();
                grid.querySelectorAll('.wish-card').forEach(function (card) {
                    card.style.display = card.dataset.name.includes(q) ? '' : 'none';
                });
                refreshStats();
            });

            document.getElementById('wishSort').addEventListener('change', function () {
                var cards = Array.prototype.slice.call(grid.querySelectorAll('.wish-card'));
                var mode = this.value;
                cards.sort(function (a, b) {
                    if (mode === 'low')  return a.dataset.price - b.dataset.price;
                    if (mode === 'high') return b.dataset.price - a.dataset.price;
                    if (mode === 'name') return a.dataset.name.localeCompare(b.dataset.name);
                    return b.dataset.added - a.dataset.added;
                });
                cards.forEach(function (c) { grid.appendChild(c); });
            });

            window.removeWish = function (btn, pid) {
                if (btn.disabled) return;
                btn.disabled = true;
                btn.classList.add('busy');
                var fd = new FormData();
                fd.append('product_id', pid);
                fd.append('_token', csrfToken());
                fetch("{{ route('wishlist.toggle') }}", {
                    method: 'POST', body: fd,
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                })
                .then(function (r) {
                    return r.json().catch(function () { return {}; }).then(function (d) {
                        return { ok: r.ok, data: d };
                    });
                })
                .then(function (res) {
                    var d = res.data;
                    if (d.status === 'removed') {
                        var card = btn.closest('.wish-card');
                        card.classList.add('hiding');
                        card.dataset.removed = '1';
                        setTimeout(function () { card.style.display = 'none'; refreshStats(); }, 400);
                        var badge = document.querySelector('[data-wishlist-count]');
                        if (badge && typeof d.count === 'number') {
                            badge.textContent = d.count;
                            badge.title = d.count + ' saved item' + (d.count === 1 ? '' : 's');
                            badge.hidden = d.count === 0;
                        }
                        showToast(d.message, 'success');
                    } else {
                        showToast(d.message || 'Could not remove', 'error');
                    }
                })
                .catch(function () { showToast('Something went wrong. Please try again.', 'error'); })
                .finally(function () {
                    btn.classList.remove('busy');
                    btn.disabled = false;
                });
            };

            refreshStats();
        })();
    </script>
</body>
</html>