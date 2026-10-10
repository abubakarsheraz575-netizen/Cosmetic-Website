@include('front-end.include.header')

@include('front-end.include.loader')

@php
    $paystackKey   = $paystackKey ?? '';
    $paystackReady = $paystackReady ?? ($paystackKey !== '');
    $defaultMethod = ($stripeReady ?? false) ? 'stripe' : ($paystackReady ? 'paystack' : 'stripe');
    /* ⭐ dynamic shipping — controller se na aaye to defaults */
    $shipFreeOver = $shipFreeOver ?? 50;
    $shipCost     = $shipCost ?? 4.99;
    $delMin       = $delMin ?? 5;
    $delMax       = $delMax ?? 7;
@endphp
<link rel="shortcut icon" href="{{ asset('favicon.png') }}?v=3">
<main>
    <!-- HERO BAND -->
    <section class="ck-hero">
        <div class="container">
            <p class="crumbs"><a href="/">Home</a><span>/</span><a href="{{ route('shop') }}">Shop</a><span>/</span><b>Checkout</b></p>
            <h1 class="ck-title">Secure <span>Checkout</span></h1>
        </div>
    </section>

    <section class="ck-body">
        <div class="container ck-layout">

            <!-- ================= LEFT ================= -->
            <form class="ck-left" id="ckForm" onsubmit="placeOrder(event)">

                <!-- Delivery details -->
                <div class="ck-card">
                    <div class="ck-card-head">
                        <span class="ck-step">1</span>
                        <div>
                            <h3>Delivery Details</h3>
                            <p>Where should we send your glow? Your signed-in account is required to place this order.</p>
                        </div>
                    </div>

                    <div class="fld-grid">
                        <div class="fld"><label>Full Name *</label><input type="text" name="name" required value="{{ auth()->user()->name ?? '' }}" placeholder="Jane Doe"></div>
                        <div class="fld"><label>Email *</label><input type="email" name="email" required value="{{ auth()->user()->email ?? '' }}" placeholder="jane@example.com"></div>
                        <div class="fld"><label>Phone *</label><input type="tel" name="phone" required placeholder="+44 7700 900000"></div>
                        <div class="fld full"><label>Street Address *</label><input type="text" name="address" required placeholder="123 Glow Street"></div>
                        <div class="fld"><label>City *</label><input type="text" name="city" required placeholder="London"></div>
                        <div class="fld"><label>Postal Code *</label><input type="text" name="postal_code" required placeholder="SW1A 1AA"></div>
                        <div class="fld full">
                            <label>Country *</label>
                            <select name="country" required>
                                <option value="United Kingdom" selected>United Kingdom</option>
                                <option value="United States">United States</option>
                                <option value="Pakistan">Pakistan</option>
                                <option value="Canada">Canada</option>
                                <option value="Australia">Australia</option>
                                <option value="Germany">Germany</option>
                                <option value="France">France</option>
                                <option value="United Arab Emirates">United Arab Emirates</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="fld full"><label>Order Notes (optional)</label><textarea name="notes" rows="2" placeholder="Gift wrap? Delivery instructions?"></textarea></div>
                    </div>
                </div>

                <input type="hidden" name="coupon_code" id="appliedCouponCode" value="{{ $couponCode ?? '' }}">
                <details class="ck-card coupon-card" id="couponDetails" @if($couponCode) open @endif>
                    <summary class="coupon-summary">
                        <span class="ck-step">%</span>
                        <span><strong>Have a coupon?</strong><small>Apply a discount code before placing your order.</small></span>
                    </summary>
                    <div class="coupon-entry">
                        <input type="text" id="couponCode" maxlength="32" value="{{ $couponCode ?? '' }}" placeholder="Enter coupon code" autocomplete="off" aria-label="Coupon code">
                        <button type="button" id="applyCouponBtn" onclick="applyCoupon()">Apply coupon</button>
                        <button type="button" id="removeCouponBtn" onclick="removeCoupon()" @if(!$couponCode) hidden @endif>Remove</button>
                    </div>
                    <p id="couponMessage" class="coupon-message" role="status" aria-live="polite">@if($couponCode) Coupon {{ $couponCode }} applied and validated. @endif</p>
                </details>

                <!-- ETA banner -->
                                <div class="eta-banner">
                    <div class="eta-ic">🚚</div>
                    <div>
                        <b>Estimated Delivery: {{ $delMin }}–{{ $delMax }} business days</b>
                        <p>Order today and your parcel should arrive by <b>{{ now()->addDays($delMax)->format('l, j F') }}</b>. You'll get updates every step of the way.</p>
                    </div>
                    <span class="eta-chip">Tracked</span>
                </div>

                <!-- Payment -->
                <div class="ck-card">
                    <div class="ck-card-head">
                        <span class="ck-step">2</span>
                        <div>
                            <h3>Payment Method</h3>
                            <p>100% secure encrypted payment</p>
                        </div>
                    </div>

                    <input type="hidden" name="payment_method" id="payMethod" value="{{ $defaultMethod }}">

                    <!-- STRIPE -->
                    <div class="pm-card {{ $defaultMethod === 'stripe' ? 'active' : '' }} {{ $stripeReady ? '' : 'off' }}" id="pmStripe" role="radio" aria-checked="{{ $defaultMethod === 'stripe' ? 'true' : 'false' }}" tabindex="0" onclick="pickMethod('stripe', this)" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();pickMethod('stripe',this)}">
                        <div class="pm-radio"></div>
                        <div class="pm-body">
                            <div class="pm-title">
                                <span class="pm-brand stripe-brand">stripe</span>
                                <span class="pm-name">Credit / Debit Card</span>
                                @if (!$stripeReady)<span class="pm-chip warn">Not configured</span>@else <span class="pm-chip ok">Instant</span>@endif
                            </div>
                            <p class="pm-desc">Visa, Mastercard, Amex — processed securely by Stripe.</p>
                        </div>
                        <svg class="pm-ic" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                            <rect x="2" y="5" width="20" height="14" rx="2" />
                            <path d="M2 10h20" />
                        </svg>
                    </div>

                    @if ($stripeReady)
                    <div class="stripe-box" id="stripeBox" style="display: {{ $defaultMethod === 'stripe' ? 'block' : 'none' }}">
                        <div id="cardElement"></div>
                        <p id="cardError" class="card-error" style="display:none"></p>
                        <p class="test-hint">🧪 Test mode — use <b>4242 4242 4242 4242</b>, any future expiry, any CVC.</p>
                    </div>
                    @endif

                    <!-- ⭐ PAYSTACK -->
                    <div class="pm-card {{ $defaultMethod === 'paystack' ? 'active' : '' }} {{ $paystackReady ? '' : 'off' }}" id="pmPaystack" role="radio" aria-checked="{{ $defaultMethod === 'paystack' ? 'true' : 'false' }}" tabindex="0" onclick="pickMethod('paystack', this)" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();pickMethod('paystack',this)}">
                        <div class="pm-radio"></div>
                        <div class="pm-body">
                            <div class="pm-title">
                                <span class="pm-brand ps-brand">paystack</span>
                                <span class="pm-name">Card / Bank Transfer</span>
                                @if (!$paystackReady)<span class="pm-chip warn">Not configured</span>@else <span class="pm-chip ok">Secure</span>@endif
                            </div>
                            <p class="pm-desc">Visa, Mastercard, Verve — you'll be redirected to Paystack's secure checkout page.</p>
                        </div>
                        <svg class="pm-ic" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                            <rect x="2" y="5" width="20" height="14" rx="2" />
                            <circle cx="12" cy="12" r="2.4" />
                        </svg>
                    </div>

                   
                </div>
            </form>

            <!-- ================= RIGHT : SUMMARY ================= -->
            <aside class="ck-right">
                <div class="ck-summary">
                    <h3>Order Summary</h3>

                    <div class="sum-items">
                        @foreach ($items as $it)
                        <div class="sum-item">
                            <div class="sum-thumb">
                                @if ($it['img'])
                                <img src="{{ $it['img'] }}" alt="{{ $it['name'] }}">
                                @else
                                <span>{{ $it['emoji'] ?: '🧴' }}</span>
                                @endif
                                <b class="sum-qty">{{ $it['qty'] }}</b>
                            </div>
                            <div class="sum-mid">
                                <a href="/product/{{ $it['slug'] }}">{{ $it['name'] }}</a>
                                @if(!empty($it['variant_label']))<small>{{ $it['variant_label'] }}</small>@endif
                                <span>£{{ number_format($it['price'], 2) }} each</span>
                            </div>
                            <b class="sum-line">£{{ number_format($it['line'], 2) }}</b>
                        </div>
                        @endforeach
                    </div>

                    @if ($shipping > 0 && $shipFreeOver > 0 && $subtotal < $shipFreeOver)
                        <div class="ship-progress">
                        <p>Add <b>£{{ number_format($shipFreeOver - $subtotal, 2) }}</b> more for <b>FREE shipping</b> 🎁</p>
                        <div class="sp-track">
                            <div class="sp-fill" style="width: {{ min(100, $subtotal / $shipFreeOver * 100) }}%"></div>
                        </div>
                </div>
                @else
                <div class="ship-unlocked">🎉 <b>FREE standard delivery</b> is applied to this order.</div>
                @endif

                <div class="sum-rows">
                    <div><span>Subtotal</span><span>£{{ number_format($subtotal, 2) }}</span></div>
                    <div id="discountRow" style="{{ ($discount ?? 0) > 0 ? '' : 'display:none' }}"><span>Discount{{ $couponCode ? ' ('.$couponCode.')' : '' }}</span><span id="discountAmount">−£{{ number_format($discount ?? 0, 2) }}</span></div>
                    <div><span>Standard shipping</span><span>@if ($shippingBase <= 0) <em class="free-chip">FREE</em> @else £{{ number_format($shippingBase, 2) }} @endif</span></div>
                    @if ($weightSurcharge > 0)
                    <div id="weightSurchargeRow" title="Cart weight: {{ number_format($cartWeightKg, 3) }} kg; included weight: {{ number_format($shipIncludedWeightKg, 3) }} kg"><span>Extra weight ({{ $billableExtraKg }} kg)</span><span>£{{ number_format($weightSurcharge, 2) }}</span></div>
                    @endif
                    <div id="taxRows" aria-live="polite">
                        @foreach($taxBreakdown ?? [] as $tax)
                        <div class="tax-row"><span>{{ $tax['name'] }} ({{ rtrim(rtrim(number_format($tax['percentage'], 4), '0'), '.') }}%)</span><span>£{{ number_format($tax['amount'], 2) }}</span></div>
                        @endforeach
                    </div>
                    <div class="sum-total"><span>Total</span><b id="checkoutTotal">£{{ number_format($total, 2) }}</b></div>
                </div>

                <button type="button" class="ck-place" id="placeBtn" onclick="document.getElementById('ckForm').requestSubmit()">
                    <span id="placeTxt">Place Order — £{{ number_format($total, 2) }}</span>
                    <span class="ck-spin" id="placeSpin" style="display:none"></span>
                </button>

                <div class="trust-row">
                    <span>🔒 SSL 256-bit</span><span>↩ 7-day returns</span><span>🚚 {{ $delMin }}–{{ $delMax }} days</span>
                </div>
        </div>
        </aside>
        </div>
    </section>
