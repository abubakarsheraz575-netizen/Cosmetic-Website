@extends('admin.layouts.ecommerce')

@section('title', 'Invoices')

@section('content')
<header class="page-head">
    <div><h1><i class="ri-file-list-3-line"></i>Paid invoices</h1><p>Invoices are shown only after successful payment. Edit invoice lines or delivery details.</p></div>
</header>
<section class="stats">
    <article class="card stat"><span class="stat-icon"><i class="ri-file-list-3-line"></i></span><div><strong>{{ $metrics['total'] }}</strong><span>Paid invoices</span></div></article>
    <article class="card stat"><span class="stat-icon green"><i class="ri-bank-card-line"></i></span><div><strong>{{ $metrics['total'] }}</strong><span>Successful payments</span></div></article>
    <article class="card stat"><span class="stat-icon amber"><i class="ri-truck-line"></i></span><div><strong>{{ $orders->where('status', 'shipped')->count() }}</strong><span>Shipped orders</span></div></article>
    <article class="card stat"><span class="stat-icon violet"><i class="ri-money-pound-circle-line"></i></span><div><strong>£{{ number_format($metrics['revenue'], 2) }}</strong><span>Paid revenue</span></div></article>
</section>
<section class="card">
    <div class="toolbar"><h2 class="card-title" style="margin:0">Invoice register</h2><input type="search" data-table-search="invoicesTable" placeholder="Search invoice, order, customer..." aria-label="Search invoices"><select data-table-status="invoicesTable" aria-label="Filter invoices"><option value="">All order statuses</option>@foreach(['pending','processing','shipped','delivered','cancelled'] as $status)<option value="{{ $status }}">{{ ucfirst($status) }}</option>@endforeach</select></div>
    <div class="table-wrap"><table id="invoicesTable">
        <thead><tr><th>Invoice / order</th><th>Customer</th><th>Products</th><th>Issued</th><th>Delivery</th><th>Payment</th><th>Total</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse($orders as $order)
            <tr data-status="{{ $order->status }}">
                <td><strong>INV-{{ str_pad((string) $order->id, 6, '0', STR_PAD_LEFT) }}</strong><small>{{ $order->order_number }}</small></td>
                <td>{{ $order->name }}<small>{{ $order->email }}</small></td>
                <td>
                    <div class="dt-products">
                    @foreach($order->items as $item)
                        <span class="dt-product-chip"><span class="product-thumb">@if($item->product?->images->first())<img src="{{ asset('storage/'.$item->product->images->first()->path) }}" alt="{{ $item->product_name }}" loading="lazy">@else{{ $item->emoji ?: '✦' }}@endif</span><span>{{ $item->product_name }} × {{ $item->qty }}</span></span>
                        @foreach($item->returns as $return)
                            <small>Return {{ $return->return_number }} · {{ $return->status }}</small>
                        @endforeach
                    @endforeach
                    </div>
                    <small>{{ $order->items->sum('qty') }} total units</small>
                </td>
                <td>{{ $order->created_at->format('M d, Y') }}</td>
                <td><span class="badge {{ $order->status }}">{{ $order->status }}</span></td>
                <td><span class="badge paid">Paid</span></td>
                <td><strong>£{{ number_format($order->total, 2) }}</strong></td>
                <td><div class="actions">
                    <a class="button primary" href="{{ route('admin.ecommerce.orders.edit', $order) }}">Edit invoice</a>
                    <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" data-confirm-modal data-confirm-title="Delete this order?" data-confirm-message="The order and its invoice will be permanently removed.">@csrf @method('DELETE')<button type="submit" class="button danger">Delete</button></form>
                </div></td>
            </tr>
        @empty
            <tr><td colspan="8" class="empty">No invoices have been created yet.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</section>
@endsection
