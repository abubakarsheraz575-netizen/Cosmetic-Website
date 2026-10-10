@extends('admin.layouts.ecommerce')

@section('title', 'Customers & vendors')

@section('content')
<style>
    .directory-stats { grid-template-columns:repeat(4,minmax(0,1fr)) }
    .directory-stats .stat > .stat-icon { display:grid;place-items:center;margin:0;line-height:1; }
    .directory-stats .stat > .stat-icon i { display:block;line-height:1; }
    .directory-table { min-width:850px }
    .person { display:flex; align-items:center; gap:11px; min-width:190px }
    .person-avatar { display:grid; place-items:center; width:42px; height:42px; flex:none; overflow:hidden; border-radius:50%; background:linear-gradient(145deg,#dff6fb,#e9efff); color:#12617b; font-weight:800 }
    .person-avatar img { width:100%; height:100%; object-fit:cover }
    .person-name { color:var(--text); font-weight:700; text-decoration:none }
    .person-name:hover { color:var(--brand); text-decoration:underline }
    .directory-toolbar { display:grid;grid-template-columns:minmax(165px,.65fr) minmax(0,2fr);align-items:center;gap:16px;margin-bottom:0; }
    .directory-filters { display:grid;grid-template-columns:minmax(190px,1.5fr) repeat(3,minmax(125px,.8fr)) auto auto;align-items:center;gap:8px;width:100%; }
    .directory-filters input,.directory-filters select { min-height:40px; padding:9px 12px; color:var(--text); background:var(--card); border:1px solid var(--border); border-radius:10px; font:inherit; font-size:12px }
    .directory-filters input { min-width:0;width:100% }
    .directory-filters select { min-width:130px }
    .directory-filters .button { min-height:40px }
    .directory-table th a { color:inherit; text-decoration:none }
    .directory-table th a:focus-visible { outline:2px solid var(--brand); outline-offset:2px }
    .badge.vendor { background:#efe8ff; color:#7144c8 }
    .badge.customer { background:#edf2f7; color:#53657b }
    .badge.blocked { background:#ffe8e8; color:#c33737 }
    .badge.active { background:#dcf7eb; color:#108255 }
    .count-note { color:var(--muted); font-size:11px }
    .pagination-wrap { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-top:15px }
    @media(max-width:1180px) { .directory-toolbar { grid-template-columns:1fr;gap:12px } .directory-filters { grid-template-columns:minmax(200px,1fr) repeat(2,minmax(130px,.7fr)) auto auto } .directory-filters input { grid-column:span 2 } }
    @media(max-width:680px) { .directory-stats { grid-template-columns:1fr 1fr } .directory-filters { grid-template-columns:1fr 1fr } .directory-filters input { grid-column:1/-1 } }
    @media(max-width:480px) { .directory-filters { grid-template-columns:1fr } .directory-filters input { grid-column:auto } }
    @media(max-width:420px) { .directory-stats { grid-template-columns:1fr } }
</style>
<header class="page-head">
    <div>
        <h1><i class="ri-group-line"></i>Customers & vendors</h1>
        <p>Browse customer accounts and vendor profiles from submitted products.</p>
    </div>
</header>

<section class="stats directory-stats" aria-label="Directory summary">
    <article class="card stat"><span class="stat-icon"><i class="ri-group-line"></i></span><div><strong>{{ number_format($stats['total']) }}</strong><span>People in directory</span></div></article>
    <article class="card stat"><span class="stat-icon violet"><i class="ri-store-2-line"></i></span><div><strong>{{ number_format($stats['vendors']) }}</strong><span>Vendors with submissions</span></div></article>
    <article class="card stat"><span class="stat-icon green"><i class="ri-user-follow-line"></i></span><div><strong>{{ number_format($stats['total'] - $stats['blocked']) }}</strong><span>Active accounts</span></div></article>
    <article class="card stat"><span class="stat-icon amber"><i class="ri-user-add-line"></i></span><div><strong>{{ number_format($stats['new']) }}</strong><span>Joined this month</span></div></article>
</section>

<section class="card">
    <div class="toolbar directory-toolbar">
        <div>
            <h2 class="card-title" style="margin:0">Directory</h2>
            <span class="count-note">{{ number_format($users->total()) }} matching accounts</span>
        </div>
        <form class="directory-filters" method="GET" action="{{ route('admin.ecommerce.customers.index') }}">
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search name or email..." aria-label="Search customers and vendors">
            <select name="type" aria-label="Filter by account type">
                <option value="">All account types</option>
                <option value="customer" @selected(($filters['type'] ?? '') === 'customer')>Customers</option>
                <option value="vendor" @selected(($filters['type'] ?? '') === 'vendor')>Vendors</option>
            </select>
            <select name="status" aria-label="Filter by account status">
                <option value="">All statuses</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                <option value="blocked" @selected(($filters['status'] ?? '') === 'blocked')>Blocked</option>
            </select>
            <select name="per_page" aria-label="Results per page">
                @foreach([10, 25, 50] as $size)
                    <option value="{{ $size }}" @selected((int) ($filters['per_page'] ?? 25) === $size)>{{ $size }} per page</option>
                @endforeach
            </select>
            @if(isset($filters['sort']))<input type="hidden" name="sort" value="{{ $sort }}">@endif
            @if(isset($filters['direction']))<input type="hidden" name="direction" value="{{ $direction }}">@endif
            <button class="button primary" type="submit"><i class="ri-search-line"></i>Filter</button>
            @if(collect($filters)->filter()->isNotEmpty())
                <a class="button" href="{{ route('admin.ecommerce.customers.index') }}">Reset</a>
            @endif
        </form>
    </div>
    <div class="table-wrap">
        <table class="directory-table" data-server-pagination="true">
            <thead><tr>
                <th scope="col" aria-sort="{{ $sort === 'name' ? ($direction === 'asc' ? 'ascending' : 'descending') : 'none' }}"><a href="{{ route('admin.ecommerce.customers.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => $sort === 'name' && $direction === 'asc' ? 'desc' : 'asc', 'page' => 1])) }}">Person <i class="ri-arrow-up-down-line" aria-hidden="true"></i></a></th>
                <th scope="col">Account type</th>
                <th scope="col" aria-sort="{{ $sort === 'created_at' ? ($direction === 'asc' ? 'ascending' : 'descending') : 'none' }}"><a href="{{ route('admin.ecommerce.customers.index', array_merge(request()->query(), ['sort' => 'created_at', 'direction' => $sort === 'created_at' && $direction === 'asc' ? 'desc' : 'asc', 'page' => 1])) }}">Joined <i class="ri-arrow-up-down-line" aria-hidden="true"></i></a></th>
                <th scope="col" aria-sort="{{ $sort === 'is_blocked' ? ($direction === 'asc' ? 'ascending' : 'descending') : 'none' }}"><a href="{{ route('admin.ecommerce.customers.index', array_merge(request()->query(), ['sort' => 'is_blocked', 'direction' => $sort === 'is_blocked' && $direction === 'asc' ? 'desc' : 'asc', 'page' => 1])) }}">Status <i class="ri-arrow-up-down-line" aria-hidden="true"></i></a></th>
                <th scope="col" aria-sort="{{ $sort === 'submitted_products_count' ? ($direction === 'asc' ? 'ascending' : 'descending') : 'none' }}"><a href="{{ route('admin.ecommerce.customers.index', array_merge(request()->query(), ['sort' => 'submitted_products_count', 'direction' => $sort === 'submitted_products_count' && $direction === 'asc' ? 'desc' : 'asc', 'page' => 1])) }}">Submitted products <i class="ri-arrow-up-down-line" aria-hidden="true"></i></a></th>
                <th scope="col"><span class="sr-only">Actions</span></th>
            </tr></thead>
            <tbody>
            @forelse($users as $person)
                <tr>
                    <td>
                        <div class="person">
                            <span class="person-avatar">
                                @if($person->profile_photo)
                                    <img src="{{ asset('storage/' . $person->profile_photo) }}" alt="{{ $person->name }} profile photo" loading="lazy">
                                @else
                                    {{ mb_strtoupper(mb_substr($person->name, 0, 1)) }}
                                @endif
                            </span>
                            <span>
                                @if($person->submitted_products_count)
                                    <a class="person-name" href="{{ url('/admin/ecommerce/customers/' . $person->id) }}">{{ $person->name }}</a>
                                @else
                                    <strong>{{ $person->name }}</strong>
                                @endif
                                <small>{{ $person->email }}</small>
                            </span>
                        </div>
                    </td>
                    <td>
                        @if($person->submitted_products_count)
                            <span class="badge vendor"><i class="ri-store-2-line"></i>&nbsp; Vendor</span>
                        @else
                            <span class="badge customer">Customer</span>
                        @endif
                    </td>
                    <td>{{ $person->created_at?->format('M j, Y') ?? '—' }}</td>
                    <td><span class="badge {{ $person->is_blocked ? 'blocked' : 'active' }}">{{ $person->is_blocked ? 'Blocked' : 'Active' }}</span></td>
                    <td>
                        @if($person->submitted_products_count)
                            <span>{{ number_format($person->submitted_products_count) }}</span>
                            <small>submitted</small>
                        @else
                            <span class="count-note">—</span>
                        @endif
                    </td>
                    <td>
                        @if($person->submitted_products_count)
                            <a class="button" href="{{ url('/admin/ecommerce/customers/' . $person->id) }}">View profile <i class="ri-arrow-right-line"></i></a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty">No accounts match these filters.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">
        <span class="count-note">Showing {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} of {{ number_format($users->total()) }}</span>
        {{ $users->links() }}
    </div>
</section>
@endsection
