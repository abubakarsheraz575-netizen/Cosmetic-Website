@extends('admin.layouts.ecommerce')

@section('title', 'Attributes & variants')
@section('content')
    <style>
        @keyframes attribute-enter { from { opacity:0;transform:translateY(12px) } to { opacity:1;transform:translateY(0) } }
        .attributes-hero { position:relative;display:flex;align-items:center;justify-content:space-between;gap:20px;overflow:hidden;padding:28px;border:1px solid #d5e9ef;border-radius:22px;background:radial-gradient(circle at 95% 8%,#dff7f7 0,transparent 34%),linear-gradient(125deg,#fff,#f0f9fc);box-shadow:0 14px 34px rgba(14,90,117,.07);animation:attribute-enter .55s cubic-bezier(.2,.75,.25,1) both }
        .attributes-hero h1 { display:flex;align-items:center;gap:12px;margin:0;color:#123744;font-size:26px;letter-spacing:-.5px }
        .attributes-hero h1 i { display:grid;place-items:center;width:48px;height:48px;border-radius:16px;background:linear-gradient(135deg,#0e5a75,#249ab1);color:#fff;font-size:23px;box-shadow:0 8px 18px #0e5a7529 }
        .attributes-hero p { max-width:670px;margin:8px 0 0;color:#617785;font-size:13px;line-height:1.7 }
        .attribute-create-trigger { display:inline-flex;align-items:center;gap:8px;min-height:42px;margin-top:15px;padding:0 16px;border:0;border-radius:12px;background:linear-gradient(135deg,#0e5a75,#1688a0);color:#fff;font-size:12px;font-weight:800;box-shadow:0 8px 20px rgba(14,90,117,.2);transition:transform .2s,box-shadow .2s }
        .attribute-create-trigger:hover { transform:translateY(-2px);box-shadow:0 12px 25px rgba(14,90,117,.25) }
        .hero-counts { display:flex;gap:9px;flex-wrap:wrap;justify-content:flex-end }
        .hero-count { min-width:96px;padding:12px 15px;border:1px solid #dbe9ee;border-radius:14px;background:#ffffffcc;text-align:center }
        .hero-count strong,.hero-count span { display:block }
        .hero-count strong { color:#0e5a75;font-size:21px }
        .hero-count span { margin-top:2px;color:#738592;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em }
        .attributes-page { width:100%;min-width:0;max-width:100% }
        .attributes-grid { display:grid;grid-template-columns:minmax(0,1fr);gap:18px;align-items:start;min-width:0 }
        .attributes-grid>.card,.attributes-page>.card { animation:attribute-enter .55s cubic-bezier(.2,.75,.25,1) both }
        .attributes-grid>.card { animation-delay:.08s }
        .attributes-page>.card { animation-delay:.16s }
        .attributes-grid>.card { min-width:0;height:100% }
        .card-intro { margin:-7px 0 16px;color:var(--muted);font-size:12px;line-height:1.65 }
        .attributes-type-row { display:grid;grid-template-columns:1fr 1fr;gap:10px }
        .assign-grid { display:grid;grid-template-columns:1fr 1fr;gap:10px }
        .assign-grid .field { min-width:0 }
        .assign-tip { margin:12px 0 5px;color:var(--muted);font-size:11px;line-height:1.6 }
        .option-row { display:grid;grid-template-columns:minmax(135px,1fr) minmax(115px,.55fr) minmax(185px,1.2fr) auto;gap:10px;align-items:end;padding:14px;border:1px solid var(--border);border-radius:14px;background:linear-gradient(135deg,var(--card),var(--bg)) }
        .option-row>label { display:grid;gap:6px;min-width:0;color:var(--muted);font-size:10px;font-weight:800 }
        .option-row input,.option-row textarea { min-width:0;width:100%;padding:10px 11px;border:1px solid var(--border);border-radius:9px;background:var(--card);color:var(--text);font:inherit;font-size:12px }
        .option-row textarea { grid-column:1/-2;min-height:55px;resize:vertical }
        .option-row .option-image-control { display:flex;align-items:center;gap:7px;flex-wrap:wrap }
        .option-row .option-image-control button { flex:none }
        .option-photo-field { display:grid;gap:6px }
        .option-photo-field>.field-label { color:var(--muted);font-size:10px;font-weight:800 }
        .option-photo-preview { display:flex;align-items:center;gap:8px;margin-top:7px }
        .option-photo-preview[hidden] { display:none }
        .option-photo-preview img,.variant-preview img,.variant-preview-fallback { width:42px;height:42px;flex:none;object-fit:cover;border:1px solid var(--border);border-radius:11px;background:var(--bg) }
        .option-photo-preview button { min-height:30px;padding:5px 8px }
        .variant-preview-fallback { display:grid;place-items:center;color:var(--muted);font-size:17px }
        .variant-list { display:flex;gap:8px;flex-wrap:wrap;margin-top:9px }
        .variant-preview { display:inline-flex;align-items:center;gap:8px;min-width:0;padding:6px 10px 6px 6px;border:1px solid var(--border);border-radius:14px;background:var(--card);font-size:11px;font-weight:700 }
        .variant-preview-copy { display:grid;gap:3px }
        .variant-preview small { color:var(--muted);font-size:9px;font-weight:500 }
        .attribute-actions { display:flex;align-items:center;gap:7px;flex-wrap:wrap }
        .attribute-dialog { width:min(500px,calc(100vw - 28px));max-width:calc(100vw - 28px);max-height:min(88vh,760px);overflow:auto;padding:0;border:1px solid var(--border);border-radius:21px;background:var(--card);color:var(--text);box-shadow:0 28px 90px rgba(10,32,50,.28) }
        .attribute-dialog[open] { position:fixed;inset:50% auto auto 50%;margin:0;transform:translate(-50%,-50%);animation:attribute-enter .22s ease-out both }
        .attribute-dialog::backdrop { background:rgba(7,24,33,.58);backdrop-filter:blur(4px) }
        .attribute-dialog-head { display:flex;align-items:flex-start;justify-content:space-between;gap:16px;padding:22px 24px 16px;border-bottom:1px solid var(--border) }
        .attribute-dialog-head h2 { margin:0;font-size:18px;letter-spacing:-.3px }
        .attribute-dialog-head p { margin:5px 0 0;color:var(--muted);font-size:11px;line-height:1.5 }
        .attribute-dialog-close { display:grid;place-items:center;flex:none;width:36px;height:36px;border:1px solid var(--border);border-radius:11px;background:var(--bg);color:var(--muted);font-size:18px;cursor:pointer }
        .attribute-dialog-form { display:grid;gap:14px;padding:20px 24px 24px }
        .attribute-edit-form { display:grid;grid-template-columns:1fr;gap:13px }
        .attribute-edit-form label,.attribute-create-form label { display:grid;gap:6px;color:var(--muted);font-size:11px;font-weight:700 }
        .attribute-edit-form input,.attribute-edit-form select { min-width:0;width:100%;padding:11px 12px;border:1px solid var(--border);border-radius:9px;background:var(--card);color:var(--text);font:inherit;font-size:13px }
        .attribute-create-form input,.attribute-create-form select { min-width:0;width:100%;padding:11px 12px;border:1px solid var(--border);border-radius:10px;background:var(--card);color:var(--text);font:inherit;font-size:13px }
        .attribute-edit-actions { display:flex;justify-content:flex-end;gap:8px;padding-top:15px;border-top:1px solid var(--border) }
        .attribute-edit-actions button { white-space:nowrap }
        .attribute-actions .button,.attribute-edit-actions .button { transition:transform .18s,box-shadow .18s }
        .attribute-actions .button:hover,.attribute-edit-actions .button:hover { transform:translateY(-1px);box-shadow:0 5px 12px rgba(16,24,40,.08) }
        .option-row { transition:transform .2s,border-color .2s,box-shadow .2s;animation:attribute-enter .35s ease-out both }
        .option-row:hover { transform:translateY(-2px);border-color:rgba(62,197,224,.55);box-shadow:0 9px 20px rgba(16,24,40,.06) }
        .attributes-page .table-wrap tbody tr:not(:has(.empty)) { animation:attribute-enter .4s ease-out both }
        .attributes-page .table-wrap tbody tr:nth-child(2) { animation-delay:.04s }.attributes-page .table-wrap tbody tr:nth-child(3) { animation-delay:.08s }.attributes-page .table-wrap tbody tr:nth-child(4) { animation-delay:.12s }
        @media(max-width:920px) { .attributes-grid { grid-template-columns:1fr }.attributes-hero { align-items:flex-start;flex-direction:column }.hero-counts { justify-content:flex-start } }
        @media(max-width:650px) { .assign-grid,.attributes-type-row { grid-template-columns:1fr }.attributes-hero { padding:19px }.attributes-hero h1 { font-size:21px }.hero-count { min-width:82px;padding:10px }.option-row { grid-template-columns:1fr 1fr }.option-row textarea { grid-column:1/-1 }.option-row .option-photo-field { grid-column:1/-1 }.attribute-dialog-head { padding:18px 18px 14px }.attribute-dialog-form { padding:16px 18px 18px }.attribute-edit-actions { flex-direction:column-reverse }.attribute-edit-actions>* { width:100% } }
        @media(prefers-reduced-motion:reduce) { *,*::before,*::after { scroll-behavior:auto!important;animation-duration:.01ms!important;animation-iteration-count:1!important;transition-duration:.01ms!important } }
    </style>
    <div class="attributes-page">
    <section class="attributes-hero">
        <div><h1><i class="ri-palette-line"></i> Attributes &amp; variants</h1><p>Build color, size, and finish options once, assign them to products, then manage each shade photo, swatch color, and description from one place.</p><button class="attribute-create-trigger" type="button" data-open-attribute-create><i class="ri-add-line"></i> Create attribute</button></div>
        <div class="hero-counts" aria-label="Catalog option totals">
            <div class="hero-count"><strong>{{ $attributes->count() }}</strong><span>Attributes</span></div>
            <div class="hero-count"><strong>{{ $attributes->sum(fn ($attribute) => $attribute->variants->count()) }}</strong><span>Option values</span></div>
            <div class="hero-count"><strong>{{ $products->count() }}</strong><span>Products</span></div>
        </div>
    </section>
    <dialog class="attribute-dialog" id="attribute-create-dialog" aria-labelledby="attribute-create-title">
        <div class="attribute-dialog-head"><div><h2 id="attribute-create-title">Create an attribute</h2><p>Add a reusable option group such as color, size, or finish.</p></div><button class="attribute-dialog-close" type="button" data-close-attribute aria-label="Close dialog"><i class="ri-close-line"></i></button></div>
        <form class="attribute-dialog-form attribute-create-form" method="POST" action="{{ route('admin.ecommerce.attributes.store') }}">
            @csrf
            <label for="attribute-create-name">Attribute name<input id="attribute-create-name" required name="name" maxlength="120" value="{{ old('name') }}" placeholder="Color"></label>
            <label for="attribute-create-type">Type<select id="attribute-create-type" name="type" required><option value="color" @selected(old('type', 'color') === 'color')>Color</option><option value="text" @selected(old('type') === 'text')>Text</option><option value="size" @selected(old('type') === 'size')>Size</option></select></label>
            <div class="attribute-edit-actions"><button class="button" type="button" data-close-attribute>Cancel</button><button class="button primary" type="submit"><i class="ri-sparkling-2-line"></i> Create attribute</button></div>
        </form>
    </dialog>
    <section class="attributes-grid">
        <form class="card" method="POST" action="{{ route('admin.ecommerce.attributes.variants') }}" enctype="multipart/form-data" id="variantForm">
            @csrf
            <h2 class="card-title"><i class="ri-shapes-line"></i> Assign product options</h2>
            <p class="card-intro">Choose a product and attribute to edit its options. Each option can have a storefront color, photo, and description.</p>
            <div class="assign-grid">
                <label class="field"><span>Product</span><select name="product_id" id="productSelect" required>
                    <option value="">Select a product</option>
                    @foreach($products as $product)<option value="{{ $product->id }}" @selected((string) old('product_id', request('product_id')) === (string) $product->id)>{{ $product->name }} · {{ $product->category?->name ?? 'Uncategorized' }} · {{ $product->stock }} in stock</option>@endforeach
                </select></label>
                <label class="field"><span>Attribute</span><select name="product_attribute_id" id="attributeSelect" required>
                    <option value="">Select an attribute</option>
                    @foreach($attributes as $attribute)<option value="{{ $attribute->id }}" @selected((string) old('product_attribute_id', request('attribute_id')) === (string) $attribute->id)>{{ $attribute->name }} · {{ $attribute->type }}</option>@endforeach
                </select></label>
            </div>
            @if($attributes->isEmpty())
                <div class="alert" role="status" style="margin-top:14px">Create an attribute in the panel beside this form before adding product options.</div>
            @else
                <p class="assign-tip">Saving replaces this product's values for this attribute. Keep the existing photos, select a new library image, or upload a different one.</p>
            @endif
            <div class="items-editor" id="optionsEditor">
                @php
                    $selectedProductId = old('product_id', request('product_id'));
                    $selectedAttributeId = old('product_attribute_id', request('attribute_id'));
                    $currentOptions = $products->firstWhere('id', (int) $selectedProductId)?->variants
                        ->where('product_attribute_id', (int) $selectedAttributeId) ?? collect();
                @endphp
                @foreach($currentOptions as $index => $variant)
                    <div class="item-edit option-row">
                        <input type="hidden" name="options[{{ $index }}][id]" value="{{ $variant->id }}">
                        <label>Variant name / shade<input required name="options[{{ $index }}][value]" value="{{ old("options.{$index}.value", $variant->value) }}" maxlength="120"></label>
                        <label>Color hex<input name="options[{{ $index }}][color_hex]" value="{{ old("options.{$index}.color_hex", $variant->color_hex) }}" placeholder="#AABBCC" pattern="#[0-9a-fA-F]{6}"></label>
                        <div class="option-photo-field"><span class="field-label">Variant photo</span>
                            <input type="hidden" class="option-image-path" name="options[{{ $index }}][image_path]" value="">
                            <span class="option-image-control"><input class="option-image-upload" type="file" name="options[{{ $index }}][image]" accept="image/jpeg,image/png,image/webp"><button class="button choose-option-image" type="button"><i class="ri-image-add-line"></i> Media library</button></span>
                            @if($variant->image_path)<span class="option-photo-preview"><img src="{{ asset('storage/'.$variant->image_path) }}" alt="{{ $variant->value }} option"><small>Current photo</small><label><input class="remove-option-image" type="checkbox" name="options[{{ $index }}][remove_image]" value="1"> Remove</label></span>@else<span class="option-photo-preview" hidden><img alt=""><small>Selected photo</small><label><input class="remove-option-image" type="checkbox" name="options[{{ $index }}][remove_image]" value="1"> Remove</label></span>@endif
                        </div>
                        <label>Description<textarea name="options[{{ $index }}][description]" maxlength="500" rows="2" placeholder="Shade, finish, or option details">{{ old("options.{$index}.description", $variant->description) }}</textarea></label>
                        <button class="button danger remove-option" type="button">Remove option</button>
                    </div>
                @endforeach
            </div>
            <button class="button" type="button" id="addOption"><i class="ri-add-line"></i> Add option</button>
            <div class="actions" style="margin-top:15px"><button class="button primary" type="submit" @disabled($attributes->isEmpty())>Save product options</button></div>
        </form>
    </section>
    <section class="card">
        <h2 class="card-title"><i class="ri-list-check-3"></i> Attribute library</h2>
        <div class="table-wrap"><table><thead><tr><th>Name</th><th>Type</th><th>Option values</th><th>Products assigned</th><th>Actions</th></tr></thead><tbody>
            @forelse($attributes as $attribute)<tr>
                <td><strong>{{ $attribute->name }}</strong></td>
                <td><span class="badge">{{ ucfirst($attribute->type) }}</span></td>
                <td>{{ $attribute->variants->count() }}</td>
                <td>{{ $attribute->variants->pluck('product_id')->unique()->count() }}</td>
                <td><div class="attribute-actions">
                    <button class="button" type="button" data-open-attribute="{{ $attribute->id }}" aria-label="Edit {{ $attribute->name }}"><i class="ri-pencil-line"></i> Edit</button>
                    <form method="POST" action="{{ route('admin.ecommerce.attributes.destroy', $attribute) }}" data-confirm-modal data-confirm-title="Delete this attribute?" data-confirm-message="Its assigned product options will also be removed.">
                        @csrf @method('DELETE')
                        <button class="button danger" type="submit" title="Delete attribute and assigned options"><i class="ri-delete-bin-line"></i> Delete</button>
                    </form>
                </div></td>
            </tr>
            @empty<tr><td colspan="5" class="empty">Create an attribute to begin assigning options.</td></tr>@endforelse
        </tbody></table></div>
    </section>
    @foreach($attributes as $attribute)
        <dialog class="attribute-dialog" id="attribute-edit-{{ $attribute->id }}" aria-labelledby="attribute-edit-title-{{ $attribute->id }}">
            <div class="attribute-dialog-head"><div><h2 id="attribute-edit-title-{{ $attribute->id }}">Edit {{ $attribute->name }}</h2><p>Update the reusable name and option type used across products.</p></div><button class="attribute-dialog-close" type="button" data-close-attribute aria-label="Close dialog"><i class="ri-close-line"></i></button></div>
            <form class="attribute-dialog-form attribute-edit-form" method="POST" action="{{ route('admin.ecommerce.attributes.update', $attribute) }}">
                @csrf @method('PATCH')
                <label for="attribute-name-{{ $attribute->id }}">Attribute name<input id="attribute-name-{{ $attribute->id }}" name="name" value="{{ $attribute->name }}" maxlength="120" required></label>
                <label for="attribute-type-{{ $attribute->id }}">Type<select id="attribute-type-{{ $attribute->id }}" name="type" required>
                    <option value="color" @selected($attribute->type === 'color')>Color</option>
                    <option value="text" @selected($attribute->type === 'text')>Text</option>
                    <option value="size" @selected($attribute->type === 'size')>Size</option>
                </select></label>
                <div class="attribute-edit-actions"><button class="button" type="button" data-close-attribute>Cancel</button><button class="button primary" type="submit"><i class="ri-save-line"></i> Save changes</button></div>
            </form>
        </dialog>
    @endforeach
    <section class="card">
        <div class="toolbar"><h2 class="card-title" style="margin:0"><i class="ri-shopping-bag-3-line"></i> Product variant assignments</h2></div>
        <div class="table-wrap"><table>
            <thead><tr><th>Product</th><th>Attributes and options</th><th></th></tr></thead>
            <tbody>
                @forelse($products as $product)
                    @php($productVariants = $product->variants->groupBy('product_attribute_id'))
                    <tr>
                        <td><div class="product-cell"><span class="product-thumb">@if($product->images->first())<img src="{{ asset('storage/'.$product->images->first()->path) }}" alt="{{ $product->name }}">@else{{ $product->emoji ?: '✦' }}@endif</span><span><strong>{{ $product->name }}</strong><small>{{ $product->category?->name ?? 'Uncategorized' }}</small></span></div></td>
                        <td>
                            @forelse($productVariants as $variants)
                                <span class="badge">{{ $variants->first()->attribute?->name ?? 'Attribute' }}</span>
                                <div class="variant-list">@foreach($variants as $variant)
                                    <span class="variant-preview">
                                        @if($variant->image_path)<img src="{{ asset('storage/'.$variant->image_path) }}" alt="{{ $variant->value }} option" loading="lazy">@else<span class="variant-preview-fallback"><i class="ri-image-line" aria-hidden="true"></i></span>@endif
                                        @if($variant->color_hex)<span class="variant-color-dot" style="display:block;width:14px;height:14px;border-radius:50%;background:{{ $variant->color_hex }};border:1px solid #94a3b8" aria-label="{{ $variant->color_hex }}"></span>@endif
                                        <span class="variant-preview-copy">
                                            {{ $variant->value }}
                                            @if($variant->description)
                                                <small>{{ $variant->description }}</small>
                                            @endif
                                            @if($variant->color_hex)
                                                <small>{{ $variant->color_hex }}</small>
                                            @endif
                                        </span>
                                    </span>
                                @endforeach</div>
                            @empty
                                <span class="dt-info">No options assigned yet</span>
                            @endforelse
                        </td>
                        <td>
                            @if($attributes->isNotEmpty())
                                <a class="button" href="{{ route('admin.ecommerce.attributes', ['product_id' => $product->id, 'attribute_id' => request('attribute_id', $attributes->first()->id)]) }}">Configure options</a>
                            @else
                                <span class="dt-info">Create an attribute first</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="empty">No products are available for variant configuration.</td></tr>
                @endforelse
            </tbody>
        </table></div>
    </section>
    <template id="optionTemplate"><div class="item-edit option-row">
        <label>Variant name / shade<input required name="__VALUE__" maxlength="120" placeholder="e.g. Rosewood"></label>
        <label>Color hex<input name="__COLOR__" placeholder="#AABBCC" pattern="#[0-9a-fA-F]{6}"></label>
        <div class="option-photo-field"><span class="field-label">Variant photo</span>
            <input type="hidden" class="option-image-path" name="__PATH__">
            <span class="option-image-control"><input class="option-image-upload" type="file" name="__IMAGE__" accept="image/jpeg,image/png,image/webp"><button class="button choose-option-image" type="button"><i class="ri-image-add-line"></i> Media library</button></span>
            <span class="option-photo-preview" hidden><img alt=""><small>Selected photo</small><label><input class="remove-option-image" type="checkbox" name="__REMOVE__" value="1"> Remove</label></span>
        </div>
        <label>Description<textarea name="__DESCRIPTION__" maxlength="500" rows="2" placeholder="Shade, finish, or option details"></textarea></label>
        <button class="button danger remove-option" type="button">Remove option</button>
    </div></template>
    </div>
    @push('scripts')
    <script>
        (function () {
            var editor = document.getElementById('optionsEditor');
            var template = document.getElementById('optionTemplate');
            var createDialog = document.getElementById('attribute-create-dialog');
            var index = editor.querySelectorAll('.option-row').length;
            document.getElementById('addOption').addEventListener('click', function () {
                var html = template.innerHTML.replaceAll('__VALUE__', 'options[' + index + '][value]')
                    .replaceAll('__COLOR__', 'options[' + index + '][color_hex]')
                    .replaceAll('__IMAGE__', 'options[' + index + '][image]')
                    .replaceAll('__PATH__', 'options[' + index + '][image_path]')
                    .replaceAll('__DESCRIPTION__', 'options[' + index + '][description]')
                    .replaceAll('__REMOVE__', 'options[' + index + '][remove_image]');
                editor.insertAdjacentHTML('beforeend', html);
                index++;
            });
            editor.addEventListener('click', function (event) {
                if (event.target.closest('.remove-option')) event.target.closest('.option-row').remove();
                var pickButton = event.target.closest('.choose-option-image');
                if (pickButton) {
                    var row = pickButton.closest('.option-row');
                    if (typeof window.openMediaPicker !== 'function') {
                        window.alert('The Media library is unavailable. Refresh the page and try again.');
                        return;
                    }
                    window.openMediaPicker(function (image) {
                        var preview = row.querySelector('.option-photo-preview');
                        var input = row.querySelector('.option-image-path');
                        var upload = row.querySelector('.option-image-upload');
                        var remove = row.querySelector('.remove-option-image');
                        input.value = image.path;
                        upload.value = '';
                        if (remove) remove.checked = false;
                        preview.querySelector('img').src = image.url;
                        preview.querySelector('small').textContent = image.name;
                        preview.hidden = false;
                    });
                    return;
                }
                if (event.target.closest('.remove-option-image')) {
                    var optionRow = event.target.closest('.option-row');
                    optionRow.querySelector('.option-image-path').value = '';
                    optionRow.querySelector('.option-image-upload').value = '';
                    optionRow.querySelector('.option-photo-preview img').removeAttribute('src');
                }
            });
            editor.addEventListener('change', function (event) {
                var upload = event.target.closest('.option-image-upload');
                if (!upload || !upload.files.length) return;
                var row = upload.closest('.option-row');
                row.querySelector('.option-image-path').value = '';
                var remove = row.querySelector('.remove-option-image');
                if (remove) remove.checked = false;
                var preview = row.querySelector('.option-photo-preview');
                preview.querySelector('img').src = URL.createObjectURL(upload.files[0]);
                preview.querySelector('small').textContent = upload.files[0].name;
                preview.hidden = false;
            });
            document.addEventListener('click', function (event) {
                var createButton = event.target.closest('[data-open-attribute-create]');
                if (createButton && createDialog) { createDialog.showModal(); return; }
                var openButton = event.target.closest('[data-open-attribute]');
                if (openButton) { var editDialog = document.getElementById('attribute-edit-' + openButton.dataset.openAttribute); if (editDialog) editDialog.showModal(); return; }
                if (event.target.closest('[data-close-attribute]')) event.target.closest('dialog').close();
                if (event.target instanceof HTMLDialogElement && event.target.classList.contains('attribute-dialog')) event.target.close();
            });
            ['productSelect', 'attributeSelect'].forEach(function (id) {
                document.getElementById(id).addEventListener('change', function () {
                    var form = document.getElementById('variantForm');
                    var url = new URL(window.location.href);
                    url.searchParams.set('product_id', form.elements.product_id.value);
                    url.searchParams.set('attribute_id', form.elements.product_attribute_id.value);
                    window.location.assign(url.toString());
                });
            });
        })();
    </script>
    @endpush
@endsection
