@extends('admin.layouts.ecommerce')

@section('title', 'Edit product group')
@section('content')
    <section class="page-head">
        <div><h1><i class="ri-edit-line"></i> Edit group</h1><p>Update the category-backed product group “{{ $category->name }}”.</p></div>
        <a class="button" href="{{ route('admin.product-specification.groups') }}">Back to groups</a>
    </section>
    @if($errors->any()) <div class="alert" role="alert" style="background:#ffe8e8;color:#a52a2a">{{ $errors->first() }}</div> @endif
    <section class="card">
        <form method="POST" action="{{ route('admin.product-specification.groups.update', $category) }}" class="form-grid">
            @csrf @method('PATCH')
            <div class="field"><label for="name">Group name</label><input id="name" name="name" required maxlength="255" value="{{ old('name', $category->name) }}"></div>
            <div class="field"><label for="icon">Category icon</label><select id="icon" name="icon" required>@foreach($categoryIconChoices as $choice)<option value="{{ $choice['value'] }}" @selected(old('icon', $category->iconClass()) === $choice['value'])>{{ $choice['label'] }}</option>@endforeach</select></div>
            <div class="field full"><label for="description">Description</label><textarea id="description" name="description" maxlength="2000">{{ old('description', $category->description) }}</textarea></div>
            <div class="field"><label for="is_active">Status</label><select id="is_active" name="is_active"><option value="1" @selected((string) old('is_active', (int) $category->is_active) === '1')>Active</option><option value="0" @selected((string) old('is_active', (int) $category->is_active) === '0')>Inactive</option></select></div>
            <div class="field" style="align-self:end"><button class="button primary" type="submit">Save group</button></div>
        </form>
    </section>
@endsection
