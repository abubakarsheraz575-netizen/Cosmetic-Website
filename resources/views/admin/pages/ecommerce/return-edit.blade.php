@extends('admin.layouts.ecommerce')

@section('title', 'Review return')

@section('content')
<header class="page-head">
    <div><h1><i class="ri-edit-2-line"></i>Review return {{ $returnRequest->return_number }}</h1><p>View the customer’s reason and record the decision.</p></div>
    <a class="button" href="{{ route('admin.ecommerce.returns') }}"><i class="ri-arrow-left-line"></i> All returns</a>
</header>
<section class="stats">
    <article class="card stat"><span class="stat-icon"><i class="ri-shopping-bag-3-line"></i></span><div><strong>{{ $returnRequest->order->order_number }}</strong><span>Order</span></div></article>
    <article class="card stat"><span class="stat-icon amber"><i class="ri-box-3-line"></i></span><div><strong>{{ $returnRequest->item->product_name }}</strong><span>Product · Qty {{ $returnRequest->quantity }}</span></div></article>
    <article class="card stat"><span class="stat-icon green"><i class="ri-user-3-line"></i></span><div><strong>{{ $returnRequest->user->name }}</strong><span>{{ $returnRequest->user->email }}</span></div></article>
    <article class="card stat"><span class="stat-icon violet"><i class="ri-time-line"></i></span><div><strong class="badge {{ $returnRequest->status }}">{{ $returnRequest->status }}</strong><span>Current status</span></div></article>
</section>
<section class="card">
    <h2 class="card-title">Customer’s reason</h2>
    <p style="line-height:1.75;white-space:pre-wrap">{{ $returnRequest->reason }}</p>
    <form method="POST" action="{{ route('admin.ecommerce.returns.update', $returnRequest) }}">
        @csrf @method('PATCH')
        <div class="form-grid">
            <div class="field"><label for="status">Decision</label>
                <select id="status" name="status" required>
                    @foreach(['pending' => 'Pending review', 'approved' => 'Approved', 'rejected' => 'Rejected', 'refunded' => 'Refunded'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $returnRequest->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field full"><label for="admin_note">Note for the return record</label><textarea id="admin_note" name="admin_note" maxlength="2000">{{ old('admin_note', $returnRequest->admin_note) }}</textarea></div>
        </div>
        <div class="actions" style="margin-top:16px"><button class="button primary" type="submit"><i class="ri-save-line"></i> Save decision</button></div>
    </form>
</section>
@endsection
