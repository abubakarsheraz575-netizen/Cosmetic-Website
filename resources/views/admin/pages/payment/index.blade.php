@extends('admin.layouts.ecommerce')

@section('title', $isLogs ? 'Payment logs' : 'Transactions')

@section('content')
<style>
    .payment-head{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap}.payment-head h1{display:flex;align-items:center;gap:12px;margin:0;font-size:25px}.payment-head p{margin:5px 0 0;color:var(--muted);font-size:12px}
    .payment-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}.payment-stat{display:flex;align-items:center;gap:11px;min-width:0;padding:15px;border:1px solid var(--border);border-radius:14px;background:var(--card);box-shadow:var(--shadow)}.payment-stat i{display:grid;place-items:center;width:41px;height:41px;flex:none;border-radius:13px;background:#e4f6fb;color:#0e7490;font-size:20px}.payment-stat strong,.payment-stat span{display:block}.payment-stat strong{overflow:hidden;font-size:19px;text-overflow:ellipsis}.payment-stat span{margin-top:3px;color:var(--muted);font-size:10px}
    .payment-card{min-width:0;padding:20px;border:1px solid var(--border);border-radius:16px;background:var(--card);box-shadow:var(--shadow)}.payment-toolbar{display:grid;grid-template-columns:minmax(180px,1fr) 160px 160px 125px auto auto;gap:8px;align-items:center}.payment-toolbar input,.payment-toolbar select{width:100%;min-width:0;min-height:40px;padding:9px 11px;border:1px solid var(--border);border-radius:9px;background:var(--card);color:var(--text);font:inherit;font-size:11px}.payment-table{min-width:900px}.payment-person{display:flex;align-items:center;gap:9px;min-width:150px}.payment-avatar{display:grid;place-items:center;width:35px;height:35px;flex:none;border-radius:50%;background:#e4f6fb;color:var(--brand);font-size:13px;font-weight:800}.payment-person strong,.payment-person small{display:block}.payment-person strong{font-size:11px}.payment-person small{margin-top:3px;color:var(--muted);font-size:10px}.payment-provider{display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border:1px solid var(--border);border-radius:18px;font-size:10px;font-weight:800}.payment-provider.stripe{background:#f1efff;color:#5145a5;border-color:#e1dcff}.payment-provider.paystack{background:#eaf8ee;color:#0e7a40;border-color:#d5efdc}.payment-provider.other{background:var(--bg);color:var(--muted)}.payment-role{display:inline-flex;padding:5px 8px;border-radius:18px;background:#eef4f8;color:#486071;font-size:9px;font-weight:800}.payment-reference{max-width:220px;overflow:hidden;font-family:ui-monospace,SFMono-Regular,Consolas,monospace;font-size:10px;text-overflow:ellipsis;white-space:nowrap}.payment-total{font-weight:800;font-variant-numeric:tabular-nums}.payment-info{color:var(--muted);font-size:11px}.payment-pager{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-top:14px}.payment-pager nav{display:flex;align-items:center;gap:5px}.payment-pager nav a,.payment-pager nav span{display:grid;place-items:center;min-width:34px;height:34px;padding:0 9px;border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:11px;text-decoration:none}.payment-pager nav span[aria-current]{border-color:var(--brand);background:var(--brand);color:#fff}.payment-note{padding:11px 13px;border-radius:10px;background:#eef8fb;color:#285c70;font-size:11px;line-height:1.6}.payment-tabs{display:flex;gap:8px;flex-wrap:wrap}.payment-tab{padding:9px 12px;border:1px solid var(--border);border-radius:9px;color:var(--muted);font-size:11px;font-weight:700;text-decoration:none}.payment-tab.active{border-color:#cbe8ec;background:#eaf7f8;color:var(--brand)}
    .payment-product-list{display:flex;flex-wrap:wrap;gap:7px;min-width:150px}.payment-product{display:flex;align-items:center;gap:7px;max-width:210px;padding:4px 8px 4px 4px;border:1px solid var(--border);border-radius:20px;font-size:10px}.payment-product img,.payment-product-placeholder{width:31px;height:31px;flex:none;border-radius:50%;object-fit:cover;background:var(--bg)}.payment-product-placeholder{display:grid;place-items:center;color:var(--muted)}.payment-product span:last-child{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.payment-ops{display:flex;align-items:center;gap:6px;white-space:nowrap}.payment-op{display:inline-flex;align-items:center;justify-content:center;gap:6px;min-height:34px;padding:0 9px;border:1px solid var(--border);border-radius:8px;background:var(--card);color:var(--brand);font-size:11px;font-weight:700;text-decoration:none;cursor:pointer}.payment-op.danger{border-color:#f2d8d8;background:#fff5f5;color:#b83232}.payment-create{display:inline-flex;align-items:center;gap:7px;padding:10px 13px;border-radius:9px;background:var(--brand);color:white;font-size:11px;font-weight:800;text-decoration:none}.provider-tabs{display:flex;gap:8px;flex-wrap:wrap;margin:0 0 12px}.provider-tabs a{display:inline-flex;align-items:center;gap:7px;padding:9px 14px;border:1px solid var(--border);border-radius:10px;background:var(--card);color:var(--muted);font-size:11px;font-weight:800;text-decoration:none}.provider-tabs a.active{border-color:#cbe8ec;background:#eaf7f8;color:var(--brand)}.manual-badge{display:inline-block;margin-left:4px;padding:3px 6px;border-radius:12px;background:#fff3d6;color:#9a6500;font-size:8px;font-weight:800;vertical-align:middle}
    @media(max-width:950px){.payment-toolbar{grid-template-columns:1fr 1fr 1fr}.payment-toolbar input{grid-column:span 2}}@media(max-width:700px){.payment-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.payment-card{padding:15px}.payment-head h1{font-size:22px}}@media(max-width:500px){.payment-toolbar{grid-template-columns:1fr 1fr}.payment-toolbar input{grid-column:1/-1}.payment-stats{gap:8px}.payment-stat{padding:11px;gap:8px}.payment-stat i{width:34px;height:34px;font-size:17px}.payment-stat strong{font-size:16px}.payment-create{width:100%;justify-content:center}}
</style>

<header class="page-head payment-head">
    <div><h1><i class="{{ $isLogs ? 'ri-file-list-3-line' : 'ri-bank-card-line' }}"></i>{{ $isLogs ? 'Payment logs' : 'Transactions' }}</h1><p>{{ $isLogs ? 'Gateway payment records with provider, payer, transaction reference, amount, and order product image.' : 'Paid purchases, including explicitly marked manual entries, with order product images.' }}</p></div>
    <nav class="payment-tabs" aria-label="Payment pages">
        <a class="payment-tab {{ $isLogs ? '' : 'active' }}" href="{{ route('admin.payment.transactions') }}">Transactions</a>
        <a class="payment-tab {{ $isLogs ? 'active' : '' }}" href="{{ route('admin.payment.logs') }}">Payment logs</a>
        <a class="payment-create" href="{{ route('admin.payment.transactions.create') }}"><i class="ri-add-line"></i>Add manual transaction</a>
    </nav>
</header>

<section class="payment-stats" aria-label="Paid payment summary">
    <article class="payment-stat"><i class="ri-checkbox-circle-line"></i><div><strong>{{ number_format($stats['transactions']) }}</strong><span>Successful transactions</span></div></article>
    <article class="payment-stat"><i class="ri-money-pound-circle-line"></i><div><strong>£{{ number_format($stats['revenue'],2) }}</strong><span>Paid order total</span></div></article>
    <article class="payment-stat"><i class="ri-bank-card-line"></i><div><strong>{{ number_format($stats['stripe']) }}</strong><span>Paid via Stripe</span></div></article>
    <article class="payment-stat"><i class="ri-secure-payment-line"></i><div><strong>{{ number_format($stats['paystack']) }}</strong><span>Paid via Paystack</span></div></article>
</section>

<section class="payment-card">
    <div class="toolbar">
        <div><h2 class="card-title" style="margin:0">{{ $isLogs ? 'Successful payment activity' : 'Paid transactions' }}</h2><span class="payment-info">{{ number_format($payments->total()) }} matching records</span></div>
        <form method="GET" action="{{ $isLogs ? route('admin.payment.logs') : route('admin.payment.transactions') }}" class="payment-toolbar">
            <input type="search" name="q" value="{{ $filters['q']??'' }}" placeholder="Search person, order, reference..." aria-label="Search paid transactions">
            @unless($isLogs)<select name="provider" aria-label="Filter payment provider"><option value="">All providers</option><option value="stripe" @selected(($filters['provider']??'')==='stripe')>Stripe</option><option value="paystack" @selected(($filters['provider']??'')==='paystack')>Paystack</option></select>@endunless
            <select name="per_page" aria-label="Rows per page">@foreach([10,25,50,100] as $size)<option value="{{ $size }}" @selected((int)($filters['per_page']??25)===$size)>{{ $size }} rows</option>@endforeach</select>
            <button class="button primary" type="submit"><i class="ri-filter-3-line"></i>Filter</button>
            @if(collect($filters)->filter()->isNotEmpty())<a class="button" href="{{ $isLogs ? route('admin.payment.logs') : route('admin.payment.transactions') }}">Reset</a>@endif
        </form>
    </div>
    @if($isLogs)<div class="payment-note" style="margin-bottom:13px"><i class="ri-information-line"></i> Select Stripe or Paystack below to view successful transactions from that provider. Failed and pending attempts are excluded.</div>@endif
    @if($isLogs)
        <nav class="provider-tabs" aria-label="Filter payment logs">
            <a class="{{ ($filters['provider']??'')==='stripe'?'active':'' }}" href="{{ route('admin.payment.logs',array_merge(collect($filters)->except('provider')->all(),['provider'=>'stripe'])) }}"><i class="ri-bank-card-line"></i> Stripe</a>
            <a class="{{ ($filters['provider']??'')==='paystack'?'active':'' }}" href="{{ route('admin.payment.logs',array_merge(collect($filters)->except('provider')->all(),['provider'=>'paystack'])) }}"><i class="ri-secure-payment-line"></i> Paystack</a>
        </nav>
    @endif
    <div class="table-wrap"><table class="payment-table" data-server-pagination="true">
        <thead><tr><th>Order</th><th>Customer / vendor</th><th>Products</th><th>Payment provider</th><th>Transaction reference</th><th>Paid at</th><th>Amount</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse($payments as $payment)
            @php
                $method = strtolower((string)$payment->payment_method);
                $buyerName = $payment->user?->name ?: $payment->name;
                $buyerEmail = $payment->user?->email ?: $payment->email;
            @endphp
            <tr>
                <td><strong>{{ $payment->order_number }}</strong><small>{{ $payment->status ? ucfirst($payment->status).' order' : 'Completed payment' }} @if($payment->is_manual_payment)<span class="manual-badge">MANUAL</span>@endif</small></td>
                <td><div class="payment-person"><span class="payment-avatar">{{ mb_strtoupper(mb_substr($buyerName,0,1)) }}</span><span><strong>{{ $buyerName }}</strong><small>{{ $buyerEmail }}</small></span></div></td>
                <td><div class="payment-product-list">@forelse($payment->items as $item)<span class="payment-product">@if($item->product?->images->first())<img src="{{ asset('storage/'.$item->product->images->first()->path) }}" alt="{{ $item->product_name }}" loading="lazy">@else<span class="payment-product-placeholder"><i class="ri-box-3-line"></i></span>@endif<span>{{ $item->product_name }} × {{ $item->qty }}</span></span>@empty<span class="payment-info">No item details</span>@endforelse</div></td>
                <td><span class="payment-provider {{ in_array($method,['stripe','paystack'],true)?$method:'other' }}"><i class="{{ $method==='stripe'?'ri-bank-card-line':($method==='paystack'?'ri-secure-payment-line':'ri-wallet-3-line') }}"></i>{{ $method ? ucfirst($method) : 'Unknown' }}</span></td>
                <td><span class="payment-reference" title="{{ $payment->transaction_id ?: 'Reference not recorded' }}">{{ $payment->transaction_id ?: '—' }}</span></td>
                <td>{{ $payment->paid_at?->format('M j, Y · g:i A') ?? 'Not recorded' }}</td>
                <td class="payment-total">£{{ number_format((float)$payment->total,2) }}</td>
                <td><div class="payment-ops">
                    @if($payment->is_manual_payment)
                        <a class="payment-op" href="{{ route('admin.payment.transactions.edit',$payment) }}" title="Edit manual payment"><i class="ri-edit-line"></i><span>Edit</span></a>
                    @else
                        <a class="payment-op" href="{{ route('admin.ecommerce.orders.edit',$payment) }}" title="Edit order details"><i class="ri-edit-line"></i><span>Edit</span></a>
                    @endif
                    <form method="POST" action="{{ route('admin.payment.transactions.destroy',$payment) }}" data-confirm-modal data-confirm-title="Delete this payment record?" data-confirm-message="The order and its payment record will be permanently deleted.">@csrf @method('DELETE')<button class="payment-op danger" type="submit" title="Delete transaction"><i class="ri-delete-bin-line"></i><span>Delete</span></button></form>
                </div></td>
            </tr>
        @empty
            <tr><td colspan="8" class="empty">{{ $isLogs && empty($filters['provider']) ? 'Choose Stripe or Paystack to view payment records.' : 'No paid transactions match these filters.' }}</td></tr>
        @endforelse
        </tbody>
    </table></div>
    <div class="payment-pager"><span class="payment-info">Showing {{ $payments->firstItem()??0 }}–{{ $payments->lastItem()??0 }} of {{ number_format($payments->total()) }} paid records</span>
        <nav aria-label="Payment pages">
            @if($payments->onFirstPage())<span aria-disabled="true">‹</span>@else<a href="{{ $payments->previousPageUrl() }}" rel="prev">‹</a>@endif
            @foreach($payments->getUrlRange(max(1,$payments->currentPage()-2),min($payments->lastPage(),$payments->currentPage()+2)) as $page=>$url)
                @if($page===$payments->currentPage())<span aria-current="page">{{ $page }}</span>@else<a href="{{ $url }}">{{ $page }}</a>@endif
            @endforeach
            @if($payments->hasMorePages())<a href="{{ $payments->nextPageUrl() }}" rel="next">›</a>@else<span aria-disabled="true">›</span>@endif
        </nav>
    </div>
</section>
@endsection
