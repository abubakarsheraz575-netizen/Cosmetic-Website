{{-- ==========================================================
     ADMIN · STORE SETTINGS — AJAX save
     Controls: Contact page (email/phone/address/hours/socials/map)
               + About page stats
========================================================== --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0e5a75">
    <title>NiceDash — Store Settings</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}?v=3">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box }
        :root { --bg:#f3f5f7; --card:#ffffff; --text:#101828; --muted:#68788f; --border:#e8edf3; --forest:#0e5a75; --forest-2:#17739b; --lime:#3ec5e0; --lime-soft:#e4f6fb; --shadow:0 1px 2px rgba(16,24,40,.04),0 8px 24px rgba(16,24,40,.05); --radius:16px }
        body.dark { --bg:#0b1220; --card:#111c2e; --text:#e7edf5; --muted:#8ba0b8; --border:#1e2c42; --lime-soft:rgba(62,197,224,.08); --shadow:0 1px 2px rgba(0,0,0,.3),0 10px 30px rgba(0,0,0,.35) }
        body { font-family:'Plus Jakarta Sans',sans-serif; background:var(--bg); color:var(--text); font-size:14px; transition:background .4s,color .4s; -webkit-text-size-adjust:100%; -webkit-tap-highlight-color:transparent; overflow-x:clip }
        ::-webkit-scrollbar { width:9px; height:9px }
        ::-webkit-scrollbar-thumb { background:#c7d2de; border-radius:10px; border:2px solid var(--bg) }
        body.dark ::-webkit-scrollbar-thumb { background:#24334d }
        button { font-family:inherit; user-select:none }

        .layout { min-height:100vh }
        .main { margin-left:268px; min-width:0; transition:margin .35s cubic-bezier(.4,0,.2,1) }
        .layout.collapsed .main { margin-left:84px }
        .sidebar { position:fixed; left:0; top:0; bottom:0; width:268px; background:var(--card); border-right:1px solid var(--border); display:flex; flex-direction:column; z-index:50; transition:width .35s cubic-bezier(.4,0,.2,1),transform .35s cubic-bezier(.4,0,.2,1) }
        .layout.collapsed .sidebar { width:84px }
        .s-nav { flex:1; overflow-y:auto; padding:12px 14px }
        .backdrop { position:fixed; inset:0; background:rgba(2,6,23,.55); z-index:45; opacity:0; visibility:hidden; pointer-events:none; transition:.3s }
        .backdrop.show { opacity:1; visibility:visible; pointer-events:auto }

        .content { padding:24px 26px calc(40px + env(safe-area-inset-bottom)); display:grid; gap:24px; min-width:0 }
        .ss-wrap { width:100%; display:grid; gap:24px; min-width:0 }

        .page-head { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:14px; width:100% }
        .page-head h2 { font-size:24px; font-weight:800; letter-spacing:-.4px }
        .page-head p { color:var(--muted); font-size:13px; margin-top:3px }

        .card { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow); overflow:hidden; transition:background .4s; min-width:0 }
        .sec-head2 { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; padding:20px 26px; border-bottom:1px solid var(--border) }
        .sec-head2 h3 { font-size:17px; font-weight:800; letter-spacing:-.3px }
        .sec-head2 p { font-size:12.5px; color:var(--muted); margin-top:3px }
        .sec-head2 > i { font-size:22px; color:var(--lime) }

        .ps-body { padding:26px 28px 28px; display:grid; gap:22px; min-width:0 }
        .fl { min-width:0; }
        .fl label { display:block; font-size:11px; font-weight:800; letter-spacing:.08em; text-transform:uppercase; color:var(--muted); margin-bottom:8px }
        .frow2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; align-items:start }
        .frow3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; align-items:start }

        .in-wrap input {
            width:100%; min-height:50px;
            border:1.5px solid var(--border); background:var(--bg);
            border-radius:12px; padding:13px 16px;
            font:600 13.5px 'Plus Jakarta Sans',sans-serif;
            color:var(--text); outline:none;
            transition:border-color .25s, box-shadow .25s, background .25s
        }
        .in-wrap input::placeholder { color:#9fb0c0; font-weight:500 }
        body.dark .in-wrap input::placeholder { color:#5c718c }
        .in-wrap input:hover { border-color:#d3deea }
        .in-wrap input:focus { border-color:var(--lime); background:var(--card); box-shadow:0 0 0 3px rgba(62,197,224,.12) }
        .image-setting { display:grid; align-content:start; gap:10px }
        .setting-image-preview { width:min(100%,280px); height:145px; object-fit:cover; border:1px solid var(--border); border-radius:12px; background:var(--bg) }
        .image-setting input[type=file] { max-width:100%; color:var(--muted); font-size:12px }
        .image-clear { display:flex; align-items:center; gap:8px; color:var(--muted); font-size:12px; text-transform:none !important; letter-spacing:0 !important }
        .image-clear input { accent-color:var(--forest) }

        .hint { font-size:11.5px; color:var(--muted); line-height:1.65; background:var(--bg); border:1px dashed var(--border); padding:11px 15px; border-radius:11px }
        .hint b { color:var(--text) }
        .hint i { vertical-align:-2px }

        .alert-ok { display:flex; align-items:center; gap:10px; background:#dcfce7; color:#16a34a; border:1px solid rgba(34,197,94,.35); padding:13px 18px; border-radius:12px; font-weight:700; font-size:13.5px }
        body.dark .alert-ok { background:rgba(74,222,128,.1); color:#4ade80 }

        .btn-save { display:inline-flex; align-items:center; justify-content:center; gap:9px; width:100%; background:linear-gradient(135deg, var(--forest), var(--forest-2)); color:#fff; border:none; padding:15px 22px; border-radius:13px; font-weight:800; font-size:13.5px; letter-spacing:.4px; cursor:pointer; transition:.25s; box-shadow:0 8px 20px rgba(14,90,117,.35) }
        body.dark .btn-save { background:linear-gradient(135deg, var(--lime), #7edcf2); color:#073344 }
        @media (hover:hover) { .btn-save:hover { transform:translateY(-2px); box-shadow:0 14px 28px rgba(14,90,117,.45) } }
        .btn-save:active { transform:scale(.98) }

        .reveal { opacity:0; transform:translateY(20px); transition:opacity .45s ease, transform .45s cubic-bezier(.22,1,.36,1) }
        .reveal.show { opacity:1; transform:none }

        @media(max-width:992px) {
            .sidebar { transform:translateX(-105%); width:268px !important }
            .layout.mobile-open .sidebar { transform:none }
            .main { margin-left:0 !important }
            .content { padding:18px 14px calc(40px + env(safe-area-inset-bottom)) }
        }
        @media(max-width:680px) {
            .page-head h2 { font-size:20px }
            .ps-body { padding:20px 16px 22px }
            .in-wrap input { font-size:16px; min-height:52px }
            .frow2, .frow3 { grid-template-columns:1fr; gap:14px }
        }
    </style>
</head>

<body>
    <div class="layout" id="layout">
        @include('admin.include.sidebar')
        <div class="main">
            @include('admin.include.navbar')
            <main class="content">

                <div class="page-head reveal">
                    <div>
                        <h2>Store Settings</h2>
                        <p>Contact page ka email, phone, address, hours, socials + About page ke stats — yahan se control karo.</p>
                    </div>
                </div>

                <div class="ss-wrap">

                    @if (session('success'))
                    <div class="alert-ok reveal"><i class="ri-checkbox-circle-fill"></i>{{ session('success') }}</div>
                    @endif

                    <form id="storeSettingsForm" method="POST" action="{{ route('admin.store-settings.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <!-- ══════════ STORE CONTACT INFO ══════════ -->
                        <div class="card reveal" style="transition-delay: 0.04s; margin-top: 25px">
                            <div class="sec-head2">
                                <div>
                                    <h3>Store Contact Info</h3>
                                    <p>Contact page ke quick cards + detail list + footer ka email.</p>
                                </div>
                                <i class="ri-contacts-book-line"></i>
                            </div>
                            <div class="ps-body">
                                <div class="frow2">
                                    <div class="fl">
                                        <label for="site_email">Store Email</label>
                                        <div class="in-wrap"><input type="email" id="site_email" name="site_email" value="{{ old('site_email', $s['site_email']) }}" placeholder="hello@cosmetics.co.uk" spellcheck="false"></div>
                                    </div>
                                    <div class="fl">
                                        <label for="site_phone">Store Phone</label>
                                        <div class="in-wrap"><input type="text" id="site_phone" name="site_phone" value="{{ old('site_phone', $s['site_phone']) }}" placeholder="+44 20 7946 0958" inputmode="tel"></div>
                                    </div>
                                </div>
                                <div class="fl">
                                    <label for="site_address">Store Address</label>
                                    <div class="in-wrap"><input type="text" id="site_address" name="site_address" value="{{ old('site_address', $s['site_address']) }}" placeholder="245 Oxford Street, London W1D 2DW, UK"></div>
                                </div>
                                <div class="fl">
                                    <label for="site_map_query">Google Map Location (search query)</label>
                                    <div class="in-wrap"><input type="text" id="site_map_query" name="site_map_query" value="{{ old('site_map_query', $s['site_map_query']) }}" placeholder="Oxford Street, London" spellcheck="false"></div>
                                </div>
                            </div>
                        </div>

                        <div class="card reveal" style="transition-delay: 0.04s; margin-top: 25px">
                            <div class="sec-head2">
                                <div>
                                    <h3>Contact Page Content &amp; Photos</h3>
                                    <p>Edit the in-page copy and photos. The banner heading and image remain under Page Headers.</p>
                                </div>
                                <i class="ri-image-edit-line"></i>
                            </div>
                            <div class="ps-body">
                                <div class="frow2">
                                    <div class="fl">
                                        <label for="contact_intro_label">Intro label</label>
                                        <div class="in-wrap"><input type="text" id="contact_intro_label" name="contact_intro_label" maxlength="100" value="{{ old('contact_intro_label', $contactSettings['contact_intro_label']) }}"></div>
                                    </div>
                                    <div class="fl">
                                        <label for="contact_form_tag">Message form eyebrow</label>
                                        <div class="in-wrap"><input type="text" id="contact_form_tag" name="contact_form_tag" maxlength="100" value="{{ old('contact_form_tag', $contactSettings['contact_form_tag']) }}"></div>
                                    </div>
                                </div>
                                <div class="fl">
                                    <label for="contact_intro_text">Intro text</label>
                                    <div class="in-wrap"><input type="text" id="contact_intro_text" name="contact_intro_text" maxlength="500" value="{{ old('contact_intro_text', $contactSettings['contact_intro_text']) }}"></div>
                                </div>
                                <div class="frow2">
                                    <div class="fl">
                                        <label for="contact_form_title">Message form title</label>
                                        <div class="in-wrap"><input type="text" id="contact_form_title" name="contact_form_title" maxlength="150" value="{{ old('contact_form_title', $contactSettings['contact_form_title']) }}"></div>
                                    </div>
                                    <div class="fl">
                                        <label for="contact_info_title">Contact details heading</label>
                                        <div class="in-wrap"><input type="text" id="contact_info_title" name="contact_info_title" maxlength="150" value="{{ old('contact_info_title', $contactSettings['contact_info_title']) }}"></div>
                                    </div>
                                </div>
                                <div class="fl">
                                    <label for="contact_info_text">Contact details introduction</label>
                                    <div class="in-wrap"><input type="text" id="contact_info_text" name="contact_info_text" maxlength="500" value="{{ old('contact_info_text', $contactSettings['contact_info_text']) }}"></div>
                                </div>
                                <div class="frow2">
                                    @foreach(['contact_image' => 'Contact feature photo', 'contact_info_image' => 'Decorative details photo'] as $imageKey => $imageLabel)
                                    <div class="fl image-setting">
                                        <label for="{{ $imageKey }}">{{ $imageLabel }}</label>
                                        <img class="setting-image-preview" id="{{ $imageKey }}Preview" src="{{ $contactImages[$imageKey] }}" alt="{{ $imageLabel }} preview">
                                        <input type="file" id="{{ $imageKey }}" name="{{ $imageKey }}" accept="image/jpeg,image/png,image/webp" onchange="previewStoreImage(this)">
                                        <label class="image-clear"><input type="checkbox" name="clear_{{ $imageKey }}" value="1"> Use default image instead</label>
                                    </div>
                                    @endforeach
                                </div>
                                <div class="fl">
                                    <label for="contact_image_alt">Feature photo description (accessibility text)</label>
                                    <div class="in-wrap"><input type="text" id="contact_image_alt" name="contact_image_alt" maxlength="255" value="{{ old('contact_image_alt', $contactSettings['contact_image_alt']) }}"></div>
                                </div>
                            </div>
                        </div>

                        <!-- ══════════ OPENING HOURS ══════════ -->
                        <div class="card reveal" style="transition-delay: 0.04s; margin-top: 25px">
                            <div class="sec-head2">
                                <div>
                                    <h3>Opening Hours</h3>
                                    <p>Contact page ke hours list mein yehi text dikhega.</p>
                                </div>
                                <i class="ri-time-line"></i>
                            </div>
                            <div class="ps-body">
                                <div class="frow3">
                                    <div class="fl">
                                        <label for="hours_weekdays">Monday — Friday</label>
                                        <div class="in-wrap"><input type="text" id="hours_weekdays" name="hours_weekdays" value="{{ old('hours_weekdays', $s['hours_weekdays']) }}" placeholder="9:00 AM – 6:00 PM"></div>
                                    </div>
                                    <div class="fl">
                                        <label for="hours_saturday">Saturday</label>
                                        <div class="in-wrap"><input type="text" id="hours_saturday" name="hours_saturday" value="{{ old('hours_saturday', $s['hours_saturday']) }}" placeholder="10:00 AM – 4:00 PM"></div>
                                    </div>
                                    <div class="fl">
                                        <label for="hours_sunday">Sunday</label>
                                        <div class="in-wrap"><input type="text" id="hours_sunday" name="hours_sunday" value="{{ old('hours_sunday', $s['hours_sunday']) }}" placeholder="Closed"></div>
                                    </div>
                                </div>
                                <p class="hint"><i class="ri-information-line"></i> Sunday ke liye <b>Closed</b> likho — red style mein dikhega contact page pe.</p>
                            </div>
                        </div>

                        <!-- ══════════ SOCIAL LINKS ══════════ -->
                        <div class="card reveal " style="transition-delay: 0.04s; margin-top: 25px">
                            <div class="sec-head2">
                                <div>
                                    <h3>Social Links</h3>
                                    <p>Contact page ke Follow Us buttons.</p>
                                </div>
                                <i class="ri-share-forward-line"></i>
                            </div>
                            <div class="ps-body">
                                <div class="frow2">
                                    <div class="fl">
                                        <label for="social_instagram">Instagram URL</label>
                                        <div class="in-wrap"><input type="url" id="social_instagram" name="social_instagram" value="{{ old('social_instagram', $s['social_instagram']) }}" placeholder="https://instagram.com/yourstore" spellcheck="false"></div>
                                    </div>
                                    <div class="fl">
                                        <label for="social_facebook">Facebook URL</label>
                                        <div class="in-wrap"><input type="url" id="social_facebook" name="social_facebook" value="{{ old('social_facebook', $s['social_facebook']) }}" placeholder="https://facebook.com/yourstore" spellcheck="false"></div>
                                    </div>
                                </div>
                                <div class="frow2">
                                    <div class="fl">
                                        <label for="social_twitter">Twitter / X URL</label>
                                        <div class="in-wrap"><input type="url" id="social_twitter" name="social_twitter" value="{{ old('social_twitter', $s['social_twitter']) }}" placeholder="https://x.com/yourstore" spellcheck="false"></div>
                                    </div>
                                    <div class="fl">
                                        <label for="social_youtube">YouTube URL</label>
                                        <div class="in-wrap"><input type="url" id="social_youtube" name="social_youtube" value="{{ old('social_youtube', $s['social_youtube']) }}" placeholder="https://youtube.com/@yourstore" spellcheck="false"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card reveal" style="transition-delay: 0.04s; margin-top: 25px">
                            <div class="sec-head2">
                                <div>
                                    <h3>Storefront Footer</h3>
                                    <p>Manage the footer headings, brand message, quick links, and copyright text shown across the storefront.</p>
                                </div>
                                <i class="ri-layout-bottom-line"></i>
                            </div>
                            <div class="ps-body">
                                <div class="frow2">
                                    <div class="fl"><label for="footer_contact_title">Contact column heading</label><div class="in-wrap"><input id="footer_contact_title" name="footer_contact_title" maxlength="80" value="{{ old('footer_contact_title', $s['footer_contact_title']) }}"></div></div>
                                    <div class="fl"><label for="footer_links_title">Links column heading</label><div class="in-wrap"><input id="footer_links_title" name="footer_links_title" maxlength="80" value="{{ old('footer_links_title', $s['footer_links_title']) }}"></div></div>
                                </div>
                                <div class="fl"><label for="footer_tagline">Brand message</label><div class="in-wrap"><input id="footer_tagline" name="footer_tagline" maxlength="255" value="{{ old('footer_tagline', $s['footer_tagline']) }}"></div></div>
                                @foreach(range(1, 5) as $linkNumber)
                                <div class="frow2">
                                    <div class="fl"><label for="footer_link_{{ $linkNumber }}_label">Quick link {{ $linkNumber }} label</label><div class="in-wrap"><input id="footer_link_{{ $linkNumber }}_label" name="footer_link_{{ $linkNumber }}_label" maxlength="80" value="{{ old("footer_link_{$linkNumber}_label", $s["footer_link_{$linkNumber}_label"]) }}"></div></div>
                                    <div class="fl"><label for="footer_link_{{ $linkNumber }}_url">Quick link {{ $linkNumber }} URL</label><div class="in-wrap"><input id="footer_link_{{ $linkNumber }}_url" name="footer_link_{{ $linkNumber }}_url" maxlength="255" value="{{ old("footer_link_{$linkNumber}_url", $s["footer_link_{$linkNumber}_url"]) }}" placeholder="/shop or https://example.com"></div></div>
                                </div>
                                @endforeach
                                <div class="fl"><label for="footer_copyright">Copyright line (use {year} for the current year)</label><div class="in-wrap"><input id="footer_copyright" name="footer_copyright" maxlength="255" value="{{ old('footer_copyright', $s['footer_copyright']) }}"></div></div>
                            </div>
                        </div>

                        <!-- ══════════ ABOUT PAGE STATS ══════════ -->
                        <div class="card reveal" style="transition-delay: 0.04s; margin-top: 25px">
                            <div class="sec-head2">
                                <div>
                                    <h3>About Page Stats</h3>
                                    <p>About page ke animated counters (Years, Clients, Products, Satisfaction).</p>
                                </div>
                                <i class="ri-bar-chart-grouped-line"></i>
                            </div>
                            <div class="ps-body">
                                <div class="frow2">
                                    <div class="fl">
                                        <label for="stat_years">Years of Expertise</label>
                                        <div class="in-wrap"><input type="number" id="stat_years" name="stat_years" value="{{ old('stat_years', $s['stat_years']) }}" min="0" max="999" inputmode="numeric"></div>
                                    </div>
                                    <div class="fl">
                                        <label for="stat_clients">Happy Clients (thousands — 25 = 25k)</label>
                                        <div class="in-wrap"><input type="number" id="stat_clients" name="stat_clients" value="{{ old('stat_clients', $s['stat_clients']) }}" min="0" max="9999" inputmode="numeric"></div>
                                    </div>
                                </div>
                                <div class="frow2">
                                    <div class="fl">
                                        <label for="stat_products">Beauty Products</label>
                                        <div class="in-wrap"><input type="number" id="stat_products" name="stat_products" value="{{ old('stat_products', $s['stat_products']) }}" min="0" max="9999" inputmode="numeric"></div>
                                    </div>
                                    <div class="fl">
                                        <label for="stat_satisfaction">Satisfaction Rate (%)</label>
                                        <div class="in-wrap"><input type="number" id="stat_satisfaction" name="stat_satisfaction" value="{{ old('stat_satisfaction', $s['stat_satisfaction']) }}" min="0" max="100" inputmode="numeric"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-save reveal" style="transition-delay: 0.04s; margin-top: 25px"><i class="ri-save-3-line"></i>Save Store Settings</button>
                    </form>

                </div>

            </main>
        </div>
    </div>

    <script>
        var storeCsrf = document.querySelector('meta[name="csrf-token"]').content;
        function previewStoreImage(input) {
            if (!input.files || !input.files[0]) return;
            var preview = document.getElementById(input.id + 'Preview');
            if (preview) preview.src = URL.createObjectURL(input.files[0]);
        }
        var storeToast = Swal.mixin({ toast:true, position:'top-end', showConfirmButton:false, timer:2600, timerProgressBar:true, background:'#111c2e', color:'#e7edf5' });
        document.getElementById('storeSettingsForm').addEventListener('submit', function (event) {
            event.preventDefault();
            var form = event.currentTarget;
            var button = form.querySelector('.btn-save');
            var original = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="ri-loader-4-line"></i>Saving…';
            fetch(form.action, {
                method:'POST',
                body:new FormData(form),
                headers:{ 'X-CSRF-TOKEN':storeCsrf, 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' }
            }).then(function (response) {
                return response.json().catch(function () { return {}; }).then(function (data) {
                    if (response.status === 419) throw new Error('Session expired — please refresh the page.');
                    if (!response.ok) {
                        var errors = data.errors || {};
                        var fields = Object.keys(errors);
                        throw new Error(fields.length ? errors[fields[0]][0] : (data.message || 'Could not save store settings.'));
                    }
                    return data;
                });
            }).then(function (data) {
                storeToast.fire({ icon:data.status === 'success' ? 'success' : 'error', title:data.message || 'Save failed' });
            }).catch(function (error) {
                storeToast.fire({ icon:'error', title:error.message || 'Could not save store settings.' });
            }).finally(function () {
                button.disabled = false;
                button.innerHTML = original;
            });
        });
        (function() {
            var io = ('IntersectionObserver' in window) ? new IntersectionObserver(function(es) {
                es.forEach(function(e) { if (e.isIntersecting) { e.target.classList.add('show'); io.unobserve(e.target); } });
            }, { threshold:.06 }) : null;
            document.querySelectorAll('.reveal').forEach(function(el) { io ? io.observe(el) : el.classList.add('show'); });
            setTimeout(function() {
                document.querySelectorAll('.reveal:not(.show)').forEach(function(el) { el.classList.add('show'); });
            }, 1200);
        })();
    </script>
</body>
</html>