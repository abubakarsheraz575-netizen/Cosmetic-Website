@extends('admin.layouts.ecommerce')

@section('title', 'Order returns')

@section('content')
<header class="page-head">
    <div><h1><i class="ri-arrow-go-back-line"></i>Order returns</h1><p>Review customer reasons and manage the return and refund decision.</p></div>
</header>
<section class="stats">
    <article class="card stat"><span class="stat-icon"><i class="ri-file-list-3-line"></i></span><div><strong>{{ $metrics['total'] }}</strong><span>Total requests</span></div></article>
    <article class="card stat"><span class="stat-icon amber"><i class="ri-time-line"></i></span><div><strong>{{ $metrics['pending'] }}</strong><span>Awaiting review</span></div></article>
    <article class="card stat"><span class="stat-icon"><i class="ri-checkbox-circle-line"></i></span><div><strong>{{ $metrics['approved'] }}</strong><span>Approved</span></div></article>
    <article class="card stat"><span class="stat-icon green"><i class="ri-refund-2-line"></i></span><div><strong>{{ $metrics['refunded'] }}</strong><span>Refunded</span></div></article>
</section>
<section class="card">
    <div class="toolbar"><h2 class="card-title" style="margin:0">Return requests</h2><input type="search" data-table-search="returnsTable" placeholder="Search returns, customers, products..." aria-label="Search return requests"></div>
    <div class="table-wrap"><table id="returnsTable">
        <thead><tr><th>Return</th><th>Order / product</th><th>Customer</th><th>Qty</th><th>Reason</th><th>Status</th><th>Requested</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse($returns as $return)
            <tr>
                <td><strong>{{ $return->return_number }}</strong></td>
                <td>{{ $return->order->order_number }}<small>{{ $return->item->product_name }}</small></td>
                <td>{{ $return->user->name }}<small>{{ $return->user->email }}</small></td>
                <td>{{ $return->quantity }}</td>
                <td>{{ \Illuminate\Support\Str::limit($return->reason, 80) }}</td>
                <td><span class="badge {{ $return->status }}">{{ $return->status }}</span></td>
                <td>{{ $return->created_at->format('M d, Y') }}</td>
                <td><div class="actions">
                    <a class="button primary" href="{{ route('admin.ecommerce.returns.edit', $return) }}">Review / edit</a>
                    <form method="POST" action="{{ route('admin.ecommerce.returns.destroy', $return) }}" data-confirm-modal data-confirm-title="Delete this return request?" data-confirm-message="This return request will be permanently removed.">@csrf @method('DELETE')<button class="button danger" type="submit">Delete</button></form>
                </div></td>
            </tr>
        @empty
            <tr><td colspan="8" class="empty">No return requests have been submitted yet.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</section>
@endsection
