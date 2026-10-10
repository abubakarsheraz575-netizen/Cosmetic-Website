@extends('admin.layouts.ecommerce')

@section('title', $title)

@section('content')
<style>
    .marketplace-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:16px}
    .marketplace-stat{display:flex;align-items:center;gap:12px;padding:16px;border:1px solid var(--border);border-radius:13px;background:var(--card)}
    .marketplace-stat i{display:grid;place-items:center;width:40px;height:40px;border-radius:12px;background:#e4f6fb;color:#0e7490;font-size:20px}
    .marketplace-stat strong,.marketplace-stat span{display:block}.marketplace-stat strong{font-size:20px}.marketplace-stat span{margin-top:3px;color:var(--muted);font-size:11px}
    .marketplace-filter{display:grid;grid-template-columns:minmax(190px,1fr) 170px 140px auto auto;gap:8px;align-items:center}
    .marketplace-filter input,.marketplace-filter select{min-width:0;min-height:40px;padding:9px 11px;border:1px solid var(--border);border-radius:10px;background:var(--card);color:var(--text);font:inherit;font-size:12px}
    .vendor-table{min-width:840px}.vendor-person{display:flex;align-items:center;gap:10px;min-width:190px}
    .vendor-avatar{width:42px;height:42px;flex:none;display:grid;place-items:center;overflow:hidden;border-radius:50%;background:#e4f6fb;color:#0e5a75;font-weight:800}
    .vendor-avatar img{width:100%;height:100%;object-fit:cover}
    .vendor-person strong,.vendor-person small{display:block}.vendor-person small{margin-top:3px;color:var(--muted);font-size:11px}
    .vendor-state{display:inline-flex;align-items:center;gap:6px;padding:5px 9px;border-radius:20px;font-size:11px;font-weight:700}
    .vendor-state.active,.vendor-state.verified{background:#dcf7eb;color:#108255}.vendor-state.blocked,.vendor-state.unverified{background:#fff3d6;color:#9a6500}
    .vendor-actions{display:flex;align-items:center;gap:6px;flex-wrap:wrap}
    .button.small{padding:8px 10px;font-size:11px}.button.danger{border-color:#ffdada;background:#fff1f1;color:#b83232;box-shadow:none}
    .marketplace-pagination{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-top:16px}
    .marketplace-pagination nav{display:flex;gap:5px}.marketplace-pagination nav a,.marketplace-pagination nav span{display:inline-flex;align-items:center;justify-content:center;min-width:34px;height:34px;padding:0 9px;border:1px solid var(--border);border-radius:8px;color:var(--text);text-decoration:none;font-size:12px}
    .marketplace-pagination nav span[aria-current]{background:var(--brand);border-color:var(--brand);color:white}
    .marketplace-confirm{width:min(520px,calc(100vw - 28px));padding:0;border:1px solid var(--border);border-radius:20px;background:var(--card);color:var(--text);box-shadow:0 28px 90px rgba(15,23,42,.32)}
    .marketplace-confirm::backdrop{background:rgba(8,18,33,.48);backdrop-filter:blur(4px)}
    .confirm-inner{padding:28px;text-align:center}.confirm-mark{display:grid;place-items:center;width:72px;height:72px;margin:0 auto 16px;border:5px solid #ffe0e0;border-radius:50%;color:#e54545;font-size:34px}
    .confirm-inner h2{margin:0;font-size:19px}.confirm-inner p{margin:10px 0 20px;color:var(--muted);line-height:1.6}
    .confirm-actions{display:flex;justify-content:center;gap:10px}.confirm-actions button{min-width:110px;padding:11px 14px;border:1px solid var(--border);border-radius:10px;background:var(--card);color:var(--text);font:inherit;font-weight:700;cursor:pointer}
    .confirm-actions button:last-child{border-color:#e53636;background:#e53636;color:#fff}
    .flash-success{padding:12px 14px;border-radius:11px;background:#e6f8ee;color:#176a3b;font-size:12px;font-weight:700}
    @media(max-width:850px){.marketplace-filter{grid-template-columns:1fr 1fr}.marketplace-filter input{grid-column:1/-1}}
    @media(max-width:560px){.marketplace-stats{grid-template-columns:1fr}.marketplace-filter{grid-template-columns:1fr}.marketplace-filter input{grid-column:auto}.confirm-inner{padding:22px}}
</style>

<header class="page-head">
    <div><h1><i class="ri-store-2-line"></i>{{ $title }}</h1><p>Review vendor accounts, their product activity, and access status.</p></div>
    <a class="button" href="{{ route('admin.marketplace.messages') }}"><i class="ri-chat-3-line"></i>Vendor messages</a>
</header>

@if(session('success'))<div class="flash-success" role="status">{{ session('success') }}</div>@endif
@if($errors->any())<div class="errors" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<section class="marketplace-stats" aria-label="Vendor account summary">
    <article class="marketplace-stat"><i class="ri-store-2-line"></i><div><strong>{{ number_format($vendors->total()) }}</strong><span>{{ $title }} in this view</span></div></article>
    <article class="marketplace-stat"><i class="ri-checkbox-circle-line"></i><div><strong>{{ number_format($vendors->getCollection()->where('is_blocked', false)->count()) }}</strong><span>Active on this page</span></div></article>
    <article class="marketplace-stat"><i class="ri-box-3-line"></i><div><strong>{{ number_format($vendors->getCollection()->sum('submitted_products_count')) }}</strong><span>Submitted products on this page</span></div></article>
</section>

<section class="card">
    <div class="toolbar">
        <div><h2 class="card-title" style="margin:0">{{ $title }} directory</h2><span class="count-note">{{ number_format($vendors->total()) }} matching vendor accounts</span></div>
        <form method="GET" action="{{ match($verification){'verified'=>route('admin.marketplace.vendors'),'unverified'=>route('admin.marketplace.vendors.unverified'),default=>route('admin.marketplace.stores')} }}" class="marketplace-filter">
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search name, email, phone" aria-label="Search vendors">
            <select name="status" aria-label="Filter account status"><option value="">All account statuses</option><option value="active" @selected(($filters['status'] ?? '')==='active')>Active</option><option value="blocked" @selected(($filters['status'] ?? '')==='blocked')>Blocked</option></select>
            <select name="per_page" aria-label="Rows per page">@foreach([10,25,50] as $size)<option value="{{ $size }}" @selected((int)($filters['per_page']??25)===$size)>{{ $size }} per page</option>@endforeach</select>
            <button class="button primary" type="submit"><i class="ri-search-line"></i>Filter</button>
            @if(collect($filters)->filter()->isNotEmpty())<a class="button" href="{{ request()->url() }}">Reset</a>@endif
        </form>
    </div>
    <div class="table-wrap"><table class="vendor-table" data-server-pagination="true">
        <thead><tr><th>Vendor</th><th>Account</th><th>Products</th><th>Joined</th><th>Status</th><th>Operations</th></tr></thead>
        <tbody>
        @forelse($vendors as $vendor)
            <tr>
                <td><div class="vendor-person"><span class="vendor-avatar">@if($vendor->profile_photo)<img src="{{ asset('storage/'.$vendor->profile_photo) }}" alt="" loading="lazy">@else{{ mb_strtoupper(mb_substr($vendor->name,0,1)) }}@endif</span><span><strong>{{ $vendor->name }}</strong><small>{{ $vendor->email }}</small></span></div></td>
                <td><span class="vendor-state {{ $vendor->email_verified_at ? 'verified' : 'unverified' }}">{{ $vendor->email_verified_at ? 'Verified' : 'Unverified' }}</span></td>
                <td><strong>{{ number_format($vendor->submitted_products_count) }}</strong><small>{{ $vendor->published_products_count }} published · {{ $vendor->pending_products_count }} pending</small></td>
                <td>{{ $vendor->created_at?->format('M j, Y') ?? '—' }}</td>
                <td><span class="vendor-state {{ $vendor->is_blocked ? 'blocked' : 'active' }}">{{ $vendor->is_blocked ? 'Blocked' : 'Active' }}</span></td>
                <td><div class="vendor-actions">
                    <a class="button small" href="{{ route('admin.marketplace.vendors.show',$vendor) }}"><i class="ri-eye-line"></i>View</a>
                    <a class="button small" href="{{ route('admin.marketplace.messages',['vendor'=>$vendor->id]) }}"><i class="ri-chat-3-line"></i>Message</a>
                    <form method="POST" action="{{ route('admin.marketplace.vendors.status',$vendor) }}" data-marketplace-confirm data-confirm-title="{{ $vendor->is_blocked ? 'Restore '.$vendor->name.'?' : 'Block '.$vendor->name.'?' }}" data-confirm-copy="{{ $vendor->is_blocked ? 'They will regain access to their account.' : 'They will immediately lose access and will not be able to sign in until restored.' }}" data-confirm-label="{{ $vendor->is_blocked ? 'Yes, Restore' : 'Yes, Block' }}">
                        @csrf @method('PATCH')<input type="hidden" name="is_blocked" value="{{ $vendor->is_blocked ? 0 : 1 }}"><button class="button small" type="submit">{{ $vendor->is_blocked ? 'Restore' : 'Block' }}</button>
                    </form>
                    <form method="POST" action="{{ route('admin.marketplace.vendors.destroy',$vendor) }}" data-marketplace-confirm data-confirm-title="Delete {{ $vendor->name }}?" data-confirm-copy="This permanently removes the vendor account and their submitted products. Order records are retained." data-confirm-label="Yes, Delete">
                        @csrf @method('DELETE')<button class="button small danger" type="submit" aria-label="Delete {{ $vendor->name }}"><i class="ri-delete-bin-line"></i></button>
                    </form>
                </div></td>
            </tr>
        @empty
            <tr><td colspan="6" class="empty">No vendors match this view.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    <div class="marketplace-pagination"><span class="count-note">Showing {{ $vendors->firstItem() ?? 0 }}–{{ $vendors->lastItem() ?? 0 }} of {{ number_format($vendors->total()) }}</span>{{ $vendors->links() }}</div>
</section>

<dialog class="marketplace-confirm" id="marketplaceConfirm">
    <form method="dialog" class="confirm-inner">
        <span class="confirm-mark"><i class="ri-error-warning-line"></i></span>
        <h2 id="confirmTitle">Confirm vendor action</h2><p id="confirmCopy"></p>
        <div class="confirm-actions"><button value="cancel">Cancel</button><button type="button" id="confirmProceed">Confirm</button></div>
    </form>
</dialog>

@push('scripts')
<script>
(() => {
    const dialog = document.getElementById('marketplaceConfirm');
    let pendingForm = null;
    document.querySelectorAll('[data-marketplace-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.confirmed === 'true') return;
            event.preventDefault();
            pendingForm = form;
            document.getElementById('confirmTitle').textContent = form.dataset.confirmTitle || 'Confirm this action?';
            document.getElementById('confirmCopy').textContent = form.dataset.confirmCopy || '';
            document.getElementById('confirmProceed').textContent = form.dataset.confirmLabel || 'Confirm';
            dialog.showModal();
        });
    });
    document.getElementById('confirmProceed').addEventListener('click', () => {
        if (!pendingForm) return;
        pendingForm.dataset.confirmed = 'true';
        dialog.close();
        pendingForm.requestSubmit();
    });
    dialog.addEventListener('close', () => { pendingForm = null; });
})();
</script>
@endpush
@endsection
