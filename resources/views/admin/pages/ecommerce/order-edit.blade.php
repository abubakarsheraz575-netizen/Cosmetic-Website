@extends('admin.layouts.ecommerce')

@section('title', 'Edit invoice')

@section('content')
<header class="page-head">
    <div><h1><i class="ri-edit-2-line"></i>Edit invoice</h1><p>Invoice INV-{{ str_pad((string) $order->id, 6, '0', STR_PAD_LEFT) }} · {{ $order->order_number }}</p></div>
    <a class="button" href="{{ route('admin.ecommerce.invoices') }}"><i class="ri-arrow-left-line"></i> Invoices</a>
</header>
<section class="stats">
    <article class="card stat"><span class="stat-icon"><i class="ri-money-pound-circle-line"></i></span><div><strong id="invoiceTotal">£{{ number_format($order->total, 2) }}</strong><span>Invoice total</span></div></article>
    <article class="card stat"><span class="stat-icon amber"><i class="ri-box-3-line"></i></span><div><strong>{{ $order->items->sum('qty') }}</strong><span>Units on invoice</span></div></article>
    <article class="card stat"><span class="stat-icon"><i class="ri-truck-line"></i></span><div><strong>{{ ucfirst($order->status) }}</strong><span>Delivery status</span></div></article>
    <article class="card stat"><span class="stat-icon green"><i class="ri-bank-card-line"></i></span><div><strong>{{ ucfirst($order->payment_status) }}</strong><span>Payment / refund status</span></div></article>
</section>
<form method="POST" action="{{ route('admin.ecommerce.orders.update', $order) }}" id="editInvoiceForm">
    @csrf @method('PATCH')
    <section class="card">
        <h2 class="card-title">Customer and delivery</h2>
        <div class="form-grid">
            <div class="field"><label for="name">Customer name</label><input id="name" name="name" value="{{ old('name', $order->name) }}" maxlength="120" required></div>
            <div class="field"><label for="email">Email</label><input id="email" type="email" name="email" value="{{ old('email', $order->email) }}" maxlength="150" required></div>
            <div class="field"><label for="phone">Phone</label><input id="phone" name="phone" value="{{ old('phone', $order->phone) }}" maxlength="30"></div>
            <div class="field"><label for="address">Address</label><input id="address" name="address" value="{{ old('address', $order->address) }}" maxlength="255" required></div>
            <div class="field"><label for="city">City</label><input id="city" name="city" value="{{ old('city', $order->city) }}" maxlength="100" required></div>
            <div class="field"><label for="postal_code">Postal code</label><input id="postal_code" name="postal_code" value="{{ old('postal_code', $order->postal_code) }}" maxlength="20" required></div>
            <div class="field"><label for="country">Country</label><input id="country" name="country" value="{{ old('country', $order->country) }}" maxlength="100" required></div>
            <div class="field"><label for="shipping">Shipping (£)</label><input id="shipping" type="number" name="shipping" min="0" step="0.01" value="{{ old('shipping', $order->shipping) }}" required></div>
            <div class="field"><label for="status">Shipment status</label><select id="status" name="status" required>@foreach(['pending','processing','shipped','delivered','cancelled'] as $status)<option value="{{ $status }}" @selected(old('status', $order->status) === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
            <div class="field"><label for="payment_status">Payment / invoice status</label><select id="payment_status" name="payment_status" required>@foreach(['pending','paid','failed','refunded'] as $status)<option value="{{ $status }}" @selected(old('payment_status', $order->payment_status) === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
            <div class="field full"><label for="notes">Order notes</label><textarea id="notes" name="notes" maxlength="2000">{{ old('notes', $order->notes) }}</textarea></div>
        </div>
    </section>
    <section class="card" id="invoiceBreakdown" data-discount="{{ $order->discount }}" data-tax-rates="{{ json_encode(collect($order->tax_breakdown ?? [])->map(fn($tax) => ['name' => $tax['name'], 'percentage' => $tax['percentage']])->values()) }}">
        <h2 class="card-title">Recorded financial breakdown</h2>
        <div class="toolbar"><span>Coupon{{ $order->coupon_code ? ' · '.$order->coupon_code : '' }}: £{{ number_format($order->discount, 2) }} discount</span><span>Taxes: £{{ number_format($order->tax_total, 2) }}</span></div>
        @foreach($order->tax_breakdown ?? [] as $tax)
            <small>{{ $tax['name'] }} · {{ $tax['percentage'] }}% (currently £{{ number_format($tax['amount'], 2) }})</small>
        @endforeach
    </section>
    <section class="card">
        <h2 class="card-title">Invoice product lines</h2>
        <p style="color:var(--muted);font-size:12px">Edit names, quantities, or prices; the subtotal and invoice total are recalculated when saved.</p>
        <div class="items-editor">
            @foreach($order->items as $index => $item)
                <div class="item-edit">
                    <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                    <label>Product name<input name="items[{{ $index }}][product_name]" maxlength="255" value="{{ old("items.$index.product_name", $item->product_name) }}" required></label>
                    <label>Quantity<input class="line-qty" type="number" min="1" max="100000" name="items[{{ $index }}][qty]" value="{{ old("items.$index.qty", $item->qty) }}" required></label>
                    <label>Unit price (£)<input class="line-price" type="number" min="0" step="0.01" name="items[{{ $index }}][product_price]" value="{{ old("items.$index.product_price", $item->product_price) }}" required></label>
                    @foreach($item->returns as $return)
                        <span class="badge {{ $return->status }}">Return {{ $return->return_number }} · {{ $return->status }}</span>
                    @endforeach
                </div>
            @endforeach
        </div>
        <div class="actions" style="justify-content:space-between;margin-top:18px">
            <span class="muted">Subtotal updates from product lines; shipping is added at save.</span>
            <button class="button primary" type="submit"><i class="ri-save-line"></i> Save invoice changes</button>
        </div>
    </section>
</form>
@endsection

@push('scripts')
<script>
    (function () {
        const rows = document.querySelectorAll('.item-edit');
        const shipping = document.getElementById('shipping');
        const total = document.getElementById('invoiceTotal');
        function updateTotal() {
            let subtotal = 0;
            rows.forEach(function (row) {
                const qty = Number(row.querySelector('.line-qty').value) || 0;
                const price = Number(row.querySelector('.line-price').value) || 0;
                subtotal += qty * price;
            });
            const delivery = Number(shipping.value) || 0;
            const breakdown = document.getElementById('invoiceBreakdown');
            const discount = Math.min(Number(breakdown.dataset.discount) || 0, subtotal);
            const taxable = Math.max(0, subtotal - discount);
            const taxRates = JSON.parse(breakdown.dataset.taxRates || '[]');
            const taxes = taxRates.reduce(function (sum, tax) {
                return sum + Math.round(taxable * Number(tax.percentage) + Number.EPSILON) / 100;
            }, 0);
            total.textContent = new Intl.NumberFormat('en-GB', { style: 'currency', currency: 'GBP' }).format(taxable + taxes + delivery);
        }
        document.getElementById('editInvoiceForm').addEventListener('input', function (event) {
            if (event.target.matches('.line-qty,.line-price,#shipping')) updateTotal();
        });
    })();
</script>
@endpush
