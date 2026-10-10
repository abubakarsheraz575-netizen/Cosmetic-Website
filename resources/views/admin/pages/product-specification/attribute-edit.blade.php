@extends('admin.layouts.ecommerce')

@section('title', 'Edit specification attribute')
@section('content')
    <section class="page-head"><div><h1><i class="ri-edit-line"></i> Edit specification attribute</h1><p>Update the definition for {{ $attribute->name }}.</p></div><a class="button" href="{{ route('admin.product-specification.attributes', ['category_id' => $attribute->category_id]) }}">Back to attributes</a></section>
    @if($errors->any()) <div class="alert" role="alert" style="background:#ffe8e8;color:#a52a2a">{{ $errors->first() }}</div> @endif
    <section class="card">
        <form method="POST" action="{{ route('admin.product-specification.attributes.update', $attribute) }}" class="form-grid">
            @csrf @method('PATCH')
            <div class="field"><label for="category_id">Group / category</label><select id="category_id" name="category_id" required>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) old('category_id', $attribute->category_id) === (string) $category->id)>{{ $category->name }}</option>@endforeach</select></div>
            <div class="field"><label for="name">Attribute name</label><input id="name" name="name" required maxlength="120" value="{{ old('name', $attribute->name) }}"></div>
            <div class="field"><label for="type">Type</label><select id="type" name="type" required>@foreach($types as $type)<option value="{{ $type }}" @selected(old('type', $attribute->type) === $type)>{{ ucfirst($type) }}</option>@endforeach</select></div>
            <div class="field" id="selectOptionsField"><label for="options">Choices (one per line, for Select type)</label><textarea id="options" name="options" rows="3" maxlength="2000" placeholder="Dry&#10;Oily&#10;Combination">{{ old('options', implode("\n", $attribute->options ?? [])) }}</textarea></div>
            <div class="field"><label for="sort_order">Display order</label><input id="sort_order" name="sort_order" type="number" min="0" max="65535" value="{{ old('sort_order', $attribute->sort_order) }}"></div>
            <div class="field"><label for="is_required">Requirement</label><select id="is_required" name="is_required"><option value="0" @selected(! old('is_required', $attribute->is_required))>Optional</option><option value="1" @selected((bool) old('is_required', $attribute->is_required))>Required</option></select></div>
            <div class="field" style="align-self:end"><button class="button primary" type="submit">Save attribute</button></div>
        </form>
    </section>
    <script>
        (function () {
            var type = document.getElementById('type');
            var options = document.getElementById('selectOptionsField');
            function updateOptionsVisibility() {
                if (options) options.hidden = !type || type.value !== 'select';
            }
            if (type) {
                type.addEventListener('change', updateOptionsVisibility);
                updateOptionsVisibility();
            }
        })();
    </script>
@endsection
