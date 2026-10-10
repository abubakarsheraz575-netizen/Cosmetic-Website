{{-- ==========================================================
     ADMIN · ABOUT PAGE SETTINGS — Ocean/Aqua (self-contained)
     Story text + Services points + Featured Product + Image
========================================================== --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0e5a75">
    <title>About Page · NiceDash</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}?v=3">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --bg: #f3f5f7; --card: #fff; --text: #101828; --muted: #68788f;
            --border: #e8edf3; --forest: #0e5a75; --forest-2: #17739b;
            --lime: #3ec5e0; --lime-soft: #e4f6fb; --radius: 16px;
            --shadow: 0 1px 2px rgba(16,24,40,.04), 0 8px 24px rgba(16,24,40,.05);
        }
        body.dark {
            --bg: #0b1220; --card: #111c2e; --text: #e7edf5; --muted: #8ba0b8;
            --border: #1e2c42; --lime-soft: rgba(62,197,224,.08);
            --shadow: 0 1px 2px rgba(0,0,0,.3), 0 10px 30px rgba(0,0,0,.35);
        }
        * { margin: 0; padding: 0; box-sizing: border-box }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--text); -webkit-tap-highlight-color: transparent }

        /* ⭐⭐⭐ BASE SHELL — sidebar fixed left (PAGE PROVIDES THIS — include nahi karta) ⭐⭐⭐ */
        .layout { min-height: 100vh }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 268px;
            z-index: 50;
            overflow-y: auto;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
        }
        .main { margin-left: 268px; min-height: 100vh; transition: margin-left .3s ease }
        .layout.collapsed .main { margin-left: 84px }
        @media (max-width: 992px) { .main { margin-left: 0 } }

        .content { width:100%; padding:26px; max-width:1480px; margin:0 auto }

        .reveal { opacity: 0; transform: translateY(16px); transition: opacity .5s ease, transform .5s cubic-bezier(.22,1,.36,1) }
        .reveal.show { opacity: 1; transform: none }

        .pg-head { background: linear-gradient(120deg, #0d4257, #17739b 55%, #3ec5e0); border-radius: 20px; padding: 24px 26px; color: #fff; display: flex; align-items: center; gap: 16px; margin-bottom: 22px; position: relative; overflow: hidden; box-shadow: 0 14px 34px rgba(14,90,117,.28) }
        .pg-head::after { content: 'About'; position: absolute; right: 26px; top: 50%; transform: translateY(-50%); font-family: Georgia, serif; font-style: italic; font-size: 58px; opacity: .09; pointer-events: none }
        .pg-ic { width: 52px; height: 52px; min-width: 52px; border-radius: 14px; background: rgba(255,255,255,.16); display: grid; place-items: center; font-size: 25px }
        .pg-head h1 { font-size: 19px; font-weight: 800 }
        .pg-head p { font-size: 12.5px; opacity: .85; margin-top: 3px }

        .grid2 { display: grid; grid-template-columns: minmax(0,1.3fr) minmax(0,1fr); gap: 24px; align-items: start }
        @media (max-width: 992px) { .grid2 { grid-template-columns: 1fr } }

        .card { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow); padding: 24px }
        .c-head { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid var(--border) }
        .c-head .ci { width: 44px; height: 44px; border-radius: 12px; background: var(--lime-soft); color: var(--forest); display: grid; place-items: center; font-size: 20px }
        body.dark .c-head .ci { color: var(--lime) }
        .c-head h3 { font-size: 15.5px; font-weight: 700 }
        .c-head p { font-size: 12px; color: var(--muted); margin-top: 2px }

        .fld { min-width:0; margin-bottom:0 }
        .card > .fld + .fld { margin-top:18px }
        .fld label { display: flex; align-items: center; gap: 8px; font-size: 11.5px; font-weight: 700; letter-spacing: .6px; text-transform: uppercase; color: var(--muted); margin-bottom: 8px }
        .fld label .lb-ic { width: 24px; height: 24px; border-radius: 7px; background: var(--lime-soft); color: var(--forest); display: grid; place-items: center; font-size: 13px }
        body.dark .fld label .lb-ic { color: var(--lime) }
        .fld input[type=text], .fld select, .fld textarea { width: 100%; min-height: 52px; border: 1.5px solid var(--border); border-radius: 12px; background: var(--card); color: var(--text); padding: 12px 14px; font: 500 14px 'Plus Jakarta Sans', sans-serif; outline: none; transition: border .2s, box-shadow .2s }
        .fld textarea { min-height: 120px; resize: vertical; line-height: 1.7 }
        .fld input:focus, .fld select:focus, .fld textarea:focus { border-color: var(--lime); box-shadow: 0 0 0 4px rgba(62,197,224,.15) }
        .hint { font-size: 11.5px; color: var(--muted); margin-top: 6px; line-height: 1.6 }

        .fld input[type=file] { width: 100%; border: 1.5px dashed var(--border); border-radius: 12px; background: var(--card); padding: 12px; color: var(--muted); font-size: 13px; cursor: pointer }
        .fld input[type=file]::file-selector-button { border: 0; background: var(--lime-soft); color: var(--forest); padding: 9px 16px; border-radius: 9px; font: 700 12px 'Plus Jakarta Sans', sans-serif; cursor: pointer; margin-right: 12px }
        body.dark .fld input[type=file]::file-selector-button { color: var(--lime) }

        .prod-chip { display: flex; align-items: center; gap: 10px; margin-top: 10px; padding: 10px 14px; border-radius: 12px; background: var(--lime-soft); border: 1px solid rgba(62,197,224,.25); font-size: 13px; font-weight: 600 }
        .prod-chip .pc-emoji { font-size: 22px }
        .prod-chip .pc-category { width:24px;height:24px;flex:none;display:grid;place-items:center;border-radius:7px;background:#fff;color:var(--forest);font-size:15px }
        .prod-chip .pc-category img { width:100%;height:100%;object-fit:contain;padding:3px }
        .prod-chip .pc-product-image { width:76px;height:60px;flex:none;object-fit:cover;border-radius:8px;background:var(--bg) }
        .prod-chip .pc-detail { min-width:0;display:grid;gap:3px }
        .prod-chip .pc-detail span { color:var(--muted);font-size:11px }
        .prod-chip[hidden] { display: none }

        .img-prev { margin-top: 10px; width: 100%; max-width:260px; height: 155px; border-radius: 12px; border: 1.5px dashed var(--border); overflow: hidden; display: grid; place-items: center; background: var(--bg) }
        .img-prev img { width: 100%; height: 100%; object-fit: cover }
        .img-prev .no-img { font-size: 11px; color: var(--muted); text-align: center; padding: 8px }

        .save-row { display: flex; align-items: center; gap: 14px; margin-top: 20px; flex-wrap: wrap }
        .save { display: inline-flex; align-items: center; gap: 9px; border: 0; cursor: pointer; min-height: 52px; padding: 0 32px; border-radius: 12px; background: linear-gradient(120deg, var(--forest), var(--forest-2)); color: #fff; font: 700 14px 'Plus Jakarta Sans', sans-serif; box-shadow: 0 10px 24px rgba(14,90,117,.28); transition: .25s }
        .save:hover { transform: translateY(-2px); box-shadow: 0 14px 30px rgba(14,90,117,.36) }
        .save:disabled { opacity: .7; cursor: wait; transform: none }
        .peek { display: inline-flex; align-items: center; gap: 7px; min-height: 52px; padding: 0 20px; border-radius: 12px; border: 1.5px solid var(--border); color: var(--text); text-decoration: none; font-weight: 600; font-size: 13px; background: var(--card); transition: .25s }
        .peek:hover { border-color: var(--lime); color: var(--forest) }
        body.dark .peek:hover { color: var(--lime) }

        @media (max-width: 680px) {
            .content { padding: 16px }
            .pg-head { padding: 18px; border-radius: 16px }
            .pg-head::after { display: none }
            .card { padding: 18px }
            .fld input[type=text], .fld select, .fld textarea { font-size: 16px }
            .save-row .save, .save-row .peek { width: 100%; justify-content: center }
        }
    </style>
</head>
<body>
<div class="layout" id="layout">
    @include('admin.include.sidebar')
    <main class="main">
        @include('admin.include.navbar')
        <section class="content">

            <div class="pg-head reveal">
                <div class="pg-ic"><i class="ri-article-line"></i></div>
                <div>
                    <h1>About Page Content</h1>
                    <p>Edit the About story, product collection, statistics, quote, highlights and visible sections.</p>
                </div>
            </div>

            <form onsubmit="event.preventDefault(); saveAbout();">
                <div class="grid2">

                    <!-- STORY -->
                    <div class="card reveal">
                        <div class="c-head">
                            <div class="ci"><i class="ri-file-text-line"></i></div>
                            <div><h3>Story Section</h3><p>About page ka left side text</p></div>
                        </div>

                        <div class="fld">
                            <label><span class="lb-ic"><i class="ri-price-tag-3-line"></i></span> Small Tag</label>
                            <input type="text" id="fTag" value="{{ $aboutSettings['about_tag'] }}" placeholder="We Help You Look Good">
                        </div>

                        <div class="fld">
                            <label><span class="lb-ic"><i class="ri-heading"></i></span> Story Title</label>
                            <input type="text" id="fTitle" value="{{ $aboutSettings['about_title'] }}" placeholder="Let's Talk About Beauty">
                        </div>

                        <div class="fld">
                            <label><span class="lb-ic"><i class="ri-text"></i></span> Story Paragraphs</label>
                            <textarea id="fText" rows="7" placeholder="Story likhein...">{{ $aboutSettings['about_text'] }}</textarea>
                            <p class="hint">💡 Har line ko alag paragraph samjha jayega. Nayi line ke liye Enter dabayein; paragraph ke darmiyan khali line bhi rakh sakte hain.</p>
                        </div>

                        <div class="fld" style="margin-bottom:0">
                            <label><span class="lb-ic"><i class="ri-list-check-2"></i></span> Services / Points</label>
                            <input type="text" id="aboutServicesLabel" value="{{ $aboutSettings['about_services_label'] }}" style="margin-bottom:10px" aria-label="Services heading">
                            <textarea id="fServ" rows="8" placeholder="Anti-Aging Skin Toner&#10;Night Creams&#10;Hair Care">{{ $aboutSettings['about_services'] }}</textarea>
                            <p class="hint">💡 <b>Ek line = ek point</b> — jitne points likhenge utne hi show honge</p>
                        </div>
                    </div>

                    <!-- SHOWCASE -->
                    <div class="card reveal" style="transition-delay:.08s">
                        <div class="c-head">
                            <div class="ci"><i class="ri-shopping-bag-3-line"></i></div>
                            <div><h3>Product Showcase</h3><p>Right side — product ki asli pic + data</p></div>
                        </div>

                        <div class="fld">
                            <label><span class="lb-ic"><i class="ri-star-line"></i></span> Featured Product</label>
                            <select id="fProd" onchange="prodPreview(this)">
                                <option value="">— No product (custom image / default) —</option>
                                @foreach ($products as $p)
                                @php $categoryDisplay = $productCategoryDisplay[$p->id]; @endphp
                                <option value="{{ $p->id }}" data-emoji="{{ $p->emoji }}" data-category-name="{{ $categoryDisplay['name'] }}" data-category-icon="{{ $categoryDisplay['icon'] }}" data-category-class="{{ $categoryDisplay['icon_class'] }}" data-category-font-icon="{{ $categoryDisplay['font_icon'] ? '1' : '0' }}" data-category-logo="{{ $categoryDisplay['logo'] }}" data-product-image="{{ $categoryDisplay['image'] }}" {{ $vProdId == $p->id ? 'selected' : '' }}>
                                    {{ $categoryDisplay['symbol'] }} {{ $categoryDisplay['name'] }} · {{ $p->name }} · GL-{{ str_pad($p->id, 4, '0', STR_PAD_LEFT) }}
                                </option>
                                @endforeach
                            </select>
                            <div class="prod-chip" id="prodChip" @unless($vProdId) hidden @endunless>
                                @if($vProdId && $vProd = $products->firstWhere('id', (int) $vProdId))
                                @php $categoryDisplay = $productCategoryDisplay[$vProd->id]; @endphp
                                @if($categoryDisplay['image'])
                                <img class="pc-product-image" src="{{ $categoryDisplay['image'] }}" alt="" loading="lazy">
                                @endif
                                <span class="pc-category">
                                    @if($categoryDisplay['logo'])
                                    <img src="{{ $categoryDisplay['logo'] }}" alt="">
                                    @elseif($categoryDisplay['font_icon'])
                                    <i class="{{ $categoryDisplay['icon_class'] }}" aria-hidden="true"></i>
                                    @else
                                    {{ $categoryDisplay['icon'] }}
                                    @endif
                                </span>
                                <span class="pc-detail"><strong>{{ $vProd->name }}</strong><span>{{ $categoryDisplay['name'] }} · £{{ number_format($vProd->price, 2) }}</span></span>
                                @endif
                            </div>
                            <p class="hint">💡 Product select karte hi About page pe uski <b>asli photo + name + SKU + price + View Product button</b> show hoga — bilkul GL-0014 jaise product ka data.</p>
                        </div>

                        <div class="fld" style="margin-bottom:0">
                            <label><span class="lb-ic"><i class="ri-image-line"></i></span> Custom Image (optional)</label>
                            <input type="file" id="fImg" accept="image/png,image/jpeg,image/webp" onchange="imgPreview(this)">
                            <div class="img-prev" id="imgPrev"><img src="{{ $aboutImages['about_image'] }}" alt="About showcase image"></div>
                            <p class="hint">💡 Ye image <b>sirf tab</b> use hogi jab koi product select NA kiya ho. Nayi upload purani ko replace kar degi.</p>
                        </div>
                    </div>
                </div>

                <div class="card reveal" style="margin-top:20px">
                    <div class="c-head">
                        <div class="ci"><i class="ri-layout-masonry-line"></i></div>
                        <div><h3>Products section</h3><p>Control the product showcase heading, description and empty state.</p></div>
                    </div>
                    <div class="grid2">
                        <div class="fld"><label>Eyebrow</label><input type="text" id="aboutProductsTagline" value="{{ $aboutSettings['about_products_tagline'] }}"></div>
                        <div class="fld"><label>Section title</label><input type="text" id="aboutProductsTitle" value="{{ $aboutSettings['about_products_title'] }}"></div>
                        <div class="fld"><label>Section description</label><textarea id="aboutProductsText">{{ $aboutSettings['about_products_text'] }}</textarea></div>
                        <div class="fld"><label>View all button</label><input type="text" id="aboutProductsButton" value="{{ $aboutSettings['about_products_button'] }}"></div>
                        <div class="fld"><label>Product card button</label><input type="text" id="aboutProductCta" value="{{ $aboutSettings['about_product_cta'] }}"></div>
                        <div class="fld"><label>Empty state title</label><input type="text" id="aboutProductsEmptyTitle" value="{{ $aboutSettings['about_products_empty_title'] }}"></div>
                        <div class="fld"><label>Empty state description</label><input type="text" id="aboutProductsEmptyText" value="{{ $aboutSettings['about_products_empty_text'] }}"></div>
                    </div>
                </div>

                <div class="grid2" style="margin-top:20px">
                    <div class="card reveal">
                        <div class="c-head">
                            <div class="ci"><i class="ri-bar-chart-grouped-line"></i></div>
                            <div><h3>About statistics</h3><p>Numbers are shared with Store Settings; labels and suffixes are managed here.</p></div>
                        </div>
                        @foreach(['years' => 'Years of expertise', 'clients' => 'Happy clients', 'products' => 'Beauty products', 'satisfaction' => 'Satisfaction rate'] as $stat => $label)
                        <div class="frow2">
                            <div class="fld"><label>{{ $label }} value</label><input type="text" inputmode="numeric" id="stat{{ ucfirst($stat) }}" value="{{ $aboutSettings['stat_'.$stat] }}"></div>
                            <div class="fld"><label>{{ $label }} suffix</label><input type="text" id="stat{{ ucfirst($stat) }}Suffix" value="{{ $aboutSettings['about_stat_'.$stat.'_suffix'] }}"></div>
                            <div class="fld"><label>{{ $label }} display label</label><input type="text" id="stat{{ ucfirst($stat) }}Label" value="{{ $aboutSettings['about_stat_'.$stat.'_label'] }}"></div>
                        </div>
                        @endforeach
                    </div>

                    <div class="card reveal" style="transition-delay:.08s">
                        <div class="c-head">
                            <div class="ci"><i class="ri-double-quotes-l"></i></div>
                            <div><h3>Quote banner</h3><p>Customize the quote text, attribution and background image.</p></div>
                        </div>
                        <div class="fld"><label>Quote text</label><textarea id="aboutQuote" rows="4">{{ $aboutSettings['about_quote'] }}</textarea></div>
                        <div class="fld"><label>Attribution</label><input type="text" id="aboutQuoteAuthor" value="{{ $aboutSettings['about_quote_author'] }}"></div>
                        <div class="fld">
                            <label>Background image</label>
                            <input type="file" id="aboutQuoteImage" accept="image/png,image/jpeg,image/webp" onchange="imgPreview(this, 'quoteImgPrev')">
                            <div class="img-prev" id="quoteImgPrev"><img src="{{ $aboutImages['about_quote_image'] }}" alt="About quote banner image"></div>
                        </div>
                    </div>
                </div>

                <div class="card reveal" style="margin-top:20px">
                    <div class="c-head">
                        <div class="ci"><i class="ri-sparkling-2-line"></i></div>
                        <div><h3>Store highlights</h3><p>Edit the three feature cards displayed on the About page.</p></div>
                    </div>
                    <div class="grid2">
                        @for($feature = 1; $feature <= 3; $feature++)
                        <div class="fld"><label>Highlight {{ $feature }} title</label><input type="text" id="aboutFeature{{ $feature }}Title" value="{{ $aboutSettings['about_feature_'.$feature.'_title'] }}"></div>
                        <div class="fld"><label>Highlight {{ $feature }} description</label><textarea id="aboutFeature{{ $feature }}Text">{{ $aboutSettings['about_feature_'.$feature.'_text'] }}</textarea></div>
                        @endfor
                    </div>
                </div>

                <div class="card reveal" style="margin-top:20px">
                    <div class="c-head">
                        <div class="ci"><i class="ri-eye-line"></i></div>
                        <div><h3>Visible About page sections</h3><p>Hide or show sections without deleting their saved content.</p></div>
                    </div>
                    <div class="grid2">
                        @foreach(['story' => 'Story and showcase', 'products' => 'Product collection', 'stats' => 'Statistics', 'quote' => 'Quote banner', 'features' => 'Store highlights', 'categories' => 'Beauty brand slider'] as $section => $label)
                        <label class="fld"><input type="checkbox" id="aboutShow{{ ucfirst($section) }}" @checked((bool) $aboutSettings['about_show_'.$section])> {{ $label }}</label>
                        @endforeach
                    </div>
                </div>

                <div class="save-row reveal">
                    <button type="submit" class="save" id="saveBtn"><i class="ri-save-3-line"></i> Save Changes</button>
                    <a href="{{ url('/about') }}" target="_blank" class="peek"><i class="ri-external-link-line"></i> View About Page</a>
                </div>
            </form>
        </section>
    </main>
</div>

<script>
    var ABOUT_SAVE = "{{ route('admin.about-settings.update') }}";
    var CSRF = document.querySelector('meta[name="csrf-token"]').content;
    var Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2600, timerProgressBar: true, background: '#111c2e', color: '#e7edf5' });

    function saveAbout() {
        var fd = new FormData();
        [
            'about_tag', 'about_title', 'about_text', 'about_services', 'about_services_label',
            'about_products_tagline', 'about_products_title', 'about_products_text',
            'about_products_button', 'about_product_cta', 'about_products_empty_title', 'about_products_empty_text',
            'about_quote', 'about_quote_author',
            'about_stat_years_suffix', 'about_stat_years_label',
            'about_stat_clients_suffix', 'about_stat_clients_label',
            'about_stat_products_suffix', 'about_stat_products_label',
            'about_stat_satisfaction_suffix', 'about_stat_satisfaction_label',
            'about_feature_1_title', 'about_feature_1_text',
            'about_feature_2_title', 'about_feature_2_text',
            'about_feature_3_title', 'about_feature_3_text'
        ].forEach(function (key) {
            var id = key.replace(/_([a-z])/g, function (_, letter) { return letter.toUpperCase(); });
            var alias = {
                about_tag: 'fTag',
                about_title: 'fTitle',
                about_text: 'fText',
                about_services: 'fServ',
                about_services_label: 'aboutServicesLabel',
                about_products_tagline: 'aboutProductsTagline',
                about_products_title: 'aboutProductsTitle',
                about_products_text: 'aboutProductsText',
                about_products_button: 'aboutProductsButton',
                about_product_cta: 'aboutProductCta',
                about_products_empty_title: 'aboutProductsEmptyTitle',
                about_products_empty_text: 'aboutProductsEmptyText',
                about_quote: 'aboutQuote',
                about_quote_author: 'aboutQuoteAuthor',
                about_stat_years_suffix: 'statYearsSuffix',
                about_stat_years_label: 'statYearsLabel',
                about_stat_clients_suffix: 'statClientsSuffix',
                about_stat_clients_label: 'statClientsLabel',
                about_stat_products_suffix: 'statProductsSuffix',
                about_stat_products_label: 'statProductsLabel',
                about_stat_satisfaction_suffix: 'statSatisfactionSuffix',
                about_stat_satisfaction_label: 'statSatisfactionLabel',
                about_feature_1_title: 'aboutFeature1Title',
                about_feature_1_text: 'aboutFeature1Text',
                about_feature_2_title: 'aboutFeature2Title',
                about_feature_2_text: 'aboutFeature2Text',
                about_feature_3_title: 'aboutFeature3Title',
                about_feature_3_text: 'aboutFeature3Text'
            };
            fd.append(key, document.getElementById(alias[key] || id).value);
        });
        ['years', 'clients', 'products', 'satisfaction'].forEach(function (stat) {
            var key = stat === 'years' ? 'stat_years' : 'stat_' + stat;
            fd.append(key, document.getElementById('stat' + stat.charAt(0).toUpperCase() + stat.slice(1)).value);
        });
        ['story', 'products', 'stats', 'quote', 'features', 'categories'].forEach(function (section) {
            fd.append('about_show_' + section, document.getElementById('aboutShow' + section.charAt(0).toUpperCase() + section.slice(1)).checked ? '1' : '0');
        });
        fd.append('about_product_id', document.getElementById('fProd').value || '');
        var img = document.getElementById('fImg');
        if (img.files[0]) fd.append('about_image', img.files[0]);
        var quoteImg = document.getElementById('aboutQuoteImage');
        if (quoteImg.files[0]) fd.append('about_quote_image', quoteImg.files[0]);
        fd.append('_token', CSRF);

        var btn = document.getElementById('saveBtn');
        btn.disabled = true; btn.innerHTML = '<i class="ri-loader-4-line"></i> Saving…';

        fetch(ABOUT_SAVE, { method: 'POST', body: fd, headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function (r) {
            return r.json().catch(function () { return {}; }).then(function (d) {
                if (r.status === 419) throw new Error('Session expired — please refresh the page.');
                if (!r.ok) {
                    var errors = d.errors || {};
                    var fields = Object.keys(errors);
                    throw new Error(fields.length ? errors[fields[0]][0] : (d.message || 'Could not save About page settings.'));
                }
                return d;
            });
        })
        .then(function (d) {
            if (d.status !== 'success') throw new Error(d.message || 'Could not save About page settings.');
            Toast.fire({ icon: 'success', title: d.message || 'About page saved.' });
            if (d.status === 'success' && d.image) {
                document.getElementById('imgPrev').innerHTML = '<img src="' + d.image + '?t=' + Date.now() + '" alt="Current">';
                img.value = '';
            }
            if (d.quote_image) {
                document.querySelector('#quoteImgPrev img').src = d.quote_image + '?t=' + Date.now();
                quoteImg.value = '';
            }
        })
        .catch(function (error) {
            Toast.fire({ icon: 'error', title: error.message || 'Save failed — try again' });
        })
        .finally(function () {
            btn.disabled = false; btn.innerHTML = '<i class="ri-save-3-line"></i> Save Changes';
        });
    }

    function prodPreview(sel) {
        var opt = sel.options[sel.selectedIndex];
        var box = document.getElementById('prodChip');
        if (!opt.value) { box.hidden = true; box.innerHTML = ''; return; }
        box.hidden = false;
        box.replaceChildren();
        var productImage = opt.getAttribute('data-product-image');
        if (productImage) {
            var image = document.createElement('img');
            image.className = 'pc-product-image';
            image.src = productImage;
            image.alt = '';
            box.appendChild(image);
        }
        var icon = document.createElement('span');
        icon.className = 'pc-category';
        var logo = opt.getAttribute('data-category-logo');
        if (logo) {
            var logoImage = document.createElement('img');
            logoImage.src = logo;
            logoImage.alt = '';
            icon.appendChild(logoImage);
        } else if (opt.getAttribute('data-category-font-icon') === '1') {
            var categoryIcon = document.createElement('i');
            categoryIcon.className = opt.getAttribute('data-category-class') || 'ri-price-tag-3-line';
            categoryIcon.setAttribute('aria-hidden', 'true');
            icon.appendChild(categoryIcon);
        } else {
            icon.textContent = opt.getAttribute('data-category-icon') || '🧴';
        }
        box.appendChild(icon);
        var detail = document.createElement('span');
        detail.className = 'pc-detail';
        var name = document.createElement('strong');
        name.textContent = opt.text;
        var category = document.createElement('span');
        category.textContent = (opt.getAttribute('data-category-name') || 'Uncategorized') + ' · Product #'+ opt.value;
        detail.append(name, category);
        box.appendChild(detail);
    }

    function imgPreview(inp, previewId) {
        var pv = document.getElementById(previewId || 'imgPrev');
        if (inp.files && inp.files[0]) {
            var rd = new FileReader();
            rd.onload = function (e) { pv.innerHTML = '<img src="' + e.target.result + '" alt="Preview">'; };
            rd.readAsDataURL(inp.files[0]);
        }
    }

    setTimeout(function () { document.querySelectorAll('.reveal').forEach(function (el) { el.classList.add('show'); }); }, 1200);
</script>
</body>
</html>