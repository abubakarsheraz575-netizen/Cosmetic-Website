<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=3">
    <link rel="icon" href="{{ asset('favicon.ico') }}?v=3" sizes="any">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Product Submissions — Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        *{box-sizing:border-box}
        body{margin:0;background:var(--bg,#f3f5f7);color:var(--text,#101828);font:14px 'Plus Jakarta Sans',sans-serif;transition:background .35s,color .35s;overflow-x:clip}
        .layout{min-height:100vh}
        .main{margin-left:268px;min-width:0;transition:margin .35s cubic-bezier(.4,0,.2,1)}
        .layout.collapsed .main{margin-left:84px}
        .submission-content{max-width:1420px;margin:0 auto;padding:24px 26px calc(40px + env(safe-area-inset-bottom));display:grid;gap:22px;min-width:0}
        .head,.head-copy,.head-actions,.section-head,.filters,.product-cell{display:flex;align-items:center}
        .head,.section-head{justify-content:space-between;gap:16px}
        .head-copy{gap:14px;min-width:0}
        .heading-icon{width:48px;height:48px;flex:0 0 48px;display:grid;place-items:center;border-radius:15px;background:linear-gradient(135deg,#0d4257,#1a78a0);color:#79dff3;font-size:23px;box-shadow:0 10px 22px rgba(14,90,117,.2)}
        .eyebrow{display:block;margin-bottom:6px;color:var(--forest,#0e5a75);font-size:10px;font-weight:800;letter-spacing:.13em;text-transform:uppercase}
        h1{font-size:24px;line-height:1.25;letter-spacing:-.4px;margin:0;font-weight:800}
        h2{font-size:15px;letter-spacing:-.15px;margin:0;font-weight:800}
        .muted{color:var(--muted,#68788f)}
        .head p,.section-head p{margin:4px 0 0;color:var(--muted,#68788f);font-size:12px;line-height:1.55}
        .head-actions{gap:10px;flex-shrink:0}
        .count-chip{display:inline-flex;align-items:center;gap:7px;padding:9px 12px;border:1px solid var(--border,#e8edf3);border-radius:11px;background:var(--card,#fff);color:var(--muted,#68788f);font-size:11px;font-weight:700;white-space:nowrap}
        .count-chip i{font-size:15px;color:var(--forest,#0e5a75)}
        .button{display:inline-flex;align-items:center;justify-content:center;gap:7px;border:1px solid transparent;border-radius:10px;padding:10px 14px;background:linear-gradient(135deg,var(--forest,#0e5a75),var(--forest-2,#17739b));color:#fff;text-decoration:none;font:700 12px 'Plus Jakarta Sans',sans-serif;white-space:nowrap;box-shadow:0 7px 16px rgba(14,90,117,.17);transition:transform .2s,box-shadow .2s}
        .button:hover{transform:translateY(-1px);box-shadow:0 10px 20px rgba(14,90,117,.25)}
        .card{min-width:0;background:var(--card,#fff);border:1px solid var(--border,#e8edf3);border-radius:16px;box-shadow:var(--shadow,0 1px 2px rgba(16,24,40,.04),0 8px 24px rgba(16,24,40,.05));overflow:hidden;transition:background .35s,border-color .35s}
        .section-head{padding:20px 22px;border-bottom:1px solid var(--border,#e8edf3)}
        .section-title{display:flex;align-items:center;gap:11px}
        .section-icon{width:36px;height:36px;display:grid;place-items:center;border-radius:11px;background:var(--lime-soft,#e4f6fb);color:var(--forest,#0e5a75);font-size:17px}
        .filters{justify-content:flex-end;gap:9px;padding:16px 20px;border-bottom:1px solid var(--border,#e8edf3)}
        .filters input,.filters select{width:min(290px,100%);height:40px;padding:0 12px;border:1px solid var(--border,#e8edf3);border-radius:10px;background:var(--bg,#f3f5f7);color:var(--text,#101828);font:500 12px 'Plus Jakarta Sans',sans-serif;outline:none;transition:border-color .2s,box-shadow .2s}
        .filters select{width:170px}
        .filters input:focus,.filters select:focus{border-color:var(--lime,#3ec5e0);box-shadow:0 0 0 3px rgba(62,197,224,.13)}
        .table-wrap{overflow-x:auto}
        table{width:100%;border-collapse:collapse;min-width:760px}
        th,td{text-align:left;padding:14px 18px;border-bottom:1px solid var(--border,#e8edf3)}
        th{background:var(--bg,#f3f5f7);color:var(--muted,#68788f);font-size:10px;font-weight:800;letter-spacing:.09em;text-transform:uppercase;white-space:nowrap}
        td{font-size:12px;vertical-align:middle}
        tbody tr:last-child td{border-bottom:0}
        tbody tr{transition:background .2s}
        tbody tr:hover{background:rgba(62,197,224,.035)}
        .product-cell{gap:11px;min-width:190px;font-size:12px;font-weight:800}
        .thumb,.thumb-placeholder{width:42px;height:42px;flex:0 0 42px;object-fit:cover;border-radius:10px;border:1px solid var(--border,#e8edf3)}
        .thumb-placeholder{display:grid;place-items:center;background:var(--lime-soft,#e4f6fb);color:var(--forest,#0e5a75);font-size:18px}
        .status-pill{display:inline-flex;align-items:center;gap:6px;border-radius:20px;padding:5px 9px;font-size:10px;font-weight:800;text-transform:capitalize;background:#fff4d6;color:#8a6412;white-space:nowrap}
        .status-pill:before{content:"";width:6px;height:6px;border-radius:50%;background:currentColor}
        .status-pill.approved{background:#e4f8ed;color:#176a3b}
        .status-pill.rejected{background:#ffeaec;color:#a52b40}
        .alert{padding:13px 15px;border:1px solid #b7e9cc;border-radius:12px;background:#e9f9f0;color:#176a3b;font-size:12px;font-weight:700}
        .row-action{padding:8px 11px;font-size:11px}
        .empty-state{padding:44px 20px;text-align:center;color:var(--muted,#68788f)}
        .empty-state i{display:block;margin:0 auto 9px;color:var(--forest,#0e5a75);font-size:30px}
        .empty-state strong{display:block;margin-bottom:4px;color:var(--text,#101828);font-size:13px}
        .pagination{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;padding:17px 20px;border-top:1px solid var(--border,#e8edf3)}
        .pagination-info{color:var(--muted,#68788f);font-size:12px}
        .pagination nav{display:flex;align-items:center;gap:5px}
        .pagination nav a,.pagination nav span,.pagination nav button{display:inline-flex;align-items:center;justify-content:center;min-width:36px;height:36px;padding:0 10px;border:1px solid var(--border,#e8edf3);border-radius:9px;background:var(--card,#fff);color:var(--text,#101828);font:700 12px 'Plus Jakarta Sans',sans-serif;text-decoration:none}
        .pagination nav a:hover{border-color:var(--forest,#0e5a75);color:var(--forest,#0e5a75)}
        .pagination nav [aria-current="page"]{border-color:var(--forest,#0e5a75);background:var(--forest,#0e5a75);color:#fff}
        .pagination nav [aria-disabled="true"],.pagination nav button:disabled{color:var(--muted,#68788f);opacity:.5}
        .pagination nav svg{width:15px;height:15px}
        body.dark .status-pill.approved{background:rgba(52,211,153,.12);color:#6ee7b7}
        body.dark .status-pill.rejected{background:rgba(248,113,113,.12);color:#fca5a5}
        body.dark .status-pill{background:rgba(251,191,36,.12);color:#fcd34d}
        body.dark .alert{background:rgba(52,211,153,.1);border-color:rgba(52,211,153,.2);color:#6ee7b7}
        @media(max-width:992px){.main,.layout.collapsed .main{margin-left:0}}
        @media(max-width:700px){.submission-content{padding:18px 16px calc(32px + env(safe-area-inset-bottom));gap:17px}.head{align-items:flex-start;flex-direction:column}.head-actions{width:100%;justify-content:space-between}.section-head{padding:17px 18px}.filters{align-items:stretch;flex-direction:column;padding:14px}.filters input,.filters select{width:100%}}
        @media(max-width:460px){.head-copy{align-items:flex-start}.heading-icon{width:42px;height:42px;flex-basis:42px;font-size:20px}.head-actions{align-items:stretch;flex-direction:column}.head-actions>*{justify-content:center}h1{font-size:21px}}
    </style>
    @include('admin.include.shell-layout')
</head>
<body>
<div class="layout" id="layout">
    @include('admin.include.sidebar')
    <div class="main">
        @include('admin.include.navbar')
<main class="submission-content">
    <header class="head">
<div class="head-copy">
    <span class="heading-icon"><i class="ri-inbox-archive-line"></i></span>
    <div><span class="eyebrow">Catalog management</span><h1>Customer product submissions</h1><p>Review pending listings and revisit decisions at any time.</p></div>
</div>
<div class="head-actions">
    <span class="count-chip"><i class="ri-file-list-3-line"></i>{{ number_format($products->total()) }} submissions</span>
    <a class="button" href="{{ route('admin.products.index') }}"><i class="ri-store-2-line"></i>Product catalog</a>
</div>
    </header>
    @if (session('status'))<div class="alert">{{ session('status') }}</div>@endif
    <section class="card">
<div class="section-head">
    <div class="section-title"><span class="section-icon"><i class="ri-shield-check-line"></i></span><div><h2>Submission queue</h2><p>Review customer listings before they appear in the shop.</p></div></div>
</div>
<form class="filters" method="get" action="{{ route('admin.product-submissions.index') }}">
            <label for="submissionSearch" style="position:absolute;left:-9999px">Search submissions or seller name</label>
            <input id="submissionSearch" name="search" type="search" value="{{ request('search') }}" placeholder="Search products or sellers…">
            <label for="submissionStatus" style="position:absolute;left:-9999px">Filter submissions by status</label>
            <select id="submissionStatus" name="status">
                <option value="">All statuses</option>
                @foreach (['pending' => 'Pending review', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </form>
        @if ($products->isEmpty())
            <div class="empty-state"><i class="ri-inbox-2-line"></i><strong>Nothing to review here</strong><span>No customer product submissions match this view.</span></div>
        @else
            <div class="table-wrap"><table data-server-pagination="true">
                <thead><tr><th>Product</th><th>Submitted by</th><th>Category</th><th>Submitted</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @foreach ($products as $product)
                    <tr>
                        <td><div class="product-cell">@if ($product->images->first())<img class="thumb" src="{{ asset('storage/' . $product->images->first()->path) }}" alt="">@else<span class="thumb-placeholder"><i class="ri-box-3-line"></i></span>@endif<span>{{ $product->name }}</span></div></td>
                        <td>{{ $product->submitter?->name ?? 'Account removed' }}</td>
                        <td>{{ $product->category?->name ?? '—' }}</td>
                        <td>{{ $product->created_at->format('M j, Y') }}</td>
                        <td><span class="status-pill {{ $product->review_status }}">{{ $product->review_status }}</span></td>
                        <td><a class="button row-action" href="{{ route('admin.product-submissions.show', $product) }}">{{ $product->review_status === 'pending' ? 'Review' : 'View' }}<i class="ri-arrow-right-line"></i></a></td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
            @php
                $firstPage = max(1, $products->currentPage() - 2);
                $lastPage = min($products->lastPage(), $products->currentPage() + 2);
            @endphp
            <div class="pagination">
                <span class="pagination-info">Showing {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} of {{ number_format($products->total()) }} submissions</span>
                <nav aria-label="Submission pages">
                    @if($products->onFirstPage())<span aria-disabled="true">Previous</span>@else<a href="{{ $products->previousPageUrl() }}">Previous</a>@endif
                    @if($firstPage > 1)<a href="{{ $products->url(1) }}">1</a><span aria-hidden="true">…</span>@endif
                    @foreach(range($firstPage, max($firstPage, $lastPage)) as $page)
                        @if($page === $products->currentPage())<span aria-current="page">{{ $page }}</span>@else<a href="{{ $products->url($page) }}">{{ $page }}</a>@endif
                    @endforeach
                    @if($lastPage < $products->lastPage())<span aria-hidden="true">…</span><a href="{{ $products->url($products->lastPage()) }}">{{ $products->lastPage() }}</a>@endif
                    @if($products->hasMorePages())<a href="{{ $products->nextPageUrl() }}">Next</a>@else<span aria-disabled="true">Next</span>@endif
                </nav>
            </div>
        @endif
    </section>
</main>
</div>
</div>
<script>
    (function () {
        var form = document.querySelector('.filters');
        var search = document.getElementById('submissionSearch');
        var status = document.getElementById('submissionStatus');
        var timer;
        if (search) search.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(function () {
                var params = new URLSearchParams(new FormData(form));
                if (typeof window.ajaxNavigate === 'function') window.ajaxNavigate(form.action + '?' + params.toString());
                else form.requestSubmit();
            }, 250);
        });
        if (status) status.addEventListener('change', function () {
            var params = new URLSearchParams(new FormData(form));
            if (typeof window.ajaxNavigate === 'function') window.ajaxNavigate(form.action + '?' + params.toString());
            else form.requestSubmit();
        });
    })();
</script>
</body>
</html>
