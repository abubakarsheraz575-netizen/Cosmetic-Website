@extends('admin.layouts.ecommerce')

@section('title', $user->name.' · Vendor')

@section('content')
<style>
    .vendor-profile-head{display:flex;align-items:center;gap:16px;flex-wrap:wrap}
    .vendor-profile-avatar{width:76px;height:76px;display:grid;place-items:center;overflow:hidden;border-radius:22px;background:#e4f6fb;color:#0e5a75;font-size:27px;font-weight:800}
    .vendor-profile-avatar img{width:100%;height:100%;object-fit:cover}
    .vendor-profile-copy{min-width:0;flex:1}.vendor-profile-copy h1{margin:0;font-size:24px}.vendor-profile-copy p{margin:5px 0 0;color:var(--muted);font-size:13px}
    .vendor-metrics{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
    .vendor-metric{padding:16px;border:1px solid var(--border);border-radius:13px;background:var(--card)}.vendor-metric strong,.vendor-metric span{display:block}.vendor-metric strong{font-size:22px}.vendor-metric span{margin-top:4px;color:var(--muted);font-size:11px}
    .vendor-bio-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.vendor-bio{padding:13px;border-radius:11px;background:var(--bg)}.vendor-bio small,.vendor-bio strong{display:block}.vendor-bio small{margin-bottom:5px;color:var(--muted);font-size:10px;text-transform:uppercase;letter-spacing:.08em}.vendor-bio strong{overflow-wrap:anywhere;font-size:13px}
    .vendor-items{min-width:700px}.vendor-item{display:flex;align-items:center;gap:10px;min-width:180px}.vendor-item img{width:42px;height:42px;object-fit:cover;border-radius:10px}.vendor-item strong,.vendor-item small{display:block}.vendor-item small{margin-top:3px;color:var(--muted);font-size:11px}
    .vendor-pagination{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-top:14px}
    .vendor-pagination nav{display:flex;gap:5px}.vendor-pagination nav a,.vendor-pagination nav span{display:grid;place-items:center;min-width:34px;height:34px;padding:0 9px;border:1px solid var(--border);border-radius:8px;color:var(--text);text-decoration:none;font-size:12px}.vendor-pagination nav span[aria-current]{background:var(--brand);border-color:var(--brand);color:#fff}
    @media(max-width:700px){.vendor-metrics{grid-template-columns:1fr 1fr}.vendor-bio-grid{grid-template-columns:1fr}.vendor-profile-copy h1{font-size:20px}}
</style>

<header class="page-head">
    <div class="vendor-profile-head">
        <span class="vendor-profile-avatar">@if($user->profile_photo)<img src="{{ asset('storage/'.$user->profile_photo) }}" alt="{{ $user->name }}">@else{{ mb_strtoupper(mb_substr($user->name,0,1)) }}@endif</span>
        <div class="vendor-profile-copy"><h1>{{ $user->name }}</h1><p>Vendor profile · Joined {{ $user->created_at?->format('F j, Y') }}</p></div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap"><a class="button" href="{{ route('admin.marketplace.messages',['vendor'=>$user->id]) }}"><i class="ri-chat-3-line"></i>Message vendor</a><a class="button" href="{{ route('admin.marketplace.stores') }}"><i class="ri-arrow-left-line"></i>Stores</a></div>
</header>

<section class="vendor-metrics">
    <article class="vendor-metric"><strong>{{ number_format($user->submitted_products_count) }}</strong><span>Total submitted</span></article>
    <article class="vendor-metric"><strong>{{ number_format($user->published_products_count) }}</strong><span>Published listings</span></article>
    <article class="vendor-metric"><strong>{{ number_format($user->pending_products_count) }}</strong><span>Awaiting review</span></article>
    <article class="vendor-metric"><strong>{{ $user->is_blocked ? 'Blocked' : 'Active' }}</strong><span>Account access</span></article>
</section>

<section class="card">
    <h2 class="card-title">Vendor information</h2>
    <div class="vendor-bio-grid">
        <div class="vendor-bio"><small>Email address</small><strong>{{ $user->email }}</strong></div>
        <div class="vendor-bio"><small>Phone</small><strong>{{ $user->phone ?: 'Not provided' }}</strong></div>
        <div class="vendor-bio"><small>Email verification</small><strong>{{ $user->email_verified_at?->format('M j, Y') ?? 'Not verified' }}</strong></div>
        <div class="vendor-bio"><small>Cosmetic preferences</small><strong>{{ collect([$user->skin_type,$user->hair_type,$user->shade_match])->filter()->join(' · ') ?: 'Not provided' }}</strong></div>
        <div class="vendor-bio"><small>Product preferences</small><strong>{{ $user->loves ?: 'Not provided' }}</strong></div>
        <div class="vendor-bio"><small>Account created</small><strong>{{ $user->created_at?->format('M j, Y g:i A') ?? '—' }}</strong></div>
        <div class="vendor-bio"><small>Marketplace status</small><strong>{{ $user->is_blocked ? 'Blocked from sign in' : 'Active' }}</strong></div>
    </div>
</section>

<section class="card">
    <div class="toolbar"><div><h2 class="card-title" style="margin:0">Submitted products</h2><span class="count-note">{{ number_format($products->total()) }} listings</span></div></div>
    <div class="table-wrap"><table class="vendor-items" data-server-pagination="true"><thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Quantity</th><th>Review</th><th>Submitted</th></tr></thead><tbody>
        @forelse($products as $product)
        <tr><td><div class="vendor-item">@if($product->images->first())<img src="{{ asset('storage/'.$product->images->first()->path) }}" alt="" loading="lazy">@else<span class="product-thumb"><i class="ri-box-3-line"></i></span>@endif<span><strong>{{ $product->name }}</strong><small>{{ $product->size?->name ?? 'No size specified' }}</small></span></div></td><td>{{ $product->category?->name ?? '—' }}</td><td>£{{ number_format($product->final_price,2) }}</td><td>{{ number_format($product->stock) }}</td><td><span class="badge {{ $product->review_status === 'approved' ? 'success' : ($product->review_status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($product->review_status) }}{{ $product->is_active ? ' · Published' : '' }}</span></td><td>{{ $product->created_at?->format('M j, Y') }}</td></tr>
        @empty<tr><td colspan="6" class="empty">No products have been submitted.</td></tr>@endforelse
    </tbody></table></div>
    <div class="vendor-pagination"><span class="count-note">Showing {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} of {{ number_format($products->total()) }}</span>{{ $products->links() }}</div>
</section>
@endsection
