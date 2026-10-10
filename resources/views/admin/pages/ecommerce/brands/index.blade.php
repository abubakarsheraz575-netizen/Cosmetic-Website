@extends('admin.layouts.ecommerce')

@section('title', 'Brands')
@section('content')
    <style>
        .brand-toolbar { display:grid;grid-template-columns:minmax(170px,.7fr) minmax(0,2fr);align-items:center;gap:18px;margin-bottom:0; }
        .brand-toolbar .card-title { margin:0;color:var(--text);font-size:14px; }
        .brand-filters { display:grid;grid-template-columns:minmax(190px,1fr) minmax(130px,.55fr) auto;align-items:center;gap:9px;width:100%; }
        .brand-filters input,.brand-filters select { min-height:40px; padding:9px 12px; color:var(--text); background:var(--card); border:1px solid var(--border); border-radius:10px; font:inherit; font-size:12px }
        .brand-filters input { width:100%;min-width:0 }
        .brand-filters select { width:100%;min-width:0 }
        .brand-filters a { color:inherit; text-decoration:none }
        .table-wrap th a { color:inherit; text-decoration:none }
        .table-wrap th a:focus-visible { outline:2px solid var(--brand); outline-offset:2px }
        .brand-logo { width:44px;height:44px;flex:none;display:grid;place-items:center;overflow:hidden;border:1px solid var(--border);border-radius:12px;background:var(--bg);color:var(--brand);font-size:19px; }
        .brand-logo img { width:100%;height:100%;object-fit:contain;padding:4px; }
        .brand-summary { display:inline-flex;align-items:center;gap:7px;padding:8px 11px;border-radius:10px;background:var(--bg);color:var(--muted);font-size:12px;font-weight:700; }
        .brand-summary strong { color:var(--text);font-size:14px; }
        .brand-pagination { display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-top:18px; }
        .brand-pagination p { margin:0;color:var(--muted);font-size:12px; }
        .brand-pagination-links { display:flex;align-items:center;flex-wrap:wrap;gap:5px; }
        .brand-page { display:inline-flex;align-items:center;justify-content:center;min-width:35px;height:35px;padding:0 10px;border:1px solid var(--border);border-radius:9px;background:var(--card);color:var(--text);font-size:12px;font-weight:700;text-decoration:none; }
        a.brand-page:hover,.brand-page.active { border-color:var(--brand);background:var(--brand);color:#fff; }
        .brand-page.disabled,.brand-page-gap { color:var(--muted);opacity:.65; }
        .brand-page-gap { padding:0 3px; }
        @media(max-width:960px) { .brand-toolbar { grid-template-columns:1fr;gap:12px } }
        @media(max-width:600px) { .brand-filters { grid-template-columns:1fr 1fr; } .brand-filters input { grid-column:1/-1 } .brand-filters .button { justify-content:center } }
    </style>
    <section class="page-head">
        <div>
            <h1><i class="ri-price-tag-3-line"></i> Brands</h1>
            <p>Manage brand profiles and the products associated with each brand.</p>
        </div>
        <a class="button primary" href="{{ route('admin.ecommerce.brands.create') }}"><i class="ri-add-line"></i> Add brand</a>
    </section>

    <section class="card">
        <div class="toolbar brand-toolbar">
            <h2 class="brand-summary"><strong>{{ number_format($brands->total()) }}</strong> {{ \Illuminate\Support\Str::plural('brand', $brands->total()) }}</h2>
            <form class="brand-filters" method="GET" action="{{ route('admin.ecommerce.brands') }}">
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search brands..." aria-label="Search brands">
                <label for="brand-per-page" class="sr-only">Rows per page</label>
                <select id="brand-per-page" name="per_page" aria-label="Rows per page">
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
                    <th scope="col" aria-sort="{{ $sort === 'name' ? ($direction === 'asc' ? 'ascending' : 'descending') : 'none' }}"><a href="{{ route('admin.ecommerce.brands', array_merge(request()->query(), ['sort' => 'name', 'direction' => $sort === 'name' && $direction === 'asc' ? 'desc' : 'asc', 'page' => 1])) }}">Brand <i class="ri-arrow-up-down-line" aria-hidden="true"></i></a></th>
                    <th scope="col">Description</th>
                    <th scope="col" aria-sort="{{ $sort === 'is_active' ? ($direction === 'asc' ? 'ascending' : 'descending') : 'none' }}"><a href="{{ route('admin.ecommerce.brands', array_merge(request()->query(), ['sort' => 'is_active', 'direction' => $sort === 'is_active' && $direction === 'asc' ? 'desc' : 'asc', 'page' => 1])) }}">Status <i class="ri-arrow-up-down-line" aria-hidden="true"></i></a></th>
                    <th scope="col" aria-sort="{{ $sort === 'products_count' ? ($direction === 'asc' ? 'ascending' : 'descending') : 'none' }}"><a href="{{ route('admin.ecommerce.brands', array_merge(request()->query(), ['sort' => 'products_count', 'direction' => $sort === 'products_count' && $direction === 'asc' ? 'desc' : 'asc', 'page' => 1])) }}">Products <i class="ri-arrow-up-down-line" aria-hidden="true"></i></a></th>
                    <th scope="col"><span class="sr-only">Actions</span></th>
                </tr></thead>
                <tbody>
                @forelse($brands as $brand)
                    <tr>
                        <td>
                            <a href="{{ route('admin.ecommerce.brands.show', $brand) }}" style="display:flex;align-items:center;gap:10px;color:inherit;text-decoration:none">
                                <span class="brand-logo">@if($brand->logo_path)<img src="{{ asset('storage/'.$brand->logo_path) }}" alt="{{ $brand->name }} logo" loading="lazy">@else<i class="ri-price-tag-3-line" aria-hidden="true"></i>@endif</span>
                                <strong>{{ $brand->name }}</strong>
                            </a>
                        </td>
                        <td>{{ \Illuminate\Support\Str::limit($brand->description ?: 'No description', 90) }}</td>
                        <td><span class="badge {{ $brand->is_active ? 'approved' : 'cancelled' }}">{{ $brand->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td>{{ $brand->products_count }}</td>
                        <td class="actions">
                            <a class="button" href="{{ route('admin.ecommerce.brands.show', $brand) }}">View products <i class="ri-arrow-right-line"></i></a>
                            <a class="button" href="{{ route('admin.ecommerce.brands.edit', $brand) }}">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">No brands have been created yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @include('admin.pages.ecommerce.brands.pagination', ['paginator' => $brands, 'itemLabel' => 'brands'])
    </section>
@endsection
