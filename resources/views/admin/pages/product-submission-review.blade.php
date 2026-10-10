<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=3">
    <link rel="icon" href="{{ asset('favicon.ico') }}?v=3" sizes="any">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Review {{ $product->name }} — Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        *{box-sizing:border-box}
        body{margin:0;background:var(--bg,#f3f5f7);color:var(--text,#101828);font:14px 'Plus Jakarta Sans',sans-serif;transition:background .35s,color .35s;overflow-x:clip}
        .layout{min-height:100vh}
        .main{margin-left:268px;min-width:0;transition:margin .35s cubic-bezier(.4,0,.2,1)}
        .layout.collapsed .main{margin-left:84px}
        .review-content{max-width:1280px;margin:0 auto;padding:24px 26px calc(40px + env(safe-area-inset-bottom));display:grid;gap:20px;min-width:0}
        .head,.head-copy,.head-actions,.card-head,.meta-row,.actions{display:flex;align-items:center}
        .head,.card-head{justify-content:space-between;gap:16px}
        .head-copy{gap:14px;min-width:0}
        .heading-icon{width:48px;height:48px;flex:0 0 48px;display:grid;place-items:center;border-radius:15px;background:linear-gradient(135deg,#0d4257,#1a78a0);color:#79dff3;font-size:23px;box-shadow:0 10px 22px rgba(14,90,117,.2)}
        .eyebrow{display:block;margin-bottom:6px;color:var(--forest,#0e5a75);font-size:10px;font-weight:800;letter-spacing:.13em;text-transform:uppercase}
        h1{margin:0;font-size:24px;line-height:1.25;font-weight:800;letter-spacing:-.4px}
        h2{margin:0;font-size:15px;font-weight:800;letter-spacing:-.15px}
        .muted{color:var(--muted,#68788f)}
        .head p,.card-head p{margin:4px 0 0;color:var(--muted,#68788f);font-size:12px;line-height:1.55}
        .head-actions{gap:10px;flex-shrink:0}
        .button{display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:9px 12px;border:1px solid var(--border,#e8edf3);border-radius:10px;background:var(--card,#fff);color:var(--text,#101828);font:700 11px 'Plus Jakarta Sans',sans-serif;text-decoration:none;white-space:nowrap;transition:border-color .2s,color .2s,transform .2s}
        .button:hover{transform:translateY(-1px);border-color:var(--lime,#3ec5e0);color:var(--forest,#0e5a75)}
        .status-pill{display:inline-flex;align-items:center;gap:6px;border-radius:20px;padding:6px 10px;background:#fff4d6;color:#8a6412;font-size:10px;font-weight:800;text-transform:capitalize;white-space:nowrap}
        .status-pill:before{content:"";width:6px;height:6px;border-radius:50%;background:currentColor}
        .status-pill.approved{background:#e4f8ed;color:#176a3b}
        .status-pill.rejected{background:#ffeaec;color:#a52b40}
        .card{min-width:0;background:var(--card,#fff);border:1px solid var(--border,#e8edf3);border-radius:16px;padding:22px;box-shadow:var(--shadow,0 1px 2px rgba(16,24,40,.04),0 8px 24px rgba(16,24,40,.05));transition:background .35s,border-color .35s}
        .detail-card{padding:0;overflow:hidden}
        .card-head{padding:18px 22px;border-bottom:1px solid var(--border,#e8edf3)}
        .card-title{display:flex;align-items:center;gap:10px}
        .section-icon{width:36px;height:36px;display:grid;place-items:center;border-radius:11px;background:var(--lime-soft,#e4f6fb);color:var(--forest,#0e5a75);font-size:17px}
        .details{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;padding:20px 22px}
        .detail{min-width:0;padding:14px;border:1px solid var(--border,#e8edf3);border-radius:11px;background:var(--bg,#f3f5f7);line-height:1.55;overflow-wrap:anywhere}
        .detail b{display:block;margin-bottom:5px;color:var(--muted,#68788f);font-size:9px;font-weight:800;letter-spacing:.09em;text-transform:uppercase}
        .detail.description{grid-column:1/-1;min-height:74px}
        .gallery{display:flex;gap:13px;flex-wrap:wrap}
        .gallery figure{margin:0}
        .gallery img{display:block;width:178px;height:178px;object-fit:cover;border-radius:12px;border:1px solid var(--border,#e8edf3);background:var(--bg,#f3f5f7);transition:transform .25s,box-shadow .25s}
        .gallery img:hover{transform:translateY(-2px);box-shadow:0 10px 24px rgba(16,24,40,.12)}
        .no-images{padding:20px;border-radius:11px;background:var(--bg,#f3f5f7);color:var(--muted,#68788f);font-size:12px}
        .actions{justify-content:flex-end;gap:10px;padding:16px 20px}
        .actions.card{padding:16px 20px}
        .decision{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:40px;padding:10px 15px;border:0;border-radius:10px;font:700 12px 'Plus Jakarta Sans',sans-serif;text-decoration:none;cursor:pointer;transition:transform .2s,box-shadow .2s,opacity .2s}
        .decision:hover:not(:disabled){transform:translateY(-1px)}
        .decision:disabled{opacity:.6;cursor:wait}
        .approve{background:linear-gradient(135deg,var(--forest,#0e5a75),var(--forest-2,#17739b));color:#fff;box-shadow:0 7px 16px rgba(14,90,117,.17)}
        .reject{background:#fff0f1;color:#a52b40}
        .feedback{padding:13px 15px;border:1px solid #b7e9cc;border-radius:12px;background:#e9f9f0;color:#176a3b;font-size:12px;font-weight:700}
        .feedback.error{border-color:#ffd0d5;background:#fff0f1;color:#a52b40}
        .reviewed-note{display:flex;align-items:center;gap:8px;color:var(--muted,#68788f);font-size:12px}
        .reviewed-note i{color:var(--forest,#0e5a75);font-size:17px}
        body.dark .status-pill.approved{background:rgba(52,211,153,.12);color:#6ee7b7}
        body.dark .status-pill.rejected{background:rgba(248,113,113,.12);color:#fca5a5}
        body.dark .status-pill{background:rgba(251,191,36,.12);color:#fcd34d}
        body.dark .reject,body.dark .feedback.error{background:rgba(248,113,113,.12);border-color:rgba(248,113,113,.2);color:#fca5a5}
        body.dark .feedback{background:rgba(52,211,153,.1);border-color:rgba(52,211,153,.2);color:#6ee7b7}
        @media(max-width:992px){.main,.layout.collapsed .main{margin-left:0}}
        @media(max-width:760px){.review-content{padding:18px 16px calc(32px + env(safe-area-inset-bottom));gap:16px}.head{align-items:flex-start;flex-direction:column}.head-actions{width:100%;justify-content:space-between}.details{grid-template-columns:repeat(2,minmax(0,1fr));padding:16px}.card-head{padding:16px}}
        @media(max-width:520px){.head-copy{align-items:flex-start}.heading-icon{width:42px;height:42px;flex-basis:42px;font-size:20px}h1{font-size:21px}.details{grid-template-columns:1fr}.detail.description{grid-column:auto}.gallery img{width:calc(50vw - 25px);height:auto;aspect-ratio:1}.actions{align-items:stretch;flex-direction:column-reverse}.decision{width:100%}.head-actions{align-items:flex-start;flex-direction:column}}
    </style>
    @include('admin.include.shell-layout')
</head>
<body>
<div class="layout" id="layout">
    @include('admin.include.sidebar')
    <div class="main">
        @include('admin.include.navbar')
<main class="review-content">
    <header class="head">
        <div class="head-copy">
            <span class="heading-icon"><i class="ri-file-search-line"></i></span>
            <div><span class="eyebrow">Submission review</span><h1>Review product</h1><p>Submitted by {{ $product->submitter?->name ?? 'Account removed' }} on {{ $product->created_at->format('M j, Y') }}</p></div>
        </div>
        <div class="head-actions">
            <span class="status-pill {{ $product->review_status }}">{{ $product->review_status }}</span>
            <a class="button" href="{{ route('admin.product-submissions.index') }}"><i class="ri-arrow-left-line"></i>All submissions</a>
        </div>
    </header>
    <div class="feedback" id="reviewFeedback" role="status" aria-live="polite" hidden></div>
    <section class="card detail-card">
        <div class="card-head"><div class="card-title"><span class="section-icon"><i class="ri-file-list-3-line"></i></span><div><h2>Submission details</h2><p>Product and seller-provided listing information.</p></div></div></div>
        <div class="details">
            <div class="detail"><b>Product name</b>{{ $product->name }}</div>
            <div class="detail"><b>Review status</b>{{ ucfirst($product->review_status) }}</div>
            <div class="detail"><b>Category</b>{{ $product->category?->name ?? '—' }}</div>
            <div class="detail"><b>Size</b>{{ $product->size ? $product->size->name . ' (' . number_format($product->size->ml) . ' ml)' : '—' }}</div>
            <div class="detail"><b>Price / discount</b>{{ number_format($product->price, 2) }} · {{ number_format($product->discount, 2) }}% off</div>
            <div class="detail"><b>Stock</b>{{ $product->stock }}</div>
            <div class="detail"><b>Display icon</b>{{ $product->emoji }}</div>
            <div class="detail description"><b>Description</b>{{ $product->description ?: 'No description provided.' }}</div>
        </div>
    </section>
    <section class="card">
        <div class="card-head" style="padding:0 0 16px;border:0"><div class="card-title"><span class="section-icon"><i class="ri-image-line"></i></span><div><h2>Product pictures</h2><p>Images submitted with this listing.</p></div></div></div>
        <div class="gallery">
            @foreach ($product->images->sortBy('sort_order') as $image)
                <figure><img src="{{ asset('storage/' . $image->path) }}" alt="{{ $product->name }}"></figure>
            @endforeach
            @if ($product->images->isEmpty())<div class="no-images">No product pictures were provided.</div>@endif
        </div>
    </section>
    @if ($product->review_status === 'pending')
    <section class="card actions" id="reviewActions">
        <form method="post" action="{{ route('admin.product-submissions.reject', $product) }}">@csrf<button class="decision reject" type="submit"><i class="ri-close-circle-line"></i>Reject submission</button></form>
        <form method="post" action="{{ route('admin.product-submissions.approve', $product) }}">@csrf<button class="decision approve" type="submit"><i class="ri-check-line"></i>Approve and publish</button></form>
    </section>
    @else
    <section class="card reviewed-note"><i class="ri-shield-check-line"></i>Reviewed {{ $product->reviewed_at?->format('M j, Y') ?? 'previously' }} by {{ $product->reviewer?->name ?? 'an administrator' }}.</section>
    @endif
</main>
</div>
</div>
<script>
    (function () {
        var token = document.querySelector('meta[name="csrf-token"]').content;
        var feedback = document.getElementById('reviewFeedback');
        document.querySelectorAll('#reviewActions form').forEach(function (form) {
            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                var button = form.querySelector('button');
                button.disabled = true;
                try {
                    var response = await fetch(form.action, {
                        method: 'POST',
                        headers: {'Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':token},
                        credentials: 'same-origin'
                    });
                    var data = await response.json();
                    if (!response.ok) throw new Error(data.message || 'The review action could not be completed.');
                    feedback.textContent = data.message;
                    feedback.hidden = false;
                    document.getElementById('reviewActions').remove();
                } catch (error) {
                    feedback.textContent = error.message || 'A network error prevented this action.';
                    feedback.classList.add('error');
                    feedback.hidden = false;
                    button.disabled = false;
                }
            });
        });
    })();
</script>
</body>
</html>
