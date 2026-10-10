<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Product Announcements</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=3">
    <link rel="icon" href="{{ asset('favicon.ico') }}?v=3" sizes="any">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { box-sizing:border-box }
        body { margin:0; background:var(--bg,#f3f5f7); color:var(--text,#101828); font:14px 'Plus Jakarta Sans',sans-serif; transition:background .35s,color .35s; overflow-x:clip }
        .layout { min-height:100vh }
        .main { margin-left:268px; min-width:0; transition:margin .35s cubic-bezier(.4,0,.2,1) }
        .layout.collapsed .main { margin-left:84px }
        .content { padding:24px 26px calc(40px + env(safe-area-inset-bottom)); max-width:1500px; margin:auto; display:grid; gap:22px; min-width:0 }
        .head,.head-copy,.card-head,.actions,.item-top,.product-tags { display:flex; align-items:center; gap:12px }
        .head,.card-head,.item-top { justify-content:space-between }
        .head { min-height:62px }
        .head-copy { min-width:0; display:block }
        .eyebrow { display:flex; align-items:center; gap:7px; margin-bottom:7px; color:var(--forest,#0e5a75); font-size:10px; font-weight:800; letter-spacing:.13em; text-transform:uppercase }
        .eyebrow i { font-size:14px }
        h1,h2,h3,p { margin:0 }
        h1 { font-size:24px; font-weight:800; letter-spacing:-.4px }
        .sub,.muted { color:var(--muted,#68788f) }
        .sub { margin-top:4px; line-height:1.6; font-size:13px }
        .card { background:var(--card,#fff); border:1px solid var(--border,#e8edf3); border-radius:16px; box-shadow:var(--shadow,0 8px 24px rgba(16,24,40,.05)); overflow:hidden; min-width:0; transition:background .35s,border-color .35s,box-shadow .25s }
        .card-head { padding:20px 24px; border-bottom:1px solid var(--border,#e8edf3) }
        .card-head h2 { font-size:15px; font-weight:800; letter-spacing:-.15px }
        .card-head p { color:var(--muted,#68788f); font-size:12px; margin-top:4px }
        .form { padding:24px; display:grid; gap:19px }
        .grid { display:grid; grid-template-columns:1fr 1fr; gap:16px }
        label { display:block; font-size:11px; font-weight:800; letter-spacing:.07em; text-transform:uppercase; margin-bottom:7px; color:var(--muted,#68788f) }
        input,select,textarea { width:100%; color:var(--text,#101828); background:var(--bg,#f3f5f7); border:1px solid var(--border,#e8edf3); border-radius:10px; padding:12px 13px; font:500 13px 'Plus Jakarta Sans',sans-serif; outline:none; transition:border-color .2s,box-shadow .2s,background .35s }
        input:focus,select:focus,textarea:focus { border-color:#35bdd6; box-shadow:0 0 0 3px rgba(53,189,214,.13) }
        .select-wrap{position:relative}
        .notice-type-trigger{width:100%;display:flex;align-items:center;justify-content:space-between;padding:12px 13px;border:1px solid var(--border,#e8edf3);border-radius:10px;background:var(--bg,#f3f5f7);color:var(--text,#101828);font:500 13px 'Plus Jakarta Sans',sans-serif;text-align:left;cursor:pointer}
        .notice-type-trigger:focus-visible{outline:none;border-color:#35bdd6;box-shadow:0 0 0 3px rgba(53,189,214,.13)}
        .notice-type-trigger>span{display:flex;align-items:center;gap:9px}
        .notice-type-trigger>span i{font-size:17px;color:var(--forest,#0e5a75)}
        .notice-type-trigger>.notice-chevron{font-size:18px;color:var(--forest,#0e5a75);transition:transform .2s}
        .notice-type-trigger[aria-expanded=true]>.notice-chevron{transform:rotate(180deg)}
        .notice-type-options{position:absolute;z-index:5;top:calc(100% + 6px);left:0;right:0;display:grid;gap:3px;padding:6px;border:1px solid var(--border,#e8edf3);border-radius:12px;background:var(--card,#fff);box-shadow:0 12px 28px rgba(16,24,40,.15)}
        .notice-type-options[hidden]{display:none}
        .notice-type-option{display:flex;align-items:center;gap:10px;width:100%;padding:10px;border:0;border-radius:8px;background:transparent;color:var(--text,#101828);font:600 12px 'Plus Jakarta Sans',sans-serif;text-align:left;cursor:pointer}
        .notice-type-option:hover,.notice-type-option[aria-selected=true],.notice-type-option:focus-visible{outline:none;background:var(--lime-soft,#e4f6fb)}
        .notice-type-option i{width:20px;color:var(--forest,#0e5a75);font-size:17px;text-align:center}
        input[type=checkbox] { accent-color:var(--forest,#0e5a75) }
        textarea { min-height:110px; resize:vertical; line-height:1.6 }
        .categories { border:1px solid var(--border,#e8edf3); border-radius:12px; overflow:hidden }
        .categories input[type=search] { border:0; border-radius:0; border-bottom:1px solid var(--border,#e8edf3); background:transparent }
        .category-list { max-height:260px; overflow:auto; padding:6px }
        .category { display:flex; gap:10px; align-items:center; padding:10px; border-radius:8px; cursor:pointer }
        .category:hover { background:rgba(62,197,224,.08) }
        .category input { width:16px; height:16px; margin:0; accent-color:#1487a6 }
        .category-name { font-weight:700 }
        .category-icon{width:32px;height:32px;flex:none;display:grid;place-items:center;border:1px solid var(--border);border-radius:10px;background:var(--lime-soft);color:var(--forest);font-size:17px}
        .hint { font-size:11px; color:var(--muted,#68788f); margin-top:7px; line-height:1.5 }
        .checkline { display:flex; align-items:center; gap:9px; color:var(--muted,#68788f); font-size:12px; font-weight:700 }
        .checkline input { width:16px; height:16px; margin:0; accent-color:#1487a6 }
        .btn { display:inline-flex; justify-content:center; align-items:center; gap:8px; border:0; border-radius:10px; padding:11px 15px; font:700 12px 'Plus Jakarta Sans',sans-serif; cursor:pointer; transition:transform .2s,box-shadow .2s,border-color .2s }
        .primary { color:white; background:linear-gradient(135deg,var(--forest,#0e5a75),var(--forest-2,#17739b)); box-shadow:0 7px 16px rgba(14,90,117,.2) }
        .primary:hover:not(:disabled) { transform:translateY(-1px); box-shadow:0 10px 20px rgba(14,90,117,.26) }
        .secondary { background:var(--bg,#f3f5f7); color:var(--text,#101828); border:1px solid var(--border,#e8edf3) }
        .danger { color:#b42318; background:#fff1f0 }
        .btn:disabled { opacity:.55; cursor:wait }
        .table-wrap { overflow:auto }
        table { border-collapse:collapse; width:100%; min-width:760px }
        th { text-align:left; background:var(--bg,#f3f5f7); color:var(--muted,#68788f); font-size:10px; font-weight:800; letter-spacing:.09em; text-transform:uppercase; padding:12px 18px }
        td { padding:15px 18px; border-top:1px solid var(--border,#e8edf3); vertical-align:top; line-height:1.5 }
        tbody tr { transition:background .2s }
        tbody tr:hover { background:rgba(62,197,224,.035) }
        .title { font-weight:800 }
        .message-preview { max-width:320px; white-space:pre-wrap; color:var(--muted,#68788f); font-size:12px; margin-top:3px }
        .product-tags { flex-wrap:wrap; align-items:flex-start; gap:5px }
        .tag,.status { display:inline-flex; align-items:center; padding:5px 9px; border-radius:20px; background:var(--lime-soft,#e4f6fb); color:var(--forest,#0e5a75); font-size:10px; font-weight:800; white-space:nowrap }
        .head .status { padding:8px 12px; font-size:11px }
        .status.off { background:#f2f4f7; color:#68788f }
        .empty { padding:35px 20px; text-align:center; color:var(--muted,#68788f) }
        .actions { justify-content:flex-end; flex-wrap:wrap }
        body.dark .status.off { background:rgba(139,160,184,.12); color:var(--muted) }
        body.dark .danger { background:rgba(248,113,113,.12); color:#fca5a5 }
        body.dark .secondary { background:var(--bg); color:var(--text); border-color:var(--border) }
        body.dark .category:hover,body.dark tbody tr:hover { background:rgba(62,197,224,.07) }
        @media(max-width:992px) { .main,.layout.collapsed .main { margin-left:0 } }
        @media(max-width:680px) { .content { padding:18px 14px calc(32px + env(safe-area-inset-bottom)); gap:17px } .grid { grid-template-columns:1fr } .head { align-items:flex-start; flex-direction:column } .form { padding:18px } .card-head { padding:17px 18px } .head .status { align-self:flex-start } }
        @media(max-width:480px) { h1 { font-size:21px } .actions .btn { flex:1 } .table-wrap { margin:0 -1px } }
    </style>
    @include('admin.include.shell-layout')
</head>
<body>
    <div class="layout" id="layout">
    @include('admin.include.sidebar')
    <div class="main">
        @include('admin.include.navbar')
        <main class="content">
            <header class="head">
                <div class="head-copy">
                    <span class="eyebrow"><i class="ri-megaphone-line"></i> Customer communications</span>
                    <h1>Category announcements</h1>
                    <p class="sub">Share updates with customers who have purchased a product in a selected category.</p>
                </div>
                <a class="btn secondary" href="{{ route('admin.announcements.manage') }}"><i class="ri-table-line"></i>Manage announcements</a>
            </header>

            <section class="card">
                <div class="card-head">
                    <div>
                        <h2 id="formHeading">{{ $editAnnouncement ? 'Edit announcement' : 'Create an announcement' }}</h2>
                        <p>Each customer is counted and notified once, even if they purchased a product multiple times.</p>
                    </div>
                    <i class="ri-megaphone-line" style="font-size:24px;color:#35bdd6"></i>
                </div>
                <form class="form" id="announcementForm">
                    <div class="grid">
                        <div>
                            <label for="title">Title</label>
                            <input id="title" name="title" maxlength="150" required placeholder="A short, clear headline" value="{{ $editAnnouncement?->title }}">
                        </div>
                        <div>
                            <label for="noticeTypeButton">Notice style</label>
                            @php
                                $noticeTypes = [
                                    'info' => ['Information', 'ri-information-line'],
                                    'good' => ['Good news', 'ri-sparkling-line'],
                                    'success' => ['Order completed', 'ri-checkbox-circle-line'],
                                    'delay' => ['Delivery delay', 'ri-time-line'],
                                    'warning' => ['Important warning', 'ri-alert-line'],
                                ];
                                $selectedType = $editAnnouncement?->type ?? 'info';
                            @endphp
                            <div class="select-wrap" id="noticeType">
                                <input type="hidden" id="type" name="type" value="{{ $selectedType }}">
                                <button class="notice-type-trigger" id="noticeTypeButton" type="button" aria-haspopup="listbox" aria-expanded="false" aria-controls="noticeTypeOptions">
                                    <span><i class="{{ $noticeTypes[$selectedType][1] }}" aria-hidden="true"></i><span>{{ $noticeTypes[$selectedType][0] }}</span></span>
                                    <i class="ri-arrow-down-s-line notice-chevron" aria-hidden="true"></i>
                                </button>
                                <div class="notice-type-options" id="noticeTypeOptions" role="listbox" aria-label="Notice style" hidden>
                                    @foreach ($noticeTypes as $value => [$label, $icon])
                                        <button class="notice-type-option" type="button" role="option" data-value="{{ $value }}" data-label="{{ $label }}" data-icon="{{ $icon }}" aria-selected="{{ $selectedType === $value ? 'true' : 'false' }}" tabindex="-1">
                                            <i class="{{ $icon }}" aria-hidden="true"></i>{{ $label }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="message">Message</label>
                        <textarea id="message" name="message" maxlength="2000" required placeholder="Write the update customers should see…">{{ $editAnnouncement?->message }}</textarea>
                        <p class="hint">Up to 2,000 characters. Notices appear on qualifying customers' dashboard and announcements page.</p>
                    </div>
                    <div>
                        <label>Target categories</label>
                        <div class="categories">
                            <input type="search" id="categorySearch" placeholder="Find a category…" aria-label="Search categories">
                            <div class="category-list" id="categoryList">
                                @forelse ($categories as $category)
                                    @php
                                        $categoryIconClass = $category->iconClass();
                                    @endphp
                                    <label class="category" data-search="{{ strtolower($category->name) }}">
                                        <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" {{ $editAnnouncement?->categories->contains('id', $category->id) ? 'checked' : '' }}>
                                        <span class="category-icon"><i class="{{ $categoryIconClass }}" aria-hidden="true"></i></span>
                                        <span class="category-name">{{ $category->name }}</span>
                                    </label>
                                @empty
                                    <p class="empty">Add categories with products to your catalog before creating an announcement.</p>
                                @endforelse
                            </div>
                        </div>
                        <p class="hint">Choose one or more categories. Customers qualify when they have a matching product in a paid order.</p>
                    </div>
                    <label class="checkline"><input type="checkbox" id="active" {{ ! $editAnnouncement || $editAnnouncement->is_active ? 'checked' : '' }}> Publish immediately</label>
                    <div class="actions" style="justify-content:flex-start">
                        <button class="btn primary" id="saveButton" type="submit"><i class="ri-send-plane-line"></i> {{ $editAnnouncement ? 'Save changes' : 'Publish announcement' }}</button>
                        <a class="btn secondary" id="cancelEdit" href="{{ route('admin.announcements') }}" {{ $editAnnouncement ? '' : 'hidden' }}>Cancel edit</a>
                    </div>
                </form>
            </section>
        </main>
    </div>
    </div>
    <script>
        (function () {
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const endpoints = {
            store: @json(route('admin.announcements.store')),
            update: @json(route('admin.announcements.update', ['announcement' => '__ID__'])),
            toggle: @json(route('admin.announcements.toggle', ['announcement' => '__ID__'])),
            delete: @json(route('admin.announcements.delete', ['announcement' => '__ID__']))
        };
        let editingId = @json($editAnnouncement?->id);
        const form = document.getElementById('announcementForm');
        const saveButton = document.getElementById('saveButton');
        const toast = Swal.mixin({toast:true,position:'top-end',showConfirmButton:false,timer:2800,timerProgressBar:true});
        const endpoint = (name, id) => endpoints[name].replace('__ID__', id);

        document.getElementById('categorySearch').addEventListener('input', event => {
            const term = event.target.value.trim().toLowerCase();
            document.querySelectorAll('#categoryList .category').forEach(row => {
                row.hidden = !row.dataset.search.includes(term);
            });
        });

        const typeButton = document.getElementById('noticeTypeButton');
        const typeWrap = document.getElementById('noticeType');
        const announcementContent = document.querySelector('.content');
        const typeOptions = document.getElementById('noticeTypeOptions');
        const typeInput = document.getElementById('type');
        const options = [...typeOptions.querySelectorAll('[role="option"]')];
        function closeTypeOptions(restoreFocus) {
            typeButton.setAttribute('aria-expanded', 'false');
            typeOptions.hidden = true;
            if (restoreFocus) typeButton.focus();
        }
        function selectType(option) {
            typeInput.value = option.dataset.value;
            typeButton.querySelector('span').innerHTML = `<i class="${option.dataset.icon}" aria-hidden="true"></i><span>${option.dataset.label}</span>`;
            options.forEach(item => item.setAttribute('aria-selected', item === option ? 'true' : 'false'));
            closeTypeOptions(true);
        }
        typeButton.addEventListener('click', () => {
            const opening = typeOptions.hidden;
            typeOptions.hidden = !opening;
            typeButton.setAttribute('aria-expanded', String(opening));
            if (opening) options.find(option => option.dataset.value === typeInput.value)?.focus();
        });
        options.forEach((option, index) => {
            option.addEventListener('click', () => selectType(option));
            option.addEventListener('keydown', event => {
                let next = index;
                if (event.key === 'ArrowDown') next = (index + 1) % options.length;
                else if (event.key === 'ArrowUp') next = (index - 1 + options.length) % options.length;
                else if (event.key === 'Home') next = 0;
                else if (event.key === 'End') next = options.length - 1;
                else if (event.key === 'Escape') { event.preventDefault(); closeTypeOptions(true); return; }
                else return;
                event.preventDefault();
                options[next].focus();
            });
        });
        typeButton.addEventListener('keydown', event => {
            if (['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(event.key)) {
                event.preventDefault();
                typeOptions.hidden = false;
                typeButton.setAttribute('aria-expanded', 'true');
                const current = options.find(option => option.dataset.value === typeInput.value);
                const currentIndex = options.indexOf(current);
                const targetIndex = event.key === 'ArrowDown'
                    ? (currentIndex + 1) % options.length
                    : event.key === 'ArrowUp'
                        ? (currentIndex - 1 + options.length) % options.length
                        : currentIndex;
                options[targetIndex]?.focus();
            } else if (event.key === 'Escape') {
                closeTypeOptions(false);
            }
        });
        typeWrap.addEventListener('focusout', event => {
            if (!typeWrap.contains(event.relatedTarget)) closeTypeOptions(false);
        });
        announcementContent.addEventListener('click', event => {
            if (!typeWrap.contains(event.target)) closeTypeOptions(false);
        });

        async function request(url, method, body) {
            const response = await fetch(url, {
                method,
                headers: {'Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':csrf},
                body
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                const first = data.errors && Object.values(data.errors)[0];
                throw new Error(first ? first[0] : (data.message || 'Request failed. Please try again.'));
            }
            return data;
        }

        form.addEventListener('submit', async event => {
            event.preventDefault();
            if (!form.reportValidity()) return;
            const selected = [...form.querySelectorAll('[name="category_ids[]"]:checked')];
            if (!selected.length) {
                toast.fire({icon:'error',title:'Choose at least one target category.'});
                return;
            }
            saveButton.disabled = true;
            const body = new FormData(form);
            body.set('is_active', document.getElementById('active').checked ? '1' : '0');
            const url = editingId ? endpoint('update', editingId) : endpoints.store;
            if (editingId) body.set('_method', 'PATCH');
            try {
                const wasEditing = editingId !== null;
                await request(url, 'POST', body);
                toast.fire({icon:'success',title:wasEditing ? 'Announcement updated.' : 'Announcement created.'});
                window.setTimeout(() => {
                    const destination = @json(route('admin.announcements.manage'));
                    if (typeof window.ajaxNavigate === 'function') window.ajaxNavigate(destination);
                    else window.location.assign(destination);
                }, 500);
            } catch (error) {
                saveButton.disabled = false;
                toast.fire({icon:'error',title:error.message});
            }
        });

        })();
    </script>
</body>
</html>
