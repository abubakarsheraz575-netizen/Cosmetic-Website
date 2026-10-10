@extends('admin.layouts.ecommerce')

@section('title', 'Coupons')
@section('content')
    <style>
        .coupon-page { display:grid;align-content:start;gap:20px;min-width:0; }
        .coupon-hero { position:relative;isolation:isolate;display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:24px;overflow:hidden;padding:30px 32px;border:1px solid rgba(255,255,255,.14);border-radius:22px;background:radial-gradient(ellipse at 82% 0%,rgba(119,231,233,.28),transparent 34%),linear-gradient(120deg,#103b52 0%,#12667a 53%,#1888a0 100%);color:#fff;box-shadow:0 20px 45px rgba(14,90,117,.19); }
        .coupon-hero:after { position:absolute;z-index:-1;right:175px;bottom:-145px;width:290px;height:290px;border:1px solid rgba(255,255,255,.13);border-radius:50%;content:"";box-shadow:0 0 0 25px rgba(255,255,255,.035),0 0 0 52px rgba(255,255,255,.025); }
        .coupon-hero-copy { max-width:650px; }
        .coupon-hero-actions { display:flex;align-items:center;gap:10px;margin-top:17px; }
        .coupon-create-trigger { min-height:42px;padding:0 16px;border:1px solid rgba(255,255,255,.72);border-radius:12px;background:#fff;color:#0e5a75;font-size:12px;font-weight:800;box-shadow:0 8px 20px rgba(4,37,55,.16); }
        .coupon-create-trigger:hover { background:#eafcff;transform:translateY(-1px); }
        .coupon-eyebrow { display:inline-flex;align-items:center;gap:7px;margin-bottom:11px;color:#a8eff2;font-size:10px;font-weight:800;letter-spacing:.16em;text-transform:uppercase; }
        .coupon-hero h1 { display:flex;align-items:center;gap:11px;margin:0;font-size:clamp(23px,3vw,32px);letter-spacing:-.7px; }
        .coupon-hero h1 i { display:grid;place-items:center;width:44px;height:44px;border:1px solid rgba(255,255,255,.24);border-radius:14px;background:rgba(255,255,255,.12);font-size:23px; }
        .coupon-hero p { max-width:580px;margin:10px 0 0;color:rgba(255,255,255,.77);font-size:13px;line-height:1.7; }
        .coupon-ticket { position:relative;display:grid;place-items:center;width:144px;height:112px;border:1px solid rgba(255,255,255,.37);border-radius:20px;background:linear-gradient(145deg,rgba(255,255,255,.2),rgba(255,255,255,.07));box-shadow:inset 0 1px rgba(255,255,255,.25),0 14px 32px rgba(4,37,55,.18);transform:rotate(5deg); }
        .coupon-ticket:before,.coupon-ticket:after { position:absolute;top:50%;width:16px;height:16px;border:1px solid rgba(255,255,255,.35);border-radius:50%;background:#146b80;content:"";transform:translateY(-50%); }
        .coupon-ticket:before { left:-9px; }.coupon-ticket:after { right:-9px; }
        .coupon-ticket i { color:#c3fbf6;font-size:34px; }.coupon-ticket span { color:#fff;font-size:9px;font-weight:800;letter-spacing:.18em; }
        .coupon-stats { display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:13px; }
        .coupon-stat { display:flex;align-items:center;gap:13px;min-width:0;padding:16px 18px;border:1px solid var(--border);border-radius:17px;background:linear-gradient(145deg,var(--card),rgba(62,197,224,.025));box-shadow:0 7px 18px rgba(16,24,40,.035); }
        .coupon-stat-icon { display:grid;place-items:center;flex:0 0 42px;width:42px;height:42px;border-radius:14px;background:#e3f7f7;color:#087b83;font-size:20px; }.coupon-stat:nth-child(2) .coupon-stat-icon{background:#e9e5ff;color:#6546bd}.coupon-stat:nth-child(3) .coupon-stat-icon{background:#fff1d9;color:#b77912}
        .coupon-stat strong { display:block;color:var(--text);font-size:20px;line-height:1.2; }.coupon-stat span:last-child { display:block;margin-top:4px;color:var(--muted);font-size:10px; }
        .coupon-layout { grid-template-columns:minmax(0,1fr);align-items:start; }
        .coupon-layout > .card { min-width:0; }
        .coupon-panel-heading { display:flex;align-items:flex-start;gap:12px;margin-bottom:20px; }
        .coupon-panel-icon { display:grid;place-items:center;flex:0 0 40px;width:40px;height:40px;border-radius:13px;background:linear-gradient(145deg,#e1f7f6,#d9f1fb);color:#087b83;font-size:19px; }
        .coupon-panel-heading h2 { margin:0;color:var(--text);font-size:16px;letter-spacing:-.2px; }
        .coupon-panel-heading p { margin:5px 0 0;color:var(--muted);font-size:11px;line-height:1.55; }
        .coupon-create-grid,.coupon-edit-grid { grid-template-columns:repeat(2,minmax(0,1fr));gap:13px; }
        .coupon-create-grid label > .field-label,.coupon-edit-grid label > .field-label { display:block;margin-bottom:6px;color:var(--muted);font-size:11px;font-weight:700; }
        .coupon-create-grid input:not([type=checkbox]),.coupon-create-grid select,.coupon-edit-grid input:not([type=checkbox]),.coupon-edit-grid select { width:100%;min-width:0;min-height:42px;border-radius:11px; }
        .coupon-create-grid input[type="datetime-local"],.coupon-edit-grid input[type="datetime-local"] { padding:10px 9px;font-size:clamp(10px, .78vw, 12px); }
        .coupon-check { display:flex!important;align-items:center;gap:9px;min-height:42px;margin:0;padding:0 12px;border:1px solid var(--border);border-radius:11px;background:var(--bg);color:var(--text)!important;font-size:11px;font-weight:700; }
        .coupon-check input { width:17px;height:17px;margin:0;accent-color:var(--brand); }
        .coupon-availability { grid-column:1/-1; }
        .coupon-availability .coupon-check { width:max-content;max-width:100%;padding:0 14px; }
        .coupon-filter-form { display:grid;grid-template-columns:minmax(150px,1fr) minmax(130px,.45fr) auto;align-items:end;gap:9px;padding:14px;margin-bottom:16px;border:1px solid var(--border);border-radius:15px;background:var(--bg); }
        .coupon-filter-form .field { min-width:0;margin:0; }.coupon-filter-form .field > span { display:block;margin:0 0 6px;color:var(--muted);font-size:10px;font-weight:800;letter-spacing:.04em; }
        .coupon-results-line { display:flex;justify-content:space-between;align-items:center;gap:10px;margin:0 0 12px;color:var(--muted);font-size:11px; }.coupon-results-line strong{color:var(--text)}
        .coupon-item { position:relative;margin-top:12px;padding:18px;border:1px solid var(--border);border-radius:17px;background:linear-gradient(145deg,var(--card),rgba(62,197,224,.04));box-shadow:0 5px 15px rgba(16,24,40,.025);transition:border-color .2s,transform .2s,box-shadow .2s; }
        .coupon-item:hover { transform:translateY(-1px);border-color:rgba(62,197,224,.45);box-shadow:0 11px 25px rgba(16,24,40,.06); }
        .coupon-item-header { display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:15px; }
        .coupon-code-wrap { display:flex;align-items:center;gap:10px;min-width:0; }
        .coupon-code-icon { display:grid;place-items:center;width:36px;height:36px;flex:none;border:1px dashed rgba(14,90,117,.28);border-radius:11px;background:rgba(62,197,224,.1);color:var(--brand);font-size:17px; }
        .coupon-code { display:inline-flex;align-items:center;gap:8px;color:var(--brand);font-family:ui-monospace,Consolas,monospace;font-size:15px;font-weight:800;letter-spacing:.05em; }
        .coupon-item-foot { display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-top:13px;padding-top:13px;border-top:1px solid var(--border);color:var(--muted);font-size:11px; }
        .coupon-foot-detail { display:flex;align-items:center;gap:7px; }.coupon-foot-detail i{color:var(--brand);font-size:15px}
        .coupon-item-actions { display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-left:auto; }
        .coupon-action { min-height:36px;padding:7px 12px;font-size:11px; }
        .coupon-delete-form { display:inline-flex;margin:0; }
        .coupon-dialog { width:min(650px,calc(100% - 28px));max-width:calc(100% - 28px);max-height:min(88vh,820px);overflow:auto;padding:0;border:1px solid var(--border);border-radius:22px;background:var(--card);color:var(--text);box-shadow:0 28px 90px rgba(10,32,50,.28); }
        .coupon-dialog[open] { position:fixed;inset:50% auto auto 50%;margin:0;transform:translate(-50%,-50%); }
        .coupon-dialog::backdrop { background:rgba(7,24,33,.58);backdrop-filter:blur(4px); }
        .coupon-dialog-head { display:flex;align-items:flex-start;justify-content:space-between;gap:16px;padding:22px 24px 16px;border-bottom:1px solid var(--border); }
        .coupon-dialog-head h2 { margin:0;color:var(--text);font-size:18px;letter-spacing:-.3px; }
        .coupon-dialog-head p { margin:5px 0 0;color:var(--muted);font-size:11px;line-height:1.5; }
        .coupon-dialog-close { display:grid;place-items:center;flex:none;width:36px;height:36px;border:1px solid var(--border);border-radius:11px;background:var(--bg);color:var(--muted);font-size:18px;cursor:pointer; }
        .coupon-dialog-form { display:grid;gap:18px;padding:20px 24px 24px; }
        .coupon-dialog-form .coupon-create-grid,.coupon-dialog-form .coupon-edit-grid { grid-template-columns:repeat(2,minmax(0,1fr));gap:14px; }
        .coupon-dialog-actions { display:flex;justify-content:flex-end;gap:9px;padding-top:15px;border-top:1px solid var(--border); }
        .coupon-dialog-actions .button { min-height:42px;padding:0 16px; }
        .coupon-count { color:var(--muted);font-size:12px; }
        .coupon-submit { min-height:43px;padding:0 17px;border-radius:11px!important;box-shadow:0 7px 16px rgba(14,90,117,.16); }
        .coupon-empty { display:grid;place-items:center;gap:6px;padding:38px 20px;border:1px dashed var(--border);border-radius:16px;background:var(--bg);text-align:center; }.coupon-empty i{color:#3eb7c4;font-size:29px}.coupon-empty strong{color:var(--text);font-size:13px}.coupon-empty span{max-width:300px;color:var(--muted);font-size:11px;line-height:1.6}
        @media(max-width:1050px) { .coupon-create-grid input[type="datetime-local"],.coupon-edit-grid input[type="datetime-local"]{font-size:12px}.coupon-ticket{width:120px;height:96px} }
        @media(max-width:650px) { .coupon-hero{grid-template-columns:1fr;padding:23px 20px}.coupon-ticket{position:absolute;right:21px;top:20px;width:68px;height:61px;border-radius:14px}.coupon-ticket i{font-size:22px}.coupon-ticket span{display:none}.coupon-ticket:before,.coupon-ticket:after{width:12px;height:12px}.coupon-hero-copy{padding-right:52px}.coupon-stats{grid-template-columns:1fr;gap:9px}.coupon-stat{padding:12px 14px}.coupon-stat-icon{width:38px;height:38px;flex-basis:38px}.coupon-stat strong{font-size:17px}.coupon-dialog-head{padding:18px 18px 14px}.coupon-dialog-form{padding:16px 18px 18px}.coupon-dialog-form .coupon-create-grid,.coupon-dialog-form .coupon-edit-grid,.coupon-filter-form{grid-template-columns:1fr}.coupon-dialog-actions{flex-direction:column-reverse}.coupon-dialog-actions .button{width:100%}.coupon-results-line{align-items:flex-start;flex-direction:column}.coupon-item-foot{align-items:flex-start;flex-direction:column}.coupon-item-actions{margin-left:0;width:100%}.coupon-action{flex:1} }
    </style>
    @php
        $couponPage = $coupons->getCollection();
        $activeCouponCount = $couponPage->filter(fn ($coupon) => $coupon->is_active && $coupon->starts_at->isPast() && $coupon->ends_at->isFuture() && $coupon->uses_count + $coupon->reserved_count < $coupon->usage_limit)->count();
        $couponRedemptionCount = $couponPage->sum('uses_count');
    @endphp
    <div class="coupon-page">
    <section class="coupon-hero">
        <div class="coupon-hero-copy">
            <span class="coupon-eyebrow"><i class="ri-sparkling-2-line"></i> Offers &amp; loyalty</span>
            <h1><i class="ri-coupon-3-line"></i> Coupons</h1>
            <p>Create thoughtful promotions, control redemption limits, and schedule exactly when every offer becomes available at checkout.</p>
            <div class="coupon-hero-actions"><button class="button coupon-create-trigger" type="button" data-open-coupon-create><i class="ri-add-line"></i> Create an offer</button></div>
        </div>
        <div class="coupon-ticket" aria-hidden="true"><i class="ri-coupon-3-fill"></i><span>MADE TO SAVE</span></div>
    </section>

    <section class="coupon-stats" aria-label="Coupon overview">
        <article class="coupon-stat"><span class="coupon-stat-icon"><i class="ri-coupon-2-line"></i></span><div><strong>{{ number_format($coupons->total()) }}</strong><span>Coupons matching your view</span></div></article>
        <article class="coupon-stat"><span class="coupon-stat-icon"><i class="ri-flashlight-line"></i></span><div><strong>{{ number_format($activeCouponCount) }}</strong><span>Available on this page</span></div></article>
        <article class="coupon-stat"><span class="coupon-stat-icon"><i class="ri-shopping-bag-3-line"></i></span><div><strong>{{ number_format($couponRedemptionCount) }}</strong><span>Redemptions on this page</span></div></article>
    </section>

    <section class="coupon-layout">
        <section class="card">
            <div class="coupon-panel-heading"><span class="coupon-panel-icon"><i class="ri-ticket-2-line"></i></span><div><h2>Offer library</h2><p>Manage discount values, schedules, and remaining redemptions.</p></div></div>
            <form method="GET" class="coupon-filter-form">
                <label class="field" style="flex:1 1 220px"><span>Search coupon code</span><input type="search" name="search" value="{{ request('search') }}" placeholder="Enter coupon code"></label>
                <label class="field"><span>Rows per page</span><select name="per_page" onchange="this.form.submit()">@foreach([10,25,50] as $pageSize)<option value="{{ $pageSize }}" @selected((int) request('per_page', 10) === $pageSize)>{{ $pageSize }} per page</option>@endforeach</select></label>
                <button class="button primary" type="submit"><i class="ri-search-line"></i> Search</button>
                @if(request()->hasAny(['search','per_page']))<a class="button" href="{{ route('admin.ecommerce.coupons') }}">Clear filters</a>@endif
            </form>
            <p class="coupon-results-line"><span>Showing <strong>{{ number_format($coupons->count()) }}</strong> of <strong>{{ number_format($coupons->total()) }}</strong> matching offers</span><span>Page {{ $coupons->currentPage() }} of {{ max(1, $coupons->lastPage()) }}</span></p>
            @forelse($coupons as $coupon)
                <article class="coupon-item">
                    <div class="coupon-item-header">
                        <div class="coupon-code-wrap"><span class="coupon-code-icon"><i class="ri-coupon-3-line" aria-hidden="true"></i></span><strong class="coupon-code">{{ $coupon->code }}</strong></div>
                        <span class="badge">{{ $coupon->uses_count }}/{{ $coupon->usage_limit }} redeemed · {{ $coupon->reserved_count }} reserved</span>
                    </div>
                    <div class="coupon-item-foot">
                        <span class="coupon-foot-detail"><i class="ri-calendar-event-line"></i>{{ $coupon->starts_at->format('M j, Y g:i A') }} – {{ $coupon->ends_at->format('M j, Y g:i A') }}</span>
                        <span class="coupon-foot-detail"><i class="ri-price-tag-3-line"></i>{{ $coupon->discount_type === 'percent' ? number_format($coupon->discount_value, 2).'%' : '£'.number_format($coupon->discount_value, 2) }}</span>
                        <div class="coupon-item-actions">
                            <button class="button coupon-action" type="button" data-open-coupon-edit="coupon-edit-{{ $coupon->id }}"><i class="ri-pencil-line"></i> Edit</button>
                            <form class="coupon-delete-form" method="POST" action="{{ route('admin.ecommerce.coupons.destroy', $coupon) }}" data-confirm-modal data-confirm-title="Delete this coupon?" data-confirm-message="Customers will no longer be able to redeem this coupon.">@csrf @method('DELETE')<button class="button danger coupon-action" type="submit"><i class="ri-delete-bin-line"></i> Delete</button></form>
                        </div>
                    </div>
                </article>
                <dialog class="coupon-dialog" id="coupon-edit-{{ $coupon->id }}" aria-labelledby="coupon-edit-title-{{ $coupon->id }}">
                    <div class="coupon-dialog-head"><div><h2 id="coupon-edit-title-{{ $coupon->id }}">Edit offer</h2><p>Update the value, schedule, redemption limit, or checkout status for {{ $coupon->code }}.</p></div><button class="coupon-dialog-close" type="button" data-close-coupon aria-label="Close dialog"><i class="ri-close-line"></i></button></div>
                    <form method="POST" action="{{ route('admin.ecommerce.coupons.update', $coupon) }}" class="coupon-dialog-form">
                        @csrf @method('PATCH')
                        <div class="form-grid coupon-edit-grid">
                            <label class="field"><span class="field-label">Discount type</span><select name="discount_type" required><option value="percent" @selected($coupon->discount_type === 'percent')>Percentage</option><option value="fixed" @selected($coupon->discount_type === 'fixed')>Fixed amount (£)</option></select></label>
                            <label class="field"><span class="field-label">Discount value</span><input type="number" name="discount_value" min="0.01" step="0.01" required value="{{ $coupon->discount_value }}"></label>
                            <label class="field"><span class="field-label">Maximum uses</span><input type="number" name="usage_limit" min="{{ max(1, $coupon->uses_count + $coupon->reserved_count) }}" required value="{{ $coupon->usage_limit }}"></label>
                            <label class="field"><span class="field-label">Starts at</span><input type="datetime-local" name="starts_at" required value="{{ $coupon->starts_at->format('Y-m-d\\TH:i') }}"></label>
                            <label class="field"><span class="field-label">Ends at</span><input type="datetime-local" name="ends_at" required value="{{ $coupon->ends_at->format('Y-m-d\\TH:i') }}"></label>
                            <label class="field coupon-availability"><span class="field-label">Availability</span><span class="coupon-check"><input type="checkbox" name="is_active" value="1" @checked($coupon->is_active)> Active at checkout</span></label>
                        </div>
                        <div class="coupon-dialog-actions"><button class="button" type="button" data-close-coupon>Cancel</button><button class="button primary coupon-submit" type="submit"><i class="ri-save-line"></i> Save changes</button></div>
                    </form>
                </dialog>
            @empty<div class="coupon-empty"><i class="ri-coupon-3-line"></i><strong>No offers found</strong><span>Create your first coupon or adjust the search filters to find an existing promotion.</span></div>@endforelse
            @if($coupons->hasPages())<div style="margin-top:16px">{{ $coupons->links() }}</div>@endif
        </section>
    </section>
    </div>
    <dialog class="coupon-dialog" id="coupon-create-dialog" aria-labelledby="coupon-create-title">
        <div class="coupon-dialog-head"><div><h2 id="coupon-create-title">Create an offer</h2><p>Choose the discount and schedule. We’ll generate a unique code automatically.</p></div><button class="coupon-dialog-close" type="button" data-close-coupon aria-label="Close dialog"><i class="ri-close-line"></i></button></div>
        <form method="POST" action="{{ route('admin.ecommerce.coupons.store') }}" class="coupon-dialog-form">
            @csrf
            <div class="form-grid coupon-create-grid">
                <label class="field"><span class="field-label">Discount type</span><select name="discount_type" required><option value="percent" @selected(old('discount_type', 'percent') === 'percent')>Percentage</option><option value="fixed" @selected(old('discount_type') === 'fixed')>Fixed amount (£)</option></select></label>
                <label class="field"><span class="field-label">Discount value</span><input type="number" name="discount_value" min="0.01" step="0.01" required value="{{ old('discount_value') }}" placeholder="e.g. 15"></label>
                <label class="field"><span class="field-label">Maximum uses</span><input type="number" name="usage_limit" min="1" step="1" required value="{{ old('usage_limit', 100) }}"></label>
                <label class="field"><span class="field-label">Starts at</span><input type="datetime-local" name="starts_at" required value="{{ old('starts_at', now()->format('Y-m-d\\TH:i')) }}"></label>
                <label class="field"><span class="field-label">Ends at</span><input type="datetime-local" name="ends_at" required value="{{ old('ends_at', now()->addMonth()->format('Y-m-d\\TH:i')) }}"></label>
                <label class="field coupon-availability"><span class="field-label">Availability</span><span class="coupon-check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))> Enable at checkout</span></label>
            </div>
            <div class="coupon-dialog-actions"><button class="button" type="button" data-close-coupon>Cancel</button><button class="button primary coupon-submit" type="submit"><i class="ri-sparkling-line"></i> Generate coupon</button></div>
        </form>
    </dialog>
    @push('scripts')
    <script>
        (function () {
            var createDialog = document.getElementById('coupon-create-dialog');
            document.addEventListener('click', function (event) {
                var createButton = event.target.closest('[data-open-coupon-create]');
                if (createButton && createDialog) { createDialog.showModal(); return; }
                var editButton = event.target.closest('[data-open-coupon-edit]');
                if (editButton) {
                    var editDialog = document.getElementById(editButton.dataset.openCouponEdit);
                    if (editDialog) editDialog.showModal();
                    return;
                }
                var closeButton = event.target.closest('[data-close-coupon]');
                if (closeButton) { closeButton.closest('dialog').close(); return; }
                if (event.target instanceof HTMLDialogElement && event.target.classList.contains('coupon-dialog')) event.target.close();
            });
        })();
    </script>
    @endpush
@endsection
