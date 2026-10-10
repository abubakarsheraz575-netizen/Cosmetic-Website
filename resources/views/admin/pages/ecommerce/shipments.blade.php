@extends('admin.layouts.ecommerce')

@section('title', 'Shipments')

@section('content')
<header class="page-head">
    <div><h1><i class="ri-truck-line"></i>Paid shipments</h1><p>Track delivery status for successfully paid customer orders.</p></div>
</header>
<section class="stats">
    <article class="card stat"><span class="stat-icon"><i class="ri-stack-line"></i></span><div><strong>{{ $metrics['total'] }}</strong><span>Paid shipments</span></div></article>
    <article class="card stat"><span class="stat-icon amber"><i class="ri-time-line"></i></span><div><strong>{{ $metrics['pending'] }}</strong><span>Pending / processing</span></div></article>
    <article class="card stat"><span class="stat-icon"><i class="ri-truck-line"></i></span><div><strong>{{ $metrics['shipped'] }}</strong><span>In transit</span></div></article>
    <article class="card stat"><span class="stat-icon green"><i class="ri-checkbox-circle-line"></i></span><div><strong>{{ $metrics['delivered'] }}</strong><span>Delivered</span></div></article>
    <article class="card stat"><span class="stat-icon violet"><i class="ri-money-pound-circle-line"></i></span><div><strong>£{{ number_format($metrics['revenue'], 2) }}</strong><span>Paid order value</span></div></article>
</section>
<section class="card">
    <div class="toolbar"><h2 class="card-title" style="margin:0">Shipment register</h2><input type="search" data-table-search="shipmentsTable" placeholder="Search tracking / customer / product..." aria-label="Search shipments"><select data-table-status="shipmentsTable" aria-label="Filter shipments"><option value="">All statuses</option>@foreach(['pending','processing','shipped','delivered','cancelled'] as $status)<option value="{{ $status }}">{{ ucfirst($status) }}</option>@endforeach</select></div>
    <div class="table-wrap"><table id="shipmentsTable">
        <thead><tr><th>Order</th><th>Customer</th><th>Products</th><th>Destination</th><th>Order date</th><th>Delivery status</th><th>Payment</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse($orders as $order)
            <tr data-status="{{ $order->status }}">
                <td><strong>{{ $order->order_number }}</strong></td>
                <td>{{ $order->name }}<small>{{ $order->email }}</small></td>
                <td><div class="dt-products">@foreach($order->items as $item)<span class="dt-product-chip"><span class="product-thumb">@if($item->product?->images->first())<img src="{{ asset('storage/'.$item->product->images->first()->path) }}" alt="{{ $item->product_name }}" loading="lazy">@else{{ $item->emoji ?: '✦' }}@endif</span><span>{{ $item->product_name }} × {{ $item->qty }}</span></span>@endforeach</div><small>{{ $order->items->count() }} item{{ $order->items->count() === 1 ? '' : 's' }}</small></td>
                <td>{{ $order->city }}, {{ $order->country }}<small>{{ $order->postal_code }}</small></td>
                <td>{{ $order->created_at->format('M d, Y') }}</td>
                <td><span class="badge {{ $order->status }}">{{ $order->status }}</span></td>
                <td><span class="badge paid">Paid</span></td>
                <td><div class="actions">
                    <a class="button primary" href="{{ route('admin.ecommerce.orders.edit', $order) }}">Edit shipment</a>
                    <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" data-confirm-modal data-confirm-title="Delete this shipment's order?" data-confirm-message="The shipment and its order will be permanently removed.">@csrf @method('DELETE')<button type="submit" class="button danger">Delete</button></form>
                </div></td>
            </tr>
        @empty
            <tr><td colspan="8" class="empty">No shipment records yet.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</section>
@endsection
