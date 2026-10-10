{{-- ============================================================
     CART DRAWER — self-contained (CSS + markup + JS)
     · SHOPPING: cart and checkout are for signed-in customers
     · QTY SYNC: mirrors cart qty into product-detail stepper
     · OPEN TRIGGERS: delegated on document → .floating-cart (footer)
       works on FIRST load too (script header mein hota hai, footer baad
       mein aata hai — direct binding miss kar deta tha)
============================================================ --}}
<div class="dw-root" id="drawerRoot">
    <div class="dw-overlay" id="dwOverlay" onclick="closeDrawer()"></div>
    <aside class="dw-panel" id="dwPanel">
        <div class="dw-head">
            <h3>
                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" />
                </svg>
                Your Bag <span class="dw-count" id="dwCount">0</span>
            </h3>
            <button class="dw-close" onclick="closeDrawer()" aria-label="Close">&times;</button>
        </div>
        <div class="dw-items" id="drawerItems"></div>
        <div class="dw-foot" id="dwFoot">
            <div class="dw-subtotal"><span>Subtotal</span><b id="drawerSubtotal">£0.00</b></div>
            <p class="dw-note">Shipping &amp; taxes calculated at checkout</p>
            <button class="dw-checkout" onclick="goToCheckout()">Checkout</button>
            <a class="dw-continue" href="{{ route('cart.index') }}">View full bag</a>
            <button class="dw-continue" onclick="closeDrawer()">Continue Shopping</button>
        </div>
    </aside>
</div>

<div class="fx-toast" id="fxToast"></div>

