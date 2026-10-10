@extends('admin.layouts.ecommerce')

@section('title', 'Schedule a collection')
@section('content')
    <style>
        @keyframes schedule-enter{from{opacity:0;transform:translateY(11px)}to{opacity:1;transform:translateY(0)}}
        .collection-create-page{display:grid;gap:18px;min-width:0}
        .collection-create-hero{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:25px 28px;border:1px solid #d7e8f1;border-radius:21px;background:radial-gradient(circle at 92% 6%,#d8f8f4 0,transparent 30%),linear-gradient(125deg,#fff,#f1f8fc);box-shadow:0 14px 34px rgba(14,90,117,.07);animation:schedule-enter .45s ease-out both}
        .collection-create-hero h1{display:flex;align-items:center;gap:11px;margin:0;color:#123744;font-size:clamp(22px,2.5vw,28px);letter-spacing:-.5px}
        .collection-create-hero h1 i{display:grid;place-items:center;width:44px;height:44px;border-radius:14px;background:linear-gradient(135deg,#0e5a75,#249ab1);color:#fff;font-size:22px}
        .collection-create-hero p{max-width:700px;margin:8px 0 0;color:#617785;font-size:12px;line-height:1.7}
        .collection-create-back{flex:none;min-height:40px}
        .collection-create-card{display:grid;gap:18px;animation:schedule-enter .5s .06s ease-out both}
        .collection-create-heading{display:flex;align-items:flex-start;gap:11px;margin-bottom:0}
        .collection-create-heading-icon{display:grid;place-items:center;flex:none;width:39px;height:39px;border-radius:12px;background:#e1f6f6;color:#087b83;font-size:19px}
        .collection-create-heading h2{margin:0;color:var(--text);font-size:16px}
        .collection-create-heading p{margin:4px 0 0;color:var(--muted);font-size:11px;line-height:1.55}
        .collection-detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
        .collection-detail-grid .field{min-width:0}.collection-detail-grid .field.full{grid-column:1/-1}
        .collection-detail-grid .field>span:first-child,.collection-products-label{color:var(--muted);font-size:11px;font-weight:800}
        .collection-detail-grid input:not([type=checkbox]),.collection-detail-grid textarea{min-width:0;width:100%;padding:11px 12px;border:1px solid var(--border);border-radius:10px;background:var(--card);color:var(--text);font:inherit;font-size:12px}
        .collection-detail-grid textarea{min-height:90px;resize:vertical}
        .collection-date-help{margin-top:1px;color:var(--muted);font-size:10px;line-height:1.45}
        .collection-product-panel{display:grid;gap:10px;padding:15px;border:1px solid var(--border);border-radius:15px;background:linear-gradient(145deg,var(--card),var(--bg))}
        .collection-product-panel-head{display:flex;align-items:center;justify-content:space-between;gap:10px}
        .collection-selected-count{padding:6px 10px;border:1px solid rgba(14,90,117,.14);border-radius:999px;background:rgba(62,197,224,.1);color:var(--brand);font-size:10px;font-weight:800;white-space:nowrap}
        .collection-product-panel .product-picker-search{margin:0}
        .collection-product-panel .product-picker{max-height:360px;background:var(--bg)}
        .collection-form-actions{display:flex;justify-content:space-between;align-items:center;gap:12px;padding-top:14px;border-top:1px solid var(--border)}
        .collection-form-actions p{margin:0;color:var(--muted);font-size:10px;line-height:1.5}
        .collection-form-actions .actions{justify-content:flex-end}
        .collection-validation-note{display:none;margin:0;padding:10px 12px;border:1px solid #f4cdcd;border-radius:10px;background:#fff4f4;color:#a83131;font-size:11px}
        .collection-validation-note.is-visible{display:block}
        @media(max-width:700px){.collection-create-hero{align-items:flex-start;flex-direction:column;padding:21px}.collection-create-back{width:100%}.collection-detail-grid{grid-template-columns:1fr}.collection-detail-grid .field.full{grid-column:auto}.collection-form-actions{align-items:stretch;flex-direction:column}.collection-form-actions .actions{width:100%}.collection-form-actions .button{width:100%}}
        @media(prefers-reduced-motion:reduce){.collection-create-hero,.collection-create-card{animation:none}}
    </style>
    <div class="collection-create-page">
        <section class="collection-create-hero">
            <div><h1><i class="ri-calendar-event-line"></i> Schedule a collection</h1><p>Give your storefront a timely story. Set its live window, choose eligible products, and confirm everything before publishing the schedule.</p></div>
            <a class="button collection-create-back" href="{{ route('admin.ecommerce.collections') }}"><i class="ri-arrow-left-line"></i> Back to collections</a>
        </section>
        <form method="POST" action="{{ route('admin.ecommerce.collections.store') }}" class="card collection-create-card" id="collectionCreateForm">
            @csrf
            <div class="collection-create-heading"><span class="collection-create-heading-icon"><i class="ri-calendar-schedule-line"></i></span><div><h2>Collection details</h2><p>Choose a name, schedule, and products for this storefront section.</p></div></div>
            <div class="collection-detail-grid">
                <label class="field"><span>Collection name</span><input required maxlength="160" name="name" value="{{ old('name') }}" placeholder="e.g. Spring skincare edit"></label>
                <label class="field"><span>Description <small>(optional)</small></span><textarea name="description" maxlength="3000" placeholder="Tell shoppers what brings these products together.">{{ old('description') }}</textarea></label>
                <label class="field"><span>Starts at</span><input required type="datetime-local" id="collectionStartsAt" value=""><small class="collection-date-help">Shown in your local time and stored consistently for checkout.</small></label>
                <input type="hidden" name="starts_at" value="{{ old('starts_at') }}" data-collection-utc>
                <label class="field"><span>Ends at</span><input required type="datetime-local" id="collectionEndsAt" value=""><small class="collection-date-help">The end time must be after the start time and in the future.</small></label>
                <input type="hidden" name="ends_at" value="{{ old('ends_at') }}" data-collection-utc>
                <div class="field full">
                    <div class="collection-product-panel">
                        <div class="collection-product-panel-head"><span class="collection-products-label">Choose products for this collection</span><span class="collection-selected-count" data-collection-selected-count>0 selected</span></div>
                        <label class="product-picker-search"><span class="sr-only">Search products for the collection</span><input type="search" data-product-picker-search placeholder="Search products by name or category..."></label>
                        <div class="product-picker">
                            @php($selectedProductIds = collect(old('products', []))->map(fn ($id) => (int) $id)->all())
                            @forelse($products as $product)
                                <label class="product-choice">
                                    <input type="checkbox" name="products[]" value="{{ $product->id }}" @checked(in_array($product->id, $selectedProductIds, true))>
                                    <span class="product-thumb">@if($product->images->first())<img src="{{ asset('storage/'.$product->images->first()->path) }}" alt="">@else{{ $product->emoji ?: '✦' }}@endif</span>
                                    <span class="product-choice-copy">
                                        <strong>{{ $product->name }}</strong>
                                        <small>{{ $product->category?->name ?? 'Uncategorized' }} · {{ $product->stock }} in stock</small>
                                        @php($productVisible = $product->is_active && $product->review_status === 'approved')
                                        @php($productVisibility = $product->review_status === 'approved' ? ($product->is_active ? 'Published on storefront' : 'Approved · unpublished') : ucfirst($product->review_status ?: 'pending').' review')
                                        <span class="collection-product-status {{ $productVisible ? '' : 'is-hidden' }}">{{ $productVisibility }}</span>
                                    </span>
                                </label>
                            @empty
                                <p class="empty">No products are available to add yet.</p>
                            @endforelse
                        </div>
                        <p class="collection-validation-note" data-collection-validation role="alert">Select at least one product before scheduling this collection.</p>
                    </div>
                </div>
            </div>
            <div class="collection-form-actions"><p>Only approved, published products appear on the storefront while this collection is active.</p><div class="actions"><a class="button" href="{{ route('admin.ecommerce.collections') }}">Cancel</a><button class="button primary" type="submit"><i class="ri-calendar-check-line"></i> Create collection</button></div></div>
        </form>
    </div>
    @push('scripts')
    <script>
        (function () {
            var form = document.getElementById('collectionCreateForm');
            if (!form) return;
            var localInputs = [form.querySelector('#collectionStartsAt'), form.querySelector('#collectionEndsAt')];
            var utcInputs = form.querySelectorAll('[data-collection-utc]');
            function asLocalInput(date) { return new Date(date.getTime() - date.getTimezoneOffset() * 60000).toISOString().slice(0, 16); }
            localInputs.forEach(function (input, index) {
                if (utcInputs[index].value) {
                    var previousValue = new Date(utcInputs[index].value);
                    if (!Number.isNaN(previousValue.getTime())) input.value = asLocalInput(previousValue);
                }
            });
            if (!localInputs[0].value) localInputs[0].value = asLocalInput(new Date(Date.now() + 5 * 60000));
            if (!localInputs[1].value) localInputs[1].value = asLocalInput(new Date(Date.now() + 30 * 86400000));
            localInputs[1].min = localInputs[0].value;
            localInputs[0].addEventListener('change', function () { localInputs[1].min = localInputs[0].value; });
            var picker = form.querySelector('.product-picker');
            var checkboxes = picker ? Array.from(picker.querySelectorAll('input[name="products[]"]')) : [];
            var counter = form.querySelector('[data-collection-selected-count]');
            var note = form.querySelector('[data-collection-validation]');
            function updateCount() { var count = checkboxes.filter(function (box) { return box.checked; }).length; counter.textContent = count + (count === 1 ? ' selected' : ' selected'); if (count) note.classList.remove('is-visible'); }
            checkboxes.forEach(function (box) { box.addEventListener('change', updateCount); });
            updateCount();
            form.addEventListener('submit', function (event) {
                if (!checkboxes.some(function (box) { return box.checked; })) { event.preventDefault(); note.classList.add('is-visible'); if (picker) picker.scrollIntoView({behavior:'smooth',block:'center'}); return; }
                localInputs.forEach(function (input, index) { var localDate = new Date(input.value); utcInputs[index].value = Number.isNaN(localDate.getTime()) ? '' : localDate.toISOString(); });
            });
        })();
    </script>
    @endpush
@endsection
