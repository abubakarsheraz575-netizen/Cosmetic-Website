{{-- ==========================================================
     ABOUT PAGE — DYNAMIC
     • Story text + services  → Admin > About Page se control
     • Right side showcase    → Admin selected REAL product
       (asli photo + name + SKU + price + View Product button)
     • Products + Stats       → Dynamic (pehle jaisa)
============================================================ --}}
@include('front-end.include.header')

@include('front-end.include.loader')

@php
    /* ---------- ADMIN-CONTROLLED CONTENT (Admin > About Page) ---------- */
    $aTag = $aboutSettings['about_tag'];
    $aTitle = $aboutSettings['about_title'];
    $aText = $aboutSettings['about_text'];
    $aServRaw = $aboutSettings['about_services'];
    $aImg = $aboutImages['about_image'];
    $aQuoteImage = $aboutImages['about_quote_image'];
    $aProduct = $aboutProduct;

    $aParas    = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n+/', $aText))));
    $aServices = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $aServRaw))));
@endphp
<link rel="shortcut icon" href="{{ asset('favicon.png') }}?v=3">
<style>
    /* ---------- PAGE HERO ---------- */
    .page-hero {
        position: relative;
        min-height: 46vh;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        background:linear-gradient(115deg, rgba(9, 52, 58, .88) 0%, rgba(24, 116, 126, .72) 50%, rgba(111, 211, 218, .55) 100%);
    }

    .page-hero::after {
        content: '';
        position: absolute;
        left: 0; right: 0; bottom: 0;
        height: 120px;
        background: linear-gradient(180deg, transparent, rgba(247, 248, 248, .35));
        pointer-events: none;
    }

    .ph-inner { position: relative; z-index: 2; padding: 150px 20px 80px; }

    .ph-title {
        font-family: 'Montserrat', sans-serif;
        font-size: clamp(38px, 6vw, 62px);
        font-weight: 300;
        letter-spacing: 4px;
        text-transform: uppercase;
        color: #fff;
        animation: heroUp .9s cubic-bezier(.22, 1, .36, 1) both;
    }

    .ph-crumb {
        margin-top: 14px;
        font-family: 'Montserrat', sans-serif;
        font-size: 12px;
        letter-spacing: 2.5px;
        text-transform: uppercase;
        color: rgba(255, 255, 255, .85);
        animation: heroUp .9s cubic-bezier(.22, 1, .36, 1) .12s both;
    }

    .ph-crumb a { color: #a9e8ee; }
    .ph-crumb a:hover { color: #fff; }

    @keyframes heroUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: none; }
    }

    /* ---------- STORY SECTION ---------- */
    .about-sec { padding: 95px 0 70px; background: var(--bg); position: relative; overflow: hidden; }

    .about-ghost {
        position: absolute;
        top: 120px;
        left: 50%;
        transform: translateX(-50%);
        font-family: 'Allura', cursive;
        font-size: clamp(180px, 28vw, 420px);
        line-height: 1;
        color: rgba(56, 170, 180, .05);
        pointer-events: none;
        white-space: nowrap;
        user-select: none;
    }

    .about-grid {
        position: relative;
        z-index: 2;
        display: grid;
        grid-template-columns: 1.15fr .85fr;
        gap: 60px;
        align-items: center;
        max-width: 980px;
        margin: 0 auto;
    }

    .about-tag {
        color: var(--teal);
        font-family: 'Montserrat', sans-serif;
        font-size: 12.5px;
        font-weight: 600;
        letter-spacing: 2.5px;
        text-transform: uppercase;
    }

    .about-title {
        font-size: clamp(24px, 3vw, 32px);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 12px 0 20px;
    }

    .about-text p { margin: 0 0 20px; font-size: 14.5px; line-height: 1.75; }

    .services-label {
        display: flex;
        align-items: center;
        gap: 14px;
        margin: 26px 0 14px;
        font-family: 'Montserrat', sans-serif;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: var(--teal);
    }
    .services-label::before,
    .services-label::after {
        content: '';
        height: 1px;
        flex: 1;
        background: rgba(56, 170, 180, .34);
    }

    .services-list {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 9px 26px;
    }

    .services-list li {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13.5px;
        color: #555;
        transition: color .3s, transform .3s;
    }

    .services-list li:hover { color: var(--teal); transform: translateX(4px); }

    .services-list li::before {
        content: '✦';
        color: var(--teal);
        font-size: 11px;
    }

    .about-img {
        position: relative;
        border-radius: 4px;
        overflow: hidden;
        box-shadow: 24px 24px 0 rgba(56, 170, 180, .14), 0 30px 60px rgba(0, 0, 0, .12);
        transition: transform .5s ease, box-shadow .5s ease;
    }

    .about-img:hover { transform: translateY(-8px); box-shadow: 24px 32px 0 rgba(56, 170, 180, .2), 0 40px 70px rgba(0, 0, 0, .16); }

    .about-img img { width: 100%; aspect-ratio: 3/3.6; object-fit: cover; }

    /* ---------- PRODUCT SHOWCASE (story right side — admin selected REAL product) ---------- */
    .about-prod { background: #fff; padding-bottom: 26px; }
    .about-prod .ap-media { aspect-ratio: 3/2.5; overflow: hidden; }
    .about-prod .ap-media img { width: 100%; height: 100%; aspect-ratio: auto; object-fit: cover; transition: transform .6s ease; }
    .about-prod:hover .ap-media img { transform: scale(1.05); }
    .about-prod .ap-media .thumb-emoji { font-size: 84px; }

    .ap-card {
        position: relative;
        background: #fff;
        margin: -58px 20px 0;
        padding: 20px 22px 22px;
        box-shadow: 0 22px 48px rgba(0, 0, 0, .12);
        border-top: 3px solid var(--teal);
    }

    .ap-cat {
        display:flex;
        align-items:center;
        gap:7px;
        font: 600 10.5px 'Montserrat', sans-serif;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: var(--teal);
    }
    .ap-category-icon { width:19px;height:19px;object-fit:contain;border-radius:4px; }

    .ap-name {
        font: 600 16px 'Montserrat', sans-serif;
        text-transform: uppercase;
        color: var(--dark);
        margin: 6px 0 3px;
    }

    .ap-sku { font-size: 11.5px; color: #9aa0a4; letter-spacing: .5px; }

    .ap-desc { font-size: 13px; color: #666; margin-top: 8px; line-height: 1.6; }

    .ap-price {
        font: 600 19px 'Montserrat', sans-serif;
        color: #0d3b40;
        margin: 10px 0 14px;
    }

    .ap-price del { color: #b3b9bd; font-size: 13px; margin-left: 6px; font-weight: 500; }

    .ap-btn { display: block; text-align: center; padding: 12px 0; font-size: 11px; letter-spacing: 2px; }

    /* ---------- PRODUCTS (dynamic section) ---------- */
    .prod-sec { padding: 30px 0 90px; background: var(--bg); }

    /* product card extras (same as home — bag + heart + badges) */
    .thumb-emoji {
        width: 100%; height: 100%;
        display: flex; align-items: center; justify-content: center;
        font-size: 64px;
        background: linear-gradient(160deg, #f2f7f6, #e6f0ef);
    }
    .thumb-link { position: absolute; inset: 0; z-index: 1; }
    .p-badge {
        position: absolute; top: 12px; left: 12px;
        padding: 5px 11px;
        font: 600 10.5px 'Montserrat', sans-serif;
        letter-spacing: 1px; text-transform: uppercase; z-index: 2;
    }
    .badge-sale { background: var(--teal); color: #fff; }
    .badge-out { background: #1b1d21; color: #fff; }
    .hover-actions {
        position: absolute; top: 12px; right: 12px;
        display: flex; flex-direction: column; gap: 9px; z-index: 2;
    }
    .h-ic {
        width: 42px; height: 42px; border-radius: 50%;
        background: #fff; border: 0;
        display: flex; align-items: center; justify-content: center;
        color: #1b1d21; cursor: pointer;
        box-shadow: 0 8px 20px rgba(0, 0, 0, .16);
        opacity: 0; transform: translateX(12px);
        transition: all .35s;
    }
    .product-card:hover .h-ic { opacity: 1; transform: none; }
    .h-ic:hover { background: var(--teal); color: #fff; }
    .h-ic.is-off { opacity: .5 !important; cursor: not-allowed; }
    .h-ic.is-off:hover { background: #fff; color: #1b1d21; }
    .h-ic.heart { color: #ff6b81; }
    .h-ic.heart:hover { background: #ff6b81; color: #fff; }
    .h-ic.heart.liked { background: #ff6b81; color: #fff; }
    .product-price del { color: #b3b9bd; font-weight: 500; margin-left: 6px; font-size: 13px; }
    .prod-cta { text-align: center; margin-top: 46px; }

    .home-empty { text-align: center; padding: 50px 20px; }
    .he-emoji { font-size: 48px; margin-bottom: 10px; }
    .home-empty h3 { font-size: 17px; margin-bottom: 5px; }
    .home-empty p { color: #9aa0a4; }

    /* ---------- STATS ---------- */
    .stats-sec { background: #fff; padding: 70px 0; border-top: 1px solid #eef0f1; border-bottom: 1px solid #eef0f1; }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 30px;
        text-align: center;
        max-width: 980px;
        margin: 0 auto;
    }

    .stat-item .num {
        font-family: 'Montserrat', sans-serif;
        font-size: clamp(30px, 4vw, 44px);
        font-weight: 700;
        color: var(--teal);
        line-height: 1.1;
    }

    .stat-item .lbl {
        margin-top: 8px;
        font-family: 'Montserrat', sans-serif;
        font-size: 11.5px;
        font-weight: 600;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: #8b9196;
    }

    /* ---------- MARBLE BAND ---------- */
    .marble-band {
        position: relative;
        min-height: 380px;
        background:
            linear-gradient(180deg, rgba(9, 52, 58, .25), rgba(9, 52, 58, .45)),
            url('{{ $aQuoteImage }}') center / cover no-repeat fixed;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 70px 20px;
    }

    .marble-band blockquote {
        max-width: 720px;
        color: #fff;
        font-family: 'Montserrat', sans-serif;
        font-size: clamp(19px, 2.6vw, 27px);
        font-weight: 300;
        line-height: 1.6;
    }

    .marble-band blockquote::before {
        content: '\201C';
        display: block;
        font-family: Georgia, serif;
        font-size: 74px;
        line-height: .6;
        color: rgba(169, 232, 238, .85);
        margin-bottom: 18px;
    }

    .marble-band cite {
        display: block;
        margin-top: 22px;
        font-style: normal;
        font-family: 'Allura', cursive;
        font-size: 34px;
        color: #a9e8ee;
    }

    /* ---------- FEATURES (page specific) ---------- */
    .about-features { padding: 90px 0 60px; background: var(--bg); }

    /* ---------- RESPONSIVE ---------- */
    @media (max-width: 991px) {
        .about-grid { grid-template-columns: 1fr; gap: 44px; }
        .about-img { max-width: 420px; margin: 0 auto; }
        .ap-card { margin: -40px 14px 0; }
        .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 40px 20px; }
        .marble-band { background-attachment: scroll; }
    }

    @media (max-width: 575px) {
        .services-list { grid-template-columns: 1fr; }
        .about-ghost { font-size: 200px; }
    }
</style>

<main>
    <!-- ================= PAGE HERO ================= -->
    <section class="page-hero" @if($pageHeader['image']) style="background-image:linear-gradient(115deg, rgba(9, 52, 58, .88) 0%, rgba(24, 116, 126, .72) 50%, rgba(111, 211, 218, .55) 100%), url('{{ $pageHeader['image'] }}'); background-position:center, center; background-size:cover;" @endif>
        <div class="ph-inner">
            <h1 class="ph-title">{{ $pageHeader['title'] }}</h1>
            <p class="ph-crumb"><a href="{{ url('/') }}">Home</a> &nbsp;/&nbsp; About Us</p>
        </div>
    </section>

    <!-- ================= STORY (ADMIN CONTROLLED + REAL PRODUCT SHOWCASE) ================= -->
    @if((bool) $aboutSettings['about_show_story'])
    <section class="about-sec">
        <span class="about-ghost" aria-hidden="true">Cosmetics</span>
        <div class="container">
            <div class="about-grid">
                <div class="about-text reveal">
                    <p class="about-tag">{{ $aTag }}</p>
                    <h2 class="about-title">{{ $aTitle }}</h2>
                    @foreach ($aParas as $aPara)
                    <p>{{ $aPara }}</p>
                    @endforeach

                    @if (count($aServices))
                    <p class="services-label">{{ $aboutSettings['about_services_label'] }}</p>
                    <ul class="services-list">
                        @foreach ($aServices as $aServ)
                        <li>{{ $aServ }}</li>
                        @endforeach
                    </ul>
                    @endif
                </div>

                <div class="about-img {{ $aProduct ? 'about-prod' : '' }} reveal" style="transition-delay:.12s">
                    @if ($aProduct)
                    {{-- 🧴 REAL PRODUCT SHOWCASE — asli photo + data (admin ne select kiya) --}}
                    @php $apImg = $aProduct->images->first(); @endphp
                    <div class="ap-media">
                        @if ($apImg)
                        <img src="{{ asset('storage/' . $apImg->path) }}" alt="{{ $aProduct->name }}" loading="lazy">
                        @else
                        <div class="thumb-emoji">{{ $aProduct->emoji ?: '🧴' }}</div>
                        @endif
                    </div>
                    <div class="ap-card">
                        @php
                            $aboutCategoryOverrides = \App\Support\HomeCategoryStrip::overrides();
                            $aboutCategoryOverride = $aboutCategoryOverrides[(string) $aProduct->category?->id] ?? [];
                            $aboutCategoryIcon = trim((string) ($aboutCategoryOverride['icon'] ?? '')) ?: ($aProduct->category?->icon ?? '🧴');
                            $aboutCategoryLogo = !empty($aboutCategoryOverride['logo']) ? asset('storage/' . $aboutCategoryOverride['logo']) : null;
                        @endphp
                        <p class="ap-cat">
                            @if($aboutCategoryLogo)
                            <img class="ap-category-icon" src="{{ $aboutCategoryLogo }}" alt="" loading="lazy">
                            @elseif(\App\Support\HomeCategoryStrip::usesIconFont($aboutCategoryIcon))
                            <i class="{{ \App\Support\HomeCategoryStrip::iconClass($aboutCategoryIcon) }}" aria-hidden="true"></i>
                            @else
                            {{ $aboutCategoryIcon }}
                            @endif
                            {{ $aProduct->category?->name ?? 'Cosmetics' }}
                        </p>
                        <h3 class="ap-name">{{ $aProduct->name }}</h3>
                        <p class="ap-sku">SKU · {{ $aProduct->sku ?? 'COS-'.str_pad($aProduct->id, 8, '0', STR_PAD_LEFT) }}</p>
                        @if (strip_tags((string) $aProduct->description))
                        <p class="ap-desc">{{ \Illuminate\Support\Str::limit(strip_tags((string) $aProduct->description), 90) }}</p>
                        @endif
                        <p class="ap-price">
                            @if ($aProduct->is_on_sale)
                            £{{ number_format($aProduct->final_price, 2) }} <del>£{{ number_format($aProduct->price, 2) }}</del>
                            @else
                            £{{ number_format($aProduct->price, 2) }}
                            @endif
                        </p>
                        <a href="{{ route('product.show', $aProduct->slug) }}" class="btn btn-teal ap-btn">{{ $aboutSettings['about_product_cta'] }}</a>
                    </div>
                    @elseif ($aImg)
                    {{-- 🖼️ ADMIN CUSTOM IMAGE (bina product select kiye) --}}
                    <img src="{{ $aImg }}" alt="Cosmetics products">
                    @else
                    {{-- DEFAULT (jab tak admin kuch na choose kare) --}}
                    <img src="https://images.unsplash.com/photo-1620916566398-39f1143ab7be?auto=format&fit=crop&w=900&q=80" alt="Cosmetics products">
                    @endif
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- ================= OUR PRODUCTS (DYNAMIC — real store products) ================= -->
    @if((bool) $aboutSettings['about_show_products'])
    <section class="prod-sec">
        <div class="container">
            <div class="sec-head reveal">
                <p class="sec-tag">{{ $aboutSettings['about_products_tagline'] }}</p>
                <h2 class="sec-title">{{ $aboutSettings['about_products_title'] }}</h2>
                <p class="sec-sub">{{ $aboutSettings['about_products_text'] }}</p>
            </div>

            @if ($featured->count())
            <div class="product-grid">
                @foreach ($featured as $p)
                @php $img = $p->images->first(); @endphp
                <article class="product-card reveal" style="transition-delay: {{ ($loop->index % 4) * 0.08 }}s">
                    <div class="product-thumb">
                        @if ($img)
                        <img src="{{ asset('storage/' . $img->path) }}" alt="{{ $p->name }}" loading="lazy">
                        @else
                        <div class="thumb-emoji">{{ $p->emoji ?: '🧴' }}</div>
                        @endif

                        @if ($p->stock <= 0)
                        <span class="p-badge badge-out">Out of Stock</span>
                        @elseif ($p->is_on_sale)
                        <span class="p-badge badge-sale">@if($p->sale_price !== null)-{{ $p->price > 0 ? (int) round(100 - ($p->final_price / $p->price * 100)) : 0 }}%@elseif($p->discount_type === 'fixed')-£{{ number_format($p->discount, 2) }}@else-{{ (int) $p->discount }}%@endif</span>
                        @endif

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
                        </div>
                    </div>
                    <p class="product-cat">{{ $p->category?->name }}</p>
                    <h3 class="product-name"><a href="{{ route('product.show', $p->slug) }}">{{ $p->name }}</a></h3>
                    <p class="product-price">
                        @if ($p->is_on_sale)
                        £{{ number_format($p->final_price, 2) }} <del>£{{ number_format($p->price, 2) }}</del>
                        @else
                        £{{ number_format($p->price, 2) }}
                        @endif
                    </p>
                    @include('front-end.include.product-sale-countdown', ['saleProduct' => $p])
                </article>
                @endforeach
            </div>
            @else
            <div class="home-empty reveal">
                <div class="he-emoji">🧴</div>
                <h3>{{ $aboutSettings['about_products_empty_title'] }}</h3>
                <p>{{ $aboutSettings['about_products_empty_text'] }}</p>
            </div>
            @endif

            <div class="prod-cta reveal">
                <a href="{{ route('shop') }}" class="btn btn-teal">{{ $aboutSettings['about_products_button'] }}</a>
            </div>
        </div>
    </section>
    @endif

    <!-- ================= STATS (DYNAMIC — admin Store Settings se) ================= -->
    @if((bool) $aboutSettings['about_show_stats'])
    <section class="stats-sec">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item reveal">
                    <p class="num"><span data-count="{{ (int) $aboutSettings['stat_years'] }}">0</span>{{ $aboutSettings['about_stat_years_suffix'] }}</p>
                    <p class="lbl">{{ $aboutSettings['about_stat_years_label'] }}</p>
                </div>
                <div class="stat-item reveal" style="transition-delay:.08s">
                    <p class="num"><span data-count="{{ (int) $aboutSettings['stat_clients'] }}">0</span>{{ $aboutSettings['about_stat_clients_suffix'] }}</p>
                    <p class="lbl">{{ $aboutSettings['about_stat_clients_label'] }}</p>
                </div>
                <div class="stat-item reveal" style="transition-delay:.16s">
                    <p class="num"><span data-count="{{ (int) $aboutSettings['stat_products'] }}">0</span>{{ $aboutSettings['about_stat_products_suffix'] }}</p>
                    <p class="lbl">{{ $aboutSettings['about_stat_products_label'] }}</p>
                </div>
                <div class="stat-item reveal" style="transition-delay:.24s">
                    <p class="num"><span data-count="{{ (int) $aboutSettings['stat_satisfaction'] }}">0</span>{{ $aboutSettings['about_stat_satisfaction_suffix'] }}</p>
                    <p class="lbl">{{ $aboutSettings['about_stat_satisfaction_label'] }}</p>
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- ================= MARBLE QUOTE BAND ================= -->
    @if((bool) $aboutSettings['about_show_quote'])
    <section class="marble-band">
        <blockquote class="reveal">
            {!! nl2br(e($aboutSettings['about_quote'])) !!}
            <cite>{{ $aboutSettings['about_quote_author'] }}</cite>
        </blockquote>
    </section>
    @endif

    <!-- ================= FEATURES ================= -->
    @if((bool) $aboutSettings['about_show_features'])
    <section class="about-features">
        <div class="container feature-grid">
            <div class="feature reveal">
                <div class="feature-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                        <line x1="19" y1="5" x2="5" y2="19" />
                        <circle cx="7" cy="7" r="2.6" />
                        <circle cx="17" cy="17" r="2.6" />
                    </svg>
                </div>
                <h3>{{ $aboutSettings['about_feature_1_title'] }}</h3>
                <p>{{ $aboutSettings['about_feature_1_text'] }}</p>
            </div>
            <div class="feature reveal" style="transition-delay:.1s">
                <div class="feature-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                        <path d="M1 6h13v10H1z" />
                        <path d="M14 9h4l3 3v4h-7" />
                        <circle cx="6" cy="17.5" r="2" />
                        <circle cx="17.5" cy="17.5" r="2" />
                    </svg>
                </div>
                <h3>{{ $aboutSettings['about_feature_2_title'] }}</h3>
                <p>{{ $aboutSettings['about_feature_2_text'] }}</p>
            </div>
            <div class="feature reveal" style="transition-delay:.2s">
                <div class="feature-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                        <circle cx="10" cy="7" r="4" />
                        <path d="M3 21c0-3.9 3.1-7 7-7 1.4 0 2.7.4 3.8 1.1" />
                        <path d="M14.5 18l2 2 4-4.5" />
                    </svg>
                </div>
                <h3>{{ $aboutSettings['about_feature_3_title'] }}</h3>
                <p>{{ $aboutSettings['about_feature_3_text'] }}</p>
            </div>
        </div>
    </section>
    @endif

    <!-- ================= BEAUTY BRAND SLIDER ================= -->
    @if((bool) $aboutSettings['about_show_categories'] && $homeBrands->isNotEmpty())
    <section class="brands-sec" aria-label="Beauty brands">
        <div class="brands-heading">
            <p>BEAUTY BRANDS</p>
            <h2>Names you know. Beauty you love.</h2>
        </div>
        <div class="brands-marquee" id="brandsMarquee">
            <div class="brands-track" id="brandsTrack">
                <div class="brands-set">
                    @foreach ($homeBrands as $brand)
                    <span class="brand-logo home-brand">
                        @if($brand['logo'])
                        <span class="sym"><img src="{{ $brand['logo'] }}" alt="" loading="lazy"></span>
                        @else
                        <span class="home-brand-mark" aria-hidden="true">{{ $brand['mark'] }}</span>
                        @endif
                        <span>{{ $brand['name'] }}</span>
                    </span>
                    @endforeach
                </div>
                <div class="brands-set" aria-hidden="true">
                    @foreach ($homeBrands as $brand)
                    <span class="brand-logo home-brand">
                        @if($brand['logo'])
                        <span class="sym"><img src="{{ $brand['logo'] }}" alt="" loading="lazy"></span>
                        @else
                        <span class="home-brand-mark" aria-hidden="true">{{ $brand['mark'] }}</span>
                        @endif
                        <span>{{ $brand['name'] }}</span>
                    </span>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- ================= NEWSLETTER ================= -->
  
</main>

@include('front-end.include.newsletter')
@include('front-end.include.footer')
@include('front-end.include.js')

<script>
    /* ---------- About counters (values admin Store Settings se) ---------- */
    document.addEventListener('DOMContentLoaded', function () {
        var counters = document.querySelectorAll('[data-count]');
        if (!counters.length) return;

        var io = ('IntersectionObserver' in window)
            ? new IntersectionObserver(function (entries) {
                entries.forEach(function (en) {
                    if (!en.isIntersecting) return;
                    animate(en.target);
                    io.unobserve(en.target);
                });
            }, { threshold: .4 })
            : null;

        function animate(el) {
            var target = parseInt(el.dataset.count, 10) || 0;
            var start = performance.now(), dur = 1500;
            (function tick(now) {
                var p = Math.min((now - start) / dur, 1);
                el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3))).toLocaleString();
                if (p < 1) requestAnimationFrame(tick);
            })(start);
        }

        counters.forEach(function (el) { io ? io.observe(el) : animate(el); });
    });

    /* ---------- wishlist toggle (addToBag cart-drawer se aata hai) ---------- */
    function toggleWish(btn, id, ev) {
        if (ev) { ev.preventDefault(); ev.stopPropagation(); }
        if (btn.disabled) return;
        btn.disabled = true;
        var fd = new FormData();
        fd.append('product_id', id);
        fd.append('_token', csrfToken());

        fetch("{{ route('wishlist.toggle') }}", {
            method: 'POST', body: fd,
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) {
            return r.json().then(function (d) {
                if (d.status === 'guest') return d;
                if (!r.ok) throw new Error(d.message || 'Could not update your wishlist');
                return d;
            });
        })
        .then(function (d) {
            if (d.status === 'guest') { showToast(d.message, 'error'); return; }
            if (d.status !== 'added' && d.status !== 'removed') throw new Error(d.message || 'Could not update your wishlist');
            btn.classList.toggle('liked', d.status === 'added');
            showToast(d.message, 'success');
        })
        .catch(function (err) { showToast(err.message || 'Something went wrong', 'error'); })
        .finally(function () { btn.disabled = false; });
    }
</script>