<style>
    .dw-root { position: fixed; inset: 0; z-index: 11000; visibility: hidden; transition: visibility .45s }
    .dw-root.open { visibility: visible }
    .dw-overlay { position: absolute; inset: 0; background: rgba(8, 12, 14, .55); opacity: 0; transition: opacity .4s; backdrop-filter: blur(3px) }
    .dw-root.open .dw-overlay { opacity: 1 }

    .dw-panel {
        position: absolute; top: 0; right: 0; height: 100%;
        width: 410px; max-width: 94vw; background: #fff;
        display: flex; flex-direction: column;
        transform: translateX(105%);
        transition: transform .45s cubic-bezier(.22, 1, .36, 1);
        box-shadow: -24px 0 70px rgba(0, 0, 0, .22)
    }
    .dw-root.open .dw-panel { transform: none }

    .dw-head { display: flex; justify-content: space-between; align-items: center; padding: 22px 24px; border-bottom: 1px solid #eef1f2 }
    .dw-head h3 { display: flex; align-items: center; gap: 10px; font: 500 15px 'Montserrat', sans-serif; letter-spacing: 2px; text-transform: uppercase; color: #1b1d21 }
    .dw-count { min-width: 22px; height: 22px; border-radius: 50%; background: var(--teal); color: #fff; font: 700 11px 'Montserrat', sans-serif; display: flex; align-items: center; justify-content: center; padding: 0 6px }
    .dw-close { background: none; border: 0; font-size: 30px; line-height: 1; color: #9aa0a4; cursor: pointer; transition: .25s }
    .dw-close:hover { color: #1b1d21; transform: rotate(90deg) }

    .dw-items { flex: 1; overflow-y: auto; padding: 10px 24px }
    .dw-item { display: flex; gap: 14px; padding: 18px 0; border-bottom: 1px dashed #e4e8ea }
    .dw-thumb { width: 76px; height: 88px; flex-shrink: 0; background: #f4f6f6; overflow: hidden; display: block }
    .dw-thumb img { width: 100%; height: 100%; object-fit: cover }
    .dw-emoji { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 34px }

    .dw-mid { flex: 1; min-width: 0 }
    .dw-name { display: block; font: 600 13px 'Montserrat', sans-serif; color: #1b1d21; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 3px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap }
    .dw-name:hover { color: var(--teal) }
    .dw-price { font-size: 13px; color: #8a9298; margin-bottom: 8px }

    .dw-qty { display: inline-flex; align-items: center; border: 1px solid #e2e6e8 }
    .dw-qty button { width: 28px; height: 28px; background: #fff; border: 0; cursor: pointer; font-size: 15px; color: #1b1d21; transition: .2s }
    .dw-qty button:hover { background: var(--teal); color: #fff }
    .dw-qty span { min-width: 34px; text-align: center; font: 600 13px 'Montserrat', sans-serif; border-left: 1px solid #e2e6e8; border-right: 1px solid #e2e6e8; line-height: 28px }

    .dw-right { display: flex; flex-direction: column; align-items: flex-end; justify-content: space-between }
    .dw-right>b { font: 600 14px 'Montserrat', sans-serif; color: #0d3b40 }
    .dw-remove { background: none; border: 0; color: #b3b9bd; cursor: pointer; transition: .2s; padding: 4px }
    .dw-remove:hover { color: #d64545; transform: scale(1.1) }

    .dw-foot { border-top: 1px solid #eef1f2; padding: 20px 24px 24px }
    .dw-subtotal { display: flex; justify-content: space-between; align-items: center; font: 500 14px 'Montserrat', sans-serif; color: #1b1d21; margin-bottom: 4px }
    .dw-subtotal b { font-size: 19px; color: #0d3b40 }
    .dw-note { font-size: 12px; color: #9aa0a4; margin-bottom: 16px }
    .dw-checkout { width: 100%; background: var(--teal); color: #fff; border: 0; padding: 15px; font: 600 12px 'Montserrat', sans-serif; letter-spacing: 2.5px; text-transform: uppercase; cursor: pointer; transition: .3s }
    .dw-checkout:hover { background: var(--teal-dark); box-shadow: 0 12px 26px rgba(56, 170, 180, .4) }
    .dw-continue { width: 100%; background: none; border: 0; margin-top: 10px; padding: 10px; font: 500 11.5px 'Montserrat', sans-serif; letter-spacing: 1.5px; text-transform: uppercase; color: #8a9298; cursor: pointer }
    .dw-continue:hover { color: #1b1d21 }

    .dw-empty { text-align: center; padding: 70px 10px }
    .dwe-emoji { font-size: 54px; margin-bottom: 14px }
    .dw-empty p { font: 600 16px 'Montserrat', sans-serif; color: #1b1d21; margin-bottom: 4px }
    .dw-empty span { display: block; font-size: 13px; color: #9aa0a4; margin-bottom: 22px }
    .dw-empty button { background: var(--teal); color: #fff; border: 0; padding: 13px 30px; font: 600 11.5px 'Montserrat', sans-serif; letter-spacing: 2px; text-transform: uppercase; cursor: pointer; transition: .3s }
    .dw-empty button:hover { background: var(--teal-dark) }

    .fx-toast {
        position: fixed; left: 50%; bottom: 34px;
        transform: translate(-50%, 20px); z-index: 13000;
        background: #12211f; color: #fff; padding: 13px 26px;
        font: 500 13.5px 'Montserrat', sans-serif; letter-spacing: .4px;
        border-radius: 50px; opacity: 0; visibility: hidden;
        transition: all .35s; box-shadow: 0 14px 40px rgba(0, 0, 0, .3);
        border-left: 3px solid var(--teal); max-width: 86vw; text-align: center
    }
    .fx-toast.show { opacity: 1; visibility: visible; transform: translate(-50%, 0) }
    .fx-toast.err { border-left-color: #e05a5a }
</style>

<script>
    /* ---------- helpers ---------- */
    function csrfToken() {
        var m = document.querySelector('meta[name="csrf-token"]');
        if (m) return m.content;
        var i = document.querySelector('input[name="_token"]');
        return i ? i.value : '';
    }

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function(c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' } [c];
        });
    }

    function updateBadges(count) {
        document.querySelectorAll('.cart-badge').forEach(function(b) {
            b.textContent = count;
            b.classList.remove('bump');
            void b.offsetWidth;
            b.classList.add('bump');
        });
    }

    var toastTimer = null;

    function showCartToast(msg, type) {
        if (typeof window.showToast === 'function') {
            window.showToast(msg, type);
            return;
        }
        var t = document.getElementById('fxToast');
        if (!t) return;
        t.textContent = msg;
        t.className = 'fx-toast show ' + (type === 'error' ? 'err' : 'ok');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function() {
            t.className = 'fx-toast';
        }, 2600);
    }

    /* ---------- open / close ---------- */
    function requireShoppingLogin(target) {
        var destination = target || (window.location.pathname + window.location.search + window.location.hash);
        window.location.assign("/login-redirect?redirect=" + encodeURIComponent(destination));
    }

    function openDrawer() {
        if (!@json(auth()->check())) {
            requireShoppingLogin('/cart');
            return;
        }
        document.getElementById('drawerRoot').classList.add('open');
        document.body.style.overflow = 'hidden';
        loadCart();
    }

    function closeDrawer() {
        document.getElementById('drawerRoot').classList.remove('open');
        document.body.style.overflow = '';
    }

    function goToCheckout() {
        var checkoutUrl = "{{ route('checkout') }}";
        if (!@json(auth()->check())) {
            requireShoppingLogin('/checkout');
            return;
        }
        closeDrawer();

        if (typeof window.ajaxNavigate === 'function') {
            window.ajaxNavigate(checkoutUrl).catch(function() {
                window.location.assign(checkoutUrl);
            });
            return;
        }

        window.location.assign(checkoutUrl);
    }

    /* ---------- load / render ---------- */
    function loadCart() {
        fetch("{{ route('cart.items') }}", { headers: { 'Accept': 'application/json' } })
            .then(function(r) {
                if (!r.ok) throw new Error('Could not load your bag');
                return r.json();
            })
            .then(renderCart)
            .catch(function() { showCartToast('Could not load your bag. Please try again.', 'error'); });
    }

    var DW_EMPTY = '<div class="dw-empty"><div class="dwe-emoji">🛍️</div><p>Your bag is empty</p><span>Fill it with something beautiful.</span><button onclick="closeDrawer()">Continue Shopping</button></div>';

    function renderCart(data) {
        updateBadges(data.count || 0);
        document.getElementById('dwCount').textContent = data.count || 0;
        var wrap = document.getElementById('drawerItems');
        var foot = document.getElementById('dwFoot');

        if (!data.items || !data.items.length) {
            wrap.innerHTML = DW_EMPTY;
            foot.style.display = 'none';
            return;
        }
        foot.style.display = 'block';
        var html = '';
        data.items.forEach(function(it) { html += dwItem(it); });
        wrap.innerHTML = html;
        document.getElementById('drawerSubtotal').textContent = '£' + Number(data.subtotal).toFixed(2);

        /* 🔄 keep the product-detail stepper in sync with the cart */
        if (window.pdSyncQty) data.items.forEach(function (it) { window.pdSyncQty(it.id, it.qty, it.variant_id); });
    }

    function dwItem(it) {
        var thumb = it.img ?
            '<img src="' + it.img + '" alt="' + esc(it.name) + '">' :
            '<div class="dw-emoji">' + esc(it.emoji || '🧴') + '</div>';

        return '<div class="dw-item">' +
            '<a class="dw-thumb" href="/product/' + esc(it.slug) + '">' + thumb + '</a>' +
            '<div class="dw-mid">' +
            '<a class="dw-name" href="/product/' + esc(it.slug) + '">' + esc(it.name) + '</a>' +
            (it.variant_label ? '<p class="dw-price">' + esc(it.variant_label) + '</p>' : '') +
            '<p class="dw-price">£' + it.price.toFixed(2) + ' each</p>' +
            '<div class="dw-qty">' +
            '<button onclick="cartQty(' + it.id + ', ' + (it.variant_id || 'null') + ', ' + (it.qty - 1) + ')">−</button>' +
            '<span>' + it.qty + '</span>' +
            '<button onclick="cartQty(' + it.id + ', ' + (it.variant_id || 'null') + ', ' + (it.qty + 1) + ')">+</button>' +
            '</div>' +
            '</div>' +
            '<div class="dw-right">' +
            '<b>£' + it.line.toFixed(2) + '</b>' +
            '<button class="dw-remove" onclick="cartRemove(' + it.id + ', ' + (it.variant_id || 'null') + ')" title="Remove">' +
            '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>' +
            '</button>' +
            '</div>' +
            '</div>';
    }

    /* ---------- server calls ---------- */
    function postCart(url, fd) {
        fd.append('_token', csrfToken());
        return fetch(url, {
            method: 'POST',
            body: fd,
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function(r) {
            return r.json().then(function(d) {
                return { ok: r.ok, d: d };
            });
        });
    }

    function addToBag(id, qty, ev, opts) {
        if (ev) {
            ev.preventDefault();
            ev.stopPropagation();
        }
        if (!@json(auth()->check())) {
            requireShoppingLogin(window.location.pathname + window.location.search + window.location.hash);
            return;
        }
        var fd = new FormData();
        fd.append('product_id', id);
        fd.append('qty', qty || 1);
        if (opts && opts.skuVariantId) fd.append('sku_variant_id', opts.skuVariantId);
        postCart("{{ route('cart.add') }}", fd).then(function(res) {
            if (res.ok && res.d.status === 'success') {
                updateBadges(res.d.count);
                showCartToast(res.d.message || 'Added to your bag', 'success');
                loadCart();
                if (opts && opts.open) openDrawer();
            } else {
                showCartToast((res.d && res.d.message) || 'Could not add to bag', 'error');
            }
        }).catch(function() {
            showCartToast('Something went wrong', 'error');
        });
    }

    function cartQty(id, variantId, qty) {
        if (qty <= 0) {
            cartRemove(id, variantId);
            return;
        }
        var fd = new FormData();
        fd.append('product_id', id);
        if (variantId) fd.append('sku_variant_id', variantId);
        fd.append('qty', qty);
        postCart("{{ route('cart.update') }}", fd).then(function(res) {
            if (res.ok && res.d.status === 'success') {
                renderCart(res.d);
                showCartToast('Your bag has been updated', 'success');
            }
            else showCartToast((res.d && res.d.message) || 'Update failed', 'error');
        }).catch(function() {
            showCartToast('Something went wrong', 'error');
        });
    }

    function cartRemove(id, variantId) {
        var fd = new FormData();
        fd.append('product_id', id);
        if (variantId) fd.append('sku_variant_id', variantId);
        postCart("{{ route('cart.remove') }}", fd).then(function(res) {
            if (res.ok) {
                renderCart(res.d);
                if (window.pdSyncRemove) window.pdSyncRemove(id, variantId);
                showCartToast('Removed from your bag', 'success');
            } else showCartToast((res.d && res.d.message) || 'Could not remove this item', 'error');
        }).catch(function() {
            showCartToast('Something went wrong', 'error');
        });
    }

    /* ---------- wire open triggers + ESC ---------- */
    /* DELEGATED on document → .floating-cart (footer) bhi pakda jata hai,
       chahe wo DOM mein is script ke BAAD aata ho. __cxDwBound guard se
       SPA re-runs pe double-binding nahi hoti. */
    if (!window.__cxDwBound) {
        window.__cxDwBound = true;
        document.addEventListener('click', function (e) {
            var t = e.target.closest('.cart-btn, .floating-cart');
            if (t) {
                e.preventDefault();
                openDrawer();
            }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeDrawer();
        });
    }
</script>