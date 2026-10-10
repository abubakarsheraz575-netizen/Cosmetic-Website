@extends('admin.layouts.ecommerce')

@section('title', 'Category products')
@section('content')
    <section class="page-head">
        <div><h1><i class="ri-apps-2-line"></i> Category browsing</h1><p>Browse shop categories and their complete product catalogs.</p></div>
        <span class="badge">{{ $categories->count() }} categories</span>
    </section>
    <section class="card">
        <div class="toolbar"><h2 class="card-title" style="margin:0">Shop categories</h2><select data-table-status="categoriesTable" aria-label="Filter categories by status"><option value="">All categories</option><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
        <div class="table-wrap"><table id="categoriesTable">
            <thead><tr><th>Category</th><th>Status</th><th>Products</th><th></th></tr></thead>
            <tbody>
            @forelse($categories as $category)
                <tr data-status="{{ $category->is_active ? 'active' : 'inactive' }}">
                    <td><div class="product-cell"><span class="product-thumb" aria-hidden="true"><i class="{{ $category->iconClass() }}"></i></span><span><strong>{{ $category->name }}</strong><small>{{ $category->description ?: 'No description' }}</small></span></div></td>
                    <td><span class="badge {{ $category->is_active ? 'approved' : 'cancelled' }}">{{ $category->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>{{ $category->products_count }}</td>
                    <td><a class="button" href="{{ route('admin.ecommerce.categories.show', $category) }}">Browse products <i class="ri-arrow-right-line"></i></a></td>
                </tr>
            @empty<tr><td colspan="4" class="empty">No categories have been created.</td></tr>@endforelse
            </tbody>
        </table></div>
    </section>
@endsection
