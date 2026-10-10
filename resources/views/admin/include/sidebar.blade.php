{{-- ==========================================================
     ADMIN SIDEBAR — Ocean/Aqua · Products and E-commerce dropdowns
     SINGLE controller script — hamburger, drawer, collapse,
     accordion, backdrop, ESC, resize. Events exclusively
     claimed (navbar engine double-toggle nahi kar sakta).
     ⭐ v3: NO SCROLLBAR — dropdown open ho to bhi scrollbar nahi
========================================================== --}}
@php
    $admin = auth()->user();
    $initials = collect(explode(' ', trim($admin->name)))
        ->filter()->map(fn ($w) => strtoupper(substr($w, 0, 1)))
        ->take(2)->implode('');

    $inProducts = request()->routeIs(['admin.products.*', 'admin.product-submissions.*', 'admin.categories', 'admin.sizes']);
    $inEcommerce = request()->routeIs('admin.ecommerce.*', 'admin.orders*');
    $inProductSpecification = request()->routeIs('admin.product-specification.*');
    $inBlog = request()->routeIs('admin.blog.*');
    $inMarketplace = request()->routeIs('admin.marketplace.*');
    $inPayment = request()->routeIs('admin.payment.*');
    $inAds = request()->routeIs('admin.ads.*');
    $inFaqs = request()->routeIs('admin.faqs.*');

    $sidebarCounts = \Illuminate\Support\Facades\Cache::store('file')->remember('admin.sidebar.counts', now()->addSeconds(10), function () {
        return [
            'orders' => \App\Models\Order::where('payment_status', 'paid')->where('status', 'pending')->count(),
            'returns' => \App\Models\OrderReturn::where('status', 'pending')->count(),
            'reviews' => \App\Models\Review::where('status', 'pending')->count(),
            'submissions' => \App\Models\Product::where('review_status', 'pending')->count(),
            'support' => \App\Models\SupportMessage::whereNull('read_at')
                ->whereHas('sender', fn ($query) => $query->where('role', 'user'))
                ->count(),
            'contacts' => \App\Models\ContactMessage::whereNull('read_at')->count(),
            'marketplace' => \App\Models\MarketplaceMessage::whereNull('read_at')
                ->whereHas('sender', fn ($query) => $query->where('role', '!=', 'admin'))
                ->count(),
        ];
    });
    $newOrdersCount = $sidebarCounts['orders'];
    $pendingReturns = $sidebarCounts['returns'];
    $pendingReviews = $sidebarCounts['reviews'];
    $pendingSubmissions = $sidebarCounts['submissions'];
    $unreadSupportCount = $sidebarCounts['support'];
    $unreadContactCount = $sidebarCounts['contacts'];
    $unreadMarketplaceCount = $sidebarCounts['marketplace'];
   
@endphp

