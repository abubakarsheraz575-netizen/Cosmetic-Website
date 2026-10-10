@extends('admin.layouts.ecommerce')

@section('title', 'Inventory')
@section('content')
    <style>
        @keyframes inventory-enter{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
        .inventory-page{display:grid;gap:18px;min-width:0;max-width:100%}
        .inventory-hero{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:27px 30px;border:1px solid #d8e5ef;border-radius:22px;background:radial-gradient(circle at 91% 4%,#dceeff 0,transparent 30%),linear-gradient(125deg,#fff,#f2f7fc);box-shadow:0 14px 34px rgba(36,87,137,.07);animation:inventory-enter .5s ease-out both}
        .inventory-hero-copy{max-width:680px}.inventory-eyebrow{display:inline-flex;align-items:center;gap:7px;margin-bottom:9px;color:#3976a6;font-size:10px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}
        .inventory-hero h1{display:flex;align-items:center;gap:11px;margin:0;color:#17344c;font-size:clamp(22px,2.5vw,29px);letter-spacing:-.5px}.inventory-hero h1 i{display:grid;place-items:center;width:44px;height:44px;border-radius:14px;background:linear-gradient(135deg,#235d89,#4c9ac4);color:#fff;font-size:22px;box-shadow:0 8px 18px rgba(35,93,137,.2)}
        .inventory-hero p{max-width:640px;margin:9px 0 0;color:#62778b;font-size:12px;line-height:1.7}
        .inventory-hero-badge{display:flex;align-items:center;gap:8px;flex:none;padding:10px 13px;border:1px solid #d5e5ef;border-radius:13px;background:#ffffffdc;color:#28668f;font-size:10px;font-weight:800;white-space:nowrap}.inventory-hero-badge i{font-size:16px}
        .inventory-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;animation:inventory-enter .5s .05s ease-out both}
        .inventory-stat{display:flex;align-items:center;gap:11px;min-width:0;padding:14px 15px;border:1px solid var(--border);border-radius:15px;background:var(--card);box-shadow:0 7px 20px rgba(16,24,40,.035)}
        .inventory-stat-icon{display:grid;place-items:center;align-content:center;justify-content:center;flex:none;width:39px;height:39px;border-radius:12px;background:#e5f1fa;color:#3979a8;font-size:22px;line-height:1}.inventory-stat-icon i{display:block;width:1em;height:1em;line-height:1}.inventory-stat-icon.ready{background:#e4f7ee;color:#188254}.inventory-stat-icon.low{background:#fff4da;color:#b87b10}.inventory-stat-icon.out{background:#ffe9e9;color:#bb3c3c}
        .inventory-stat strong{display:block;color:var(--text);font-size:19px;line-height:1.15}.inventory-stat span{display:block;margin-top:4px;color:var(--muted);font-size:9px;font-weight:800;letter-spacing:.045em;text-transform:uppercase}
        .inventory-note{display:flex;align-items:flex-start;gap:10px;padding:12px 15px;border:1px solid #dce8f1;border-radius:13px;background:linear-gradient(105deg,#f7fbff,#fff);color:#536a80;font-size:10px;line-height:1.6;animation:inventory-enter .5s .08s ease-out both}.inventory-note i{flex:none;margin-top:1px;color:#4588b3;font-size:16px}
        .inventory-card{min-width:0;padding:0;overflow:hidden;animation:inventory-enter .5s .12s ease-out both}.inventory-card-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:17px 19px;border-bottom:1px solid var(--border);background:linear-gradient(180deg,var(--card),var(--bg))}
        .inventory-card-title{display:flex;align-items:flex-start;gap:10px;min-width:0}.inventory-card-title-icon{display:grid;place-items:center;flex:none;width:36px;height:36px;border-radius:11px;background:#e5f1fa;color:#3979a8;font-size:18px}.inventory-card-title h2{margin:0;color:var(--text);font-size:14px}.inventory-card-title p{margin:4px 0 0;color:var(--muted);font-size:10px;line-height:1.5}
        .inventory-toolbar-actions{display:flex;align-items:center;gap:8px;flex:none}.inventory-toolbar-actions select{min-height:37px;max-width:190px;padding:8px 10px;border:1px solid var(--border);border-radius:9px;background:var(--card);color:var(--text);font:inherit;font-size:11px}
        .inventory-table-wrap{width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch}.inventory-table{width:100%;min-width:790px;border-collapse:separate;border-spacing:0}.inventory-table th{padding:12px;background:var(--bg);font-size:9px}.inventory-table td{padding:13px 11px}.inventory-table tbody tr{transition:background .18s}.inventory-table tbody tr:hover{background:rgba(62,197,224,.04)}
        .inventory-table .product-cell{min-width:210px}.inventory-category{display:inline-flex;max-width:160px;padding:5px 8px;border:1px solid var(--border);border-radius:999px;background:var(--bg);color:var(--muted);font-size:10px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .stock-input{width:100%;max-width:140px;padding:9px 10px;border:1px solid var(--border);border-radius:9px;background:var(--card);color:var(--text);font:inherit;font-size:12px;transition:border-color .18s,box-shadow .18s}.stock-input:focus{outline:0;border-color:#5ca4c9;box-shadow:0 0 0 3px rgba(76,154,196,.13)}
        .stock-field{display:grid;gap:5px;min-width:0}.stock-field span{color:var(--muted);font-size:9px;font-weight:700;line-height:1.45}.stock-variant-list{display:grid;gap:8px;min-width:0}.stock-total{display:block;margin-bottom:4px;color:var(--text);font-size:12px}.stock-total-note{display:block;margin-bottom:8px;color:var(--muted);font-size:9px;line-height:1.45}
        .stock-status{display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border-radius:999px;font-size:10px;font-weight:800;white-space:nowrap}.stock-status.in{border:1px solid #ccebd9;background:#ecf9f1;color:#177449}.stock-status.low{border:1px solid #f1dfb6;background:#fff8e8;color:#986409}.stock-status.out{border:1px solid #f1cccc;background:#fff1f1;color:#ae3030}.stock-status i{font-size:13px}
        .inventory-card-foot{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:13px 19px;border-top:1px solid var(--border);background:var(--bg)}.inventory-card-foot p{margin:0;color:var(--muted);font-size:10px}.inventory-card-foot .actions{justify-content:flex-end}
        .inventory-empty{padding:35px 18px;text-align:center;color:var(--muted)}.inventory-empty i{display:block;margin-bottom:9px;color:#5796bd;font-size:28px}.inventory-empty strong{display:block;margin-bottom:4px;color:var(--text);font-size:13px}
        @media(max-width:900px){.inventory-hero{align-items:flex-start;flex-direction:column}.inventory-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.inventory-card-head{align-items:flex-start;flex-direction:column}.inventory-toolbar-actions{width:100%;justify-content:space-between}.inventory-toolbar-actions select{flex:1;max-width:none}}
        @media(max-width:680px){.inventory-hero{padding:20px}.inventory-hero-badge{white-space:normal}.inventory-stats{gap:9px}.inventory-stat{padding:11px 12px}.inventory-stat-icon{width:34px;height:34px;font-size:19px}.inventory-stat strong{font-size:16px}.inventory-stat span{font-size:8px}.inventory-card-head{padding:16px}.inventory-toolbar-actions{align-items:stretch;flex-direction:column}.inventory-toolbar-actions select,.inventory-toolbar-actions button{width:100%;max-width:none}.inventory-table-wrap{overflow:visible}.inventory-table{display:block;min-width:0;width:100%}.inventory-table thead{display:none}.inventory-table tbody{display:grid;gap:11px;padding:12px}.inventory-table tbody tr{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));min-width:0;padding:9px;border:1px solid var(--border);border-radius:13px;background:var(--card);box-shadow:0 4px 12px rgba(16,24,40,.03)}.inventory-table td{display:grid;align-content:start;gap:6px;min-width:0;padding:8px 6px;border:0;font-size:11px}.inventory-table td::before{content:attr(data-label);color:var(--muted);font-size:9px;font-weight:800;letter-spacing:.05em;text-transform:uppercase}.inventory-table td:first-child{grid-column:1/-1;display:flex;padding-bottom:10px;border-bottom:1px solid var(--border)}.inventory-table td:first-child::before{display:none}.inventory-table td:nth-child(2){display:flex;align-items:center}.inventory-table td:nth-child(2)::before{content:'Category ·'}.inventory-table td:nth-child(3){grid-column:1/-1}.inventory-table .product-cell{min-width:0}.inventory-table .stock-input{min-width:0;max-width:none}.inventory-table .stock-variant-list{grid-template-columns:repeat(2,minmax(0,1fr))}.inventory-table .stock-field{padding:8px;border:1px solid var(--border);border-radius:9px;background:var(--bg)}.inventory-table .stock-status{width:max-content;max-width:100%;white-space:normal}.inventory-card-foot{align-items:stretch;flex-direction:column;padding:13px 16px}.inventory-card-foot .actions,.inventory-card-foot .button{width:100%}}
        @media(max-width:390px){.inventory-stats{grid-template-columns:1fr 1fr}.inventory-table tbody tr{grid-template-columns:1fr}.inventory-table td:first-child,.inventory-table td:nth-child(2){grid-column:auto}.inventory-table .stock-variant-list{grid-template-columns:1fr}}
        @media(prefers-reduced-motion:reduce){.inventory-hero,.inventory-stats,.inventory-note,.inventory-card{animation:none}.inventory-table tbody tr,.stock-input{transition:none}}
    </style>
    @php
        $lowStockCount = $products->whereBetween('stock', [1, 5])->count();
        $outOfStockCount = $products->where('stock', 0)->count();
        $healthyStockCount = $products->filter(fn ($product) => (int) $product->stock > 5)->count();
    @endphp
    <div class="inventory-page">
        <section class="inventory-hero">
            <div class="inventory-hero-copy"><span class="inventory-eyebrow"><i class="ri-stack-line"></i> Stock health monitor</span><h1><i class="ri-archive-line"></i> Inventory control</h1><p>Keep product availability accurate across your catalog. Update single-item stock or manage each sellable option, then save quantities together with a clear view of stock health.</p></div>
            <span class="inventory-hero-badge"><i class="ri-refresh-line"></i> Variant totals recalculate automatically</span>
        </section>
        <section class="inventory-stats" aria-label="Inventory health summary">
            <article class="inventory-stat"><span class="inventory-stat-icon"><i class="ri-box-3-line"></i></span><div><strong>{{ $products->count() }}</strong><span>Products tracked</span></div></article>
            <article class="inventory-stat"><span class="inventory-stat-icon ready"><i class="ri-checkbox-circle-line"></i></span><div><strong>{{ $healthyStockCount }}</strong><span>Healthy · 6+ units</span></div></article>
            <article class="inventory-stat"><span class="inventory-stat-icon low"><i class="ri-error-warning-line"></i></span><div><strong>{{ $lowStockCount }}</strong><span>Low · 1–5 units</span><span class="sr-only">{{ $lowStockCount }} low stock</span></div></article>
            <article class="inventory-stat"><span class="inventory-stat-icon out"><i class="ri-close-circle-line"></i></span><div><strong>{{ $outOfStockCount }}</strong><span>Out of stock</span><span class="sr-only">{{ $outOfStockCount }} out of stock</span></div></article>
        </section>
        <div class="inventory-note" role="note"><i class="ri-information-line"></i><span>Products with sellable variants are managed per option. Their total stock is recalculated from the option quantities after saving; other products use one direct stock value.</span></div>
        <form method="POST" action="{{ route('admin.ecommerce.inventory.update') }}" class="card inventory-card">
            @csrf @method('PATCH')
            <div class="inventory-card-head">
                <div class="inventory-card-title"><span class="inventory-card-title-icon"><i class="ri-list-settings-line"></i></span><div><h2>Stock levels</h2><p>Filter by stock health, adjust quantities, and save the changes together.</p></div></div>
                <div class="inventory-toolbar-actions"><select data-table-status="inventoryTable" aria-label="Filter inventory by stock state"><option value="">All stock states</option><option value="out-of-stock">Out of stock</option><option value="low-stock">Low stock (1-5)</option><option value="in-stock">In stock</option></select><button class="button primary" type="submit"><i class="ri-save-line"></i> Save inventory</button></div>
            </div>
            <div class="inventory-table-wrap"><table id="inventoryTable" class="inventory-table">
                <thead><tr><th>Product</th><th>Category</th><th>Stock quantity</th><th>Stock state</th></tr></thead>
                <tbody>
                @forelse($products as $product)
                    @php($stockState = (int) $product->stock === 0 ? 'out-of-stock' : ((int) $product->stock <= 5 ? 'low-stock' : 'in-stock'))
                    <tr data-status="{{ $stockState }}">
                        <td data-label="Product"><div class="product-cell"><span class="product-thumb">@if($product->images->first())<img src="{{ asset('storage/'.$product->images->first()->path) }}" alt="{{ $product->name }}" loading="lazy">@else{{ $product->emoji ?: '✦' }}@endif</span><span><strong>{{ $product->name }}</strong><small>#{{ $product->id }}</small></span></div></td>
                        <td data-label="Category"><span class="inventory-category">{{ $product->category?->name ?? 'Uncategorized' }}</span></td>
                        <td data-label="Stock quantity">
                            @if($product->skuVariants->isNotEmpty())
                                <strong class="stock-total">{{ $product->stock }} total units</strong><small class="stock-total-note">Set quantities for each sellable option below.</small>
                                <div class="stock-variant-list">
                                    @foreach($product->skuVariants as $skuVariant)
                                        <label class="stock-field"><span>{{ $skuVariant->options->map(fn ($option) => $option->attribute?->name.': '.$option->value)->implode(' · ') }} · {{ $skuVariant->sku }}</span><input class="stock-input" required type="number" min="0" step="1" inputmode="numeric" aria-label="Stock for {{ $product->name }} {{ $skuVariant->sku }}" name="sku_variants[{{ $skuVariant->id }}][stock]" value="{{ old("sku_variants.{$skuVariant->id}.stock", $skuVariant->stock) }}"></label>
                                    @endforeach
                                </div>
                            @else
                                <label class="field"><span class="sr-only">Stock quantity for {{ $product->name }}</span><input class="stock-input" required type="number" min="0" step="1" inputmode="numeric" aria-label="Stock quantity for {{ $product->name }}" name="products[{{ $product->id }}][stock]" value="{{ old("products.{$product->id}.stock", $product->stock) }}"></label>
                            @endif
                        </td>
                        <td data-label="Stock state"><span class="stock-status {{ $stockState === 'out-of-stock' ? 'out' : ($stockState === 'low-stock' ? 'low' : 'in') }}"><i class="{{ $stockState === 'out-of-stock' ? 'ri-close-circle-line' : ($stockState === 'low-stock' ? 'ri-error-warning-line' : 'ri-checkbox-circle-line') }}"></i>{{ $stockState === 'out-of-stock' ? 'Out of stock' : ($stockState === 'low-stock' ? 'Low stock' : 'In stock') }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="inventory-empty"><i class="ri-archive-line"></i><strong>No products in inventory yet</strong>Add products to the catalog to begin tracking stock.</td></tr>
                @endforelse
                </tbody>
            </table></div>
            @if($products->isNotEmpty())<div class="inventory-card-foot"><p>Enter whole units only. A zero quantity marks an item as out of stock.</p><div class="actions"><button class="button primary" type="submit"><i class="ri-save-line"></i> Save inventory</button></div></div>@endif
        </form>
    </div>
@endsection
