@extends('admin.layouts.ecommerce')

@section('title', 'Product prices')
@section('content')
    <style>
        @keyframes pricing-enter{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
        .pricing-page{display:grid;gap:18px;min-width:0;max-width:100%}
        .pricing-hero{position:relative;display:flex;align-items:center;justify-content:space-between;gap:22px;overflow:hidden;padding:27px 30px;border:1px solid #cfe5e9;border-radius:22px;background:radial-gradient(circle at 92% 2%,#d9f7f2 0,transparent 30%),linear-gradient(125deg,#fff,#eff8fb);box-shadow:0 14px 34px rgba(14,90,117,.07);animation:pricing-enter .5s ease-out both}
        .pricing-hero-copy{max-width:680px}.pricing-eyebrow{display:inline-flex;align-items:center;gap:7px;margin-bottom:9px;color:#087f84;font-size:10px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}
        .pricing-hero h1{display:flex;align-items:center;gap:11px;margin:0;color:#123744;font-size:clamp(22px,2.5vw,29px);letter-spacing:-.5px}.pricing-hero h1 i{display:grid;place-items:center;width:44px;height:44px;border-radius:14px;background:linear-gradient(135deg,#0e5a75,#1b9c9a);color:#fff;font-size:22px;box-shadow:0 8px 18px #0e5a7529}
        .pricing-hero p{max-width:640px;margin:9px 0 0;color:#617785;font-size:12px;line-height:1.7}
        .pricing-hero-badge{display:flex;align-items:center;gap:9px;flex:none;padding:11px 14px;border:1px solid #d6e9e9;border-radius:14px;background:#ffffffd9;color:#0d6672;font-size:11px;font-weight:800;white-space:nowrap}.pricing-hero-badge i{font-size:17px}
        .pricing-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;animation:pricing-enter .5s .05s ease-out both}
        .pricing-stat{display:flex;align-items:center;gap:12px;min-width:0;padding:15px 17px;border:1px solid var(--border);border-radius:15px;background:var(--card);box-shadow:0 7px 20px rgba(16,24,40,.035)}
        .pricing-stat-icon{display:grid;place-items:center;align-content:center;justify-content:center;flex:none;width:40px;height:40px;border-radius:12px;background:#e1f6f3;color:#0c8581;font-size:22px;line-height:1}.pricing-stat-icon i{display:block;width:1em;height:1em;line-height:1}.pricing-stat-icon.purple{background:#eee9ff;color:#7451bc}.pricing-stat-icon.gold{background:#fff3dc;color:#bd7916}
        .pricing-stat strong{display:block;color:var(--text);font-size:20px;line-height:1.15}.pricing-stat span{display:block;margin-top:4px;color:var(--muted);font-size:10px;font-weight:700;letter-spacing:.05em;text-transform:uppercase}
        .pricing-campaign{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:18px 20px;border:1px solid #dce9ef;border-radius:17px;background:linear-gradient(110deg,#fff,#f5f9fc);box-shadow:0 7px 22px rgba(16,24,40,.035);animation:pricing-enter .5s .1s ease-out both}
        .pricing-campaign-copy{display:flex;align-items:flex-start;gap:12px;min-width:0}.pricing-campaign-icon{display:grid;place-items:center;flex:none;width:42px;height:42px;border-radius:13px;background:#e7f3ff;color:#3575aa;font-size:20px}
        .pricing-campaign h2{margin:0;color:var(--text);font-size:14px}.pricing-campaign p{max-width:740px;margin:5px 0 0;color:var(--muted);font-size:11px;line-height:1.6}.pricing-campaign-action{flex:none}
        .pricing-card{min-width:0;padding:0;overflow:hidden;animation:pricing-enter .5s .14s ease-out both}.pricing-card-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:18px 20px;border-bottom:1px solid var(--border);background:linear-gradient(180deg,var(--card),var(--bg))}
        .pricing-card-title{display:flex;align-items:flex-start;gap:10px;min-width:0}.pricing-card-title-icon{display:grid;place-items:center;flex:none;width:36px;height:36px;border-radius:11px;background:#e1f6f6;color:#087b83;font-size:18px}.pricing-card-title h2{margin:0;color:var(--text);font-size:14px}.pricing-card-title p{margin:4px 0 0;color:var(--muted);font-size:10px;line-height:1.5}
        .pricing-toolbar-actions{display:flex;align-items:center;gap:8px;flex:none}.pricing-toolbar-actions select{min-height:37px;max-width:210px;padding:8px 10px;border:1px solid var(--border);border-radius:9px;background:var(--card);color:var(--text);font:inherit;font-size:11px}
        .pricing-table-wrap{width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch}.pricing-table{width:100%;min-width:1100px;border-collapse:separate;border-spacing:0}.pricing-table th{padding:12px 11px;background:var(--bg);font-size:9px}.pricing-table td{padding:12px 10px}.pricing-table tbody tr{transition:background .18s}.pricing-table tbody tr:hover{background:rgba(62,197,224,.045)}
        .pricing-table .product-cell{min-width:200px}.pricing-table .price-input{min-width:95px;width:100%;max-width:145px;padding:9px 10px;border:1px solid var(--border);border-radius:9px;background:var(--card);color:var(--text);font:inherit;font-size:11px;transition:border-color .18s,box-shadow .18s}.pricing-table .price-input:focus{outline:0;border-color:#45b9c7;box-shadow:0 0 0 3px rgba(62,197,224,.13)}.pricing-table .date-input{min-width:160px;max-width:190px}
        .pricing-category{display:inline-flex;max-width:160px;padding:5px 8px;border:1px solid var(--border);border-radius:999px;background:var(--bg);color:var(--muted);font-size:10px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .price-current{display:block;color:var(--text);font-size:13px;font-weight:800;white-space:nowrap}.price-current.is-sale{color:#07846e}.price-state{display:inline-flex;align-items:center;gap:4px;margin-top:4px;color:var(--muted);font-size:9px;font-weight:700;white-space:nowrap}.price-state.is-sale{color:#07846e}
        .pricing-card-foot{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:13px 20px;border-top:1px solid var(--border);background:var(--bg)}.pricing-card-foot p{margin:0;color:var(--muted);font-size:10px}.pricing-card-foot .actions{justify-content:flex-end}
        .pricing-empty{padding:34px 18px;text-align:center;color:var(--muted)}.pricing-empty i{display:block;margin-bottom:9px;color:#42aeb5;font-size:28px}.pricing-empty strong{display:block;margin-bottom:4px;color:var(--text);font-size:13px}
        @media(max-width:900px){.pricing-hero{align-items:flex-start;flex-direction:column}.pricing-stats{grid-template-columns:repeat(3,minmax(0,1fr))}.pricing-card-head{align-items:flex-start;flex-direction:column}.pricing-toolbar-actions{width:100%;justify-content:space-between}.pricing-toolbar-actions select{flex:1;max-width:none}}
        @media(max-width:680px){.pricing-hero{padding:20px}.pricing-hero-badge{white-space:normal}.pricing-stats{grid-template-columns:1fr}.pricing-stat{padding:12px 14px}.pricing-campaign{align-items:stretch;flex-direction:column}.pricing-campaign-action,.pricing-campaign-action button{width:100%}.pricing-card-head{padding:16px}.pricing-toolbar-actions{align-items:stretch;flex-direction:column}.pricing-toolbar-actions select,.pricing-toolbar-actions button{width:100%;max-width:none}.pricing-table-wrap{overflow:visible}.pricing-table{display:block;min-width:0;width:100%}.pricing-table thead{display:none}.pricing-table tbody{display:grid;gap:11px;padding:12px}.pricing-table tbody tr{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));min-width:0;padding:9px;border:1px solid var(--border);border-radius:13px;background:var(--card);box-shadow:0 4px 12px rgba(16,24,40,.03)}.pricing-table td{display:grid;align-content:start;gap:5px;min-width:0;padding:8px 6px;border:0;font-size:11px}.pricing-table td::before{content:attr(data-label);color:var(--muted);font-size:9px;font-weight:800;letter-spacing:.05em;text-transform:uppercase}.pricing-table td:first-child{grid-column:1/-1;display:flex;padding-bottom:10px;border-bottom:1px solid var(--border)}.pricing-table td:first-child::before{display:none}.pricing-table td:nth-child(2){grid-column:1/-1;display:flex;align-items:center}.pricing-table td:nth-child(2)::before{content:'Category ·'}.pricing-table .product-cell{min-width:0}.pricing-table .price-input{min-width:0;max-width:none;font-size:10px}.pricing-table .date-input{min-width:0;width:100%;font-size:9px}.pricing-card-foot{align-items:stretch;flex-direction:column;padding:13px 16px}.pricing-card-foot .actions,.pricing-card-foot .button{width:100%}}
        @media(max-width:390px){.pricing-table tbody tr{grid-template-columns:1fr}.pricing-table td:first-child,.pricing-table td:nth-child(2){grid-column:auto}}
        @media(prefers-reduced-motion:reduce){.pricing-hero,.pricing-stats,.pricing-campaign,.pricing-card{animation:none}.pricing-table tbody tr,.pricing-table .price-input{transition:none}}
    </style>
    @php
        $pricedCount = $products->filter(fn ($product) => $product->sale_price !== null)->count();
        $eligibleCount = $products->filter(fn ($product) => $product->is_active && $product->review_status === 'approved' && (float) $product->price > 0 && ($product->sale_price === null || ($product->sale_ends_at && $product->sale_ends_at->isPast())))->count();
    @endphp
    <div class="pricing-page">
        <section class="pricing-hero">
            <div class="pricing-hero-copy"><span class="pricing-eyebrow"><i class="ri-sparkling-2-line"></i> Merchandising control centre</span><h1><i class="ri-price-tag-3-line"></i> Product pricing</h1><p>Keep your catalog competitive with clear regular prices and carefully scheduled promotions. Review the live price, edit multiple products, and publish the updates together.</p></div>
            <span class="pricing-hero-badge"><i class="ri-shield-check-line"></i> Bulk updates are saved together</span>
        </section>
        <section class="pricing-stats" aria-label="Pricing overview">
            <article class="pricing-stat"><span class="pricing-stat-icon"><i class="ri-shopping-bag-3-line"></i></span><div><strong>{{ $products->count() }}</strong><span>Catalog products</span></div></article>
            <article class="pricing-stat"><span class="pricing-stat-icon purple"><i class="ri-percent-line"></i></span><div><strong>{{ $pricedCount }}</strong><span>With sale pricing</span></div></article>
            <article class="pricing-stat"><span class="pricing-stat-icon gold"><i class="ri-flashlight-line"></i></span><div><strong>{{ $eligibleCount }}</strong><span>Campaign eligible</span></div></article>
        </section>
        <form method="POST" action="{{ route('admin.ecommerce.prices.random-sales') }}" class="pricing-campaign" onsubmit="return confirm('Start random 5–30% sales lasting 1–7 days for all eligible published products? Active or scheduled sales will not be changed.')">
            @csrf
            <div class="pricing-campaign-copy"><span class="pricing-campaign-icon"><i class="ri-timer-flash-line"></i></span><div><h2>Timed sale campaign</h2><p>Launch randomized 5–30% discounts for eligible published products. Each promotion runs 1–7 days, and active or upcoming sales are protected from changes.</p></div></div>
            <div class="pricing-campaign-action"><button class="button primary" type="submit"><i class="ri-sparkling-2-line"></i> Start random sales</button></div>
        </form>
        <form method="POST" action="{{ route('admin.ecommerce.prices.update') }}" class="card pricing-card">
            @csrf @method('PATCH')
            <div class="pricing-card-head">
                <div class="pricing-card-title"><span class="pricing-card-title-icon"><i class="ri-list-settings-line"></i></span><div><h2>Catalog pricing</h2><p>Update regular prices and time-limited offers, then save your catalog in one step.</p></div></div>
                <div class="pricing-toolbar-actions"><select data-table-status="pricesTable" aria-label="Filter products by category"><option value="">All categories</option>@foreach($products->pluck('category')->filter()->unique('id')->sortBy('name') as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach<option value="none">Uncategorized</option></select><button class="button primary" type="submit"><i class="ri-save-line"></i> Save all prices</button></div>
            </div>
            <div class="pricing-table-wrap"><table id="pricesTable" class="pricing-table">
                <thead><tr><th>Product</th><th>Category</th><th>Regular price</th><th>Sale price</th><th>Sale starts</th><th>Sale ends</th><th>Current price</th></tr></thead>
                <tbody>
                @forelse($products as $product)
                    @php
                        $saleState = $product->sale_price === null ? 'No sale' : ($product->sale_starts_at?->isFuture() ? 'Scheduled' : ($product->sale_ends_at?->isPast() ? 'Ended' : 'On sale'));
                        $currentIsSale = $product->sale_price !== null && $product->sale_starts_at && $product->sale_ends_at && $product->sale_starts_at->isPast() && $product->sale_ends_at->isFuture();
                    @endphp
                    <tr data-status="{{ $product->category_id ?: 'none' }}">
                        <td data-label="Product"><div class="product-cell"><span class="product-thumb">@if($product->images->first())<img src="{{ asset('storage/'.$product->images->first()->path) }}" alt="{{ $product->name }}" loading="lazy">@else{{ $product->emoji ?: '✦' }}@endif</span><span><strong>{{ $product->name }}</strong><small>#{{ $product->id }}</small></span></div></td>
                        <td data-label="Category"><span class="pricing-category">{{ $product->category?->name ?? 'Uncategorized' }}</span></td>
                        <td data-label="Regular price"><label class="field"><span class="sr-only">Regular price for {{ $product->name }}</span><input class="price-input" required type="number" min="0" max="99999999.99" step="0.01" name="products[{{ $product->id }}][price]" value="{{ old("products.{$product->id}.price", $product->price) }}" inputmode="decimal"></label></td>
                        <td data-label="Sale price"><label class="field"><span class="sr-only">Sale price for {{ $product->name }}</span><input class="price-input" type="number" min="0" max="99999999.99" step="0.01" name="products[{{ $product->id }}][sale_price]" value="{{ old("products.{$product->id}.sale_price", $product->sale_price) }}" placeholder="—" inputmode="decimal"></label></td>
                        <td data-label="Sale starts"><label class="field"><span class="sr-only">Sale start for {{ $product->name }}</span><input class="price-input date-input" type="datetime-local" name="products[{{ $product->id }}][sale_starts_at]" value="{{ old("products.{$product->id}.sale_starts_at", $product->sale_starts_at?->format('Y-m-d\TH:i')) }}"></label></td>
                        <td data-label="Sale ends"><label class="field"><span class="sr-only">Sale end for {{ $product->name }}</span><input class="price-input date-input" type="datetime-local" name="products[{{ $product->id }}][sale_ends_at]" value="{{ old("products.{$product->id}.sale_ends_at", $product->sale_ends_at?->format('Y-m-d\TH:i')) }}"></label></td>
                        <td data-label="Current price"><span class="price-current {{ $currentIsSale ? 'is-sale' : '' }}">£{{ number_format((float) $product->final_price, 2) }}</span><span class="price-state {{ $currentIsSale ? 'is-sale' : '' }}"><i class="{{ $currentIsSale ? 'ri-price-tag-3-line' : 'ri-time-line' }}"></i>{{ $saleState }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="pricing-empty"><i class="ri-shopping-bag-3-line"></i><strong>No products to price yet</strong>Add products to your catalog to start managing prices and promotions.</td></tr>
                @endforelse
                </tbody>
            </table></div>
            @if($products->isNotEmpty())<div class="pricing-card-foot"><p>Sale prices must be lower than regular prices; both schedule dates are required for an offer.</p><div class="actions"><button class="button primary" type="submit"><i class="ri-save-line"></i> Save all prices</button></div></div>@endif
        </form>
    </div>
@endsection
