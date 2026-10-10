{{-- ============================================================
     USER SIDEBAR — Cosmetics Store
     Route-aware active states → works on every user page.
     Live count badges on Orders + Wishlist.
     ⭐ Orders badge = SIRF PAID orders ginta hai
============================================================ --}}
@php
    $user     = auth()->user();
    $initial  = strtoupper(substr($user->name, 0, 1));
    $isActive = fn (...$patterns) => request()->routeIs(...$patterns) ? 'active' : '';

    /* ⭐ live badge count — PAID + in-transit only (unpaid/failed skip) */
    $ordersActiveCount = \App\Models\Order::where('user_id', $user->id)
        ->where('payment_status', 'paid')
        ->whereIn('status', ['pending', 'processing', 'shipped'])
        ->count();
    $openReturnsCount = \App\Models\OrderReturn::where('user_id', $user->id)->where('status', 'pending')->count();
    $wishCount = $user->wishlist()->count();
@endphp

<style>
    /* self-contained badge styles (base doesn't own these) */
    .side-link .s-badge {
        margin-left: auto;
        min-width: 20px;
        height: 20px;
        padding: 0 7px;
        border-radius: 10px;
        background: linear-gradient(135deg, var(--teal), var(--teal-2));
        color: #fff;
        font-size: 10.5px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(56, 170, 180, .35);
    }
    .side-link .s-badge.pink {
        background: linear-gradient(135deg, #ff6b81, #e0566e);
        box-shadow: 0 4px 10px rgba(255, 107, 129, .35);
    }
    .side-link .s-badge[hidden] { display: none; }
</style>

<style>
    .layout,
    .layout > .main,
    .main,
    .main > main,
    .main > section,
    .main > div {
        min-width: 0;
        max-width: 100%;
    }

    .main :is(img, svg, video, canvas) {
        max-width: 100%;
    }

    .main :is(.table-scroll, .table-wrap, .table-responsive) {
        max-width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    @media (max-width: 860px) {
        .layout > .main {
            width: 100% !important;
            max-width: 100% !important;
            margin-left: 0 !important;
        }
    }

    @media (max-width: 560px) {
        .layout > .main {
            padding-left: 14px !important;
            padding-right: 14px !important;
        }

        .main :is(.topbar, .page-head, .page-title, .filters) {
            min-width: 0;
            max-width: 100%;
            flex-wrap: wrap;
        }

        .main :is(.sum-row, .stats, .wish-grid, .prof-grid, .prof-grid2, .submission-layout, .submission-grid) {
            grid-template-columns: minmax(0, 1fr) !important;
        }
    }

    @media (max-width: 380px) {
        .layout > .main {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }
    }
</style>

<aside class="sidebar" id="sidebar">

    {{-- Brand --}}
    <a href="{{ url('/') }}" class="side-brand">
        <span class="brand-tile">C</span>
        <span class="brand-txt">
            <b>Cosmetics</b>
            <small>Beauty Pronounced</small>
        </span>
    </a>

    {{-- Membership badge --}}
    <div class="side-badge">
        <i></i>
        <span>Glow member · <b>since {{ $user->created_at->format('Y') }}</b></span>
    </div>

    {{-- Menu --}}
    <p class="side-label">Menu</p>
    <nav class="side-nav">
        <a href="{{ route('user.dashboard') }}" class="side-link {{ $isActive('user.dashboard') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/></svg>
            Dashboard
        </a>

        <a href="{{ route('user.orders') }}" class="side-link {{ $isActive('user.orders') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 7h12l1.5 13.5a1 1 0 0 1-1 1.1H5.5a1 1 0 0 1-1-1.1L6 7z"/><path d="M9 10V6a3 3 0 0 1 6 0v4"/></svg>
            My Orders
            @if($ordersActiveCount)
            <span class="s-badge" title="{{ $ordersActiveCount }} paid order{{ $ordersActiveCount > 1 ? 's' : '' }} on the way">{{ $ordersActiveCount }}</span>
            @endif
        </a>

        <a href="{{ route('user.returns') }}" class="side-link {{ $isActive('user.returns') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 2.6-6.4L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l3 2"/></svg>
            My Returns
            @if($openReturnsCount)<span class="s-badge">{{ $openReturnsCount }}</span>@endif
        </a>

        <a href="{{ route('user.wishlist') }}" class="side-link {{ $isActive('user.wishlist') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21.2l7.8-7.7 1-1.1a5.5 5.5 0 0 0 0-7.8z"/></svg>
            Wishlist
            <span data-wishlist-count class="s-badge pink" title="{{ $wishCount }} saved item{{ $wishCount === 1 ? '' : 's' }}" @if (! $wishCount) hidden @endif>{{ $wishCount }}</span>
        </a>

        <a href="{{ route('user.product-submissions.index') }}" class="side-link {{ $isActive('user.product-submissions.index') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/><rect x="3" y="3" width="18" height="18" rx="4"/></svg>
            Submit a Product
        </a>
        @if($user->submittedProducts()->exists())
        <a href="{{ route('user.marketplace') }}" class="side-link {{ $isActive('user.marketplace') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10h18M5 10V6l2-3h10l2 3v4M5 10v11h14V10M9 14h6"/></svg>
            Marketplace messages
        </a>
        <a href="{{ route('user.marketplace.withdrawals') }}" class="side-link {{ $isActive('user.marketplace.withdrawals') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h3m5 0h2"/></svg>
            Withdrawals
        </a>
        @endif

        <a href="{{ route('user.product-submissions.submissions') }}" class="side-link {{ $isActive('user.product-submissions.submissions') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M8 6h13M8 12h13M8 18h13"/><path d="M3 6h.01M3 12h.01M3 18h.01"/></svg>
            My Submissions
        </a>

        <a href="{{ route('user.announcements') }}" class="side-link {{ $isActive('user.announcements') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11v2a2 2 0 0 0 2 2h2l4 5V4l-4 5H5a2 2 0 0 0-2 2Z"/><path d="M15 9a5 5 0 0 1 0 6M18 6a9 9 0 0 1 0 12"/></svg>
            Announcements
        </a>

        <a href="{{ route('user.support') }}" class="side-link {{ $isActive('user.support*') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8z"/></svg>
            Support
        </a>
    </nav>

    {{-- Account --}}
    <p class="side-label" style="margin-top:26px;">Account</p>
    <nav class="side-nav">
        <a href="{{ route('user.profile') }}" class="side-link {{ $isActive('user.profile') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            My Profile
        </a>

        <form method="POST" action="{{ route('logout') }}" class="side-form">
            @csrf
            <button type="submit" class="side-link side-link-danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Log out
            </button>
        </form>
    </nav>

    {{-- User card --}}
    <div class="side-user">
        <div class="avatar">{{ $initial }}<span class="online"></span></div>
        <div class="side-user-info">
            <b>{{ $user->name }}</b>
            <span>{{ $user->email }}</span>
        </div>
    </div>

</aside>

<div class="overlay" id="overlay"></div>

<script src="{{ asset('js/ajax-navigation.js') }}"></script>
<script>
/* ==========================================================
   USER SPA ENGINE — no-reload navigation for /user/* pages
   Forms, auth pages, external links → normal reload (safe)
============================================================ */
(function () {
    if (window.__userSPA) return;
    window.__userSPA = true;

    var PREFIX = '/user';
    var SKIP = ['/login', '/register', '/forgot-password', '/reset-password',
                '/verify-email', '/confirm-password', '/logout'];

    function shouldSkip(url) {
        try {
            var path = new URL(url, location.origin).pathname;
            return SKIP.some(function (p) { return path.indexOf(p) === 0; });
        } catch (e) { return true; }
    }

    function ready(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }

    /* reveal + safety net — content can never stay hidden */
    function initReveal() {
        var els = document.querySelectorAll('.reveal');
        if ('IntersectionObserver' in window) {
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (en) {
                    if (en.isIntersecting) { en.target.classList.add('show'); io.unobserve(en.target); }
                });
            }, { threshold: .08 });
            els.forEach(function (el) { el.classList.remove('show'); io.observe(el); });
        }
        setTimeout(function () {
            document.querySelectorAll('.reveal:not(.show)').forEach(function (el) {
                el.classList.add('show');
            });
        }, 900);
    }

    /* re-run page scripts (DOMContentLoaded-safe) */
    function runScripts(scope) {
        var orig = document.addEventListener.bind(document);
        var dcl = [];
        document.addEventListener = function (type, fn, opts) {
            if (type === 'DOMContentLoaded') { dcl.push(fn); return; }
            return orig(type, fn, opts);
        };
        scope.querySelectorAll('script').forEach(function (old) {
            if (old.textContent.indexOf('__userSPA') !== -1) return;
            var s = document.createElement('script');
            [].forEach.call(old.attributes, function (a) { s.setAttribute(a.name, a.value); });
            s.textContent = old.textContent;
            old.parentNode.replaceChild(s, old);
        });
        document.addEventListener = orig;
        dcl.forEach(function (fn) { try { fn({}); } catch (e) { console.error('[userSPA] script error:', e); } });
    }

    /* swap page styles kept in <head> */
    function swapStyles(doc) {
        document.querySelectorAll('head style[data-page]').forEach(function (el) { el.remove(); });
        doc.querySelectorAll('style').forEach(function (el) {
            var c = document.createElement('style');
            c.textContent = el.textContent;
            c.setAttribute('data-page', '');
            document.head.appendChild(c);
        });
    }

    var navLock = false;

    function go(url, push) {
        if (navLock) return;
        var target;
        try { target = new URL(url, location.origin); } catch (e) { return; }
        if (shouldSkip(target.href)) { window.location.href = target.href; return; }

        navLock = true;

        fetch(target.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function (res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.text().then(function (html) { return { html: html, finalUrl: res.url || target.href }; });
            })
            .then(function (r) {
                if (shouldSkip(r.finalUrl)) { window.location.href = r.finalUrl; return; }

                var doc = new DOMParser().parseFromString(r.html, 'text/html');
                document.title = doc.title;

                var ld = doc.body.querySelector('#cosLoader');
                if (ld) ld.remove();

                swapStyles(doc);
                document.body.innerHTML = doc.body.innerHTML;
                window.scrollTo(0, 0);

                runScripts(document.body);
                initReveal();

                if (push) { try { history.pushState({}, '', r.finalUrl); } catch (e) {} }
                navLock = false;
            })
            .catch(function () {
                navLock = false;
                window.location.href = target.href;   /* fallback — never stuck */
            });
    }

    ready(function () {
        document.querySelectorAll('head style').forEach(function (el) { el.setAttribute('data-page', ''); });
        initReveal();

        if (!window.__ajaxNavigationRouter) document.addEventListener('click', function (e) {
            var a = e.target.closest('a');
            if (!a) return;
            var href = a.getAttribute('href');
            if (!href || href.charAt(0) === '#') return;
            if (/^(javascript:|mailto:|tel:)/.test(href)) return;
            if (a.target === '_blank' || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
            var u;
            try { u = new URL(a.href, location.origin); } catch (err) { return; }
            if (u.origin !== location.origin) return;
            if (u.pathname.indexOf(PREFIX) !== 0) return;

            e.preventDefault();
            go(u.href, true);
        });

        if (!window.__ajaxNavigationRouter) window.addEventListener('popstate', function () {
            if (location.pathname.indexOf(PREFIX) === 0) go(location.href, false);
        });
    });
})();
</script>