@extends('admin.layouts.ecommerce')

@section('title', 'Create a label set')
@section('content')
    <style>
        @keyframes label-create-enter{from{opacity:0;transform:translateY(11px)}to{opacity:1;transform:translateY(0)}}
        .label-create-page{display:grid;gap:18px;min-width:0}
        .label-create-hero{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:25px 28px;border:1px solid #f0dfca;border-radius:21px;background:radial-gradient(circle at 92% 5%,#fff0d9 0,transparent 31%),linear-gradient(125deg,#fff,#fff8f0);box-shadow:0 14px 34px rgba(167,106,30,.07);animation:label-create-enter .45s ease-out both}
        .label-create-hero h1{display:flex;align-items:center;gap:11px;margin:0;color:#41301b;font-size:clamp(22px,2.5vw,28px);letter-spacing:-.5px}.label-create-hero h1 i{display:grid;place-items:center;width:44px;height:44px;border-radius:14px;background:linear-gradient(135deg,#9c6218,#d79436);color:#fff;font-size:22px}
        .label-create-hero p{max-width:700px;margin:8px 0 0;color:#786951;font-size:12px;line-height:1.7}.label-create-back{flex:none;min-height:40px}
        .label-create-card{display:grid;gap:18px;animation:label-create-enter .5s .06s ease-out both}.label-create-heading{display:flex;align-items:flex-start;gap:11px}.label-create-heading-icon{display:grid;place-items:center;flex:none;width:39px;height:39px;border-radius:12px;background:#fff0d9;color:#a66a18;font-size:19px}.label-create-heading h2{margin:0;color:var(--text);font-size:16px}.label-create-heading p{margin:4px 0 0;color:var(--muted);font-size:11px;line-height:1.55}
        .label-detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.label-detail-grid .field{min-width:0}.label-detail-grid .field.full{grid-column:1/-1}.label-detail-grid .field>span:first-child{color:var(--muted);font-size:11px;font-weight:800}.label-detail-grid input:not([type=checkbox]),.label-detail-grid select{min-width:0;width:100%;padding:11px 12px;border:1px solid var(--border);border-radius:10px;background:var(--card);color:var(--text);font:inherit;font-size:12px}
        .label-product-panel{display:grid;gap:10px;padding:15px;border:1px solid var(--border);border-radius:15px;background:linear-gradient(145deg,var(--card),var(--bg))}.label-product-panel-head{display:flex;align-items:center;justify-content:space-between;gap:10px}.label-products-label{color:var(--muted);font-size:11px;font-weight:800}.label-selected-count{padding:6px 10px;border:1px solid #efdfc9;border-radius:999px;background:#fff8ed;color:#986018;font-size:10px;font-weight:800;white-space:nowrap}.label-product-panel .product-picker-search{margin:0}.label-product-panel .product-picker{max-height:380px;background:var(--bg)}
        .label-create-note{display:none;margin:0;padding:10px 12px;border:1px solid #f4cdcd;border-radius:10px;background:#fff4f4;color:#a83131;font-size:11px}.label-create-note.is-visible{display:block}
        .label-form-actions{display:flex;justify-content:space-between;align-items:center;gap:12px;padding-top:14px;border-top:1px solid var(--border)}.label-form-actions p{margin:0;color:var(--muted);font-size:10px;line-height:1.5}.label-form-actions .actions{justify-content:flex-end}
        @media(max-width:700px){.label-create-hero{align-items:flex-start;flex-direction:column;padding:21px}.label-create-back{width:100%}.label-detail-grid{grid-template-columns:1fr}.label-detail-grid .field.full{grid-column:auto}.label-form-actions{align-items:stretch;flex-direction:column}.label-form-actions .actions{width:100%}.label-form-actions .button{width:100%}}
        @media(prefers-reduced-motion:reduce){.label-create-hero,.label-create-card{animation:none}}
    </style>
    <div class="label-create-page">
        <section class="label-create-hero">
            <div><h1><i class="ri-price-tag-3-line"></i> Create a product label</h1><p>Name the label, choose its storefront type, and assign products. Reusing an existing name and type updates that label set’s assignments.</p></div>
            <a class="button label-create-back" href="{{ route('admin.ecommerce.labels') }}"><i class="ri-arrow-left-line"></i> Back to labels</a>
        </section>
        <form method="POST" action="{{ route('admin.ecommerce.labels.store') }}" class="card label-create-card" id="labelCreateForm">
            @csrf
            <div class="label-create-heading"><span class="label-create-heading-icon"><i class="ri-bookmark-3-line"></i></span><div><h2>Label details</h2><p>Use a short, recognizable name that helps shoppers scan the catalog.</p></div></div>
            <div class="label-detail-grid">
                <label class="field"><span>Label name</span><input required maxlength="100" name="name" value="{{ old('name') }}" placeholder="e.g. Summer spotlight"></label>
                <label class="field"><span>Label type</span><select name="type" required>@foreach(\App\Models\ProductLabel::TYPES as $type)<option value="{{ $type }}" @selected(old('type', 'new') === $type)>{{ ucfirst($type) }}</option>@endforeach</select></label>
                <div class="field full">
                    <div class="label-product-panel">
                        <div class="label-product-panel-head"><span class="label-products-label">Choose products for this label</span><span class="label-selected-count" data-label-selected-count>0 selected</span></div>
                        <label class="product-picker-search"><span class="sr-only">Search products for the label</span><input type="search" data-product-picker-search placeholder="Search products by name or category..."></label>
                        <div class="product-picker">
                            @php($selectedProductIds = collect(old('products', []))->map(fn ($id) => (int) $id)->all())
                            @forelse($products as $product)
                                <label class="product-choice"><input type="checkbox" name="products[]" value="{{ $product->id }}" @checked(in_array($product->id, $selectedProductIds, true))><span class="product-thumb">@if($product->images->first())<img src="{{ asset('storage/'.$product->images->first()->path) }}" alt="">@else{{ $product->emoji ?: '✦' }}@endif</span><span class="product-choice-copy"><strong>{{ $product->name }}</strong><small>{{ $product->category?->name ?? 'Uncategorized' }} · {{ $product->stock }} in stock</small></span></label>
                            @empty
                                <p class="empty">No products are available to assign yet.</p>
                            @endforelse
                        </div>
                        <p class="label-create-note" data-label-validation role="alert">Select at least one product before saving this label set.</p>
                    </div>
                </div>
            </div>
            <div class="label-form-actions"><p>Saving an existing name and type replaces the products assigned to that label set.</p><div class="actions"><a class="button" href="{{ route('admin.ecommerce.labels') }}">Cancel</a><button class="button primary" type="submit"><i class="ri-save-line"></i> Save label set</button></div></div>
        </form>
    </div>
    @push('scripts')
    <script>
        (function () {
            var form = document.getElementById('labelCreateForm');
            if (!form) return;
            var picker = form.querySelector('.product-picker');
            var boxes = picker ? Array.from(picker.querySelectorAll('input[name="products[]"]')) : [];
            var count = form.querySelector('[data-label-selected-count]');
            var note = form.querySelector('[data-label-validation]');
            function updateCount() { var selected = boxes.filter(function (box) { return box.checked; }).length; count.textContent = selected + ' selected'; if (selected) note.classList.remove('is-visible'); }
            boxes.forEach(function (box) { box.addEventListener('change', updateCount); });
            updateCount();
            form.addEventListener('submit', function (event) { if (boxes.some(function (box) { return box.checked; })) return; event.preventDefault(); note.classList.add('is-visible'); if (picker) picker.scrollIntoView({behavior:'smooth',block:'center'}); });
        })();
    </script>
    @endpush
@endsection
