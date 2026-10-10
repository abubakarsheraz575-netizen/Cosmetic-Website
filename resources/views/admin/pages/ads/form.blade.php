@extends('admin.layouts.ecommerce')

@section('title', $title)

@section('content')
<style>
    .ad-form{max-width:920px;margin:auto;color:#172b3a}.ad-form-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:18px}.ad-form h1{margin:0;font-size:25px}.ad-form-head a{color:#28727b;font-size:12px;font-weight:700;text-decoration:none}
    .ad-form-card{padding:22px;border:1px solid #e3eaf0;border-radius:16px;background:#fff;box-shadow:0 12px 34px rgba(26,54,71,.06)}
    .ad-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:15px}.ad-field{display:grid;gap:6px}.ad-field.full{grid-column:1/-1}.ad-field label{font-size:11px;font-weight:800}.ad-field input,.ad-field select,.ad-field textarea{width:100%;min-height:42px;padding:10px 12px;border:1px solid #dfe7ed;border-radius:9px;background:#fff;color:#172b3a;font:inherit;font-size:12px}.ad-field textarea{min-height:75px;resize:vertical}.ad-help{color:#748494;font-size:10px;line-height:1.5}.ad-checkbox{display:flex;align-items:center;gap:9px;font-size:12px}.ad-checkbox input{width:17px;height:17px}.ad-submit{display:inline-flex;align-items:center;gap:8px;margin-top:18px;padding:11px 16px;border:0;border-radius:9px;background:#128c92;color:#fff;font-weight:800;cursor:pointer}.ad-error{color:#bd3434;font-size:10px}.ad-image-row{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:8px}.ad-image-row button{padding:0 12px;border:1px solid #dfe7ed;border-radius:9px;background:#f4f8fa;color:#28727b;font-size:11px;font-weight:800;white-space:nowrap;cursor:pointer}.ad-image-preview{display:block;width:min(100%,420px);height:150px;margin-top:8px;border:1px solid #e3eaf0;border-radius:10px;background:#f5f7f8;object-fit:cover}.ad-image-preview[hidden]{display:none}
    @media(max-width:650px){.ad-form-card{padding:15px}.ad-grid{grid-template-columns:1fr}.ad-field.full{grid-column:auto}.ad-submit{width:100%;justify-content:center}}
</style>
<main class="ad-form">
    <header class="ad-form-head"><h1>{{ $title }}</h1><a href="{{ route('admin.ads.index') }}">← Back to ads</a></header>
    <form class="ad-form-card" method="POST" action="{{ $ad->exists ? route('admin.ads.update', $ad) : route('admin.ads.store') }}">
        @csrf
        @if($ad->exists)@method('PATCH')@endif
        @if($errors->any())<div class="alert alert-danger" role="alert">Please correct the highlighted fields before saving.</div>@endif
        <div class="ad-grid">
            <div class="ad-field"><label for="name">Internal name *</label><input id="name" name="name" value="{{ old('name', $ad->name) }}" required maxlength="120">@error('name')<span class="ad-error">{{ $message }}</span>@enderror</div>
            <div class="ad-field"><label for="shortcode">Ad code (optional)</label><input id="shortcode" name="shortcode" value="{{ old('shortcode', $ad->shortcode) }}" maxlength="32" placeholder="Generated if left blank">@error('shortcode')<span class="ad-error">{{ $message }}</span>@enderror</div>
            <div class="ad-field full"><label for="title">Headline *</label><input id="title" name="title" value="{{ old('title', $ad->title) }}" required maxlength="180">@error('title')<span class="ad-error">{{ $message }}</span>@enderror</div>
            <div class="ad-field full"><label for="subtitle">Supporting text</label><textarea id="subtitle" name="subtitle" maxlength="180">{{ old('subtitle', $ad->subtitle) }}</textarea>@error('subtitle')<span class="ad-error">{{ $message }}</span>@enderror</div>
            <div class="ad-field"><label for="button_label">Button label</label><input id="button_label" name="button_label" value="{{ old('button_label', $ad->button_label) }}" maxlength="60" placeholder="Shop now"></div>
            <div class="ad-field"><label for="target_url">Destination URL *</label><input type="text" id="target_url" name="target_url" value="{{ old('target_url', $ad->target_url) }}" required placeholder="/shop or https://example.com/shop">@error('target_url')<span class="ad-error">{{ $message }}</span>@enderror</div>
            <div class="ad-field full"><span class="ad-help">Use a same-site path such as /shop, or a full HTTPS URL. Plain HTTP destinations are not accepted.</span></div>
            <div class="ad-field full">
                <label for="image_url">Desktop image *</label>
                <div class="ad-image-row"><input type="text" id="image_url" name="image_url" value="{{ old('image_url', $ad->image_url) }}" required maxlength="2048" placeholder="Choose from Media library or paste an HTTPS image URL"><button type="button" data-ad-image-picker="image_url" data-preview="desktop-ad-preview"><i class="ri-image-add-line"></i> Choose image</button></div>
                <img class="ad-image-preview" id="desktop-ad-preview" src="{{ $ad->imageSource('image_url') }}" alt="Desktop ad preview" @if(!$ad->imageSource('image_url')) hidden @endif>
                @error('image_url')<span class="ad-error">{{ $message }}</span>@enderror
                <span class="ad-help">Choose an image you own from Admin → Media, or use a licensed HTTPS image URL. Media library images stay on your store.</span>
            </div>
            <div class="ad-field full">
                <label for="tablet_image_url">Optional tablet / mobile image</label>
                <div class="ad-image-row"><input type="text" id="tablet_image_url" name="tablet_image_url" value="{{ old('tablet_image_url', $ad->tablet_image_url) }}" maxlength="2048" placeholder="Leave empty to reuse the desktop image"><button type="button" data-ad-image-picker="tablet_image_url" data-preview="tablet-ad-preview"><i class="ri-image-add-line"></i> Choose image</button></div>
                <img class="ad-image-preview" id="tablet-ad-preview" src="{{ $ad->imageSource('tablet_image_url') }}" alt="Tablet and mobile ad preview" @if(!$ad->imageSource('tablet_image_url')) hidden @endif>
                @error('tablet_image_url')<span class="ad-error">{{ $message }}</span>@enderror
            </div>
            <div class="ad-field"><label for="status">Status</label><select id="status" name="status"><option value="draft" @selected(old('status', $ad->status ?: 'draft') === 'draft')>Draft (not public)</option><option value="published" @selected(old('status', $ad->status) === 'published')>Published</option></select></div>
            <div class="ad-field"><label for="sort_order">Display order</label><input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $ad->sort_order ?? 0) }}" min="0" max="100000" required></div>
            <div class="ad-field"><label for="starts_at">Start showing (optional)</label><input type="datetime-local" id="starts_at" name="starts_at" value="{{ old('starts_at', $ad->starts_at?->format('Y-m-d\TH:i')) }}"></div>
            <div class="ad-field"><label for="expires_at">Stop showing (optional)</label><input type="datetime-local" id="expires_at" name="expires_at" value="{{ old('expires_at', $ad->expires_at?->format('Y-m-d\TH:i')) }}">@error('expires_at')<span class="ad-error">{{ $message }}</span>@enderror</div>
            <label class="ad-checkbox ad-field full"><span><input type="checkbox" name="opens_new_tab" value="1" @checked(old('opens_new_tab', $ad->opens_new_tab))> Open the destination in a new tab</span></label>
        </div>
        <button class="ad-submit" type="submit"><i class="ri-save-line"></i>Save ad</button>
    </form>
</main>
@include('admin.include.media-picker')
@push('scripts')
<script>
    (function () {
        document.querySelectorAll('[data-ad-image-picker]').forEach(function (button) {
            button.addEventListener('click', function () {
                if (typeof window.openMediaPicker !== 'function') {
                    window.alert('The Media library is unavailable. Refresh the page and try again.');
                    return;
                }
                window.openMediaPicker(function (image) {
                    var input = document.getElementById(button.dataset.adImagePicker);
                    var preview = document.getElementById(button.dataset.preview);
                    input.value = image.path;
                    preview.src = image.url;
                    preview.hidden = false;
                });
            });
        });
        ['image_url', 'tablet_image_url'].forEach(function (id) {
            var input = document.getElementById(id);
            var preview = document.getElementById(input.id === 'image_url' ? 'desktop-ad-preview' : 'tablet-ad-preview');
            input.addEventListener('input', function () {
                if (/^https:\/\/\S+$/i.test(input.value)) {
                    preview.src = input.value;
                    preview.hidden = false;
                } else {
                    preview.removeAttribute('src');
                    preview.hidden = true;
                }
            });
            preview.addEventListener('error', function () { preview.hidden = true; });
        });
    })();
</script>
@endpush
@endsection
