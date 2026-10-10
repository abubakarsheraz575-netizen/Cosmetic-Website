@extends('admin.layouts.ecommerce')

@section('title', 'Product labels')
@section('content')
    <style>
        @keyframes label-enter{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
        .label-page{display:grid;gap:18px;min-width:0}
        .label-hero{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:27px 30px;border:1px solid #f0dfca;border-radius:22px;background:radial-gradient(circle at 91% 5%,#fff0d9 0,transparent 31%),linear-gradient(125deg,#fff,#fff8f0);box-shadow:0 14px 34px rgba(167,106,30,.07);animation:label-enter .5s ease-out both}
        .label-hero-copy{max-width:690px}.label-eyebrow{display:inline-flex;align-items:center;gap:7px;margin-bottom:9px;color:#ae711e;font-size:10px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}
        .label-hero h1{display:flex;align-items:center;gap:11px;margin:0;color:#41301b;font-size:clamp(22px,2.5vw,29px);letter-spacing:-.5px}.label-hero h1 i{display:grid;place-items:center;width:44px;height:44px;border-radius:14px;background:linear-gradient(135deg,#9c6218,#d79436);color:#fff;font-size:22px;box-shadow:0 8px 18px rgba(167,106,30,.22)}
        .label-hero p{max-width:620px;margin:9px 0 0;color:#786951;font-size:12px;line-height:1.7}
        .label-hero-actions{display:flex;align-items:center;gap:11px;flex:none}.label-total{padding:9px 12px;border:1px solid #ead9c2;border-radius:999px;background:#ffffffc9;color:#925d18;font-size:11px;font-weight:800;white-space:nowrap}
        .label-create-link{min-height:42px;padding:0 16px;border-radius:12px!important;background:#9c6218!important;border-color:#9c6218!important;box-shadow:0 8px 18px rgba(167,106,30,.18)}
        .label-library{min-width:0;animation:label-enter .5s .07s ease-out both}.label-library-heading{display:flex;align-items:flex-start;gap:11px;margin-bottom:16px}.label-library-icon{display:grid;place-items:center;flex:none;width:39px;height:39px;border-radius:12px;background:#fff0d9;color:#a66a18;font-size:19px}.label-library-heading h2{margin:0;color:var(--text);font-size:16px}.label-library-heading p{margin:4px 0 0;color:var(--muted);font-size:11px;line-height:1.55}
        .label-set-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:13px}.label-set-card{min-width:0;padding:16px;border:1px solid var(--border);border-radius:16px;background:linear-gradient(145deg,var(--card),rgba(246,185,97,.035));box-shadow:0 5px 15px rgba(16,24,40,.025);animation:label-enter .4s ease-out both;transition:transform .2s,border-color .2s,box-shadow .2s}.label-set-card:hover{transform:translateY(-2px);border-color:#edd6b5;box-shadow:0 12px 24px rgba(116,79,31,.08)}
        .label-set-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px}.label-set-title{display:flex;align-items:center;gap:10px;min-width:0}.label-set-mark{display:grid;place-items:center;flex:none;width:36px;height:36px;border:1px solid #f0dec3;border-radius:11px;background:#fff5e8;color:#a66a18;font-size:17px}.label-set-title-copy{min-width:0}.label-set-title strong{display:block;overflow:hidden;color:var(--text);font-size:13px;text-overflow:ellipsis;white-space:nowrap}.label-set-title small{display:block;margin-top:3px;color:var(--muted);font-size:10px}
        .label-type-badge{flex:none;padding:6px 9px;border:1px solid #efdfc9;border-radius:999px;background:#fff8ed;color:#986018;font-size:10px;font-weight:800;text-transform:capitalize}
        .label-set-products{display:flex;gap:7px;flex-wrap:wrap;margin-top:13px;padding-top:12px;border-top:1px solid var(--border)}.label-set-products .dt-product-chip{max-width:100%;background:var(--card)}
        .label-delete-form{margin-left:auto;flex:none}.label-delete{min-height:34px;padding:6px 10px;font-size:10px}
        .label-empty{display:grid;place-items:center;gap:7px;padding:40px 20px;border:1px dashed var(--border);border-radius:16px;background:var(--bg);text-align:center}.label-empty i{color:#c58a38;font-size:28px}.label-empty strong{color:var(--text);font-size:13px}.label-empty span{max-width:360px;color:var(--muted);font-size:11px;line-height:1.6}
        @media(max-width:850px){.label-hero{align-items:flex-start;flex-direction:column}.label-hero-actions{width:100%;justify-content:space-between}}
        @media(max-width:600px){.label-hero{padding:20px}.label-hero-actions{align-items:stretch;flex-direction:column}.label-create-link{width:100%}.label-set-grid{grid-template-columns:1fr}.label-set-head{flex-wrap:wrap}.label-delete-form{width:100%}.label-delete{width:100%}}
        @media(prefers-reduced-motion:reduce){.label-hero,.label-library,.label-set-card{animation:none}.label-set-card{transition:none}}
    </style>
    <div class="label-page">
        <section class="label-hero">
            <div class="label-hero-copy"><span class="label-eyebrow"><i class="ri-bookmark-3-line"></i> Storefront merchandising</span><h1><i class="ri-price-tag-3-line"></i> Product labels</h1><p>Group products under clear labels such as Hot, New, or Sale. Keep storefront merchandising organized and update product assignments from one simple workflow.</p></div>
            <div class="label-hero-actions"><span class="label-total">{{ $labels->count() }} {{ \Illuminate\Support\Str::plural('label set', $labels->count()) }}</span><a class="button primary label-create-link" href="{{ route('admin.ecommerce.labels.create') }}"><i class="ri-add-line"></i> Create a label set</a></div>
        </section>
        <section class="card label-library">
            <div class="label-library-heading"><span class="label-library-icon"><i class="ri-stack-line"></i></span><div><h2>Maintained label sets</h2><p>Review product assignments and remove sets that are no longer needed.</p></div></div>
            @forelse($labels as $label)
                <article class="label-set-card">
                    <div class="label-set-head">
                        <div class="label-set-title"><span class="label-set-mark"><i class="ri-bookmark-3-line"></i></span><span class="label-set-title-copy"><strong>{{ $label->name }}</strong><small>{{ $label->products_count }} {{ \Illuminate\Support\Str::plural('product', $label->products_count) }} assigned</small></span></div>
                        <span class="label-type-badge">{{ $label->type }}</span>
                        <form class="label-delete-form" method="POST" action="{{ route('admin.ecommerce.labels.destroy', $label) }}" data-confirm-modal data-confirm-title="Remove this label set?" data-confirm-message="The label set will be removed from all assigned products.">@csrf @method('DELETE')<button class="button danger label-delete" type="submit"><i class="ri-delete-bin-line"></i> Remove set</button></form>
                    </div>
                    <div class="label-set-products">
                        @forelse($label->products as $product)
                            <span class="dt-product-chip"><span class="product-thumb">@if($product->images->first())<img src="{{ asset('storage/'.$product->images->first()->path) }}" alt="">@else{{ $product->emoji ?: '✦' }}@endif</span>{{ $product->name }}</span>
                        @empty
                            <span class="dt-info">No products assigned.</span>
                        @endforelse
                    </div>
                </article>
            @empty
                <div class="label-empty"><i class="ri-bookmark-3-line"></i><strong>No label sets yet</strong><span>Create your first product label to organize a storefront collection of items.</span></div>
            @endforelse
        </section>
    </div>
@endsection
