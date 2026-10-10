@extends('admin.layouts.ecommerce')

@section('title', 'Product specifications · '.$product->name)
@section('content')
    <section class="page-head">
        <div><h1><i class="ri-list-check-2"></i> Product specifications</h1><p>{{ $product->name }} · {{ $product->category->name }}. Saved values appear in the product detail page on your storefront.</p></div>
        <a class="button" href="{{ route('admin.product-specification.groups.show', $product->category_id) }}">Back to group</a>
    </section>
    @if(session('success')) <div class="alert" role="status">{{ session('success') }}</div> @endif
    @if($errors->any()) <div class="alert" role="alert" style="background:#ffe8e8;color:#a52a2a">{{ $errors->first() }}</div> @endif
    <section class="card">
        @if($attributes->isEmpty())
            <p class="empty">This category has no specification fields yet. Add fields from Product Specification → Attributes first.</p>
        @else
            <form method="POST" action="{{ route('admin.product-specification.products.update', $product) }}" class="form-grid">
                @csrf @method('PATCH')
                @foreach($attributes as $attribute)
                    @php($fieldValue = old('specifications.'.$attribute->id, $values[$attribute->id] ?? ''))
                    <div class="field">
                        <label for="specification-{{ $attribute->id }}">{{ $attribute->name }} @if($attribute->is_required)<span aria-label="required">*</span>@else<small>(Optional)</small>@endif</label>
                        @if($attribute->type === 'select')
                            <select id="specification-{{ $attribute->id }}" name="specifications[{{ $attribute->id }}]">
                                <option value="">Choose {{ strtolower($attribute->name) }}</option>
                                @foreach($attribute->options ?? [] as $option)<option value="{{ $option }}" @selected($fieldValue === $option)>{{ $option }}</option>@endforeach
                            </select>
                        @elseif($attribute->type === 'boolean')
                            <select id="specification-{{ $attribute->id }}" name="specifications[{{ $attribute->id }}]">
                                <option value="">Choose an answer</option><option value="Yes" @selected($fieldValue === 'Yes')>Yes</option><option value="No" @selected($fieldValue === 'No')>No</option>
                            </select>
                        @elseif($attribute->type === 'size')
                            <select id="specification-{{ $attribute->id }}" name="specifications[{{ $attribute->id }}]">
                                <option value="">Choose size</option>
                                @foreach($sizes as $size) @php($sizeLabel = $size->name.' ('.$size->ml.' ml)')<option value="{{ $sizeLabel }}" @selected($fieldValue === $sizeLabel)>{{ $sizeLabel }}</option>@endforeach
                            </select>
                        @else
                            <input id="specification-{{ $attribute->id }}" name="specifications[{{ $attribute->id }}]" type="{{ $attribute->type === 'number' ? 'number' : 'text' }}" @if($attribute->type === 'number')step="any" inputmode="decimal"@endif maxlength="1000" value="{{ $fieldValue }}" @required($attribute->is_required)>
                        @endif
                        @error('specifications.'.$attribute->id)<small style="color:#a52a2a">{{ $message }}</small>@enderror
                    </div>
                @endforeach
                <div style="grid-column:1/-1;display:flex;justify-content:flex-end"><button class="button primary" type="submit">Save specifications</button></div>
            </form>
        @endif
    </section>
@endsection
