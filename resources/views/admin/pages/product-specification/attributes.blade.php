@extends('admin.layouts.ecommerce')

@section('title', 'Product Specification · Attributes')
@section('content')
    <section class="page-head">
        <div><h1><i class="ri-list-settings-line"></i> Product Specification Attributes</h1><p>Define fields for each product category. Set the actual values on a product from Product Specification Groups; saved values appear on that product's storefront detail page. These are separate from sellable product variants.</p></div>
        <a class="button" href="{{ route('admin.sizes') }}">Manage size catalog</a>
    </section>
    @if(session('success')) <div class="alert" role="status">{{ session('success') }}</div> @endif
    @if($errors->any()) <div class="alert" role="alert" style="background:#ffe8e8;color:#a52a2a">{{ $errors->first() }}</div> @endif

    <section class="card">
        <h2 class="card-title">Add specification attribute</h2>
        @if($categories->isEmpty())
            <p class="empty">Create a category before adding its specification attributes.</p>
        @else
            <form method="POST" action="{{ route('admin.product-specification.attributes.store') }}" class="form-grid">
                @csrf
                <div class="field"><label for="category_id">Group / category</label><select id="category_id" name="category_id" required><option value="">Choose a group</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) old('category_id', $categoryId) === (string) $category->id)>{{ $category->name }}</option>@endforeach</select></div>
                <div class="field"><label for="name">Attribute name</label><input id="name" name="name" required maxlength="120" value="{{ old('name') }}" placeholder="e.g. Skin type"></div>
                <div class="field"><label for="type">Type</label><select id="type" name="type" required>@foreach($types as $type)<option value="{{ $type }}" @selected(old('type', 'text') === $type)>{{ ucfirst($type) }}</option>@endforeach</select></div>
                <div class="field" id="selectOptionsField"><label for="options">Choices (one per line, for Select type)</label><textarea id="options" name="options" rows="3" maxlength="2000" placeholder="Dry&#10;Oily&#10;Combination">{{ old('options') }}</textarea></div>
                <div class="field"><label for="sort_order">Display order</label><input id="sort_order" name="sort_order" type="number" min="0" max="65535" value="{{ old('sort_order', 0) }}"></div>
                <div class="field"><label for="is_required">Requirement</label><select id="is_required" name="is_required"><option value="0">Optional</option><option value="1" @selected(old('is_required') === '1')>Required</option></select></div>
                <div class="field" style="align-self:end"><button class="button primary" type="submit">Add attribute</button></div>
            </form>
        @endif
    </section>

    <section class="card">
        <form class="toolbar" method="GET" action="{{ route('admin.product-specification.attributes') }}">
            <h2 class="card-title" style="margin:0">Specification fields</h2>
            <input type="search" name="search" value="{{ $search }}" placeholder="Search attributes…" aria-label="Search attributes">
            <select name="category_id" aria-label="Filter by group"><option value="">All groups</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) $categoryId === (string) $category->id)>{{ $category->name }}</option>@endforeach</select>
            <button class="button primary" type="submit"><i class="ri-search-line"></i> Search</button>
        </form>
        <div class="table-wrap"><table>
            <thead><tr><th>Attribute</th><th>Group</th><th>Type</th><th>Required</th><th>Order</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($attributes as $attribute)
                <tr>
                    <td><strong>{{ $attribute->name }}</strong></td>
                    <td>{{ $attribute->category->name }}</td>
                    <td><span class="badge">{{ ucfirst($attribute->type) }}</span>@if($attribute->type === 'select')<small style="display:block;margin-top:4px;color:var(--muted)">{{ count($attribute->options ?? []) }} choices</small>@endif</td>
                    <td>{{ $attribute->is_required ? 'Yes' : 'No' }}</td>
                    <td>{{ $attribute->sort_order }}</td>
                    <td><div style="display:flex;gap:6px;flex-wrap:wrap">
                        <a class="button" href="{{ route('admin.product-specification.attributes.edit', $attribute) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.product-specification.attributes.destroy', $attribute) }}" data-confirm-modal data-confirm-title="Delete this specification attribute?" data-confirm-message="This attribute will be permanently removed.">@csrf @method('DELETE')<button class="button danger" type="submit">Delete</button></form>
                    </div></td>
                </tr>
            @empty<tr><td colspan="6" class="empty">No specification attributes match your search.</td></tr>@endforelse
            </tbody>
        </table></div>
        <div style="margin-top:16px">{{ $attributes->links() }}</div>
    </section>

    <section class="card">
        <div class="toolbar"><h2 class="card-title" style="margin:0">Size classifications</h2><a class="button" href="{{ route('admin.sizes') }}">Open size management</a></div>
        <p style="color:var(--muted);font-size:12px">The Size attribute uses the existing millilitre catalog and reusable size tiers: up to 50 ml is Small, up to 250 ml is Medium, and over 250 ml is Large.</p>
        <div class="table-wrap"><table>
            <thead><tr><th>Size</th><th>Millilitres</th><th>Classification</th><th>Status</th></tr></thead>
            <tbody>@forelse($sizes as $size)<tr><td>{{ $size->name }}</td><td>{{ number_format($size->ml) }} ml</td><td><span class="badge">{{ $size->size_tier }}</span></td><td><span class="badge {{ $size->is_active ? 'approved' : 'cancelled' }}">{{ $size->is_active ? 'Active' : 'Inactive' }}</span></td></tr>@empty<tr><td colspan="4" class="empty">No sizes are configured yet.</td></tr>@endforelse</tbody>
        </table></div>
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
