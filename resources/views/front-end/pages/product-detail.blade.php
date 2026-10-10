@include('front-end.include.header')

@include('front-end.include.loader')

@php
    $isWished = auth()->check() && auth()->user()->wishlist()->where('product_id', $product->id)->exists();

    /* ⭐ Dynamic shipping — admin Shipping settings se (fallbacks safe) */
    $freeShipOver = \App\Models\Setting::get('free_ship_over') ?: 50;
    $delMinDays   = \App\Models\Setting::get('delivery_min_days') ?: 5;
    $delMaxDays   = \App\Models\Setting::get('delivery_max_days') ?: 7;
    $skuVariantData = $product->skuVariants->map(function ($variant) {
        return [
            'id' => $variant->id,
            'stock' => (int) $variant->stock,
            'options' => $variant->options->map(function ($option) {
                return [
                    'attribute_id' => (int) $option->product_attribute_id,
                    'value' => $option->value,
                    'image' => $option->image_path ? asset('storage/'.$option->image_path) : null,
                    'description' => $option->description,
                    'attribute_name' => $option->attribute?->name ?? 'Option',
                    'color_hex' => $option->color_hex,
                ];
            })->values(),
        ];
    })->values();
@endphp
<link rel="shortcut icon" href="{{ asset('favicon.png') }}?v=3">
<main>
    <!-- ================= BREADCRUMB BAND — header floats INSIDE ================= -->
    <section class="pd-hero" @if($pageHeader['image']) style="background-image:linear-gradient(120deg, rgba(13, 59, 64, .88), rgba(21, 94, 87, .74)), url('{{ $pageHeader['image'] }}'); background-position:center, center; background-size:cover;" @endif>
        <div class="container">
            <h1 class="pd-page-title">{{ $pageHeader['title'] }}</h1>
            <p class="crumbs">
                <a href="/">Home</a><span>/</span>
                <a href="{{ route('shop') }}">Shop</a><span>/</span>
                <b>{{ $product->name }}</b>
            </p>
        </div>

    </section>

    <!-- ================= PRODUCT ================= -->
    <section class="pd-sec">
        <div class="container">
            <div class="pd-card">

                <div class="pd-top">
                    <!-- GALLERY -->
                    <div class="pd-gallery">
                        <div class="pd-main" id="pdMain" onclick="openLightbox()" @if(!count($gallery)) style="cursor:default" @endif>
                            @if (count($gallery))
                            <img id="pdMainImg" src="{{ $gallery[0] }}" alt="{{ $product->name }}">
                            @else
                            <div class="pd-emoji">{{ $product->emoji ?: '🧴' }}</div>
                            @endif

                            @if ($product->stock <= 0)
                            <span class="p-badge badge-out">Out of Stock</span>
                            @elseif ($product->is_on_sale)
                            <span class="p-badge badge-sale">@if($product->sale_price !== null)-{{ $product->price > 0 ? (int) round(100 - ($product->final_price / $product->price * 100)) : 0 }}%@elseif($product->discount_type === 'fixed')-£{{ number_format($product->discount, 2) }}@else-{{ (int) $product->discount }}%@endif</span>
                            @endif
                            @include('front-end.include.product-labels', ['product' => $product])

                            @if (count($gallery) > 1)
                            <button type="button" class="pd-arrow left" onclick="event.stopPropagation(); goImg(gIdx-1)" aria-label="Previous">‹</button>
                            <button type="button" class="pd-arrow right" onclick="event.stopPropagation(); goImg(gIdx+1)" aria-label="Next">›</button>
                            <span class="pd-zoom">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                                </svg>
                            </span>
                            @endif
                        </div>

                        @if (count($gallery) > 1)
                        <div class="pd-thumbs" id="pdThumbs">
                            @foreach ($gallery as $i => $url)
                            <button type="button" class="pd-thumb {{ $i === 0 ? 'active' : '' }}" onclick="goImg({{ $i }})">
                                <img src="{{ $url }}" alt="Photo {{ $i + 1 }}" loading="lazy">
                            </button>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    <!-- INFO -->
                    <div class="pd-info">
                        @if($product->brand)
                        <a class="pd-brand" href="{{ route('shop', ['brand' => $product->brand->slug]) }}">
                            @if($product->brand->logo_path)
                            <img src="{{ asset('storage/' . $product->brand->logo_path) }}" alt="{{ $product->brand->name }} logo">
                            @else
                            <i class="ri-price-tag-3-line" aria-hidden="true"></i>
                            @endif
                            <span>{{ $product->brand->name }}</span>
                        </a>
                        @endif
                        @if ($product->category)
                        <p class="pd-cat"><a href="{{ route('shop', ['category' => $product->category->slug]) }}">{{ $product->category->name }}</a></p>
                        @endif

                        <h2 class="pd-name">{{ $product->name }}</h2>

                        <div class="pd-rating" onclick="jumpReviews()" title="See reviews">
                            <span class="stars">
                                @for ($i = 1; $i <= 5; $i++)
                                <span class="st {{ $i <= round($reviewAvg) ? 'on' : '' }}">★</span>
                                @endfor
                            </span>
                            <span>{{ $reviewTotal }} review{{ $reviewTotal === 1 ? '' : 's' }}</span>
                        </div>

                        @foreach($product->variants->groupBy('product_attribute_id') as $attributeVariants)
                            @php
                                $variantAttribute = $attributeVariants->first()->attribute;
                                $isColorAttribute = $variantAttribute?->type === 'color';
                            @endphp
                            <section class="pd-variants {{ $isColorAttribute ? 'is-color-variants' : '' }}" role="group" aria-label="{{ $variantAttribute?->name ?? 'Product' }} options">
                                <div class="pd-variant-heading">
                                    <span>{{ $variantAttribute?->name ?? 'Option' }}</span>
                                    <strong id="pdVariantValue{{ $variantAttribute?->id }}">{{ $isColorAttribute ? 'Choose a shade' : 'Choose an option' }}</strong>
                                </div>
                                <div class="pd-variant-options">
                                    @foreach($attributeVariants->unique('value') as $variant)
                                        <button type="button"
                                            class="pd-variant-swatch pd-sku-option {{ $isColorAttribute ? 'is-color' : 'is-text' }}"
                                            aria-label="{{ $variant->value }}"
                                            aria-pressed="false"
                                            title="{{ $variant->value }}"
                                            data-value="{{ $variant->value }}"
                                            data-description="{{ $variant->description }}"
                                            data-image="{{ $variant->image_path ? asset('storage/' . $variant->image_path) : '' }}"
                                            data-color-hex="{{ $variant->color_hex }}"
                                            data-attribute-id="{{ $variantAttribute?->id }}"
                                            data-attribute-name="{{ $variantAttribute?->name ?? 'Option' }}"
                                            @if($variant->color_hex) style="--variant-color:{{ $variant->color_hex }}" @endif
                                            onclick="selectProductVariant(this)">
                                            @if(!$isColorAttribute || !$variant->color_hex)<span>{{ $variant->value }}</span>@endif
                                        </button>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                        @if($product->variants->isNotEmpty())
                        <p class="pd-option-description" id="pdOptionDescription" hidden></p>
                        @endif

                        <div class="pd-price">
                            <span class="pd-final">£{{ number_format($product->final_price, 2) }}</span>
                            @if ($product->is_on_sale)
                            <del>£{{ number_format($product->price, 2) }}</del>
                            <span class="pd-save">You save £{{ number_format($product->price - $product->final_price, 2) }}</span>
                            @endif
                        </div>
                        @include('front-end.include.product-sale-countdown', ['saleProduct' => $product])

                        <p class="pd-stock {{ $product->stock <= 0 ? 'out' : 'ok' }}" id="pdStock">
                            @if($product->skuVariants->isNotEmpty())
                                ● Choose options to check availability
                            @elseif($product->stock <= 0)
                                ● Out of stock
                            @elseif($product->stock <= 5)
                                ● Only {{ $product->stock }} left in stock
                            @else
                                ● In stock — ready to ship
                            @endif
                        </p>

                        <p class="pd-desc">{{ \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 170) ?: 'A beautiful essential for your everyday routine.' }}</p>

                        <div class="pd-actions">
                            <div class="qty-box">
                                <button type="button" onclick="qtyStep(-1)">−</button>
                                <input type="text" id="pdQty" value="1" readonly>
                                <button type="button" onclick="qtyStep(1)">+</button>
                            </div>

                            <button type="button" class="pd-add" id="pdAdd" onclick="addFromDetail(event)" @disabled($product->stock <= 0)>
                                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" />
                                </svg>
                                <span class="pd-add-txt">Add to Bag — <span id="pdAddTotal">£{{ number_format($product->final_price, 2) }}</span></span>
                            </button>

                            <button type="button" class="pd-wish {{ $isWished ? 'liked' : '' }}" id="pdWish" onclick="toggleWishDetail(event)" title="Save to wishlist">
                                <svg width="19" height="19" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21.2l7.8-7.7 1-1.1a5.5 5.5 0 0 0 0-7.8z" />
                                </svg>
                            </button>
                        </div>

                        <!-- ⭐ DYNAMIC SHIPPING META — admin Shipping settings se -->
                        <div class="pd-meta">
                            <span>🚚 Free shipping over £{{ number_format((float) $freeShipOver, 2) }}</span>
                            <span>📦 Delivery in {{ $delMinDays }}–{{ $delMaxDays }} days</span>
                            <span>↩ 7-day easy returns</span>

                            @if ($product->size)
                            <span>🧪 {{ $product->size->ml }} ml</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- TABS -->
                <div class="pd-tabs-wrap" id="pdTabsWrap">
                    <div class="pd-tabs">
                        <button type="button" class="pd-tab active" onclick="pdTab('desc', this)">Description</button>
                        <button type="button" class="pd-tab" id="tabBtnReviews" onclick="pdTab('reviews', this)">Reviews (<span id="rvCount">{{ $reviewTotal }}</span>)</button>
                    </div>

                    <div class="pd-pane active" id="pane-desc">
                        <div class="pd-desc-full">{!! nl2br(e($product->description ?: 'No description yet.')) !!}</div>
                        <div class="pd-specs">
                            <div><span>Category</span><b>{{ $product->category?->name ?? '—' }}</b></div>
                            <div><span>Size</span><b>{{ $product->size ? $product->size->ml . ' ml' : '—' }}</b></div>
                            <div><span>SKU</span><b>GL-{{ str_pad($product->id, 4, '0', STR_PAD_LEFT) }}</b></div>
                            <div><span>Added</span><b>{{ $product->created_at->format('M d, Y') }}</b></div>
                            @foreach($product->specificationValues->filter(fn ($item) => filled($item->value))->sortBy(fn ($item) => $item->attribute?->sort_order ?? 0) as $specification)
                            <div><span>{{ $specification->attribute->name }}</span><b>{{ $specification->value }}</b></div>
                            @endforeach
                        </div>
                    </div>

                    <div class="pd-pane" id="reviews">
                        <div class="rv-grid">
                            <div class="rv-summary">
                                <div class="rv-big" id="rvBig">{{ number_format($reviewAvg, 1) }}</div>
                                <span class="stars" id="rvAvgStars">
                                    @for ($i = 1; $i <= 5; $i++)
                                    <span class="st {{ $i <= round($reviewAvg) ? 'on' : '' }}">★</span>
                                    @endfor
                                </span>
                                <p class="rv-total" id="rvTotalTxt">Based on {{ $reviewTotal }} review{{ $reviewTotal === 1 ? '' : 's' }}</p>
                                <div class="rv-bars">
                                    @for ($s = 5; $s >= 1; $s--)
                                    <div class="rv-bar">
                                        <span>{{ $s }}★</span>
                                        <div class="track">
                                            <div class="fill" id="barFill{{ $s }}" style="width: {{ $reviewTotal ? round($dist[$s] / $reviewTotal * 100) : 0 }}%"></div>
                                        </div>
                                        <b id="barCount{{ $s }}">{{ $dist[$s] }}</b>
                                    </div>
                                    @endfor
                                </div>
                            </div>

                            <div class="rv-main">
                                <div class="rv-list" id="rvList">
                                    @forelse ($reviews as $r)
                                    <div class="rv-item">
                                        <div class="rv-head">
                                            <div>
                                                <p class="rv-name">{{ $r->name }}</p>
                                                <span class="stars">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                    <span class="st {{ $i <= $r->rating ? 'on' : '' }}">★</span>
                                                    @endfor
                                                </span>
                                            </div>
                                            <span class="rv-date">{{ $r->created_at->format('M d, Y') }}</span>
                                        </div>
                                        <p class="rv-text">{{ $r->comment }}</p>
                                    </div>
                                    @empty
                                    <p class="rv-none" id="rvNone">No reviews yet — be the first to share your experience!</p>
                                    @endforelse
                                </div>

                                @auth
                                <form class="rv-form" onsubmit="submitReview(event)">
                                    <h4>Write a Review</h4>
                                    <div class="rv-form-row">
                                        <input type="text" id="rvName" value="{{ e(auth()->user()->name) }}" readonly title="Your account name">
                                        <div class="star-input" id="starInput">
                                            <button type="button" onclick="pickStar(1)">★</button>
                                            <button type="button" onclick="pickStar(2)">★</button>
                                            <button type="button" onclick="pickStar(3)">★</button>
                                            <button type="button" onclick="pickStar(4)">★</button>
                                            <button type="button" onclick="pickStar(5)">★</button>
                                        </div>
                                    </div>
                                    <textarea id="rvComment" rows="4" placeholder="Share your experience with this product *" maxlength="1500" required></textarea>
                                    <button type="submit" class="rv-submit" id="rvSubmit">Submit Review</button>
                                    <p class="rv-note">✓ Reviews are checked by our team before appearing live.</p>
                                </form>
                                @endauth
                                @guest
                                <div class="rv-form rv-login-nudge">
                                    <h4>Write a Review</h4>
                                    <div class="rvl-emoji">🔐</div>
                                    <p class="rvl-txt">Only logged-in members can share reviews.<br>Login karke apna experience share karein!</p>
                                    <a class="rv-submit rvl-btn" href="{{ route('login.redirect', ['redirect' => request()->getRequestUri() . '#reviews']) }}">Login to Write a Review</a>
                                </div>
                                @endguest
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RELATED -->
            @if ($related->count())
            <div class="related">
                <div class="rel-head">
                    <p class="sec-tag">Others also bought</p>
                    <h2 class="sec-title">You May Also Like</h2>
                </div>
                <div class="product-grid rel-grid">
                    @foreach ($related as $p)
                    @php $img = $p->images->first(); @endphp
                    <article class="product-card">
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
                                <a class="h-ic" title="Choose options" href="{{ route('product.show', $p->slug) }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" />
                                    </svg>
                                </a>
                                @else
                                <button type="button" class="h-ic {{ $p->stock <= 0 ? 'is-off' : '' }}" title="Add to bag" onclick="addToBag({{ $p->id }}, 1, event)" @disabled($p->stock <= 0)>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" />
                                    </svg>
                                </button>
                                @endif
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
            </div>
            @endif
        </div>
    </section>
