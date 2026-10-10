<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Submissions — Cosmetics Store</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    @include('user.include.base')
    <style>
        .submissions-main{min-width:0}
        .sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
        .submissions-wrap{max-width:1240px;margin:0 auto;padding:0 0 38px}
        .submissions-topbar-title{min-width:0;flex:1}
        .submissions-topbar-title h1{font-size:21px;color:var(--ink);font-weight:600}
        .submissions-topbar-title p{font-size:12.5px;color:rgba(22,35,60,.58)}
        .submissions-heading{display:flex;align-items:center;justify-content:space-between;gap:18px;margin:4px 0 22px}
        .submissions-heading-copy{display:flex;align-items:center;gap:14px}
        .submissions-heading-icon{width:48px;height:48px;flex:none;display:grid;place-items:center;border-radius:15px;background:linear-gradient(135deg,#14532d,#1b6a3c);color:#c3ed6c;font-size:23px;box-shadow:0 10px 22px rgba(20,83,45,.22)}
        .submissions-heading h2{font-size:26px;line-height:1.2;color:var(--ink);font-weight:700;letter-spacing:-.5px}
        .submissions-heading p{font-size:13px;color:var(--txt);margin-top:6px}
        .submissions-submit-link{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:11px 15px;border-radius:11px;background:linear-gradient(135deg,#a3e635,#93d92a);color:#18351c;font-size:12px;font-weight:800;text-decoration:none;box-shadow:0 8px 18px rgba(163,230,53,.22)}
        .submissions-card{overflow:hidden;background:rgba(255,255,255,.9);border:1px solid var(--glass-border);border-radius:18px;box-shadow:0 10px 28px rgba(22,35,60,.06)}
        .submissions-toolbar{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:17px 19px;border-bottom:1px solid var(--glass-border)}
        .submissions-count{color:var(--ink);font-size:12px;font-weight:700}
        .submissions-count span{display:block;margin-top:3px;color:var(--txt);font-size:10px;font-weight:400}
        .submissions-filters{display:flex;align-items:center;justify-content:flex-end;gap:9px;flex-wrap:wrap}
        .submissions-search{position:relative;width:min(250px,100%)}
        .submissions-search i{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#82909e;font-size:16px}
        .submissions-search input,.submissions-filters select{height:39px;border:1px solid #e1e7ed;border-radius:10px;background:#f8fafb;color:var(--ink);font:500 11px Poppins,sans-serif;outline:none}
        .submissions-search input{width:100%;padding:0 11px 0 34px}
        .submissions-filters select{min-width:132px;padding:0 31px 0 11px;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2368788f' stroke-width='2.4'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 10px center}
        .submissions-search input:focus,.submissions-filters select:focus{border-color:#63b957;box-shadow:0 0 0 3px rgba(99,185,87,.12)}
        .submissions-table-wrap{overflow-x:auto}
        .submissions-table{width:100%;border-collapse:collapse;min-width:720px}
        .submissions-table th,.submissions-table td{padding:13px 17px;border-bottom:1px solid #edf0f2;text-align:left;font-size:11px}
        .submissions-table th{background:#fafcfb;color:#71808b;font-size:10px;font-weight:700;letter-spacing:.04em;text-transform:uppercase}
        .submissions-table tbody tr:last-child td{border-bottom:0}
        .submissions-table tbody tr:hover{background:#fbfdfb}
        .submission-product{display:flex;align-items:center;gap:11px;min-width:190px}
        .submission-product-image,.submission-product-placeholder{width:43px;height:43px;flex:none;display:grid;place-items:center;overflow:hidden;border-radius:11px;background:#f2f8ef;color:#1b6a3c}
        .submission-product-image{object-fit:cover}
        .submission-product-placeholder i{font-size:20px}
        .submission-product strong{display:block;color:var(--ink);font-size:11px;font-weight:700}
        .submission-product span{display:block;margin-top:3px;color:var(--txt);font-size:10px}
        .submission-price{color:var(--ink);font-weight:700;white-space:nowrap}
        .submission-status{display:inline-flex;align-items:center;gap:5px;padding:5px 9px;border-radius:20px;background:#fff4d6;color:#8a6412;font-size:10px;font-weight:700;text-transform:capitalize;white-space:nowrap}
        .submission-status.approved{background:#e4f8ed;color:#176a3b}
        .submission-status.rejected{background:#ffeaec;color:#a52b40}
        .submission-view-button{display:inline-flex;align-items:center;gap:5px;padding:7px 10px;border:1px solid #dfe8e2;border-radius:9px;background:#fff;color:#315d43;font:700 10px Poppins,sans-serif;cursor:pointer}
        .submission-view-button:hover{border-color:#a3d884;background:#f5fbf2}
        .submissions-empty{padding:54px 20px;text-align:center}
        .submissions-empty>i{display:grid;place-items:center;width:58px;height:58px;margin:0 auto 13px;border-radius:18px;background:#f1f8ee;color:#438b38;font-size:26px}
        .submissions-empty h3{color:var(--ink);font-size:16px}
        .submissions-empty p{max-width:380px;margin:7px auto 16px;color:var(--txt);font-size:11px;line-height:1.7}
        .submissions-pagination{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 18px;border-top:1px solid var(--glass-border);color:var(--txt);font-size:10px}
        .submissions-pagination nav{display:flex;align-items:center;gap:5px}
        .submissions-pagination nav a,.submissions-pagination nav span[aria-current="page"] span,.submissions-pagination nav span[aria-disabled="true"] span{display:grid;min-width:32px;height:32px;place-items:center;padding:0 8px;border:1px solid #e4e9ec;border-radius:9px;background:#fff;color:#506171;font-size:11px;text-decoration:none}
        .submissions-pagination nav span[aria-current="page"] span{border-color:#1b6a3c;background:#1b6a3c;color:#fff}
        .submissions-pagination nav a:hover{border-color:#a3d884;color:#1b6a3c}
        .submissions-pagination nav span[aria-disabled="true"] span{color:#aeb7bf}
        .submission-modal{width:min(92vw,560px);max-height:88dvh;margin:auto;padding:0;overflow:auto;border:1px solid rgba(22,35,60,.1);border-radius:20px;background:#fff;color:var(--ink);box-shadow:0 24px 80px rgba(20,35,60,.24)}
        .submission-modal::backdrop{background:rgba(11,22,34,.56);backdrop-filter:blur(4px)}
        .submission-modal-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:17px 20px;border-bottom:1px solid #edf0f2}
        .submission-modal-head h3{font-size:15px}
        .submission-modal-close{width:34px;height:34px;border:0;border-radius:10px;background:#f2f5f5;color:#526171;font-size:19px;cursor:pointer}
        .submission-modal-body{padding:19px 20px}
        .submission-modal-gallery{display:flex;gap:8px;overflow-x:auto;margin-bottom:16px}
        .submission-modal-gallery img{width:82px;height:82px;flex:none;object-fit:cover;border-radius:12px;background:#f4f6f5}
        .submission-modal-description{margin:8px 0 15px;color:#657384;font-size:11px;line-height:1.7;white-space:pre-wrap}
        .submission-modal-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}
        .submission-modal-field{padding:10px 12px;border-radius:11px;background:#f7f9f8}
        .submission-modal-field span{display:block;color:#83909a;font-size:9px;text-transform:uppercase;letter-spacing:.04em}
        .submission-modal-field strong{display:block;margin-top:4px;color:var(--ink);font-size:11px;text-transform:capitalize}
        @media(max-width:700px){.submissions-wrap{padding-bottom:28px}.submissions-heading{align-items:flex-start}.submissions-heading h2{font-size:21px}.submissions-heading p{max-width:360px}.submissions-submit-link{padding:10px;font-size:0}.submissions-submit-link i{font-size:17px}.submissions-toolbar{align-items:flex-start;flex-direction:column}.submissions-filters{width:100%;justify-content:stretch}.submissions-search{flex:1;width:auto}.submissions-filters select{min-width:0;flex:1}.submissions-pagination{align-items:flex-start;flex-direction:column}.submission-modal-grid{grid-template-columns:1fr 1fr}}
    </style>
</head>
<body>
<div class="layout">
    @include('user.include.sidebar')
    <main class="main submissions-main">
        <header class="topbar">
            <button class="hamburger" type="button" id="hamburger" aria-label="Open navigation" aria-controls="sidebar" aria-expanded="false">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
            </button>
            <div class="submissions-topbar-title"><h1>My Submissions</h1><p>Track review status for the products you have shared.</p></div>
        </header>

        <div class="submissions-wrap">
            <div class="submissions-heading">
                <div class="submissions-heading-copy">
                    <span class="submissions-heading-icon" aria-hidden="true"><i class="ri-file-list-3-line"></i></span>
                    <div><h2>My submissions</h2><p>Products appear in the shop only after admin approval.</p></div>
                </div>
                <a class="submissions-submit-link" href="{{ route('user.product-submissions.index') }}"><i class="ri-add-line" aria-hidden="true"></i><span>Submit a product</span></a>
            </div>

            <section class="submissions-card" aria-label="Product submissions">
                <div class="submissions-toolbar">
                    <div class="submissions-count">
                        {{ $submissions->total() }} {{ \Illuminate\Support\Str::plural('submission', $submissions->total()) }}
                        <span>Only your submissions are shown here.</span>
                    </div>
                    <form class="submissions-filters" id="submissionFilters" action="{{ route('user.product-submissions.submissions') }}" method="get">
                        <label class="submissions-search">
                            <i class="ri-search-line" aria-hidden="true"></i>
                            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search products…" aria-label="Search your submissions" autocomplete="off">
                        </label>
                        <label>
                            <span class="sr-only">Filter by review status</span>
                            <select name="status" aria-label="Filter by review status">
                                <option value="">All statuses</option>
                                @foreach (['pending' => 'Pending review', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            <span class="sr-only">Submissions per page</span>
                            <select name="per_page" aria-label="Submissions per page">
                                @foreach ([10, 20, 50] as $size)
                                    <option value="{{ $size }}" @selected((int) ($filters['per_page'] ?? 10) === $size)>{{ $size }} per page</option>
                                @endforeach
                            </select>
                        </label>
                    </form>
                </div>

                @if ($submissions->isNotEmpty())
                    <div class="submissions-table-wrap">
                        <table class="submissions-table">
                            <thead><tr><th scope="col">Product</th><th scope="col">Price</th><th scope="col">Submitted</th><th scope="col">Review status</th><th scope="col">Details</th></tr></thead>
                            <tbody>
                                @foreach ($submissions as $submission)
                                    @php
                                        $mainImage = $submission->images->first();
                                        $submissionDetails = [
                                            'name' => $submission->name,
                                            'category' => $submission->category?->name ?? 'Uncategorized',
                                            'size' => $submission->size?->ml ? $submission->size->ml . ' ml' : 'Not specified',
                                            'price' => number_format((float) $submission->price, 2),
                                            'discount' => number_format((float) $submission->discount, 0),
                                            'stock' => $submission->stock,
                                            'description' => $submission->description,
                                            'status' => $submission->review_status,
                                            'submitted' => $submission->created_at->format('M j, Y'),
                                            'images' => $submission->images->map(fn ($image) => asset('storage/' . $image->path))->values(),
                                        ];
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="submission-product">
                                                @if ($mainImage)
                                                    <img class="submission-product-image" src="{{ asset('storage/' . $mainImage->path) }}" alt="Photo of {{ $submission->name }}" loading="lazy">
                                                @else
                                                    <span class="submission-product-placeholder" aria-hidden="true"><i class="ri-image-line"></i></span>
                                                @endif
                                                <div><strong>{{ $submission->name }}</strong><span>{{ $submission->category?->name ?? 'Uncategorized' }}</span></div>
                                            </div>
                                        </td>
                                        <td class="submission-price">£{{ number_format((float) $submission->price, 2) }}</td>
                                        <td>{{ $submission->created_at->format('M j, Y') }}</td>
                                        <td><span class="submission-status {{ $submission->review_status }}"><i class="ri-circle-fill" aria-hidden="true"></i>{{ $submission->review_status }}</span></td>
                                        <td><button type="button" class="submission-view-button" data-submission='@json($submissionDetails)'><i class="ri-eye-line" aria-hidden="true"></i> View</button></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if ($submissions->hasPages())
                        <div class="submissions-pagination">
                            <span>Showing {{ $submissions->firstItem() }}–{{ $submissions->lastItem() }} of {{ $submissions->total() }}</span>
                            {{ $submissions->onEachSide(1)->links() }}
                        </div>
                    @endif
                @else
                    <div class="submissions-empty">
                        <i class="ri-inbox-archive-line" aria-hidden="true"></i>
                        <h3>{{ ($filters['q'] ?? '') || ($filters['status'] ?? '') ? 'No matching submissions' : 'No submissions yet' }}</h3>
                        <p>{{ ($filters['q'] ?? '') || ($filters['status'] ?? '') ? 'Try a different search or status filter.' : 'When you submit a product for review, its details and approval status will appear here.' }}</p>
                        @if (!(($filters['q'] ?? '') || ($filters['status'] ?? '')))
                            <a class="submissions-submit-link" href="{{ route('user.product-submissions.index') }}"><i class="ri-add-line" aria-hidden="true"></i><span>Submit your first product</span></a>
                        @endif
                    </div>
                @endif
            </section>
        </div>
    </main>
</div>
<div class="overlay" id="overlay"></div>
<dialog class="submission-modal" id="submissionModal" aria-labelledby="submissionModalTitle">
    <div class="submission-modal-head"><h3 id="submissionModalTitle">Submission details</h3><button class="submission-modal-close" id="submissionModalClose" type="button" aria-label="Close details"><i class="ri-close-line"></i></button></div>
    <div class="submission-modal-body">
        <div class="submission-modal-gallery" id="submissionModalGallery"></div>
        <p class="submission-modal-description" id="submissionModalDescription"></p>
        <div class="submission-modal-grid" id="submissionModalFields"></div>
    </div>
</dialog>
<script>
    (function () {
        var sidebar = document.getElementById('sidebar');
        var overlay = document.getElementById('overlay');
        var hamburger = document.getElementById('hamburger');
        var filters = document.getElementById('submissionFilters');
        var search = filters.querySelector('[name="q"]');
        var modal = document.getElementById('submissionModal');
        var debounceTimer;

        function navigateFilters() {
            var params = new URLSearchParams(new FormData(filters));
            params.delete('page');
            if (typeof window.ajaxNavigate === 'function') {
                window.ajaxNavigate(filters.action + '?' + params.toString());
            } else {
                window.location.assign(filters.action + '?' + params.toString());
            }
        }

        filters.addEventListener('submit', function (event) {
            event.preventDefault();
            navigateFilters();
        });
        search.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(navigateFilters, 350);
        });
        filters.querySelectorAll('select').forEach(function (select) {
            select.addEventListener('change', navigateFilters);
        });

        if (sidebar && overlay && hamburger) {
            function closeSidebar() {
                sidebar.classList.remove('open');
                overlay.classList.remove('show');
                hamburger.setAttribute('aria-expanded', 'false');
            }
            hamburger.addEventListener('click', function () {
                var open = !sidebar.classList.contains('open');
                sidebar.classList.toggle('open', open);
                overlay.classList.toggle('show', open);
                hamburger.setAttribute('aria-expanded', String(open));
            });
            overlay.addEventListener('click', closeSidebar);
        }

        function fillModal(data) {
            document.getElementById('submissionModalTitle').textContent = data.name || 'Submission details';
            document.getElementById('submissionModalDescription').textContent = data.description || 'No description provided.';
            var gallery = document.getElementById('submissionModalGallery');
            gallery.replaceChildren();
            (data.images || []).forEach(function (url, index) {
                var image = document.createElement('img');
                image.src = url;
                image.alt = 'Product photo ' + (index + 1) + ' of ' + data.name;
                gallery.appendChild(image);
            });
            var fields = [
                ['Category', data.category],
                ['Size', data.size],
                ['Price', '£' + data.price],
                ['Discount', data.discount + '%'],
                ['Stock', data.stock],
                ['Submitted', data.submitted],
                ['Review status', data.status]
            ];
            var container = document.getElementById('submissionModalFields');
            container.replaceChildren();
            fields.forEach(function (field) {
                var wrapper = document.createElement('div');
                wrapper.className = 'submission-modal-field';
                var label = document.createElement('span');
                label.textContent = field[0];
                var value = document.createElement('strong');
                value.textContent = field[1] == null ? '—' : String(field[1]);
                wrapper.append(label, value);
                container.appendChild(wrapper);
            });
        }

        document.querySelectorAll('.submission-view-button').forEach(function (button) {
            button.addEventListener('click', function () {
                fillModal(JSON.parse(button.dataset.submission));
                modal.showModal();
            });
        });
        document.getElementById('submissionModalClose').addEventListener('click', function () { modal.close(); });
        modal.addEventListener('click', function (event) {
            if (event.target === modal) modal.close();
        });
    })();
</script>
</body>
</html>
