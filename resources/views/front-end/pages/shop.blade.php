@include('front-end.include.header')

@include('front-end.include.loader')

@php
    $wishIds = auth()->check() ? auth()->user()->wishlist()->pluck('product_id')->toArray() : [];
    $shopProductIcons = array_column(config('product_icons.choices'), 'class', 'value');
    $shopCategoryIcons = $shopProductIcons;
@endphp
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
<main>
    <!-- ================= SHOP HERO — header floats INSIDE the band ================= -->
    <section class="shop-hero">
        <div class="container shop-hero-inner">
            <div>
                <p class="sh-kicker">A Brush of Perfection</p>
                <h1 class="sh-title">{{ $pageHeader['title'] }}</h1>
            </div>
            <p class="crumbs"><a href="/">Home</a><span>/</span><b>Shop</b></p>
        </div>
    </section>

    <!-- ================= SHOP BODY ================= -->
    <section class="shop-body">
        <div class="container">
            @if($brands->isNotEmpty())
            <section class="shop-brands" aria-label="Shop by brand">
                <div class="shop-brands-heading">
                    <div>
                        <p>DISCOVER YOUR FAVOURITES</p>
                        <h2>Shop by brand</h2>
                    </div>
                    @if($selectedBrand)
                    <a href="{{ route('shop') }}" class="shop-brand-reset">View all brands</a>
                    @endif
                </div>
                <div class="shop-brands-list">
                    @foreach($brands as $brand)
                    <a class="shop-brand-card {{ $selectedBrand?->id === $brand->id ? 'active' : '' }}"
                       href="{{ route('shop', $selectedBrand?->id === $brand->id ? [] : ['brand' => $brand->slug]) }}"
                       @if($selectedBrand?->id === $brand->id) aria-current="true" @endif>
                        <span class="shop-brand-logo">
                            @if($brand->logo_path)
                            <img src="{{ asset('storage/' . $brand->logo_path) }}" alt="" loading="lazy">
                            @else
                            <i class="ri-price-tag-3-line" aria-hidden="true"></i>
                            @endif
                        </span>
                        <span class="shop-brand-copy"><strong>{{ $brand->name }}</strong><small>{{ $brand->products_count }} {{ \Illuminate\Support\Str::plural('product', $brand->products_count) }}</small></span>
                        <i class="ri-arrow-right-line shop-brand-arrow" aria-hidden="true"></i>
                    </a>
                    @endforeach
                </div>
            </section>
            @endif

            <div class="shop-layout">

            <!-- Sidebar -->
            <aside class="shop-side">
                <div class="side-card">
                    <div class="search-box">
                        <input type="text" id="shopSearch" placeholder="Search products…" autocomplete="off">
                        <span class="search-ic">
                            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <circle cx="11" cy="11" r="7" />
                                <path stroke-linecap="round" d="M20 20l-3.5-3.5" />
                            </svg>
                        </span>
                        </div>
                    </div>
                </div>

                <div class="side-card">
                    <h4 class="side-title">Filter by<br>Categories</h4>
                    <ul class="cat-list" id="catList">
                        <li>
                            <button type="button" class="cat-link {{ !request('category') ? 'active' : '' }}" data-cat="" onclick="pickCategory(this)">
                                <span class="cat-name">
                                    <i class="cat-icon ri-apps-2-line" aria-hidden="true"></i>
                                    All Products
                                </span>
                                <span class="cat-count">{{ $totalActive }}</span>
                            </button>
                        </li>
                        @foreach ($categories as $c)
                        @php
                            $categoryOverride = $categoryOverrides[(string) $c->id] ?? [];
                            $categoryLogoPath = $categoryOverride['logo'] ?? '';
                            $categoryLogo = $categoryLogoPath ? asset('storage/' . $categoryLogoPath) : null;
                            $categoryIcon = trim((string) ($categoryOverride['icon'] ?? '')) ?: (string) $c->icon;
                        @endphp
                        <li>
                            <button type="button" class="cat-link {{ request('category') === $c->slug ? 'active' : '' }}" data-cat="{{ $c->slug }}" onclick="pickCategory(this)">
                                <span class="cat-name">
                                    @if($categoryLogo)
                                    <img class="cat-icon-image" src="{{ $categoryLogo }}" alt="" loading="lazy">
                                    @else
                                    @if(\App\Support\HomeCategoryStrip::usesIconFont($categoryIcon))
                                    <i class="cat-icon {{ \App\Support\HomeCategoryStrip::iconClass($categoryIcon) }}" aria-hidden="true"></i>
                                    @else
                                    <span class="cat-icon cat-icon-emoji" aria-hidden="true">{{ $categoryIcon }}</span>
                                    @endif
                                    @endif
                                    {{ $c->name }}
                                </span>
                                <span class="cat-count">{{ $c->products_count }}</span>
                            </button>
                        </li>
                        @endforeach
                    </ul>
                </div>

                <div class="side-card side-promo">
                    <p class="sp-script">glow</p>
                    <h4>Season Sale</h4>
                    <p>Up to 50% off on selected beauty essentials. Grab your favourites before they're gone.</p>
                </div>
            </aside>

            <!-- Content -->
            <div class="shop-content">
                <div class="shop-toolbar">
                    <p class="result-count" id="resultCount">{{ $products->total() ? 'Showing ' . $products->firstItem() . '–' . $products->lastItem() . ' of ' . $products->total() . ' products' : 'No products found' }}</p>
                    <div class="sort-wrap">
                        <label>Sort by</label>
                        <select id="sortSelect" onchange="applyFilters()">
                            <option value="latest">Latest</option>
                            <option value="price_low">Price: Low to High</option>
                            <option value="price_high">Price: High to Low</option>
                            <option value="name">Name: A → Z</option>
                        </select>
                    </div>
                </div>

                <div class="product-grid" id="productGrid">
                    @foreach ($products as $p)
                    @php
                        $img = $p->images->first();
                        $activeCollection = $p->collections->first();
                        $soldUnits = (int) ($p->sold_units ?? 0);
                        $salesCapacity = max(1, $soldUnits + (int) $p->stock);
                        $salesProgress = min(100, (int) round($soldUnits / $salesCapacity * 100));
                    @endphp
                    <article class="product-card">
                        <div class="product-thumb">
                            @if ($img)
                            <img src="{{ asset('storage/' . $img->path) }}" alt="{{ $p->name }}" loading="lazy">
                            @else
                            <div class="thumb-icon" aria-hidden="true"><i class="{{ $shopProductIcons[$p->emoji] ?? config('product_icons.fallback') }}"></i></div>
                            @endif

                            @if ($p->stock <= 0)
                            <span class="p-badge badge-out">Out of Stock</span>
                            @elseif ($p->is_on_sale)
                            <span class="p-badge badge-sale">@if($p->sale_price !== null)-{{ $p->price > 0 ? (int) round(100 - ($p->final_price / $p->price * 100)) : 0 }}%@elseif($p->discount_type === 'fixed')-£{{ number_format($p->discount, 2) }}@else-{{ (int) $p->discount }}%@endif</span>
                            @endif
                            @include('front-end.include.product-labels', ['product' => $p])

                            <a class="thumb-link" href="{{ route('product.show', $p->slug) }}" aria-label="{{ $p->name }}"></a>

                            <div class="hover-actions">
                                @if($p->sku_variants_count > 0)
                                <a class="h-ic" title="Choose options" href="{{ route('product.show', $p->slug) }}" aria-label="Choose options for {{ $p->name }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" />
                                    </svg>
                                </a>
                                @else
                                <button type="button" class="h-ic {{ $p->stock <= 0 ? 'is-off' : '' }}" title="Add to bag"
                                        onclick="addToBag({{ $p->id }}, 1, event)" @disabled($p->stock <= 0)>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" />
                                    </svg>
                                </button>
                                @endif
                                <button type="button" class="h-ic heart {{ in_array($p->id, $wishIds) ? 'liked' : '' }}" title="Wishlist"
                                        onclick="toggleWish(this, {{ $p->id }}, event)">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21.2l7.8-7.7 1-1.1a5.5 5.5 0 0 0 0-7.8z" />
                                    </svg>
                                </button>
                                <button type="button" class="h-ic js-quick-view"
                                        data-quick-view-url="{{ route('shop.quick-view', $p->slug) }}"
                                        aria-label="Quick view {{ $p->name }}" title="Quick view">
                                    <i class="ri-eye-line" aria-hidden="true"></i>
                                </button>
                                <button type="button" class="h-ic js-compare"
                                        data-product-id="{{ $p->id }}"
                                        data-product-name="{{ $p->name }}"
                                        data-product-url="{{ route('product.show', $p->slug) }}"
                                        data-product-price="{{ $p->final_price }}"
                                        data-product-brand="{{ $p->brand?->name ?? '' }}"
                                        data-product-stock="{{ $p->stock }}"
                                        data-product-image="{{ $img ? asset('storage/' . $img->path) : '' }}"
                                        aria-label="Add {{ $p->name }} to compare" aria-pressed="false" title="Compare">
                                    <i class="ri-arrow-left-right-line" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                        <a class="product-card-cta" href="{{ route('product.show', $p->slug) }}">
                            <span>{{ $p->sku_variants_count > 0 ? 'Select options' : 'View details' }}</span>
                            <i class="ri-arrow-right-line" aria-hidden="true"></i>
                        </a>
                        <div class="product-meta">
                            <p class="product-cat">{{ $p->category?->name }}</p>
                            @if($p->brand)
                            <a class="product-brand" href="{{ route('shop', ['brand' => $p->brand->slug]) }}">
                                @if($p->brand->logo_path)<img src="{{ asset('storage/' . $p->brand->logo_path) }}" alt="" loading="lazy">@endif
                                <span>{{ $p->brand->name }}</span>
                            </a>
                            @endif
                        </div>
                        <h3 class="product-name"><a href="{{ route('product.show', $p->slug) }}">{{ $p->name }}</a></h3>
                        <div class="product-rating" aria-label="{{ $p->approved_reviews_count ? number_format($p->approved_reviews_avg_rating, 1) . ' out of 5 based on ' . $p->approved_reviews_count . ' reviews' : 'No reviews yet' }}">
                            <span class="product-rating-stars" aria-hidden="true">
                                @for($star = 1; $star <= 5; $star++)
                                <span class="{{ $star <= round((float) $p->approved_reviews_avg_rating) ? 'is-filled' : '' }}">★</span>
                                @endfor
                            </span>
                            @if($p->approved_reviews_count)
                            <span>{{ number_format($p->approved_reviews_avg_rating, 1) }}</span>
                            <span class="product-rating-count">({{ $p->approved_reviews_count }})</span>
                            @else
                            <span class="product-rating-count">No reviews</span>
                            @endif
                        </div>
                        <p class="product-price">
                            <span>
                            @if ($p->is_on_sale)
                            £{{ number_format($p->final_price, 2) }} <del>£{{ number_format($p->price, 2) }}</del>
                            @else
                            £{{ number_format($p->price, 2) }}
                            @endif
                            </span>
                        </p>
                        @include('front-end.include.product-sale-countdown', ['saleProduct' => $p])
                        @if($activeCollection)
                        <div class="product-collection-progress">
                            <div class="product-countdown" data-countdown-end="{{ $activeCollection->ends_at->toIso8601String() }}">
                                <span><b data-countdown-days>00</b><small>DAYS</small></span>
                                <span><b data-countdown-hours>00</b><small>HRS</small></span>
                                <span><b data-countdown-minutes>00</b><small>MINS</small></span>
                                <span><b data-countdown-seconds>00</b><small>SECS</small></span>
                            </div>
                            <div class="product-sold-track" role="progressbar" aria-label="Collection sales" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $salesProgress }}"><span style="width:{{ $salesProgress }}%"></span></div>
                            <p class="product-sold-copy">Sold: {{ $soldUnits }}/{{ $salesCapacity }}</p>
                        </div>
                        @endif
                    </article>
                    @endforeach
                </div>

                <nav class="shop-pagination" id="shopPagination" aria-label="Shop pages" style="display: {{ $products->lastPage() > 1 ? 'flex' : 'none' }};">
                    <a class="page-link {{ $products->onFirstPage() ? 'disabled' : '' }}" href="{{ $products->previousPageUrl() ?: '#' }}" data-page="{{ max(1, $products->currentPage() - 1) }}" aria-label="Previous page">‹</a>
                    <span class="page-status" id="pageStatus">Page {{ $products->currentPage() }} of {{ $products->lastPage() }}</span>
                    <a class="page-link {{ $products->currentPage() >= $products->lastPage() ? 'disabled' : '' }}" href="{{ $products->nextPageUrl() ?: '#' }}" data-page="{{ min($products->lastPage(), $products->currentPage() + 1) }}" aria-label="Next page">›</a>
                </nav>

                <div class="empty-state" id="emptyState" style="display: {{ count($products) ? 'none' : 'flex' }};">
                    <div class="es-icon" aria-hidden="true"><svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-4-4"/></svg></div>
                    <h3>No products found</h3>
                    <p>Try a different keyword or browse all categories.</p>
                    <button type="button" class="es-reset" onclick="resetFilters()">Reset Filters</button>
                </div>
            </div>
        </div>
        </div>
    </section>
