<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'E-commerce') — Cosmetics Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=3">
    <link rel="icon" href="{{ asset('favicon.ico') }}?v=3" sizes="any">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box }
        :root { --bg:#f3f5f7; --card:#fff; --text:#101828; --muted:#68788f; --border:#e8edf3; --brand:#0e5a75; --accent:#3ec5e0; --shadow:0 8px 24px rgba(16,24,40,.05); --radius:16px }
        body.dark { --bg:#0b1220; --card:#111c2e; --text:#e7edf5; --muted:#8ba0b8; --border:#1e2c42; --shadow:0 10px 30px rgba(0,0,0,.25) }
        html,body { margin:0; min-height:100%; background:var(--bg); color:var(--text); font-family:'Plus Jakarta Sans',sans-serif }
        .layout { min-height:100vh }
        .main { min-width:0; min-height:100vh; margin-left:268px; background:var(--bg); transition:margin .3s }
        .layout.collapsed .main { margin-left:84px }
        .content { display:grid; gap:20px; min-width:0; padding:26px; }
        .page-head { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:14px }
        .page-head h1 { display:flex; align-items:center; gap:12px; margin:0; font-size:25px; letter-spacing:-.5px }
        .page-head h1 i { display:grid; place-items:center; width:44px; height:44px; border-radius:14px; color:#fff; background:linear-gradient(135deg,#0e5a75,#17739b); font-size:22px }
        .page-head p { margin:5px 0 0; color:var(--muted); font-size:13px }
        .card { min-width:0; padding:21px; border:1px solid var(--border); border-radius:var(--radius); background:var(--card); box-shadow:var(--shadow) }
        .stats { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:16px }
        .stat { display:flex; align-items:center; gap:14px }
        .stat-icon { display:grid; place-items:center; width:46px; height:46px; flex:none; border-radius:14px; color:#0e8fb5; background:rgba(62,197,224,.14); font-size:22px }
        .stat > .stat-icon { display:grid; place-items:center; margin:0; line-height:1 }
        .stat > .stat-icon i { display:block; line-height:1 }
        .stat-icon.amber { color:#bd7900; background:#fff3d6 }
        .stat-icon.green { color:#108255; background:#dcf7eb }
        .stat-icon.violet { color:#7144c8; background:#efe8ff }
        .stat strong { display:block; font-size:23px; line-height:1.2 }
        .stat span { display:block; margin-top:4px; color:var(--muted); font-size:12px }
        .charts { display:grid; grid-template-columns:minmax(0,1.5fr) minmax(280px,1fr); gap:16px }
        .card-title { margin:0 0 15px; font-size:15px }
        .chart { height:290px }
        .chart-empty-wrap { position:relative }
        .chart-empty-center { position:absolute; inset:0; display:grid; place-content:center; gap:4px; pointer-events:none; text-align:center }
        .chart-empty-center strong { color:var(--text); font-size:13px }
        .chart-empty-center span { color:var(--muted); font-size:11px }
        .chart-error { display:grid; place-items:center; height:100%; padding:18px; border-radius:12px; background:rgba(224,93,93,.08); color:#b83232; text-align:center; font-size:13px }
        .toolbar { display:flex; flex-wrap:wrap; gap:10px; align-items:center; justify-content:space-between; margin-bottom:15px }
        .toolbar input,.toolbar select,.field input,.field select,.field textarea { width:100%; padding:11px 13px; color:var(--text); background:var(--card); border:1px solid var(--border); border-radius:10px; font:inherit }
        .toolbar input { max-width:330px }
        .toolbar select { width:auto; min-width:155px }
        .table-wrap { width:100%; overflow-x:auto; -webkit-overflow-scrolling:touch }
        table { width:100%; border-collapse:collapse; min-width:760px }
        .dt-controls,.dt-footer { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; padding:12px 0 }
        .dt-controls { border-bottom:1px solid var(--border); margin-bottom:8px }
        .dt-search { display:flex; align-items:center; gap:8px; color:var(--muted); font-size:12px }
        .dt-search input,.dt-length select { min-width:0; padding:9px 11px; border:1px solid var(--border); border-radius:9px; background:var(--card); color:var(--text); font:inherit }
        .dt-search input { width:min(280px,60vw) }
        .dt-length { display:flex; align-items:center; gap:8px; color:var(--muted); font-size:12px }
        .dt-info { color:var(--muted); font-size:12px }
        .dt-pages { display:flex; flex-wrap:wrap; gap:5px; align-items:center }
        .dt-page { min-width:34px; height:34px; padding:0 9px; border:1px solid var(--border); border-radius:8px; background:var(--card); color:var(--text); font:inherit; font-size:12px; font-weight:600; cursor:pointer }
        .dt-page:hover,.dt-page.active { border-color:var(--brand); background:var(--brand); color:#fff }
        .dt-page:disabled { opacity:.45; cursor:not-allowed }
        th[data-sort-key] { cursor:pointer; user-select:none }
        th[data-sort-key]::after { content:' ↕'; color:var(--muted); font-size:10px }
        th[data-sort-direction="asc"]::after { content:' ↑'; color:var(--brand) }
        th[data-sort-direction="desc"]::after { content:' ↓'; color:var(--brand) }
        .product-cell { display:flex; align-items:center; gap:11px; min-width:180px }
        .product-thumb { width:44px; height:44px; flex:none; overflow:hidden; display:grid; place-items:center; border-radius:11px; border:1px solid var(--border); background:var(--bg); color:var(--muted); font-size:19px }
        .product-thumb img { width:100%; height:100%; object-fit:cover }
        .product-picker { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:9px; max-height:310px; overflow:auto; padding:10px; border:1px solid var(--border); border-radius:12px; background:var(--bg) }
        .product-picker-search { margin-bottom:8px }
        .product-picker-search input { width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:9px; background:var(--card); color:var(--text); font:inherit }
        .product-choice { display:flex; align-items:center; gap:10px; min-width:0; padding:9px; border:1px solid var(--border); border-radius:10px; background:var(--card); cursor:pointer }
        .product-choice[hidden] { display:none }
        .product-choice:hover { border-color:var(--accent); box-shadow:0 4px 12px rgba(16,24,40,.06) }
        .product-choice input { width:16px; height:16px; flex:none; padding:0; accent-color:var(--brand); }
        .product-choice .product-thumb { width:40px; height:40px }
        .product-choice-copy { min-width:0; flex:1 }
        .product-choice-copy strong,.product-choice-copy small { display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap }
        .product-choice-copy small { margin-top:3px; color:var(--muted); font-size:11px }
        .dt-cards { display:grid; gap:12px }
        .dt-record { min-width:0; padding:15px; border:1px solid var(--border); border-radius:13px; background:var(--card) }
        .dt-products { display:flex; flex-wrap:wrap; gap:8px; margin-top:12px }
        .dt-product-chip { display:inline-flex; align-items:center; gap:7px; min-width:0; padding:5px 9px 5px 5px; border:1px solid var(--border); border-radius:20px; color:var(--text); font-size:11px }
        .dt-product-chip .product-thumb { width:28px; height:28px; border-radius:50%; font-size:13px }
        th,td { padding:13px 12px; text-align:left; border-bottom:1px solid var(--border); vertical-align:middle }
        th { color:var(--muted); font-size:10px; text-transform:uppercase; letter-spacing:.09em; white-space:nowrap }
        td { font-size:12.5px }
        td small { display:block; margin-top:4px; color:var(--muted) }
        .badge { display:inline-flex; align-items:center; padding:5px 9px; border-radius:20px; background:rgba(62,197,224,.13); color:#087794; font-weight:700; font-size:11px; text-transform:capitalize; white-space:nowrap }
        .badge.pending,.badge.processing,.badge.requested { background:#fff3d6; color:#a66a00 }
        .badge.shipped,.badge.approved { background:#e2f3fd; color:#176a9b }
        .badge.delivered,.badge.paid,.badge.refunded { background:#dcf7eb; color:#108255 }
        .badge.cancelled,.badge.failed,.badge.rejected { background:#ffe8e8; color:#c33737 }
        .actions { display:flex; flex-wrap:wrap; align-items:center; gap:7px }
        .button { display:inline-flex; justify-content:center; align-items:center; gap:6px; min-height:36px; padding:8px 12px; border:1px solid var(--border); border-radius:9px; background:var(--card); color:var(--text); font-family:inherit; font-size:12px; font-weight:600; text-decoration:none; cursor:pointer; white-space:nowrap }
        .button.primary { border-color:var(--brand); background:var(--brand); color:#fff }
        .button.danger { color:#bd3030; border-color:#f3caca }
        .button:hover { filter:brightness(.97) }
        .alert { padding:12px 15px; border-radius:10px; background:#dcf7eb; color:#126d4a; font-size:13px }
        .errors { padding:12px 15px; border-radius:10px; background:#ffe8e8; color:#a82727; font-size:13px }
        .admin-flash-toast { position:fixed;top:18px;right:18px;z-index:10000;width:min(440px,calc(100vw - 32px));padding:14px 17px;border:1px solid rgba(38,162,105,.25);border-radius:13px;background:#f0fbf5;color:#126d4a;box-shadow:0 16px 40px rgba(16,24,40,.16);font-size:13px;font-weight:700; }
        .admin-flash-toast.error { border-color:rgba(224,93,93,.3);background:#fff5f5;color:#a72c2c; }
        .admin-flash-toast ul { margin:7px 0 0;padding-left:18px;font-weight:500; }
        .admin-flash-toast button { float:right;padding:0 0 0 12px;border:0;background:transparent;color:inherit;font:inherit;font-size:18px;cursor:pointer; }
        .form-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:15px }
        .field { display:grid; gap:7px; min-width:0 }
        .field label { font-size:12px; font-weight:700 }
        input[type="checkbox"] { width:16px; height:16px; flex:none; padding:0; accent-color:var(--brand); }
        .sr-only { position:absolute; top:0; left:0; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0 }
        .field textarea { min-height:110px; resize:vertical }
        .field.full { grid-column:1/-1 }
        .items-editor { display:grid; gap:10px }
        .item-edit { display:grid; grid-template-columns:minmax(160px,1fr) 110px 140px; gap:12px; align-items:center; padding:12px; border:1px solid var(--border); border-radius:12px }
        .item-edit label { display:grid; gap:6px; color:var(--muted); font-size:11px }
        .empty { padding:30px; text-align:center; color:var(--muted) }
        @media(max-width:1100px) { .stats { grid-template-columns:repeat(2,minmax(0,1fr)) } .charts { grid-template-columns:1fr } }
        @media(max-width:992px) { .main,.layout.collapsed .main { margin-left:0 } }
        @media(max-width:680px) { .content { padding:16px 13px 28px } .page-head h1 { font-size:21px } .stats { grid-template-columns:1fr 1fr; gap:10px } .stat { padding:13px; gap:9px; align-items:flex-start } .stat > div { min-width:0 } .stat strong { font-size:clamp(14px,3.8vw,17px); overflow-wrap:anywhere } .stat span { line-height:1.35 } .stat-icon { width:34px;height:34px;font-size:17px } .card { padding:16px } .form-grid { grid-template-columns:1fr } .field.full { grid-column:auto } .item-edit { grid-template-columns:1fr 1fr } .item-edit label:first-child { grid-column:1/-1 } .toolbar input { max-width:none; flex:1 1 100% } .dt-controls,.dt-footer { align-items:flex-start } .dt-search { flex:1 1 100% } .dt-search input { width:100% } .dt-length { width:100%; justify-content:flex-start } .product-picker { grid-template-columns:1fr } }
        @media(max-width:420px) { .stats { grid-template-columns:1fr } .item-edit { grid-template-columns:1fr } .item-edit label:first-child { grid-column:auto } }
    </style>
    @include('admin.include.shell-layout')
    @stack('head')
</head>
<body>
    <div class="layout" id="layout">
        @include('admin.include.sidebar')
        <div class="main">
            @include('admin.include.navbar')
            <main class="content">
                @if(session('success')) <div class="admin-flash-toast" role="status" aria-live="polite"><button type="button" aria-label="Dismiss notification" onclick="this.parentElement.remove()">&times;</button>{{ session('success') }}</div> @endif
                @if($errors->any())
                    <div class="errors" role="alert"><strong>Check the highlighted information:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                    <div class="admin-flash-toast error" role="alert"><button type="button" aria-label="Dismiss notification" onclick="this.parentElement.remove()">&times;</button>Some information needs attention.<ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    <script>
        (function () {
            var toast = document.querySelector('.admin-flash-toast:not(.error)');
            if (toast) window.setTimeout(function () { toast.remove(); }, 5200);
        })();
    </script>
    <script>
        (function () {
            if (window.__commerceDataTablesBound) {
                window.__initCommerceDataTables(document);
                return;
            }
            window.__commerceDataTablesBound = true;
            var tableStates = new WeakMap();

            function initDataTables(root) {
                (root || document).querySelectorAll('.table-wrap table').forEach(function (table) {
                    if (table.dataset.serverPagination === 'true' || tableStates.has(table)) return;
                    var wrapper = table.closest('.table-wrap');
                    if (!wrapper || !wrapper.parentNode) return;
                    var tableId = table.id || ('ecommerceTable' + Math.random().toString(36).slice(2));
                    table.id = tableId;
                    var existingSearch = document.querySelector('[data-table-search="' + tableId + '"]');
                    var search = existingSearch;
                    var controls = document.createElement('div');
                    controls.className = 'dt-controls';
                    if (!search) {
                        controls.innerHTML = '<label class="dt-search"><i class="ri-search-line" aria-hidden="true"></i><span class="sr-only">Search table</span><input type="search" placeholder="Search records..." aria-label="Search records"></label><label class="dt-length">Show <select aria-label="Rows per page"><option>10</option><option>25</option><option>50</option><option>100</option><option value="-1">All</option></select> rows</label>';
                        search = controls.querySelector('input');
                    } else {
                        controls.innerHTML = '<label class="dt-length">Show <select aria-label="Rows per page"><option>10</option><option>25</option><option>50</option><option>100</option><option value="-1">All</option></select> rows</label>';
                    }
                    wrapper.parentNode.insertBefore(controls, wrapper);
                    var status = document.querySelector('[data-table-status="' + tableId + '"]');
                    var footer = document.createElement('div');
                    footer.className = 'dt-footer';
                    footer.innerHTML = '<span class="dt-info" aria-live="polite"></span><nav class="dt-pages" aria-label="Table pages"></nav>';
                    wrapper.parentNode.insertBefore(footer, wrapper.nextSibling);
                    var length = controls.querySelector('.dt-length select');
                    var headers = Array.from(table.querySelectorAll('thead th'));
                    headers.forEach(function (header, index) {
                        header.dataset.sortKey = String(index);
                        header.setAttribute('aria-sort', 'none');
                        header.setAttribute('tabindex', '0');
                    });
                    var rows = Array.from(table.querySelectorAll('tbody tr')).filter(function (row) {
                        return !row.querySelector('.empty');
                    });
                    var noResults = document.createElement('tr');
                    noResults.className = 'dt-empty';
                    noResults.hidden = true;
                    noResults.innerHTML = '<td class="empty" colspan="' + Math.max(1, headers.length) + '">No matching records found.</td>';
                    if (table.tBodies[0]) table.tBodies[0].appendChild(noResults);
                    var state = { page: 1, pageSize: 10, rows: rows, search: search, status: status, length: length, noResults: noResults, footer: footer, sortColumn: -1, sortDirection: 1 };
                    tableStates.set(table, state);

                    function draw() {
                        var term = state.search ? state.search.value.trim().toLocaleLowerCase() : '';
                        var selectedStatus = state.status ? state.status.value : '';
                        var filtered = state.rows.filter(function (row) {
                            var searchableRow = row.cloneNode(true);
                            searchableRow.querySelectorAll('.sr-only,[aria-hidden="true"]').forEach(function (element) { element.remove(); });
                            return (!term || searchableRow.textContent.toLocaleLowerCase().includes(term)) &&
                                (!selectedStatus || row.dataset.status === selectedStatus);
                        });
                        var pageCount = state.pageSize === -1 ? 1 : Math.max(1, Math.ceil(filtered.length / state.pageSize));
                        state.page = Math.min(state.page, pageCount);
                        var first = state.pageSize === -1 ? 0 : (state.page - 1) * state.pageSize;
                        var visible = state.pageSize === -1 ? filtered : filtered.slice(first, first + state.pageSize);
                        var visibleRows = new Set(visible);
                        state.rows.forEach(function (row) { row.hidden = !visibleRows.has(row); });
                        state.noResults.hidden = filtered.length !== 0;
                        var from = filtered.length ? first + 1 : 0;
                        var to = state.pageSize === -1 ? filtered.length : Math.min(first + state.pageSize, filtered.length);
                        footer.querySelector('.dt-info').textContent = 'Showing ' + from + ' to ' + to + ' of ' + filtered.length + ' entries';
                        var nav = footer.querySelector('.dt-pages');
                        nav.replaceChildren();
                        function addButton(label, page, disabled, active) {
                            var button = document.createElement('button');
                            button.type = 'button';
                            button.className = 'dt-page' + (active ? ' active' : '');
                            button.textContent = label;
                            button.disabled = disabled;
                            if (active) button.setAttribute('aria-current', 'page');
                            button.addEventListener('click', function () { state.page = page; draw(); });
                            nav.appendChild(button);
                        }
                        addButton('Previous', state.page - 1, state.page === 1 || !filtered.length, false);
                        var pageNumbers = new Set([1, pageCount, state.page - 1, state.page, state.page + 1]);
                        var last = 0;
                        Array.from(pageNumbers).filter(function (page) { return page >= 1 && page <= pageCount; }).sort(function (a, b) { return a - b; }).forEach(function (page) {
                            if (last && page - last > 1) {
                                var dots = document.createElement('span');
                                dots.className = 'dt-info';
                                dots.textContent = '…';
                                nav.appendChild(dots);
                            }
                            addButton(String(page), page, false, page === state.page);
                            last = page;
                        });
                        addButton('Next', state.page + 1, state.page >= pageCount || !filtered.length, false);
                    }
                    state.draw = draw;

                    if (state.search) state.search.addEventListener('input', function () { state.page = 1; draw(); });
                    if (state.status) state.status.addEventListener('change', function () { state.page = 1; draw(); });
                    if (state.length) state.length.addEventListener('change', function () {
                        state.pageSize = Number(state.length.value);
                        state.page = 1;
                        draw();
                    });
                    headers.forEach(function (header, index) {
                        header.addEventListener('click', function () {
                            state.sortDirection = state.sortColumn === index ? -state.sortDirection : 1;
                            state.sortColumn = index;
                            rows.sort(function (left, right) {
                                var a = (left.cells[index]?.textContent || '').trim();
                                var b = (right.cells[index]?.textContent || '').trim();
                                var aNumber = Number(a.replace(/[^\d.-]/g, ''));
                                var bNumber = Number(b.replace(/[^\d.-]/g, ''));
                                var bothNumeric = /\d/.test(a) && /\d/.test(b);
                                var dateLike = /\b(?:jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)\b|\d{4}-\d{2}-\d{2}/i;
                                var aDate = dateLike.test(a) ? Date.parse(a) : NaN;
                                var bDate = dateLike.test(b) ? Date.parse(b) : NaN;
                                var compare = Number.isFinite(aDate) && Number.isFinite(bDate)
                                    ? aDate - bDate
                                    : (bothNumeric && Number.isFinite(aNumber) && Number.isFinite(bNumber)
                                        ? aNumber - bNumber
                                        : a.localeCompare(b, undefined, { numeric: true, sensitivity: 'base' }));
                                return compare * state.sortDirection;
                            });
                            rows.forEach(function (row) { table.tBodies[0].appendChild(row); });
                            headers.forEach(function (item, itemIndex) {
                                if (itemIndex === index) {
                                    item.dataset.sortDirection = state.sortDirection === 1 ? 'asc' : 'desc';
                                    item.setAttribute('aria-sort', state.sortDirection === 1 ? 'ascending' : 'descending');
                                } else {
                                    delete item.dataset.sortDirection;
                                    item.setAttribute('aria-sort', 'none');
                                }
                            });
                            state.page = 1;
                            draw();
                        });
                        header.addEventListener('keydown', function (event) {
                            if (event.key === 'Enter' || event.key === ' ') {
                                event.preventDefault();
                                header.click();
                            }
                        });
                    });
                    draw();
                });
            }

            window.addEventListener('ajax:page-ready', function () { initDataTables(document); });
            window.__initCommerceDataTables = initDataTables;
            document.addEventListener('input', function (event) {
                var search = event.target.closest('[data-product-picker-search]');
                if (!search) return;
                var field = search.closest('.field');
                var picker = field && field.querySelector('.product-picker');
                if (!picker) return;
                var term = search.value.trim().toLocaleLowerCase();
                picker.querySelectorAll('.product-choice').forEach(function (choice) {
                    choice.hidden = !!term && !choice.textContent.toLocaleLowerCase().includes(term);
                });
            });
            initDataTables(document);
        })();
    </script>
    @stack('scripts')
    @include('admin.include.media-picker')
</body>
</html>
