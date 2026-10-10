@include('front-end.include.header')

@include('front-end.include.loader')

@php
    /* 🛡️ SELF-SUFFICIENT — route variables na bheje to khud load */
    $homeReviews        = $homeReviews        ?? \App\Models\Review::where('status', 'approved')->latest()->take(3)->get();
    $homeReviewProducts = $homeReviewProducts ?? \App\Models\Product::whereIn('id', $homeReviews->pluck('product_id')->unique())->get()->keyBy('id');
    $hasRealTesti = $homeReviews->count() > 0;
@endphp

<main>
    <!-- ================= HERO ================= -->
    <section class="hero" style="background-image:url('{{ $homeSettings['home_hero_image'] }}')">
        <div class="container">
            <div class="hero-content">
                <p class="hero-kicker">{{ $homeSettings['home_hero_tagline'] }}</p>
                <h1 class="hero-title">{!! nl2br(e($homeSettings['home_hero_title'])) !!}</h1>
                <p class="hero-text">{{ $homeSettings['home_hero_text'] }}</p>
                <a href="{{ route('shop') }}" class="btn btn-outline">{{ $homeSettings['home_hero_button'] }}</a>
            </div>
        </div>
    </section>

    <!-- ================= PRODUCTS (REAL DATA) ================= -->
    @if((bool) $homeSettings['home_show_featured'])
    <section class="products-sec" id="products">
        <div class="container">
            <div class="sec-head reveal">
                <p class="sec-tag">{{ $homeSettings['home_featured_tagline'] }}</p>
                <h2 class="sec-title">{{ $homeSettings['home_featured_title'] }}</h2>
                <p class="sec-sub">{{ $homeSettings['home_featured_text'] }}</p>
            </div>

            <div class="product-grid">
                @foreach ($featuredProducts as $p)
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
                        @include('front-end.include.product-labels', ['product' => $p])

                        <a class="thumb-link" href="{{ route('product.show', $p->slug) }}" aria-label="{{ $p->name }}"></a>

                        <div class="hover-actions">
                            {{-- 🛒 BAG FIRST --}}
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
                            {{-- 💖 HEART SECOND --}}
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

            @if ($featuredProducts->isEmpty())
            <div class="home-empty reveal">
                <div class="he-emoji">🧴</div>
                <h3>No products yet</h3>
                <p>Beautiful things are coming soon.</p>
            </div>
            @endif
        </div>
    </section>
    @endif

    @foreach(($activeProductCollections ?? collect()) as $activeCollection)
    <section class="products-sec product-collection-sec">
        <div class="container">
            <div class="sec-head reveal">
                <p class="sec-tag">Limited time collection</p>
                <h2 class="sec-title">{{ $activeCollection->name }}</h2>
                @if($activeCollection->description)<p class="sec-sub">{{ $activeCollection->description }}</p>@endif
            </div>
            <div class="product-grid">
                @foreach($activeCollection->products as $p)
                    @php
                        $img = $p->images->first();
                        $sold = (int) ($p->paid_sold_quantity ?? 0);
                        $remaining = max(0, (int) $p->stock);
                        $capacity = $sold + $remaining;
                        $soldPercent = $capacity > 0 ? min(100, (int) round($sold * 100 / $capacity)) : 100;
                    @endphp
                    <article class="product-card reveal">
                        <div class="product-thumb">
                            @if($img)
                                <img src="{{ asset('storage/' . $img->path) }}" alt="{{ $p->name }}" loading="lazy">
                            @else
                                <div class="thumb-emoji">{{ $p->emoji ?: '🧴' }}</div>
                            @endif
                            @if($p->stock <= 0)
                                <span class="p-badge badge-out">Out of Stock</span>
                            @elseif($p->is_on_sale)
                                <span class="p-badge badge-sale">@if($p->sale_price !== null)-{{ $p->price > 0 ? (int) round(100 - ($p->final_price / $p->price * 100)) : 0 }}%@elseif($p->discount_type === 'fixed')-£{{ number_format($p->discount, 2) }}@else-{{ (int) $p->discount }}%@endif</span>
                            @endif
                            @include('front-end.include.product-labels', ['product' => $p])
                            <a class="thumb-link" href="{{ route('product.show', $p->slug) }}" aria-label="{{ $p->name }}"></a>
                        </div>
                        <p class="product-cat">{{ $p->category?->name }}</p>
                        <h3 class="product-name"><a href="{{ route('product.show', $p->slug) }}">{{ $p->name }}</a></h3>
                        <p class="product-price">£{{ number_format($p->final_price, 2) }} @if($p->is_on_sale)<del>£{{ number_format($p->price, 2) }}</del>@endif</p>
                        <div class="collection-offer" data-countdown="{{ $activeCollection->ends_at->toIso8601String() }}">
                            <span class="collection-clock-label"><i class="ri-timer-line"></i> Time left</span>
                            <div class="collection-clock" aria-label="Time remaining">
                                <span class="collection-time-unit"><b data-days>00</b><small>Days</small></span>
                                <span class="collection-time-unit"><b data-hours>00</b><small>Hrs</small></span>
                                <span class="collection-time-unit"><b data-minutes>00</b><small>Mins</small></span>
                                <span class="collection-time-unit"><b data-seconds>00</b><small>Secs</small></span>
                            </div>
                            <span class="collection-progress" role="img" aria-label="{{ $sold }} items sold, {{ $remaining }} in stock"><span style="width:{{ $soldPercent }}%"></span></span>
                            <span class="collection-sold">{{ $sold }} sold <span>{{ $remaining }} left</span></span>
                        </div>
                        @include('front-end.include.product-sale-countdown', ['saleProduct' => $p])
                    </article>
                @endforeach
            </div>
        </div>
    </section>
    @endforeach

    <!-- ================= PROMO ================= -->
    @if((bool) $homeSettings['home_show_promo'])
    <section class="promo-sec" id="about">
        <div class="container">
            <div class="promo-card reveal">
                <div class="promo-img">
                    <img src="{{ $homeSettings['home_promo_image'] }}" alt="Cosmetics collection">
                    <div class="promo-badge">
                        <span class="num">{{ $homeSettings['home_promo_badge'] }}</span>
                        <p>{{ $homeSettings['home_promo_badge_text'] }}</p>
                    </div>
                </div>
                <div class="promo-body">
                    <p class="sec-tag">{{ $homeSettings['home_promo_tagline'] }}</p>
                    <h2 class="sec-title">{{ $homeSettings['home_promo_title'] }}</h2>
                    <p>{{ $homeSettings['home_promo_text'] }}</p>
                    <a href="{{ route('shop') }}" class="btn btn-teal">{{ $homeSettings['home_promo_button'] }}</a>
                </div>
            </div>
        </div>
    </section>
    @endif

    @if(($activeAds ?? collect())->isNotEmpty())
    <section class="home-ad-carousel" data-interval="{{ ($adsRotationSeconds ?? 6) * 1000 }}" aria-label="Featured promotions">
        <div class="home-ad-slides">
            @foreach($activeAds as $ad)
            <a class="home-ad-slide {{ $loop->first ? 'is-active' : '' }}"
               href="{{ route('ads.click', $ad) }}"
               @if($ad->opens_new_tab) target="_blank" rel="noopener noreferrer" @endif
               aria-label="{{ $ad->title }}{{ $ad->button_label ? ', '.$ad->button_label : '' }}"
               aria-hidden="{{ $loop->first ? 'false' : 'true' }}">
                <picture>
                   @if($ad->imageSource('tablet_image_url'))<source media="(max-width: 768px)" srcset="{{ $ad->imageSource('tablet_image_url') }}">@endif
                   <img src="{{ $ad->imageSource('image_url') }}" alt="" @if(!$loop->first) loading="lazy" @endif>
                </picture>
                <span class="home-ad-shade"></span>
                <span class="home-ad-copy">
                    <span class="home-ad-eyebrow">A little something special</span>
                    <span class="home-ad-title">{{ $ad->title }}</span>
                    @if($ad->subtitle)<span class="home-ad-subtitle">{{ $ad->subtitle }}</span>@endif
                    @if($ad->button_label)<span class="home-ad-button">{{ $ad->button_label }} <span aria-hidden="true">→</span></span>@endif
                </span>
            </a>
            @endforeach
        </div>
        @if($activeAds->count() > 1)
        <div class="home-ad-dots" role="group" aria-label="Choose a promotion">
            @foreach($activeAds as $ad)
            <button type="button" class="{{ $loop->first ? 'is-active' : '' }}" aria-label="Show promotion {{ $loop->iteration }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}"></button>
            @endforeach
        </div>
        @endif
    </section>
    @endif

    <!-- ================= FEATURES ================= -->
    @if((bool) $homeSettings['home_show_features'])
    <section class="features-sec">
        <div class="container feature-grid">
            <div class="feature reveal">
                <div class="feature-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                        <line x1="19" y1="5" x2="5" y2="19" />
                        <circle cx="7" cy="7" r="2.6" />
                        <circle cx="17" cy="17" r="2.6" />
                    </svg>
                </div>
                <h3>{{ $homeSettings['home_feature_1_title'] }}</h3>
                <p>{{ $homeSettings['home_feature_1_text'] }}</p>
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
                <h3>{{ $homeSettings['home_feature_2_title'] }}</h3>
                <p>{{ $homeSettings['home_feature_2_text'] }}</p>
            </div>
            <div class="feature reveal" style="transition-delay:.2s">
                <div class="feature-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                        <circle cx="10" cy="7" r="4" />
                        <path d="M3 21c0-3.9 3.1-7 7-7 1.4 0 2.7.4 3.8 1.1" />
                        <path d="M14.5 18l2 2 4-4.5" />
                    </svg>
                </div>
                <h3>{{ $homeSettings['home_feature_3_title'] }}</h3>
                <p>{{ $homeSettings['home_feature_3_text'] }}</p>
            </div>
        </div>
    </section>
    @endif

    <!-- ================= BEAUTY BRAND SLIDER ================= -->
    @if(($homeBrands ?? collect())->isNotEmpty())
    <section class="brands-sec" aria-label="Featured brands">
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

    <!-- ================= ⭐ TESTIMONIALS (DYNAMIC — approved reviews) ================= -->
    @if((bool) $homeSettings['home_show_testimonials'] && $hasRealTesti)
    <section class="testi-sec" id="testimonials">
        <div class="container">
            <div class="testi-card reveal">
                <div class="testi-body">
                    <div class="testi-top">
                        <p class="sec-tag">Testimonials</p>
                        <h2 class="sec-title">Our Happy Clients</h2>
                    </div>

                    <div class="testi-stage">
                        @if ($hasRealTesti)
                        {{-- ⭐ REAL APPROVED REVIEWS --}}
                        @foreach ($homeReviews as $r)
                        @php $tp = $homeReviewProducts[$r->product_id] ?? null; @endphp
                        <div class="testi-slide {{ $loop->first ? 'active' : '' }}">
                            <p class="testi-stars">
                                @for ($si = 1; $si <= 5; $si++)
                                <span class="{{ $si <= $r->rating ? 'star-on' : 'star-off' }}">★</span>
                                @endfor
                            </p>
                            <p class="testi-quote">{{ $r->comment }}</p>
                            <div class="testi-person">
                                <span class="testi-init">{{ strtoupper(substr(trim($r->name), 0, 1)) }}</span>
                                <div class="testi-p-info">
                                    <p class="testi-name">{{ $r->name }}</p>
                                    <p class="testi-role">Verified Buyer{{ $tp ? ' · ' . $tp->name : '' }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        @endif
                    </div>

                    <div class="testi-nav">
                        <button class="t-arrow" id="tPrev" aria-label="Previous">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M15 18l-6-6 6-6" />
                            </svg>
                        </button>
                        <div class="t-dots">
                            {{-- ⭐ DOTS BHI DYNAMIC — slides jitne --}}
                            @for ($d = 1; $d <= $homeReviews->count(); $d++)
                            <button class="t-dot {{ $d === 1 ? 'active' : '' }}" aria-label="Slide {{ $d }}"></button>
                            @endfor
                        </div>
                        <button class="t-arrow" id="tNext" aria-label="Next">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 18l6-6-6-6" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="testi-img">
                    <img data-parallax src="{{ $homeSettings['home_testimonial_image'] }}" alt="Happy client">
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- ================= LATEST PRODUCTS (REAL DATA) ================= -->
    @if((bool) $homeSettings['home_show_latest'])
    <section class="latest-sec" id="latest">
        <div class="container">
            <div class="sec-head reveal">
                <p class="sec-tag">{{ $homeSettings['home_latest_tagline'] }}</p>
                <h2 class="sec-title">{{ $homeSettings['home_latest_title'] }}</h2>
                <p class="sec-sub">{{ $homeSettings['home_latest_text'] }}</p>
            </div>

            @if ($latestProducts->count())
            <div class="product-grid">
                @foreach ($latestProducts as $p)
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
            @endif
        </div>
    </section>
    @endif

    <!-- ================= NEWSLETTER ================= -->
 
</main>

@include('front-end.include.newsletter')
@include('front-end.include.footer')
@include('front-end.include.js')

<script>
    /* ---------- home wishlist toggle (real cart drawer is in the header) ---------- */
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

<script>
    (function () {
        var carousel = document.querySelector('.home-ad-carousel');
        if (carousel) {
            var slides = Array.from(carousel.querySelectorAll('.home-ad-slide'));
            var dots = Array.from(carousel.querySelectorAll('.home-ad-dots button'));
            var current = 0;
            var timer = null;
            var interval = Math.max(3000, Number(carousel.dataset.interval) || 6000);
            function show(index) {
                current = (index + slides.length) % slides.length;
                slides.forEach(function (slide, position) {
                    var active = position === current;
                    slide.classList.toggle('is-active', active);
                    slide.setAttribute('aria-hidden', active ? 'false' : 'true');
                });
                dots.forEach(function (dot, position) {
                    var active = position === current;
                    dot.classList.toggle('is-active', active);
                    dot.setAttribute('aria-pressed', active ? 'true' : 'false');
                });
            }
            function stop() {
                if (timer) { window.clearInterval(timer); timer = null; }
                document.removeEventListener('visibilitychange', handleVisibilityChange);
            }
            function start() {
                stop();
                if (slides.length > 1 && !window.matchMedia('(prefers-reduced-motion: reduce)').matches && !document.hidden) {
                    timer = window.setInterval(function () { show(current + 1); }, interval);
                    document.addEventListener('visibilitychange', handleVisibilityChange);
                }
            }
            function handleVisibilityChange() { start(); }
            dots.forEach(function (dot, index) {
                dot.addEventListener('click', function () { show(index); start(); });
            });
            carousel.addEventListener('mouseenter', stop);
            carousel.addEventListener('mouseleave', start);
            carousel.addEventListener('focusin', stop);
            carousel.addEventListener('focusout', function (event) {
                if (!carousel.contains(event.relatedTarget)) start();
            });
            window.addEventListener('ajax:navigating', stop, { once: true });
            window.addEventListener('pagehide', stop, { once: true });
            start();
        }

        var countdowns = document.querySelectorAll('[data-countdown]');
        function updateCountdowns() {
            var now = Date.now();
            countdowns.forEach(function (countdown) {
                var remaining = Math.max(0, new Date(countdown.dataset.countdown).getTime() - now);
                var values = {
                    days: Math.floor(remaining / 86400000),
                    hours: Math.floor((remaining % 86400000) / 3600000),
                    minutes: Math.floor((remaining % 3600000) / 60000),
                    seconds: Math.floor((remaining % 60000) / 1000)
                };
                Object.keys(values).forEach(function (key) {
                    var output = countdown.querySelector('[data-' + key + ']');
                    if (output) output.textContent = String(values[key]).padStart(2, '0');
                });
                if (remaining === 0) {
                    var collectionSection = countdown.closest('.product-collection-sec');
                    if (collectionSection) collectionSection.remove();
                }
            });
        }
        if (countdowns.length) {
            updateCountdowns();
            var countdownTimer = window.setInterval(updateCountdowns, 1000);
            var stopCountdowns = function () { window.clearInterval(countdownTimer); };
            window.addEventListener('ajax:navigating', stopCountdowns, { once: true });
            window.addEventListener('pagehide', stopCountdowns, { once: true });
        }
    }());
</script>

<style>
    .home-ad-carousel{position:relative;width:min(1600px,calc(100% - 2 * clamp(24px,8vw,160px)));margin:clamp(48px,8vw,112px) auto;overflow:hidden;border:1px solid rgba(27,67,67,.1);border-radius:24px;background:#eaf0eb;color:#fff;box-shadow:0 24px 70px rgba(23,55,53,.16)}
    .home-ad-slides{position:relative;min-height:clamp(380px,36vw,560px)}
    .home-ad-slide{position:absolute;inset:0;display:flex;align-items:center;overflow:hidden;background:linear-gradient(110deg,#143b3a 0%,#24524d 43%,#dce6dd 100%);color:#fff;text-decoration:none;opacity:0;visibility:hidden;transition:opacity .7s ease,visibility .7s}
    .home-ad-slide.is-active{position:relative;min-height:inherit;opacity:1;visibility:visible}
    .home-ad-slide picture{position:absolute;top:0;right:2.5%;bottom:0;width:58%;height:100%;display:flex;align-items:center;justify-content:flex-end}
    .home-ad-slide img{display:block;width:100%;height:100%;object-fit:contain;object-position:right center;filter:drop-shadow(0 18px 34px rgba(14,35,31,.2))}
    .home-ad-shade{position:absolute;inset:0;background:linear-gradient(90deg,rgba(12,42,39,.97) 0%,rgba(16,51,47,.88) 35%,rgba(23,64,58,.44) 57%,rgba(23,64,58,.03) 82%);pointer-events:none}
    .home-ad-copy{position:relative;z-index:1;width:min(1240px,calc(100% - 64px));margin:0 auto;padding:64px 0 76px;transform:translateY(20px)}
    .home-ad-eyebrow{display:block;margin-bottom:13px;color:#bde8d8;font:700 11px 'Montserrat',sans-serif;letter-spacing:2px;text-transform:uppercase}
    .home-ad-title{display:block;max-width:620px;font:600 clamp(30px,4.2vw,58px)/1.08 'Playfair Display',Georgia,serif;text-wrap:balance}
    .home-ad-subtitle{display:block;max-width:500px;margin-top:14px;color:rgba(255,255,255,.92);font-size:clamp(13px,1.35vw,16px);line-height:1.65}
    .home-ad-button{display:inline-flex;gap:12px;align-items:center;margin-top:22px;padding:12px 17px;border:1px solid rgba(255,255,255,.75);border-radius:3px;font:700 11px 'Montserrat',sans-serif;letter-spacing:.4px}
    .home-ad-slide:hover .home-ad-button{background:white;color:#19343a}
    .home-ad-dots{position:absolute;z-index:2;left:50%;bottom:19px;display:flex;gap:8px;transform:translateX(-50%)}
    .home-ad-dots button{width:9px;height:9px;padding:0;border:1px solid #fff;border-radius:50%;background:transparent;cursor:pointer;transition:all .2s}
    .home-ad-dots button.is-active{width:25px;border-radius:8px;background:#fff}
    @media(min-width:1500px){.home-ad-slide picture{right:4%;width:56%}.home-ad-slide img{max-width:1100px}}
    @media(max-width:900px){.home-ad-carousel{width:calc(100% - 64px);margin:64px auto;border-radius:20px}.home-ad-slides{min-height:clamp(360px,48vw,440px)}.home-ad-slide picture{right:1%;width:62%}.home-ad-copy{width:calc(100% - 52px);padding:52px 0 70px}.home-ad-title{max-width:54%;font-size:clamp(30px,5.2vw,46px)}.home-ad-subtitle{max-width:48%}.home-ad-shade{background:linear-gradient(90deg,rgba(12,42,39,.97) 0%,rgba(16,51,47,.87) 43%,rgba(23,64,58,.24) 75%,rgba(23,64,58,.02) 100%)}}
    @media(max-width:600px){.home-ad-carousel{width:calc(100% - 32px);margin:40px auto 48px;border-radius:16px}.home-ad-slides{min-height:470px}.home-ad-slide{align-items:flex-start;background:linear-gradient(180deg,#153e3d 0%,#27564f 54%,#dce6dd 100%)}.home-ad-slide picture{top:auto;right:0;bottom:0;left:0;width:100%;height:63%;justify-content:center}.home-ad-slide img{object-position:center bottom}.home-ad-copy{width:calc(100% - 40px);margin:0 auto;padding:30px 0 245px;transform:none}.home-ad-eyebrow{margin-bottom:9px;font-size:9px;letter-spacing:1.6px}.home-ad-title{max-width:100%;font-size:clamp(29px,8vw,38px)}.home-ad-subtitle{max-width:100%;margin-top:9px;font-size:13px;line-height:1.45}.home-ad-button{margin-top:14px;padding:10px 13px;font-size:10px}.home-ad-shade{background:linear-gradient(180deg,rgba(13,43,41,.97) 0%,rgba(17,49,46,.88) 37%,rgba(23,64,58,.16) 69%,rgba(23,64,58,0) 100%)}.home-ad-dots{bottom:13px}}
    .product-collection-sec{padding-top:58px;padding-bottom:64px;background:#edf3f8}
    .product-collection-sec .container{width:min(1200px,calc(100% - 40px))}
    .product-collection-sec .product-grid{grid-template-columns:repeat(auto-fill,minmax(min(100%,235px),1fr));gap:20px}
    .product-collection-sec .product-card{min-width:0;overflow:hidden;border:1px solid #e4eaf0;border-radius:12px;background:#fff;box-shadow:0 8px 24px rgba(32,54,74,.07);transition:transform .25s,box-shadow .25s}
    .product-collection-sec .product-card:hover{transform:translateY(-4px);box-shadow:0 16px 34px rgba(32,54,74,.13)}
    .product-collection-sec .product-thumb{aspect-ratio:3/3.15;background:#f4f6f6}
    .product-collection-sec .product-thumb img{object-fit:cover}
    .product-collection-sec .product-cat,.product-collection-sec .product-name,.product-collection-sec .product-price,.product-collection-sec .collection-offer{margin-left:16px;margin-right:16px}
    .product-collection-sec .product-cat{margin-top:13px;font-size:11px;text-transform:uppercase;letter-spacing:.08em}
    .product-collection-sec .product-name{min-height:2.6em;font-size:14px;line-height:1.4}
    .product-collection-sec .product-price{margin-bottom:12px}
    .product-collection-sec .collection-offer{display:grid;grid-template-columns:1fr;gap:8px;margin-top:0;margin-bottom:15px;padding:11px;border:1px solid #e4eaf0;border-radius:9px;background:#f8fafc;font-variant-numeric:tabular-nums}
    .collection-clock-label{color:#526e7a;font-size:10px;font-weight:700}.collection-clock{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:5px;color:#193a4a;text-align:center}
    .collection-time-unit{display:grid;place-items:center;min-width:0;padding:6px 3px;border:1px solid #e2e8ee;border-radius:6px;background:#fff}
    .collection-time-unit b{font-size:16px;line-height:1.15;font-weight:800}.collection-time-unit small{margin-top:2px;color:#798c98;font-size:8px;font-weight:700;text-transform:uppercase}
    .collection-progress{grid-column:1/-1;height:6px;overflow:hidden;border-radius:6px;background:#e1e7ed}.collection-progress>span{display:block;height:100%;border-radius:6px;background:#1260aa}
    .collection-sold{color:#647b89;font-size:9px}.collection-sold span{float:right}
    .collection-offer.is-ended .collection-clock{grid-column:1/-1;color:#8a4f3f}
    @media(max-width:850px){.product-collection-sec .product-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}}
    @media(max-width:600px){.product-collection-sec .collection-offer{padding:8px}.collection-time-unit{padding:5px 2px}.collection-time-unit b{font-size:13px}.collection-time-unit small{font-size:7px}.product-collection-sec .product-cat,.product-collection-sec .product-name,.product-collection-sec .product-price,.product-collection-sec .collection-offer{margin-left:11px;margin-right:11px}.product-collection-sec .product-name{font-size:12px}}
    /* ---------- real product cards (bag + heart, badges, detail links) ---------- */
    .product-card { position: relative; }

    .thumb-emoji {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 64px;
        background: linear-gradient(160deg, #f2f7f6, #e6f0ef);
    }

    .thumb-link { position: absolute; inset: 0; z-index: 1; }

    .p-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        padding: 5px 11px;
        font: 600 10.5px 'Montserrat', sans-serif;
        letter-spacing: 1px;
        text-transform: uppercase;
        z-index: 2;
    }

    .badge-sale { background: var(--teal); color: #fff; }
    .badge-out { background: #1b1d21; color: #fff; }
    .label-badge { top: 12px; left: 12px; right: auto; }
    .badge-hot { background: #c83a55; color: #fff; }
    .badge-new { background: #295d8f; color: #fff; }
    .badge-label-sale { background: #b86b18; color: #fff; }

    .hover-actions {
        position: absolute;
        top: 12px;
        right: 12px;
        display: flex;
        flex-direction: column;
        gap: 9px;
        z-index: 2;
    }

    .h-ic {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: #fff;
        border: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #1b1d21;
        cursor: pointer;
        box-shadow: 0 8px 20px rgba(0, 0, 0, .16);
        opacity: 0;
        transform: translateX(12px);
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

    .home-empty { text-align: center; padding: 60px 20px; }
    .he-emoji { font-size: 52px; margin-bottom: 12px; }
    .home-empty h3 { font-size: 18px; margin-bottom: 6px; }
    .home-empty p { color: #9aa0a4; }

    /* ---------- ⭐ DYNAMIC TESTIMONIALS extras ---------- */
    /* rating-based stars (real reviews) */
    .testi-stars .star-on  { color: #f0b429; }
    .testi-stars .star-off { color: #dfe3e5; }

    /* initials avatar — reviews mein photo nahi hoti */
    .testi-init {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--teal), #2b7fb8);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font: 700 20px 'Montserrat', sans-serif;
        border: 2px solid rgba(56, 170, 180, .5);
        box-shadow: 0 6px 16px rgba(56, 170, 180, .3);
        flex-shrink: 0;
    }
</style>

</body>

</html>