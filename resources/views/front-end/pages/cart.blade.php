@include('front-end.include.header')
@include('front-end.include.loader')

<main class="bag-page">
    <section class="bag-hero">
        <div class="bag-container">
            <p><a href="/">Home</a><span>/</span><b>Your bag</b></p>
            <h1>Your shopping bag</h1>
            <span>Review your items before secure checkout.</span>
        </div>
    </section>
    <section class="bag-content">
        <div class="bag-container">
            <div id="bagNotice" class="bag-notice" role="status" aria-live="polite"></div>
            @if(count($items))
                <div class="bag-layout">
                    <div class="bag-items">
                        @foreach($items as $item)
                            <article class="bag-item">
                                <a class="bag-photo" href="{{ route('product.show', $item['slug']) }}">
                                    @if($item['img'])
                                        <img src="{{ $item['img'] }}" alt="{{ $item['name'] }}">
                                    @else
                                        <span>{{ $item['emoji'] }}</span>
                                    @endif
                                </a>
                                <div class="bag-description">
                                    <a class="bag-name" href="{{ route('product.show', $item['slug']) }}">{{ $item['name'] }}</a>
                                    @if($item['variant_label'])
                                        <p>{{ $item['variant_label'] }}</p>
                                    @endif
                                    <span>£{{ number_format($item['price'], 2) }} each</span>
                                </div>
                                <div class="bag-quantity">
                                    <label for="qty-{{ $loop->index }}">Qty</label>
                                    <input id="qty-{{ $loop->index }}" type="number" min="1" max="{{ max(1, $item['stock']) }}" value="{{ $item['qty'] }}" data-product-id="{{ $item['id'] }}" data-variant-id="{{ $item['variant_id'] ?? '' }}" aria-label="Quantity for {{ $item['name'] }}">
                                </div>
                                <strong class="bag-line">£{{ number_format($item['line'], 2) }}</strong>
                                <button class="bag-remove" type="button" data-remove data-product-id="{{ $item['id'] }}" data-variant-id="{{ $item['variant_id'] ?? '' }}">Remove</button>
                            </article>
                        @endforeach
                    </div>
                    <aside class="bag-summary">
                        <h2>Order summary</h2>
                        <div><span>Subtotal</span><strong>£{{ number_format($subtotal, 2) }}</strong></div>
                        <p>Shipping and taxes are calculated at checkout.</p>
                        <a class="bag-checkout" href="{{ route('checkout') }}">Continue to checkout</a>
                        <a class="bag-shopping" href="{{ route('shop') }}">Continue shopping</a>
                    </aside>
                </div>
            @else
                <section class="bag-empty">
                    <span aria-hidden="true">🛍️</span>
                    <h2>Your bag is empty</h2>
                    <p>Explore the shop and add products to your bag.</p>
                    <a class="bag-checkout" href="{{ route('shop') }}">Explore the shop</a>
                </section>
            @endif
        </div>
    </section>
</main>

