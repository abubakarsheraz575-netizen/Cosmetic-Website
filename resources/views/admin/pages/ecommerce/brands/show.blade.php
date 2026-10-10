@extends('admin.layouts.ecommerce')

@section('title', $brand->name.' products')
@section('content')
    <style>
        .brand-products-toolbar { display:grid;grid-template-columns:auto minmax(0,1fr);align-items:center;gap:18px; }
        .brand-products-toolbar .card-title { margin:0;color:var(--text);font-size:14px; }
        .brand-filters { display:grid;grid-template-columns:minmax(190px,1fr) minmax(145px,.55fr) auto;align-items:center;gap:9px;width:100%; }
        .brand-filters input,.brand-filters select { min-height:40px; padding:9px 12px; color:var(--text); background:var(--card); border:1px solid var(--border); border-radius:10px; font:inherit; font-size:12px }
        .brand-filters input { width:100%;max-width:none;min-width:0 }
        .brand-filters select { width:100%;min-width:0 }
        .brand-filters a { color:inherit; text-decoration:none }
        .table-wrap th a { color:inherit; text-decoration:none }
        .table-wrap th a:focus-visible { outline:2px solid var(--brand); outline-offset:2px }
        .brand-pagination { display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-top:18px; }
        .brand-pagination p { margin:0;color:var(--muted);font-size:12px; }
        .brand-pagination-links { display:flex;align-items:center;flex-wrap:wrap;gap:5px; }
        .brand-page { display:inline-flex;align-items:center;justify-content:center;min-width:35px;height:35px;padding:0 10px;border:1px solid var(--border);border-radius:9px;background:var(--card);color:var(--text);font-size:12px;font-weight:700;text-decoration:none; }
        a.brand-page:hover,.brand-page.active { border-color:var(--brand);background:var(--brand);color:#fff; }
        .brand-page.disabled,.brand-page-gap { color:var(--muted);opacity:.65; }
        .brand-page-gap { padding:0 3px; }
        @media(max-width:1100px) { .brand-products-toolbar { grid-template-columns:1fr;gap:12px } }
        @media(max-width:600px) { .brand-filters { grid-template-columns:minmax(0,1fr) minmax(0,1fr); } .brand-filters input { grid-column:1/-1; } .brand-filters .button { justify-content:center; } .brand-pagination { align-items:flex-start;flex-direction:column; } }
    </style>
    <section class="page-head">
        <div style="display:flex;align-items:center;gap:14px">
            @if($brand->logo_path)
                <img src="{{ asset('storage/'.$brand->logo_path) }}" alt="{{ $brand->name }} logo" style="width:60px;height:60px;object-fit:contain;border-radius:12px">
            @endif
            <div>
                <h1><i class="ri-price-tag-3-line"></i> {{ $brand->name }}</h1>
                <p>{{ $brand->description ?: 'Products from this brand.' }}</p>
                @if($brand->website_url)<p><a href="{{ $brand->website_url }}" target="_blank" rel="noopener noreferrer">{{ $brand->website_url }}</a></p>@endif
            </div>
        </div>
        <div class="actions">
            <a class="button" href="{{ route('admin.ecommerce.brands') }}"><i class="ri-arrow-left-line"></i> All brands</a>
            <a class="button primary" href="{{ route('admin.ecommerce.brands.edit', $brand) }}"><i class="ri-edit-line"></i> Edit brand</a>
        </div>
    </section>

    <section class="card">
        <div class="toolbar brand-products-toolbar">
            <h2 class="card-title">{{ number_format($products->total()) }} products</h2>
            <form class="brand-filters" method="GET" action="{{ route('admin.ecommerce.brands.show', $brand) }}">
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search products..." aria-label="Search products in {{ $brand->name }}">
                <label for="brand-products-per-page" class="sr-only">Rows per page</label>
                <select id="brand-products-per-page" name="per_page" aria-label="Rows per page">
                    @foreach([10, 25, 50] as $size)
                        <option value="{{ $size }}" @selected((int) ($filters['per_page'] ?? 25) === $size)>{{ $size }} per page</option>
                    @endforeach
                </select>
                @if(isset($filters['sort']))<input type="hidden" name="sort" value="{{ $sort }}">@endif
                @if(isset($filters['direction']))<input type="hidden" name="direction" value="{{ $direction }}">@endif
                <button class="button primary" type="submit"><i class="ri-search-line"></i> Search</button>
            </form>
        </div>
        <div class="table-wrap">
            <table data-server-pagination="true">
                <thead><tr>
                    <th scope="col" aria-sort="{{ $sort === 'name' ? ($direction === 'asc' ? 'ascending' : 'descending') : 'none' }}"><a href="{{ route('admin.ecommerce.brands.show', array_merge(['brand' => $brand], request()->query(), ['sort' => 'name', 'direction' => $sort === 'name' && $direction === 'asc' ? 'desc' : 'asc', 'page' => 1])) }}">Product <i class="ri-arrow-up-down-line" aria-hidden="true"></i></a></th>
                    <th scope="col" aria-sort="{{ $sort === 'price' ? ($direction === 'asc' ? 'ascending' : 'descending') : 'none' }}"><a href="{{ route('admin.ecommerce.brands.show', array_merge(['brand' => $brand], request()->query(), ['sort' => 'price', 'direction' => $sort === 'price' && $direction === 'asc' ? 'desc' : 'asc', 'page' => 1])) }}">Price <i class="ri-arrow-up-down-line" aria-hidden="true"></i></a></th>
                    <th scope="col" aria-sort="{{ $sort === 'stock' ? ($direction === 'asc' ? 'ascending' : 'descending') : 'none' }}"><a href="{{ route('admin.ecommerce.brands.show', array_merge(['brand' => $brand], request()->query(), ['sort' => 'stock', 'direction' => $sort === 'stock' && $direction === 'asc' ? 'desc' : 'asc', 'page' => 1])) }}">Quantity / stock <i class="ri-arrow-up-down-line" aria-hidden="true"></i></a></th>
                    <th scope="col" aria-sort="{{ $sort === 'is_active' ? ($direction === 'asc' ? 'ascending' : 'descending') : 'none' }}"><a href="{{ route('admin.ecommerce.brands.show', array_merge(['brand' => $brand], request()->query(), ['sort' => 'is_active', 'direction' => $sort === 'is_active' && $direction === 'asc' ? 'desc' : 'asc', 'page' => 1])) }}">Status <i class="ri-arrow-up-down-line" aria-hidden="true"></i></a></th>
                </tr></thead>
                <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px">
                                @if($product->images->first())
                                    <img src="{{ asset('storage/'.$product->images->first()->path) }}" alt="{{ $product->name }}" style="width:48px;height:48px;object-fit:cover;border-radius:9px">
                                @else
                                    <span class="stat-icon"><i class="ri-box-3-line"></i></span>
                                @endif
                                <span><strong>{{ $product->name }}</strong><small>{{ $product->slug }}</small></span>
                            </div>
                        </td>
                        <td>£{{ number_format((float) $product->price, 2) }}</td>
                        <td>{{ number_format((int) $product->stock) }}</td>
                        <td><span class="badge {{ $product->is_active ? 'approved' : 'cancelled' }}">{{ $product->is_active ? 'Active' : 'Inactive' }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="empty">This brand has no associated products.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @include('admin.pages.ecommerce.brands.pagination', ['paginator' => $products, 'itemLabel' => 'products'])
    </section>
@endsection
