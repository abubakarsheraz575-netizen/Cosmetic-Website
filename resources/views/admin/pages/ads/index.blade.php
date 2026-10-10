@extends('admin.layouts.ecommerce')

@section('title', 'Ads')

@section('content')
<style>
    main.ads-page{--ads-ink:#172b3a;--ads-muted:#708091;--ads-line:#e3eaf0;--ads-accent:#128c92;color:var(--ads-ink);width:100%;min-width:0;box-sizing:border-box}
    .ads-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:20px;flex-wrap:wrap}
    .ads-head h1{margin:0;font-size:26px}.ads-head p{margin:6px 0 0;color:var(--ads-muted);font-size:12px}
    .ads-create{display:inline-flex;align-items:center;gap:8px;padding:11px 15px;border-radius:10px;background:var(--ads-accent);color:#fff;font-size:12px;font-weight:800;text-decoration:none}
    .ads-panel{min-width:0;max-width:100%;box-sizing:border-box;padding:18px;border:1px solid var(--ads-line);border-radius:16px;background:#fff;box-shadow:0 12px 34px rgba(26,54,71,.06)}
    .ads-toolbar{display:flex;min-width:0;max-width:100%;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:15px}
    .ads-toolbar form{display:grid;grid-template-columns:minmax(180px,230px) minmax(135px,155px) minmax(115px,130px) auto;min-width:0;max-width:100%;gap:8px;align-items:center}.ads-toolbar form>*{min-width:0;max-width:100%}.ads-toolbar input,.ads-toolbar select{min-height:40px;padding:8px 11px;border:1px solid var(--ads-line);border-radius:9px;background:#fff;color:var(--ads-ink)}.ads-toolbar .site-select-wrap{width:100%;min-width:0}.ads-toolbar .site-select-trigger{width:100%;min-width:0}
    .ads-filter{padding:9px 13px;border:0;border-radius:9px;background:#203849;color:#fff;font-size:12px;font-weight:700;cursor:pointer}
    .ads-table{width:100%;min-width:850px;border-collapse:collapse}.ads-table th{padding:11px 10px;background:#f5f8fa;color:#738392;text-align:left;font-size:10px;letter-spacing:.06em;text-transform:uppercase}
    .ads-table td{padding:12px 10px;border-bottom:1px solid #edf1f4;font-size:12px;vertical-align:middle}
    .ads-preview{width:92px;height:58px;border-radius:8px;object-fit:cover;background:#edf2f4}
    .ads-title{display:block;font-weight:800}.ads-sub{display:block;margin-top:4px;color:var(--ads-muted);font-size:10px}
    .ads-code{color:#687b8b;font:11px ui-monospace,Consolas,monospace}.ads-state{display:inline-flex;padding:5px 9px;border-radius:20px;background:#f0f3f5;color:#637483;font-size:10px;font-weight:800}
    .ads-state.published{background:#e9f7f1;color:#087a54}.ads-actions{display:flex;gap:6px}.ads-action{display:grid;place-items:center;width:32px;height:32px;border:1px solid var(--ads-line);border-radius:8px;background:#fff;color:#28727b;font-size:15px;text-decoration:none;cursor:pointer}.ads-action.delete{color:#bc4040}
    .ads-pager{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-top:14px;color:var(--ads-muted);font-size:11px}.ads-pager nav{display:flex;gap:5px}.ads-pager a,.ads-pager span{display:grid;place-items:center;min-width:33px;height:33px;padding:0 8px;border:1px solid var(--ads-line);border-radius:8px;color:var(--ads-ink);text-decoration:none}.ads-pager [aria-current]{background:var(--ads-accent);border-color:var(--ads-accent);color:#fff}
    .ads-empty{padding:35px 16px;text-align:center;color:var(--ads-muted)}.ads-empty strong{display:block;margin-bottom:6px;color:var(--ads-ink)}
    @media(max-width:900px){.ads-toolbar form{grid-template-columns:minmax(150px,1fr) minmax(130px,1fr) minmax(110px,1fr) auto}}@media(max-width:650px){.ads-panel{padding:12px}.ads-toolbar{width:100%;flex-direction:column;align-items:stretch}.ads-toolbar form{width:100%;grid-template-columns:1fr;align-items:stretch}.ads-toolbar input,.ads-toolbar select,.ads-toolbar .site-select-wrap{width:100%;max-width:100%;flex:1}.ads-toolbar input{min-width:0}.ads-create{width:100%;justify-content:center}}
</style>
<main class="ads-page">
    <header class="ads-head">
        <div><h1>Homepage ads</h1><p>Create and schedule promotional slides with Media library images or secure HTTPS image links.</p></div>
        <a class="ads-create" href="{{ route('admin.ads.create') }}"><i class="ri-add-line"></i>Create ad</a>
    </header>
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    <section class="ads-panel">
        <div class="ads-toolbar">
            <strong>{{ number_format($ads->total()) }} ads</strong>
            <form method="GET" action="{{ route('admin.ads.index') }}">
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search ads" aria-label="Search ads">
                <select name="status" aria-label="Filter status"><option value="">All statuses</option><option value="published" @selected(($filters['status'] ?? '') === 'published')>Published</option><option value="draft" @selected(($filters['status'] ?? '') === 'draft')>Draft</option></select>
                <select name="per_page" aria-label="Rows per page">@foreach([10,25,50] as $size)<option value="{{ $size }}" @selected((int)($filters['per_page'] ?? 10) === $size)>{{ $size }} per page</option>@endforeach</select>
                <button class="ads-filter" type="submit">Filter</button>
            </form>
        </div>
        <div class="table-wrap">
            <table class="ads-table" data-server-pagination="true">
                <thead><tr><th>Preview</th><th>Name / headline</th><th>Code</th><th>Status</th><th>Schedule</th><th>Clicks</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($ads as $ad)
                        <tr>
                            <td><img class="ads-preview" src="{{ $ad->imageSource('image_url') }}" alt="" loading="lazy"></td>
                            <td><span class="ads-title">{{ $ad->title }}</span><span class="ads-sub">{{ $ad->name }}</span></td>
                            <td class="ads-code">{{ $ad->shortcode }}</td>
                            <td><span class="ads-state {{ $ad->status }}">{{ ucfirst($ad->status) }}</span></td>
                            <td>{{ $ad->starts_at?->format('M j, Y') ?? 'Any time' }}<span class="ads-sub">Until {{ $ad->expires_at?->format('M j, Y') ?? 'No expiry' }}</span></td>
                            <td>{{ number_format($ad->clicks_count) }}</td>
                            <td><div class="ads-actions">
                                <a class="ads-action" href="{{ route('admin.ads.edit', $ad) }}" aria-label="Edit {{ $ad->name }}" title="Edit"><i class="ri-edit-line"></i></a>
                                <form method="POST" action="{{ route('admin.ads.destroy', $ad) }}" data-confirm-modal data-confirm-title="Delete this ad?" data-confirm-message="This advertisement will be permanently deleted.">@csrf @method('DELETE')<button class="ads-action delete" aria-label="Delete {{ $ad->name }}" title="Delete"><i class="ri-delete-bin-line"></i></button></form>
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="ads-empty"><strong>No ads created yet</strong>Add an ad here, then set it to Published and enable the carousel in Ads settings.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <footer class="ads-pager"><span>Showing {{ $ads->firstItem() ?? 0 }}–{{ $ads->lastItem() ?? 0 }} of {{ number_format($ads->total()) }}</span>
            <nav aria-label="Ads pages">
                @if($ads->onFirstPage())<span aria-disabled="true">‹</span>@else<a href="{{ $ads->previousPageUrl() }}" rel="prev">‹</a>@endif
                @foreach($ads->getUrlRange(max(1, $ads->currentPage()-2), min($ads->lastPage(), $ads->currentPage()+2)) as $page => $url)
                    @if($page === $ads->currentPage())<span aria-current="page">{{ $page }}</span>@else<a href="{{ $url }}">{{ $page }}</a>@endif
                @endforeach
                @if($ads->hasMorePages())<a href="{{ $ads->nextPageUrl() }}" rel="next">›</a>@else<span aria-disabled="true">›</span>@endif
            </nav>
        </footer>
    </section>
</main>
@endsection
