@extends('admin.layouts.ecommerce')

@section('title', $vendor->name . ' — vendor profile')

@section('content')
<style>
    .back-link { display:inline-flex; align-items:center; gap:6px; color:var(--muted); font-size:12px; font-weight:700; text-decoration:none }
    .back-link:hover { color:var(--brand) }
    .vendor-hero { display:grid; grid-template-columns:auto minmax(0,1fr) auto; align-items:center; gap:18px; padding:24px; border-radius:18px; background:linear-gradient(115deg,rgba(14,90,117,.1),rgba(62,197,224,.06) 55%,rgba(113,68,200,.08)); border:1px solid var(--border) }
    .vendor-photo { display:grid; place-items:center; width:82px; height:82px; overflow:hidden; border-radius:22px; background:linear-gradient(145deg,#dff6fb,#e9efff); color:#12617b; font-size:30px; font-weight:800 }
    .vendor-photo img { width:100%; height:100%; object-fit:cover }
    .vendor-heading { min-width:0 }
    .vendor-heading h1 { margin:0; font-size:23px; letter-spacing:-.5px; overflow-wrap:anywhere }
    .vendor-heading p { margin:6px 0 10px; color:var(--muted); font-size:13px }
    .vendor-badges { display:flex; flex-wrap:wrap; gap:7px }
    .vendor-contact { color:var(--muted); font-size:12px; text-align:right; line-height:1.8 }
    .vendor-contact a { color:var(--brand); font-weight:700; text-decoration:none }
    .profile-stats { grid-template-columns:repeat(4,minmax(0,1fr)) }
    .profile-grid { display:grid; grid-template-columns:minmax(250px,.75fr) minmax(0,1.7fr); gap:18px; align-items:start }
    .info-list { display:grid; gap:0; margin:0 }
    .info-row { display:grid; grid-template-columns:110px minmax(0,1fr); gap:10px; padding:12px 0; border-bottom:1px solid var(--border); font-size:12px }
    .info-row:last-child { border-bottom:0 }
    .info-row dt { color:var(--muted); font-weight:700 }
    .info-row dd { margin:0; overflow-wrap:anywhere }
    .product-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px }
    .product-card { min-width:0; padding:12px; border:1px solid var(--border); border-radius:13px; background:var(--card) }
    .product-image { display:grid; place-items:center; width:100%; height:150px; overflow:hidden; border-radius:10px; background:linear-gradient(145deg,#f2f6f8,#eaf5f7); color:var(--muted); font-size:34px }
    .product-image img { width:100%; height:100%; object-fit:cover }
    .product-title { display:flex; align-items:flex-start; justify-content:space-between; gap:8px; margin:11px 0 5px }
    .product-title h3 { margin:0; font-size:13px; line-height:1.4 }
    .product-subtitle { min-height:15px; color:var(--muted); font-size:10px }
    .product-data { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:7px; margin-top:12px }
    .product-data span { padding:8px 7px; border-radius:8px; background:var(--bg); color:var(--muted); font-size:10px; text-align:center }
    .product-data strong { display:block; margin-bottom:3px; color:var(--text); font-size:13px }
    .badge.unpublished { background:#f0f2f5; color:#596579 }
    .product-pagination { display:flex; justify-content:flex-end; margin-top:15px }
    @media(max-width:1000px) { .profile-grid { grid-template-columns:1fr } }
    @media(max-width:680px) { .vendor-hero { grid-template-columns:auto minmax(0,1fr); padding:17px; gap:13px } .vendor-photo { width:64px; height:64px; border-radius:18px } .vendor-heading h1 { font-size:19px } .vendor-contact { grid-column:1/-1; text-align:left; border-top:1px solid var(--border); padding-top:10px } .profile-stats { grid-template-columns:repeat(2,minmax(0,1fr)) } .product-grid { grid-template-columns:1fr } }
    @media(max-width:420px) { .profile-stats { grid-template-columns:1fr } .info-row { grid-template-columns:92px minmax(0,1fr) } }
</style>
<a class="back-link" href="{{ route('admin.ecommerce.customers.index') }}"><i class="ri-arrow-left-line"></i>Back to customers & vendors</a>

<section class="vendor-hero">
    <span class="vendor-photo">
        @if($vendor->profile_photo)
            <img src="{{ asset('storage/' . $vendor->profile_photo) }}" alt="{{ $vendor->name }} profile photo">
        @else
            {{ mb_strtoupper(mb_substr($vendor->name, 0, 1)) }}
        @endif
    </span>
    <div class="vendor-heading">
        <h1>{{ $vendor->name }}</h1>
        <p>Vendor profile · Joined {{ $vendor->created_at?->format('F j, Y') ?? 'date unavailable' }}</p>
        <div class="vendor-badges">
            <span class="badge vendor"><i class="ri-store-2-line"></i>&nbsp; Vendor</span>
            <span class="badge {{ $vendor->is_blocked ? 'blocked' : 'active' }}">{{ $vendor->is_blocked ? 'Blocked account' : 'Active account' }}</span>
            @if($vendor->email_verified_at)<span class="badge paid"><i class="ri-verified-badge-line"></i>&nbsp; Email verified</span>@endif
        </div>
    </div>
    <div class="vendor-contact">
        <div><i class="ri-mail-line"></i> <a href="mailto:{{ $vendor->email }}">{{ $vendor->email }}</a></div>
        @if($vendor->phone)<div><i class="ri-phone-line"></i> <a href="tel:{{ $vendor->phone }}">{{ $vendor->phone }}</a></div>@endif
    </div>
</section>

<section class="stats profile-stats" aria-label="Vendor product summary">
    <article class="card stat"><span class="stat-icon"><i class="ri-box-3-line"></i></span><div><strong>{{ number_format($counts['submitted']) }}</strong><span>Submitted products</span></div></article>
    <article class="card stat"><span class="stat-icon green"><i class="ri-check-double-line"></i></span><div><strong>{{ number_format($counts['published']) }}</strong><span>Published products</span></div></article>
    <article class="card stat"><span class="stat-icon amber"><i class="ri-time-line"></i></span><div><strong>{{ number_format($counts['pending']) }}</strong><span>Awaiting review</span></div></article>
    <article class="card stat"><span class="stat-icon violet"><i class="ri-stack-line"></i></span><div><strong>{{ number_format($products->total()) }}</strong><span>Products shown</span></div></article>
</section>

<div class="profile-grid">
    <section class="card">
        <h2 class="card-title">Vendor details</h2>
        <dl class="info-list">
            <div class="info-row"><dt>Email</dt><dd>{{ $vendor->email }}</dd></div>
            <div class="info-row"><dt>Phone</dt><dd>{{ $vendor->phone ?: 'Not provided' }}</dd></div>
            <div class="info-row"><dt>Skin type</dt><dd>{{ $vendor->skin_type ?: 'Not provided' }}</dd></div>
            <div class="info-row"><dt>Hair type</dt><dd>{{ $vendor->hair_type ?: 'Not provided' }}</dd></div>
            <div class="info-row"><dt>Shade match</dt><dd>{{ $vendor->shade_match ?: 'Not provided' }}</dd></div>
            <div class="info-row"><dt>Loves</dt><dd>{{ $vendor->loves ?: 'Not provided' }}</dd></div>
            <div class="info-row"><dt>Email status</dt><dd>{{ $vendor->email_verified_at ? 'Verified' : 'Unverified' }}</dd></div>
            <div class="info-row"><dt>Account status</dt><dd>{{ $vendor->is_blocked ? 'Blocked' : 'Active' }}</dd></div>
        </dl>
    </section>

    <section class="card">
        <div class="toolbar">
            <div><h2 class="card-title" style="margin:0">Submitted products</h2><span class="count-note">Published products are approved and active, matching storefront approval rules.</span></div>
            <span class="badge">{{ number_format($products->total()) }} total</span>
        </div>
        @if($products->isEmpty())
            <div class="empty">No submitted products found for this vendor.</div>
        @else
            <div class="product-grid">
                @foreach($products as $product)
                    @php
                        $isPublished = $product->review_status === 'approved' && $product->is_active;
                        $productStatus = $isPublished ? 'Published' : ($product->review_status === 'approved' ? 'Approved · unpublished' : ucfirst($product->review_status ?: 'submitted'));
                    @endphp
                    <article class="product-card">
                        <div class="product-image">
                            @if($product->images->first())
                                <img src="{{ asset('storage/' . $product->images->first()->path) }}" alt="{{ $product->name }}" loading="lazy">
                            @else
                                {{ $product->emoji ?: '✦' }}
                            @endif
                        </div>
                        <div class="product-title">
                            <h3>{{ $product->name }}</h3>
                            <span class="badge {{ $isPublished ? 'published' : ($product->review_status ?: 'unpublished') }}">{{ $productStatus }}</span>
                        </div>
                        <div class="product-subtitle">
                            @if($product->category){{ $product->category->name }} · @endif
                            Submitted {{ $product->created_at?->format('M j, Y') ?? 'date unavailable' }}
                        </div>
                        <div class="product-data">
                            <span><strong>{{ number_format((int) $product->stock) }}</strong>Stock</span>
                            <span><strong>{{ $product->images->count() }}</strong>Images</span>
                            <span><strong>{{ $product->review_status === 'approved' ? 'Yes' : 'No' }}</strong>Approved</span>
                        </div>
                    </article>
                @endforeach
            </div>
            @if($products->hasPages())<div class="product-pagination">{{ $products->links() }}</div>@endif
        @endif
    </section>
</div>
@endsection
