@extends('admin.layouts.ecommerce')

@section('title', 'Taxes')
@section('content')
    <style>
        .tax-page { display:grid;align-content:start;gap:20px;min-width:0; }
        .tax-hero { position:relative;isolation:isolate;display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:24px;overflow:hidden;padding:30px 32px;border:1px solid rgba(255,255,255,.14);border-radius:22px;background:radial-gradient(ellipse at 84% -6%,rgba(246,202,125,.32),transparent 35%),linear-gradient(120deg,#26395c 0%,#354c78 52%,#5868a4 100%);color:#fff;box-shadow:0 20px 45px rgba(38,57,92,.2); }
        .tax-hero:after { position:absolute;z-index:-1;right:163px;bottom:-160px;width:300px;height:300px;border:1px solid rgba(255,255,255,.14);border-radius:50%;content:"";box-shadow:0 0 0 25px rgba(255,255,255,.035),0 0 0 52px rgba(255,255,255,.025); }
        .tax-hero-copy { max-width:650px; }
        .tax-hero-actions { display:flex;align-items:center;gap:10px;margin-top:17px; }
        .tax-create-trigger { min-height:42px;padding:0 16px;border:1px solid rgba(255,255,255,.72);border-radius:12px;background:#fff;color:#354c78;font-size:12px;font-weight:800;box-shadow:0 8px 20px rgba(10,24,53,.16); }
        .tax-create-trigger:hover { background:#fff8e9;transform:translateY(-1px); }
        .tax-eyebrow { display:inline-flex;align-items:center;gap:7px;margin-bottom:11px;color:#f6d994;font-size:10px;font-weight:800;letter-spacing:.16em;text-transform:uppercase; }
        .tax-hero h1 { display:flex;align-items:center;gap:11px;margin:0;font-size:clamp(23px,3vw,32px);letter-spacing:-.7px; }
        .tax-hero h1 i { display:grid;place-items:center;width:44px;height:44px;border:1px solid rgba(255,255,255,.24);border-radius:14px;background:rgba(255,255,255,.12);font-size:23px; }
        .tax-hero p { max-width:580px;margin:10px 0 0;color:rgba(255,255,255,.77);font-size:13px;line-height:1.7; }
        .tax-orbit { position:relative;display:grid;place-items:center;width:130px;height:130px;border:1px solid rgba(255,255,255,.28);border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,.15),rgba(255,255,255,.035) 65%);box-shadow:inset 0 0 0 10px rgba(255,255,255,.035),0 12px 30px rgba(10,24,53,.15); }
        .tax-orbit:before,.tax-orbit:after { position:absolute;inset:10px;border:1px dashed rgba(255,255,255,.23);border-radius:50%;content:"";transform:rotate(35deg) scaleX(.48); }.tax-orbit:after{transform:rotate(-35deg) scaleX(.48)}
        .tax-orbit i { color:#ffe4a4;font-size:39px;filter:drop-shadow(0 5px 12px rgba(0,0,0,.18)); }
        .tax-stats { display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:13px; }
        .tax-stat { display:flex;align-items:center;gap:13px;min-width:0;padding:16px 18px;border:1px solid var(--border);border-radius:17px;background:linear-gradient(145deg,var(--card),rgba(113,94,190,.025));box-shadow:0 7px 18px rgba(16,24,40,.035); }
        .tax-stat-icon { display:grid;place-items:center;flex:0 0 42px;width:42px;height:42px;border-radius:14px;background:#eeebff;color:#6749bd;font-size:20px; }.tax-stat:nth-child(2) .tax-stat-icon{background:#dcf7eb;color:#108255}.tax-stat:nth-child(3) .tax-stat-icon{background:#fff1d9;color:#b77912}
        .tax-stat strong { display:block;color:var(--text);font-size:20px;line-height:1.2; }.tax-stat span:last-child { display:block;margin-top:4px;color:var(--muted);font-size:10px; }
        .tax-layout { grid-template-columns:minmax(0,1fr);align-items:start; }
        .tax-layout > .card { min-width:0; }
        .tax-panel-heading { display:flex;align-items:flex-start;gap:12px;margin-bottom:20px; }
        .tax-panel-icon { display:grid;place-items:center;flex:0 0 40px;width:40px;height:40px;border-radius:13px;background:linear-gradient(145deg,#eeebff,#e2efff);color:#6749bd;font-size:19px; }
        .tax-panel-heading h2 { margin:0;color:var(--text);font-size:16px;letter-spacing:-.2px; }
        .tax-panel-heading p { margin:5px 0 0;color:var(--muted);font-size:11px;line-height:1.55; }
        .tax-create-grid { grid-template-columns:repeat(2,minmax(0,1fr));gap:14px; }
        .tax-create-grid label > .field-label,.tax-edit-form label > .field-label { display:block;margin-bottom:6px;color:var(--muted);font-size:11px;font-weight:700; }
        .tax-create-grid input:not([type=checkbox]),.tax-edit-form input:not([type=checkbox]) { width:100%;min-width:0;min-height:43px;border-radius:11px; }
        .tax-check { display:flex!important;align-items:center;gap:9px;min-height:42px;margin:0;padding:0 12px;border:1px solid var(--border);border-radius:11px;background:var(--bg);color:var(--text)!important;font-size:11px;font-weight:700; }.tax-check input{width:17px;height:17px;margin:0;accent-color:var(--brand)}
        .tax-availability { grid-column:1/-1; }
        .tax-availability .tax-check { width:max-content;max-width:100%;padding:0 14px; }
        .tax-create-note { display:flex;align-items:flex-start;gap:9px;margin:17px 0 0;padding:12px;border:1px solid rgba(103,73,189,.13);border-radius:12px;background:rgba(103,73,189,.055);color:var(--muted);font-size:10px;line-height:1.6; }.tax-create-note i{color:#6749bd;font-size:16px}
        .tax-filter-form { display:grid;grid-template-columns:minmax(160px,1fr) minmax(130px,.45fr) auto;align-items:end;gap:9px;margin-bottom:16px;padding:14px;border:1px solid var(--border);border-radius:15px;background:var(--bg); }
        .tax-filter-form .field { min-width:0;margin:0; }.tax-filter-form .field > span{display:block;margin:0 0 6px;color:var(--muted);font-size:10px;font-weight:800}
        .tax-results-line { display:flex;justify-content:space-between;align-items:center;gap:10px;margin:0 0 12px;color:var(--muted);font-size:11px; }.tax-results-line strong{color:var(--text)}
        .tax-item { margin-top:12px;padding:18px;border:1px solid var(--border);border-radius:17px;background:linear-gradient(145deg,var(--card),rgba(103,73,189,.035));box-shadow:0 5px 15px rgba(16,24,40,.025);transition:border-color .2s,transform .2s,box-shadow .2s; }.tax-item:hover{transform:translateY(-1px);border-color:rgba(103,73,189,.3);box-shadow:0 11px 25px rgba(16,24,40,.06)}
        .tax-item-top { display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px; }
        .tax-name-wrap { display:flex;align-items:center;gap:10px;min-width:0; }.tax-name-icon{display:grid;place-items:center;width:36px;height:36px;flex:none;border:1px solid rgba(103,73,189,.13);border-radius:11px;background:rgba(103,73,189,.08);color:#6749bd;font-size:17px}.tax-name-wrap strong{overflow:hidden;color:var(--text);font-size:13px;text-overflow:ellipsis;white-space:nowrap}
        .tax-rate-pill { flex:none;padding:7px 11px;border:1px solid rgba(103,73,189,.14);border-radius:999px;background:rgba(103,73,189,.07);color:#6749bd;font-size:12px;font-weight:800; }
        .tax-actions { display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-top:14px;padding-top:13px;border-top:1px solid var(--border); }
        .tax-action-note { display:flex;align-items:center;gap:7px;color:var(--muted);font-size:11px; }.tax-action-note i{color:#6749bd;font-size:15px}
        .tax-action-buttons { display:flex;align-items:center;gap:8px;flex-wrap:wrap; }
        .tax-action { min-height:36px;padding:7px 12px;font-size:11px; }
        .tax-delete-form { display:inline-flex;margin:0; }
        .tax-dialog { width:min(540px,calc(100% - 28px));max-width:calc(100% - 28px);max-height:min(88vh,760px);overflow:auto;padding:0;border:1px solid var(--border);border-radius:22px;background:var(--card);color:var(--text);box-shadow:0 28px 90px rgba(10,32,50,.28); }
        .tax-dialog[open] { position:fixed;inset:50% auto auto 50%;margin:0;transform:translate(-50%,-50%); }
        .tax-dialog::backdrop { background:rgba(12,22,43,.58);backdrop-filter:blur(4px); }
        .tax-dialog-head { display:flex;align-items:flex-start;justify-content:space-between;gap:16px;padding:22px 24px 16px;border-bottom:1px solid var(--border); }
        .tax-dialog-head h2 { margin:0;color:var(--text);font-size:18px;letter-spacing:-.3px; }
        .tax-dialog-head p { margin:5px 0 0;color:var(--muted);font-size:11px;line-height:1.5; }
        .tax-dialog-close { display:grid;place-items:center;flex:none;width:36px;height:36px;border:1px solid var(--border);border-radius:11px;background:var(--bg);color:var(--muted);font-size:18px;cursor:pointer; }
        .tax-dialog-form { display:grid;gap:16px;padding:20px 24px 24px; }
        .tax-dialog-form .tax-create-grid,.tax-dialog-form .tax-edit-form { grid-template-columns:repeat(2,minmax(0,1fr));gap:14px; }
        .tax-dialog-form .tax-edit-form { display:grid; }
        .tax-dialog-actions { display:flex;justify-content:flex-end;gap:9px;padding-top:15px;border-top:1px solid var(--border); }
        .tax-dialog-actions .button { min-height:42px;padding:0 16px; }
        .tax-empty { display:grid;place-items:center;gap:6px;padding:38px 20px;border:1px dashed var(--border);border-radius:16px;background:var(--bg);text-align:center; }.tax-empty i{color:#8872d2;font-size:29px}.tax-empty strong{color:var(--text);font-size:13px}.tax-empty span{max-width:310px;color:var(--muted);font-size:11px;line-height:1.6}
        .tax-submit { min-height:43px;padding:0 17px;border-radius:11px!important;box-shadow:0 7px 16px rgba(53,76,120,.15); }
        @media(max-width:1050px) { .tax-orbit{width:110px;height:110px} }
        @media(max-width:650px) { .tax-hero{grid-template-columns:1fr;padding:23px 20px}.tax-orbit{position:absolute;right:21px;top:20px;width:67px;height:67px}.tax-orbit i{font-size:25px}.tax-hero-copy{padding-right:48px}.tax-stats{grid-template-columns:1fr;gap:9px}.tax-stat{padding:12px 14px}.tax-stat-icon{width:38px;height:38px;flex-basis:38px}.tax-stat strong{font-size:17px}.tax-filter-form{grid-template-columns:1fr}.tax-dialog-head{padding:18px 18px 14px}.tax-dialog-form{padding:16px 18px 18px}.tax-dialog-form .tax-create-grid,.tax-dialog-form .tax-edit-form{grid-template-columns:1fr}.tax-availability{grid-column:auto}.tax-dialog-actions{flex-direction:column-reverse}.tax-dialog-actions .button{width:100%}.tax-results-line{align-items:flex-start;flex-direction:column}.tax-item-top{align-items:flex-start}.tax-actions{align-items:flex-start;flex-direction:column}.tax-action-buttons{width:100%}.tax-action{flex:1} }
    </style>
    @php
        $taxPage = $taxes->getCollection();
        $activeTaxCount = $taxPage->where('is_active', true)->count();
        $combinedTaxRate = $taxPage->where('is_active', true)->sum(fn ($tax) => (float) $tax->percentage);
        $highestTaxRate = $taxPage->max('percentage') ?? 0;
    @endphp
    <div class="tax-page">
    <section class="tax-hero">
        <div class="tax-hero-copy">
            <span class="tax-eyebrow"><i class="ri-shield-check-line"></i> Checkout rules</span>
            <h1><i class="ri-percent-line"></i> Tax centre</h1>
            <p>Keep every checkout transparent and consistent. Configure the percentage rates applied to eligible, discounted merchandise totals.</p>
            <div class="tax-hero-actions"><button class="button tax-create-trigger" type="button" data-open-tax-create><i class="ri-add-line"></i> Add a tax rule</button></div>
        </div>
        <div class="tax-orbit" aria-hidden="true"><i class="ri-scales-3-line"></i></div>
    </section>

    <section class="tax-stats" aria-label="Tax overview">
        <article class="tax-stat"><span class="tax-stat-icon"><i class="ri-list-check-3"></i></span><div><strong>{{ number_format($taxes->total()) }}</strong><span>Configured tax rules</span></div></article>
        <article class="tax-stat"><span class="tax-stat-icon"><i class="ri-toggle-line"></i></span><div><strong>{{ number_format($activeTaxCount) }}</strong><span>Active on this page</span></div></article>
        <article class="tax-stat"><span class="tax-stat-icon"><i class="ri-percent-line"></i></span><div><strong>{{ number_format($combinedTaxRate, 2) }}%</strong><span>Combined active rate on this page</span></div></article>
    </section>

    <section class="tax-layout">
        <section class="card">
            <div class="tax-panel-heading"><span class="tax-panel-icon"><i class="ri-equalizer-2-line"></i></span><div><h2>Tax rules</h2><p>Review each rate and control whether it applies at checkout.</p></div></div>
            <form method="GET" class="tax-filter-form">
                <label class="field" style="flex:1 1 220px"><span>Search taxes</span><input type="search" name="search" value="{{ request('search') }}" placeholder="GST, service tax..."></label>
                <label class="field"><span>Rows per page</span><select name="per_page" onchange="this.form.submit()">@foreach([10,25,50] as $pageSize)<option value="{{ $pageSize }}" @selected((int) request('per_page', 10) === $pageSize)>{{ $pageSize }} per page</option>@endforeach</select></label>
                <button class="button primary" type="submit"><i class="ri-search-line"></i> Search</button>
                @if(request()->hasAny(['search','per_page']))<a class="button" href="{{ route('admin.ecommerce.taxes') }}">Clear filters</a>@endif
            </form>
            <p class="tax-results-line"><span>Showing <strong>{{ number_format($taxes->count()) }}</strong> of <strong>{{ number_format($taxes->total()) }}</strong> matching rules</span><span>Highest rate on this page: <strong>{{ number_format((float) $highestTaxRate, 2) }}%</strong></span></p>
            @forelse($taxes as $tax)
                <article class="tax-item">
                    <div class="tax-item-top"><div class="tax-name-wrap"><span class="tax-name-icon"><i class="ri-government-line"></i></span><strong>{{ $tax->name }}</strong></div><span class="tax-rate-pill">{{ number_format((float) $tax->percentage, 4) }}%</span></div>
                    <div class="tax-actions">
                        <span class="tax-action-note"><i class="{{ $tax->is_active ? 'ri-checkbox-circle-line' : 'ri-pause-circle-line' }}"></i>{{ $tax->is_active ? 'Active at checkout' : 'Paused — not applied' }}</span>
                        <div class="tax-action-buttons">
                            <button class="button tax-action" type="button" data-open-tax-edit="tax-edit-{{ $tax->id }}"><i class="ri-pencil-line"></i> Edit rule</button>
                            <form class="tax-delete-form" method="POST" action="{{ route('admin.ecommerce.taxes.destroy', $tax) }}" data-confirm-modal data-confirm-title="Delete this tax rule?" data-confirm-message="This rate will no longer be added to checkout totals.">@csrf @method('DELETE')<button class="button danger tax-action" type="submit"><i class="ri-delete-bin-line"></i> Delete</button></form>
                        </div>
                    </div>
                </article>
                <dialog class="tax-dialog" id="tax-edit-{{ $tax->id }}" aria-labelledby="tax-edit-title-{{ $tax->id }}">
                    <div class="tax-dialog-head"><div><h2 id="tax-edit-title-{{ $tax->id }}">Edit tax rule</h2><p>Update the rate and whether it applies at checkout.</p></div><button class="tax-dialog-close" type="button" data-close-tax aria-label="Close dialog"><i class="ri-close-line"></i></button></div>
                    <form method="POST" action="{{ route('admin.ecommerce.taxes.update', $tax) }}" class="tax-dialog-form">
                        @csrf @method('PATCH')
                        <div class="form-grid tax-edit-form">
                            <label class="field"><span class="field-label">Rule name</span><input name="name" maxlength="120" required value="{{ $tax->name }}"></label>
                            <label class="field"><span class="field-label">Percentage rate</span><input type="number" name="percentage" min="0.0001" max="100" step="0.0001" required value="{{ $tax->percentage }}"></label>
                            <label class="field tax-availability"><span class="field-label">Checkout status</span><span class="tax-check"><input type="checkbox" name="is_active" value="1" @checked($tax->is_active)> Apply this rate at checkout</span></label>
                        </div>
                        <div class="tax-dialog-actions"><button class="button" type="button" data-close-tax>Cancel</button><button class="button primary tax-submit" type="submit"><i class="ri-save-line"></i> Save changes</button></div>
                    </form>
                </dialog>
            @empty<div class="tax-empty"><i class="ri-percent-line"></i><strong>No tax rules found</strong><span>Add a tax rule to configure checkout, or adjust your search to find an existing rate.</span></div>@endforelse
            @if($taxes->hasPages())<div style="margin-top:16px">{{ $taxes->links() }}</div>@endif
        </section>
    </section>
    </div>
    <dialog class="tax-dialog" id="tax-create-dialog" aria-labelledby="tax-create-title">
        <div class="tax-dialog-head"><div><h2 id="tax-create-title">Add a tax rule</h2><p>Give the rate a clear name and set the percentage applied at checkout.</p></div><button class="tax-dialog-close" type="button" data-close-tax aria-label="Close dialog"><i class="ri-close-line"></i></button></div>
        <form method="POST" action="{{ route('admin.ecommerce.taxes.store') }}" class="tax-dialog-form">
            @csrf
            <div class="form-grid tax-create-grid">
                <label class="field"><span class="field-label">Rule name</span><input name="name" maxlength="120" required value="{{ old('name') }}" placeholder="GST"></label>
                <label class="field"><span class="field-label">Percentage rate</span><input type="number" name="percentage" min="0.0001" max="100" step="0.0001" required value="{{ old('percentage') }}" placeholder="5"></label>
                <label class="field tax-availability"><span class="field-label">Checkout status</span><span class="tax-check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))> Apply this rate at checkout</span></label>
            </div>
            <div class="tax-create-note"><i class="ri-information-line"></i><span>Active tax rates are applied to discounted merchandise totals when an order is calculated.</span></div>
            <div class="tax-dialog-actions"><button class="button" type="button" data-close-tax>Cancel</button><button class="button primary tax-submit" type="submit"><i class="ri-add-line"></i> Create tax rule</button></div>
        </form>
    </dialog>
    @push('scripts')
    <script>
        (function () {
            var createDialog = document.getElementById('tax-create-dialog');
            document.addEventListener('click', function (event) {
                var createButton = event.target.closest('[data-open-tax-create]');
                if (createButton && createDialog) { createDialog.showModal(); return; }
                var editButton = event.target.closest('[data-open-tax-edit]');
                if (editButton) {
                    var editDialog = document.getElementById(editButton.dataset.openTaxEdit);
                    if (editDialog) editDialog.showModal();
                    return;
                }
                var closeButton = event.target.closest('[data-close-tax]');
                if (closeButton) { closeButton.closest('dialog').close(); return; }
                if (event.target instanceof HTMLDialogElement && event.target.classList.contains('tax-dialog')) event.target.close();
            });
        })();
    </script>
    @endpush
@endsection
