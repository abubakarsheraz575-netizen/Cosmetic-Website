@extends('admin.layouts.ecommerce')

@section('title', $category->name.' products')
@section('content')
    <section class="page-head">
        <div><h1><i class="{{ $category->iconClass() }}" aria-hidden="true"></i> {{ $category->name }}</h1><p>{{ $category->description ?: 'Products in this shop category.' }}</p></div>
        <a class="button" href="{{ route('admin.ecommerce.categories') }}"><i class="ri-arrow-left-line"></i> All categories</a>
    </section>
    <section class="card">
        <div class="toolbar"><h2 class="card-title">{{ $products->total() }} products</h2></div>
        <div class="table-wrap"><table data-server-pagination="true">
            <thead><tr><th>Product</th><th>Price</th><th>Discount</th><th>Stock</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($products as $product)
                <tr>
                    <td><div class="product-cell"><span class="product-thumb">@if($product->images->first())<img src="{{ asset('storage/'.$product->images->first()->path) }}" alt="{{ $product->name }}">@else{{ $product->emoji ?: '✦' }}@endif</span><span><strong>{{ $product->name }}</strong><small>{{ $product->slug }}</small></span></div></td>
                    <td>£{{ number_format((float) $product->price, 2) }}</td>
                    <td>{{ number_format((float) $product->discount, 2) }}%</td>
                    <td>{{ $product->stock }}</td>
                    <td><span class="badge {{ $product->is_active ? 'approved' : 'cancelled' }}">{{ $product->is_active ? 'Active' : 'Inactive' }}</span></td>
                </tr>
            @empty<tr><td colspan="5" class="empty">This category has no products.</td></tr>@endforelse
            </tbody>
        </table></div>
        <div style="margin-top:16px">{{ $products->links() }}</div>
    </section>
@endsection
