@extends('admin.layouts.ecommerce')
@section('title', 'Customer carts')
@section('content')
<style>
    .customer-carts{color:var(--text);min-width:0}.customer-carts-head{display:flex;justify-content:space-between;align-items:end;gap:15px;flex-wrap:wrap;margin-bottom:18px}.customer-carts-head h1{margin:0;font-size:26px}.customer-carts-head p{margin:5px 0 0;color:var(--muted);font-size:12px}.customer-carts-panel{min-width:0;overflow:hidden;padding:20px;border:1px solid var(--border);border-radius:17px;background:var(--card);box-shadow:var(--shadow)}.customer-carts-toolbar{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:16px}.customer-carts-filters{display:grid;grid-template-columns:minmax(180px,1fr) 150px auto;gap:8px;flex:1;justify-content:end}.customer-carts-filters input,.customer-carts-filters select{width:100%;min-width:0;min-height:42px;padding:9px 11px;border:1px solid var(--border);border-radius:10px;background:var(--card);color:var(--text);font:inherit;font-size:12px}.customer-carts-filters button,.cart-view{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:38px;padding:0 12px;border:0;border-radius:9px;background:var(--brand);color:#fff;font:inherit;font-size:11px;font-weight:800;text-decoration:none;cursor:pointer}.customer-carts-table{width:100%;min-width:760px;border-collapse:collapse}.customer-carts-table th{padding:11px 10px;background:var(--bg);color:var(--muted);text-align:left;font-size:9px;letter-spacing:.07em;text-transform:uppercase}.customer-carts-table td{padding:12px 10px;border-bottom:1px solid var(--border);font-size:12px}.cart-customer{display:grid;gap:3px}.cart-customer a{color:var(--brand);font-weight:800;text-decoration:none}.cart-customer small,.cart-muted{color:var(--muted);font-size:10px}.cart-kind,.cart-count{display:inline-flex;padding:5px 9px;border-radius:20px;background:var(--bg);font-size:10px;font-weight:800}.cart-empty{text-align:center;padding:32px;color:var(--muted)}.customer-carts-pager{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-top:14px;color:var(--muted);font-size:11px}
    @media(max-width:760px){.customer-carts-panel{padding:14px}.customer-carts-filters{grid-template-columns:1fr 1fr}.customer-carts-filters input{grid-column:1/-1}.customer-carts-filters button{grid-column:1/-1}}
</style>
<main class="customer-carts">
    <header class="customer-carts-head"><div><h1>Customer carts</h1><p>Signed-in customers only. Guest carts are not stored or shown here.</p></div></header>
    <section class="customer-carts-panel">
        <div class="customer-carts-toolbar">
            <strong>{{ number_format($carts->total()) }} saved carts</strong>
            <form class="customer-carts-filters" method="GET" action="{{ route('admin.ecommerce.customer-carts.index') }}">
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search customer name or email..." aria-label="Search customer carts">
                <select name="per_page" aria-label="Carts per page">@foreach([10,25,50] as $size)<option value="{{ $size }}" @selected((int)($filters['per_page'] ?? 10) === $size)>{{ $size }} per page</option>@endforeach</select>
                <button type="submit"><i class="ri-search-line"></i>Search</button>
            </form>
        </div>
        <div class="table-wrap"><table class="customer-carts-table"><thead><tr><th>Identifier</th><th>Customer</th><th>Type</th><th>Items</th><th>Total</th><th>Updated at</th><th>Operations</th></tr></thead><tbody>
            @forelse($carts as $cart)
                <tr>
                    <td>#{{ $cart->id }}</td>
                    <td><div class="cart-customer"><a href="{{ route('admin.ecommerce.customer-carts.show', $cart) }}">{{ $cart->user->name }}</a><small>{{ $cart->user->email }}</small></div></td>
                    <td><span class="cart-kind">Customer</span></td>
                    <td><span class="cart-count">{{ $cart->item_count }} items</span></td>
                    <td>£{{ number_format($cart->cart_total, 2) }}</td>
                    <td>{{ $cart->last_activity->format('M j, Y · H:i') }}</td>
                    <td><a class="cart-view" href="{{ route('admin.ecommerce.customer-carts.show', $cart) }}"><i class="ri-eye-line"></i>View cart</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="cart-empty">No signed-in customer carts to show.</td></tr>
            @endforelse
        </tbody></table></div>
        <footer class="customer-carts-pager"><span>Showing {{ $carts->firstItem() ?? 0 }}–{{ $carts->lastItem() ?? 0 }} of {{ number_format($carts->total()) }}</span>{{ $carts->links() }}</footer>
    </section>
</main>
@endsection
