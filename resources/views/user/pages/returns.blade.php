@include('user.include.base')

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Returns — Cosmetics Store</title>
    @include('user.include.base')
    <style>
        .returns-main { padding-top:30px!important }
        .returns-head { display:flex;align-items:center;justify-content:space-between;gap:14px;margin:0 0 20px }
        .returns-head h1 { color:var(--ink);font-size:23px;font-weight:700 }
        .returns-head p { margin-top:5px;color:var(--txt);font-size:12px }
        .return-stats { display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:20px }
        .return-stat { display:grid;gap:5px;padding:18px;border-radius:16px;background:var(--glass);border:1px solid var(--glass-border) }
        .return-stat strong { color:var(--ink);font-size:23px }
        .return-stat span { color:var(--txt);font-size:11px;text-transform:uppercase;letter-spacing:.06em }
        .return-table { overflow-x:auto;border-radius:18px }
        .return-table table { width:100%;min-width:680px;border-collapse:collapse;background:var(--glass);border:1px solid var(--glass-border) }
        .return-table th,.return-table td { padding:14px;text-align:left;border-bottom:1px solid var(--glass-border);font-size:12px }
        .return-table th { color:var(--txt);font-size:10px;text-transform:uppercase;letter-spacing:.08em }
        .return-table td small { display:block;margin-top:4px;color:var(--txt) }
        .return-status { display:inline-flex;padding:5px 10px;border-radius:18px;background:#fff3d6;color:#9e6900;text-transform:capitalize;font-weight:700 }
        .return-status.approved,.return-status.refunded { background:#dcf7eb;color:#108255 }
        .return-status.rejected { background:#ffe8e8;color:#b83232 }
        .empty-return { padding:36px;text-align:center;color:var(--txt);background:var(--glass);border-radius:18px }
        .eligible-returns { margin:24px 0 30px }
        .eligible-returns h2 { margin:0 0 14px;color:var(--ink);font-size:17px }
        .eligible-order { margin:0 0 14px;padding:18px;border:1px solid var(--glass-border);border-radius:16px;background:var(--glass) }
        .eligible-order.target-order { border-color:rgba(56,170,180,.72);box-shadow:0 0 0 4px rgba(56,170,180,.1);scroll-margin-top:24px; }
        .target-order-note { margin:0 0 12px;padding:10px 12px;border-radius:10px;background:rgba(56,170,180,.1);color:var(--teal-2);font-size:12px;font-weight:600; }
        .eligible-order h3 { margin:0 0 10px;color:var(--ink);font-size:14px }
        .eligible-item { display:grid;grid-template-columns:minmax(0,1fr) minmax(240px,1fr);gap:16px;align-items:start;padding:14px 0;border-top:1px solid var(--glass-border) }
        .eligible-item b { color:var(--ink);font-size:13px }
        .eligible-item small { display:block;margin-top:5px;color:var(--txt);font-size:11px }
        .eligible-item form { display:grid;gap:9px }
        .eligible-item label { display:grid;gap:5px;color:var(--txt);font-size:11px }
        .eligible-item input,.eligible-item textarea { width:100%;padding:9px 10px;border:1px solid var(--glass-border);border-radius:9px;background:#fff;color:var(--ink);font:inherit;font-size:12px }
        .eligible-item textarea { min-height:70px;resize:vertical }
        .eligible-item button { justify-self:start;padding:9px 13px;border:0;border-radius:9px;color:#fff;background:linear-gradient(135deg,var(--teal),var(--teal-2));font-weight:600;cursor:pointer }
        .active-return { display:inline-flex;margin-top:8px;color:var(--teal-2);font-size:12px;font-weight:600 }
        .eligible-empty { padding:18px;border-radius:14px;background:var(--glass);color:var(--txt) }
        @media(max-width:600px) { .eligible-item { grid-template-columns:1fr } }
        @media(max-width:780px) { .return-stats { grid-template-columns:repeat(2,minmax(0,1fr)) } }
        @media(max-width:480px) { .return-stats { grid-template-columns:1fr 1fr;gap:8px } .return-stat { padding:14px } .return-stat strong { font-size:19px } }
    </style>
</head>
<body>
    <div class="scene" aria-hidden="true"><span class="orb orb-1"></span><span class="orb orb-2"></span><span class="orb orb-3"></span></div>
    <div class="layout">
        @include('user.include.sidebar')
        <main class="main returns-main">
            <header class="returns-head"><div><h1>My Returns</h1><p>Request returns for paid, delivered products and track their review.</p></div></header>
            @if(session('success'))<p role="status" style="padding:12px;border-radius:12px;background:#dcf7eb;color:#126d4a">{{ session('success') }}</p>@endif
            @if($errors->any())<div role="alert" style="padding:12px;margin:12px 0;border-radius:12px;background:#ffe8e8;color:#9b2525"><ul style="margin:0;padding-left:20px">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <section class="return-stats">
                <article class="return-stat"><strong>{{ $metrics['total'] }}</strong><span>Total requests</span></article>
                <article class="return-stat"><strong>{{ $metrics['pending'] }}</strong><span>Awaiting review</span></article>
                <article class="return-stat"><strong>{{ $metrics['approved'] }}</strong><span>Approved</span></article>
                <article class="return-stat"><strong>{{ $metrics['refunded'] }}</strong><span>Refunded</span></article>
            </section>
            <section class="eligible-returns" aria-labelledby="eligible-returns-title">
                <h2 id="eligible-returns-title">Request a return</h2>
                @php $hasEligibleItem = false; @endphp
                @foreach($eligibleOrders as $order)
                    @php
                        $returnableItems = $order->items->filter(fn ($item) => ! $item->returns->contains(fn ($return) => in_array($return->status, ['pending', 'approved', 'refunded'], true)));
                    @endphp
                    @if($returnableItems->isNotEmpty())
                        @php $hasEligibleItem = true; @endphp
                        <article class="eligible-order {{ $targetOrder?->id === $order->id ? 'target-order' : '' }}" id="return-order-{{ $order->id }}">
                            <h3>Order {{ $order->order_number }}</h3>
                            @if($targetOrder?->id === $order->id)<p class="target-order-note">Choose a delivered item below, then tell us why you’re returning it.</p>@endif
                            @foreach($returnableItems as $item)
                                <div class="eligible-item">
                                    <div style="display:flex;align-items:center;gap:11px">
                                        <span style="width:58px;height:58px;flex:none;display:grid;place-items:center;overflow:hidden;border:1px solid var(--glass-border);border-radius:12px;background:#fff;font-size:20px">
                                            @if($item->product?->images->first())<img src="{{ asset('storage/'.$item->product->images->first()->path) }}" alt="{{ $item->product_name }}" style="width:100%;height:100%;object-fit:cover" loading="lazy">@else{{ $item->emoji ?: '✦' }}@endif
                                        </span>
                                        <span><b>{{ $item->product_name }}</b><small>Quantity {{ $item->qty }} · £{{ number_format($item->line_total, 2) }}</small></span>
                                    </div>
                                    <form method="POST" action="{{ route('user.orders.returns.store', [$order, $item]) }}">
                                        @csrf
                                        <label>Quantity to return
                                            <input type="number" name="quantity" min="1" max="{{ $item->qty }}" value="{{ old('quantity', 1) }}" required>
                                        </label>
                                        <label>Reason for return
                                            <textarea name="reason" minlength="5" maxlength="2000" required placeholder="Tell us why you are returning this product.">{{ old('reason') }}</textarea>
                                        </label>
                                        <button type="submit">Submit return request</button>
                                    </form>
                                </div>
                            @endforeach
                        </article>
                    @endif
                @endforeach
                @unless($hasEligibleItem)
                    <div class="eligible-empty">No delivered paid products are currently eligible for a new return request.</div>
                @endunless
            </section>
            @if($returns->isEmpty())
                <div class="empty-return"><h2>No return requests</h2><p>When you request a return from a delivered order, its progress appears here.</p><a href="{{ route('user.orders') }}">View my orders</a></div>
            @else
                <div class="return-table"><table><thead><tr><th>Return</th><th>Order</th><th>Product</th><th>Qty</th><th>Your reason</th><th>Status</th><th>Requested</th><th>Admin note</th></tr></thead><tbody>
                    @foreach($returns as $return)
                        <tr><td>{{ $return->return_number }}</td><td>{{ $return->order->order_number }}</td><td>{{ $return->item->product_name }}</td><td>{{ $return->quantity }}</td><td>{{ \Illuminate\Support\Str::limit($return->reason, 90) }}</td><td><span class="return-status {{ $return->status }}">{{ $return->status }}</span></td><td>{{ $return->created_at->format('M d, Y') }}</td><td>{{ $return->admin_note ?: '—' }}</td></tr>
                    @endforeach
                </tbody></table></div>
            @endif
        </main>
    </div>
</body>
</html>
