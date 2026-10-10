@include('front-end.include.header')

@include('front-end.include.loader')
<main>
    <section class="ok-sec">
        <div class="container">
            <div class="ok-card">
                <div class="ok-circle"><svg viewBox="0 0 52 52"><circle class="ok-circ" cx="26" cy="26" r="24" fill="none"/><path class="ok-check" fill="none" d="M14 27l8 8 16-16"/></svg></div>

                <h1>Thank You, {{ $order->name }}!</h1>
                <p class="ok-sub">Your order has been placed and payment was successful.</p>

                <div class="ok-meta">
                    <div><span>Order Number</span><b>{{ $order->order_number }}</b></div>
                    <div><span>Payment</span><b>{{ ucfirst($order->payment_method) }} · Paid ✅</b></div>
                    <div><span>Total Paid</span><b>£{{ number_format($order->total, 2) }}</b></div>
                </div>

                <div class="ok-eta">
                    <span class="ok-truck">🚚</span>
                    <div>
                        <b>Estimated Delivery: 5–7 business days</b>
                        <p>Expected between <b>{{ $order->created_at->copy()->addDays(5)->format('D, M j') }}</b> and <b>{{ $order->created_at->copy()->addDays(7)->format('D, M j') }}</b> — we'll email tracking to {{ $order->email }}.</p>
                    </div>
                </div>

                <div class="ok-items">
                    @foreach ($order->items as $it)
                    <div class="ok-item">
                        <div class="ok-thumb">
                            @if ($it->product && $it->product->images->first())
                            <img src="{{ asset('storage/' . $it->product->images->first()->path) }}" alt="{{ $it->product_name }}">
                            @else
                            <span>{{ $it->emoji ?: '🧴' }}</span>
                            @endif
                        </div>
                        <div>
                            <b>{{ $it->product_name }}</b>
                            @if($it->variant_options)
                                <span>{{ collect($it->variant_options)->pluck('value')->implode(', ') }}</span>
                            @endif
                            <span>Qty {{ $it->qty }} × £{{ number_format($it->product_price, 2) }}</span>
                        </div>
                        <b>£{{ number_format($it->line_total, 2) }}</b>
                    </div>
                    @endforeach
                    <div class="ok-tot"><span>Subtotal</span><span>£{{ number_format($order->subtotal, 2) }}</span></div>
                    @if($order->discount > 0)
                    <div class="ok-tot"><span>Discount{{ $order->coupon_code ? ' ('.$order->coupon_code.')' : '' }}</span><span>−£{{ number_format($order->discount, 2) }}</span></div>
                    @endif
                    <div class="ok-tot"><span>Shipping</span><span>{{ $order->shipping <= 0 ? 'FREE' : '£' . number_format($order->shipping, 2) }}</span></div>
                    @foreach($order->tax_breakdown ?? [] as $tax)
                    <div class="ok-tot"><span>{{ $tax['name'] }} ({{ rtrim(rtrim(number_format($tax['percentage'], 4), '0'), '.') }}%)</span><span>£{{ number_format($tax['amount'], 2) }}</span></div>
                    @endforeach
                    <div class="ok-tot grand"><span>Total</span><b>£{{ number_format($order->total, 2) }}</b></div>
                </div>

                <div class="ok-btns">
                    <a href="{{ route('shop') }}" class="btn btn-teal">Continue Shopping</a>
                    <a href="/" class="btn btn-outline-dark">Back Home</a>
                </div>
            </div>
        </div>
    </section>
</main>

@include('front-end.include.footer')
@include('front-end.include.cart-drawer')
@include('front-end.include.js')

<style>
    .site-header{position:sticky;top:0;background:#fff;box-shadow:0 2px 20px rgba(0,0,0,.07);z-index:1000}
    .site-header .logo,.site-header .cart-btn{color:#1b1d21}
    .nav-toggle span{background:#1b1d21}
    @media (min-width: 992px){
        .site-header .main-nav a{color:#5b6066}
        .site-header .main-nav a:hover,.site-header .main-nav a.active{color:#0d3b40}
        .site-header .main-nav a::after{background:var(--teal)}
    }
    .ok-sec{padding:70px 0 95px;background:var(--bg)}
    .ok-card{max-width:680px;margin:0 auto;background:#fff;box-shadow:0 24px 70px rgba(0,0,0,.08);padding:50px 44px;text-align:center}
    .ok-circle{width:96px;height:96px;margin:0 auto 22px}
    .ok-circ{stroke:var(--teal);stroke-width:3;stroke-dasharray:160;stroke-dashoffset:160;animation:okDraw .8s ease forwards}
    .ok-check{stroke:var(--teal);stroke-width:4;stroke-linecap:round;stroke-linejoin:round;stroke-dasharray:48;stroke-dashoffset:48;animation:okDraw .5s ease .55s forwards}
    @keyframes okDraw{to{stroke-dashoffset:0}}
    .ok-card h1{font:300 clamp(26px,4vw,36px) 'Montserrat',sans-serif;text-transform:uppercase;color:var(--dark)}
    .ok-sub{color:#9aa0a4;margin:8px 0 26px}
    .ok-meta{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:22px}
    .ok-meta div{background:#f7fafa;border:1px solid #eef1f2;padding:14px}
    .ok-meta span{display:block;font-size:10.5px;text-transform:uppercase;letter-spacing:1.2px;color:#9aa0a4;margin-bottom:3px}
    .ok-meta b{font:700 13.5px 'Montserrat',sans-serif;color:#0d3b40}
    .ok-eta{display:flex;gap:16px;align-items:center;text-align:left;background:linear-gradient(120deg,#0d3b40,#155e57);color:#fff;padding:18px 22px;margin-bottom:26px}
    .ok-truck{font-size:28px}
    .ok-eta b{font:600 14px 'Montserrat',sans-serif;display:block}
    .ok-eta p{font-size:12.5px;color:rgba(255,255,255,.8);margin-top:3px}
    .ok-items{text-align:left;border-top:1px solid #eef1f2;padding-top:8px}
    .ok-item{display:flex;align-items:center;gap:14px;padding:12px 0;border-bottom:1px dashed #eef1f2}
    .ok-thumb{width:54px;height:60px;min-width:54px;background:#f4f6f6;overflow:hidden}
    .ok-thumb img{width:100%;height:100%;object-fit:cover}
    .ok-thumb span{width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:24px}
    .ok-item>div{flex:1}
    .ok-item b{display:block;font:600 13px 'Montserrat',sans-serif;color:var(--dark)}
    .ok-item span{font-size:12px;color:#9aa0a4}
    .ok-tot{display:flex;justify-content:space-between;font-size:13.5px;color:#666;padding:8px 0}
    .ok-tot.grand{border-top:1px solid #eef1f2;margin-top:6px;padding-top:14px;align-items:center}
    .ok-tot.grand span{font:600 14px 'Montserrat',sans-serif;color:var(--dark);text-transform:uppercase}
    .ok-tot.grand b{font:700 24px 'Montserrat',sans-serif;color:#0d3b40}
    .ok-btns{display:flex;gap:14px;justify-content:center;margin-top:30px;flex-wrap:wrap}
    .btn-outline-dark{border-color:#1b1d21;color:#1b1d21}
    .btn-outline-dark:hover{background:#1b1d21;color:#fff}
    @media (max-width:575px){ .ok-meta{grid-template-columns:1fr} .ok-card{padding:34px 22px} }
</style>

</body>

</html>