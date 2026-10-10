@extends('admin.layouts.ecommerce')

@section('title', 'Vendor withdrawals')

@section('content')
<style>
    .withdrawal-table{min-width:1320px}.withdrawal-vendor strong,.withdrawal-vendor small{display:block}.withdrawal-vendor small{margin-top:3px;color:var(--muted);font-size:11px}
    .withdrawal-amount{font-size:14px;font-weight:800;font-variant-numeric:tabular-nums;white-space:nowrap}
    .payout-details{max-width:220px;overflow-wrap:anywhere;color:var(--muted);font-size:11px}
    .withdrawal-products{max-width:190px;color:var(--muted);font-size:10px;line-height:1.55}
    .withdrawal-transaction{max-width:200px;overflow-wrap:anywhere;color:var(--muted);font:10px ui-monospace,Consolas,monospace}
    .withdrawal-proof{display:inline-flex;align-items:center;gap:5px;color:#087a54;font-size:10px;font-weight:800}
    .withdrawal-unverified{color:#ad5b00;font-size:10px;font-weight:700}
    .withdrawal-actions{display:flex;gap:6px;align-items:center;flex-wrap:wrap}.withdrawal-actions .button{padding:8px 10px;font-size:11px}
    .withdrawal-action{padding:8px 10px;border:0;border-radius:8px;background:#0e7f69;color:#fff;font:inherit;font-size:11px;font-weight:700;cursor:pointer}
    .withdrawal-action.reject,.danger-button{padding:8px 10px;border:1px solid #ffdada;border-radius:9px;background:#fff1f1;color:#b83232;cursor:pointer}
    .withdrawal-action.complete{background:#176c9a}
    .withdrawal-pagination{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-top:14px}
    .withdrawal-pagination nav{display:flex;gap:5px}.withdrawal-pagination nav a,.withdrawal-pagination nav span{display:grid;place-items:center;min-width:34px;height:34px;padding:0 9px;border:1px solid var(--border);border-radius:8px;color:var(--text);text-decoration:none;font-size:12px}.withdrawal-pagination nav span[aria-current]{background:var(--brand);border-color:var(--brand);color:#fff}
    .withdrawal-note{padding:12px 14px;border:1px solid #cce8f4;border-radius:11px;background:#eff9fd;color:#315b6d;font-size:12px}
    .withdrawal-tabs{display:flex;gap:8px;overflow-x:auto;padding-bottom:5px}.withdrawal-tabs a{display:inline-flex;align-items:center;gap:7px;flex:none;padding:9px 13px;border:1px solid var(--border);border-radius:10px;background:var(--card);color:var(--muted);font-size:11px;font-weight:700;text-decoration:none}.withdrawal-tabs a.active{border-color:var(--brand);background:var(--brand);color:#fff}.withdrawal-tabs b{font-size:10px}
    .commission-form{display:flex;align-items:end;gap:12px;flex-wrap:wrap}.commission-form label{display:grid;gap:6px;color:var(--muted);font-size:11px;font-weight:700}.commission-form input{width:160px;min-height:40px;padding:9px 11px;border:1px solid var(--border);border-radius:9px;background:var(--card);color:var(--text);font:inherit}    .commission-form button{min-height:40px;padding:0 14px;border:0;border-radius:9px;background:var(--brand);color:#fff;font:inherit;font-size:11px;font-weight:700;cursor:pointer}.withdrawal-section-head{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:14px}
    .withdrawal-confirm{width:min(500px,calc(100vw - 28px));padding:26px;border:1px solid var(--border);border-radius:18px;background:var(--card);color:var(--text);text-align:center}.withdrawal-confirm::backdrop{background:rgba(8,18,33,.48);backdrop-filter:blur(4px)}
    .withdrawal-confirm h2{margin:0}.withdrawal-confirm p{color:var(--muted);line-height:1.6}.withdrawal-confirm button{padding:10px 14px;border:0;border-radius:9px;background:#df3535;color:white;font:inherit;font-weight:700}
    @media(max-width:700px){.withdrawal-section-head{align-items:stretch}.commission-form{align-items:stretch}.commission-form label,.commission-form input,.commission-form button{width:100%}}
</style>
<header class="page-head"><div><h1><i class="ri-bank-card-line"></i>Vendor withdrawals</h1><p>Review payout requests submitted by marketplace vendors.</p></div></header>
@if(session('success'))<div class="flash-success" role="status">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
<div class="withdrawal-note"><strong>Payment verification:</strong> seller payouts are linked to customer orders marked paid by Stripe or Paystack, with the transaction reference stored for review. Accepting a request moves it to processing; mark it completed only after the payout is sent.</div>
<section class="card">
    <div class="withdrawal-section-head"><div><h2 class="card-title" style="margin:0">Marketplace commission</h2><span class="count-note">Current rate: {{ number_format($commissionRate, 2) }}% · saved on each payout request</span></div>
        <form class="commission-form" method="POST" action="{{ route('admin.marketplace.withdrawals.commission') }}">@csrf @method('PATCH')
            <label for="commissionRate">Commission for new requests (%)<input id="commissionRate" type="number" name="commission_rate" min="0" max="100" step="0.01" value="{{ old('commission_rate', number_format($commissionRate, 2, '.', '')) }}" required></label>
            <button type="submit">Save commission</button>
        </form>
    </div>
</section>
<section class="card">
    <div class="withdrawal-section-head"><div><h2 class="card-title" style="margin:0">Payout requests</h2><span class="count-note">{{ number_format($withdrawals->total()) }} requests</span></div></div>
    <nav class="withdrawal-tabs" aria-label="Filter payout requests">
        @foreach(['all' => 'All', 'pending' => 'Pending', 'processing' => 'Accepted / processing', 'completed' => 'Paid', 'rejected' => 'Rejected'] as $key => $label)
            @php
                $count = $key === 'all' ? $withdrawals->total() : (int) ($counts[$key] ?? 0);
            @endphp
            <a href="{{ route('admin.marketplace.withdrawals', $key === 'all' ? [] : ['status' => $key]) }}" class="{{ (($filters['status'] ?? 'all') === $key) ? 'active' : '' }}" @if(($filters['status'] ?? 'all') === $key) aria-current="page" @endif>{{ $label }} <b>{{ number_format($count) }}</b></a>
        @endforeach
    </nav>
    <div class="table-wrap"><table class="withdrawal-table" data-server-pagination="true"><thead><tr><th>Vendor</th><th>Verified sale</th><th>Products</th><th>Net sale</th><th>Commission</th><th>Payout</th><th>Payout destination</th><th>Customer payment transaction</th><th>Requested</th><th>Status</th><th>Operations</th></tr></thead><tbody>
        @forelse($withdrawals as $withdrawal)
            @php
                $verifiedOrder = $withdrawal->order
                    && $withdrawal->order->payment_status === 'paid'
                    && $withdrawal->order->paid_at
                    && $withdrawal->order->transaction_id
                    && hash_equals((string) $withdrawal->payment_transaction_id, (string) $withdrawal->order->transaction_id);
                $vendorProducts = $withdrawal->order?->items
                    ? $withdrawal->order->items->filter(fn ($item) => (int) ($item->product?->submitted_by ?? 0) === (int) $withdrawal->vendor_id)->map(fn ($item) => $item->product_name.' × '.$item->qty)->implode(', ')
                    : '';
            @endphp
            <tr>
                <td class="withdrawal-vendor"><strong>{{ $withdrawal->vendor?->name ?? 'Vendor account removed' }}</strong><small>{{ $withdrawal->vendor?->email ?? '' }}</small></td>
                <td>@if($verifiedOrder)<strong>{{ $withdrawal->order_number }}</strong><small class="withdrawal-proof"><i class="ri-shield-check-line"></i> Customer payment verified</small><small>{{ $withdrawal->order_paid_at?->format('M j, Y H:i') }}</small>@else<span class="withdrawal-unverified">No verified paid order</span>@endif</td>
                <td class="withdrawal-products">{{ $vendorProducts ?: 'Legacy request' }}</td>
                <td class="withdrawal-amount">@if($withdrawal->sales_amount !== null)£{{ number_format((float) $withdrawal->sales_amount, 2) }}@else—@endif</td>
                <td>@if($withdrawal->commission_rate !== null){{ number_format((float) $withdrawal->commission_rate, 2) }}%<small class="withdrawal-vendor">£{{ number_format((float) $withdrawal->commission_amount, 2) }}</small>@else—@endif</td>
                <td class="withdrawal-amount">£{{ number_format((float)$withdrawal->amount,2) }}</td>
                <td><strong>{{ $withdrawal->payout_method }}</strong><div class="payout-details">{{ $withdrawal->payout_details }}</div></td>
                <td class="withdrawal-transaction">{{ $withdrawal->payment_transaction_id ?? 'Not linked' }}</td>
                <td>{{ $withdrawal->created_at?->format('M j, Y') }}</td>
                <td><span class="status {{ $withdrawal->status }}">{{ ucfirst($withdrawal->status) }}</span></td>
                <td><div class="withdrawal-actions">
                    @if($withdrawal->status === 'pending' && $verifiedOrder)
                        <form method="POST" action="{{ route('admin.marketplace.withdrawals.update',$withdrawal) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="processing"><button class="withdrawal-action" type="submit">Accept</button></form>
                    @elseif($withdrawal->status === 'processing' && $verifiedOrder)
                        <form method="POST" action="{{ route('admin.marketplace.withdrawals.update',$withdrawal) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="completed"><button class="withdrawal-action complete" type="submit">Mark paid</button></form>
                    @endif
                    @if(in_array($withdrawal->status, ['pending', 'processing'], true))
                        <form method="POST" action="{{ route('admin.marketplace.withdrawals.update',$withdrawal) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="rejected"><button class="withdrawal-action reject" type="submit">Reject</button></form>
                    @endif
                    @if($withdrawal->status !== 'completed')
                        <form method="POST" action="{{ route('admin.marketplace.withdrawals.destroy',$withdrawal) }}" data-delete-withdrawal>@csrf @method('DELETE')<button type="submit" class="danger-button" aria-label="Delete withdrawal request {{ $withdrawal->id }}"><i class="ri-delete-bin-line"></i></button></form>
                    @endif
                </div></td>
            </tr>
        @empty<tr><td colspan="11" class="empty">No payout requests in this tab.</td></tr>@endforelse
    </tbody></table></div>
    <div class="withdrawal-pagination"><span class="count-note">Showing {{ $withdrawals->firstItem() ?? 0 }}–{{ $withdrawals->lastItem() ?? 0 }} of {{ number_format($withdrawals->total()) }}</span>{{ $withdrawals->links() }}</div>
</section>
<dialog class="withdrawal-confirm" id="withdrawalConfirm"><h2>Delete withdrawal request?</h2><p>This request will be permanently removed.</p><button type="button" id="withdrawalDeleteConfirm">Delete request</button></dialog>
@push('scripts')
<script>
(() => {let pending=null;const dialog=document.getElementById('withdrawalConfirm');document.querySelectorAll('[data-delete-withdrawal]').forEach(form=>form.addEventListener('submit',event=>{if(form.dataset.confirmed==='true')return;event.preventDefault();pending=form;dialog.showModal()}));document.getElementById('withdrawalDeleteConfirm').addEventListener('click',()=>{if(!pending)return;pending.dataset.confirmed='true';dialog.close();pending.requestSubmit()});dialog.addEventListener('close',()=>{pending=null})})();
</script>
@endpush
@endsection
