@extends('admin.layouts.ecommerce')
@section('title', 'Customer cart')
@section('content')
<style>
    .customer-cart-detail{display:grid;gap:16px;min-width:0;color:var(--text)}.customer-cart-detail-head{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap}.customer-cart-detail-head h1{margin:0;font-size:24px}.customer-cart-detail-head p{margin:5px 0 0;color:var(--muted);font-size:12px}.customer-cart-back{display:inline-flex;align-items:center;gap:7px;color:var(--brand);font-size:12px;font-weight:800;text-decoration:none}.customer-cart-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}.customer-cart-summary article,.customer-cart-lines{padding:18px;border:1px solid var(--border);border-radius:15px;background:var(--card);box-shadow:var(--shadow)}.customer-cart-summary span{display:block;color:var(--muted);font-size:11px}.customer-cart-summary strong{display:block;margin-top:6px;font-size:18px}.customer-cart-lines{min-width:0}.customer-cart-lines h2{margin:0 0 12px;font-size:15px}.customer-cart-table{width:100%;min-width:650px;border-collapse:collapse}.customer-cart-table th{padding:11px;text-align:left;background:var(--bg);color:var(--muted);font-size:9px;text-transform:uppercase}.customer-cart-table td{padding:12px 11px;border-bottom:1px solid var(--border);font-size:12px}.cart-product{display:flex;align-items:center;gap:10px;min-width:180px}.cart-product img,.cart-placeholder{display:grid;width:42px;height:42px;flex:none;place-items:center;object-fit:cover;border-radius:10px;background:var(--bg)}.cart-product strong,.cart-product small{display:block}.cart-product small{margin-top:3px;color:var(--muted);font-size:10px}.cart-unavailable{color:#a14532;font-size:10px;font-weight:800}.cart-lines-empty{text-align:center;padding:28px;color:var(--muted)}
    @media(max-width:700px){.customer-cart-summary{grid-template-columns:1fr 1fr}.customer-cart-summary article:last-child{grid-column:1/-1}}
    @media(max-width:480px){.customer-cart-summary{grid-template-columns:1fr}.customer-cart-summary article:last-child{grid-column:auto}}
</style>
<main class="customer-cart-detail">
    <header class="customer-cart-detail-head"><div><h1>Customer cart #{{ $customerCart->id }}</h1><p>Last activity {{ $customerCart->last_activity->format('F j, Y · H:i') }}</p></div><a class="customer-cart-back" href="{{ route('admin.ecommerce.customer-carts.index') }}"><i class="ri-arrow-left-line"></i>All customer carts</a></header>
    <section class="customer-cart-summary" aria-label="Cart summary">
        <article><span>Customer</span><strong>{{ $customerCart->user->name }}</strong><span>{{ $customerCart->user->email }}</span></article>
        <article><span>Items</span><strong>{{ $customerCart->item_count }}</strong></article>
        <article><span>Current total</span><strong>£{{ number_format($customerCart->cart_total, 2) }}</strong></article>
    </section>
    <section class="customer-cart-lines"><h2>Cart items</h2><div class="table-wrap"><table class="customer-cart-table"><thead><tr><th>Product</th><th>SKU</th><th>Unit price</th><th>Quantity</th><th>Subtotal</th></tr></thead><tbody>
        @forelse($customerCart->cart_lines as $line)
            <tr><td><div class="cart-product">@if($line['image'])<img src="{{ $line['image'] }}" alt="">@else<span class="cart-placeholder"><i class="ri-flask-line"></i></span>@endif<div><strong>{{ $line['name'] }}</strong>@if($line['variant'])<small>{{ $line['variant'] }}</small>@endif @unless($line['available'])<small class="cart-unavailable">Unavailable now</small>@endunless</div></div></td><td>{{ $line['sku'] ?: '—' }}</td><td>£{{ number_format($line['price'], 2) }}</td><td>{{ $line['quantity'] }}</td><td>£{{ number_format($line['line_total'], 2) }}</td></tr>
        @empty
            <tr><td colspan="5" class="cart-lines-empty">This customer cart has no available items.</td></tr>
        @endforelse
    </tbody></table></div></section>
</main>
@endsection
