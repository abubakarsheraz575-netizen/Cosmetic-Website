@extends('admin.layouts.ecommerce')

@section('title', 'Product Specification · Groups')
@section('content')
    <section class="page-head">
        <div><h1><i class="ri-folders-line"></i> Product Specification Groups</h1><p>Product groups are backed by shop categories. Open a group to browse product photos, stock and specifications.</p></div>
        <div style="display:flex;align-items:center;gap:8px"><span class="badge">{{ $groups->total() }} groups</span><a class="button primary" href="{{ route('admin.categories') }}">Create group</a></div>
    </section>

    @if(session('success')) <div class="alert" role="status">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert" role="alert" style="background:#ffe8e8;color:#a52a2a">{{ session('error') }}</div> @endif

    <section class="card">
        <form class="toolbar" method="GET" action="{{ route('admin.product-specification.groups') }}">
            <h2 class="card-title" style="margin:0">Groups</h2>
            <input type="search" name="search" value="{{ $search }}" placeholder="Search groups…" aria-label="Search groups">
            <select name="status" aria-label="Filter by status">
                <option value="">All statuses</option>
                <option value="active" @selected($status === 'active')>Active</option>
                <option value="inactive" @selected($status === 'inactive')>Inactive</option>
            </select>
            <button class="button primary" type="submit"><i class="ri-search-line"></i> Search</button>
        </form>
        <div class="table-wrap"><table>
            <thead><tr><th>Group</th><th>Products</th><th>Status</th><th>Specification attributes</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($groups as $group)
                <tr>
                    <td><div class="product-cell"><span class="product-thumb" aria-hidden="true"><i class="{{ $group->iconClass() }}"></i></span><span><strong>{{ $group->name }}</strong><small>{{ $group->description ?: 'No description' }}</small></span></div></td>
                    <td>{{ $group->products_count }}</td>
                    <td><span class="badge {{ $group->is_active ? 'approved' : 'cancelled' }}">{{ $group->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>{{ $group->specification_attributes_count }}</td>
                    <td>
                        <div style="display:flex;flex-wrap:wrap;gap:6px">
                            <a class="button primary" href="{{ route('admin.product-specification.groups.show', $group) }}">View products</a>
                            <a class="button" href="{{ route('admin.product-specification.groups.edit', $group) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.product-specification.groups.destroy', $group) }}" data-confirm-modal data-confirm-title="Delete this product group?" data-confirm-message="Only empty groups can be deleted.">
                                @csrf @method('DELETE')
                                <button class="button danger" type="submit">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty">No matching product groups.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        <div style="margin-top:16px">{{ $groups->links() }}</div>
    </section>
@endsection
