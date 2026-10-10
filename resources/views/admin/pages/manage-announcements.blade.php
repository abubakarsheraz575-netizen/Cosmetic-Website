<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Announcements — Cosmetics Admin</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}?v=3">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style data-page>
        .manage-page{width:min(100%,1500px);margin:0 auto;padding:32px clamp(18px,3vw,42px) 52px;display:grid;gap:22px}
        .manage-head{position:relative;isolation:isolate;display:flex;align-items:center;justify-content:space-between;gap:20px;min-height:150px;padding:28px 32px;overflow:hidden;border:1px solid rgba(62,197,224,.2);border-radius:24px;background:radial-gradient(circle at 88% 10%,rgba(112,224,242,.28),transparent 29%),linear-gradient(120deg,#0b3140 0%,#0e5a75 62%,#14799a 100%);box-shadow:0 18px 42px rgba(14,90,117,.16)}
        .manage-head:after{position:absolute;z-index:-1;right:14%;bottom:-100px;width:240px;height:240px;border:1px solid rgba(255,255,255,.12);border-radius:50%;content:""}
        .manage-head-copy{max-width:720px}
        .manage-eyebrow{display:inline-flex;align-items:center;gap:7px;margin-bottom:12px;color:#a7edf8;font-size:10px;font-weight:800;letter-spacing:.16em;text-transform:uppercase}
        .manage-head h1{margin:0;color:#fff;font-size:clamp(25px,3vw,34px);font-weight:800;letter-spacing:-.04em}
        .manage-head p{max-width:590px;margin:8px 0 0;color:rgba(255,255,255,.76);font-size:13px;line-height:1.7}
        .manage-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:44px;padding:11px 16px;border:1px solid var(--border);border-radius:12px;background:var(--card);color:var(--text);font:700 12px 'Plus Jakarta Sans',sans-serif;text-decoration:none;cursor:pointer;transition:transform .18s,border-color .18s,box-shadow .18s}
        .manage-btn:hover{transform:translateY(-1px);border-color:var(--lime);box-shadow:0 8px 22px rgba(16,24,40,.12)}
        .manage-btn.primary{flex:0 0 auto;border-color:rgba(255,255,255,.4);background:#fff;color:#0e5a75;box-shadow:0 8px 20px rgba(0,0,0,.15)}
        .manage-btn.primary:hover{background:#e8fbff}
        .manage-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
        .manage-stat{display:flex;align-items:center;gap:14px;padding:18px 20px;border:1px solid var(--border);border-radius:17px;background:var(--card);box-shadow:var(--shadow)}
        .manage-stat-icon{display:grid;place-items:center;flex:0 0 44px;height:44px;border-radius:14px;background:var(--lime-soft);color:var(--forest);font-size:20px}
        .manage-stat strong{display:block;color:var(--text);font-size:21px;font-weight:800;line-height:1.1}
        .manage-stat span:last-child{display:block;margin-top:5px;color:var(--muted);font-size:11px;font-weight:600}
        .manage-card{overflow:hidden;border:1px solid var(--border);border-radius:20px;background:var(--card);box-shadow:var(--shadow)}
        .manage-card-head{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:20px 24px;border-bottom:1px solid var(--border)}
        .manage-card-head h2{margin:0;color:var(--text);font-size:16px;font-weight:800;letter-spacing:-.02em}
        .manage-card-head p{margin:5px 0 0;color:var(--muted);font-size:11px;line-height:1.6}
        .manage-total{flex:0 0 auto;padding:8px 12px;border:1px solid rgba(62,197,224,.18);border-radius:20px;background:var(--lime-soft);color:var(--forest);font-size:10px;font-weight:800}
        .manage-toolbar{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:15px 22px;border-bottom:1px solid var(--border)}
        .manage-search{display:flex;align-items:center;gap:9px;width:min(100%,340px);min-height:40px;padding:0 12px;border:1px solid var(--border);border-radius:11px;background:var(--bg);color:var(--muted)}
        .manage-search:focus-within{border-color:var(--lime);box-shadow:0 0 0 3px rgba(62,197,224,.14)}
        .manage-search i{font-size:16px}
        .manage-search input{width:100%;min-width:0;border:0;outline:0;background:transparent;color:var(--text);font:500 12px 'Plus Jakarta Sans',sans-serif}
        .manage-search input::placeholder{color:var(--muted)}
        .manage-filters{display:flex;align-items:center;gap:5px;padding:4px;border:1px solid var(--border);border-radius:12px;background:var(--bg)}
        .manage-filter{min-height:31px;padding:6px 10px;border:0;border-radius:8px;background:transparent;color:var(--muted);font:700 10px 'Plus Jakarta Sans',sans-serif;cursor:pointer}
        .manage-filter:hover{color:var(--forest)}
        .manage-filter.active{background:var(--card);color:var(--forest);box-shadow:0 2px 7px rgba(16,24,40,.09)}
        .manage-scroll{overflow:auto}
        .manage-table{width:100%;min-width:760px;border-collapse:separate;border-spacing:0}
        .manage-table th{padding:13px 17px;background:var(--bg);color:var(--muted);font-size:9px;font-weight:800;letter-spacing:.12em;text-align:left;text-transform:uppercase;white-space:nowrap}
        .manage-table th:first-child{padding-left:24px}
        .manage-table th:last-child,.manage-table td:last-child{text-align:right}
        .manage-table td{padding:17px;border-top:1px solid var(--border);color:var(--text);font-size:11px;vertical-align:middle}
        .manage-table td:first-child{padding-left:24px}
        .manage-table tbody tr{transition:background .16s}
        .manage-table tbody tr:hover{background:rgba(62,197,224,.045)}
        .notice-title{max-width:300px;color:var(--text);font-size:12px;font-weight:800;line-height:1.45}
        .notice-message{max-width:330px;margin-top:5px;color:var(--muted);font-size:10px;line-height:1.6}
        .manage-categories{display:flex;flex-wrap:wrap;gap:6px;max-width:360px}
        .manage-category{display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border:1px solid rgba(62,197,224,.14);border-radius:10px;background:var(--lime-soft);color:var(--forest);font-size:9px;font-weight:700;white-space:nowrap}
        .manage-category i{font-size:13px}
        .manage-status{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border:1px solid #b7ebcf;border-radius:20px;background:#ecfdf3;color:#067647;font-size:9px;font-weight:800;white-space:nowrap}
        .manage-status:before{width:6px;height:6px;border-radius:50%;background:currentColor;content:""}
        .manage-status.paused{border-color:#e4e7ec;background:#f2f4f7;color:#667085}
        .manage-actions{display:flex;justify-content:flex-end;gap:6px;white-space:nowrap}
        .manage-icon-btn{width:35px;height:35px;display:grid;place-items:center;border:1px solid var(--border);border-radius:11px;background:var(--card);color:var(--forest);font-size:16px;text-decoration:none;cursor:pointer;transition:transform .15s,border-color .15s,background .15s}
        .manage-icon-btn:hover{transform:translateY(-1px);border-color:var(--lime);background:var(--lime-soft)}
        .manage-icon-btn.danger{background:#fff6f5;color:#b42318}
        .manage-icon-btn.danger:hover{border-color:#fda29b;background:#fee4e2}
        .manage-empty{padding:54px 20px!important;color:var(--muted)!important;text-align:center}
        .manage-empty i{display:block;margin-bottom:9px;color:var(--lime);font-size:30px}
        .manage-pagination{padding:15px 20px;border-top:1px solid var(--border)}
        .manage-pagination nav{display:flex;justify-content:space-between;align-items:center;gap:12px}
        .manage-pagination nav>div:first-child{color:var(--muted);font-size:11px}
        .manage-pagination nav>div:last-child{display:flex;align-items:center;gap:4px}
        .manage-pagination a,.manage-pagination span[aria-current],.manage-pagination span[aria-disabled]{min-width:34px;min-height:34px;display:grid;place-items:center;padding:6px 9px;border:1px solid var(--border);border-radius:10px;background:var(--card);color:var(--text);font-size:11px;text-decoration:none}
        .manage-pagination a:hover{border-color:var(--lime);background:var(--lime-soft)}
        .manage-pagination span[aria-current]{border-color:var(--forest);background:var(--forest);color:#fff}
        .manage-pagination span[aria-disabled]{color:var(--muted);opacity:.55}
        body.dark .manage-status{border-color:rgba(52,211,153,.2);background:rgba(52,211,153,.1);color:#6ee7b7}
        body.dark .manage-status.paused{border-color:var(--border);background:rgba(139,160,184,.1);color:var(--muted)}
        body.dark .manage-icon-btn.danger{background:rgba(248,113,113,.08);color:#fca5a5}
        body.dark .manage-icon-btn.danger:hover{background:rgba(248,113,113,.16)}
        body.dark .manage-filter.active{background:#17243a}
        .manage-hidden{display:none!important}
        .swal2-popup{border-radius:18px!important;font-family:'Plus Jakarta Sans',sans-serif!important}
        @media(max-width:760px){.manage-page{padding:20px 14px 36px;gap:16px}.manage-head{align-items:flex-start;flex-direction:column;padding:23px 21px}.manage-btn.primary{width:100%}.manage-stats{grid-template-columns:1fr}.manage-stat{padding:15px 17px}.manage-toolbar{align-items:stretch;flex-direction:column;padding:13px}.manage-search{width:100%}.manage-filters{width:100%;overflow:auto}.manage-filter{flex:1}.manage-card-head{align-items:flex-start;padding:17px}.manage-card-head p{max-width:340px}.manage-table td:first-child,.manage-table th:first-child{padding-left:15px}.manage-pagination nav{align-items:flex-start;flex-direction:column}.manage-pagination nav>div:last-child{flex-wrap:wrap}}
        @media(min-width:761px){.manage-stats{grid-template-columns:repeat(3,minmax(0,1fr))}}
    </style>
    @include('admin.include.shell-layout')
</head>
<body>
    <div class="layout" id="layout">
        @include('admin.include.sidebar')
        <div class="main">
            @include('admin.include.navbar')
            <main class="manage-page">
                <header class="manage-head">
                    <div class="manage-head-copy">
                        <span class="manage-eyebrow"><i class="ri-megaphone-line"></i>Customer communications</span>
                        <h1>Announcements</h1>
                        <p>Keep customers informed. Review who each notice reaches, make quick edits, and control what is live.</p>
                    </div>
                    <a class="manage-btn primary" href="{{ route('admin.announcements') }}"><i class="ri-add-line"></i>Create announcement</a>
                </header>
                <section class="manage-stats" aria-label="Announcement overview">
                    <article class="manage-stat"><span class="manage-stat-icon"><i class="ri-megaphone-line"></i></span><div><strong>{{ number_format($announcementStats['total']) }}</strong><span>Total announcements</span></div></article>
                    <article class="manage-stat"><span class="manage-stat-icon"><i class="ri-radio-button-line"></i></span><div><strong>{{ number_format($announcementStats['published']) }}</strong><span>Currently published</span></div></article>
                    <article class="manage-stat"><span class="manage-stat-icon"><i class="ri-pause-circle-line"></i></span><div><strong>{{ number_format($announcementStats['paused']) }}</strong><span>Paused for later</span></div></article>
                </section>
                <section class="manage-card">
                    <header class="manage-card-head">
                        <div><h2>Your announcements</h2><p>Audience counts include each customer once. Open the audience to review matching orders and delivery timing.</p></div>
                        <span class="manage-total">{{ number_format($announcements->total()) }} total</span>
                    </header>
                    <div class="manage-toolbar">
                        <label class="manage-search"><i class="ri-search-line" aria-hidden="true"></i><input id="announcementSearch" type="search" placeholder="Search titles or categories…" aria-label="Search announcements"></label>
                        <div class="manage-filters" role="group" aria-label="Filter announcements">
                            <button class="manage-filter active" type="button" data-filter="all" aria-pressed="true">All</button>
                            <button class="manage-filter" type="button" data-filter="published" aria-pressed="false">Published</button>
                            <button class="manage-filter" type="button" data-filter="paused" aria-pressed="false">Paused</button>
                        </div>
                    </div>
                    <div class="manage-scroll">
                        <table class="manage-table">
                            <thead><tr><th>Announcement</th><th>Target categories</th><th>Customers</th><th>Status</th><th style="text-align:right">Actions</th></tr></thead>
                            <tbody>
                                @forelse ($announcements as $announcement)
                                    <tr id="announcement-{{ $announcement->id }}" data-announcement-row data-status="{{ $announcement->is_active ? 'published' : 'paused' }}" data-search="{{ strtolower($announcement->title.' '.$announcement->message.' '.$announcement->categories->pluck('name')->implode(' ')) }}">
                                        <td>
                                            <div class="notice-title">{{ $announcement->title }}</div>
                                            <div class="notice-message">{{ \Illuminate\Support\Str::limit($announcement->message, 100) }}</div>
                                        </td>
                                        <td>
                                            <div class="manage-categories">
                                                @foreach ($announcement->categories as $category)
                                                    @php
                                                        $iconClass = $category->iconClass();
                                                    @endphp
                                                    <span class="manage-category"><i class="{{ $iconClass }}" aria-hidden="true"></i>{{ $category->name }}</span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td><strong>{{ number_format($announcement->recipient_count) }}</strong><span style="display:block;margin-top:3px;color:var(--muted);font-size:9px">customers</span></td>
                                        <td><span class="manage-status {{ $announcement->is_active ? '' : 'paused' }}">{{ $announcement->is_active ? 'Published' : 'Paused' }}</span></td>
                                        <td>
                                            <div class="manage-actions">
                                                <a class="manage-icon-btn" href="{{ route('admin.announcements.customers', $announcement) }}" title="View recipient customers" aria-label="View recipient customers"><i class="ri-eye-line"></i></a>
                                                <a class="manage-icon-btn" href="{{ route('admin.announcements', ['edit' => $announcement->id]) }}" title="Edit announcement" aria-label="Edit announcement"><i class="ri-edit-line"></i></a>
                                                <button class="manage-icon-btn" type="button" data-action="toggle" data-url="{{ route('admin.announcements.toggle', $announcement) }}" title="{{ $announcement->is_active ? 'Pause' : 'Publish' }}" aria-label="{{ $announcement->is_active ? 'Pause' : 'Publish' }}"><i class="{{ $announcement->is_active ? 'ri-pause-circle-line' : 'ri-play-circle-line' }}"></i></button>
                                                <button class="manage-icon-btn danger" type="button" data-action="delete" data-url="{{ route('admin.announcements.delete', $announcement) }}" title="Delete announcement" aria-label="Delete announcement"><i class="ri-delete-bin-line"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="manage-empty"><i class="ri-megaphone-line"></i><strong>No announcements yet</strong><span style="display:block;margin-top:5px">Create your first announcement to start reaching customers.</span></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($announcements->hasPages())
                        <div class="manage-pagination">{{ $announcements->links() }}</div>
                    @endif
                </section>
            </main>
        </div>
    </div>
    <script>
        (function () {
            var csrfElement = document.querySelector('meta[name="csrf-token"]');
            var csrf = csrfElement ? csrfElement.content : '';
            var toast = Swal.mixin({toast:true,position:'top-end',showConfirmButton:false,timer:2600,timerProgressBar:true});
            var search = document.getElementById('announcementSearch');
            var filter = 'all';
            var rows = Array.prototype.slice.call(document.querySelectorAll('[data-announcement-row]'));

            function applyFilters() {
                var term = search.value.trim().toLowerCase();
                rows.forEach(function (row) {
                    row.classList.toggle('manage-hidden', (filter !== 'all' && row.dataset.status !== filter) || (term && row.dataset.search.indexOf(term) === -1));
                });
                var empty = document.getElementById('announcementFilterEmpty');
                if (!empty && rows.length) {
                    empty = document.createElement('tr');
                    empty.id = 'announcementFilterEmpty';
                    empty.innerHTML = '<td class="manage-empty" colspan="5"><i class="ri-search-line"></i>No announcements match these filters.</td>';
                    rows[0].parentNode.appendChild(empty);
                }
                if (empty) empty.classList.toggle('manage-hidden', !rows.length || rows.some(function(row) { return !row.classList.contains('manage-hidden'); }));
            }

            search.addEventListener('input', applyFilters);
            document.querySelectorAll('[data-filter]').forEach(function (button) {
                button.addEventListener('click', function () {
                    filter = button.dataset.filter;
                    document.querySelectorAll('[data-filter]').forEach(function (item) {
                        var active = item === button;
                        item.classList.toggle('active', active);
                        item.setAttribute('aria-pressed', active ? 'true' : 'false');
                    });
                    applyFilters();
                });
            });

            async function sendRequest(button, method) {
                button.disabled = true;
                try {
                    var response = await fetch(button.dataset.url, {
                        method: method,
                        headers: {'Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':csrf}
                    });
                    var contentType = response.headers.get('content-type') || '';
                    var data = contentType.includes('application/json')
                        ? await response.json().catch(function () { return {}; })
                        : {};
                    if (!response.ok || !contentType.includes('application/json')) {
                        var fieldErrors = data.errors && Object.values(data.errors)[0];
                        throw new Error(fieldErrors ? fieldErrors[0] : (data.message || 'Could not update the announcement. Refresh the page and try again.'));
                    }
                    toast.fire({icon:'success',title:data.message});
                    window.setTimeout(function () {
                        if (typeof window.ajaxNavigate === 'function') window.ajaxNavigate(window.location.href, false);
                        else window.location.reload();
                    }, 450);
                } catch (error) {
                    button.disabled = false;
                    toast.fire({icon:'error',title:error.message});
                }
            }

            document.querySelectorAll('[data-action="toggle"]').forEach(function (button) {
                button.addEventListener('click', function () { sendRequest(button, 'PATCH'); });
            });
            document.querySelectorAll('[data-action="delete"]').forEach(function (button) {
                button.addEventListener('click', async function () {
                    var result = await Swal.fire({
                        title: 'Delete this announcement?',
                        text: 'Customers will no longer see this notice.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Delete announcement',
                        confirmButtonColor: '#b42318'
                    });
                    if (result.isConfirmed) sendRequest(button, 'DELETE');
                });
            });
        })();
    </script>
</body>
</html>
