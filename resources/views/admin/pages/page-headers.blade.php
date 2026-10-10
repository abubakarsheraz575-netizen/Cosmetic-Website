<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Page Headers — Cosmetics Admin</title>
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
        .content{width:100%;padding:26px}
        .headers-page{width:min(100%,1280px);margin:0 auto;display:grid;gap:20px}
        .headers-head{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}
        .headers-head h1{margin:0;font-size:24px;font-weight:800;letter-spacing:-.4px}
        .headers-head p{margin:5px 0 0;color:#68788f;font-size:13px}
        .headers-card{overflow:hidden;border:1px solid #e5ebf0;border-radius:17px;background:#fff;box-shadow:0 8px 24px rgba(16,24,40,.05)}
        .headers-card-head{display:flex;align-items:center;gap:12px;padding:18px 21px;border-bottom:1px solid #edf1f4}
        .headers-icon{width:42px;height:42px;flex:none;display:grid;place-items:center;border-radius:12px;background:#e4f6fb;color:#0e7188;font-size:20px}
        .headers-card-head h2{margin:0;font-size:15px;font-weight:800}
        .headers-card-head p{margin:4px 0 0;color:#758394;font-size:11.5px}
        .headers-fields{display:grid;grid-template-columns:minmax(0,.85fr) minmax(0,1.15fr);gap:20px;padding:21px}
        .headers-field{min-width:0}
        .headers-field label{display:block;margin-bottom:7px;color:#4c5c6d;font-size:11px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
        .headers-field input:not([type=file]),.headers-field textarea{width:100%;min-height:45px;padding:11px 13px;border:1px solid #dfe7ec;border-radius:11px;background:#fbfcfd;color:#172b3a;font:500 13px 'Plus Jakarta Sans',sans-serif;outline:0}
        .headers-field textarea{min-height:96px;resize:vertical}
        .headers-field input:focus,.headers-field textarea:focus{border-color:#3ec5e0;box-shadow:0 0 0 3px rgba(62,197,224,.13)}
        .headers-image{display:flex;align-items:center;gap:14px}
        .headers-preview{width:min(100%,280px);height:112px;flex:none;object-fit:cover;border:1px solid #e1e8ed;border-radius:11px;background:linear-gradient(120deg,#0d4257,#17739b 55%,#3ec5e0)}
        .headers-field input[type=file]{width:100%;min-width:0;color:#596879;font-size:12px}
        .headers-field input[type=file]::file-selector-button{margin-right:10px;padding:8px 10px;border:0;border-radius:8px;background:#e4f6fb;color:#155e75;font:700 11px 'Plus Jakarta Sans',sans-serif;cursor:pointer}
        .headers-hint{margin:7px 0 0;color:#8a96a3;font-size:10.5px;line-height:1.5}
        .headers-actions{display:flex;align-items:center;gap:12px;flex-wrap:wrap;padding-bottom:8px}
        .headers-save{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:48px;padding:0 24px;border:0;border-radius:12px;background:linear-gradient(120deg,#0e5a75,#17739b);color:#fff;font:800 13px 'Plus Jakarta Sans',sans-serif;box-shadow:0 8px 20px rgba(14,90,117,.22);cursor:pointer}
        .headers-save:disabled{opacity:.65;cursor:wait}
        .headers-note{color:#82909c;font-size:11px}
        @media(max-width:992px){.main,.layout.collapsed .main{width:100%;margin-left:0}}
        @media(max-width:680px){.content{padding:16px}.headers-head h1{font-size:21px}.headers-fields{grid-template-columns:1fr;padding:16px}.headers-image{align-items:flex-start;flex-direction:column}.headers-preview{width:100%;height:150px}.headers-actions .headers-save{width:100%}}
    </style>
</head>
<body>
    <div class="layout" id="layout">
        @include('admin.include.sidebar')
        <div class="main">
            @include('admin.include.navbar')
            <main class="content">
                <form class="headers-page" id="pageHeadersForm" method="POST" action="{{ route('admin.page-headers.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    <header class="headers-head">
                        <div>
                            <h1>Storefront Page Headers</h1>
                            <p>Set the heading and banner image shown on each public page. Existing page content stays unchanged.</p>
                        </div>
                    </header>

                    @foreach(config('page_headers.pages') as $page => $definition)
                    <section class="headers-card">
                        <div class="headers-card-head">
                            <span class="headers-icon"><i class="ri-image-edit-line"></i></span>
                            <div>
                                <h2>{{ $definition['label'] }} page</h2>
                                <p>Customize the page banner title and background image.</p>
                            </div>
                        </div>
                        <div class="headers-fields">
                            <div class="headers-field">
                                <label for="headerTitle-{{ $page }}">Banner heading</label>
                                <input id="headerTitle-{{ $page }}" name="page_header_{{ $page }}_title" maxlength="255" required value="{{ $headers[$page]['title'] }}">
                                @if($page === 'product_detail')
                                <p class="headers-hint">This banner label is separate from each product's name and product details.</p>
                                @endif
                            </div>
                            @if($page === 'blog')
                            <div class="headers-field">
                                <label for="headerDescription-{{ $page }}">Journal introduction</label>
                                <textarea id="headerDescription-{{ $page }}" name="page_header_{{ $page }}_description" maxlength="500" required>{{ $headers[$page]['description'] }}</textarea>
                                <p class="headers-hint">This text appears below the Beauty Journal title on /blog.</p>
                            </div>
                            @endif
                            <div class="headers-field">
                                <label for="headerImage-{{ $page }}">Banner image</label>
                                <div class="headers-image">
                                    <img class="headers-preview" id="headerPreview-{{ $page }}" src="{{ $headers[$page]['image'] ?: '' }}" alt="{{ $definition['label'] }} banner preview" @if(!$headers[$page]['image']) hidden @endif>
                                    <input id="headerImage-{{ $page }}" type="file" name="page_header_{{ $page }}_image" accept="image/jpeg,image/png,image/webp">
                                </div>
                                <p class="headers-hint">Upload JPG, PNG or WebP, up to 5 MB. Leave empty to keep the current image. Product Detail uses its original gradient until an image is uploaded.</p>
                            </div>
                        </div>
                    </section>
                    @endforeach

                    <footer class="headers-actions">
                        <button class="headers-save" id="pageHeadersSave" type="submit"><i class="ri-save-3-line"></i> Save Page Headers</button>
                        <span class="headers-note">Saved titles and images appear on the storefront immediately.</span>
                    </footer>
                </form>
            </main>
        </div>
    </div>
    <div class="backdrop" id="backdrop"></div>
    <script>
        (function () {
            var form = document.getElementById('pageHeadersForm');
            var button = document.getElementById('pageHeadersSave');
            var token = document.querySelector('meta[name="csrf-token"]').content;
            var toast = window.Swal ? Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2800 }) : null;

            Object.keys(@json(config('page_headers.pages'))).forEach(function (page) {
                var input = document.getElementById('headerImage-' + page);
                input.addEventListener('change', function () {
                    if (input.files && input.files[0]) {
                        var preview = document.getElementById('headerPreview-' + page);
                        preview.hidden = false;
                        preview.src = URL.createObjectURL(input.files[0]);
                    }
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
                            throw new Error(fields.length ? errors[fields[0]][0] : (data.message || 'Could not save page headers.'));
                        }
                        return data;
                    });
                }).then(function (data) {
                    if (data.status !== 'success') throw new Error(data.message || 'Could not save page headers.');
                    Object.keys(data.images || {}).forEach(function (page) {
                        var preview = document.getElementById('headerPreview-' + page);
                        preview.hidden = !data.images[page];
                        preview.src = data.images[page] || '';
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