<style>
    /* ========== SHARED THEME VARS (har admin page + dark mode) ========== */
    :root {
        --bg: #f3f5f7;
        --card: #ffffff;
        --text: #101828;
        --muted: #68788f;
        --border: #e8edf3;
        --forest: #0e5a75;
        --forest-2: #17739b;
        --lime: #3ec5e0;
        --lime-soft: #e4f6fb;
        --topbar: rgba(255, 255, 255, .85);
        --shadow: 0 1px 2px rgba(16, 24, 40, .04), 0 8px 24px rgba(16, 24, 40, .05);
        --radius: 16px;
    }

    body.dark {
        --bg: #0b1220;
        --card: #111c2e;
        --text: #e7edf5;
        --muted: #8ba0b8;
        --border: #1e2c42;
        --lime-soft: rgba(62, 197, 224, .08);
        --topbar: rgba(17, 28, 46, .85);
        --shadow: 0 1px 2px rgba(0, 0, 0, .3), 0 10px 30px rgba(0, 0, 0, .35);
    }

    .reveal { transition: opacity .45s ease, transform .45s cubic-bezier(.22, 1, .36, 1) }

    /* ---------- shell ---------- */
    .sidebar {
        background: linear-gradient(180deg, #0a2531 0%, #071c26 100%) !important;
        border-right: 1px solid rgba(62, 197, 224, .12) !important;
        padding: 20px 14px 16px !important;
    }

    /* ---------- backdrop (sidebar ka apna — NO blur ghost) ---------- */
    .backdrop {
        position: fixed;
        inset: 0;
        z-index: 45;
        background: rgba(2, 6, 23, .55);
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity .3s, visibility .3s;
    }

    .backdrop.show {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }

    body.nb-lock { overflow: hidden; }

    select:not([multiple]):not([size]) {
        -webkit-appearance: none;
        appearance: none;
        padding-right: 42px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2368788f' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m7 10 5 5 5-5'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        background-size: 14px 14px;
        vertical-align: middle;
    }

    select:not([multiple]):not([size]):focus-visible {
        outline: 3px solid rgba(62, 197, 224, .22);
        outline-offset: 1px;
    }

    @media (min-width: 993px) {
        .backdrop { display: none !important; }
    }

    /* ---------- brand ---------- */
    .k-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 4px 8px 18px;
        border-bottom: 1px solid rgba(62, 197, 224, .10);
        margin-bottom: 14px;
    }

    .k-logo {
        width: 42px;
        height: 42px;
        min-width: 42px;
        border-radius: 13px;
        display: grid;
        place-items: center;
        font-family: Georgia, 'Times New Roman', serif;
        font-size: 21px;
        font-weight: 700;
        color: #062a38;
        background: linear-gradient(135deg, #7edcf2, #3fb0d8);
        box-shadow: 0 10px 24px rgba(62, 197, 224, .30);
    }

    .k-name {
        font-family: Georgia, 'Times New Roman', serif;
        font-size: 21px;
        font-weight: 700;
        color: #fff;
        line-height: 1.1;
        white-space: nowrap;
    }

    .k-name span {
        color: var(--lime);
        font-style: italic;
    }

    .k-tag {
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .28em;
        text-transform: uppercase;
        color: rgba(62, 197, 224, .55);
        margin-top: 3px;
        white-space: nowrap;
    }

    /* ---------- access chip ---------- */
    .k-chip {
        display: flex;
        align-items: center;
        gap: 9px;
        margin: 0 6px 8px;
        padding: 10px 13px;
        border-radius: 12px;
        border: 1px solid rgba(62, 197, 224, .22);
        background: rgba(62, 197, 224, .06);
        font-size: 9.5px;
        font-weight: 700;
        letter-spacing: .2em;
        text-transform: uppercase;
        color: rgba(148, 226, 246, .85);
        white-space: nowrap;
    }

    .k-dot {
        width: 7px;
        height: 7px;
        min-width: 7px;
        border-radius: 50%;
        background: var(--lime);
        box-shadow: 0 0 10px var(--lime);
        animation: kPulse 2s infinite;
    }

    @keyframes kPulse {
        0%, 100% { opacity: 1 }
        50% { opacity: .35 }
    }

    /* ---------- sections & links ---------- */
    .k-sec {
        font-size: 9.5px;
        font-weight: 700;
        letter-spacing: .22em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, .32);
        margin: 16px 10px 7px;
        white-space: nowrap;
    }

    .k-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border-radius: 11px;
        color: rgba(255, 255, 255, .66);
        font-weight: 600;
        font-size: 13.5px;
        text-decoration: none;
        position: relative;
        margin-bottom: 3px;
        white-space: nowrap;
        background: transparent;
        border: none;
        width: 100%;
        font-family: inherit;
        text-align: left;
        cursor: pointer;
        transition: color .25s, background .25s, transform .25s;
    }

    .k-link i {
        display: block;
        font-size: 18px;
        width: 22px;
        line-height: 1;
        text-align: center;
        flex-shrink: 0;
    }

    .k-link:hover {
        color: #fff;
        background: rgba(255, 255, 255, .05);
        transform: translateX(3px);
    }

    .k-link.active {
        color: #fff;
        background: rgba(255, 255, 255, .06);
        box-shadow: inset 0 0 0 1px rgba(62, 197, 224, .18);
    }

    .k-link.active i {
        color: var(--lime);
        filter: drop-shadow(0 0 8px rgba(62, 197, 224, .7));
    }

    .k-link.active::before {
        content: '';
        position: absolute;
        left: -14px;
        top: 22%;
        bottom: 22%;
        width: 3.5px;
        border-radius: 6px;
        background: var(--lime);
        box-shadow: 0 0 12px var(--lime);
    }

    /* ---------- orders badge ---------- */
    .k-badge {
        margin-left: auto;
        font-size: 9.5px;
        font-weight: 800;
        background: var(--lime);
        color: #062a38;
        padding: 2px 8px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(62, 197, 224, .4);
    }

    /* ---------- products parent + chevron ---------- */
    .k-parent .k-chev {
        margin-left: auto;
        font-size: 16px;
        width: auto;
        transition: transform .3s cubic-bezier(.22, 1, .36, 1);
        color: rgba(255, 255, 255, .4);
    }

    .k-parent.open .k-chev {
        transform: rotate(180deg);
        color: var(--lime);
    }

    .k-parent.open i:first-child {
        color: var(--lime);
    }

    /* ---------- smooth dropdown (grid-rows animation) ---------- */
    .k-sub {
        display: grid;
        grid-template-rows: 0fr;
        transition: grid-template-rows .38s cubic-bezier(.22, 1, .36, 1);
    }

    .k-sub.open {
        grid-template-rows: 1fr;
    }

    .k-sub-inner {
        overflow: hidden;
        min-height: 0;
    }

    .k-sub-inner > .k-parent {
        width: calc(100% - 14px);
        margin: 4px 0 2px 14px;
        padding: 8px 8px 8px 20px;
        gap: 8px;
        font-size: 12px;
    }

    .k-sub-inner > .k-parent i:first-child {
        width: 16px;
        font-size: 15px;
    }

    .k-sub a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px 8px 20px;
        margin: 2px 0 2px 14px;
        border-left: 1.5px solid rgba(62, 197, 224, .15);
        color: rgba(255, 255, 255, .5);
        font-size: 12.5px;
        font-weight: 600;
        text-decoration: none;
        border-radius: 0 10px 10px 0;
        transition: .22s;
        white-space: nowrap;
    }

    .k-sub a::before {
        content: '';
        width: 5px;
        height: 5px;
        min-width: 5px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .25);
        transition: .22s;
    }

    .k-sub a:hover {
        color: #fff;
        border-left-color: rgba(62, 197, 224, .5);
        transform: translateX(3px);
    }

    .k-sub a:hover::before {
        background: var(--lime);
        box-shadow: 0 0 8px var(--lime);
    }

    .k-sub a.k-sub-active {
        color: var(--lime);
        border-left-color: var(--lime);
        background: linear-gradient(90deg, rgba(62, 197, 224, .10), transparent);
    }

    .k-sub a.k-sub-active::before {
        background: var(--lime);
        box-shadow: 0 0 8px var(--lime);
    }

    /* ---------- logout ---------- */
    .k-link.k-logout { color: rgba(255, 255, 255, .55); }
    .k-link.k-logout i { transition: color .25s, transform .25s; }

    .k-link.k-logout:hover {
        color: #fca5a5;
        background: rgba(248, 113, 113, .08);
        box-shadow: inset 0 0 0 1px rgba(248, 113, 113, .18);
    }

    .k-link.k-logout:hover i {
        color: #f87171;
        transform: translateX(3px);
    }

    .k-logout-form { display: contents; }

    /* ---------- user card ---------- */
    .k-user {
        margin-top: auto;
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 12px;
        border-radius: 14px;
        background: rgba(255, 255, 255, .04);
        border: 1px solid rgba(62, 197, 224, .12);
        white-space: nowrap;
    }

    .k-av {
        width: 38px;
        height: 38px;
        min-width: 38px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        font-size: 13px;
        font-weight: 800;
        color: #062a38;
        background: linear-gradient(135deg, #7edcf2, #3fb0d8);
        box-shadow: 0 8px 18px rgba(62, 197, 224, .30);
        overflow: hidden;
    }

    .k-av img {
        width: 100%;
        height: 100%;
        border-radius: inherit;
        object-fit: cover;
    }

    .k-u-info { min-width: 0; line-height: 1.3; }

    .k-u-info b {
        display: block;
        color: #fff;
        font-size: 13px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .k-u-info span {
        font-size: 11px;
        color: rgba(255, 255, 255, .42);
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
    }

    /* ---------- collapsed: ICONS ONLY ---------- */
    .layout.collapsed .k-name,
    .layout.collapsed .k-tag,
    .layout.collapsed .k-chip,
    .layout.collapsed .k-sec,
    .layout.collapsed .k-u-info,
    .layout.collapsed .k-badge,
    .layout.collapsed .k-sub,
    .layout.collapsed .s-label {
        display: none;   /* display:none — icons perfectly center rehte hain */
    }

    .layout.collapsed .k-brand {
        justify-content: center;
        padding-bottom: 14px;
    }

    .layout.collapsed .k-brand > div:not(.k-logo) { display: none; }

    .layout.collapsed .k-link {
        justify-content: center;
        gap: 0;
        padding: 11px;
    }

    .layout.collapsed .k-link i { font-size: 19px; }
    .layout.collapsed .k-parent .k-chev { display: none; }

    .layout.collapsed .k-user {
        justify-content: center;
        padding: 8px;
    }

    /* ---------- MOBILE DRAWER (≤992px) ---------- */
    @media (max-width: 992px) {
        .sidebar {
            position: fixed !important;
            top: 0;
            left: 0;
            bottom: 0;
            right: auto;
            height: 100dvh;
            width: 268px !important;
            z-index: 60;
            transform: translateX(-105%);
            transition: transform .38s cubic-bezier(.22, 1, .36, 1);
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        /* dono tarah ke markup pe kaam karta hai */
        .layout.mobile-open .sidebar,
        .sidebar.mobile-open {
            transform: none;
            box-shadow: 0 0 60px rgba(0, 0, 0, .35);
        }

        /* mobile pe collapsed labels wapis dikhne chahiye */
        .layout.collapsed .k-name,
        .layout.collapsed .k-tag,
        .layout.collapsed .k-chip,
        .layout.collapsed .k-sec,
        .layout.collapsed .k-u-info,
        .layout.collapsed .s-label {
            display: block;
        }

        .layout.collapsed .k-brand { justify-content: flex-start; }
        .layout.collapsed .k-brand > div:not(.k-logo) { display: block; }
        .layout.collapsed .k-link { justify-content: flex-start; gap: 12px; padding: 10px 12px; }
        .layout.collapsed .k-link i { font-size: 18px; }
        .layout.collapsed .k-user { justify-content: flex-start; padding: 12px; }
        .layout.collapsed .k-parent .k-chev { display: block; }
    }

    /* ═════════ ⭐ NO SCROLLBAR — sidebar scroll bina scrollbar ke ═════════
       Products dropdown open ho to bhi scrollbar nazar nahi aayegi.
       Scroll functionality (mouse wheel / touch) pehle jaisi kaam karti hai. */
    .s-nav {
        scrollbar-width: none;           /* Firefox */
        -ms-overflow-style: none;        /* IE / old Edge */
    }

    /* Shared guardrails for every admin screen at narrow viewport widths. */
    .layout,
    .layout > .main,
    .main,
    .main > main,
    .content,
    .contacts-page,
    .audience-page,
    .manage-page,
    .review-content,
    .submission-content,
    .support-main {
        min-width: 0;
        max-width: 100%;
    }

    .main :is(img, svg, video, canvas) {
        max-width: 100%;
    }

    .main :is(input, select, textarea, button) {
        max-width: 92%;
    }

    .main :is(.table-wrap, .table-scroll, .table-responsive, .table-container) {
        max-width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    @media (max-width: 992px) {
        .layout > .main,
        .layout.collapsed > .main {
            width: 100%;
            margin-left: 0 !important;
        }
    }

    @media (max-width: 680px) {
        .main > main.content,
        .main > .content,
        .main > section.content,
        .main > main.contacts-page,
        .main > main.audience-page,
        .main > main.manage-page,
        .main > main.review-content,
        .main > main.submission-content,
        .main > main.support-main {
            padding-left: 14px !important;
            padding-right: 14px !important;
        }

        .main :is(.r-hero, .r-charts, .r-bottom, .r-stats, .ord-grid, .p-grid, .details, .notes, .form-grid, .form-layout, .settings-grid, .grid2) {
            grid-template-columns: minmax(0, 1fr) !important;
        }

        .main :is(.page-head, .card-head, .toolbar, .head-actions) {
            min-width: 0;
            max-width: 100%;
        }
    }

    @media (max-width: 480px) {
        .main > main.content,
        .main > .content,
        .main > section.content,
        .main > main.contacts-page,
        .main > main.audience-page,
        .main > main.manage-page,
        .main > main.review-content,
        .main > main.submission-content,
        .main > main.support-main {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }
    }

    .s-nav::-webkit-scrollbar {
        display: none;                   /* Chrome / Safari / Edge */
        width: 0;
        height: 0;
    }

    /* sidebar khud (mobile drawer mein overflow-y: auto hota hai) */
    .sidebar {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .sidebar::-webkit-scrollbar {
        display: none;
        width: 0;
        height: 0;
    }
</style>

<aside class="sidebar" id="sidebar">

    <!-- ===== Brand ===== -->
    <div class="k-brand">
        <div class="k-logo">C</div>
        <div>
            <p class="k-name">Cos<span>metic</span></p>
            <p class="k-tag">Admin Console</p>
        </div>
    </div>

    <!-- ===== Access chip ===== -->
    <div class="k-chip"><span class="k-dot"></span>Operations · Full Access</div>

    <!-- ===== Nav ===== -->
    <nav class="s-nav">

        <p class="k-sec s-sec">Overview</p>
        <a href="{{ route('admin.dashboard') }}" class="k-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" title="Dashboard">
            <i class="ri-dashboard-horizontal-line"></i><span class="s-label">Dashboard</span>
        </a>

        <p class="k-sec s-sec">E-commerce</p>
        <button type="button" id="ecommerceToggle"
            class="k-link k-parent {{ $inEcommerce ? 'active open' : '' }}"
            aria-expanded="{{ $inEcommerce ? 'true' : 'false' }}" aria-controls="ecommerceSub" title="E-commerce">
            <i class="ri-shopping-bag-3-line"></i><span class="s-label">E-commerce</span>
            <i class="ri-arrow-down-s-line k-chev"></i>
        </button>
        <div class="k-sub {{ $inEcommerce ? 'open' : '' }}" id="ecommerceSub">
            <div class="k-sub-inner">
                <a href="{{ route('admin.ecommerce.reports') }}" class="k-sub-link {{ request()->routeIs('admin.ecommerce.reports') ? 'k-sub-active' : '' }}"><span class="s-label">Reports</span></a>
                <a href="{{ route('admin.ecommerce.prices') }}" class="k-sub-link {{ request()->routeIs('admin.ecommerce.prices*') ? 'k-sub-active' : '' }}"><span class="s-label">Product prices</span></a>
                <a href="{{ route('admin.ecommerce.inventory') }}" class="k-sub-link {{ request()->routeIs('admin.ecommerce.inventory*') ? 'k-sub-active' : '' }}"><span class="s-label">Inventory</span></a>
                <a href="{{ route('admin.ecommerce.categories') }}" class="k-sub-link {{ request()->routeIs('admin.ecommerce.categories*') ? 'k-sub-active' : '' }}"><span class="s-label">Category products</span></a>
                <a href="{{ route('admin.ecommerce.attributes') }}" class="k-sub-link {{ request()->routeIs('admin.ecommerce.attributes*') ? 'k-sub-active' : '' }}"><span class="s-label">Attributes & variants</span></a>
                <a href="{{ route('admin.ecommerce.collections') }}" class="k-sub-link {{ request()->routeIs('admin.ecommerce.collections*') ? 'k-sub-active' : '' }}"><span class="s-label">Collections</span></a>
                <a href="{{ route('admin.ecommerce.labels') }}" class="k-sub-link {{ request()->routeIs('admin.ecommerce.labels*') ? 'k-sub-active' : '' }}"><span class="s-label">Product labels</span></a>
                <a href="{{ route('admin.ecommerce.brands') }}" class="k-sub-link {{ request()->routeIs('admin.ecommerce.brands*') ? 'k-sub-active' : '' }}"><span class="s-label">Brands</span></a>
                <a href="{{ route('admin.ecommerce.customers.index') }}" class="k-sub-link {{ request()->routeIs('admin.ecommerce.customers.*') ? 'k-sub-active' : '' }}"><span class="s-label">Customers &amp; vendors</span></a>
                <a href="{{ route('admin.ecommerce.coupons') }}" class="k-sub-link {{ request()->routeIs('admin.ecommerce.coupons*') ? 'k-sub-active' : '' }}"><span class="s-label">Coupons</span></a>
                <a href="{{ route('admin.ecommerce.taxes') }}" class="k-sub-link {{ request()->routeIs('admin.ecommerce.taxes*') ? 'k-sub-active' : '' }}"><span class="s-label">Taxes</span></a>
                <a href="{{ route('admin.orders') }}" class="k-sub-link {{ request()->routeIs('admin.orders*') ? 'k-sub-active' : '' }}">
                    <span class="s-label">Orders</span>
                    @if($newOrdersCount) <span class="k-badge">{{ $newOrdersCount }}</span> @endif
                </a>
                <a href="{{ route('admin.ecommerce.returns') }}" class="k-sub-link {{ request()->routeIs('admin.ecommerce.returns*') ? 'k-sub-active' : '' }}">
                    <span class="s-label">Order returns</span>
                    @if($pendingReturns) <span class="k-badge">{{ $pendingReturns }}</span> @endif
                </a>
                <a href="{{ route('admin.ecommerce.shipments') }}" class="k-sub-link {{ request()->routeIs('admin.ecommerce.shipments') ? 'k-sub-active' : '' }}"><span class="s-label">Shipments</span></a>
                <a href="{{ route('admin.ecommerce.invoices') }}" class="k-sub-link {{ request()->routeIs('admin.ecommerce.invoices*', 'admin.ecommerce.orders.*') ? 'k-sub-active' : '' }}"><span class="s-label">Invoices</span></a>
            </div>
        </div>

        <button type="button" id="productSpecificationToggle"
            class="k-link k-parent {{ $inProductSpecification ? 'active open' : '' }}"
            aria-expanded="{{ $inProductSpecification ? 'true' : 'false' }}"
            aria-controls="productSpecificationSub" title="Product Specification">
            <i class="ri-list-settings-line"></i><span class="s-label">Product Specification</span>
            <i class="ri-arrow-down-s-line k-chev"></i>
        </button>
        <div class="k-sub {{ $inProductSpecification ? 'open' : '' }}" id="productSpecificationSub">
            <div class="k-sub-inner">
                <a href="{{ route('admin.product-specification.groups') }}" class="k-sub-link {{ request()->routeIs('admin.product-specification.groups*') ? 'k-sub-active' : '' }}"><span class="s-label">Groups</span></a>
                <a href="{{ route('admin.product-specification.attributes') }}" class="k-sub-link {{ request()->routeIs('admin.product-specification.attributes*') ? 'k-sub-active' : '' }}"><span class="s-label">Attributes</span></a>
            </div>
        </div>

        <button type="button" id="marketplaceToggle"
            class="k-link k-parent {{ $inMarketplace ? 'active open' : '' }}"
            aria-expanded="{{ $inMarketplace ? 'true' : 'false' }}" aria-controls="marketplaceSub" title="Marketplace">
            <i class="ri-store-2-line"></i><span class="s-label">Marketplace</span>
            <i class="ri-arrow-down-s-line k-chev"></i>
        </button>
        <div class="k-sub {{ $inMarketplace ? 'open' : '' }}" id="marketplaceSub">
            <div class="k-sub-inner">
                <a href="{{ route('admin.marketplace.stores') }}" class="k-sub-link {{ request()->routeIs('admin.marketplace.stores') ? 'k-sub-active' : '' }}"><span class="s-label">Stores</span></a>
                <a href="{{ route('admin.marketplace.withdrawals') }}" class="k-sub-link {{ request()->routeIs('admin.marketplace.withdrawals*') ? 'k-sub-active' : '' }}"><span class="s-label">Withdrawals</span></a>
                <a href="{{ route('admin.marketplace.vendors') }}" class="k-sub-link {{ request()->routeIs('admin.marketplace.vendors') || request()->routeIs('admin.marketplace.vendors.show') ? 'k-sub-active' : '' }}"><span class="s-label">Vendors</span></a>
                <a href="{{ route('admin.marketplace.vendors.unverified') }}" class="k-sub-link {{ request()->routeIs('admin.marketplace.vendors.unverified') ? 'k-sub-active' : '' }}"><span class="s-label">Unverified vendors</span></a>
                <a href="{{ route('admin.marketplace.messages') }}" class="k-sub-link {{ request()->routeIs('admin.marketplace.messages*') ? 'k-sub-active' : '' }}">
                    <span class="s-label">Messages</span>
                    @if($unreadMarketplaceCount) <span class="k-badge">{{ $unreadMarketplaceCount }}</span> @endif
                </a>
            </div>
        </div>

        <button type="button" id="blogToggle"
            class="k-link k-parent {{ $inBlog ? 'active open' : '' }}"
            aria-expanded="{{ $inBlog ? 'true' : 'false' }}" aria-controls="blogSub" title="Blog">
            <i class="ri-article-line"></i><span class="s-label">Blog</span>
            <i class="ri-arrow-down-s-line k-chev"></i>
        </button>
        <div class="k-sub {{ $inBlog ? 'open' : '' }}" id="blogSub">
            <div class="k-sub-inner">
                <a href="{{ route('admin.blog.posts') }}" class="k-sub-link {{ request()->routeIs('admin.blog.posts*') ? 'k-sub-active' : '' }}"><span class="s-label">Posts</span></a>
                <a href="{{ route('admin.blog.categories') }}" class="k-sub-link {{ request()->routeIs('admin.blog.categories*') ? 'k-sub-active' : '' }}"><span class="s-label">Categories</span></a>
                <a href="{{ route('admin.blog.tags') }}" class="k-sub-link {{ request()->routeIs('admin.blog.tags*') ? 'k-sub-active' : '' }}"><span class="s-label">Tags</span></a>
                <a href="{{ route('admin.blog.reports') }}" class="k-sub-link {{ request()->routeIs('admin.blog.reports') ? 'k-sub-active' : '' }}"><span class="s-label">Reports</span></a>
                <a href="{{ route('admin.blog.seo') }}" class="k-sub-link {{ request()->routeIs('admin.blog.seo*') ? 'k-sub-active' : '' }}"><span class="s-label">SEO</span></a>
            </div>
        </div>

        <button type="button" id="paymentToggle"
            class="k-link k-parent {{ $inPayment ? 'active open' : '' }}"
            aria-expanded="{{ $inPayment ? 'true' : 'false' }}" aria-controls="paymentSub" title="Payment">
            <i class="ri-bank-card-line"></i><span class="s-label">Payment</span>
            <i class="ri-arrow-down-s-line k-chev"></i>
        </button>
        <div class="k-sub {{ $inPayment ? 'open' : '' }}" id="paymentSub">
            <div class="k-sub-inner">
                <a href="{{ route('admin.payment.transactions') }}" class="k-sub-link {{ request()->routeIs('admin.payment.transactions') ? 'k-sub-active' : '' }}"><span class="s-label">Transactions</span></a>
                <a href="{{ route('admin.payment.logs') }}" class="k-sub-link {{ request()->routeIs('admin.payment.logs') ? 'k-sub-active' : '' }}"><span class="s-label">Payment logs</span></a>
            </div>
        </div>

        <button type="button" id="adsToggle"
            class="k-link k-parent {{ $inAds ? 'active open' : '' }}"
            aria-expanded="{{ $inAds ? 'true' : 'false' }}" aria-controls="adsSub" title="Ads">
            <i class="ri-megaphone-line"></i><span class="s-label">Ads</span>
            <i class="ri-arrow-down-s-line k-chev"></i>
        </button>
        <div class="k-sub {{ $inAds ? 'open' : '' }}" id="adsSub">
            <div class="k-sub-inner">
                <a href="{{ route('admin.ads.index') }}" class="k-sub-link {{ request()->routeIs('admin.ads.index', 'admin.ads.create', 'admin.ads.edit') ? 'k-sub-active' : '' }}"><span class="s-label">Ads</span></a>
                <a href="{{ route('admin.ads.settings') }}" class="k-sub-link {{ request()->routeIs('admin.ads.settings*') ? 'k-sub-active' : '' }}"><span class="s-label">Ads settings</span></a>
            </div>
        </div>

        <button type="button" id="faqsToggle"
            class="k-link k-parent {{ $inFaqs ? 'active open' : '' }}"
            aria-expanded="{{ $inFaqs ? 'true' : 'false' }}" aria-controls="faqsSub" title="FAQs">
            <i class="ri-question-answer-line"></i><span class="s-label">FAQs</span>
            <i class="ri-arrow-down-s-line k-chev"></i>
        </button>
        <div class="k-sub {{ $inFaqs ? 'open' : '' }}" id="faqsSub">
            <div class="k-sub-inner">
                <a href="{{ route('admin.faqs.index') }}" class="k-sub-link {{ request()->routeIs('admin.faqs.index', 'admin.faqs.create', 'admin.faqs.edit') ? 'k-sub-active' : '' }}"><span class="s-label">FAQs</span></a>
                <a href="{{ route('admin.faqs.categories') }}" class="k-sub-link {{ request()->routeIs('admin.faqs.categories*') ? 'k-sub-active' : '' }}"><span class="s-label">Categories</span></a>
            </div>
        </div>
        <a href="{{ route('admin.newsletters.index') }}" class="k-link {{ request()->routeIs('admin.newsletters.*') ? 'active' : '' }}" title="Newsletter subscribers">
            <i class="ri-mail-line"></i><span class="s-label">Newsletters</span>
        </a>
        <a href="{{ route('admin.media.index') }}" class="k-link {{ request()->routeIs('admin.media.*') ? 'active' : '' }}" title="Media library">
            <i class="ri-folder-image-line"></i><span class="s-label">Media</span>
        </a>

        <p class="k-sec s-sec">Management</p>
        <a href="{{ route('admin.users') }}" class="k-link {{ request()->routeIs('admin.users') ? 'active' : '' }}" title="Users">
            <i class="ri-user-3-line"></i><span class="s-label">Users</span>
        </a>

        {{-- ===== PRODUCTS (dropdown) ===== --}}
        <button type="button" id="productsToggle"
            class="k-link k-parent {{ $inProducts ? 'active open' : '' }}"
            title="Products">
            <i class="ri-box-3-line"></i><span class="s-label">Products</span>
            <i class="ri-arrow-down-s-line k-chev"></i>
        </button>

        <div class="k-sub {{ $inProducts ? 'open' : '' }}" id="productsSub">
            <div class="k-sub-inner">
                <a href="{{ route('admin.categories') }}" class="k-sub-link {{ request()->routeIs('admin.categories') ? 'k-sub-active' : '' }}">
                    <span class="s-label">Categories</span>
                </a>
                <a href="{{ route('admin.sizes') }}" class="k-sub-link {{ request()->routeIs('admin.sizes') ? 'k-sub-active' : '' }}">
                    <span class="s-label">ML / Litres</span>
                </a>
                <a href="{{ route('admin.products.create') }}" class="k-sub-link {{ request()->routeIs('admin.products.create') ? 'k-sub-active' : '' }}">
                    <span class="s-label">Add New Product</span>
                </a>
                <a href="{{ route('admin.products.index') }}" class="k-sub-link {{ request()->routeIs('admin.products.index') || request()->routeIs('admin.products.show') ? 'k-sub-active' : '' }}">
                    <span class="s-label">All Products</span>
                </a>
                <a href="{{ route('admin.product-submissions.index') }}" class="k-sub-link {{ request()->routeIs('admin.product-submissions.*') ? 'k-sub-active' : '' }}">
                    <span class="s-label">Customer Submissions</span>
                    @if($pendingSubmissions) <span class="k-badge">{{ $pendingSubmissions }}</span> @endif
                </a>
            </div>
        </div>

        <a href="{{ route('admin.support') }}" class="k-link {{ request()->routeIs('admin.support*') ? 'active' : '' }}" title="Customer Support">
            <i class="ri-message-3-line"></i><span class="s-label">Support Inbox</span>
            <span id="supportUnreadBadge" class="k-badge" style="{{ $unreadSupportCount ? '' : 'display:none' }}">{{ $unreadSupportCount }}</span>
        </a>

        <a href="{{ route('admin.contacts') }}" class="k-link {{ request()->routeIs('admin.contacts*') ? 'active' : '' }}" title="Contact Messages">
            <i class="ri-mail-open-line"></i><span class="s-label">Contact</span>
            @if($unreadContactCount) <span class="k-badge">{{ $unreadContactCount }}</span> @endif
        </a>

        <a href="{{ route('admin.announcements') }}" class="k-link {{ request()->routeIs('admin.announcements') ? 'active' : '' }}" title="Announcements">
            <i class="ri-megaphone-line"></i><span class="s-label">Announcements</span>
        </a>
        <a href="{{ route('admin.announcements.manage') }}" class="k-link {{ request()->routeIs('admin.announcements.manage', 'admin.announcements.customers') ? 'active' : '' }}" title="Notice Manager">
            <i class="ri-table-line"></i><span class="s-label">Notice Manager</span>
        </a>

        <a href="{{ route('admin.payments') }}" class="k-link {{ request()->routeIs('admin.payments*') ? 'active' : '' }}" title="Payment Settings">
            <i class="ri-bank-card-line"></i><span class="s-label">Payment Settings</span>
        </a>

                {{-- 🚚 SHIPPING — delivery settings --}}
        <a href="{{ route('admin.shipping') }}" class="k-link {{ request()->routeIs('admin.shipping*') ? 'active' : '' }}" title="Shipping">
            <i class="ri-truck-line"></i><span class="s-label">Shipping</span>
        </a>

        <p class="k-sec s-sec">System</p>
        <a href="{{ route('admin.mail') }}" class="k-link {{ request()->routeIs('admin.mail') ? 'active' : '' }}" title="Mail Settings">
            <i class="ri-mail-line"></i><span class="s-label">Mail Settings</span>
        </a>

        

        <p class="k-sec s-sec">Frontend</p>
        <a href="{{ route('admin.home-settings') }}" class="k-link {{ request()->routeIs('admin.home-settings*') ? 'active' : '' }}" title="Home Page Content">
            <i class="ri-home-gear-line"></i><span class="s-label">Home Page Content</span>
        </a>
        <a href="{{ route('admin.home-slider') }}" class="k-link {{ request()->routeIs('admin.home-slider*') ? 'active' : '' }}" title="Manage the homepage brand slider">
            <i class="ri-slideshow-3-line"></i><span class="s-label">Slider</span>
        </a>
        <a href="{{ route('admin.page-headers') }}" class="k-link {{ request()->routeIs('admin.page-headers*') ? 'active' : '' }}" title="Edit storefront page header images and titles">
            <i class="ri-image-edit-line"></i><span class="s-label">Page Headers</span>
        </a>
        <a href="{{ route('admin.reviews') }}" class="k-link {{ request()->routeIs('admin.reviews*') ? 'active' : '' }}" title="Manage customer testimonials">
            <i class="ri-chat-quote-line"></i><span class="s-label">Testimonials</span>
            @if($pendingReviews) <span class="k-badge">{{ $pendingReviews }}</span> @endif
        </a>
        <a href="{{ route('admin.store-settings') }}" class="k-link {{ request()->routeIs('admin.store-settings*') ? 'active' : '' }}" title="Edit storefront contact details and statistics">
            <i class="ri-contacts-line"></i><span class="s-label">Contact Page</span>
        </a>
        <a href="{{ route('admin.footer-settings') }}" class="k-link {{ request()->routeIs('admin.footer-settings*') ? 'active' : '' }}" title="Manage storefront footer content and links">
            <i class="ri-layout-bottom-line"></i><span class="s-label">Footer</span>
        </a>
        <a href="{{ route('admin.about-settings') }}" class="k-link {{ request()->routeIs('admin.about-settings*') ? 'active' : '' }}" title="Edit storefront about page">
            <i class="ri-article-line"></i><span class="s-label">About Page</span>
        </a>


        <p class="k-sec s-sec">Account</p>

        {{-- 👤 MY PROFILE --}}
        <a href="{{ route('admin.profile') }}" class="k-link {{ request()->routeIs('admin.profile') ? 'active' : '' }}" title="My Profile">
            <i class="ri-user-settings-line"></i><span class="s-label">My Profile</span>
        </a>

        <form method="POST" action="{{ route('logout') }}" class="k-logout-form">
            @csrf
            <button type="submit" class="k-link k-logout" title="Log Out">
                <i class="ri-logout-box-r-line"></i><span class="s-label">Log Out</span>
            </button>
        </form>




    </nav>

    

    <!-- ===== User card (photo support ke saath) ===== -->
    <div class="k-user">
        <div class="k-av">
            @if ($admin->profile_photo)
            <img src="{{ asset('storage/' . $admin->profile_photo) }}" alt="{{ $admin->name }}">
            @else
            {{ $initials }}
            @endif
        </div>
        <div class="k-u-info">
            <b>{{ $admin->name }}</b>
            <span>Super Admin</span>
        </div>
    </div>

</aside>

<div class="backdrop" id="backdrop"></div>
@include('admin.include.delete-confirmation')

<script src="{{ asset('js/ajax-navigation.js') }}"></script>
<script>
    /* ==========================================================
       NICEDASH SIDEBAR CONTROLLER v2 — SINGLE source of truth
       Handles: #menuBtn (mobile drawer + desktop collapse),
       both accordions, backdrop, ESC, resize.
       stopImmediatePropagation se navbar engine in clicks pe
       dobara toggle nahi kar sakta (double-toggle FIX).
    ============================================================ */
    (function () {
        if (window.__ndSidebar) return;
        window.__ndSidebar = true;

        var MOBILE_BP = 992;

        function layoutEl()   { return document.getElementById('layout') || document.querySelector('.layout'); }
        function sidebarEl()  { return document.getElementById('sidebar') || document.querySelector('.sidebar'); }
        function backdropEl() { return document.getElementById('backdrop'); }
        function isMobile()   { return window.innerWidth <= MOBILE_BP; }
        function isOpen()     { var s = sidebarEl(); return !!(s && s.classList.contains('mobile-open')); }

        function openMobile() {
            var l = layoutEl(), s = sidebarEl(), b = backdropEl();
            if (l) l.classList.add('mobile-open');
            if (s) s.classList.add('mobile-open');
            if (b) b.classList.add('show');
            document.body.classList.add('nb-lock');
        }

        function closeMobile() {
            var l = layoutEl(), s = sidebarEl(), b = backdropEl();
            if (l) l.classList.remove('mobile-open');
            if (s) s.classList.remove('mobile-open');
            if (b) b.classList.remove('show');
            document.body.classList.remove('nb-lock');
        }

        function toggleMobile() { isOpen() ? closeMobile() : openMobile(); }

        function toggleCollapse() {
            var l = layoutEl();
            if (l) l.classList.toggle('collapsed');
        }

        function toggleAccordion(btn) {
            btn.classList.toggle('open');
            var subId = {
                productsToggle: 'productsSub',
                ecommerceToggle: 'ecommerceSub',
                productSpecificationToggle: 'productSpecificationSub',
                marketplaceToggle: 'marketplaceSub',
                blogToggle: 'blogSub',
                paymentToggle: 'paymentSub',
                adsToggle: 'adsSub',
                faqsToggle: 'faqsSub'
            }[btn.id];
            var sub = document.getElementById(subId);
            if (sub) sub.classList.toggle('open');
            btn.setAttribute('aria-expanded', btn.classList.contains('open') ? 'true' : 'false');
        }

        function claim(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }

        document.addEventListener('click', function (e) {
            if (!e.target || !e.target.closest) return;

            /* 1. hamburger — mobile: drawer · desktop: collapse */
            if (e.target.closest('#menuBtn')) {
                claim(e);
                if (isMobile()) toggleMobile();
                else toggleCollapse();
                return;
            }

            /* 2. backdrop click → close drawer */
            if (e.target.closest('#backdrop')) { closeMobile(); return; }

            /* 3. products accordion */
            var pt = e.target.closest('#productsToggle, #ecommerceToggle, #productSpecificationToggle, #marketplaceToggle, #blogToggle, #paymentToggle, #adsToggle, #faqsToggle');
            if (pt) {
                claim(e);
                var l = layoutEl();
                if (!isMobile() && l && l.classList.contains('collapsed')) {
                    l.classList.remove('collapsed');   /* collapsed tha → pehle expand */
                    return;
                }
                toggleAccordion(pt);
                return;
            }

        });

        document.addEventListener('click', function (e) {
            if (!isMobile() || !isOpen() || !e.target || !e.target.closest) return;
            if (e.target.closest('#sidebar a')) closeMobile();
        }, true);

        window.addEventListener('ajax:navigating', closeMobile);
        window.addEventListener('pagehide', closeMobile);

        /* ESC closes drawer */
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && isOpen()) closeMobile();
        });

        /* resized to desktop → auto-close drawer */
        window.addEventListener('resize', function () {
            if (!isMobile() && isOpen()) closeMobile();
        });
    })();
</script>