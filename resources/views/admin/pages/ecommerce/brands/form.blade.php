@extends('admin.layouts.ecommerce')

@php($isEditing = (bool) $brand)
@section('title', $isEditing ? 'Edit '.$brand->name : 'Add brand')
@section('content')
    @php($selectedProducts = old('products', $brand?->products->pluck('id')->all() ?? []))
    <section class="page-head">
        <div>
            <h1><i class="ri-price-tag-3-line"></i> {{ $isEditing ? 'Edit brand' : 'Add brand' }}</h1>
            <p>Set brand details and choose which products belong to it.</p>
        </div>
        <a class="button" href="{{ route('admin.ecommerce.brands') }}"><i class="ri-arrow-left-line"></i> All brands</a>
    </section>

    <form class="card" method="post" enctype="multipart/form-data" action="{{ $isEditing ? route('admin.ecommerce.brands.update', $brand) : route('admin.ecommerce.brands.store') }}">
        @csrf
        @if($isEditing) @method('PUT') @endif
        <div class="form-grid">
            <div class="field">
                <label for="brand-name">Brand name</label>
                <input id="brand-name" name="name" value="{{ old('name', $brand?->name) }}" maxlength="120" required>
            </div>
            <div class="field">
                <label for="brand-website">Website URL</label>
                <input id="brand-website" type="url" name="website_url" value="{{ old('website_url', $brand?->website_url) }}" maxlength="255" placeholder="https://example.com">
            </div>
            <div class="field full">
                <label for="brand-description">Description</label>
                <textarea id="brand-description" name="description" maxlength="3000">{{ old('description', $brand?->description) }}</textarea>
            </div>
            <div class="field">
                <label for="choose-brand-logo">Logo or brand image</label>
                <input type="hidden" name="logo_path" id="brand-logo-path" value="{{ old('logo_path') }}">
                <button class="button" type="button" id="choose-brand-logo">Choose from Media library</button>
                <small>Upload images from Admin → Media, then select them here.</small>
                <div class="brand-image-preview-wrap">
                    <img id="brand-logo-preview" class="brand-image-preview" src="{{ $brand?->logo_path ? asset('storage/'.$brand->logo_path) : '' }}" alt="{{ $brand?->name ?: 'Selected brand image' }} preview" @if(!$brand?->logo_path) hidden @endif>
                    <span id="brand-logo-placeholder" class="brand-image-placeholder" @if($brand?->logo_path) hidden @endif><i class="ri-image-add-line"></i><span>Choose an image to preview</span></span>
                    @if($brand?->logo_path)<label><input type="checkbox" name="remove_logo" value="1"> Remove current image</label>@endif
                </div>
            </div>
            <div class="field">
                <label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $brand?->is_active ?? true))> Active brand</label>
            </div>
            <div class="field full">
                <label for="brand-products">Associated products</label>
                @if($products->isEmpty())
                    <p class="empty">Create products before associating them with a brand.</p>
                @else
                    <div class="items-editor">
                        @foreach($products as $product)
                            <label class="item-edit" for="product-{{ $product->id }}">
                                <span style="display:flex;align-items:center;gap:10px;color:var(--text)">
                                    @if($product->images->first())
                                        <img src="{{ asset('storage/'.$product->images->first()->path) }}" alt="" style="width:42px;height:42px;object-fit:cover;border-radius:8px">
                                    @else
                                        <i class="ri-box-3-line"></i>
                                    @endif
                                    <span><strong>{{ $product->name }}</strong><small>Stock: {{ $product->stock }}</small></span>
                                </span>
                                <span>Associate <input id="product-{{ $product->id }}" type="checkbox" name="products[]" value="{{ $product->id }}" @checked(in_array($product->id, $selectedProducts))></span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        <div class="actions" style="margin-top:18px">
            <button class="button primary" type="submit">{{ $isEditing ? 'Save changes' : 'Create brand' }}</button>
            @if($isEditing)
                <button class="button danger" type="submit" form="delete-brand-form">Delete brand</button>
            @endif
        </div>
    </form>

    @if($isEditing)
        <form id="delete-brand-form" method="post" action="{{ route('admin.ecommerce.brands.destroy', $brand) }}" data-confirm-modal data-confirm-title="Delete this brand?" data-confirm-message="Products will remain in the catalog without their brand association.">
            @csrf @method('DELETE')
        </form>
    @endif
@endsection

@push('head')
<style>
    .brand-image-preview-wrap{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-top:10px}.brand-image-preview{width:96px;height:96px;object-fit:contain;padding:5px;border:1px solid var(--border);border-radius:12px;background:var(--card)}.brand-image-placeholder{display:flex;width:96px;height:96px;flex-direction:column;align-items:center;justify-content:center;gap:6px;border:1px dashed var(--border);border-radius:12px;background:var(--bg);color:var(--muted);text-align:center;font-size:9px}.brand-image-placeholder[hidden]{display:none}.brand-image-placeholder i{font-size:22px;color:var(--brand)}.brand-image-preview-wrap label{display:flex;align-items:center;gap:7px;color:var(--muted);font-size:11px}
</style>
@endpush
@push('scripts')
<script>
    (function () {
        var pathInput = document.getElementById('brand-logo-path');
        document.getElementById('choose-brand-logo').addEventListener('click', function () {
            if (typeof window.openMediaPicker !== 'function') {
                window.alert('The Media library is unavailable. Refresh the page and try again.');
                return;
            }
            window.openMediaPicker(function (image) {
                pathInput.value = image.path;
                var preview = document.getElementById('brand-logo-preview');
                var placeholder = document.getElementById('brand-logo-placeholder');
                preview.src = image.url;
                preview.alt = image.name + ' preview';
                preview.hidden = false;
                placeholder.hidden = true;
            });
        });
    })();
</script>
@endpush