</main>

@include('front-end.include.footer')
@include('front-end.include.cart-drawer')
@include('front-end.include.js')

<script src="https://js.stripe.com/v3/"></script>

<style>
    .site-header { position: sticky; top: 0; background: #fff; box-shadow: 0 2px 20px rgba(0, 0, 0, .07); z-index: 1000 }
    .site-header .logo, .site-header .cart-btn { color: #1b1d21 }
    .nav-toggle span { background: #1b1d21 }
    @media (min-width: 992px) {
        .site-header .main-nav a { color: #5b6066 }
        .site-header .main-nav a:hover, .site-header .main-nav a.active { color: #0d3b40 }
        .site-header .main-nav a::after { background: var(--teal) }
    }

    .ck-hero { background: linear-gradient(120deg, #0d3b40, #155e57); position: relative; overflow: hidden }
    .ck-hero::after { content: 'glow'; position: absolute; right: 5%; top: 50%; transform: translateY(-50%); font-family: 'Allura', cursive; font-size: 110px; color: rgba(255, 255, 255, .06); pointer-events: none }
    .ck-hero .container { position: relative; z-index: 2; padding: 34px 20px 30px }
    .crumbs { color: rgba(255, 255, 255, .75); font-size: 13px }
    .crumbs a { color: rgba(255, 255, 255, .75) }
    .crumbs a:hover { color: #fff }
    .crumbs span { margin: 0 9px; opacity: .45 }
    .crumbs b { color: #fff; font-weight: 600 }
    .ck-title { font: 300 clamp(26px, 4vw, 40px) 'Montserrat', sans-serif; text-transform: uppercase; color: #fff; margin-top: 8px }
    .ck-title span { font-family: 'Allura', cursive; text-transform: none; color: #7fe3d2; font-size: 1.25em }
    .ck-body { padding: 58px 0 95px; background: linear-gradient(180deg, #f6f8f8 0%, var(--bg) 100%) }
    .ck-layout { display: grid; grid-template-columns: minmax(0, 1.55fr) minmax(340px, .85fr); gap: 34px; align-items: start }
    .ck-left { min-width: 0 }
    .ck-card { background: #fff; border: 1px solid #edf0f0; border-radius: 18px; box-shadow: 0 18px 50px rgba(13, 59, 64, .07); padding: 32px; margin-bottom: 22px }
    .ck-card-head { display: flex; gap: 14px; align-items: center; margin-bottom: 22px }
    .ck-card-head h3 { font: 600 17px 'Montserrat', sans-serif; color: var(--dark); text-transform: uppercase; letter-spacing: .5px }
    .ck-card-head p { font-size: 13px; color: #9aa0a4; margin-top: 2px }
    .ck-step { width: 38px; height: 38px; min-width: 38px; border-radius: 12px; background: linear-gradient(135deg, var(--teal), #2c8d97); color: #fff; font: 800 16px 'Montserrat', sans-serif; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 18px rgba(56, 170, 180, .35) }
    .fld-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px }
    .fld { display: flex; flex-direction: column; gap: 6px }
    .fld.full { grid-column: 1/-1 }
    .fld label { font: 600 11.5px 'Montserrat', sans-serif; letter-spacing: 1px; text-transform: uppercase; color: #7a7f84 }
    .fld input, .fld select, .fld textarea { border: 1px solid #e1e7e7; background: #f9fbfb; padding: 14px 15px; font: inherit; font-size: 14px; color: #333; outline: none; transition: .25s; border-radius: 9px; resize: vertical }
    .fld input:focus, .fld select:focus, .fld textarea:focus { border-color: var(--teal); box-shadow: 0 0 0 3px rgba(56, 170, 180, .12); background: #fff }
    .coupon-entry { display:flex; gap:10px }
    .coupon-card { padding:24px 26px }
    .coupon-summary { gap:15px; padding:2px 0 15px }
    .coupon-summary .ck-step { flex:0 0 38px }
    .coupon-entry { align-items:stretch; gap:11px }
    .coupon-entry input { flex:1; min-width:0; min-height:46px; border:1px solid #e1e7e7; background:#f9fbfb; padding:12px 15px; font:inherit; border-radius:10px; text-transform:uppercase; letter-spacing:.04em; transition:border-color .2s,box-shadow .2s }
    .coupon-entry input:focus { outline:none; border-color:var(--teal); box-shadow:0 0 0 3px rgba(56,170,180,.13); background:#fff }
    .coupon-entry button { min-height:46px; border:0; border-radius:10px; padding:0 20px; color:#fff; background:linear-gradient(135deg,var(--teal),#278d96); font:600 12px 'Montserrat',sans-serif; cursor:pointer; box-shadow:0 6px 14px rgba(56,170,180,.16); transition:transform .2s,box-shadow .2s }
    .coupon-entry button:hover:not(:disabled) { transform:translateY(-1px); box-shadow:0 9px 18px rgba(56,170,180,.24) }
    .coupon-entry button:disabled { opacity:.6; cursor:wait }
    .coupon-message { min-height:16px; margin:10px 0 0 53px; color:#18835b; font-size:12px; line-height:1.5 }
    .coupon-message.error { color:#c3475a }
    .coupon-summary { display:flex; align-items:center; gap:14px; cursor:pointer; list-style:none }
    .coupon-summary::-webkit-details-marker { display:none }
    .coupon-summary strong,.coupon-summary small { display:block }
    .coupon-summary strong { color:var(--dark); font:600 16px 'Montserrat',sans-serif }
    .coupon-summary small { margin-top:4px; color:#9aa0a4; font-size:12px }
    #removeCouponBtn { color:var(--teal); background:#edf7f6 }
    #removeCouponBtn[hidden] { display:none }
    @media(max-width:575px) { .coupon-card { padding:20px 18px } .coupon-entry { flex-wrap:wrap } .coupon-entry input { flex:1 1 100% } .coupon-entry button { min-height:44px; flex:1 } .coupon-message { margin-left:0 } }
    #taxRows { display:grid; grid-template-columns:minmax(0,1fr); gap:4px; padding:4px 0 }
    .tax-row { display:flex; justify-content:space-between; align-items:center; gap:14px; width:100%; padding:4px 0; color:#62696d; font-size:12px }
    .tax-row span:last-child { flex:none; text-align:right; font-variant-numeric:tabular-nums }
    .eta-banner { display: flex; gap: 16px; align-items: center; background: linear-gradient(120deg, #0d3b40, #155e57); color: #fff; padding: 22px 26px; margin-bottom: 22px; position: relative; overflow: hidden; border-radius: 16px; box-shadow: 0 14px 32px rgba(13, 59, 64, .14) }
    .eta-banner::after { content: 'glow'; position: absolute; right: 18px; bottom: -18px; font-family: 'Allura', cursive; font-size: 64px; color: rgba(255, 255, 255, .07) }
    .eta-ic { font-size: 30px }
    .eta-banner b { font: 600 15px 'Montserrat', sans-serif; display: block }
    .eta-banner p { font-size: 12.5px; color: rgba(255, 255, 255, .8); margin-top: 3px }
    .eta-chip { margin-left: auto; background: rgba(255, 255, 255, .14); border: 1px solid rgba(255, 255, 255, .25); padding: 5px 13px; font: 700 10.5px 'Montserrat', sans-serif; letter-spacing: 1.5px; text-transform: uppercase; flex-shrink: 0 }
    .pm-card { display: flex; gap: 15px; align-items: center; border: 1.5px solid #e5ebeb; background: linear-gradient(135deg, #fbfcfc, #f7f9f9); padding: 20px 22px; transition: border-color .25s, box-shadow .25s, transform .25s, background .25s; position: relative; cursor: pointer; margin-bottom: 12px; border-radius: 15px; outline: none }
    .pm-card:hover:not(.off) { border-color: #9acfc9; transform: translateY(-2px); box-shadow: 0 12px 28px rgba(13, 59, 64, .08) }
    .pm-card:focus-visible { box-shadow: 0 0 0 4px rgba(56, 170, 180, .18) }
    .pm-card.active { border-color: var(--teal); background: linear-gradient(135deg, #fff, #f1fbf9); box-shadow: 0 12px 30px rgba(56, 170, 180, .14) }
    .pm-card.off { opacity: .58; cursor: not-allowed }
    .pm-radio { width: 21px; height: 21px; min-width: 21px; border-radius: 50%; border: 2px solid #c7d0d1; position: relative; transition: .25s }
    .pm-card.active .pm-radio { border-color: var(--teal) }
    .pm-card.active .pm-radio::after { content: ''; position: absolute; inset: 3px; border-radius: 50%; background: var(--teal) }
    .pm-body { flex: 1 }
    .pm-title { display: flex; align-items: center; gap: 10px; flex-wrap: wrap }
    .pm-name { font: 600 13px 'Montserrat', sans-serif; color: var(--dark); text-transform: uppercase; letter-spacing: .5px }
    .pm-brand { font-weight: 800; font-size: 16px }
    .stripe-brand { color: #635bff; font-style: italic }
    .ps-brand { color: #00a3c4; font-style: italic }
    .pm-chip { font: 700 9.5px 'Montserrat', sans-serif; letter-spacing: 1px; text-transform: uppercase; padding: 5px 10px; border-radius: 20px }
    .pm-chip.ok { background: #e7f8f3; color: #0d8f6e }
    .pm-chip.warn { background: #fdf0e3; color: #c77d0a }
    .pm-desc { font-size: 12.5px; color: #778184; margin-top: 5px; line-height: 1.6 }
    .pm-ic { color: #8b999b; flex-shrink: 0; width: 32px; height: 32px; padding: 7px; background: #f0f4f4; border-radius: 9px }
    .stripe-box { border: 1px solid #b7ded9; background: #fff; padding: 18px; margin: -7px 0 14px; border-radius: 0 0 14px 14px; box-shadow: 0 10px 24px rgba(13, 59, 64, .05) }
    #cardElement { min-height: 46px; padding: 13px 14px; border: 1px solid #e4eaea; border-radius: 9px; background: #fbfcfc }
    .card-error { color: #d64545; font-size: 12.5px; margin-top: 8px }
    .test-hint { font-size: 11.5px; color: #9aa0a4; margin-top: 10px; background: #f7fafa; padding: 8px 12px; border-radius: 6px }
    .paystack-box { border: 1px solid #b7ded9; background: #fff; padding: 18px; margin: -7px 0 14px; border-radius: 0 0 14px 14px; box-shadow: 0 10px 24px rgba(13, 59, 64, .05) }
    .ps-note { font-size: 13px; color: #465356; line-height: 1.75 }
    .ps-test { font-size: 11.5px; color: #778184; margin-top: 10px; background: #f3f8f7; padding: 10px 12px; border-radius: 8px }
    .ck-summary { background: #fff; border: 1px solid #edf0f0; border-radius: 18px; box-shadow: 0 18px 50px rgba(13, 59, 64, .08); padding: 28px; position: sticky; top: 90px }
    .ck-summary h3 { font: 600 16px 'Montserrat', sans-serif; text-transform: uppercase; letter-spacing: 1px; color: var(--dark); margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid #eef1f2 }
    .sum-items { max-height: 280px; overflow-y: auto; margin-bottom: 16px }
    .sum-item { display: flex; gap: 12px; align-items: center; padding: 10px 0; border-bottom: 1px dashed #eef1f2 }
    .sum-thumb { position: relative; width: 56px; height: 62px; min-width: 56px; background: #f4f6f6; overflow: hidden }
    .sum-thumb img { width: 100%; height: 100%; object-fit: cover }
    .sum-thumb span { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 26px }
    .sum-qty { position: absolute; top: -6px; right: -6px; width: 20px; height: 20px; border-radius: 50%; background: var(--teal); color: #fff; font: 700 10px 'Montserrat', sans-serif; display: flex; align-items: center; justify-content: center }
    .sum-mid { flex: 1; min-width: 0 }
    .sum-mid a { display: block; font: 600 12.5px 'Montserrat', sans-serif; color: var(--dark); text-transform: uppercase; letter-spacing: .4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis }
    .sum-mid a:hover { color: var(--teal) }
    .sum-mid span { font-size: 12px; color: #9aa0a4 }
    .sum-line { font: 600 13.5px 'Montserrat', sans-serif; color: #0d3b40 }
    .ship-progress p { font-size: 12.5px; color: #555; margin-bottom: 8px }
    .sp-track { height: 7px; background: #e8eded; border-radius: 10px; overflow: hidden }
    .sp-fill { height: 100%; background: linear-gradient(90deg, var(--teal), #5fd0c0); border-radius: 10px; transition: width 1s ease }
    .ship-unlocked { background: #e7f8f3; color: #0d8f6e; font-size: 13px; padding: 11px 14px; margin-bottom: 16px; text-align: center; border-radius: 6px }
    .sum-rows { border-top: 1px solid #eef1f2; padding-top: 14px }
    .sum-rows>div { display: flex; justify-content: space-between; font-size: 13.5px; color: #666; padding: 5px 0 }
    .free-chip { background: #e7f8f3; color: #0d8f6e; font: 700 10.5px 'Montserrat', sans-serif; font-style: normal; padding: 3px 10px; border-radius: 20px; letter-spacing: 1px }
    .sum-total { margin-top: 10px; padding-top: 14px !important; border-top: 1px solid #eef1f2; align-items: center }
    .sum-total span { font: 600 14px 'Montserrat', sans-serif; color: var(--dark); text-transform: uppercase; letter-spacing: 1px }
    .sum-total b { font: 700 26px 'Montserrat', sans-serif; color: #0d3b40 }
    .ck-place { width: 100%; margin-top: 18px; background: linear-gradient(110deg, #238a82, var(--teal)); color: #fff; border: 0; padding: 18px; font: 600 13px 'Montserrat', sans-serif; letter-spacing: 1.5px; text-transform: uppercase; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 12px; transition: .3s; border-radius: 11px; box-shadow: 0 10px 22px rgba(56, 170, 180, .2) }
    .ck-place:hover { background: var(--teal-dark); box-shadow: 0 14px 30px rgba(56, 170, 180, .4); transform: translateY(-2px) }
    .ck-place:disabled { background: #c3c9cc; cursor: wait; transform: none; box-shadow: none }
    .ck-spin { width: 17px; height: 17px; border-radius: 50%; border: 2.5px solid rgba(255, 255, 255, .35); border-top-color: #fff; animation: ckspin .7s linear infinite }
    @keyframes ckspin { to { transform: rotate(360deg) } }
    .trust-row { display: flex; justify-content: center; gap: 16px; margin-top: 16px; font-size: 11.5px; color: #9aa0a4; flex-wrap: wrap }
    @media (max-width: 991px) {
        .ck-layout { grid-template-columns: 1fr }
        .ck-summary { position: static; order: -1 }
        .fld-grid { grid-template-columns: 1fr }
    }
    @media (max-width: 600px) {
        .ck-body { padding: 30px 0 60px }
        .ck-card, .ck-summary { padding: 21px; border-radius: 14px }
        .ck-card-head { margin-bottom: 18px }
        .ck-card-head h3 { font-size: 15px }
        .pm-card { padding: 16px; gap: 11px }
        .pm-title { gap: 7px }
        .pm-brand { font-size: 14px }
        .eta-banner { align-items: flex-start; padding: 18px }
        .eta-chip { display: none }
    }
</style>

<script>
    /* ══════════════════════════════════════════════
       v4 — PAYSTACK = FULL PAGE REDIRECT (no popup/iframe)
       X-Frame-Options block ka PERMANENT solution
    ══════════════════════════════════════════════ */
    var CK_DEBUG = true;

    function ckLog() {
        if (!CK_DEBUG) return;
        var args = ['%c🛒 CHECKOUT', 'background:#0d3b40;color:#7fe3d2;font-weight:bold;padding:2px 6px;border-radius:4px'];
        for (var i = 0; i < arguments.length; i++) args.push(arguments[i]);
        console.log.apply(console, args);
    }

    var STRIPE_KEY = @json($stripeKey);
    var METHOD = @json($defaultMethod);
    var stripe = null, cardEl = null;
    var ckPlacing = false;
    var couponRequestPending = false;

    window.ckFail = function (msg) {
        ckLog('❌ FAIL:', msg);
        ckPlacing = false;
        var btn = document.getElementById('placeBtn');
        if (btn) btn.disabled = false;
        var txt = document.getElementById('placeTxt');
        if (txt) txt.textContent = 'Place Order — ' + (document.getElementById('checkoutTotal')?.textContent || '£{{ number_format($total, 2) }}');
        var spin = document.getElementById('placeSpin');
        if (spin) spin.style.display = 'none';
        if (typeof showToast === 'function') showToast(msg, 'error');
        else alert(msg);
    };

    if (STRIPE_KEY) {
        stripe = Stripe(STRIPE_KEY);
        var elements = stripe.elements();
        cardEl = elements.create('card', {
            style: { base: { fontSize: '15px', color: '#1b1d21', fontFamily: "'Open Sans', sans-serif", '::placeholder': { color: '#aab1b5' } }, invalid: { color: '#d64545' } }
        });
        cardEl.mount('#cardElement');
        cardEl.on('change', function(e) {
            var err = document.getElementById('cardError');
            if (e.error) { err.textContent = e.error.message; err.style.display = 'block'; }
            else err.style.display = 'none';
        });
    }

    function pickMethod(m, el) {
        if (el.classList.contains('off')) { showToast('This payment method is not configured yet.', 'error'); return; }
        METHOD = m;
        document.getElementById('payMethod').value = m;
        document.querySelectorAll('.pm-card').forEach(function(c) { c.classList.remove('active'); });
        el.classList.add('active');
        document.querySelectorAll('.pm-card').forEach(function(c) { c.setAttribute('aria-checked', c === el ? 'true' : 'false'); });
        var sb = document.getElementById('stripeBox');
        if (sb) sb.style.display = (m === 'stripe') ? 'block' : 'none';
        var pb = document.getElementById('paystackBox');
        if (pb) pb.style.display = (m === 'paystack') ? 'block' : 'none';
        ckLog('🔄 method →', m);
    }

    @if($checkoutError)
    document.addEventListener('DOMContentLoaded', function() {
        ckLog('server flash error:', @json($checkoutError));
        showToast(@json($checkoutError), 'error');
    });
    @endif

    function applyCoupon() {
        var input = document.getElementById('couponCode');
        var message = document.getElementById('couponMessage');
        var button = document.getElementById('applyCouponBtn');
        var applied = document.getElementById('appliedCouponCode');
        var code = input.value.trim();
        if (!code) {
            message.textContent = applied.value
                ? 'Enter a new code, or use Remove to clear the currently applied coupon.'
                : 'Enter a coupon code first.';
            message.classList.add('error');
            return;
        }

        couponRequestPending = true;
        button.disabled = true;
        message.classList.remove('error');
        message.textContent = 'Checking coupon…';
        var formData = new FormData();
        formData.append('coupon_code', code);
        formData.append('_token', csrfToken());
        fetch(@json(route('checkout.coupon')), {
            method: 'POST',
            body: formData,
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function(response) {
            return response.json().then(function(data) { return { ok: response.ok, data: data }; });
        }).then(function(result) {
            if (!result.ok || result.data.status !== 'success') {
                throw new Error(result.data.message || 'That coupon could not be applied.');
            }

            var data = result.data;
            applied.value = data.coupon_code || '';
            input.value = data.coupon_code || '';
            document.getElementById('removeCouponBtn').hidden = !data.coupon_code;
            message.textContent = data.coupon_code
                ? 'Coupon ' + data.coupon_code + ' validated and applied — you save £' + Number(data.discount).toFixed(2) + '.'
                : 'Coupon removed. Discount, taxes, and total updated.';
            message.classList.remove('error');
            var discountRow = document.getElementById('discountRow');
            discountRow.style.display = Number(data.discount) > 0 ? '' : 'none';
            discountRow.querySelector('span').textContent = data.coupon_code ? 'Discount (' + data.coupon_code + ')' : 'Discount';
            document.getElementById('discountAmount').textContent = '−£' + Number(data.discount).toFixed(2);
            document.getElementById('checkoutTotal').textContent = '£' + Number(data.total).toFixed(2);
            document.getElementById('placeTxt').textContent = 'Place Order — £' + Number(data.total).toFixed(2);

            var taxRows = document.getElementById('taxRows');
            taxRows.replaceChildren();
            data.tax_breakdown.forEach(function(tax) {
                var row = document.createElement('div');
                row.className = 'tax-row';
                var label = document.createElement('span');
                label.textContent = tax.name + ' (' + Number(tax.percentage).toString() + '%)';
                var amount = document.createElement('span');
                amount.textContent = '£' + Number(tax.amount).toFixed(2);
                row.append(label, amount);
                taxRows.appendChild(row);
            });
        }).catch(function(error) {
            message.textContent = applied.value
                ? error.message + ' Your previously validated coupon ' + applied.value + ' remains applied; remove it if you do not want to use it.'
                : error.message;
            message.classList.add('error');
        }).finally(function() {
            couponRequestPending = false;
            button.disabled = false;
        });
    }

    function removeCoupon() {
        var input = document.getElementById('couponCode');
        var message = document.getElementById('couponMessage');
        var button = document.getElementById('removeCouponBtn');
        var applyButton = document.getElementById('applyCouponBtn');
        couponRequestPending = true;
        button.disabled = true;
        applyButton.disabled = true;
        message.classList.remove('error');
        message.textContent = 'Removing coupon and recalculating…';
        var formData = new FormData();
        formData.append('coupon_code', '');
        formData.append('_token', csrfToken());
        fetch(@json(route('checkout.coupon')), {
            method: 'POST',
            body: formData,
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function(response) {
            return response.json().then(function(data) {
                if (!response.ok || data.status !== 'success') throw new Error(data.message || 'Could not remove coupon.');
                return data;
            });
        }).then(function(data) {
            document.getElementById('appliedCouponCode').value = '';
            input.value = '';
            button.hidden = true;
            message.textContent = data.message + ' Taxes and total updated.';
            var discountRow = document.getElementById('discountRow');
            discountRow.style.display = 'none';
            discountRow.querySelector('span').textContent = 'Discount';
            document.getElementById('discountAmount').textContent = '−£0.00';
            document.getElementById('checkoutTotal').textContent = '£' + Number(data.total).toFixed(2);
            document.getElementById('placeTxt').textContent = 'Place Order — £' + Number(data.total).toFixed(2);
            var taxRows = document.getElementById('taxRows');
            taxRows.replaceChildren();
            data.tax_breakdown.forEach(function(tax) {
                var row = document.createElement('div');
                row.className = 'tax-row';
                var label = document.createElement('span');
                label.textContent = tax.name + ' (' + Number(tax.percentage).toString() + '%)';
                var amount = document.createElement('span');
                amount.textContent = '£' + Number(tax.amount).toFixed(2);
                row.append(label, amount);
                taxRows.appendChild(row);
            });
        }).catch(function(error) {
            message.textContent = error.message;
            message.classList.add('error');
        }).finally(function() {
            couponRequestPending = false;
            button.disabled = false;
            applyButton.disabled = false;
        });
    }

    function placeOrder(e) {
        e.preventDefault();
        ckLog('═══ PLACE ORDER CLICKED ═══');
        if (ckPlacing) { ckLog('⏳ already placing'); return; }
        if (couponRequestPending) {
            showToast('Please wait for coupon validation to finish.', 'error');
            return;
        }
        var typedCoupon = document.getElementById('couponCode').value.trim().toUpperCase();
        var appliedCoupon = document.getElementById('appliedCouponCode').value.trim().toUpperCase();
        if (typedCoupon !== appliedCoupon) {
            var couponMessage = document.getElementById('couponMessage');
            couponMessage.textContent = typedCoupon
                ? 'Apply and validate this code before placing your order.'
                : 'Remove the currently applied coupon before checking out without it.';
            couponMessage.classList.add('error');
            document.getElementById('couponDetails').open = true;
            document.getElementById('couponCode').focus();
            return;
        }

        var method = document.getElementById('payMethod').value;
        ckLog('1️⃣ method =', method);

        if (method === 'stripe' && !stripe) { ckFail('Card payment is not configured yet. Please add your keys in the admin panel.'); return; }

        var form = document.getElementById('ckForm');
        var fd = new FormData(form);
        fd.append('_token', csrfToken());
        fd.append('payment_method', method);

        var btn = document.getElementById('placeBtn');
        ckPlacing = true;
        btn.disabled = true;
        document.getElementById('placeTxt').textContent = method === 'paystack' ? 'Redirecting to Paystack…' : 'Processing…';
        document.getElementById('placeSpin').style.display = 'inline-block';

        ckLog('2️⃣ fetch → checkout.process…');

        var settled = false;
        var safety = setTimeout(function () {
            if (!settled) { ckLog('⏰ 40s — server silent'); ckFail('Server did not respond in 40s — laravel.log check karo.'); }
        }, 40000);

        fetch("{{ route('checkout.process') }}", {
                method: 'POST', body: fd,
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) {
                ckLog('3️⃣ HTTP status =', r.status);
                return r.text().then(function(t) {
                    ckLog('4️⃣ raw body (300 chars):', t.substring(0, 300));
                    var d;
                    try { d = JSON.parse(t); }
                    catch (err) { d = { status: 'error', message: 'Server ne JSON nahi bheja (upar raw body dekho)' }; }
                    return { ok: r.ok, d: d };
                });
            })
            .then(function(res) {
                settled = true; clearTimeout(safety);
                ckLog('5️⃣ parsed =', res);

                if (!res.ok || res.d.status !== 'success') { ckFail((res.d && res.d.message) || 'Could not place order.'); return; }

                /* ══════════ ⭐ PAYSTACK — FULL PAGE REDIRECT ══════════ */
                if (method === 'paystack') {
                    var ps = res.d.paystack;
                    ckLog('6️⃣ PAYSTACK data:', ps);
                    if (!ps || !ps.authorization_url) { ckFail('Paystack initialization failed — laravel.log check karo.'); return; }
                    ckLog('🚀 REDIRECTING to Paystack hosted page:', ps.authorization_url);
                    /* order DB mein ban chuka hai — page redirect kar raha hai.
                       Payment ke baad Paystack khud callback route pe wapas le aayega. */
                    window.location.href = ps.authorization_url;
                    return;
                }

                /* ══════════ STRIPE flow (unchanged) ══════════ */
                ckLog('6️⃣ STRIPE confirm start');
                stripe.confirmCardPayment(res.d.client_secret, {
                    payment_method: {
                        card: cardEl,
                        billing_details: {
                            name: form.name.value, email: form.email.value, phone: form.phone.value,
                            address: { line1: form.address.value, city: form.city.value, postal_code: form.postal_code.value, country: 'GB' }
                        }
                    }
                }).then(function(result) {
                    if (result.error) {
                        var failureData = new FormData();
                        failureData.append('gateway', 'stripe_failed');
                        failureData.append('order_id', res.d.order_id);
                        failureData.append('payment_intent', result.paymentIntent ? result.paymentIntent.id : res.d.payment_intent_id);
                        failureData.append('_token', csrfToken());
                        fetch("{{ route('checkout.confirm') }}", {
                            method: 'POST',
                            body: failureData,
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        }).finally(function() { ckFail(result.error.message); });
                        return;
                    }
                    if (result.paymentIntent.status === 'succeeded') {
                        var cfd = new FormData();
                        cfd.append('order_id', res.d.order_id);
                        cfd.append('payment_intent', result.paymentIntent.id);
                        cfd.append('_token', csrfToken());

                        fetch("{{ route('checkout.confirm') }}", {
                                method: 'POST', body: cfd,
                                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                            })
                            .then(function(r) { return r.json(); })
                            .then(function(d) {
                                ckLog('stripe confirm =', d);
                                if (d.status === 'success') { window.location.href = d.redirect; }
                                else ckFail(d.message || 'Confirmation failed.');
                            })
                            .catch(function() { ckFail('Confirmation failed.'); });
                    }
                });
            })
            .catch(function(err) {
                settled = true; clearTimeout(safety);
                ckLog('💥 fetch CRASHED:', err);
                ckFail('Something went wrong. Please try again.');
            });
    }

    ckLog('🟢 Checkout JS v4 loaded (Paystack = REDIRECT flow) | Paystack ready =', @json($paystackReady), '| Stripe key =', @json((bool) $stripeKey));
</script>

</body>

</html>