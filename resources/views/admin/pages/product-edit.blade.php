<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=3">
    <link rel="icon" href="{{ asset('favicon.ico') }}?v=3" sizes="any">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Edit {{ $product->name }} — NiceDash Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f3f5f7;color:#182b35;font:14px 'Plus Jakarta Sans',sans-serif}
        .main{margin-left:268px!important}.layout.collapsed .main{margin-left:84px!important}.content{max-width:1450px;margin:auto;padding:24px;display:grid;gap:18px}
        .head,.panel{padding:22px;border:1px solid #e4ebef;border-radius:16px;background:#fff;box-shadow:0 8px 26px #1c3a4d0b}
        .head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}.head h1{margin:0;font-size:24px}.head p{margin:6px 0 0;color:#74838c;font-size:12px}
        .button{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:40px;padding:0 14px;border:1px solid #dce5ea;border-radius:10px;background:#fff;color:#28536a;font:700 12px inherit;text-decoration:none;cursor:pointer}.button.primary{border-color:#176d8e;background:#176d8e;color:#fff}
        .columns{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(310px,.85fr);gap:18px;align-items:start}
        .panel h2{margin:0 0 6px;font-size:17px}.panel>p{margin:0 0 17px;color:#74838c;font-size:12px;line-height:1.5}
        .fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:13px}.field{display:grid;gap:6px;min-width:0}.field.full{grid-column:1/-1}.field label{font-size:11px;font-weight:800;color:#425864}
        input,select,textarea{width:100%;min-height:41px;padding:9px 11px;border:1px solid #dce5ea;border-radius:9px;background:#fff;color:#1b3440;font:inherit;font-size:12px}textarea{min-height:86px;resize:vertical}
        .switch{display:flex;align-items:center;gap:9px}.switch input{width:17px;min-height:17px}.subnote{color:#7a8992;font-size:10px;line-height:1.45}
        .actions{display:flex;justify-content:flex-end;gap:8px;margin-top:16px}.notice{display:none;padding:11px 13px;margin-bottom:12px;border-radius:9px;background:#e7f7ee;color:#176d47;font-size:12px}.notice.error{background:#fff0f0;color:#a72b2b}.notice.show{display:block}
        .cover-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(125px,1fr));gap:11px}.cover-choice{position:relative;display:grid;gap:7px;padding:7px;border:1px solid #e0e8ec;border-radius:12px;background:#f8fafb;cursor:pointer}.cover-choice:has(input[type=radio]:checked){border-color:#147a54;box-shadow:0 0 0 2px #147a5424}.cover-choice img{width:100%;aspect-ratio:1;object-fit:cover;border-radius:8px}.cover-choice span{display:flex;align-items:center;gap:6px;font-size:10px;font-weight:800}.cover-choice input{width:15px;min-height:15px;accent-color:#147a54}.cover-choice .remove-photo{accent-color:#ad3f43}
        .new-cover{margin-top:16px;padding:14px;border:1px dashed #b9cbd3;border-radius:12px;background:#f8fafb}.new-cover label{display:block;margin-bottom:8px;font-size:11px;font-weight:800}.gallery-preview{margin-top:12px}.gallery-count{margin-top:10px;font-size:11px;font-weight:800;color:#176d47}
        @media(max-width:900px){.columns{grid-template-columns:1fr}}@media(max-width:992px){.main{margin-left:0!important}}@media(max-width:600px){.content{padding:14px}.head,.panel{padding:16px}.fields{grid-template-columns:1fr}.field.full{grid-column:auto}.cover-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.actions .button{flex:1}}
    </style>
    @include('admin.include.shell-layout')
</head>
<body>
    <div class="layout">
        @include('admin.include.sidebar')
        <div class="main">
            @include('admin.include.navbar')
            <main class="content">
                <header class="head">
                    <div><h1>Edit product</h1><p>{{ $product->name }} · #{{ $product->slug }}</p></div>
                    <a class="button" href="{{ route('admin.products.index') }}"><i class="ri-arrow-left-line"></i>Back to products</a>
                </header>
                <div id="editNotice" class="notice" role="status"></div>
                <div class="columns">
                    <section class="panel">
                        <h2>Product information</h2>
                        <p>Edit the product details shown in your store. Product fallback icons are not managed here.</p>
                        <form id="productEditForm" action="{{ route('admin.products.update', $product) }}">
                            @csrf
                            <div class="fields">
                                <div class="field full"><label for="editName">Product name</label><input id="editName" name="name" maxlength="150" required value="{{ $product->name }}"></div>
                                <div class="field"><label for="editCategory">Category</label><select id="editCategory" name="category_id" required>@foreach($categories as $category)<option value="{{ $category->id }}" @selected($product->category_id === $category->id)>{{ $category->name }}</option>@endforeach</select></div>
                                <div class="field"><label for="editBrand">Brand</label><select id="editBrand" name="brand_id"><option value="">No brand</option>@foreach($brands as $brand)<option value="{{ $brand->id }}" @selected($product->brand_id === $brand->id)>{{ $brand->name }}</option>@endforeach</select></div>
                                <div class="field"><label for="editSize">Size / volume</label><select id="editSize" name="size_id"><option value="">Not specified</option>@foreach($sizes as $size)<option value="{{ $size->id }}" @selected($product->size_id === $size->id)>{{ $size->name }} ({{ number_format($size->ml) }} ml)</option>@endforeach</select></div>
                                <div class="field"><label for="editPrice">Regular price (£)</label><input id="editPrice" type="number" name="price" min="0" step="0.01" required value="{{ $product->price }}"></div>
                                <div class="field"><label for="editDiscountType">Discount type</label><select id="editDiscountType" name="discount_type"><option value="percent" @selected($product->discount_type !== 'fixed')>Percentage</option><option value="fixed" @selected($product->discount_type === 'fixed')>Fixed amount (£)</option></select></div>
                                <div class="field"><label for="editDiscount">Discount</label><input id="editDiscount" type="number" name="discount" min="0" step="0.01" value="{{ $product->discount }}"></div>
                                <div class="field"><label>Stock</label><input type="number" name="stock" min="0" value="{{ $product->stock }}" @readonly($product->skuVariants->isNotEmpty())><span class="subnote">Variant stock is maintained in Ecommerce → Inventory.</span></div>
                                <div class="field full"><label>Shipping package</label><span class="subnote">Weight is used to calculate any extra shipping charge at checkout. Product dimensions are saved for package reference.</span></div>
                                <div class="field"><label for="editShippingWeight">Weight</label><input id="editShippingWeight" type="number" name="shipping_weight" min="0" max="1000000" step="0.001" value="{{ $product->shipping_weight }}"></div>
                                <div class="field"><label for="editWeightUnit">Weight unit</label><select id="editWeightUnit" name="weight_unit">@foreach(['mg', 'g', 'kg', 'oz', 'lb'] as $unit)<option value="{{ $unit }}" @selected($product->weight_unit === $unit)>{{ $unit }}</option>@endforeach</select></div>
                                <div class="field"><label for="editShippingLength">Length</label><input id="editShippingLength" type="number" name="shipping_length" min="0" max="1000000" step="0.01" value="{{ $product->shipping_length }}"></div>
                                <div class="field"><label for="editShippingWidth">Width</label><input id="editShippingWidth" type="number" name="shipping_width" min="0" max="1000000" step="0.01" value="{{ $product->shipping_width }}"></div>
                                <div class="field"><label for="editShippingHeight">Height</label><input id="editShippingHeight" type="number" name="shipping_height" min="0" max="1000000" step="0.01" value="{{ $product->shipping_height }}"></div>
                                <div class="field"><label for="editDimensionUnit">Dimension unit</label><select id="editDimensionUnit" name="dimension_unit">@foreach(['mm', 'cm', 'm', 'in'] as $unit)<option value="{{ $unit }}" @selected($product->dimension_unit === $unit)>{{ $unit }}</option>@endforeach</select></div>
                                <div class="field full"><label for="editDescription">Description</label><textarea id="editDescription" name="description" maxlength="1000">{{ $product->description }}</textarea></div>
                                <label class="switch field full"><input type="checkbox" name="is_active" value="1" @checked($product->is_active)>Published on the storefront</label>
                            </div>
                            <div class="actions"><button class="button primary" type="submit"><i class="ri-save-line"></i>Save product</button></div>
                        </form>
                    </section>
                    <section class="panel">
                        <h2>Product photo gallery</h2>
                        <p>Add or remove photos for this product, then choose one as the storefront photo. Up to six photos total are allowed.</p>
                        <form id="galleryForm" action="{{ route('admin.products.images.update', $product) }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="cover_new_image_index" id="coverNewImageIndex" value="">
                            <div id="existingGallery" class="cover-grid">
                            @forelse($product->images as $image)
                                <label class="cover-choice">
                                    <img src="{{ asset('storage/'.$image->path) }}" alt="Product photo {{ $loop->iteration }}">
                                    <span><input type="radio" name="cover_image_id" value="{{ $image->id }}" @checked($image->is_cover)>Storefront photo</span>
                                    <span><input class="remove-photo" type="checkbox" name="remove_image_ids[]" value="{{ $image->id }}">Remove photo</span>
                                </label>
                            @empty
                                <p class="subnote">This product has no photos yet. Add the first one below.</p>
                            @endforelse
                            </div>
                            <div id="galleryCount" class="gallery-count">{{ $product->images->count() }} / 6 photos</div>
                            <div class="new-cover">
                                <label>Add photos</label>
                                <button class="button" type="button" id="chooseMediaGallery"><i class="ri-folder-image-line"></i>Choose from Media library</button>
                                <div class="field" style="margin-top:12px">
                                    <label for="galleryUploads">Upload photos</label>
                                    <input id="galleryUploads" type="file" name="gallery_uploads[]" accept="image/jpeg,image/png,image/webp" multiple>
                                </div>
                                <span class="subnote">JPG, PNG, or WebP · max 2 MB each. You can add several photos at once.</span>
                                <div id="galleryPreview" class="cover-grid gallery-preview"></div>
                            </div>
                            <div class="actions"><button class="button primary" type="submit"><i class="ri-image-edit-line"></i>Save photo gallery</button></div>
                        </form>
                    </section>
                </div>
            </main>
        </div>
    </div>
    <script>
        (function () {
            var token = document.querySelector('meta[name="csrf-token"]').content;
            var notice = document.getElementById('editNotice');
            function showNotice(message, error) {
                notice.textContent = message;
                notice.classList.toggle('error', !!error);
                notice.classList.add('show');
                notice.scrollIntoView({behavior:'smooth', block:'nearest'});
            }
            function submitForm(form, url) {
                var data = new FormData(form);
                data.set('_token', token);
                data.set('_method', 'PATCH');
                if (form.id === 'productEditForm') data.set('is_active', form.querySelector('[name="is_active"]').checked ? '1' : '0');
                fetch(url, {method:'POST', body:data, headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}})
                    .then(function (response) {
                        return response.json().then(function (payload) {
                            if (!response.ok) {
                                var errors = payload.errors || {};
                                var first = Object.keys(errors)[0];
                                throw new Error(first ? errors[first][0] : (payload.message || 'Could not save changes.'));
                            }
                            return payload;
                        });
                    })
                    .then(function (payload) {
                        showNotice(payload.message || 'Changes saved.', false);
                        if (form.id === 'galleryForm') window.setTimeout(function () { window.location.reload(); }, 600);
                    })
                    .catch(function (error) { showNotice(error.message, true); });
            }
            document.getElementById('productEditForm').addEventListener('submit', function (event) {
                event.preventDefault();
                submitForm(this, this.action);
            });
            document.getElementById('galleryForm').addEventListener('submit', function (event) {
                event.preventDefault();
                if (selectedMedia.length + galleryUploads.files.length > remainingSlots()) {
                    showNotice('Remove an existing photo first; products can have up to six photos.', true);
                    return;
                }
                submitForm(this, this.action);
            });
            var galleryForm = document.getElementById('galleryForm');
            var galleryPreview = document.getElementById('galleryPreview');
            var galleryUploads = document.getElementById('galleryUploads');
            var selectedMedia = [];
            function remainingSlots() {
                var current = galleryForm.querySelectorAll('#existingGallery .cover-choice').length;
                var removing = galleryForm.querySelectorAll('#existingGallery .remove-photo:checked').length;
                return Math.max(0, 6 - current + removing);
            }
            function updateGalleryCount() {
                var current = galleryForm.querySelectorAll('#existingGallery .cover-choice').length;
                var removing = galleryForm.querySelectorAll('#existingGallery .remove-photo:checked').length;
                var incoming = selectedMedia.length + galleryUploads.files.length;
                document.getElementById('galleryCount').textContent = (current - removing + incoming) + ' / 6 photos';
            }
            function renderGalleryPreview() {
                galleryPreview.replaceChildren();
                var files = Array.from(galleryUploads.files);
                files.forEach(function (file, index) {
                    var card = document.createElement('label');
                    card.className = 'cover-choice';
                    var image = document.createElement('img');
                    image.src = URL.createObjectURL(file);
                    image.alt = file.name;
                    var choice = document.createElement('span');
                    choice.innerHTML = '<input type="radio" name="newCoverChoice" value="' + index + '">Storefront photo';
                    var input = choice.querySelector('input');
                    input.addEventListener('change', function () {
                        galleryForm.querySelectorAll('[name="cover_image_id"]').forEach(function (radio) { radio.checked = false; });
                        document.getElementById('coverNewImageIndex').value = index;
                    });
                    var caption = document.createElement('span');
                    caption.textContent = file.name;
                    card.append(image, choice, caption);
                    galleryPreview.appendChild(card);
                });
                selectedMedia.forEach(function (item, mediaIndex) {
                    var newIndex = files.length + mediaIndex;
                    var hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'image_paths[]';
                    hidden.value = item.path;
                    galleryPreview.appendChild(hidden);
                    var card = document.createElement('label');
                    card.className = 'cover-choice';
                    var image = document.createElement('img');
                    image.src = item.url;
                    image.alt = item.name;
                    var choice = document.createElement('span');
                    choice.innerHTML = '<input type="radio" name="newCoverChoice" value="' + newIndex + '">Storefront photo';
                    choice.querySelector('input').addEventListener('change', function () {
                        galleryForm.querySelectorAll('[name="cover_image_id"]').forEach(function (radio) { radio.checked = false; });
                        document.getElementById('coverNewImageIndex').value = newIndex;
                    });
                    var caption = document.createElement('span');
                    caption.textContent = item.name;
                    card.append(image, choice, caption);
                    galleryPreview.appendChild(card);
                });
                updateGalleryCount();
            }
            document.getElementById('chooseMediaGallery').addEventListener('click', function () {
                if (typeof window.openMediaPicker !== 'function') {
                    showNotice('The Media library picker is not available on this page. Refresh and try again.', true);
                    return;
                }
                window.openMediaPicker(function (image) {
                    if (selectedMedia.some(function (item) { return item.path === image.path; })) {
                        showNotice('That photo is already selected.', true);
                        return;
                    }
                    if (selectedMedia.length + galleryUploads.files.length >= remainingSlots()) {
                        showNotice('Remove an existing photo first; products can have up to six photos.', true);
                        return;
                    }
                    selectedMedia.push(image);
                    renderGalleryPreview();
                });
            });
            galleryForm.querySelectorAll('[name="cover_image_id"]').forEach(function (input) {
                input.addEventListener('change', function () {
                    document.getElementById('coverNewImageIndex').value = '';
                    galleryPreview.querySelectorAll('[name="newCoverChoice"]').forEach(function (radio) { radio.checked = false; });
                });
            });
            galleryForm.querySelectorAll('.remove-photo').forEach(function (input) {
                input.addEventListener('change', function () {
                    var cover = galleryForm.querySelector('[name="cover_image_id"]:checked');
                    if (cover && cover.closest('.cover-choice').querySelector('.remove-photo').checked) {
                        cover.checked = false;
                    }
                    updateGalleryCount();
                });
            });
            galleryUploads.addEventListener('change', function () {
                document.getElementById('coverNewImageIndex').value = '';
                if (this.files.length + selectedMedia.length > remainingSlots()) {
                    showNotice('Remove an existing photo first; products can have up to six photos.', true);
                    this.value = '';
                }
                renderGalleryPreview();
            });
        })();
    </script>
    @include('admin.include.media-picker')
</body>
</html>
