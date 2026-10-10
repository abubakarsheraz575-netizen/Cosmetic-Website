<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Notifications — Cosmetics Admin</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}?v=3">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css">
    <style data-page>
        *{box-sizing:border-box}
        body{margin:0;background:var(--bg,#f3f5f7);color:var(--text,#101828);font:14px 'Plus Jakarta Sans',sans-serif}
        .layout{min-height:100vh}.main{min-width:0;margin-left:268px}.layout.collapsed .main{margin-left:84px}
        .notifications-page{max-width:1100px;margin:0 auto;padding:28px;display:grid;gap:22px}
        .notifications-head{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}
        .notifications-head h1{margin:0;font-size:25px;font-weight:800;letter-spacing:-.5px}
        .notifications-head p{margin:6px 0 0;color:var(--muted);font-size:12px}
        .notification-summary{display:inline-flex;align-items:center;gap:8px;padding:9px 13px;border:1px solid var(--border);border-radius:12px;background:var(--card);color:var(--muted);font-size:12px;font-weight:700}
        .notification-summary i{color:var(--lime);font-size:17px}
        .notification-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:16px 20px;border-bottom:1px solid var(--border)}
        .notification-toolbar strong{font-size:13px}
        .notification-toolbar span{color:var(--muted);font-size:11px}
        .notification-mark{display:inline-flex;align-items:center;gap:7px;min-height:38px;padding:8px 12px;border:1px solid var(--border);border-radius:10px;background:var(--card);color:var(--forest);font:700 11px 'Plus Jakarta Sans',sans-serif;cursor:pointer}
        .notification-mark:hover{background:var(--lime-soft)}
        .notification-mark:disabled{opacity:.55;cursor:wait}
        .notification-card{overflow:hidden;border:1px solid var(--border);border-radius:17px;background:var(--card);box-shadow:var(--shadow)}
        .notification-list{display:grid}
        .notification-row{display:flex;align-items:center;gap:15px;min-width:0;padding:17px 20px;border-bottom:1px solid var(--border);color:inherit;text-decoration:none;transition:background .16s}
        .notification-row:last-child{border-bottom:0}
        .notification-row:hover{background:var(--bg)}
        .notification-row.unread{background:var(--lime-soft)}
        .notification-icon{display:grid;place-items:center;flex:none;width:42px;height:42px;border-radius:13px;font-size:19px}
        .notification-icon.i-green{background:#e8f7ee;color:#16a34a}
        .notification-icon.i-blue{background:#e8f1fd;color:#2563eb}
        .notification-icon.i-amber{background:#fdf4e3;color:#d97706}
        .notification-icon.i-violet{background:#f1ecfd;color:#7c3aed}
        .notification-icon.i-red{background:#fee2e2;color:#dc2626}
        .notification-copy{flex:1;min-width:0}
        .notification-copy strong{display:block;color:var(--text);font-size:13px;line-height:1.5}
        .notification-copy span{display:block;margin-top:3px;color:var(--muted);font-size:11.5px;line-height:1.5;overflow-wrap:anywhere}
        .notification-copy time{display:block;margin-top:5px;color:var(--muted);font-size:10.5px;font-weight:600}
        .notification-status{display:inline-flex;align-items:center;gap:6px;flex:none;color:var(--muted);font-size:10px;font-weight:700}
        .notification-status:before{content:'';width:7px;height:7px;border-radius:50%;background:currentColor}
        .notification-row.unread .notification-status{color:var(--forest)}
        .notification-empty{padding:58px 20px;text-align:center;color:var(--muted)}
        .notification-empty i{display:block;margin-bottom:10px;color:var(--lime);font-size:32px}
        .notification-empty strong{display:block;margin-bottom:5px;color:var(--text);font-size:14px}
        .notification-empty span{font-size:11px}
        .notification-pagination{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:15px 20px;border-top:1px solid var(--border)}
        .notification-pagination span{color:var(--muted);font-size:11px}
        .notification-pages{display:flex;gap:7px}
        .notification-pages a,.notification-pages button{display:grid;place-items:center;min-width:36px;height:36px;padding:0 10px;border:1px solid var(--border);border-radius:9px;background:var(--card);color:var(--text);font:700 11px 'Plus Jakarta Sans',sans-serif;text-decoration:none}
        .notification-pages a:hover{border-color:var(--lime);color:var(--forest)}
        .notification-pages button:disabled{opacity:.45}
        @media(max-width:992px){.main,.layout.collapsed .main{margin-left:0}}
        @media(max-width:640px){.notifications-page{padding:20px 14px}.notification-row{align-items:flex-start;padding:15px}.notification-status{margin-top:8px}.notification-toolbar{align-items:flex-start;padding:14px;flex-wrap:wrap}.notification-pagination{padding:13px;flex-wrap:wrap}}
    </style>
    @include('admin.include.shell-layout')
</head>
<body>
    <div class="layout" id="layout">
        @include('admin.include.sidebar')
        <div class="main">
            @include('admin.include.navbar')
            <main class="notifications-page">
                <header class="notifications-head">
                    <div>
                        <h1>Notifications</h1>
                        <p>Updates from orders, payments, customers, reviews and inventory.</p>
                    </div>
                    <span class="notification-summary"><i class="ri-notification-3-line"></i>{{ $unreadCount }} unread</span>
                </header>

                <section class="notification-card" aria-label="All notifications">
                    <div class="notification-toolbar">
                        <div><strong>All notifications</strong><br><span>Newest first · {{ $notifications->total() }} total</span></div>
                        <button type="button" class="notification-mark" data-notifications-mark-all @disabled($unreadCount === 0)>
                            <i class="ri-check-double-line"></i>Mark all as read
                        </button>
                    </div>

                    @if ($notifications->isNotEmpty())
                        <div class="notification-list">
                            @foreach ($notifications as $notification)
                                <a class="notification-row notification-open {{ $notification['read'] ? '' : 'unread' }}"
                                   href="{{ $notification['url'] }}"
                                   data-notification-key="{{ $notification['key'] }}">
                                    <span class="notification-icon {{ $notification['icon_class'] }}"><i class="{{ $notification['icon'] }}"></i></span>
                                    <span class="notification-copy">
                                        <strong>{{ $notification['title'] }}</strong>
                                        <span>{{ $notification['description'] }}</span>
                                        <time datetime="{{ \Illuminate\Support\Carbon::createFromTimestamp($notification['timestamp'])->toIso8601String() }}">{{ $notification['time'] }}</time>
                                    </span>
                                    <span class="notification-status">{{ $notification['read'] ? 'Read' : 'Unread' }}</span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="notification-empty">
                            <i class="ri-notification-off-line"></i>
                            <strong>No notifications yet</strong>
                            <span>New orders, customers, reviews and low-stock alerts will appear here.</span>
                        </div>
                    @endif

                    @if ($notifications->hasPages())
                        <nav class="notification-pagination" aria-label="Notification pages">
                            <span>Showing {{ $notifications->firstItem() }}–{{ $notifications->lastItem() }} of {{ $notifications->total() }}</span>
                            <div class="notification-pages">
                                <a href="{{ $notifications->previousPageUrl() ?: '#' }}" @if(!$notifications->previousPageUrl()) aria-disabled="true" tabindex="-1" @endif aria-label="Previous page"><i class="ri-arrow-left-s-line"></i></a>
                                <a href="{{ $notifications->nextPageUrl() ?: '#' }}" @if(!$notifications->nextPageUrl()) aria-disabled="true" tabindex="-1" @endif aria-label="Next page"><i class="ri-arrow-right-s-line"></i></a>
                            </div>
                        </nav>
                    @endif
                </section>
            </main>
        </div>
    </div>
    <script>
        (function() {
            var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
            var readUrl = @json(route('admin.notifications.read'));
            var readAllUrl = @json(route('admin.notifications.read-all'));
            function post(url, data) {
                return fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data || {}),
                    credentials: 'same-origin'
                }).then(function(response) {
                    return response.json().catch(function() { return {}; }).then(function(body) {
                        if (!response.ok) throw new Error(body.message || 'Could not update notifications.');
                        return body;
                    });
                });
            }
            document.querySelectorAll('[data-notifications-mark-all]').forEach(function(button) {
                button.addEventListener('click', function() {
                    if (button.disabled) return;
                    button.disabled = true;
                    post(readAllUrl).then(function() {
                        document.querySelectorAll('.notification-row.unread').forEach(function(row) {
                            row.classList.remove('unread');
                            row.querySelector('.notification-status').textContent = 'Read';
                        });
                        document.querySelector('.notification-summary').innerHTML = '<i class="ri-notification-3-line"></i>0 unread';
                        button.disabled = true;
                        var badge = document.getElementById('nbNotifCount');
                        var dot = document.getElementById('nbNotifDot');
                        var pill = document.getElementById('nbUnreadPill');
                        if (badge) badge.style.display = 'none';
                        if (dot) dot.style.display = 'none';
                        if (pill) pill.textContent = '0 new';
                        var navbarMark = document.getElementById('nbMarkAll');
                        if (navbarMark) navbarMark.disabled = true;
                    }).catch(function(error) {
                        if (window.showToast) window.showToast(error.message, 'error');
                        else if (window.nbToast) window.nbToast(error.message, 'ri-error-warning-line');
                        button.disabled = false;
                    });
                });
            });
            document.querySelectorAll('.notification-open').forEach(function(link) {
                link.addEventListener('click', function(event) {
                    if (!link.classList.contains('unread')) return;
                    event.preventDefault();
                    post(readUrl, { notification_key: link.dataset.notificationKey }).then(function() {
                        if (typeof window.adminAjaxNavigate === 'function') window.adminAjaxNavigate(link.href);
                        else window.location.assign(link.href);
                    }).catch(function(error) {
                        if (window.showToast) window.showToast(error.message, 'error');
                        else if (window.nbToast) window.nbToast(error.message, 'ri-error-warning-line');
                    });
                });
            });
        })();
    </script>
</body>
</html>