</main>

<dialog class="shop-dialog quick-view-dialog" id="productQuickView" aria-labelledby="quickViewTitle">
    <button type="button" class="shop-dialog-close" data-close-dialog aria-label="Close quick view">×</button>
    <div class="quick-view-content" id="quickViewContent"></div>
</dialog>
<dialog class="shop-dialog compare-dialog" id="productCompare" aria-labelledby="compareTitle">
    <button type="button" class="shop-dialog-close" data-close-dialog aria-label="Close comparison">×</button>
    <div class="compare-dialog-content" id="compareContent"></div>
</dialog>
<div class="compare-bar" id="compareBar" hidden>
    <span id="compareCount">0 products selected</span>
    <button type="button" id="openCompare">Compare</button>
    <button type="button" id="clearCompare" aria-label="Clear compared products">Clear</button>
</div>

<style>
    /* ═══ NO white sticky header — the transparent header floats INSIDE the teal band ═══ */

    /* ---------- hero band (taller top padding to hold the header) ---------- */
    .shop-hero {
        background-image:
            linear-gradient(90deg, rgba(7, 35, 37, .88) 0%, rgba(13, 59, 64, .72) 48%, rgba(15, 73, 67, .42) 100%),
            url('{{ $pageHeader['image'] }}');
        background-position:center, center 45%;
        background-size:cover;
        background-color:#145d55;
        position: relative;
        min-height: 280px;
        overflow: hidden
    }

    .shop-hero::before {
        content: '';
        position: absolute;
        width: 340px;
        height: 340px;
        border-radius: 50%;
        border: 1px solid rgba(255, 255, 255, .09);
        top: -150px;
        right: -70px
    }

    .shop-hero::after {
        content: 'glow';
        position: absolute;
        right: 5%;
        top: 50%;
        transform: translateY(-50%);
        font-family: 'Allura', cursive;
        font-size: 120px;
        color: rgba(255, 255, 255, .06);
        pointer-events: none
    }

    .shop-hero-inner {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 14px;
        padding: 128px 20px 52px;   /* ← top room for the transparent header */
        position: relative;
        z-index: 2
    }

    .sh-kicker { font: 500 13px 'Montserrat', sans-serif; letter-spacing: 2.5px; text-transform: uppercase; color: #7fe3d2 }

    .sh-title {
        font: 300 clamp(30px, 4.6vw, 46px) 'Montserrat', sans-serif;
        text-transform: uppercase;
        color: #fff;
        margin-top: 10px;
        line-height: 1.15
    }

    .sh-title { color:#fff }

    .crumbs { color: rgba(255, 255, 255, .75); font-size: 13px }
    .crumbs a { color: rgba(255, 255, 255, .75) }
    .crumbs a:hover { color: #fff }
    .crumbs span { margin: 0 9px; opacity: .45 }
    .crumbs b { color: #fff; font-weight: 600 }

    /* ---------- layout ---------- */
    .shop-body { padding: 70px 0 95px; background: var(--bg) }
    .shop-layout { display: grid; grid-template-columns: 270px 1fr; gap: 34px; align-items: start }
    .shop-brands { margin: 0 0 34px; }
    .shop-brands-heading { display:flex; align-items:end; justify-content:space-between; gap:16px; margin-bottom:15px; }
    .shop-brands-heading p { margin:0 0 5px; color:var(--teal-dark); font:600 10px 'Montserrat',sans-serif; letter-spacing:2px; }
    .shop-brands-heading h2 { margin:0; color:var(--dark); font:500 22px 'Montserrat',sans-serif; }
    .shop-brand-reset { color:var(--teal-dark); font-size:12px; font-weight:600; text-decoration:none; }
    .shop-brands-list { display:grid; grid-template-columns:repeat(auto-fit,minmax(min(100%,200px),1fr)); gap:12px; }
    .shop-brand-card { display:flex; align-items:center; gap:12px; min-width:0; min-height:78px; padding:12px 14px; border:1px solid #e8eeee; border-radius:12px; background:#fff; color:var(--dark); text-decoration:none; box-shadow:0 6px 22px rgba(0,0,0,.035); transition:border-color .2s,transform .2s,box-shadow .2s; }
    .shop-brand-card:hover,.shop-brand-card.active { border-color:var(--teal); transform:translateY(-2px); box-shadow:0 10px 24px rgba(30,120,113,.1); }
    .shop-brand-logo { display:grid; place-items:center; flex:none; width:48px; height:48px; overflow:hidden; border-radius:10px; background:#f5f8f7; color:var(--teal-dark); font-size:21px; }
    .shop-brand-logo img { width:100%; height:100%; object-fit:contain; padding:5px; }
    .shop-brand-copy { display:grid; gap:4px; min-width:0; }
    .shop-brand-copy strong { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font:600 13px 'Montserrat',sans-serif; }
    .shop-brand-copy small { color:#899197; font-size:11px; }
    .shop-brand-arrow { margin-left:auto; color:#9aa5a4; }

    /* ---------- sidebar ---------- */
    .side-card {
        background: #fff;
        padding: 26px;
        box-shadow: 0 10px 34px rgba(0, 0, 0, .06);
        margin-bottom: 26px;
        position: relative
    }

    .side-card::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 46px;
        height: 3px;
        background: var(--teal)
    }

    .side-title {
        font: 500 16px 'Montserrat', sans-serif;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        line-height: 1.45;
        color: var(--dark);
        margin-bottom: 16px
    }

    .search-box { display: flex; border: 1px solid #e2e6e8; transition: .3s }
    .search-box:focus-within { border-color: var(--teal); box-shadow: 0 0 0 3px rgba(56, 170, 180, .12) }
    .search-box input { flex: 1; min-width: 0; border: 0; outline: 0; padding: 12px 14px; font: inherit; font-size: 14px; background: transparent; color: #333 }
    .search-ic { display: flex; align-items: center; padding: 0 14px; color: var(--teal) }

    .cat-list li+li { border-top: 1px dashed #eef1f2 }

    .cat-link {
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        background: none;
        border: 0;
        cursor: pointer;
        padding: 11px 2px;
        font: inherit;
        font-size: 14px;
        color: #5b6066;
        transition: .25s;
        text-align: left
    }

    .cat-name { display: inline-flex; align-items: center; gap: 9px }
    .cat-icon { flex-shrink: 0; width: 17px; font-size: 16px; text-align: center; color: var(--teal) }
    .cat-icon-image { width:19px;height:19px;flex:none;object-fit:contain;border-radius:4px; }
    .cat-link:hover { color: var(--teal); padding-left: 8px }
    .cat-link.active { color: var(--teal); font-weight: 600 }

    .cat-count {
        font-size: 11.5px;
        background: #f1f4f4;
        color: #8a9298;
        padding: 2px 9px;
        border-radius: 20px;
        transition: .25s;
        flex-shrink: 0
    }

    .cat-link.active .cat-count { background: var(--teal); color: #fff }

    .side-promo {
        background: linear-gradient(150deg, #0d3b40, #155e57);
        color: #fff;
        text-align: center;
        padding: 36px 24px
    }

    .side-promo::after { display: none }

    .sp-script { font-family: 'Allura', cursive; font-size: 46px; color: #7fe3d2; line-height: 1; margin-bottom: 4px }

    .side-promo h4 { font: 500 15px 'Montserrat', sans-serif; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 10px }
    .side-promo p { font-size: 13px; color: rgba(255, 255, 255, .72); line-height: 1.7 }

    /* ---------- toolbar ---------- */
    .shop-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 14px;
        background: #fff;
        padding: 14px 20px;
        box-shadow: 0 10px 34px rgba(0, 0, 0, .06);
        margin-bottom: 30px
    }

    .result-count { font-size: 14px; color: #7a7f84 }

    .sort-wrap { display: flex; flex:none;align-items: center; gap: 10px; font-size: 13px; color: #7a7f84;white-space:nowrap }
    .sort-wrap label{white-space:nowrap}

    .sort-wrap .site-select-wrap { flex: 0 0 176px; width: 176px }
    .sort-wrap .site-select-trigger { min-height: 42px; height: 42px; padding: 9px 12px }
    .sort-wrap .site-select-chevron {
        width: 8px;
        height: 8px;
        border-right: 1.5px solid currentColor;
        border-bottom: 1.5px solid currentColor;
        font-size: 0;
        line-height: 0;
        transform: rotate(45deg)
    }
    .sort-wrap .site-select-trigger[aria-expanded="true"] .site-select-chevron { transform: rotate(225deg) }
    .sort-wrap .site-select-option { white-space: nowrap }
    .shop-toolbar{flex-wrap:nowrap}

    .sort-wrap .site-select-trigger:focus-visible { border-color: var(--teal) }

    /* ---------- grid + cards (BEAUTIFIED) ---------- */
    .product-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:20px;position:relative;align-items:start;transition:opacity .25s}
    .product-grid.is-loading{opacity:.4;pointer-events:none}
    .product-grid.is-loading::after{content:'';position:absolute;inset:0;margin:auto;width:38px;height:38px;border-radius:50%;border:3px solid rgba(56,170,180,.25);border-top-color:var(--teal);animation:spin .7s linear infinite}
    @keyframes spin{to{transform:rotate(360deg)}}
    @media(max-width:520px){.shop-toolbar{align-items:center;gap:8px;padding:12px;flex-wrap:wrap}.result-count{min-width:0;font-size:11px}.sort-wrap{gap:6px;font-size:11px;margin-left:auto}.sort-wrap .site-select-wrap{flex-basis:150px;width:150px}.sort-wrap .site-select-trigger{min-height:38px;height:38px;padding:8px 10px;font-size:11px}}

    .product-card{position:relative;align-self:start;background:#fff;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,.07);transition:transform .35s,box-shadow .35s;animation:cardIn .55s cubic-bezier(.22,1,.36,1) both}
    .product-card:hover{transform:translateY(-7px);box-shadow:0 30px 60px rgba(0,0,0,.13)}
    .product-card::after{content:'';position:absolute;left:0;right:0;bottom:0;height:3px;background:linear-gradient(90deg,var(--teal),#5fd0c0);transform:scaleX(0);transform-origin:left;transition:transform .4s ease;z-index:3}
    .product-card:hover::after{transform:scaleX(1)}

    .product-thumb{position:relative;overflow:hidden;aspect-ratio:3/3.15;background:#f4f6f6}
    .product-thumb img{width:100%;height:100%;object-fit:cover;transition:transform .7s ease}
    .product-card:hover .product-thumb img{transform:scale(1.08)}
    .thumb-icon{width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#7ab9b2;background:linear-gradient(160deg,#f2f7f6,#e6f0ef)}
    .thumb-icon i{font-size:84px;line-height:1;color:#5eaaa2}
    .thumb-link{position:absolute;inset:0;z-index:1}
    .p-badge{position:absolute;top:12px;left:12px;padding:5px 11px;font:600 10.5px 'Montserrat',sans-serif;letter-spacing:1px;text-transform:uppercase;z-index:2}
    .badge-sale{background:var(--teal);color:#fff}
    .badge-out{background:#1b1d21;color:#fff}
    .label-badge{left:12px;right:auto}
    .badge-hot{background:#c83a55;color:#fff}
    .badge-new{background:#295d8f;color:#fff}
    .hover-actions{position:absolute;top:12px;right:12px;display:flex;flex-direction:column;gap:9px;z-index:2}
    .h-ic{width:40px;height:40px;border-radius:50%;background:#fff;border:0;display:flex;align-items:center;justify-content:center;color:#1b1d21;cursor:pointer;box-shadow:0 8px 20px rgba(0,0,0,.16);opacity:0;transform:translateX(12px);transition:all .35s}
    .product-card:hover .h-ic{opacity:1;transform:none}
    .h-ic:focus-visible{opacity:1;transform:none;outline:2px solid var(--teal);outline-offset:2px}
    .h-ic[aria-pressed="true"]{background:var(--teal);color:#fff}
    .h-ic:hover{background:var(--teal);color:#fff}
    .h-ic.is-off{opacity:.5!important;cursor:not-allowed}
    .h-ic.is-off:hover{background:#fff;color:#1b1d21}
    .h-ic.heart{color:#ff6b81}
    .h-ic.heart:hover{background:#ff6b81;color:#fff}
    .h-ic.heart.liked{background:#ff6b81;color:#fff}

    .product-meta{display:flex;align-items:center;justify-content:space-between;gap:8px;min-width:0;padding:18px 20px 0}
    .product-cat{display:block;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin:0;font:600 10.5px 'Montserrat',sans-serif;letter-spacing:1.8px;text-transform:uppercase;color:#b3b9bd}
    .product-brand{display:inline-flex;align-items:center;justify-content:flex-end;gap:5px;min-width:0;max-width:58%;margin:0 0 0 auto;color:#56746f;font:600 9px 'Montserrat',sans-serif;letter-spacing:.6px;text-decoration:none;text-transform:uppercase;white-space:nowrap}
    .product-brand:hover{color:var(--teal-dark)}
    .product-brand img{width:18px;height:18px;flex:none;object-fit:contain;border-radius:4px}
    .product-brand span{overflow:hidden;text-overflow:ellipsis}
    .product-name{padding:6px 20px 0;margin:0;font:600 15px 'Montserrat',sans-serif;letter-spacing:.6px;text-transform:uppercase;color:var(--dark)}
    .product-name a:hover{color:var(--teal)}
    .product-card-cta{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:11px 20px;background:var(--teal);color:#fff;font:600 10px 'Montserrat',sans-serif;letter-spacing:1.2px;text-transform:uppercase;text-decoration:none;transition:background .2s}
    .product-card-cta:hover{background:var(--teal-dark);color:#fff}
    .product-rating{display:flex;align-items:center;gap:5px;padding:7px 20px 0;color:#58636a;font:500 10px 'Montserrat',sans-serif}
    .product-rating-stars{display:inline-flex;gap:1px;color:#d9dfe0;font-size:13px;line-height:1}
    .product-rating-stars .is-filled{color:#e6a92c}
    .product-rating-count{color:#92999d}
    .product-price{display:flex;align-items:baseline;justify-content:space-between;padding:8px 20px 20px;margin:0;font:700 16px 'Montserrat',sans-serif;color:var(--teal-dark)}
    .product-price del{color:#c3c9cc;font-weight:500;font-size:12.5px;margin-left:6px}
    .product-collection-progress{padding:0 20px 17px}
    .product-countdown{display:flex;gap:6px;margin:0 0 11px}
    .product-countdown>span{display:grid;place-items:center;min-width:0;flex:1;padding:6px 3px;border:1px solid #e5e9ed;border-radius:7px;background:#fff;color:#1b1d21}
    .product-countdown b{font:700 16px/1.2 'Montserrat',sans-serif}
    .product-countdown small{margin-top:2px;color:#899197;font:600 8px/1.2 'Montserrat',sans-serif}
    .product-sold-track{height:7px;overflow:hidden;border-radius:8px;background:#e9edf0}
    .product-sold-track span{display:block;height:100%;border-radius:inherit;background:#1762aa;transition:width .25s}
    .product-sold-copy{margin:7px 0 0;color:#788087;font:500 10px 'Montserrat',sans-serif}

    /* ---------- empty state ---------- */
    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        background: #fff;
        padding: 70px 30px;
        box-shadow: 0 10px 34px rgba(0, 0, 0, .06)
    }

    .es-icon { display:flex; align-items:center; justify-content:center; color:var(--teal); margin-bottom:14px }

    .shop-pagination { display:flex; align-items:center; justify-content:center; gap:18px; margin-top:32px }
    .page-link { width:40px; height:40px; display:flex; align-items:center; justify-content:center; border:1px solid #e2e6e8; color:var(--dark); font-size:24px; line-height:1; transition:.25s }
    .page-link:hover:not(.disabled) { border-color:var(--teal); background:var(--teal); color:#fff }
    .page-link.disabled { opacity:.4; pointer-events:none }
    .page-status { color:#7a7f84; font-size:13px }

    .empty-state h3 {
        font: 500 18px 'Montserrat', sans-serif;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: var(--dark);
        margin-bottom: 6px
    }

    .empty-state p { font-size: 14px; color: #9aa0a4; margin-bottom: 22px }

    .shop-dialog{position:fixed;inset:0;margin:auto;padding:30px;width:min(760px,calc(100% - 28px));max-width:none;max-height:min(86vh,760px);overflow:auto;border:0;border-radius:16px;background:#fff;color:var(--dark);box-shadow:0 28px 90px rgba(0,0,0,.25)}
    .shop-dialog::backdrop{background:rgba(12,25,27,.68);backdrop-filter:blur(3px)}
    .shop-dialog-close{position:sticky;top:0;float:right;z-index:2;display:grid;place-items:center;width:38px;height:38px;margin:-14px -14px 4px 8px;border:0;border-radius:50%;background:#f0f4f3;color:var(--dark);font-size:25px;cursor:pointer}
    .quick-view-layout{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:28px;align-items:center;clear:both}
    .quick-view-image{width:100%;aspect-ratio:1/1.05;object-fit:cover;border-radius:12px;background:#f3f6f5}
    .quick-view-copy h2,.compare-dialog-content h2{margin:0 0 8px;font:600 22px 'Montserrat',sans-serif;line-height:1.35}
    .quick-view-category{margin:0 0 9px;color:#879195;font:600 10px 'Montserrat',sans-serif;letter-spacing:1.5px;text-transform:uppercase}
    .quick-view-brand{margin:0 0 12px;color:var(--teal-dark);font:600 12px 'Montserrat',sans-serif}
    .quick-view-rating{margin:0 0 14px;color:#9a751e;font-size:13px}
    .quick-view-description{color:#677177;font-size:14px;line-height:1.75}
    .quick-view-price{margin:16px 0;color:var(--teal-dark);font:700 22px 'Montserrat',sans-serif}
    .quick-view-options{display:grid;gap:7px;margin:14px 0;padding:12px;background:#f6f8f7;border-radius:9px;font-size:12px}
    .quick-view-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:18px}
    .quick-view-actions a,.quick-view-actions button{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:0 16px;border:1px solid var(--teal);border-radius:5px;background:var(--teal);color:#fff;font:600 11px 'Montserrat',sans-serif;text-decoration:none;text-transform:uppercase;cursor:pointer}
    .quick-view-actions a.secondary{background:#fff;color:var(--teal-dark)}
    .compare-dialog{width:min(900px,calc(100% - 28px))}
    .compare-dialog-content{clear:both}
    .compare-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(165px,1fr));gap:12px;margin-top:20px}
    .compare-item{min-width:0;padding:12px;border:1px solid #e7eceb;border-radius:10px}
    .compare-item img{width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:7px;background:#f3f6f5}
    .compare-item h3{margin:10px 0 7px;font:600 12px 'Montserrat',sans-serif;line-height:1.5}
    .compare-item p{margin:4px 0;color:#738087;font-size:12px}
    .compare-item a{color:var(--teal-dark);font-weight:600}
    .compare-remove{margin-top:10px;border:0;background:none;color:#a44;font:600 11px 'Montserrat',sans-serif;cursor:pointer}
    .compare-bar{position:fixed;z-index:80;right:18px;bottom:86px;display:flex;align-items:center;gap:12px;padding:12px 15px;border-radius:12px;background:#123f3f;color:#fff;box-shadow:0 12px 36px rgba(0,0,0,.24);font-size:12px}
    .compare-bar[hidden]{display:none}
    .compare-bar button{padding:8px 11px;border:0;border-radius:6px;background:#fff;color:#123f3f;font:600 10px 'Montserrat',sans-serif;cursor:pointer}
    .compare-bar button:last-child{background:transparent;color:#dceeed}
    @media(hover:none),(pointer:coarse){.h-ic{width:34px;height:34px;opacity:1;transform:none}.hover-actions{gap:6px}.h-ic svg{width:14px;height:14px}}
    @media(max-width:767px){.quick-view-layout{grid-template-columns:1fr;gap:16px}.quick-view-image{max-height:280px;aspect-ratio:1.2/1}.shop-dialog{padding:20px}.quick-view-copy h2{font-size:19px}.compare-bar{right:8px;bottom:76px;left:8px;justify-content:space-between;gap:6px;padding:9px;font-size:10px}}

    .es-reset {
        background: var(--teal);
        color: #fff;
        border: 0;
        padding: 13px 32px;
        font: 600 11.5px 'Montserrat', sans-serif;
        letter-spacing: 2px;
        text-transform: uppercase;
        cursor: pointer;
        transition: .3s
    }

    .es-reset:hover { background: var(--teal-dark); transform: translateY(-2px) }

    /* ---------- responsive ---------- */
    @media (max-width: 991px) {
        .shop-layout { grid-template-columns: 1fr }
        .product-grid { grid-template-columns: repeat(2, minmax(0, 1fr));gap:18px }
        .shop-hero-inner { padding: 100px 20px 40px }
        .shop-hero { min-height: 245px; background-position: center, 58% center, center }
    }

    @media (max-width: 575px) {
        .product-grid { grid-template-columns: repeat(2,minmax(0,1fr));gap:12px }
        .product-thumb{aspect-ratio:1/1.05}
        .product-meta{gap:5px;padding:12px 12px 0}
        .product-cat{font-size:8px;letter-spacing:.7px}
        .product-brand{max-width:60%;gap:3px;font-size:8px;letter-spacing:.2px}
        .product-brand img{width:18px;height:18px}
        .product-name{padding:5px 12px 0;font-size:12px;letter-spacing:.2px}
        .product-card-cta{padding:9px 12px;font-size:8px;letter-spacing:.6px}
        .product-rating{padding:6px 12px 0;font-size:8px;gap:3px}
        .product-rating-stars{font-size:11px}
        .product-price{padding:8px 12px 12px;font-size:14px}
        .product-price del{font-size:10px}
        .product-collection-progress{padding:0 12px 12px}
        .product-countdown{gap:3px}
        .product-countdown>span{padding:5px 2px}
        .product-countdown b{font-size:12px}
        .product-countdown small{font-size:7px}
        .shop-hero::after { display: none }
    }
</style>

@include('front-end.include.footer')
@include('front-end.include.js')

<script>
    /* ---------- state ---------- */
    var currentCat = @js(request('category', ''));
    var currentBrand = @js(request('brand', ''));
    var currentSort = @js(request('sort', 'latest'));
    var currentPage = @js($products->currentPage());
    var SHOP_URL = "{{ route('shop') }}";
    var QUICK_VIEW_URL = @js(route('shop.quick-view', ['slug' => '__SLUG__']));
    var WISH_IDS = @json($wishIds);
    var SHOP_PRODUCT_ICONS = @json($shopProductIcons);
    var SHOP_PRODUCT_ICON_FALLBACK = @json(config('product_icons.fallback'));
    var comparedProducts = [];
    var compareLimit = 4;

    var SVG_BAG = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>';

    var SVG_HEART = '<svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21.2l7.8-7.7 1-1.1a5.5 5.5 0 0 0 0-7.8z"/></svg>';
    function productFallback(icon) {
        var iconClass = Object.prototype.hasOwnProperty.call(SHOP_PRODUCT_ICONS, icon) ?
            SHOP_PRODUCT_ICONS[icon] : SHOP_PRODUCT_ICON_FALLBACK;
        return '<div class="thumb-icon" aria-hidden="true"><i class="' + iconClass + '"></i></div>';
    }

    document.getElementById('sortSelect').value = currentSort;
    document.getElementById('shopSearch').value = @js(request('search', ''));

    /* ---------- category pick ---------- */
    function pickCategory(el) {
        document.querySelectorAll('.cat-link').forEach(function(b) {
            b.classList.remove('active');
        });
        el.classList.add('active');
        currentCat = el.dataset.cat;
        applyFilters(1);
    }

    /* ---------- live search ---------- */
    var searchTimer = null;
    document.getElementById('shopSearch').addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() { applyFilters(1); }, 280);
    });

    /* ---------- fetch + re-render ---------- */
    var filterRequest = 0;
    function applyFilters(page, pushHistory) {
        var q = document.getElementById('shopSearch').value.trim();
        var sort = document.getElementById('sortSelect').value;
        currentSort = sort;
        page = Math.max(1, parseInt(page || 1, 10));
        var params = new URLSearchParams();
        if (q) params.set('search', q);
        if (currentCat) params.set('category', currentCat);
        if (currentBrand) params.set('brand', currentBrand);
        if (sort && sort !== 'latest') params.set('sort', sort);
        if (page > 1) params.set('page', page);

        var grid = document.getElementById('productGrid');
        grid.classList.add('is-loading');
        var requestId = ++filterRequest;
        var targetUrl = SHOP_URL + (params.toString() ? '?' + params.toString() : '');

        fetch(targetUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(function(r) {
                return r.json().then(function(data) {
                    if (!r.ok) throw new Error(data.message || 'Could not load products');
                    return data;
                });
            })
            .then(function(res) {
                if (requestId !== filterRequest) return;
                grid.classList.remove('is-loading');
                var list = res.products || [];
                var html = '';
                list.forEach(function(p, i) { html += productCard(p, i); });
                grid.innerHTML = html;

                currentPage = res.current_page || page;
                var firstItem = list.length ? (currentPage - 1) * res.per_page + 1 : 0;
                var lastItem = list.length ? firstItem + list.length - 1 : 0;
                document.getElementById('resultCount').textContent = list.length ?
                    'Showing ' + firstItem + '–' + lastItem + ' of ' + res.count + ' products' : 'No products found';
                document.getElementById('emptyState').style.display = list.length ? 'none' : 'flex';
                renderPagination(res.current_page || 1, res.last_page || 1, params);
                var newUrl = location.pathname + (params.toString() ? '?' + params.toString() : '');
                if (pushHistory) history.pushState({ shop: true }, '', newUrl);
                else history.replaceState({ shop: true }, '', newUrl);
            })
            .catch(function() {
                if (requestId !== filterRequest) return;
                grid.classList.remove('is-loading');
                showToast('Something went wrong. Please try again.', 'error');
            });
    }

    function renderPagination(page, lastPage, params) {
        var nav = document.getElementById('shopPagination');
        var prev = Math.max(1, page - 1), next = Math.min(lastPage, page + 1);
        nav.style.display = lastPage > 1 ? 'flex' : 'none';
        nav.innerHTML = '<a class="page-link ' + (page <= 1 ? 'disabled' : '') + '" href="' + pageUrl(prev, params) + '" data-page="' + prev + '" aria-label="Previous page">‹</a>' +
            '<span class="page-status" id="pageStatus">Page ' + page + ' of ' + lastPage + '</span>' +
            '<a class="page-link ' + (page >= lastPage ? 'disabled' : '') + '" href="' + pageUrl(next, params) + '" data-page="' + next + '" aria-label="Next page">›</a>';
    }

    function pageUrl(page, params) {
        var pageParams = new URLSearchParams(params.toString());
        if (page > 1) pageParams.set('page', page);
        else pageParams.delete('page');
        return SHOP_URL + (pageParams.toString() ? '?' + pageParams.toString() : '');
    }

    function ratingMarkup(rating, count) {
        var average = Number(rating) || 0;
        var reviewCount = Number(count) || 0;
        var stars = '';
        for (var star = 1; star <= 5; star++) {
            stars += '<span class="' + (star <= Math.round(average) ? 'is-filled' : '') + '">★</span>';
        }
        return '<div class="product-rating" aria-label="' +
            (reviewCount ? average.toFixed(1) + ' out of 5 based on ' + reviewCount + ' reviews' : 'No reviews yet') +
            '"><span class="product-rating-stars" aria-hidden="true">' + stars + '</span>' +
            (reviewCount ? '<span>' + average.toFixed(1) + '</span><span class="product-rating-count">(' + reviewCount + ')</span>' :
                '<span class="product-rating-count">No reviews</span>') + '</div>';
    }

    function compareButton(p) {
        var selected = comparedProducts.some(function(item) { return item.id === Number(p.id); });
        return '<button type="button" class="h-ic js-compare" data-product-id="' + Number(p.id) +
            '" data-product-name="' + esc(p.name) + '" data-product-url="' + esc(p.url) +
            '" data-product-price="' + Number(p.final_price) + '" data-product-brand="' +
            esc(p.brand ? p.brand.name : '') + '" data-product-stock="' + Number(p.stock) +
            '" data-product-image="' + esc(p.img || '') + '" aria-label="Add ' + esc(p.name) +
            ' to compare" aria-pressed="' + selected + '" title="Compare">' +
            '<i class="ri-arrow-left-right-line" aria-hidden="true"></i></button>';
    }

    function quickViewButton(p) {
        return '<button type="button" class="h-ic js-quick-view" data-quick-view-url="' +
            esc(QUICK_VIEW_URL.replace('__SLUG__', encodeURIComponent(p.slug))) +
            '" aria-label="Quick view ' + esc(p.name) + '" title="Quick view">' +
            '<i class="ri-eye-line" aria-hidden="true"></i></button>';
    }

    document.getElementById('shopPagination').addEventListener('click', function(e) {
        var link = e.target.closest('[data-page]');
        if (!link || link.classList.contains('disabled')) return;
        e.preventDefault();
        applyFilters(link.dataset.page, true);
    });

    window.addEventListener('popstate', function() {
        if (location.pathname !== new URL(SHOP_URL, location.origin).pathname) return;
        var params = new URLSearchParams(location.search);
        currentCat = params.get('category') || '';
        currentBrand = params.get('brand') || '';
        currentSort = params.get('sort') || 'latest';
        document.getElementById('shopSearch').value = params.get('search') || '';
        document.getElementById('sortSelect').value = currentSort;
        document.querySelectorAll('.cat-link').forEach(function(b) {
            b.classList.toggle('active', b.dataset.cat === currentCat);
        });
        applyFilters(params.get('page') || 1, false);
    });

    function productCard(p, i) {
        var thumb = p.img ?
            '<img src="' + p.img + '" alt="' + esc(p.name) + '" loading="lazy">' :
            productFallback(p.icon);

        var badge = '';
        if (p.stock <= 0) badge = '<span class="p-badge badge-out">Out of Stock</span>';
        else if (p.is_on_sale) badge = '<span class="p-badge badge-sale">' + (p.sale_price !== null ? '-' + (p.price > 0 ? Math.round(100 - p.final_price / p.price * 100) : 0) + '%' : (p.discount_type === 'fixed' ? '-£' + Number(p.discount).toFixed(2) : '-' + p.discount + '%')) + '</span>';
        (p.labels || []).slice(0, 3).forEach(function (label, index) {
            if (label.type === 'sale' && p.is_on_sale) return;
            var top = (p.stock <= 0 || p.is_on_sale ? 43 : 12) + (index * 31);
            badge += '<span class="p-badge label-badge badge-' + esc(label.type) + '" style="top:' + top + 'px">' + esc(label.name) + '</span>';
        });

        var addBtn = p.has_variants ?
            '<a class="h-ic" title="Choose options" href="' + esc(p.url) + '">' + SVG_BAG + '</a>' :
            (p.stock > 0 ?
                '<button type="button" class="h-ic" title="Add to bag" onclick="addToBag(' + p.id + ', 1, event)">' + SVG_BAG + '</button>' :
                '<button type="button" class="h-ic is-off" title="Out of stock" disabled>' + SVG_BAG + '</button>');

        var liked = WISH_IDS.indexOf(p.id) !== -1;
        var heart = '<button type="button" class="h-ic heart' + (liked ? ' liked' : '') + '" title="Wishlist" onclick="toggleWish(this, ' + p.id + ', event)">' + SVG_HEART + '</button>';
        var quickView = quickViewButton(p);
        var compare = compareButton(p);
        var cardCta = '<a class="product-card-cta" href="' + esc(p.url) + '"><span>' +
            (p.has_variants ? 'Select options' : 'View details') +
            '</span><i class="ri-arrow-right-line" aria-hidden="true"></i></a>';

        var price = p.is_on_sale ?
            '£' + p.final_price.toFixed(2) + ' <del>£' + p.price.toFixed(2) + '</del>' :
            '£' + p.price.toFixed(2);
        var saleTimer = p.is_on_sale && p.sale_ends_at ?
            '<div class="product-sale-timer" data-sale-countdown-end="' + esc(p.sale_ends_at) + '" aria-label="Time remaining in this sale">' +
            '<span class="product-sale-timer-label">Sale ends in</span>' +
            '<span><b data-sale-days>00</b><small>DAYS</small></span>' +
            '<span><b data-sale-hours>00</b><small>HRS</small></span>' +
            '<span><b data-sale-minutes>00</b><small>MINS</small></span>' +
            '<span><b data-sale-seconds>00</b><small>SECS</small></span></div>' : '';

        var collectionProgress = '';
        if (p.collection_ends_at) {
            var sold = Math.max(0, parseInt(p.sold_units, 10) || 0);
            var capacity = Math.max(1, sold + (parseInt(p.stock, 10) || 0));
            var progress = Math.min(100, Math.round(sold / capacity * 100));
            collectionProgress = '<div class="product-collection-progress">' +
                '<div class="product-countdown" data-countdown-end="' + esc(p.collection_ends_at) + '">' +
                '<span><b data-countdown-days>00</b><small>DAYS</small></span>' +
                '<span><b data-countdown-hours>00</b><small>HRS</small></span>' +
                '<span><b data-countdown-minutes>00</b><small>MINS</small></span>' +
                '<span><b data-countdown-seconds>00</b><small>SECS</small></span></div>' +
                '<div class="product-sold-track" role="progressbar" aria-label="Collection sales" aria-valuemin="0" aria-valuemax="100" aria-valuenow="' + progress + '"><span style="width:' + progress + '%"></span></div>' +
                '<p class="product-sold-copy">Sold: ' + sold + '/' + capacity + '</p></div>';
        }

        return '<article class="product-card" style="animation-delay:' + ((i % 4) * 0.07) + 's">' +
            '<div class="product-thumb">' + thumb + badge +
            '<a class="thumb-link" href="' + esc(p.url) + '" aria-label="' + esc(p.name) + '"></a>' +
            '<div class="hover-actions">' + addBtn + heart + quickView + compare + '</div></div>' +
            cardCta +
            '<div class="product-meta"><p class="product-cat">' + esc(p.category || '') + '</p>' +
            (p.brand ? '<a class="product-brand" href="' + SHOP_URL + '?brand=' + encodeURIComponent(p.brand.slug) + '">' + (p.brand.logo ? '<img src="' + esc(p.brand.logo) + '" alt="" loading="lazy">' : '') + '<span>' + esc(p.brand.name) + '</span></a>' : '') + '</div>' +
            '<h3 class="product-name"><a href="' + esc(p.url) + '">' + esc(p.name) + '</a></h3>' +
            ratingMarkup(p.average_rating, p.review_count) +
            '<p class="product-price"><span>' + price + '</span></p>' + saleTimer + collectionProgress +
            '</article>';
    }

    function refreshCompareState() {
        document.getElementById('compareBar').hidden = comparedProducts.length === 0;
        document.getElementById('compareCount').textContent =
            comparedProducts.length + ' of ' + compareLimit + ' products selected';
        document.querySelectorAll('.js-compare').forEach(function(button) {
            var selected = comparedProducts.some(function(item) {
                return item.id === Number(button.dataset.productId);
            });
            button.setAttribute('aria-pressed', selected ? 'true' : 'false');
        });
    }

    function toggleComparedProduct(button) {
        var id = Number(button.dataset.productId);
        var existing = comparedProducts.findIndex(function(item) { return item.id === id; });
        if (existing !== -1) {
            comparedProducts.splice(existing, 1);
        } else {
            if (comparedProducts.length >= compareLimit) {
                showToast('Compare up to ' + compareLimit + ' products at a time.', 'error');
                return;
            }
            comparedProducts.push({
                id: id,
                name: button.dataset.productName,
                url: button.dataset.productUrl,
                price: Number(button.dataset.productPrice),
                brand: button.dataset.productBrand,
                stock: Number(button.dataset.productStock),
                image: button.dataset.productImage
            });
        }
        refreshCompareState();
    }

    function renderCompareDialog() {
        var dialog = document.getElementById('productCompare');
        if (!comparedProducts.length) {
            dialog.close();
            return;
        }
        var cards = comparedProducts.map(function(item) {
            return '<article class="compare-item">' +
                (item.image ? '<img src="' + esc(item.image) + '" alt="' + esc(item.name) + '">' :
                    productFallback('')) +
                '<h3><a href="' + esc(item.url) + '">' + esc(item.name) + '</a></h3>' +
                '<p>Brand: ' + esc(item.brand || '—') + '</p>' +
                '<p>Price: £' + item.price.toFixed(2) + '</p>' +
                '<p>Stock: ' + (item.stock > 0 ? 'In stock (' + item.stock + ')' : 'Out of stock') + '</p>' +
                '<button type="button" class="compare-remove" data-remove-compare="' + item.id + '">Remove</button>' +
                '</article>';
        }).join('');
        document.getElementById('compareContent').innerHTML =
            '<h2 id="compareTitle">Compare products</h2><p>Compare the selected products by brand, current price and availability.</p>' +
            '<div class="compare-grid">' + cards + '</div>';
        dialog.showModal();
    }

    function showQuickView(url) {
        var dialog = document.getElementById('productQuickView');
        var content = document.getElementById('quickViewContent');
        content.innerHTML = '<p role="status">Loading product details…</p>';
        dialog.showModal();
        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(response) {
                return response.json().then(function(data) {
                    if (!response.ok) throw new Error(data.message || 'Could not load product details.');
                    return data;
                });
            })
            .then(function(product) {
                var image = product.image ?
                    '<img class="quick-view-image" src="' + esc(product.image) + '" alt="' + esc(product.name) + '">' :
                    productFallback(product.icon);
                var options = product.options.length ?
                    '<div class="quick-view-options"><strong>Available options</strong>' +
                    product.options.map(function(option) {
                        return '<span>' + esc(option.name) + ' — ' +
                            (option.stock > 0 ? option.stock + ' in stock' : 'out of stock') + '</span>';
                    }).join('') + '</div>' : '';
                var quickAdd = !product.has_variants && product.stock > 0 ?
                    '<button type="button" onclick="addToBag(' + Number(product.id) + ', 1, event)">Add to bag</button>' :
                    (product.has_variants ? '<a href="' + esc(product.url) + '">Select options</a>' :
                        '<button type="button" disabled>Out of stock</button>');
                content.innerHTML = '<div class="quick-view-layout">' + image +
                    '<div class="quick-view-copy"><p class="quick-view-category">' + esc(product.category || '') + '</p>' +
                    '<h2 id="quickViewTitle">' + esc(product.name) + '</h2>' +
                    (product.brand ? '<p class="quick-view-brand">' + esc(product.brand) + '</p>' : '') +
                    '<p class="quick-view-rating">' +
                    (product.review_count ? '★ ' + Number(product.average_rating).toFixed(1) + ' · ' + product.review_count + ' reviews' : 'No reviews yet') +
                    '</p><p class="quick-view-description">' + esc(product.description || 'No product description is available.') + '</p>' +
                    options + '<p class="quick-view-price">£' + Number(product.final_price).toFixed(2) +
                    (product.is_on_sale ? ' <del>£' + Number(product.price).toFixed(2) + '</del>' : '') + '</p>' +
                    '<p>' + (product.stock > 0 ? 'In stock — ' + product.stock + ' available' : 'Out of stock') + '</p>' +
                    '<div class="quick-view-actions">' + quickAdd +
                    '<a class="secondary" href="' + esc(product.url) + '">Full details</a></div></div></div>';
            })
            .catch(function(error) {
                content.innerHTML = '<p role="alert">' + esc(error.message) + '</p>';
            });
    }

    document.getElementById('productGrid').addEventListener('click', function(event) {
        var quickView = event.target.closest('.js-quick-view');
        var compare = event.target.closest('.js-compare');
        if (quickView) {
            event.preventDefault();
            showQuickView(quickView.dataset.quickViewUrl);
        } else if (compare) {
            event.preventDefault();
            event.stopPropagation();
            toggleComparedProduct(compare);
        }
    });

    document.querySelectorAll('[data-close-dialog]').forEach(function(button) {
        button.addEventListener('click', function() { button.closest('dialog').close(); });
    });
    [document.getElementById('productQuickView'), document.getElementById('productCompare')].forEach(function(dialog) {
        dialog.addEventListener('click', function(event) {
            if (event.target === dialog) dialog.close();
        });
    });
    document.getElementById('openCompare').addEventListener('click', renderCompareDialog);
    document.getElementById('clearCompare').addEventListener('click', function() {
        comparedProducts = [];
        refreshCompareState();
        document.getElementById('productCompare').close();
    });
    document.getElementById('compareContent').addEventListener('click', function(event) {
        var removeButton = event.target.closest('[data-remove-compare]');
        if (!removeButton) return;
        comparedProducts = comparedProducts.filter(function(item) {
            return item.id !== Number(removeButton.dataset.removeCompare);
        });
        refreshCompareState();
        renderCompareDialog();
    });

    var saleCountdownExpired = false;
    function updateProductCountdowns() {
        document.querySelectorAll('.product-countdown[data-countdown-end]').forEach(function(timer) {
            var remaining = Math.max(0, new Date(timer.dataset.countdownEnd).getTime() - Date.now());
            if (remaining === 0) {
                var collectionOffer = timer.closest('.product-collection-progress');
                if (collectionOffer) collectionOffer.remove();
                return;
            }
            var values = {
                days: Math.floor(remaining / 86400000),
                hours: Math.floor((remaining % 86400000) / 3600000),
                minutes: Math.floor((remaining % 3600000) / 60000),
                seconds: Math.floor((remaining % 60000) / 1000)
            };
            Object.keys(values).forEach(function(unit) {
                var field = timer.querySelector('[data-countdown-' + unit + ']');
                if (field) field.textContent = String(values[unit]).padStart(2, '0');
            });
        });
        document.querySelectorAll('[data-sale-countdown-end]').forEach(function(timer) {
            var remaining = Math.max(0, new Date(timer.dataset.saleCountdownEnd).getTime() - Date.now());
            if (remaining === 0) {
                if (!saleCountdownExpired) {
                    saleCountdownExpired = true;
                    window.location.reload();
                }
                return;
            }
            var values = {
                days: Math.floor(remaining / 86400000),
                hours: Math.floor((remaining % 86400000) / 3600000),
                minutes: Math.floor((remaining % 3600000) / 60000),
                seconds: Math.floor((remaining % 60000) / 1000)
            };
            Object.keys(values).forEach(function(unit) {
                var field = timer.querySelector('[data-sale-' + unit + ']');
                if (field) field.textContent = String(values[unit]).padStart(2, '0');
            });
        });
    }
    updateProductCountdowns();
    window.setInterval(updateProductCountdowns, 1000);

    /* ---------- wishlist toggle ---------- */
    function toggleWish(btn, id, ev) {
        if (ev) {
            ev.preventDefault();
            ev.stopPropagation();
        }
        if (btn.disabled) return;
        btn.disabled = true;
        var fd = new FormData();
        fd.append('product_id', id);
        fd.append('_token', csrfToken());

        fetch("{{ route('wishlist.toggle') }}", {
                method: 'POST',
                body: fd,
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) {
                return r.json().then(function(d) {
                    if (d.status === 'guest') return d;
                    if (!r.ok) throw new Error(d.message || 'Could not update your wishlist');
                    return d;
                });
            })
            .then(function(d) {
                if (d.status === 'guest') { showToast(d.message, 'error'); return; }
                if (d.status !== 'added' && d.status !== 'removed') throw new Error(d.message || 'Could not update your wishlist');
                btn.classList.toggle('liked', d.status === 'added');
                if (d.status === 'added') {
                    if (WISH_IDS.indexOf(id) === -1) WISH_IDS.push(id);
                } else {
                    WISH_IDS = WISH_IDS.filter(function(x) { return x !== id; });
                }
                showToast(d.message, 'success');
            })
            .catch(function(err) {
                showToast(err.message || 'Something went wrong', 'error');
            })
            .finally(function() {
                btn.disabled = false;
            });
    }

    function resetFilters() {
        document.getElementById('shopSearch').value = '';
        document.getElementById('sortSelect').value = 'latest';
        currentCat = '';
        var first = document.querySelector('.cat-link');
        document.querySelectorAll('.cat-link').forEach(function(b) { b.classList.remove('active'); });
        if (first) first.classList.add('active');
        applyFilters(1);
    }
</script>

</body>

</html>