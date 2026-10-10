<script src="{{ asset('js/ajax-navigation.js') }}"></script>
<style>
    .product-sale-timer{display:flex;align-items:center;gap:6px;padding:0 20px 16px;color:#8a3c49}
    .product-sale-timer>span:not(.product-sale-timer-label){display:grid;place-items:center;min-width:0;flex:1;padding:5px 2px;border:1px solid #f1d9dd;border-radius:7px;background:#fff8f8}
    .product-sale-timer b{font:700 13px/1.2 'Montserrat',sans-serif}
    .product-sale-timer small{margin-top:2px;color:#967c80;font:600 7px/1.2 'Montserrat',sans-serif}
    .product-sale-timer-label{flex:none;font:700 9px 'Montserrat',sans-serif;text-transform:uppercase}
    @media(max-width:600px){.product-sale-timer{gap:3px;padding-right:11px;padding-left:11px}.product-sale-timer-label{font-size:8px}.product-sale-timer b{font-size:11px}}
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {

        /* 📱 Mobile nav — handled by header.blade.php's own script.
           Do NOT bind navToggle here — double-binding breaks the hamburger. */

        /* ---------- Scroll reveal ---------- */
        var revealEls = document.querySelectorAll('.reveal');
        if ('IntersectionObserver' in window && revealEls.length) {
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('show');
                        io.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.12 });
            revealEls.forEach(function (el) { io.observe(el); });
        } else {
            revealEls.forEach(function (el) { el.classList.add('show'); });
        }

        /* 🎡 Brands marquee — PURE CSS (animation on .brands-track in header). */

        /* ---------- Testimonial slider ---------- */
        var slides = document.querySelectorAll('.testi-slide');
        var dots = document.querySelectorAll('.t-dot');
        var prevBtn = document.getElementById('tPrev');
        var nextBtn = document.getElementById('tNext');
        var current = 0, autoTimer = null;

        function goTo(index) {
            if (!slides.length) return;
            current = (index + slides.length) % slides.length;
            slides.forEach(function (s, i) { s.classList.toggle('active', i === current); });
            dots.forEach(function (d, i) { d.classList.toggle('active', i === current); });
        }
        function startAuto() {
            stopAuto();
            autoTimer = setInterval(function () { goTo(current + 1); }, 6000);
        }
        function stopAuto() { if (autoTimer) clearInterval(autoTimer); }

        if (slides.length) {
            if (prevBtn) prevBtn.addEventListener('click', function () { goTo(current - 1); startAuto(); });
            if (nextBtn) nextBtn.addEventListener('click', function () { goTo(current + 1); startAuto(); });
            dots.forEach(function (dot, i) {
                dot.addEventListener('click', function () { goTo(i); startAuto(); });
            });
            window.addEventListener('ajax:navigating', stopAuto, { once: true });
            window.addEventListener('pagehide', stopAuto, { once: true });
            startAuto();
        }

        /* ---------- Parallax image ---------- */
        var parallaxImg = document.querySelector('[data-parallax]');
        if (parallaxImg) {
            var ticking = false;
            function updateParallax() {
                var rect = parallaxImg.getBoundingClientRect();
                var vh = window.innerHeight || document.documentElement.clientHeight;
                if (rect.bottom > 0 && rect.top < vh) {
                    var progress = (vh - rect.top) / (vh + rect.height);
                    var move = (progress - 0.5) * 60;
                    parallaxImg.style.transform = 'translateY(' + move + 'px)';
                }
                ticking = false;
            }
            window.addEventListener('scroll', function () {
                if (!ticking) {
                    ticking = true;
                    requestAnimationFrame(updateParallax);
                }
            }, { passive: true });
            updateParallax();
        }

        /* ---------- Cart badge + quick add (home demo products) ---------- */
        var cartCount = parseInt((document.querySelector('.cart-badge') || {}).textContent || '0', 10) || 0;
        var badges = document.querySelectorAll('.cart-badge');

        function updateBadges() {
            badges.forEach(function (b) {
                b.textContent = cartCount;
                b.classList.remove('bump');
                void b.offsetWidth;
                b.classList.add('bump');
            });
        }

        document.querySelectorAll('.quick-add').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                cartCount++;
                updateBadges();
            });
        });

        var saleTimers = document.querySelectorAll('[data-sale-countdown-end]');
        var saleCountdownInterval;
        var saleReloading = false;
        function updateSaleCountdowns() {
            saleTimers.forEach(function (timer) {
                var remaining = Math.max(0, new Date(timer.dataset.saleCountdownEnd).getTime() - Date.now());
                var values = {
                    days: Math.floor(remaining / 86400000),
                    hours: Math.floor((remaining % 86400000) / 3600000),
                    minutes: Math.floor((remaining % 3600000) / 60000),
                    seconds: Math.floor((remaining % 60000) / 1000)
                };
                Object.keys(values).forEach(function (unit) {
                    var output = timer.querySelector('[data-sale-' + unit + ']');
                    if (output) output.textContent = String(values[unit]).padStart(2, '0');
                });
                if (remaining === 0 && !saleReloading) {
                    saleReloading = true;
                    window.clearInterval(saleCountdownInterval);
                    window.location.reload();
                }
            });
        }
        if (saleTimers.length) {
            updateSaleCountdowns();
            saleCountdownInterval = window.setInterval(updateSaleCountdowns, 1000);
            var stopSaleCountdowns = function () { window.clearInterval(saleCountdownInterval); };
            window.addEventListener('ajax:navigating', stopSaleCountdowns, { once: true });
            window.addEventListener('pagehide', stopSaleCountdowns, { once: true });
        }
    });
</script>