<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Home Page Content — Cosmetics Admin</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}?v=3">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        *{box-sizing:border-box}
        body{margin:0;background:#f3f5f7;color:#101828;font:14px 'Plus Jakarta Sans',sans-serif}
        .layout{min-height:100vh}
        .sidebar{position:fixed;top:0;left:0;bottom:0;width:268px;z-index:50;display:flex;flex-direction:column;overflow-y:auto;overflow-x:hidden}
        .main{min-width:0;min-height:100vh;margin-left:268px;transition:margin-left .3s ease}
        .layout.collapsed .main{margin-left:84px}
        .content{width:100%;max-width:none;padding:26px}
        .home-settings{width:min(100%,1480px);margin:0 auto;display:grid;gap:20px}
        .hs-head{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}
        .hs-head h1{margin:0;font-size:24px;font-weight:800;letter-spacing:-.4px}
        .hs-head p{margin:5px 0 0;color:#68788f;font-size:13px}
        .hs-preview{display:inline-flex;align-items:center;gap:8px;min-height:42px;padding:0 14px;border:1px solid #dce6eb;border-radius:11px;background:#fff;color:#155e75;text-decoration:none;font-weight:700}
        .hs-preview:hover{border-color:#3ec5e0;background:#f0fbfd}
        .hs-card{overflow:hidden;border:1px solid #e5ebf0;border-radius:17px;background:#fff;box-shadow:0 8px 24px rgba(16,24,40,.05)}
        .hs-card-head{display:flex;align-items:center;gap:12px;padding:18px 21px;border-bottom:1px solid #edf1f4}
        .hs-icon{width:42px;height:42px;flex:none;display:grid;place-items:center;border-radius:12px;background:#e4f6fb;color:#0e7188;font-size:20px}
        .hs-card-head h2{margin:0;font-size:15px;font-weight:800}
        .hs-card-head p{margin:4px 0 0;color:#758394;font-size:11.5px}
        .hs-fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px 20px;padding:21px}
        .hs-field{min-width:0}
        .hs-field.full{grid-column:1/-1}
        .hs-field label{display:block;margin-bottom:7px;color:#4c5c6d;font-size:11px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
        .hs-field input:not([type=file]),.hs-field textarea{width:100%;min-height:43px;padding:11px 13px;border:1px solid #dfe7ec;border-radius:11px;background:#fbfcfd;color:#172b3a;font:500 13px 'Plus Jakarta Sans',sans-serif;outline:0}
        .hs-field textarea{min-height:82px;resize:vertical;line-height:1.6}
        .hs-field input:focus,.hs-field textarea:focus{border-color:#3ec5e0;box-shadow:0 0 0 3px rgba(62,197,224,.13)}
        .hs-image{display:flex;align-items:center;gap:13px}
        .hs-image img{width:132px;height:82px;flex:none;object-fit:cover;border:1px solid #e1e8ed;border-radius:10px;background:#f3f6f8}
        .hs-image input{width:100%;min-width:0;color:#596879;font-size:12px}
        .hs-image input::file-selector-button{margin-right:10px;padding:8px 10px;border:0;border-radius:8px;background:#e4f6fb;color:#155e75;font:700 11px 'Plus Jakarta Sans',sans-serif;cursor:pointer}
        .hs-hint{margin:7px 0 0;color:#8a96a3;font-size:10.5px;line-height:1.5}
        .hs-checks{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;padding:18px 21px}
        .hs-check{display:flex;align-items:center;gap:10px;padding:12px 13px;border:1px solid #e7edf1;border-radius:11px;background:#fbfcfd;color:#35465a;font-size:12px;font-weight:700}
        .hs-check input{width:17px;height:17px;accent-color:#13829c}
        .hs-category-list{display:grid;gap:10px;padding:20px}
        .hs-category{display:grid;grid-template-columns:minmax(150px,1fr) minmax(180px,1fr) minmax(210px,1fr) auto;align-items:end;gap:16px;padding:16px;border:1px solid #e7edf1;border-radius:12px;background:#fbfcfd}
        .hs-category .hs-field{min-width:0}
        .hs-category .hs-field label{font-size:10px}
        .hs-category .hs-field input[type=file]{width:100%;min-width:0;color:#596879;font-size:11px}
        .hs-category .hs-field input[type=file]::file-selector-button{margin-right:8px;padding:7px 9px;border:0;border-radius:8px;background:#e4f6fb;color:#155e75;font:700 10px 'Plus Jakarta Sans',sans-serif;cursor:pointer}
        .hs-category-logo{width:38px;height:38px;object-fit:contain;border:1px solid #e1e8ed;border-radius:9px;background:#fff;padding:4px;vertical-align:middle}
        .hs-category-clear{display:flex;align-items:center;gap:6px;margin-top:8px;color:#68788f;font-size:10px;font-weight:600;text-transform:none}
        .hs-category-clear input{accent-color:#13829c}
        .hs-category-toggle{display:flex;align-items:center;gap:8px;min-height:43px;padding:0 11px;border:1px solid #e1e8ed;border-radius:10px;background:#fff;color:#35465a;font-size:11px;font-weight:700;white-space:nowrap}
        .hs-category-toggle input{width:17px;height:17px;accent-color:#13829c}
        .hs-category-empty{margin:0;padding:21px;color:#758394;font-size:12px}
        .hs-actions{display:flex;align-items:center;gap:12px;flex-wrap:wrap;padding:0 0 8px}
        .hs-save{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:48px;padding:0 24px;border:0;border-radius:12px;background:linear-gradient(120deg,#0e5a75,#17739b);color:#fff;font:800 13px 'Plus Jakarta Sans',sans-serif;box-shadow:0 8px 20px rgba(14,90,117,.22);cursor:pointer}
        .hs-save:disabled{opacity:.65;cursor:wait}
        .hs-note{color:#82909c;font-size:11px}
        @media(max-width:992px){.main,.layout.collapsed .main{width:100%;margin-left:0}}
        @media(max-width:1100px){.hs-category{grid-template-columns:repeat(2,minmax(0,1fr))}.hs-category-toggle{grid-column:1/-1;justify-self:start}}
        @media(max-width:680px){.content{padding:16px}.home-settings{gap:14px}.hs-head h1{font-size:21px}.hs-fields,.hs-checks{grid-template-columns:1fr;padding:16px}.hs-field.full{grid-column:auto}.hs-card-head{padding:15px 16px}.hs-image{align-items:flex-start;flex-direction:column}.hs-image img{width:100%;height:150px}.hs-category{grid-template-columns:1fr}.hs-category-toggle{grid-column:auto;justify-self:start}.hs-actions .hs-save{width:100%}}
    </style>
</head>
<body>
    <div class="layout" id="layout">
        @include('admin.include.sidebar')
        <div class="main">
            @include('admin.include.navbar')
            <main class="content">
                <form class="home-settings" id="homeSettingsForm" method="POST" action="{{ route('admin.home-settings.update') }}" enctype="multipart/form-data">
                    @csrf
                    <header class="hs-head">
                        <div>
                            <h1>Home Page Content</h1>
                            <p>Manage storefront copy, imagery, product sections and section visibility.</p>
                        </div>
                        <a class="hs-preview" href="{{ route('home') }}" target="_blank" rel="noopener"><i class="ri-external-link-line"></i> Preview storefront</a>
                    </header>

                    <section class="hs-card">
                        <div class="hs-card-head"><span class="hs-icon"><i class="ri-image-edit-line"></i></span><div><h2>Hero section</h2><p>Main storefront headline, copy, button and background.</p></div></div>
                        <div class="hs-fields">
                            <div class="hs-field"><label for="heroTagline">Eyebrow text</label><input id="heroTagline" name="home_hero_tagline" maxlength="255" value="{{ $settings['home_hero_tagline'] }}"></div>
                            <div class="hs-field"><label for="heroButton">Button text</label><input id="heroButton" name="home_hero_button" maxlength="100" value="{{ $settings['home_hero_button'] }}"></div>
                            <div class="hs-field full"><label for="heroTitle">Headline</label><textarea id="heroTitle" name="home_hero_title" maxlength="255">{{ $settings['home_hero_title'] }}</textarea><p class="hs-hint">Use a new line to place the next word on a separate headline line.</p></div>
                            <div class="hs-field full"><label for="heroText">Description</label><textarea id="heroText" name="home_hero_text" maxlength="2000">{{ $settings['home_hero_text'] }}</textarea></div>
                            <div class="hs-field full"><label for="heroImage">Background image</label><div class="hs-image"><img id="heroImagePreview" src="{{ $settings['home_hero_image'] }}" alt="Hero background preview"><input id="heroImage" type="file" name="home_hero_image" accept="image/jpeg,image/png,image/webp"></div><p class="hs-hint">Upload JPG, PNG or WebP, up to 5 MB. Leave empty to keep the current image.</p></div>
                        </div>
                    </section>

                    <section class="hs-card">
                        <div class="hs-card-head"><span class="hs-icon"><i class="ri-shopping-bag-3-line"></i></span><div><h2>Featured products</h2><p>Product cards are populated from active products in the catalog.</p></div></div>
                        <div class="hs-fields">
                            <div class="hs-field"><label>Section eyebrow</label><input name="home_featured_tagline" maxlength="255" value="{{ $settings['home_featured_tagline'] }}"></div>
                            <div class="hs-field"><label>Section title</label><input name="home_featured_title" maxlength="255" value="{{ $settings['home_featured_title'] }}"></div>
                            <div class="hs-field full"><label>Section description</label><textarea name="home_featured_text" maxlength="2000">{{ $settings['home_featured_text'] }}</textarea></div>
                        </div>
                    </section>

                    <section class="hs-card">
                        <div class="hs-card-head"><span class="hs-icon"><i class="ri-sparkling-2-line"></i></span><div><h2>Promotion section</h2><p>Editable promotional copy and image.</p></div></div>
                        <div class="hs-fields">
                            <div class="hs-field"><label>Badge value</label><input name="home_promo_badge" maxlength="30" value="{{ $settings['home_promo_badge'] }}"></div>
                            <div class="hs-field"><label>Badge caption</label><input name="home_promo_badge_text" maxlength="255" value="{{ $settings['home_promo_badge_text'] }}"></div>
                            <div class="hs-field"><label>Eyebrow text</label><input name="home_promo_tagline" maxlength="255" value="{{ $settings['home_promo_tagline'] }}"></div>
                            <div class="hs-field"><label>Title</label><input name="home_promo_title" maxlength="255" value="{{ $settings['home_promo_title'] }}"></div>
                            <div class="hs-field full"><label>Description</label><textarea name="home_promo_text" maxlength="2000">{{ $settings['home_promo_text'] }}</textarea></div>
                            <div class="hs-field"><label>Button text</label><input name="home_promo_button" maxlength="100" value="{{ $settings['home_promo_button'] }}"></div>
                            <div class="hs-field full"><label>Promotion image</label><div class="hs-image"><img id="promoImagePreview" src="{{ $settings['home_promo_image'] }}" alt="Promotion image preview"><input type="file" id="promoImage" name="home_promo_image" accept="image/jpeg,image/png,image/webp"></div><p class="hs-hint">Upload JPG, PNG or WebP, up to 5 MB. Leave empty to keep the current image.</p></div>
                        </div>
                    </section>

                    <section class="hs-card">
                        <div class="hs-card-head"><span class="hs-icon"><i class="ri-medal-line"></i></span><div><h2>Store highlights</h2><p>Three reassurance cards shown below the promotion.</p></div></div>
                        <div class="hs-fields">
                            @for($feature = 1; $feature <= 3; $feature++)
                            <div class="hs-field"><label>Highlight {{ $feature }} title</label><input name="home_feature_{{ $feature }}_title" maxlength="100" value="{{ $settings['home_feature_'.$feature.'_title'] }}"></div>
                            <div class="hs-field"><label>Highlight {{ $feature }} description</label><textarea name="home_feature_{{ $feature }}_text" maxlength="1000">{{ $settings['home_feature_'.$feature.'_text'] }}</textarea></div>
                            @endfor
                        </div>
                    </section>

                    <section class="hs-card">
                        <div class="hs-card-head"><span class="hs-icon"><i class="ri-shapes-line"></i></span><div><h2>Category logo slider</h2><p>Customize the icon and displayed name for each active category with approved products. Changes are also used on the About page.</p></div></div>
                        <div class="hs-category-list">
                            @forelse($homeCategories as $category)
                            <div class="hs-category">
                                <div class="hs-field">
                                    <label for="homeCategoryName{{ $category['id'] }}">Displayed name</label>
                                    <input id="homeCategoryName{{ $category['id'] }}" name="home_category_strip[{{ $category['id'] }}][name]" maxlength="100" value="{{ $category['name'] }}">
                                </div>
                                <div class="hs-field">
                                    <label for="homeCategoryIcon{{ $category['id'] }}">Emoji / icon class</label>
                                    <input id="homeCategoryIcon{{ $category['id'] }}" name="home_category_strip[{{ $category['id'] }}][icon]" maxlength="100" value="{{ $category['icon'] }}" placeholder="Emoji or ri-heart-3-line">
                                    <p class="hs-hint">Use any emoji or Remix Icon class, for example ri-heart-3-line. An uploaded logo takes priority.</p>
                                </div>
                                <div class="hs-field">
                                    <label for="homeCategoryLogo{{ $category['id'] }}">Upload photo or SVG</label>
                                    <img class="hs-category-logo" id="homeCategoryLogoPreview{{ $category['id'] }}" src="{{ $category['logo'] ?: '' }}" alt="{{ $category['name'] }} logo preview" @if(!$category['logo']) hidden @endif>
                                    <input id="homeCategoryLogo{{ $category['id'] }}" type="file" name="home_category_strip[{{ $category['id'] }}][logo]" accept="image/jpeg,image/png,image/webp,image/svg+xml">
                                    <label class="hs-category-clear"><input type="checkbox" name="home_category_strip[{{ $category['id'] }}][clear_logo]" value="1"> Remove uploaded logo</label>
                                    <p class="hs-hint">JPG, PNG, WebP or safe SVG, up to 2 MB.</p>
                                </div>
                                <label class="hs-category-toggle">
                                    <input type="checkbox" name="home_category_strip[{{ $category['id'] }}][visible]" value="1" @checked($category['visible'])>
                                    Show in slider
                                </label>
                            </div>
                            @empty
                            <p class="hs-category-empty">No active categories with approved active products are available yet.</p>
                            @endforelse
                        </div>
                    </section>

                    <section class="hs-card">
                        <div class="hs-card-head"><span class="hs-icon"><i class="ri-chat-quote-line"></i></span><div><h2>Customer reviews and latest products</h2><p>These sections use approved reviews and active catalog products.</p></div></div>
                        <div class="hs-fields">
                            <div class="hs-field"><label>Latest section eyebrow</label><input name="home_latest_tagline" maxlength="255" value="{{ $settings['home_latest_tagline'] }}"></div>
                            <div class="hs-field"><label>Latest section title</label><input name="home_latest_title" maxlength="255" value="{{ $settings['home_latest_title'] }}"></div>
                            <div class="hs-field full"><label>Latest section description</label><textarea name="home_latest_text" maxlength="2000">{{ $settings['home_latest_text'] }}</textarea></div>
                            <div class="hs-field full"><label>Testimonials image</label><div class="hs-image"><img id="testimonialImagePreview" src="{{ $settings['home_testimonial_image'] }}" alt="Testimonials image preview"><input type="file" id="testimonialImage" name="home_testimonial_image" accept="image/jpeg,image/png,image/webp"></div><p class="hs-hint">Upload JPG, PNG or WebP, up to 5 MB. Leave empty to keep the current image.</p></div>
                        </div>
                        <div class="hs-checks">
                            @foreach(['featured' => 'Show featured products', 'promo' => 'Show promotion', 'features' => 'Show store highlights', 'testimonials' => 'Show approved testimonials', 'latest' => 'Show latest products'] as $section => $label)
                            <label class="hs-check"><input type="checkbox" name="home_show_{{ $section }}" value="1" @checked((bool) $settings['home_show_'.$section])>{{ $label }}</label>
                            @endforeach
                        </div>
                    </section>

                    <footer class="hs-actions">
                        <button class="hs-save" id="homeSettingsSave" type="submit"><i class="ri-save-3-line"></i> Save Home Page</button>
                        <span class="hs-note">Changes are published to the storefront immediately.</span>
                    </footer>
                </form>
            </main>
        </div>
    </div>
    <div class="backdrop" id="backdrop"></div>
    <script>
        (function () {
            var form = document.getElementById('homeSettingsForm');
            var button = document.getElementById('homeSettingsSave');
            var token = document.querySelector('meta[name="csrf-token"]').content;
            var toast = window.Swal ? Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2800 }) : null;

            ['heroImage', 'promoImage', 'testimonialImage'].forEach(function (id) {
                var input = document.getElementById(id);
                input.addEventListener('change', function () {
                    if (!input.files || !input.files[0]) return;
                    var preview = document.getElementById(id + 'Preview');
                    preview.src = URL.createObjectURL(input.files[0]);
                });
            });
            form.querySelectorAll('.hs-category input[type="file"]').forEach(function (input) {
                input.addEventListener('change', function () {
                    if (!input.files || !input.files[0]) return;
                    var preview = document.getElementById(input.id.replace('homeCategoryLogo', 'homeCategoryLogoPreview'));
                    var clear = input.closest('.hs-field').querySelector('input[type="checkbox"]');
                    preview.src = URL.createObjectURL(input.files[0]);
                    preview.hidden = false;
                    clear.checked = false;
                });
            });

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                button.disabled = true;
                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                }).then(function (response) {
                    return response.json().catch(function () { return {}; }).then(function (data) {
                        if (!response.ok) {
                            var errors = data.errors || {};
                            var fields = Object.keys(errors);
                            throw new Error(fields.length ? errors[fields[0]][0] : (data.message || 'Could not save home page content.'));
                        }
                        return data;
                    });
                }).then(function (data) {
                    if (data.status !== 'success') throw new Error(data.message || 'Could not save home page content.');
                    Object.keys(data.images || {}).forEach(function (key) {
                        var imageId = key === 'home_hero_image' ? 'heroImagePreview' : (key === 'home_promo_image' ? 'promoImagePreview' : 'testimonialImagePreview');
                        document.getElementById(imageId).src = data.images[key] + (data.images[key].indexOf('?') === -1 ? '?' : '&') + 'v=' + Date.now();
                    });
                    Object.keys(data.category_logos || {}).forEach(function (categoryId) {
                        var preview = document.getElementById('homeCategoryLogoPreview' + categoryId);
                        if (!preview) return;
                        preview.src = data.category_logos[categoryId] || '';
                        preview.hidden = !data.category_logos[categoryId];
                    });
                    form.querySelectorAll('input[type="file"]').forEach(function (input) { input.value = ''; });
                    if (toast) toast.fire({ icon: 'success', title: data.message });
                }).catch(function (error) {
                    if (toast) toast.fire({ icon: 'error', title: error.message });
                    else window.alert(error.message);
                }).finally(function () { button.disabled = false; });
            });
        })();
    </script>
</body>
</html>
