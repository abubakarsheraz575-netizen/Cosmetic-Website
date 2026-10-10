<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $seoMeta['title'] ?? 'Cosmetics Store — Beauty Pronounced' }}</title>
    @if(isset($seoMeta))
    <meta name="description" content="{{ $seoMeta['description'] }}">
    <meta name="robots" content="{{ $seoMeta['indexable'] ? 'index,follow' : 'noindex,nofollow' }}">
    <link rel="canonical" href="{{ $seoMeta['canonical'] }}">
    @endif
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}?v=3">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Open+Sans:wght@400;600&family=Allura&display=swap" rel="stylesheet">

    <style>
        :root {
            --teal: #38aab4;
            --teal-dark: #2f98a1;
            --dark: #1b1d21;
            --text: #666;
            --bg: #f7f8f8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Open Sans', sans-serif;
            font-size: 15px;
            line-height: 1.75;
            color: var(--text);
            background: #fff;
            overflow-x: hidden;
        }

        img { max-width: 100%; display: block; }

        a { text-decoration: none; color: inherit; transition: color .3s; }

        ul { list-style: none; }

        h1, h2, h3, h4 {
            font-family: 'Montserrat', sans-serif;
            color: var(--dark);
            line-height: 1.3;
        }

        .container { max-width: 1170px; margin: 0 auto; padding: 0 20px; }

        section { scroll-margin-top: 20px; }

        /* ---------- SCROLL REVEAL ---------- */
        .reveal { opacity: 0; transform: translateY(28px); transition: opacity .8s ease, transform .8s cubic-bezier(.22, 1, .36, 1); }
        .reveal.show { opacity: 1; transform: none; }

        /* ---------- SECTION HEADINGS ---------- */
        .sec-head { text-align: center; max-width: 660px; margin: 0 auto 55px; }

        .sec-tag {
            color: var(--teal);
            font-family: 'Montserrat', sans-serif;
            font-size: 14px;
            font-weight: 500;
            letter-spacing: 2.5px;
            text-transform: uppercase;
        }

        .sec-title {
            font-size: clamp(26px, 4vw, 36px);
            font-weight: 400;
            text-transform: uppercase;
            margin: 12px 0 10px;
            letter-spacing: .5px;
        }

        .sec-sub { color: #7a7f84; }

        /* ---------- BUTTONS ---------- */
        .btn {
            display: inline-block;
            padding: 15px 40px;
            font-family: 'Montserrat', sans-serif;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 3px;
            text-transform: uppercase;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all .35s;
        }

        .btn-outline { border-color: rgba(255, 255, 255, .85); color: #fff; background: transparent; }
        .btn-outline:hover { background: #fff; color: #111; }

        .btn-teal { background: var(--teal); color: #fff; }
        .btn-teal:hover {
            background: var(--teal-dark);
            box-shadow: 0 12px 28px rgba(56, 170, 180, .35);
            transform: translateY(-2px);
        }

        /* ================= HEADER — SINGLE ROW ================= */
        .site-header {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: transparent;
        }

        .header-inner {
            display: flex;
            align-items: center;
            gap: 22px;
            padding: 14px 28px;
            margin-top: 30px;
        }

        .logo {
            font-family: 'Allura', cursive;
            font-size: 34px;
            line-height: 1;
            color: #fff;
            flex-shrink: 0;
        }

        .main-nav {
            display: flex;
            align-items: center;
            gap: 26px;
            flex-wrap: wrap;
            justify-content: center;
            flex: 1;
        }

        .main-nav a {
            font-family: 'Montserrat', sans-serif;
            font-size: 12.5px;
            font-weight: 500;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, .85);
            padding: 4px 0;
            position: relative;
            white-space: nowrap;
        }

        .main-nav a::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -2px;
            width: 0;
            height: 2px;
            background: #a9e8ee;
            transition: width .3s;
        }

        .main-nav a:hover::after,
        .main-nav a.active::after { width: 100%; }

        .main-nav a:hover,
        .main-nav a.active { color: #fff; }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 11px;
            margin-left: auto;
            flex-shrink: 0;
        }

        /* ================= WHITE CIRCLE ICON BUTTONS ================= */
        .ic-circle {
            width: 42px;
            height: 42px;
            min-width: 42px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1.5px solid rgba(255, 255, 255, .6);
            background: rgba(255, 255, 255, .12);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            cursor: pointer;
            position: relative;
            transition: transform .3s, background .3s, border-color .3s, box-shadow .3s;
            font-family: inherit;
            color: #fff;
        }

        .ic-circle:hover {
            border-color: #fff;
            background: rgba(255, 255, 255, .24);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, .18);
        }

        /* badge (floating footer cart uses this) */
        .cart-badge {
            position: absolute;
            top: -3px;
            right: -3px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #2bc155;
            color: #fff;
            font-family: 'Montserrat', sans-serif;
            font-size: 10px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid rgba(255, 255, 255, .9);
            box-shadow: 0 3px 8px rgba(0, 0, 0, .2);
        }

        .cart-badge.bump { animation: bump .45s ease; }

        @keyframes bump {
            0% { transform: scale(1); }
            40% { transform: scale(1.55); }
            100% { transform: scale(1); }
        }

        /* ================= USER ACCOUNT ================= */
        .user-dd { position: relative; display: inline-flex; }

        .u-av {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--teal), #2b7fb8);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font: 700 11px 'Montserrat', sans-serif;
            letter-spacing: .5px;
        }

        .user-btn .u-ic { width: 18px; height: 18px; }

        .user-panel {
            position: absolute;
            top: calc(100% + 14px);
            right: 0;
            z-index: 1300;
            width: 260px;
            background: #fff;
            border: 1px solid #eef1f2;
            border-radius: 16px;
            box-shadow: 0 24px 60px rgba(16, 24, 40, .2);
            overflow: hidden;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px) scale(.96);
            transform-origin: top right;
            transition: .32s cubic-bezier(.22, 1, .36, 1);
            text-align: left;
        }

        .user-dd.open .user-panel { opacity: 1; visibility: visible; transform: none; }

        .up-head {
            background: linear-gradient(135deg, #0d3b40, #155e57);
            color: #fff;
            padding: 18px 20px;
            position: relative;
            overflow: hidden;
        }

        .up-head::after {
            content: '';
            position: absolute;
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .1);
            top: -40px;
            right: -25px;
        }

        .up-head b { display: block; font: 600 14px 'Montserrat', sans-serif; position: relative; z-index: 1; }

        .up-head span {
            font-size: 11.5px;
            color: rgba(255, 255, 255, .72);
            word-break: break-all;
            position: relative;
            z-index: 1;
        }

        .up-menu { padding: 8px; }

        .up-item {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 10px 12px;
            border-radius: 10px;
            font: 600 13px 'Montserrat', sans-serif;
            color: #1b1d21;
            transition: .22s;
            width: 100%;
            border: none;
            background: none;
            cursor: pointer;
            font-family: inherit;
            text-align: left;
        }

        .up-item:hover { background: #f0f7f7; color: var(--teal-dark); transform: translateX(3px); }
        .up-item svg { flex-shrink: 0; }

        .up-item.out { color: #d64545; }
        .up-item.out:hover { background: rgba(214, 69, 69, .07); color: #d64545; }

        .up-sep { height: 1px; background: #eef1f2; margin: 6px 8px; }

        .m-only { display: none; }

        .nav-toggle {
            display: none;
            flex-direction: column;
            gap: 5px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 6px;
            flex-shrink: 0;
        }

        .nav-toggle span { width: 24px; height: 2px; background: #fff; transition: .3s; }

        .nav-toggle.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
        .nav-toggle.open span:nth-child(2) { opacity: 0; }
        .nav-toggle.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

        /* ================= LIGHT-HEADER AUTO MODE ================= */
        .site-header.on-light .ic-circle {
            border-color: rgba(27, 29, 33, .28);
            background: rgba(27, 29, 33, .05);
            color: var(--dark);
        }

        .site-header.on-light .ic-circle:hover {
            border-color: var(--teal);
            background: rgba(56, 170, 180, .1);
            color: var(--teal-dark);
            box-shadow: 0 8px 20px rgba(56, 170, 180, .18);
        }

        .site-header.on-light .nav-toggle span { background: var(--dark); }

        .site-header.on-light .user-panel { top: calc(100% + 12px); }

        /* ================= HERO ================= */
        .hero {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: #0d3b40 url('https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=1920&q=80') right center / cover no-repeat;
        }

        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, rgba(7, 46, 51, .78) 0%, rgba(7, 46, 51, .5) 45%, rgba(7, 46, 51, .12) 100%);
        }

        .hero::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            top: 0;
            height: 180px;
            background: linear-gradient(180deg, rgba(7, 46, 51, .6), rgba(7, 46, 51, 0));
            pointer-events: none;
        }

        .hero .container { position: relative; z-index: 2; width: 100%; }

        .hero-content { max-width: 660px; color: #fff; padding: 120px 0 90px; }

        .hero-kicker, .hero-title, .hero-text, .hero-content .btn {
            animation: heroUp .9s cubic-bezier(.22, 1, .36, 1) both;
        }

        .hero-title { animation-delay: .12s; }
        .hero-text { animation-delay: .24s; }
        .hero-content .btn { animation-delay: .36s; }

        @keyframes heroUp {
            from { opacity: 0; transform: translateY(34px); }
            to { opacity: 1; transform: none; }
        }

        .hero-kicker {
            font-family: 'Montserrat', sans-serif;
            font-size: 17px;
            font-weight: 500;
            letter-spacing: 2.5px;
            text-transform: uppercase;
        }

        .hero-title {
            font-size: clamp(46px, 7.5vw, 92px);
            font-weight: 300;
            color: #fff;
            text-transform: uppercase;
            line-height: 1.12;
            margin: 18px 0 26px;
        }

        .hero-text {
            color: rgba(255, 255, 255, .92);
            font-size: 16px;
            max-width: 570px;
            margin-bottom: 40px;
        }

        /* ================= PRODUCTS ================= */
        .products-sec { padding: 95px 0; background: var(--bg); }
        .latest-sec { padding: 0 0 95px; background: var(--bg); }

        .product-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 28px; }

        .product-card { cursor: pointer; }

        .product-thumb { position: relative; overflow: hidden; aspect-ratio: 3 / 3.55; background: #ececec; }

        .product-thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform .6s ease; }

        .product-card:hover .product-thumb img { transform: scale(1.07); }

        .quick-add {
            position: absolute;
            top: 14px;
            right: 14px;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #fff;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1b1d21;
            opacity: 0;
            transform: translateY(-8px);
            transition: all .35s;
            box-shadow: 0 8px 20px rgba(0, 0, 0, .18);
        }

        .product-card:hover .quick-add { opacity: 1; transform: none; }
        .quick-add:hover { background: var(--teal); color: #fff; }

        .product-cat { font-size: 13px; color: #9aa0a4; margin: 16px 0 3px; }

        .product-name {
            font-family: 'Montserrat', sans-serif;
            font-size: 16px;
            font-weight: 600;
            letter-spacing: .5px;
            text-transform: uppercase;
            color: var(--dark);
        }

        .product-name a:hover { color: var(--teal); }

        .product-price {
            margin-top: 5px;
            font-family: 'Montserrat', sans-serif;
            font-size: 15px;
            font-weight: 600;
            color: #333;
        }

        /* ================= PROMO ================= */
        .promo-sec { padding: 0 0 95px; background: var(--bg); }

        .promo-card {
            display: grid;
            grid-template-columns: 1.05fr .95fr;
            background: #fff;
            box-shadow: 0 24px 70px rgba(0, 0, 0, .08);
            overflow: hidden;
            transition: transform .4s ease, box-shadow .4s ease;
        }

        .promo-card:hover { transform: translateY(-5px); box-shadow: 0 34px 80px rgba(0, 0, 0, .12); }

        .promo-img { position: relative; overflow: hidden; min-height: 480px; }

        .promo-img img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 5s ease;
        }

        .promo-card:hover .promo-img img { transform: scale(1.08); }

        .promo-badge {
            position: absolute;
            left: 28px;
            bottom: 28px;
            background: rgba(255, 255, 255, .95);
            padding: 16px 22px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 14px 34px rgba(0, 0, 0, .16);
        }

        .promo-badge .num {
            font-family: 'Montserrat', sans-serif;
            font-size: 32px;
            font-weight: 700;
            color: var(--teal);
            line-height: 1;
        }

        .promo-badge p { font-size: 12.5px; line-height: 1.45; color: #555; max-width: 150px; }

        .promo-body {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 70px 60px;
        }

        .promo-body::before {
            content: 'glow';
            position: absolute;
            top: 30px;
            right: 44px;
            font-family: 'Allura', cursive;
            font-size: 70px;
            line-height: 1;
            color: rgba(56, 170, 180, .13);
            pointer-events: none;
        }

        .promo-body .sec-title { margin-bottom: 14px; }

        .promo-body p { max-width: 400px; margin-bottom: 32px; }

        /* ================= FEATURES ================= */
        .features-sec { padding: 95px 0 65px; background: var(--bg); }

        .feature-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 40px; }

        .feature { text-align: center; padding: 0 18px; }

        .feature-icon {
            width: 76px;
            height: 76px;
            border: 1px solid var(--teal);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 22px;
            color: var(--teal);
            transition: all .35s;
        }

        .feature:hover .feature-icon {
            background: var(--teal);
            color: #fff;
            box-shadow: 0 12px 30px rgba(56, 170, 180, .35);
            transform: rotate(8deg);
        }

        .feature h3 {
            font-size: 19px;
            font-weight: 500;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        /* ================= BRANDS SLIDER ================= */
        .brands-sec { padding: 34px 0; background: #f8fafb; border-block: 1px solid #e9eef1; overflow: hidden; }
        .brands-heading { width: min(1240px, calc(100% - 48px)); margin: 0 auto 20px; }
        .brands-heading p { margin-bottom: 6px; color: var(--teal); font: 700 10px 'Montserrat', sans-serif; letter-spacing: 2px; }
        .brands-heading h2 { color: #18333a; font: 600 clamp(18px, 2.2vw, 24px) 'Playfair Display', Georgia, serif; }

        .brands-marquee {
            overflow: hidden;
            cursor: grab;
            user-select: none;
            -webkit-user-select: none;
            touch-action: pan-y;
        }

        .brands-marquee.dragging { cursor: grabbing; }

        .brands-track {
            display: flex;
            width: max-content;
            will-change: transform;
            animation: brandsScroll 42s linear infinite;   /* 🎡 pure CSS infinite loop */
        }

        @keyframes brandsScroll {
            from { transform: translateX(0); }
            to   { transform: translateX(-50%); }   /* -50% = one full set (you have 2 identical sets) */
        }

        .brands-set { display: flex; align-items: center; gap: 16px; padding-right: 16px; }

        .brand-logo {
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            font-size: 22px;
            color: #141414;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
            flex-shrink: 0;
            opacity: .85;
            transition: opacity .3s;
        }

        .brand-logo:hover { opacity: 1; }

        .brand-logo .thin { font-weight: 300; letter-spacing: 4px; }
        .brand-logo .sym { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; flex:none; font-size:18px; letter-spacing:2px; color:var(--teal); }
        .brand-logo .sym img { display:block; width:100%; height:100%; object-fit:contain; }
        .brand-logo.home-brand { box-sizing:border-box; gap:13px; width:190px; height:76px; padding:12px 16px; border:1px solid #e7ecef; border-radius:14px; background:#fff; color:#243740; font-size:15px; letter-spacing:.1px; opacity:1; box-shadow:0 5px 18px rgba(19,49,61,.05); }
        .brand-logo.home-brand .sym { width:auto; max-width:96px; height:35px; }
        .brand-logo.home-brand .sym img { width:auto; max-width:96px; height:35px; }
        .home-brand-mark { display:grid; flex:0 0 38px; place-items:center; width:38px; height:38px; border-radius:12px; background:linear-gradient(145deg,#e0f5f8,#c4e8ec); color:#176276; font:800 13px 'Montserrat',sans-serif; letter-spacing:-.4px; }

        /* ================= TESTIMONIALS ================= */
        .testi-sec { padding: 0 0 95px; background: var(--bg); }

        .testi-card {
            display: grid;
            grid-template-columns: 1.08fr .92fr;
            background: #fff;
            overflow: hidden;
            box-shadow: 0 24px 70px rgba(0, 0, 0, .08);
        }

        .testi-img { position: relative; overflow: hidden; min-height: 480px; }

        .testi-img img {
            position: absolute;
            left: 0;
            top: -16%;
            width: 100%;
            height: 132%;
            object-fit: cover;
            will-change: transform;
        }

        .testi-body { display: flex; flex-direction: column; justify-content: center; padding: 60px 64px 50px; }

        .testi-top { text-align: center; margin-bottom: 8px; }
        .testi-top .sec-title { margin: 10px 0 0; }

        .testi-stage { display: grid; flex: 1; }

        .testi-slide {
            grid-area: 1 / 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            opacity: 0;
            visibility: hidden;
            transform: translateY(26px);
            transition: opacity .65s ease, transform .65s cubic-bezier(.22, 1, .36, 1), visibility .65s;
            pointer-events: none;
        }

        .testi-slide.active { opacity: 1; visibility: visible; transform: none; pointer-events: auto; }

        .testi-stars { color: #f0b429; font-size: 15px; letter-spacing: 4px; margin-bottom: 14px; }

        .testi-quote { font-size: 17px; color: #3d4148; max-width: 480px; margin-bottom: 28px; }

        .testi-quote::before {
            content: '\201C';
            font-family: Georgia, serif;
            font-size: 68px;
            line-height: 1;
            display: block;
            color: rgba(56, 170, 180, .25);
            margin-bottom: 4px;
        }

        .testi-person { display: flex; align-items: center; gap: 14px; }

        .testi-avatar {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--teal);
            box-shadow: 0 6px 16px rgba(56, 170, 180, .25);
        }

        .testi-p-info { text-align: left; line-height: 1.4; }
        .testi-name { font-family: 'Montserrat', sans-serif; font-size: 14px; font-weight: 600; color: #222; }
        .testi-role { font-size: 12.5px; color: #9aa0a4; }

        .testi-nav { display: flex; align-items: center; justify-content: center; gap: 22px; margin-top: 30px; }

        .t-arrow {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 1px solid #dfe3e5;
            background: #fff;
            color: #333;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all .3s;
        }

        .t-arrow:hover {
            background: var(--teal);
            border-color: var(--teal);
            color: #fff;
            transform: scale(1.08);
        }

        .t-dots { display: flex; gap: 8px; }

        .t-dot {
            width: 9px;
            height: 9px;
            border-radius: 20px;
            background: #d8dcde;
            border: none;
            cursor: pointer;
            padding: 0;
            transition: all .35s;
        }

        .t-dot:hover { background: #b6bdc1; }
        .t-dot.active { width: 26px; background: var(--teal); }

        /* ================= NEWSLETTER ================= */
        .newsletter-sec { background: #edeff0; padding: 80px 0; }

        .newsletter-wrap { display: grid; grid-template-columns: 1fr 1fr; gap: 50px; align-items: center; }

        .newsletter-wrap h2 { font-size: clamp(24px, 3.4vw, 32px); font-weight: 400; text-transform: uppercase; }

        .news-form input {
            width: 100%;
            max-width: 500px;
            padding: 15px 18px;
            border: 1px solid #ccd1d3;
            background: #fff;
            font-family: inherit;
            font-size: 14px;
            outline: none;
            transition: border-color .3s;
        }

        .news-form input:focus { border-color: var(--teal); }
        .news-form button { margin-top: 18px; }

        /* ================= FOOTER ================= */
        .site-footer { background: #000; color: #c9c9c9; }

        .footer-main {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 40px;
            align-items: start;
            padding: 75px 0 55px;
        }

        .site-footer h4 {
            color: #fff;
            font-size: 15px;
            font-weight: 500;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 24px;
        }

        .contact-list li { display: flex; gap: 12px; align-items: flex-start; margin-bottom: 14px; font-size: 14px; }
        .contact-list svg { flex-shrink: 0; margin-top: 4px; }

        .footer-logo { font-family: 'Allura', cursive; font-size: 54px; color: #fff; align-self: center; }
        .footer-brand { display:grid; justify-items:center; align-content:center; gap:7px; align-self:center; text-align:center; }
        .footer-tagline { max-width:250px; color:#aaa; font-size:12px; line-height:1.6; }

        .quick-links { text-align: right; }
        .quick-links li { margin-bottom: 12px; }
        .quick-links a:hover { color: #fff; }

        .footer-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
            padding: 22px 0;
            font-size: 14px;
        }

        .socials { display: flex; gap: 12px; }

        .socials a {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, .4);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            transition: all .3s;
        }

        .socials a:hover {
            background: var(--teal);
            border-color: var(--teal);
            transform: translateY(-3px);
        }

        /* ================= FLOATING CART (footer) — UNTOUCHED ================= */
        .floating-cart {
            position: fixed;
            right: 26px;
            bottom: 26px;
            z-index: 900;
            padding: 0;
            border: 0;
            width: 58px;
            height: 58px;
            border-radius: 50%;
            background: var(--teal);
            color: #fff;
            font: inherit;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 12px 30px rgba(56, 170, 180, .5);
            cursor: pointer;
            transition: transform .3s;
        }

        .floating-cart:hover { transform: translateY(-4px); }

        .floating-cart .cart-badge { top: -4px; left: -4px; right: auto; }

        /* ================= RESPONSIVE ================= */
        @media (max-width: 1150px) {
            .main-nav { gap: 18px; }
            .main-nav a { font-size: 11.5px; letter-spacing: 1px; }
        }

        @media (max-width: 991px) {
            .header-inner { padding: 12px 16px; gap: 14px; margin-top: 14px; }
            .logo { font-size: 30px; }
            .nav-toggle { display: flex; }
            .header-actions { gap: 9px; }

            .main-nav {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: rgba(9, 42, 46, .97);
                flex-direction: column;
                align-items: center;
                padding: 18px 0 24px;
                gap: 16px;
                box-shadow: 0 24px 40px rgba(0, 0, 0, .25);
            }

            .main-nav.open { display: flex; }

            .m-only {
                display: inline-block;
                padding: 4px 0;
                border: none;
                border-radius: 0;
                color: rgba(255, 255, 255, .85);
            }

            .m-only:hover {
                background: none;
                color: #a9e8ee;
            }

            .product-grid { grid-template-columns: repeat(2, 1fr); }

            .promo-card, .testi-card, .newsletter-wrap { grid-template-columns: 1fr; }

            .promo-img, .testi-img { min-height: 320px; order: -1; }
            .testi-body { padding: 46px 26px 42px; }
            .promo-body { padding: 55px 26px; }
            .feature-grid { grid-template-columns: 1fr; gap: 50px; }

            .footer-main { grid-template-columns: 1fr; }
            .quick-links { text-align: left; }
            .footer-logo { order: -1; }
        }

        @media (max-width: 575px) {
            .product-grid { grid-template-columns: 1fr; }
            .hero { min-height: 76vh; }
            .brands-heading { width: calc(100% - 32px); }
            .brands-set { gap: 12px; padding-right: 12px; }
            .brand-logo.home-brand { width:168px; height:68px; padding:10px 12px; font-size:13px; }
            .home-brand-mark { flex-basis:34px; width:34px; height:34px; font-size:12px; }
            .header-inner { gap: 10px; padding: 11px 12px; }
            .logo { font-size: 26px; }

            .ic-circle { width: 37px; height: 37px; min-width: 37px; }
            .u-av { width: 26px; height: 26px; font-size: 10px; }

            .user-panel {
                position: fixed;
                top: 60px;
                right: 10px;
                left: auto;
                width: min(280px, calc(100vw - 20px));
            }
        }
    </style>
</head>

<body>
    @php
        $feUser = auth()->user();
        $feInitials = $feUser
            ? collect(explode(' ', trim($feUser->name)))->filter()->map(fn ($w) => strtoupper(substr($w, 0, 1)))->take(2)->implode('')
            : '';
        $feDashboard = $feUser && $feUser->isAdmin() ? route('admin.dashboard') : route('user.dashboard');
        /* 👤 Profile role-wise: admin → NiceDash profile · user → User Area profile */
        $feProfile = ! $feUser ? null : ($feUser->isAdmin() ? route('admin.profile') : route('user.profile'));
        $backTo = urlencode(request()->getRequestUri());
    @endphp

    <!-- ================= HEADER — SINGLE ROW (cart removed — floating footer bag is THE bag) ================= -->
    <header class="site-header" id="siteHeader">
        <div class="header-inner">

            <button class="nav-toggle" id="navToggle" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>

            <a href="/" class="logo">Cosmetics</a>

            <nav class="main-nav" id="mainNav">
                <a href="/" class="{{ request()->is('/') ? 'active' : '' }}">Home</a>
                <a href="{{ route('shop') }}" class="{{ request()->routeIs('shop') || request()->routeIs('product.show') ? 'active' : '' }}">Shop</a>
                <a href="{{ route('blog.index') }}" class="{{ request()->routeIs('blog.*') ? 'active' : '' }}">Beauty Journal</a>
                <a href="{{ route('faqs.index') }}" class="{{ request()->routeIs('faqs.*') ? 'active' : '' }}">FAQs</a>
                <a href="{{ route('testimonials') }}">Testimonials</a>
                <a href="{{ route('about') }}">About</a>
                <a href="{{ route('contact') }}">Contact</a>

                @guest
                <a href="{{ route('login.redirect') }}?redirect={{ $backTo }}" class="m-only">Login</a>
                <a href="{{ route('register') }}" class="m-only">Create Account</a>
                @endguest

                @if ($feUser)
                <a href="{{ $feDashboard }}" class="m-only">My Dashboard</a>
                @if ($feProfile)
                <a href="{{ $feProfile }}" class="m-only">My Profile</a>
                @endif
                @endif
            </nav>

            <div class="header-actions">

                @guest
                <!-- 👤 Guest → saves current page → login -->
                <a href="{{ route('login.redirect') }}?redirect={{ $backTo }}" class="user-btn ic-circle" aria-label="Login or Register" title="Login / Register">
                    <svg class="u-ic" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="8" r="4" />
                        <path d="M4 21c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5" />
                    </svg>
                </a>
                @endguest

                @if ($feUser)
                <!-- 👤 Logged in → dropdown -->
                <div class="user-dd" id="userDD">
                    <button type="button" class="user-btn ic-circle" onclick="toggleUserMenu(event)" aria-label="Account menu" title="{{ $feUser->name }}">
                        <span class="u-av">{{ $feInitials }}</span>
                    </button>

                    <div class="user-panel">
                        <div class="up-head">
                            <b>{{ $feUser->name }}</b>
                            <span>{{ $feUser->email }}</span>
                        </div>
                        <div class="up-menu">
                            <a class="up-item" href="{{ $feDashboard }}">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
                                My Dashboard
                            </a>
                            @if ($feProfile)
                            <a class="up-item" href="{{ $feProfile }}">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5"/></svg>
                                My Profile
                            </a>
                            @endif
                            <div class="up-sep"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="up-item out">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>
                                    Log Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </header>

    <script>
        /* ---------- mobile nav ---------- */
        var navToggle = document.getElementById('navToggle');
        var mainNav = document.getElementById('mainNav');
        if (navToggle && mainNav) {
            navToggle.addEventListener('click', function () {
                navToggle.classList.toggle('open');
                mainNav.classList.toggle('open');
            });
            mainNav.querySelectorAll('a').forEach(function (link) {
                link.addEventListener('click', function () {
                    navToggle.classList.remove('open');
                    mainNav.classList.remove('open');
                });
            });
        }

        /* ---------- account dropdown ---------- */
        function toggleUserMenu(e) {
            e.preventDefault();
            e.stopPropagation();
            var dd = document.getElementById('userDD');
            if (dd) dd.classList.toggle('open');
        }

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.user-dd')) {
                var dd = document.getElementById('userDD');
                if (dd) dd.classList.remove('open');
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                var dd = document.getElementById('userDD');
                if (dd) dd.classList.remove('open');
            }
        });

        /* ---------- light-header auto-detect ---------- */
        (function () {
            function checkHeader() {
                var h = document.getElementById('siteHeader');
                if (!h) return;
                var bg = getComputedStyle(h).backgroundColor || '';
                var solid = bg && bg !== 'transparent' && bg.indexOf('rgba(0, 0, 0, 0)') !== 0;
                h.classList.toggle('on-light', solid);
            }
            if (document.readyState !== 'loading') checkHeader();
            else document.addEventListener('DOMContentLoaded', checkHeader);
        })();
    </script>

    {{-- 🛒 CART DRAWER — included here so the bag works on EVERY page --}}
    @include('front-end.include.cart-drawer')