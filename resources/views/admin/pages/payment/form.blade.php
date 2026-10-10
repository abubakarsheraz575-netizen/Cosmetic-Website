@extends('admin.layouts.ecommerce')

@section('title', $title)

@section('content')
<style>
    .payment-form-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}.payment-form-head h1{display:flex;align-items:center;gap:10px;margin:0;font-size:24px}.payment-form-head p{margin:5px 0 0;color:var(--muted);font-size:12px}.payment-form-grid{display:grid;grid-template-columns:minmax(0,1fr) 300px;gap:16px;align-items:start}.payment-form-card{min-width:0;padding:20px;border:1px solid var(--border);border-radius:15px;background:var(--card);box-shadow:var(--shadow)}.payment-form-card h2{margin:0 0 14px;font-size:15px}.payment-fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.payment-field{display:grid;gap:6px;min-width:0}.payment-field.full{grid-column:1/-1}.payment-field label{font-size:11px;font-weight:800}.payment-field input,.payment-field select,.payment-field textarea{width:100%;min-height:41px;padding:10px 11px;border:1px solid var(--border);border-radius:9px;background:var(--bg);color:var(--text);font:inherit;font-size:12px}.payment-field textarea{min-height:85px;resize:vertical}.payment-hint{color:var(--muted);font-size:10px;line-height:1.55}.payment-form-aside{display:grid;gap:14px}.payment-warning{padding:12px;border:1px solid #f2d9a5;border-radius:10px;background:#fff8e7;color:#79570f;font-size:11px;line-height:1.55}.manual-product-preview{display:flex;align-items:center;gap:10px;padding:10px;border:1px solid var(--border);border-radius:10px;background:var(--bg)}.manual-product-preview img{width:44px;height:44px;object-fit:cover;border-radius:8px}.manual-product-preview span{color:var(--muted);font-size:11px}.payment-save-actions{display:flex;justify-content:flex-end;gap:8px;flex-wrap:wrap;margin-top:15px}.payment-error-list{padding:12px;border-radius:10px;background:#fff0f0;color:#a52b40;font-size:12px}
    @media(max-width:850px){.payment-form-grid{grid-template-columns:1fr}.payment-form-aside{grid-template-columns:1fr 1fr}}@media(max-width:560px){.payment-form-card{padding:15px}.payment-fields,.payment-form-aside{grid-template-columns:1fr}.payment-field.full{grid-column:auto}.payment-form-head h1{font-size:21px}}
</style>
<header class="page-head payment-form-head"><div><h1><i class="ri-bank-card-line"></i>{{ $title }}</h1><p>Record an offline/manual payment that has already been received.</p></div><a href="{{ route('admin.payment.transactions') }}" class="button"><i class="ri-arrow-left-line"></i>Back to transactions</a></header>
@if($errors->any())<div class="payment-error-list" role="alert"><strong>Please correct the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ $order->exists ? route('admin.payment.transactions.update',$order) : route('admin.payment.transactions.store') }}">
    @csrf @if($order->exists) @method('PATCH') @endif
    <div class="payment-form-grid">
        <section class="payment-form-card">
            <h2>Payer and payment details</h2>
            <div class="payment-fields">
                <div class="payment-field full"><label for="paymentUser">Link to customer/vendor account (optional)</label><select id="paymentUser" name="user_id"><option value="">Guest / no account linked</option>@foreach($customers as $customer)<option value="{{ $customer->id }}" @selected((string)old('user_id',$order->user_id)===(string)$customer->id)>{{ $customer->name }} — {{ $customer->email }}</option>@endforeach</select><span class="payment-hint">Vendor accounts are detected from their submitted products.</span></div>
                <div class="payment-field"><label for="paymentName">Payer name *</label><input id="paymentName" name="name" maxlength="120" required value="{{ old('name',$order->name) }}"></div>
                <div class="payment-field"><label for="paymentEmail">Payer email *</label><input id="paymentEmail" type="email" name="email" maxlength="150" required value="{{ old('email',$order->email) }}"></div>
                <div class="payment-field"><label for="paymentPhone">Phone</label><input id="paymentPhone" name="phone" maxlength="30" value="{{ old('phone',$order->phone) }}"></div>
                <div class="payment-field"><label for="paymentAmount">Amount received (£) *</label><input id="paymentAmount" type="number" name="amount" min="0.01" max="99999999.99" step="0.01" required value="{{ old('amount',$order->exists?$order->total:'') }}"></div>
                <div class="payment-field"><label for="paymentReference">Manual receipt/reference</label><input id="paymentReference" name="transaction_id" maxlength="255" value="{{ old('transaction_id',$order->exists?$order->transaction_id:'') }}" placeholder="Receipt number or bank reference"></div>
                <div class="payment-field"><label for="paidAt">Date received</label><input id="paidAt" type="datetime-local" name="paid_at" value="{{ old('paid_at',$order->paid_at?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i')) }}"></div>
                <div class="payment-field"><label for="paymentProduct">Related product (optional)</label><select id="paymentProduct" name="product_id"><option value="">Use description instead</option>@foreach($products as $product)<option value="{{ $product->id }}" data-image="{{ $product->images->first()?asset('storage/'.$product->images->first()->path):'' }}" @selected((string)old('product_id',$order->items->first()?->product_id)===(string)$product->id)>{{ $product->name }}</option>@endforeach</select></div>
                <div class="payment-field"><label for="paymentDescription">Payment description</label><input id="paymentDescription" name="description" maxlength="255" value="{{ old('description',$order->items->first()?->product_name) }}" placeholder="e.g. In-store product payment"></div>
                <div class="payment-field full"><label for="paymentNote">Internal note</label><textarea id="paymentNote" name="payment_note" maxlength="2000" placeholder="Optional note for administrators">{{ old('payment_note',$order->payment_note) }}</textarea></div>
            </div>
            <div class="payment-save-actions"><a class="button" href="{{ route('admin.payment.transactions') }}">Cancel</a><button type="submit" class="button primary"><i class="ri-save-line"></i>{{ $order->exists?'Save transaction':'Record paid transaction' }}</button></div>
        </section>
        <aside class="payment-form-aside">
            <section class="payment-form-card"><h2>Manual payment entry</h2><div class="payment-warning"><i class="ri-alert-line"></i> This records a payment you have already received outside checkout. It does not contact Stripe or Paystack and must not be used to claim an unverified gateway payment.</div></section>
            <section class="payment-form-card"><h2>Product image preview</h2><div class="manual-product-preview" id="productPreview">@if($order->items->first()?->product?->images?->first())<img src="{{ asset('storage/'.$order->items->first()->product->images->first()->path) }}" alt="">@else<span>Select a product to see its image</span>@endif</div></section>
            @if($order->exists)<section class="payment-form-card"><h2>Record details</h2><span class="payment-hint">{{ $order->order_number }}<br>Manual entry · Created {{ $order->created_at->format('M j, Y') }}</span></section>@endif
        </aside>
    </div>
</form>
@endsection

@push('scripts')
<script>
(()=>{const select=document.getElementById('paymentProduct'),preview=document.getElementById('productPreview');select.addEventListener('change',()=>{const option=select.selectedOptions[0],image=option.dataset.image;preview.replaceChildren();if(image){const img=document.createElement('img');img.src=image;img.alt='';preview.appendChild(img)}const label=document.createElement('span');label.textContent=option.value?option.textContent:'Select a product to see its image';preview.appendChild(label)})})();
</script>
@endpush
