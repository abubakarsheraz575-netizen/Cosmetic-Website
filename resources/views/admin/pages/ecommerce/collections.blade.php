@extends('admin.layouts.ecommerce')

@section('title', 'Scheduled collections')
@section('content')
    <style>
        @keyframes collection-enter{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
        .collection-hero{display:flex;align-items:center;justify-content:space-between;gap:22px;padding:27px 30px;border:1px solid #d7e8f1;border-radius:22px;background:radial-gradient(circle at 90% 8%,#d7f7f5 0,transparent 30%),linear-gradient(125deg,#fff,#f1f8fc);box-shadow:0 14px 34px rgba(14,90,117,.07);animation:collection-enter .5s ease-out both}
        .collection-hero-copy{max-width:720px}.collection-eyebrow{display:inline-flex;align-items:center;gap:7px;margin-bottom:9px;color:#0e8090;font-size:10px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}
        .collection-hero h1{display:flex;align-items:center;gap:11px;margin:0;color:#123744;font-size:clamp(22px,2.5vw,29px);letter-spacing:-.5px}.collection-hero h1 i{display:grid;place-items:center;width:44px;height:44px;border-radius:14px;background:linear-gradient(135deg,#0e5a75,#249ab1);color:#fff;font-size:22px;box-shadow:0 8px 18px #0e5a7529}
        .collection-hero p{max-width:650px;margin:9px 0 0;color:#617785;font-size:12px;line-height:1.7}
        .collection-hero-actions{display:flex;align-items:center;gap:12px;flex:none}.collection-total{padding:9px 12px;border:1px solid #dbe9ee;border-radius:999px;background:#ffffffc9;color:#0e5a75;font-size:11px;font-weight:800;white-space:nowrap}
        .collection-create-link{min-height:42px;padding:0 16px;border-radius:12px!important;box-shadow:0 8px 18px rgba(14,90,117,.18)}
        .collection-layout{display:grid;gap:18px;min-width:0}.collection-layout>.card{animation:collection-enter .5s .08s ease-out both}
        .collection-record{border-color:#e3edf1;border-radius:16px;box-shadow:0 5px 15px rgba(16,24,40,.035);transition:transform .2s,border-color .2s,box-shadow .2s}.collection-record:hover{transform:translateY(-2px);border-color:#b9e1e7;box-shadow:0 13px 28px rgba(14,90,117,.09)}
        .collection-record-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px}.collection-record-title{min-width:0}.collection-record-title strong{display:block;color:var(--text);font-size:14px;letter-spacing:-.1px}.collection-record-title small{display:block;margin-top:4px;color:var(--muted);font-size:11px}
        .collection-delete{flex:none}.collection-list-empty{padding:36px 20px;border:1px dashed var(--border);border-radius:16px;background:var(--bg);color:var(--muted);text-align:center}
        .collection-schedule-card{display:grid;gap:9px;margin-top:12px;padding:12px;border:1px solid var(--border);border-radius:11px;background:var(--bg)}
        .collection-schedule-dates{display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap;color:var(--muted);font-size:10px}
        .collection-time-left{display:flex;align-items:center;gap:7px;color:var(--brand);font-size:11px;font-weight:800}
        .collection-time-left i{font-size:15px}.collection-time-left.is-ended{color:#9a4b4b}
        .collection-visibility-note{margin:0;color:var(--muted);font-size:11px;line-height:1.5}
        .collection-product-status{display:block;margin-top:3px;color:var(--muted);font-size:10px}
        .collection-product-status.is-hidden{color:#9a4b4b}
        .dt-product-chip .collection-product-status{margin:0 0 0 2px}
        @media(max-width:800px){.collection-hero{align-items:flex-start;flex-direction:column}.collection-hero-actions{width:100%;justify-content:space-between}}
        @media(max-width:520px){.collection-hero{padding:20px}.collection-hero-actions{align-items:stretch;flex-direction:column}.collection-create-link{width:100%}.collection-record-head{align-items:stretch;flex-direction:column}.collection-delete{width:100%}}
        @media(prefers-reduced-motion:reduce){.collection-hero,.collection-layout>.card{animation:none}.collection-record{transition:none}}
    </style>
    <section class="collection-hero">
        <div class="collection-hero-copy"><span class="collection-eyebrow"><i class="ri-calendar-check-line"></i> Merchandising planner</span><h1><i class="ri-calendar-event-line"></i> Scheduled collections</h1><p>Collections appear on the homepage only while scheduled. Curate time-bound product stories, and note that only approved, published products are shown.</p></div>
        <div class="collection-hero-actions"><span class="collection-total">{{ $collections->count() }} {{ \Illuminate\Support\Str::plural('collection', $collections->count()) }}</span><a class="button primary collection-create-link" href="{{ route('admin.ecommerce.collections.create') }}"><i class="ri-add-line"></i> Schedule a collection</a></div>
    </section>
    <section class="collection-layout">
        <section class="card">
            <div class="toolbar"><div><h2 class="card-title" style="margin:0">Your schedule</h2><p class="card-intro" style="margin:5px 0 0">Review the active window and storefront readiness of every collection.</p></div></div>
            <div class="dt-cards">
            @forelse($collections as $collection)
                <article class="dt-record collection-record">
                    <div class="collection-record-head"><div class="collection-record-title"><strong>{{ $collection->name }}</strong><small>{{ $collection->products_count }} {{ \Illuminate\Support\Str::plural('product', $collection->products_count) }} selected</small></div>
                        <form method="POST" action="{{ route('admin.ecommerce.collections.destroy', $collection) }}" data-confirm-modal data-confirm-title="Delete this collection?" data-confirm-message="This collection will be permanently removed.">@csrf @method('DELETE')<button class="button danger collection-delete" type="submit"><i class="ri-delete-bin-line"></i> Delete collection</button></form>
                    </div>
                    <div class="collection-schedule-card">
                        <div class="collection-schedule-dates"><span><i class="ri-calendar-event-line"></i> Starts <time data-collection-local-date="{{ $collection->starts_at->toIso8601String() }}"></time></span><span><i class="ri-calendar-check-line"></i> Ends <time data-collection-local-date="{{ $collection->ends_at->toIso8601String() }}"></time></span></div>
                        <span class="collection-time-left" data-collection-start="{{ $collection->starts_at->toIso8601String() }}" data-collection-end="{{ $collection->ends_at->toIso8601String() }}"><i class="ri-timer-line"></i><span>Checking schedule…</span></span>
                        <p class="collection-visibility-note">Only approved, published products appear in this homepage section during the scheduled dates.</p>
                    </div>
                    @if($collection->description)<p style="font-size:12px;color:var(--muted)">{{ $collection->description }}</p>@endif
                    <div class="dt-products">
                        @foreach($collection->products as $product)
                            @php($productVisible = $product->is_active && $product->review_status === 'approved')
                            <span class="dt-product-chip">
                                <span class="product-thumb">@if($product->images->first())<img src="{{ asset('storage/'.$product->images->first()->path) }}" alt="">@else{{ $product->emoji ?: '✦' }}@endif</span>
                                {{ $product->name }}
                                @unless($productVisible)
                                    <span class="collection-product-status">{{ $product->review_status === 'approved' ? 'Approved · unpublished' : ucfirst($product->review_status).' review' }}</span>
                                    <span class="collection-product-status is-hidden">Not shown on storefront</span>
                                @endunless
                            </span>
                        @endforeach
                    </div>
                </article>
            @empty<div class="collection-list-empty"><i class="ri-calendar-event-line" style="font-size:28px;color:#1688a0"></i><strong style="display:block;margin:8px 0;color:var(--text)">No collections scheduled yet</strong><span>Create your first time-bound collection to feature products on the storefront.</span></div>@endforelse
            </div>
        </section>
    </section>
    @push('scripts')
    <script>
        (function () {
            var collectionForm = document.querySelector('form[action="{{ route('admin.ecommerce.collections.store') }}"]');
            if (collectionForm) {
                var localInputs = [
                    collectionForm.querySelector('#collectionStartsAt'),
                    collectionForm.querySelector('#collectionEndsAt')
                ];
                var utcInputs = collectionForm.querySelectorAll('[data-collection-utc]');
                localInputs.forEach(function (input, index) {
                    if (utcInputs[index].value) {
                        var previousValue = new Date(utcInputs[index].value);
                        if (!Number.isNaN(previousValue.getTime())) {
                            var localValue = new Date(previousValue.getTime() - previousValue.getTimezoneOffset() * 60000);
                            input.value = localValue.toISOString().slice(0, 16);
                        }
                    }
                });
                collectionForm.addEventListener('submit', function () {
                    localInputs.forEach(function (input, index) {
                        var localDate = new Date(input.value);
                        utcInputs[index].value = Number.isNaN(localDate.getTime()) ? '' : localDate.toISOString();
                    });
                });
            }
            document.querySelectorAll('[data-collection-local-date]').forEach(function (date) {
                var localDate = new Date(date.dataset.collectionLocalDate);
                if (!Number.isNaN(localDate.getTime())) {
                    date.textContent = localDate.toLocaleString();
                }
            });
            var schedules = document.querySelectorAll('[data-collection-start][data-collection-end]');
            function updateSchedules() {
                var now = Date.now();
                schedules.forEach(function (schedule) {
                    var start = new Date(schedule.dataset.collectionStart).getTime();
                    var end = new Date(schedule.dataset.collectionEnd).getTime();
                    var remaining = now < start ? start - now : end - now;
                    var prefix = now < start ? 'Starts in ' : 'Ends in ';
                    var text = schedule.querySelector('span');
                    if (remaining <= 0) {
                        schedule.classList.add('is-ended');
                        text.textContent = 'Collection ended';
                        return;
                    }
                    var days = Math.floor(remaining / 86400000);
                    var hours = Math.floor((remaining % 86400000) / 3600000);
                    var minutes = Math.floor((remaining % 3600000) / 60000);
                    var seconds = Math.floor((remaining % 60000) / 1000);
                    text.textContent = prefix + days + 'd ' + String(hours).padStart(2, '0') + 'h ' + String(minutes).padStart(2, '0') + 'm ' + String(seconds).padStart(2, '0') + 's';
                });
            }
            updateSchedules();
            if (schedules.length) {
                var scheduleTimer = window.setInterval(updateSchedules, 1000);
                var stopScheduleTimer = function () { window.clearInterval(scheduleTimer); };
                window.addEventListener('ajax:navigating', stopScheduleTimer, { once: true });
                window.addEventListener('pagehide', stopScheduleTimer, { once: true });
            }
        })();
    </script>
    @endpush
@endsection
