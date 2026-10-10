@extends('admin.layouts.ecommerce')

@section('title', 'Homepage Slider')
@section('content')
    <style>
        .slider-intro { display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap }
        .slider-intro p { max-width:720px;margin:5px 0 0;color:var(--muted);font-size:13px;line-height:1.6 }
        .slider-preview { display:inline-flex;align-items:center;gap:8px;padding:10px 13px;border:1px solid var(--border);border-radius:10px;background:var(--card);color:var(--brand);font-size:12px;font-weight:700;text-decoration:none }
        .slider-list { display:grid;gap:12px }
        .slider-row { display:grid;grid-template-columns:40px 54px minmax(130px,1fr) minmax(100px,.7fr) minmax(190px,1.2fr) auto;align-items:center;gap:13px;min-width:0;padding:14px;border:1px solid var(--border);border-radius:14px;background:var(--card) }
        .slider-order { display:grid;gap:5px }
        .slider-order button,.slider-remove { display:grid;place-items:center;width:34px;height:32px;border:1px solid var(--border);border-radius:9px;background:var(--bg);color:var(--text);cursor:pointer }
        .slider-order button:hover { border-color:var(--accent);color:var(--brand) }
        .slider-remove { color:#b42318 }
        .slider-logo { display:grid;place-items:center;width:52px;height:52px;overflow:hidden;border:1px solid var(--border);border-radius:12px;background:var(--bg);color:var(--brand);font-size:14px;font-weight:800 }
        .slider-logo img { width:100%;height:100%;padding:5px;object-fit:contain }
        .slider-field { display:grid;gap:6px;min-width:0;color:var(--muted);font-size:10px;font-weight:800;letter-spacing:.04em;text-transform:uppercase }
        .slider-field input:not([type=file]) { width:100%;min-width:0;min-height:40px;padding:9px 10px;border:1px solid var(--border);border-radius:9px;background:var(--card);color:var(--text);font:500 12px 'Plus Jakarta Sans',sans-serif }
        .slider-upload { display:grid;gap:7px;min-width:0 }
        .slider-upload input { width:100%;max-width:100%;min-width:0;color:var(--muted);font-size:10px }
        .slider-upload input::file-selector-button { padding:7px 9px;border:0;border-radius:7px;background:var(--bg);color:var(--brand);font:700 10px 'Plus Jakarta Sans',sans-serif;cursor:pointer }
        .slider-visible { display:flex;align-items:center;gap:7px;white-space:nowrap;color:var(--muted);font-size:11px;font-weight:700 }
        .slider-visible input,.slider-clear input { accent-color:var(--brand) }
        .slider-clear { display:flex;align-items:center;gap:5px;width:max-content;max-width:100%;color:var(--muted);font-size:10px;font-weight:600;white-space:normal }
        .slider-actions { display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap }
        .slider-add,.slider-save { display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:42px;padding:0 15px;border:1px solid var(--border);border-radius:10px;background:var(--card);color:var(--text);font:700 12px 'Plus Jakarta Sans',sans-serif;cursor:pointer }
        .slider-save { border:0;background:linear-gradient(120deg,#0e5a75,#17739b);color:#fff;padding:0 20px }
        .slider-empty { padding:28px;text-align:center;color:var(--muted);font-size:13px }
        .slider-errors { padding:12px 15px;border:1px solid #fecdca;border-radius:10px;background:#fef3f2;color:#b42318;font-size:12px }
        .slider-errors ul { margin:6px 0 0;padding-left:18px }
        @media(max-width:1080px) { .slider-row { grid-template-columns:36px 48px minmax(130px,1fr) minmax(90px,.6fr) minmax(170px,1fr); } .slider-visibility { grid-column:3/-1;display:flex;align-items:center;gap:14px; } .slider-remove { grid-column:5;grid-row:1;justify-self:end } }
        @media(max-width:680px) { .content { padding:16px } .slider-row { grid-template-columns:36px 48px minmax(0,1fr) 36px;gap:10px;padding:11px } .slider-field-name { grid-column:3;grid-row:1 } .slider-field-mark { grid-column:2/4;grid-row:2 } .slider-upload { grid-column:1/-1;grid-row:3 } .slider-upload input[type=file] { width:100%;max-width:100% } .slider-visibility { grid-column:1/-1;grid-row:4;justify-content:space-between } .slider-remove { grid-column:4;grid-row:1 } }
    </style>

    <section class="page-head">
        <div class="slider-intro">
            <div>
                <h1><i class="ri-slideshow-3-line"></i> Homepage Slider</h1>
                <p>Manage the brand slider on the storefront homepage: add names and logo images, change the order, and choose which brands are visible. The slider updates as soon as you save.</p>
            </div>
            <a class="slider-preview" href="{{ route('home') }}" target="_blank" rel="noopener"><i class="ri-external-link-line"></i> Preview homepage</a>
        </div>
    </section>

    @if(session('success'))
        <div class="alert success" role="status">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="slider-errors" role="alert">
            <strong>Could not save the slider:</strong>
            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.home-slider.update') }}" enctype="multipart/form-data" id="homeSliderForm">
        @csrf
        @method('PUT')
        <section class="card">
            <div class="slider-actions" style="margin-bottom:15px">
                <div>
                    <h2 class="card-title" style="margin-bottom:4px">Brand cards</h2>
                    <span style="color:var(--muted);font-size:11px">Use the arrow buttons to change order. Upload JPG, PNG, or WebP logos up to 2 MB.</span>
                </div>
                <button type="button" class="slider-add" id="addSliderBrand"><i class="ri-add-line"></i> Add brand</button>
            </div>

            <div class="slider-list" id="sliderBrandList">
                @php
                    $brandMap = $brands->keyBy('id');
                    $brandOrder = old('order', $brands->pluck('id')->all());
                @endphp
                @foreach($brandOrder as $brandId)
                    @php $brand = $brandMap->get($brandId); @endphp
                    @if($brand)
                    <article class="slider-row" data-slider-row data-brand-id="{{ $brand['id'] }}">
                        <div class="slider-order">
                            <button type="button" data-move="up" aria-label="Move {{ $brand['name'] }} up"><i class="ri-arrow-up-line"></i></button>
                            <button type="button" data-move="down" aria-label="Move {{ $brand['name'] }} down"><i class="ri-arrow-down-line"></i></button>
                        </div>
                        <div class="slider-logo" data-logo-preview>
                            @if($brand['logo'])<img src="{{ $brand['logo'] }}" alt="">@else{{ $brand['mark'] }}@endif
                        </div>
                        <label class="slider-field slider-field-name">Brand name
                            <input type="text" name="brands[{{ $brand['id'] }}][name]" maxlength="120" required value="{{ old('brands.'.$brand['id'].'.name', $brand['name']) }}">
                        </label>
                        <label class="slider-field slider-field-mark">Text mark (used when no logo is uploaded)
                            <input type="text" name="brands[{{ $brand['id'] }}][mark]" maxlength="12" value="{{ old('brands.'.$brand['id'].'.mark', $brand['mark']) }}">
                        </label>
                        <div class="slider-upload">
                            <input type="hidden" name="brands[{{ $brand['id'] }}][logo_path]" value="{{ old('brands.'.$brand['id'].'.logo_path', $brand['logo_path']) }}">
                            <button class="button" type="button" data-logo-picker>Choose logo from Media library</button>
                            @if($brand['logo'])
                            <label class="slider-clear"><input type="checkbox" name="brands[{{ $brand['id'] }}][remove_logo]" value="1"> Remove logo</label>
                            @endif
                        </div>
                        <div class="slider-visibility">
                            <label class="slider-visible"><input type="checkbox" name="brands[{{ $brand['id'] }}][visible]" value="1" @checked(old('brands.'.$brand['id'].'.visible', $brand['visible']))> Show on homepage</label>
                        </div>
                        <button type="button" class="slider-remove" data-remove-brand aria-label="Remove {{ $brand['name'] }}"><i class="ri-delete-bin-line"></i></button>
                        <input type="hidden" name="order[]" value="{{ $brand['id'] }}">
                    </article>
                    @endif
                @endforeach
            </div>
            <p class="slider-empty" id="sliderEmpty" @if($brands->isNotEmpty()) hidden @endif>No brand cards yet. Use “Add brand” to create one.</p>
        </section>

        <div class="slider-actions" style="margin-top:16px">
            <span style="color:var(--muted);font-size:11px">Inactive and removed cards are not shown on the homepage.</span>
            <button type="submit" class="slider-save"><i class="ri-save-line"></i> Save slider</button>
        </div>
    </form>

    <template id="sliderBrandTemplate">
        <article class="slider-row" data-slider-row data-brand-id="__ID__">
            <div class="slider-order">
                <button type="button" data-move="up" aria-label="Move brand up"><i class="ri-arrow-up-line"></i></button>
                <button type="button" data-move="down" aria-label="Move brand down"><i class="ri-arrow-down-line"></i></button>
            </div>
            <div class="slider-logo" data-logo-preview>__MARK__</div>
            <label class="slider-field slider-field-name">Brand name
                <input type="text" name="brands[__ID__][name]" maxlength="120" required placeholder="Brand name">
            </label>
            <label class="slider-field slider-field-mark">Text mark (used when no logo is uploaded)
                <input type="text" name="brands[__ID__][mark]" maxlength="12" placeholder="e.g. AB">
            </label>
            <div class="slider-upload">
                <input type="hidden" name="brands[__ID__][logo_path]" value="">
                <button class="button" type="button" data-logo-picker>Choose logo from Media library</button>
            </div>
            <div class="slider-visibility">
                <label class="slider-visible"><input type="checkbox" name="brands[__ID__][visible]" value="1" checked> Show on homepage</label>
            </div>
            <button type="button" class="slider-remove" data-remove-brand aria-label="Remove brand"><i class="ri-delete-bin-line"></i></button>
            <input type="hidden" name="order[]" value="__ID__">
        </article>
    </template>

    <script>
        (function () {
            var list = document.getElementById('sliderBrandList');
            var empty = document.getElementById('sliderEmpty');
            var template = document.getElementById('sliderBrandTemplate');
            if (!list || !template) return;

            function syncEmpty() {
                empty.hidden = list.querySelectorAll('[data-slider-row]').length > 0;
            }

            document.getElementById('addSliderBrand').addEventListener('click', function () {
                var id = 'custom-' + (window.crypto && crypto.randomUUID ? crypto.randomUUID() : Date.now().toString(36) + Math.random().toString(36).slice(2));
                var html = template.innerHTML.replaceAll('__ID__', id).replace('__MARK__', 'BR');
                list.insertAdjacentHTML('beforeend', html);
                list.lastElementChild.querySelector('input[name$="[name]"]').focus();
                syncEmpty();
            });

            list.addEventListener('click', function (event) {
                var picker = event.target.closest('[data-logo-picker]');
                if (picker) {
                    if (typeof window.openMediaPicker !== 'function') {
                        window.alert('The Media library is unavailable. Refresh the page and try again.');
                        return;
                    }
                    var row = picker.closest('[data-slider-row]');
                    window.openMediaPicker(function (image) {
                        row.querySelector('input[name$="[logo_path]"]').value = image.path;
                        var preview = row.querySelector('[data-logo-preview]');
                        var logo = document.createElement('img');
                        logo.alt = '';
                        logo.src = image.url;
                        preview.replaceChildren(logo);
                        var removeLogo = row.querySelector('input[name$="[remove_logo]"]');
                        if (removeLogo) removeLogo.checked = false;
                    });
                    return;
                }
                var remove = event.target.closest('[data-remove-brand]');
                if (remove) {
                    remove.closest('[data-slider-row]').remove();
                    syncEmpty();
                    return;
                }

                var move = event.target.closest('[data-move]');
                if (!move) return;
                var row = move.closest('[data-slider-row]');
                if (move.dataset.move === 'up' && row.previousElementSibling) {
                    list.insertBefore(row, row.previousElementSibling);
                } else if (move.dataset.move === 'down' && row.nextElementSibling) {
                    list.insertBefore(row.nextElementSibling, row);
                }
            });

        }());
    </script>
@endsection