</main>

<!-- ================= LIGHTBOX ================= -->
@if (count($gallery))
<div class="lb" id="lightbox">
    <span class="lb-counter" id="lbCounter">1 / {{ count($gallery) }}</span>
    <button type="button" class="lb-close" onclick="closeLightbox()" aria-label="Close">&times;</button>
    <button type="button" class="lb-arrow left" onclick="goImg(gIdx-1)" aria-label="Previous">‹</button>
    <figure class="lb-fig"><img id="lbImg" src="{{ $gallery[0] }}" alt="{{ $product->name }}"></figure>
    <button type="button" class="lb-arrow right" onclick="goImg(gIdx+1)" aria-label="Next">›</button>
    @if (count($gallery) > 1)
    <div class="lb-thumbs" id="lbThumbs">
        @foreach ($gallery as $i => $url)
        <button type="button" class="lb-thumb {{ $i === 0 ? 'active' : '' }}" onclick="goImg({{ $i }})"><img src="{{ $url }}" alt=""></button>
        @endforeach
    </div>
    @endif
</div>
@endif

<style>
    /* ═══ NO white sticky header — header floats INSIDE the teal band ═══ */

    .pd-hero { background: linear-gradient(120deg, #0d3b40, #155e57); position: relative; overflow: hidden }
    .pd-hero::after { content: 'glow'; position: absolute; right: 5%; top: 50%; transform: translateY(-50%); font-family: 'Allura', cursive; font-size: 110px; color: rgba(255, 255, 255, .06); pointer-events: none }
    .pd-hero .container { position: relative; z-index: 2; padding: 122px 20px 36px }
    .pd-page-title { margin:0 0 10px;color:#fff;font:300 clamp(28px,4vw,42px) 'Montserrat',sans-serif;text-transform:uppercase;letter-spacing:2px }
    .crumbs { color: rgba(255, 255, 255, .75); font-size: 13px }
    .crumbs a { color: rgba(255, 255, 255, .75) }
    .crumbs a:hover { color: #fff }
    .crumbs span { margin: 0 9px; opacity: .45 }
    .crumbs b { color: #fff; font-weight: 600 }

    .pd-sec { padding: 60px 0 95px; background: var(--bg) }
    .pd-card { background: #fff; box-shadow: 0 24px 70px rgba(0, 0, 0, .07); padding: 44px }
    .pd-top { display: grid; grid-template-columns: 1.02fr .98fr; gap: 46px }

    .pd-main { position: relative; aspect-ratio: 1/1; background: #f4f6f6; overflow: hidden; cursor: zoom-in }
    .pd-main img { width: 100%; height: 100%; object-fit: cover; transition: transform .6s ease }
    .pd-main:hover img { transform: scale(1.05) }
    .pd-emoji { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 110px; background: linear-gradient(160deg, #f2f7f6, #e6f0ef) }
    .pd-zoom { position: absolute; right: 14px; bottom: 14px; width: 40px; height: 40px; background: rgba(255, 255, 255, .92); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #1b1d21; box-shadow: 0 8px 20px rgba(0, 0, 0, .16) }
    .p-badge { position: absolute; top: 12px; left: 12px; padding: 5px 11px; font: 600 10.5px 'Montserrat', sans-serif; letter-spacing: 1px; text-transform: uppercase; z-index: 2 }
    .badge-sale { background: var(--teal); color: #fff }
    .badge-out { background: #1b1d21; color: #fff }
    .label-badge { left: 12px; right: auto }
    .badge-hot { background: #c83a55; color: #fff }
    .badge-new { background: #295d8f; color: #fff }
    .pd-arrow { position: absolute; top: 50%; transform: translateY(-50%); width: 42px; height: 42px; border-radius: 50%; background: rgba(255, 255, 255, .9); border: 0; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #1b1d21; opacity: 0; transition: .3s; box-shadow: 0 8px 20px rgba(0, 0, 0, .16); z-index: 2 }
    .pd-main:hover .pd-arrow { opacity: 1 }
    .pd-arrow.left { left: 12px }
    .pd-arrow.right { right: 12px }
    .pd-arrow:hover { background: var(--teal); color: #fff }
    .pd-thumbs { display: grid; grid-template-columns: repeat(6, 1fr); gap: 10px; margin-top: 12px }
    .pd-thumb { aspect-ratio: 1; border: 2px solid transparent; padding: 0; background: #f4f6f6; cursor: pointer; overflow: hidden; transition: .25s }
    .pd-thumb img { width: 100%; height: 100%; object-fit: cover }
    .pd-thumb.active { border-color: var(--teal) }
    .pd-thumb:hover { border-color: #9fd9d3 }

    .pd-cat { font: 500 12.5px 'Montserrat', sans-serif; letter-spacing: 2px; text-transform: uppercase; color: var(--teal) }
    .pd-cat a { color: var(--teal) }
    .pd-cat a:hover { text-decoration: underline }
    .pd-name { font: 300 clamp(26px, 3.4vw, 38px) 'Montserrat', sans-serif; text-transform: uppercase; color: var(--dark); margin: 8px 0 10px; line-height: 1.2 }
    .stars { color: #f0b429; letter-spacing: 3px; font-size: 14px }
    .st { color: #dfe3e5 }
    .st.on { color: #f0b429 }
    .pd-rating { display: flex; align-items: center; gap: 10px; font-size: 13px; color: #9aa0a4; cursor: pointer }
    .pd-variants { display:grid;gap:12px;margin:20px 0 5px;color:#58656a;font-size:13px }
    .pd-variant-heading { display:flex;align-items:baseline;gap:8px;flex-wrap:wrap;font-weight:600 }
    .pd-variant-heading strong { color:#0d3b40;font-size:12px;font-weight:500 }
    .pd-variant-options { display:flex;align-items:center;flex-wrap:wrap;gap:11px }
    .pd-variant-swatch { position:relative;width:40px;height:40px;display:grid;place-items:center;padding:0;overflow:hidden;border:2px solid #e1e7e7;border-radius:50%;background:var(--variant-color,#fff);color:#0d3b40;cursor:pointer;transition:border-color .18s,box-shadow .18s,transform .18s }
    .pd-variant-swatch.is-color:not([style*="--variant-color"]) { background:#f0f2f2 }
    .pd-variant-swatch.is-text { width:auto;min-width:42px;height:38px;padding:0 13px;border-radius:999px;background:#fff;font-size:11px;font-weight:700 }
    .pd-variant-swatch:hover { transform:translateY(-1px);border-color:#9abbb8 }
    .pd-variant-swatch[aria-pressed="true"] { border-color:var(--teal);box-shadow:0 0 0 3px rgba(56,170,180,.18) }
    .pd-variant-swatch:focus-visible { outline:2px solid var(--teal);outline-offset:3px }
    .pd-option-description { margin:11px 0 0;padding:10px 12px;border-left:3px solid var(--teal);border-radius:0 9px 9px 0;background:#f2f9f8;color:#58656a;font-size:12px;line-height:1.6 }
    .pd-brand { display:inline-flex;align-items:center;gap:9px;width:max-content;max-width:100%;margin-bottom:14px;padding:8px 12px;border:1px solid #e6eeee;border-radius:999px;background:#fff;color:#315f5a;font:600 12px 'Montserrat',sans-serif;letter-spacing:.5px;text-decoration:none; }
    .pd-brand:hover { border-color:var(--teal);color:var(--teal-dark) }
    .pd-brand img { width:27px;height:27px;object-fit:contain;border-radius:50% }
    .pd-brand i { font-size:19px }
    .pd-price { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; margin: 18px 0 6px }
    .pd-final { font: 600 30px 'Montserrat', sans-serif; color: #0d3b40 }
    .pd-price del { color: #b3b9bd; font-size: 18px }
    .pd-save { background: #e7f8f3; color: #0d8f6e; font: 600 12px 'Montserrat', sans-serif; padding: 6px 12px }
    .pd-stock { font-size: 13.5px; margin: 10px 0 16px }
    .pd-stock.ok { color: #0d8f6e }
    .pd-stock.low { color: #c77d0a }
    .pd-stock.out { color: #d64545 }
    .pd-desc { color: #666; font-size: 14.5px; margin-bottom: 24px }

    .pd-actions { display: flex; gap: 14px; flex-wrap: wrap; margin-bottom: 22px; align-items: stretch }
    .qty-box { display: flex; border: 1px solid #e2e6e8; height: 52px }
    .qty-box button { width: 44px; background: #fff; border: 0; font-size: 18px; cursor: pointer; color: #1b1d21; transition: .25s }
    .qty-box button:hover { background: var(--teal); color: #fff }
    .qty-box input { width: 52px; text-align: center; border: 0; border-left: 1px solid #e2e6e8; border-right: 1px solid #e2e6e8; font: 600 15px 'Montserrat', sans-serif; color: #1b1d21; outline: none }

    .pd-add { flex: 1; min-width: 180px; height: 52px; border: 0; cursor: pointer; font: 600 12px 'Montserrat', sans-serif; letter-spacing: 2px; text-transform: uppercase; color: #fff; background: var(--teal); padding: 0 22px; display: flex; align-items: center; justify-content: center; gap: 10px; transition: .3s; white-space: nowrap }
    .pd-add:hover { background: var(--teal-dark); box-shadow: 0 12px 28px rgba(56, 170, 180, .35); transform: translateY(-2px) }
    .pd-add:disabled { background: #c3c9cc; cursor: not-allowed; box-shadow: none; transform: none }

    .pd-wish { width: 52px; height: 52px; min-width: 52px; border: 1px solid #e2e6e8; border-radius: 12px; background: #fff; display: flex; align-items: center; justify-content: center; color: #ff6b81; cursor: pointer; transition: .25s }
    .pd-wish:hover { border-color: #ff6b81; transform: translateY(-2px); box-shadow: 0 8px 18px rgba(255, 107, 129, .25) }
    .pd-wish.liked { background: #ff6b81; border-color: #ff6b81; color: #fff }
    .pd-wish.busy { pointer-events: none; opacity: .6 }

    .pd-meta { display: flex; flex-wrap: wrap; gap: 10px 24px; padding-top: 18px; border-top: 1px dashed #e4e8ea; font-size: 13px; color: #7a7f84 }

    .pd-tabs-wrap { margin-top: 46px; border-top: 1px solid #eef1f2; padding-top: 8px }
    .pd-tabs { display: flex; gap: 6px; justify-content: center; flex-wrap: wrap }
    .pd-tab { background: none; border: 0; padding: 14px 22px; font: 500 13px 'Montserrat', sans-serif; letter-spacing: 1.6px; text-transform: uppercase; color: #8a9298; cursor: pointer; border-bottom: 2px solid transparent; transition: .25s }
    .pd-tab:hover { color: #0d3b40 }
    .pd-tab.active { color: #0d3b40; border-bottom-color: var(--teal) }
    .pd-pane { display: none; padding: 34px 6px 8px; max-width: 920px; margin: 0 auto }
    .pd-pane.active { display: block; animation: fadeUp .45s both }
    @keyframes fadeUp { from { opacity: 0; transform: translateY(14px) } to { opacity: 1; transform: none } }
    .pd-desc-full { color: #555; line-height: 1.9 }
    .pd-specs { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-top: 28px }
    .pd-specs div { background: #f7fafa; border: 1px solid #eef1f2; padding: 14px 16px }
    .pd-specs span { display: block; font-size: 11.5px; text-transform: uppercase; letter-spacing: 1.2px; color: #9aa0a4; margin-bottom: 3px }
    .pd-specs b { font: 600 14px 'Montserrat', sans-serif; color: #1b1d21 }

    .rv-grid { display: grid; grid-template-columns: 280px 1fr; gap: 40px }
    .rv-summary { background: #f7fafa; border: 1px solid #eef1f2; padding: 30px; text-align: center; height: max-content }
    .rv-big { font: 600 52px 'Montserrat', sans-serif; color: #0d3b40; line-height: 1 }
    .rv-total { font-size: 13px; color: #9aa0a4; margin-top: 6px }
    .rv-bars { margin-top: 22px; text-align: left }
    .rv-bar { display: flex; align-items: center; gap: 10px; font-size: 12.5px; color: #7a7f84; margin-bottom: 9px }
    .rv-bar span { width: 26px }
    .rv-bar .track { flex: 1; height: 7px; background: #e8eded; border-radius: 10px; overflow: hidden }
    .rv-bar .fill { height: 100%; background: var(--teal); border-radius: 10px; transition: width .8s ease }
    .rv-bar b { width: 26px; text-align: right; color: #444 }
    .rv-item { padding: 20px 0; border-bottom: 1px dashed #e4e8ea }
    .rv-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap }
    .rv-name { font: 600 14px 'Montserrat', sans-serif; color: #1b1d21; margin-bottom: 2px }
    .rv-date { font-size: 12.5px; color: #aab1b5 }
    .rv-text { margin-top: 8px; font-size: 14.5px; color: #555 }
    .rv-none { color: #9aa0a4; font-style: italic }
    .rv-form { margin-top: 30px; background: #f7fafa; border: 1px solid #eef1f2; padding: 28px }
    .rv-form h4 { font: 500 15px 'Montserrat', sans-serif; letter-spacing: 1.8px; text-transform: uppercase; margin-bottom: 18px; color: #1b1d21 }
    .rv-form-row { display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 14px }
    .rv-form-row input { flex: 1; min-width: 200px }
    .rv-form input, .rv-form textarea { border: 1px solid #dfe3e5; background: #fff; padding: 12px 14px; font: inherit; font-size: 14px; outline: none; transition: border .25s; color: #333; resize: vertical }
    .rv-form input:focus, .rv-form textarea:focus { border-color: var(--teal) }
    .rv-form textarea { width: 100%; margin-bottom: 16px }
    .star-input { display: flex; gap: 6px; align-items: center }
    .star-input button { background: none; border: 0; font-size: 26px; color: #d8dcde; cursor: pointer; transition: .15s; padding: 0 2px }
    .star-input button.on { color: #f0b429 }
    .star-input button:hover { transform: scale(1.15) }
    .rv-submit { background: var(--teal); color: #fff; border: 0; padding: 14px 34px; font: 600 11.5px 'Montserrat', sans-serif; letter-spacing: 2px; text-transform: uppercase; cursor: pointer; transition: .3s }
    .rv-submit:hover { background: var(--teal-dark); box-shadow: 0 12px 26px rgba(56, 170, 180, .35) }
    .rv-submit:disabled { background: #c3c9cc; cursor: wait }

    .related { margin-top: 70px }
    .rel-head { text-align: center; margin-bottom: 40px }
    .sec-tag { color: var(--teal); font: 500 13px 'Montserrat', sans-serif; letter-spacing: 2.5px; text-transform: uppercase }
    .sec-title { font: 400 clamp(24px, 3.4vw, 32px) 'Montserrat', sans-serif; text-transform: uppercase; letter-spacing: .5px; color: var(--dark); margin-top: 10px }
    .product-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 26px }
    .product-card { animation: fadeUp .5s both }
    .product-thumb { position: relative; overflow: hidden; aspect-ratio: 3/3.4; background: #f4f6f6; box-shadow: 0 10px 30px rgba(0, 0, 0, .06) }
    .product-thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform .6s ease }
    .product-card:hover .product-thumb img { transform: scale(1.07) }
    .thumb-emoji { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 64px; background: linear-gradient(160deg, #f2f7f6, #e6f0ef) }
    .thumb-link { position: absolute; inset: 0; z-index: 1 }
    .hover-actions { position: absolute; top: 12px; right: 12px; z-index: 2 }
    .h-ic { width: 40px; height: 40px; border-radius: 50%; background: #fff; border: 0; display: flex; align-items: center; justify-content: center; color: #1b1d21; cursor: pointer; box-shadow: 0 8px 20px rgba(0, 0, 0, .16); opacity: 0; transform: translateX(12px); transition: all .35s }
    .product-card:hover .h-ic { opacity: 1; transform: none }
    .h-ic:hover { background: var(--teal); color: #fff }
    .h-ic.is-off { opacity: .5 !important; cursor: not-allowed }
    .product-cat { font-size: 12px; color: #9aa0a4; margin: 14px 0 3px; text-transform: uppercase; letter-spacing: .8px }
    .product-name { font: 600 15px 'Montserrat', sans-serif; letter-spacing: .5px; text-transform: uppercase; color: var(--dark) }
    .product-name a:hover { color: var(--teal) }
    .product-price { margin-top: 5px; font: 600 14.5px 'Montserrat', sans-serif; color: #333 }
    .product-price del { color: #b3b9bd; font-weight: 500; margin-left: 7px; font-size: 13px }

    .lb { position: fixed; inset: 0; z-index: 12000; background: rgba(5, 9, 11, .93); backdrop-filter: blur(8px); display: flex; flex-direction: column; align-items: center; justify-content: center; visibility: hidden; opacity: 0; transition: opacity .3s, visibility .3s }
    .lb.open { visibility: visible; opacity: 1 }
    .lb-fig { margin: 0 }
    .lb-fig img { max-width: 86vw; max-height: 72vh; object-fit: contain; box-shadow: 0 30px 80px rgba(0, 0, 0, .6); animation: lbIn .35s cubic-bezier(.22, 1, .36, 1) }
    @keyframes lbIn { from { opacity: 0; transform: scale(.94) } to { opacity: 1; transform: none } }
    .lb-arrow { position: fixed; top: 50%; transform: translateY(-50%); width: 52px; height: 52px; border-radius: 50%; border: 1px solid rgba(255, 255, 255, .25); background: rgba(255, 255, 255, .08); color: #fff; font-size: 24px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: .25s; backdrop-filter: blur(6px); z-index: 2 }
    .lb-arrow:hover { background: var(--teal); border-color: var(--teal) }
    .lb-arrow.left { left: 26px }
    .lb-arrow.right { right: 26px }
    .lb-close { position: fixed; top: 22px; right: 26px; width: 46px; height: 46px; border-radius: 50%; border: 1px solid rgba(255, 255, 255, .25); background: rgba(255, 255, 255, .08); color: #fff; font-size: 24px; cursor: pointer; transition: .25s; z-index: 2 }
    .lb-close:hover { background: #d64545; border-color: #d64545; transform: rotate(90deg) }
    .lb-counter { position: fixed; top: 36px; left: 34px; color: rgba(255, 255, 255, .75); font: 500 13px 'Montserrat', sans-serif; letter-spacing: 2px; z-index: 2 }
    .lb-thumbs { display: flex; gap: 9px; margin-top: 22px; flex-wrap: wrap; justify-content: center; max-width: 86vw }
    .lb-thumb { width: 64px; height: 64px; border: 2px solid transparent; padding: 0; background: none; cursor: pointer; opacity: .55; transition: .25s }
    .lb-thumb img { width: 100%; height: 100%; object-fit: cover }
    .lb-thumb.active { border-color: var(--teal); opacity: 1 }
    .lb-thumb:hover { opacity: 1 }

    @media (max-width: 991px) {
        .pd-card { padding: 26px }
        .pd-top { grid-template-columns: 1fr; gap: 30px }
        .rv-grid { grid-template-columns: 1fr }
        .product-grid { grid-template-columns: repeat(2, 1fr) }
        .pd-specs { grid-template-columns: repeat(2, 1fr) }
        .pd-thumbs { grid-template-columns: repeat(4, 1fr) }
        .pd-hero .container { padding-top: 96px }
    }
    @media (max-width: 575px) {
        .product-grid { grid-template-columns: 1fr }
        .pd-hero::after { display: none }
    }

    .rv-form input[readonly] { background: #f2f6f6; color: #555; cursor: not-allowed }
    .rv-note { margin-top: 12px; font-size: 12px; color: #0d8f6e }
    .rv-note.ok { font-weight: 600 }
    .rv-login-nudge { text-align: center; padding: 36px 24px }
    .rvl-emoji { font-size: 42px; margin-bottom: 8px }
    .rvl-txt { color: #666; font-size: 14px; line-height: 1.7; margin-bottom: 18px }
    .rvl-btn { display: inline-block; text-decoration: none; cursor: pointer }
</style>

@include('front-end.include.footer')
@include('front-end.include.js')

<script>
    /* ═══ ⚠️ ALL BLADE VARS ON ONE LINE — mangled multi-line ones killed the script! ═══ */
    var gallery = @json($gallery);
    var BASE_GALLERY = gallery.slice();
    var gIdx = 0;
    var BASE_STOCK = {{ (int) $product->stock }};
    var STOCK_MAX = BASE_STOCK;
    var UNIT_PRICE = {{ (float) $product->final_price }};
    var PRODUCT_ID = {{ $product->id }};
    var SKU_VARIANTS = @json($skuVariantData);
    var SELECTED_SKU_ID = null;

    function setSelectedOptionImage(image) {
        gallery = BASE_GALLERY.slice();
        if (image) gallery = [image].concat(gallery.filter(function(url) { return url !== image; })).slice(0, 6);
        gIdx = 0;

        var mainPanel = document.getElementById('pdMain');
        var mainImage = document.getElementById('pdMainImg');
        if (!gallery.length) {
            mainPanel.style.cursor = 'default';
            if (mainImage) mainImage.remove();
            if (!mainPanel.querySelector('.pd-emoji')) {
                var placeholder = document.createElement('div');
                placeholder.className = 'pd-emoji';
                placeholder.textContent = '{{ addslashes($product->emoji ?: '🧴') }}';
                mainPanel.prepend(placeholder);
            }
            var oldThumbs = document.getElementById('pdThumbs');
            if (oldThumbs) oldThumbs.remove();
            return;
        }
        if (!mainImage) {
            mainImage = document.createElement('img');
            mainImage.id = 'pdMainImg';
            mainImage.alt = '{{ addslashes($product->name) }}';
            mainPanel.querySelector('.pd-emoji')?.remove();
            mainPanel.prepend(mainImage);
        }
        mainPanel.style.cursor = 'zoom-in';
        mainImage.src = gallery[0];

        var thumbnails = document.getElementById('pdThumbs');
        if (!thumbnails) {
            thumbnails = document.createElement('div');
            thumbnails.id = 'pdThumbs';
            thumbnails.className = 'pd-thumbs';
            document.querySelector('.pd-gallery').appendChild(thumbnails);
        }
        thumbnails.replaceChildren();
        gallery.forEach(function(url, index) {
            var thumb = document.createElement('button');
            thumb.type = 'button';
            thumb.className = 'pd-thumb' + (index === 0 ? ' active' : '');
            thumb.addEventListener('click', function() { goImg(index); });
            var thumbImage = document.createElement('img');
            thumbImage.src = url;
            thumbImage.alt = 'Photo ' + (index + 1);
            thumb.appendChild(thumbImage);
            thumbnails.appendChild(thumb);
        });

        var lightbox = document.getElementById('lightbox');
        if (!lightbox) return;
        var lightboxImage = document.getElementById('lbImg');
        if (lightboxImage) lightboxImage.src = gallery[0];
        var counter = document.getElementById('lbCounter');
        if (counter) counter.textContent = '1 / ' + gallery.length;
        var lightboxThumbs = document.getElementById('lbThumbs');
        if (!lightboxThumbs && gallery.length > 1) {
            lightboxThumbs = document.createElement('div');
            lightboxThumbs.id = 'lbThumbs';
            lightboxThumbs.className = 'lb-thumbs';
            lightbox.appendChild(lightboxThumbs);
        } else if (lightboxThumbs && gallery.length < 2) {
            lightboxThumbs.remove();
            lightboxThumbs = null;
        }
        if (lightboxThumbs) {
            lightboxThumbs.replaceChildren();
            gallery.forEach(function(url, index) {
                var thumb = document.createElement('button');
                thumb.type = 'button';
                thumb.className = 'lb-thumb' + (index === 0 ? ' active' : '');
                thumb.addEventListener('click', function() { goImg(index); });
                var thumbImage = document.createElement('img');
                thumbImage.src = url;
                thumbImage.alt = '';
                thumb.appendChild(thumbImage);
                lightboxThumbs.appendChild(thumb);
            });
        }
    }

    function updateSelectedOptionDescription(selected) {
        var description = document.getElementById('pdOptionDescription');
        if (!description) return;
        var details = selected.map(function(button) {
            var text = button.dataset.description || '';
            var selectedLabel = button.dataset.attributeName + ': ' + button.dataset.value;
            return text ? selectedLabel + ' · ' + text : selectedLabel;
        });
        description.textContent = details.join('  ·  ');
        description.hidden = details.length === 0;
    }

    function applySkuVariant(variant) {
        SELECTED_SKU_ID = variant ? Number(variant.id) : null;
        var optionImage = variant && Array.isArray(variant.options)
            ? variant.options.map(function(option) { return option.image; }).find(Boolean)
            : null;
        if (!optionImage) {
            var selectedButtons = Array.from(document.querySelectorAll('.pd-sku-option[aria-pressed="true"]'));
            optionImage = selectedButtons.map(function(button) { return button.dataset.image; }).find(Boolean);
        }
        setSelectedOptionImage(optionImage || null);
        updateSelectedOptionDescription(Array.from(document.querySelectorAll('.pd-sku-option[aria-pressed="true"]')));
        STOCK_MAX = variant ? Number(variant.stock) : (SKU_VARIANTS.length ? 0 : BASE_STOCK);
        var stockMessage = document.getElementById('pdStock');
        var addButton = document.getElementById('pdAdd');
        if (SKU_VARIANTS.length && !variant) {
            stockMessage.textContent = '● Choose a valid option combination';
            stockMessage.className = 'pd-stock out';
            if (addButton) addButton.disabled = true;
        } else if (SKU_VARIANTS.length && variant.stock <= 0) {
            stockMessage.textContent = '● This option is out of stock';
            stockMessage.className = 'pd-stock out';
            if (addButton) addButton.disabled = true;
        } else if (STOCK_MAX <= 0) {
            stockMessage.textContent = '● Out of stock';
            stockMessage.className = 'pd-stock out';
            if (addButton) addButton.disabled = true;
        } else {
            stockMessage.textContent = STOCK_MAX <= 5
                ? '● Only ' + STOCK_MAX + ' left in stock'
                : '● In stock — ready to ship';
            stockMessage.className = 'pd-stock ' + (STOCK_MAX <= 5 ? 'low' : 'ok');
            if (addButton) addButton.disabled = false;
        }
        var quantity = document.getElementById('pdQty');
        if (STOCK_MAX > 0 && Number(quantity.value) > STOCK_MAX) quantity.value = STOCK_MAX;
        if (STOCK_MAX <= 0) quantity.value = 1;
        updateAddBtn();
    }

    function syncSelectedSkuVariant() {
        if (!SKU_VARIANTS.length) return;
        var selections = {};
        var complete = true;
        document.querySelectorAll('.pd-variants').forEach(function(group) {
            var selected = group.querySelector('.pd-sku-option[aria-pressed="true"]');
            if (!selected) complete = false;
            else selections[selected.dataset.attributeId] = selected.dataset.value;
        });
        if (!complete) {
            applySkuVariant(null);
            document.getElementById('pdStock').textContent = '● Choose options to check availability';
            return;
        }
        var variant = SKU_VARIANTS.find(function(candidate) {
            return candidate.options.every(function(option) {
                return selections[String(option.attribute_id)] === option.value;
            });
        });
        applySkuVariant(variant || null);
    }

    function selectSkuVariantById(id) {
        if (!id || !SKU_VARIANTS.length) return false;
        var variant = SKU_VARIANTS.find(function(candidate) { return Number(candidate.id) === Number(id); });
        if (!variant) return false;
        variant.options.forEach(function(option) {
            var button = Array.from(document.querySelectorAll('.pd-sku-option[data-attribute-id="' + option.attribute_id + '"]'))
                .find(function(candidate) { return candidate.dataset.value === option.value; });
            if (button) {
                button.closest('.pd-variants').querySelectorAll('.pd-sku-option').forEach(function(candidate) {
                    candidate.setAttribute('aria-pressed', candidate === button ? 'true' : 'false');
                });
                var selectedLabel = document.getElementById('pdVariantValue' + option.attribute_id);
                if (selectedLabel) selectedLabel.textContent = option.value;
            }
        });
        applySkuVariant(variant);
        return true;
    }

    function selectProductVariant(button) {
        button.closest('.pd-variants').querySelectorAll('.pd-sku-option').forEach(function (choice) {
            choice.setAttribute('aria-pressed', choice === button ? 'true' : 'false');
        });
        var value = document.getElementById('pdVariantValue' + button.dataset.attributeId);
        if (value) value.textContent = button.dataset.value || '';
        if (SKU_VARIANTS.length) syncSelectedSkuVariant();
        else applySkuVariant(null);
    }

    /* ---------- 🔄 QTY SYNC: cart ↔ page stepper ---------- */
    var IN_CART = false;
    var IN_CART_VARIANT_ID = null;

    window.pdSyncQty = function (id, qty, variantId) {
        if (Number(id) !== Number(PRODUCT_ID)) return;
        if (SKU_VARIANTS.length) {
            if (!selectSkuVariantById(variantId)) return;
        } else if (variantId) {
            return;
        }
        IN_CART = true;
        IN_CART_VARIANT_ID = variantId ? Number(variantId) : null;
        var inp = document.getElementById('pdQty');
        if (inp && qty != null) {
            qty = parseInt(qty, 10) || 1;
            if (STOCK_MAX > 0 && qty > STOCK_MAX) qty = STOCK_MAX;
            if (qty < 1) qty = 1;
            inp.value = qty;
        }
        updateAddBtn();
    };

    window.pdSyncRemove = function (id, variantId) {
        if (Number(id) !== Number(PRODUCT_ID)) return;
        if ((variantId || null) !== (SELECTED_SKU_ID || null)) return;
        IN_CART = false;
        IN_CART_VARIANT_ID = null;
    };

    function syncCartQty(q) {
        if (!@json(auth()->check())) return;
        var fd = new FormData();
        fd.append('product_id', PRODUCT_ID);
        if (SELECTED_SKU_ID) fd.append('sku_variant_id', SELECTED_SKU_ID);
        fd.append('qty', q);
        fd.append('_token', csrfToken());
        fetch("{{ route('cart.update') }}", {
                method: 'POST', body: fd,
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) {
                return r.json().then(function (d) {
                    if (!r.ok) throw new Error(d.message || 'Could not update your bag.');
                    return d;
                });
            })
            .then(function (d) {
                if (d.status === 'success') {
                    if (typeof updateBadges === 'function') updateBadges(d.count || 0);
                    var dr = document.getElementById('drawerRoot');
                    if (dr && dr.classList.contains('open') && d.items && typeof renderCart === 'function') renderCart(d);
                } else if (/not in bag/i.test(d.message || '')) {
                    IN_CART = false;
                } else {
                    showToast(d.message || 'Could not update your bag.', 'error');
                }
            })
            .catch(function (error) { showToast(error.message || 'Could not update your bag.', 'error'); });
    }

    /* page load → if this product is already in the bag, show its qty immediately */
    (function () {
        if (!@json(auth()->check())) return;
        fetch("{{ route('cart.items') }}", { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) {
                return r.json().then(function (d) {
                    if (!r.ok) throw new Error(d.message || 'Could not load your bag.');
                    return d;
                });
            })
            .then(function (d) {
                if (!d.items) return;
                d.items.forEach(function (it) {
                    if (Number(it.id) === Number(PRODUCT_ID) && window.pdSyncQty) window.pdSyncQty(it.id, it.qty, it.variant_id);
                });
            })
            .catch(function (error) { showToast(error.message || 'Could not load your bag.', 'error'); });
    })();

    /* ---------- gallery + lightbox ---------- */
    function goImg(i) {
        if (!gallery.length) return;
        gIdx = (i + gallery.length) % gallery.length;
        var main = document.getElementById('pdMainImg');
        if (main) main.src = gallery[gIdx];
        document.querySelectorAll('.pd-thumb').forEach(function (t, idx) { t.classList.toggle('active', idx === gIdx); });
        var lbImg = document.getElementById('lbImg');
        if (lbImg) {
            lbImg.src = gallery[gIdx];
            lbImg.style.animation = 'none'; void lbImg.offsetWidth; lbImg.style.animation = '';
            var counter = document.getElementById('lbCounter');
            if (counter) counter.textContent = (gIdx + 1) + ' / ' + gallery.length;
            document.querySelectorAll('.lb-thumb').forEach(function (t, idx) { t.classList.toggle('active', idx === gIdx); });
        }
    }

    function openLightbox() {
        var lightbox = document.getElementById('lightbox');
        if (!gallery.length || !lightbox) return;
        lightbox.classList.add('open');
        document.body.style.overflow = 'hidden';
        goImg(gIdx);
    }

    function closeLightbox() {
        var lb = document.getElementById('lightbox');
        if (lb) lb.classList.remove('open');
        document.body.style.overflow = '';
    }

    var lbEl = document.getElementById('lightbox');
    if (lbEl) {
        lbEl.addEventListener('click', function (e) { if (e.target === this) closeLightbox(); });
    }

    document.addEventListener('keydown', function (e) {
        var lb = document.getElementById('lightbox');
        var lbOpen = lb && lb.classList.contains('open');
        if (e.key === 'Escape' && lbOpen) closeLightbox();
        if (lbOpen && e.key === 'ArrowLeft') goImg(gIdx - 1);
        if (lbOpen && e.key === 'ArrowRight') goImg(gIdx + 1);
    });

    /* ---------- quantity + add ---------- */
    function qtyStep(d) {
        var inp = document.getElementById('pdQty');
        var v = (parseInt(inp.value, 10) || 1) + d;
        if (v < 1) v = 1;
        if (STOCK_MAX > 0 && v > STOCK_MAX) v = STOCK_MAX;
        inp.value = v;
        updateAddBtn();
        if (IN_CART && IN_CART_VARIANT_ID === (SELECTED_SKU_ID || null)) syncCartQty(v); /* 🔄 stepper drives the selected cart line */
    }

    function updateAddBtn() {
        var btn = document.getElementById('pdAdd');
        if (!btn) return;
        if (SKU_VARIANTS.length && !SELECTED_SKU_ID) {
            btn.disabled = true;
            return;
        }
        if (STOCK_MAX <= 0) {
            btn.disabled = true;
            return;
        }
        btn.disabled = false;
        var q = parseInt(document.getElementById('pdQty').value, 10) || 1;
        document.getElementById('pdAddTotal').textContent = '£' + (UNIT_PRICE * q).toFixed(2);
    }

    function addFromDetail(ev) {
        var q = parseInt(document.getElementById('pdQty').value, 10) || 1;
        if (SKU_VARIANTS.length && !SELECTED_SKU_ID) {
            showToast('Choose a valid product option first.', 'error');
            return;
        }
        addToBag(PRODUCT_ID, q, ev, { open: true, skuVariantId: SELECTED_SKU_ID });
    }

    if (SKU_VARIANTS.length) applySkuVariant(null);
    else applySkuVariant({ stock: BASE_STOCK });

    /* ---------- wishlist toggle ---------- */
    function toggleWishDetail(ev) {
        if (ev) ev.preventDefault();
        var btn = document.getElementById('pdWish');
        if (btn.disabled) return;
        btn.disabled = true;
        btn.classList.add('busy');
        var fd = new FormData();
        fd.append('product_id', PRODUCT_ID);
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
                btn.classList.remove('busy');
                if (d.status === 'guest') { showToast(d.message, 'error'); return; }
                if (d.status !== 'added' && d.status !== 'removed') { showToast(d.message || 'Could not update your wishlist', 'error'); return; }
                btn.classList.toggle('liked', d.status === 'added');
                showToast(d.message, 'success');
            })
            .catch(function (err) { showToast(err.message || 'Something went wrong', 'error'); })
            .finally(function () { btn.disabled = false; btn.classList.remove('busy'); });
    }

    /* ---------- tabs ---------- */
    function pdTab(name, btn) {
        document.querySelectorAll('.pd-tab').forEach(function (t) { t.classList.remove('active'); });
        document.querySelectorAll('.pd-pane').forEach(function (p) { p.classList.remove('active'); });
        btn.classList.add('active');
        document.getElementById(name === 'reviews' ? 'reviews' : 'pane-' + name).classList.add('active');
    }

    function jumpReviews() {
        pdTab('reviews', document.getElementById('tabBtnReviews'));
        document.getElementById('pdTabsWrap').scrollIntoView({ behavior: 'smooth' });
    }

    /* ---------- review form (LOGIN REQUIRED — admin approval) ---------- */
    var selRating = 0;

    function pickStar(n) {
        selRating = n;
        document.querySelectorAll('#starInput button').forEach(function (b, i) { b.classList.toggle('on', i < n); });
    }

    function submitReview(e) {
        e.preventDefault();
        var comment = document.getElementById('rvComment').value.trim();
        if (!selRating) { showToast('Please select a star rating', 'error'); return; }
        if (!comment) { showToast('Please write your review', 'error'); return; }

        var fd = new FormData();
        fd.append('rating', selRating);
        fd.append('comment', comment);
        fd.append('_token', csrfToken());

        var btn = document.getElementById('rvSubmit');
        btn.disabled = true;

        fetch("{{ route('reviews.store', $product->id) }}", {
                method: 'POST', body: fd,
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) { return r.json().catch(function () { return { status: 'error', message: 'Server error' }; }); })
            .then(function (d) {
                btn.disabled = false;
                if (d.status === 'guest') {
                    showToast(d.message || 'Please login to review', 'error');
                    setTimeout(function () {
                        var destination = d.redirect || '/login-redirect?redirect=' + encodeURIComponent(location.pathname + location.search + '#reviews');
                        if (typeof window.ajaxNavigate === 'function') window.ajaxNavigate(destination);
                        else window.location.assign(destination);
                    }, 900);
                    return;
                }
                if (d.status !== 'success') { showToast(d.message || 'Could not post review', 'error'); return; }

                /* pending review list mein ABHI insert NAHI hoga — admin approve karega */
                document.getElementById('rvComment').value = '';
                selRating = 0; pickStar(0);
            })
            .catch(function () { btn.disabled = false; showToast('Something went wrong', 'error'); });
    }

    /* #reviews deep links open the review tab after both regular and AJAX navigation. */
    (function () {
        if (location.hash === '#reviews') {
            var tab = document.getElementById('tabBtnReviews');
            if (tab) pdTab('reviews', tab);
        }
    })();
</script>

</body>

</html>