@extends('admin.layouts.ecommerce')

@section('title', $category->name.' specifications')
@section('content')
    <section class="page-head">
        <div><h1><i class="{{ $category->iconClass() }}" aria-hidden="true"></i> {{ $category->name }}</h1><p>{{ $category->description ?: 'Products and specifications in this group.' }}</p></div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <a class="button" href="{{ route('admin.product-specification.groups.edit', $category) }}">Edit group</a>
            <a class="button" href="{{ route('admin.product-specification.groups') }}">All groups</a>
        </div>
    </section>
    <section class="card">
        <div class="toolbar"><h2 class="card-title" style="margin:0">Group specifications</h2><a class="button" href="{{ route('admin.product-specification.attributes', ['category_id' => $category->id]) }}">Manage attributes</a></div>
        @if($attributes->isEmpty())<p class="empty">No specification attributes are defined for this group.</p>
        @else
            <div style="display:flex;flex-wrap:wrap;gap:8px">
                @foreach($attributes as $attribute)
                    <span class="badge">{{ $attribute->name }} · {{ ucfirst($attribute->type) }}{{ $attribute->is_required ? ' · Required' : '' }}</span>
                @endforeach
            </div>
        @endif
    </section>
    <section class="card">
        <div class="toolbar"><h2 class="card-title" style="margin:0">{{ $products->total() }} products</h2><a class="button" href="{{ route('admin.sizes') }}">Manage sizes</a></div>
        <div class="table-wrap"><table>
            <thead><tr><th>Product / pictures</th><th>Size</th><th>Size tier</th><th>Price</th><th>Inventory</th><th>Status</th><th>Specifications</th></tr></thead>
            <tbody>
            @forelse($products as $product)
                <tr>
                    <td>
                        <div class="product-cell">
                            <span class="product-thumb">@if($product->images->first())<img src="{{ asset('storage/'.$product->images->first()->path) }}" alt="{{ $product->name }}">@else{{ $product->emoji ?: '✦' }}@endif</span>
                            <span><strong>{{ $product->name }}</strong><small>{{ $product->images->count() }} picture(s) · {{ $product->slug }}</small>
                            @if($product->images->count() > 1)<span style="display:flex;gap:4px;margin-top:6px">@foreach($product->images->skip(1)->take(4) as $image)<img src="{{ asset('storage/'.$image->path) }}" alt="{{ $product->name }} picture" style="width:30px;height:30px;object-fit:cover;border-radius:6px">@endforeach</span>@endif
                            </span>
                        </div>
                    </td>
                    <td>{{ $product->size ? $product->size->name : '—' }}</td>
                    <td>{{ $product->size ? $product->size->size_tier : '—' }}</td>
                    <td>£{{ number_format((float) $product->price, 2) }}</td>
                    <td>{{ number_format($product->stock) }}</td>
                    <td><span class="badge {{ $product->is_active ? 'approved' : 'cancelled' }}">{{ $product->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td><a class="button primary" href="{{ route('admin.product-specification.products.edit', $product) }}">Set product specifications</a></td>
                </tr>
            @empty<tr><td colspan="7" class="empty">This group has no products yet.</td></tr>@endforelse
            </tbody>
        </table></div>
        <div style="margin-top:16px">{{ $products->links() }}</div>
    </section>
@endsection
