<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Announcements — Cosmetics Store</title>
    @include('user.include.base')
    <style>
        .announcement-list { display:grid; gap:14px; max-width:900px; }
        .announcement-card { display:flex; gap:14px; align-items:flex-start; padding:18px 20px; border-left:4px solid var(--teal); }
        .announcement-card.type-delay { border-left-color:#f0b429; }
        .announcement-card.type-good { border-left-color:#2ec181; }
        .announcement-card.type-success { border-left-color:#2ec181; }
        .announcement-card.type-warning { border-left-color:#e89a32; }
        .announcement-icon { font-size:23px; }
        .announcement-copy { min-width:0; flex:1; }
        .announcement-copy h2 { color:var(--ink); font-size:15px; }
        .announcement-copy p { color:var(--txt); font-size:13px; line-height:1.65; margin-top:5px; white-space:pre-wrap; }
        .announcement-copy small { display:block; color:rgba(22,35,60,.5); margin-top:8px; }
        .announcement-categories { display:flex; flex-wrap:wrap; gap:6px; margin-top:9px; }
        .announcement-categories span { padding:4px 9px; border-radius:20px; background:rgba(56,170,180,.09); color:var(--teal-2); font-size:10px; font-weight:600; }
        .announcement-empty { padding:24px; color:var(--txt); }
        .announcement-pagination { margin-top:18px; }
    </style>
</head>
<body>
    <div class="layout" id="layout">
        @include('user.include.sidebar')
        <main class="main">
            <div class="topbar">
                <button class="hamburger" type="button" id="hamburger" aria-label="Open navigation">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                </button>
                <div class="page-head"><h1>Announcements</h1><p>Updates for categories you have purchased from.</p></div>
            </div>
            <section class="announcement-list">
                @forelse ($announcements as $announcement)
                    <article class="announcement-card glass reveal type-{{ $announcement->type }}">
                        <span class="announcement-icon">{{ in_array($announcement->type, ['delay', 'warning'], true) ? '⚠️' : (in_array($announcement->type, ['good', 'success'], true) ? '✨' : '📢') }}</span>
                        <div class="announcement-copy">
                            <h2>{{ $announcement->title }}</h2>
                            <p>{{ $announcement->message }}</p>
                            <div class="announcement-categories">
                                @foreach ($announcement->categories as $category)
                                    <span>{{ $category->name }}</span>
                                @endforeach
                            </div>
                            <small>{{ $announcement->created_at->diffForHumans() }}</small>
                        </div>
                    </article>
                @empty
                    <div class="announcement-empty glass">There are no announcements for your purchases yet.</div>
                @endforelse
                <div class="announcement-pagination">{{ $announcements->links() }}</div>
            </section>
        </main>
    </div>
    <div class="overlay" id="overlay"></div>
    <script>
        (function () {
            var sidebar = document.getElementById('sidebar');
            var overlay = document.getElementById('overlay');
            var hamburger = document.getElementById('hamburger');
            if (!sidebar || !overlay || !hamburger) return;
            function closeSidebar() {
                sidebar.classList.remove('open');
                overlay.classList.remove('show');
            }
            hamburger.addEventListener('click', function () {
                sidebar.classList.toggle('open');
                overlay.classList.toggle('show');
            });
            overlay.addEventListener('click', closeSidebar);
        })();
    </script>
</body>
</html>