<style>
    .bag-container{width:min(1180px,calc(100% - 40px));margin:auto}
    .bag-hero{padding:36px 0;background:linear-gradient(120deg,#0d3b40,#155e57);color:#fff}
    .bag-hero p{margin:0 0 16px;font-size:13px}.bag-hero p a{color:#d8eeeb;text-decoration:none}.bag-hero p span{margin:0 10px;color:#a7cbc5}.bag-hero h1{margin:0 0 7px;color:#fff;font:300 clamp(26px,4vw,40px) 'Montserrat',sans-serif;text-transform:uppercase}.bag-hero>div>span{color:#d4e5e2;font-size:13px}
    .bag-content{min-height:48vh;padding:42px 0 80px;background:#f6f8f8}.bag-layout{display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:24px;align-items:start}
    .bag-items,.bag-summary,.bag-empty{border:1px solid #e8eded;border-radius:14px;background:#fff;box-shadow:0 8px 24px #19363b0b}
    .bag-item{display:grid;grid-template-columns:88px minmax(0,1fr) 92px 90px 66px;gap:16px;align-items:center;padding:18px;border-bottom:1px solid #edf0f0}.bag-item:last-child{border-bottom:0}
    .bag-photo{width:88px;height:88px;display:grid;place-items:center;overflow:hidden;border-radius:10px;background:#f6f8f8;text-decoration:none}.bag-photo img{width:100%;height:100%;object-fit:cover}.bag-photo span{font-size:32px}
    .bag-description{display:grid;gap:6px}.bag-name{color:#173e40;font-weight:800;text-decoration:none}.bag-description p,.bag-description>span{margin:0;color:#788588;font-size:12px}.bag-quantity{display:grid;gap:5px;color:#667779;font-size:11px}.bag-quantity input{width:74px;min-height:38px;padding:7px;border:1px solid #dce5e5;border-radius:8px;font:inherit}.bag-line{white-space:nowrap;color:#173e40}
    .bag-remove,.bag-shopping{border:0;background:none;color:#8b6666;font:inherit;font-size:11px;font-weight:600;text-decoration:underline;cursor:pointer}.bag-summary{position:sticky;top:92px;padding:22px}.bag-summary h2{margin:0 0 20px;font-size:17px}.bag-summary>div{display:flex;justify-content:space-between;gap:12px;color:#34494b}.bag-summary>div strong{color:#123f40}.bag-summary p,.bag-summary small{display:block;color:#879292;font-size:11px;line-height:1.5}.bag-summary p{margin:12px 0 18px}.bag-checkout{display:block;padding:14px;border-radius:8px;background:#0d6c68;color:#fff;text-align:center;font-size:12px;font-weight:800;text-decoration:none}.bag-checkout:hover{background:#0b5653}.bag-shopping{display:block;margin:16px auto;text-align:center;color:#426363}.bag-summary small{text-align:center}
    .bag-empty{padding:54px 20px;text-align:center}.bag-empty>span{font-size:48px}.bag-empty h2{margin:12px 0 8px;color:#173e40}.bag-empty p{max-width:470px;margin:0 auto 22px;color:#778687;font-size:13px;line-height:1.6}.bag-empty .bag-checkout{width:min(250px,100%);margin:auto}
    .bag-notice{display:none;margin-bottom:16px;padding:12px;border-radius:8px;background:#fff0f0;color:#a72b2b;font-size:12px}.bag-notice.show{display:block}
    .site-header{position:sticky;top:0;background:#fff;box-shadow:0 2px 20px rgba(0,0,0,.07);z-index:1000}.site-header .logo{color:#1b1d21}.nav-toggle span{background:#1b1d21}
    @media(max-width:800px){.bag-layout{grid-template-columns:1fr}.bag-summary{position:static}.bag-item{grid-template-columns:72px minmax(0,1fr) 75px;gap:12px;padding:14px}.bag-photo{width:72px;height:72px}.bag-line{grid-column:2;grid-row:2}.bag-remove{grid-column:3;grid-row:2;justify-self:end}.bag-quantity{grid-column:3;grid-row:1}.bag-hero{padding:28px 0}}
    @media(max-width:480px){.bag-container{width:calc(100% - 28px)}.bag-content{padding:24px 0 50px}.bag-item{grid-template-columns:62px minmax(0,1fr) 66px;gap:9px;padding:12px}.bag-photo{width:62px;height:62px}.bag-description{font-size:12px}.bag-quantity input{width:62px}}
</style>

@include('front-end.include.footer')
@include('front-end.include.cart-drawer')
@include('front-end.include.js')
<script>
    (function () {
        var notice = document.getElementById('bagNotice');
        var token = document.querySelector('meta[name="csrf-token"]').content;
        function send(url, fields) {
            var data = new FormData();
            Object.keys(fields).forEach(function (key) { if (fields[key] !== '') data.append(key, fields[key]); });
            data.append('_token', token);
            fetch(url, {method:'POST', body:data, headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}})
                .then(function (response) {
                    return response.json().then(function (payload) {
                        if (!response.ok) throw new Error(payload.message || 'Bag update failed. Please try again.');
                        if (typeof window.ajaxNavigate !== 'function') {
                            throw new Error('Your bag could not be updated without reloading the page.');
                        }
                        return window.ajaxNavigate(window.location.href, false).then(function (navigated) {
                            if (!navigated) throw new Error('Could not update your bag. Please try again.');
                        });
                    });
                })
                .catch(function (error) {
                    notice.textContent = error.message;
                    notice.classList.add('show');
                });
        }
        document.querySelectorAll('.bag-quantity input').forEach(function (input) {
            input.addEventListener('change', function () {
                var qty = Math.max(1, Math.min(Number(this.max), Number(this.value) || 1));
                this.value = qty;
                send(@json(route('cart.update')), {
                    product_id:this.dataset.productId,
                    sku_variant_id:this.dataset.variantId,
                    qty:String(qty)
                });
            });
        });
        document.querySelectorAll('[data-remove]').forEach(function (button) {
            button.addEventListener('click', function () {
                send(@json(route('cart.remove')), {
                    product_id:this.dataset.productId,
                    sku_variant_id:this.dataset.variantId
                });
            });
        });
    })();
</script>
