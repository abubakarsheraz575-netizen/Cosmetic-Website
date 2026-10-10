<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Support Inbox — Cosmetics Admin</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}?v=3">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <style data-page>
        *{box-sizing:border-box}
        body{margin:0;background:#f3f5f7;color:#101828;font-family:'Plus Jakarta Sans',sans-serif;font-size:14px}
        .layout{min-height:100vh}
        .sidebar{position:fixed;left:0;top:0;bottom:0;width:268px;display:flex;flex-direction:column;z-index:50;transition:width .35s,transform .35s}
        .layout.collapsed .sidebar{width:84px}
        .s-nav{flex:1;min-height:0;overflow-y:auto}
        .support-main{min-height:100vh;margin-left:268px;min-width:0;padding:25px 26px 38px;transition:margin .3s}
        .layout.collapsed .support-main{margin-left:84px}
        .support-page-head{display:flex;align-items:center;gap:13px;margin-bottom:20px}
        .support-menu-toggle{display:none;width:42px;height:42px;flex:none;place-items:center;border:1px solid #e3eaf0;border-radius:12px;background:#fff;color:#145d74;font-size:20px;cursor:pointer}
        .support-page-icon{width:46px;height:46px;display:grid;place-items:center;border-radius:14px;background:linear-gradient(135deg,#0e5a75,#17739b);color:#3ec5e0;font-size:22px;box-shadow:0 8px 20px rgba(14,90,117,.22)}
        .support-page-head h1{margin:0;font-size:21px;font-weight:800;letter-spacing:-.4px}
        .support-page-head p{margin:4px 0 0;color:#68788f;font-size:12.5px}
        .support-app{height:min(760px,calc(100vh - 142px));min-height:500px;display:grid;grid-template-columns:310px minmax(0,1fr);grid-template-rows:minmax(0,1fr);overflow:hidden;border:1px solid #e3eaf0;border-radius:20px;background:#fff;box-shadow:0 12px 35px rgba(16,24,40,.08)}
        .support-inbox{min-width:0;display:flex;flex-direction:column;border-right:1px solid #e8edf3;background:#fff}
        .inbox-head{padding:20px 18px 14px;border-bottom:1px solid #edf0f3}
        .inbox-title{display:flex;align-items:center;justify-content:space-between;gap:8px}
        .inbox-title h2{margin:0;font-size:15px;font-weight:800}
        .inbox-live{display:inline-flex;align-items:center;gap:6px;color:#13875e;font-size:10.5px;font-weight:700}
        .live-dot{width:7px;height:7px;border-radius:50%;background:#24bf83;box-shadow:0 0 0 3px rgba(36,191,131,.13)}
        .inbox-search{display:flex;align-items:center;gap:8px;margin-top:13px;padding:9px 11px;border:1px solid #e8edf3;border-radius:11px;background:#f7f9fa;color:#7a8795}
        .inbox-search i{font-size:16px}
        .inbox-search input{width:100%;border:0;outline:0;background:transparent;color:#101828;font:500 12px 'Plus Jakarta Sans',sans-serif}
        .inbox-list{flex:1;min-height:0;overflow:auto}
        .inbox-pager{min-height:48px;display:flex;align-items:center;justify-content:space-between;gap:8px;padding:8px 13px;border-top:1px solid #edf0f3;background:#fff;color:#7b8794;font-size:10px}
        .inbox-pager[hidden]{display:none}
        .inbox-pager-actions{display:flex;gap:5px}
        .inbox-pager button{width:28px;height:28px;display:grid;place-items:center;border:1px solid #e5ebef;border-radius:8px;background:#fff;color:#465967;font-size:15px;cursor:pointer}
        .inbox-pager button:hover:not(:disabled){border-color:#52b9bd;color:#147a82}
        .inbox-pager button:disabled{opacity:.4;cursor:not-allowed}
        .inbox-empty{padding:45px 22px;text-align:center;color:#8995a2;font-size:12px;line-height:1.6}
        .inbox-empty i{display:block;margin-bottom:9px;color:#85cbd0;font-size:28px}
        .inbox-conversation{width:100%;display:flex;align-items:center;gap:11px;padding:13px 15px;border:0;border-bottom:1px solid #f1f3f5;background:#fff;text-align:left;cursor:pointer;transition:background .18s}
        .inbox-conversation:hover{background:#f7fbfb}
        .inbox-conversation.active{background:#eaf7f6;box-shadow:inset 3px 0 #38aab4}
        .inbox-avatar{position:relative;width:42px;height:42px;flex:none;display:grid;place-items:center;border-radius:50%;background:linear-gradient(135deg,#d5f3ef,#e2e8fc);color:#247c83;font-size:13px;font-weight:800;overflow:hidden}
        .inbox-avatar img{width:100%;height:100%;object-fit:cover}
        .inbox-avatar i{position:absolute;right:0;bottom:0;width:11px;height:11px;border:2px solid #fff;border-radius:50%;background:#adb7c2}
        .inbox-avatar.online i{background:#21bc7d}
        .inbox-info{min-width:0;flex:1}
        .inbox-info-top{display:flex;align-items:center;justify-content:space-between;gap:7px}
        .inbox-info strong{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#253245;font-size:12px;font-weight:800}
        .inbox-time{flex:none;color:#9aa4af;font-size:9px}
        .inbox-preview-row{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-top:4px}
        .inbox-preview{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#8994a0;font-size:10.5px}
        .inbox-unread{min-width:18px;height:18px;display:grid;place-items:center;padding:0 5px;border-radius:10px;background:#28a989;color:#fff;font-size:9px;font-weight:800}
        .support-conversation{min-width:0;min-height:0;height:100%;display:flex;flex-direction:column;overflow:hidden;background:#f4f7f6}
        .chat-empty{flex:1;display:grid;place-items:center;padding:35px;text-align:center;color:#8693a0}
        .chat-empty-icon{width:76px;height:76px;display:grid;place-items:center;margin:0 auto 15px;border-radius:24px;background:#e4f6f3;color:#32a5a6;font-size:34px}
        .chat-empty h3{margin:0;color:#314054;font-size:16px}
        .chat-empty p{max-width:330px;margin:7px auto 0;font-size:12px;line-height:1.65}
        .chat-pane{height:auto;min-height:0;display:flex;flex:1 1 auto;flex-direction:column;overflow:hidden}
        .chat-header{min-height:74px;display:flex;align-items:center;gap:12px;padding:13px 20px;border-bottom:1px solid #e6ecec;background:#fff}
        .chat-back{display:none;width:35px;height:35px;flex:none;border:0;border-radius:10px;background:#f1f5f5;color:#48646a;font-size:19px}
        .chat-head-avatar{width:42px;height:42px;flex:none;display:grid;place-items:center;border-radius:50%;background:linear-gradient(135deg,#d5f3ef,#e2e8fc);color:#247c83;font-weight:800;overflow:hidden}
        .chat-head-avatar img{width:100%;height:100%;object-fit:cover}
        .chat-head-info{min-width:0;flex:1}
        .chat-head-info strong{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#253245;font-size:13px}
        .chat-head-info span{display:block;margin-top:3px;color:#8b97a3;font-size:10.5px}
        .chat-head-info span.online{color:#178b63}
        .chat-lock{display:flex;align-items:center;gap:6px;color:#8a98a2;font-size:10px}
        .chat-lock i{color:#50aeb0;font-size:15px}
        .chat-delete{width:35px;height:35px;display:grid;place-items:center;border:1px solid #e6ecee;border-radius:10px;background:#fff;color:#9b5d63;font-size:17px;cursor:pointer}
        .chat-delete:hover{border-color:#f0c1c1;background:#fff3f3;color:#b42318}
        .chat-messages{flex:1;min-height:0;overflow:auto;padding:18px clamp(13px,3vw,35px);background-color:#f4f7f6;background-image:radial-gradient(rgba(56,170,180,.065) .8px,transparent .8px);background-size:17px 17px;scroll-behavior:smooth}
        .chat-day{width:max-content;margin:3px auto 16px;padding:5px 11px;border:1px solid #e7ecec;border-radius:20px;background:rgba(255,255,255,.85);color:#929da5;font-size:9.5px}
        .chat-message{display:flex;margin:11px 0;animation:chatPop .18s ease both}
        .chat-message.mine{justify-content:flex-end}
        .chat-bubble{max-width:min(78%,530px);padding:10px 13px 7px;border:1px solid rgba(22,35,60,.06);border-radius:14px 14px 14px 5px;background:#fff;color:#293748;box-shadow:0 3px 12px rgba(31,51,89,.06);overflow-wrap:anywhere}
        .chat-message.mine .chat-bubble{border-radius:14px 14px 5px 14px;border-color:rgba(56,170,180,.14);background:linear-gradient(135deg,#e1f6ee,#d6eeeb)}
        .chat-body{font-size:12.5px;line-height:1.55;white-space:pre-wrap}
        .chat-order-context{display:flex;align-items:center;gap:10px;margin:8px 0;padding:8px;border:1px solid rgba(56,170,180,.2);border-radius:10px;background:rgba(56,170,180,.06);font-size:10px}
        .chat-order-context img{width:44px;height:44px;object-fit:cover;border-radius:8px;background:#fff}
        .chat-order-context .chat-image-preview{width:44px;height:44px;max-height:44px;flex:0 0 44px}
        .chat-order-context .chat-image-preview img{width:100%;height:100%;max-height:none}
        .chat-order-context strong,.chat-order-context a{display:block;color:#286e83;font-weight:700;text-decoration:none}
        .chat-order-context span{display:block;margin-top:3px;color:#778394}
        .chat-attachment{display:flex;align-items:center;gap:9px;margin:2px 0 6px;padding:9px 10px;border:1px solid rgba(22,35,60,.08);border-radius:10px;background:rgba(255,255,255,.7);color:#286e83;text-decoration:none}
        .chat-attachment>i{font-size:20px}
        .chat-attachment span{max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:10.5px;font-weight:700}
        .chat-image-preview{display:block;width:min(100%,300px);padding:0;overflow:hidden;border:1px solid rgba(22,35,60,.1);border-radius:12px;background:#fff;cursor:zoom-in}
        .chat-image-preview img{display:block;width:100%;max-height:230px;object-fit:cover}
        .chat-image-download{display:flex;align-items:center;gap:7px;margin-top:7px;color:#286e83;font-size:10.5px;font-weight:700;text-decoration:none}
        .support-lightbox{position:fixed;inset:50% auto auto 50%;transform:translate(-50%,-50%);width:min(92vw,1100px);max-width:none;max-height:90dvh;margin:0;overflow:auto;padding:12px;border:1px solid rgba(255,255,255,.2);border-radius:18px;background:#111c2e;color:#fff;box-shadow:0 24px 80px rgba(0,0,0,.45)}
        .support-lightbox::backdrop{background:rgba(9,16,28,.82);backdrop-filter:blur(5px)}
        .support-lightbox-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:2px 2px 10px}
        .support-lightbox-name{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12px}
        .support-lightbox-actions{display:flex;align-items:center;gap:8px}
        .support-lightbox-actions a,.support-lightbox-close{width:34px;height:34px;display:grid;place-items:center;border:1px solid rgba(255,255,255,.16);border-radius:10px;background:rgba(255,255,255,.08);color:#fff;text-decoration:none;font-size:17px;cursor:pointer}
        .support-lightbox img{display:block;max-width:100%;max-height:calc(90dvh - 74px);margin:auto;border-radius:10px;object-fit:contain}
        .chat-time{display:flex;justify-content:flex-end;gap:4px;margin-top:4px;color:#929ca6;font-size:9px}
        .chat-time i{color:#36aeb1;font-size:13px}
        .chat-typing{min-height:22px;padding:2px 19px;color:#8996a1;font-size:10.5px}
        .chat-compose{display:block;flex:0 0 auto;position:relative;z-index:1;padding:11px 14px 13px;border-top:1px solid #e6ecec;background:#fff}
        .chat-file-chip{display:none;align-items:center;gap:7px;margin:0 2px 8px;padding:6px 10px;border-radius:9px;background:#eef8f7;color:#367f82;font-size:10.5px}
        .chat-file-chip.show{display:inline-flex}
        .chat-file-chip span{max-width:210px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .chat-file-chip button{width:22px;height:22px;display:grid;place-items:center;border:0;border-radius:7px;background:transparent;color:#667783;font-size:15px;cursor:pointer}
        .chat-file-chip button:hover{background:#dcefed;color:#246f75}
        .chat-compose-row{display:flex;align-items:flex-end;gap:8px;padding:6px 7px 6px 10px;border:1px solid #e0e7e9;border-radius:15px;background:#fbfcfc;transition:.2s}
        .chat-compose-row:focus-within{border-color:#52b9bd;box-shadow:0 0 0 3px rgba(56,170,180,.1)}
        .chat-action{width:36px;height:36px;flex:none;display:grid;place-items:center;border:0;border-radius:11px;background:transparent;color:#687986;font-size:19px;cursor:pointer}
        .chat-action:hover{background:#eff7f7;color:#277f87}
        .chat-compose textarea{flex:1;min-width:0;max-height:115px;min-height:36px;resize:none;border:0;outline:0;padding:8px 2px;background:transparent;color:#273747;font:400 12px 'Plus Jakarta Sans',sans-serif}
        .chat-submit{background:linear-gradient(135deg,#0e5a75,#17739b);color:#fff;box-shadow:0 4px 11px rgba(14,90,117,.2)}
        .chat-submit:disabled{opacity:.5;cursor:wait}
        .chat-note{margin:7px 3px 0;color:#9aa4ac;font-size:9.5px}
        @keyframes chatPop{from{opacity:0;transform:translateY(4px)}to{opacity:1;transform:none}}
        body.dark .support-app,body.dark .support-inbox,body.dark .chat-header,body.dark .chat-compose{background:#111c2e;border-color:#1e2c42}
        body.dark .inbox-conversation{background:#111c2e;border-color:#1e2c42;color:#e7edf5}
        body.dark .inbox-conversation:hover{background:#17263b}
        body.dark .inbox-conversation.active{background:rgba(62,197,224,.1)}
        body.dark .inbox-info strong,body.dark .chat-head-info strong{color:#e7edf5}
        body.dark .chat-messages{background-color:#0b1220}
        body.dark .chat-bubble{background:#17263b;color:#e7edf5;border-color:#26364c}
        body.dark .chat-order-context{background:rgba(62,197,224,.08);border-color:rgba(62,197,224,.18)}
        body.dark .chat-compose-row,body.dark .inbox-search{background:#0b1220;border-color:#26364c}
        body.dark .chat-compose textarea,body.dark .inbox-search input{color:#e7edf5}
        body.dark .chat-day{background:#111c2e;border-color:#26364c}
        body.dark .chat-message.mine .chat-bubble{background:#123c42}
        body.dark .inbox-pager{background:#111c2e;border-color:#1e2c42;color:#8ba0b8}
        body.dark .inbox-pager button{background:#0b1220;border-color:#26364c;color:#e7edf5}
        @media(max-width:992px){.sidebar{position:fixed!important;top:0;left:0;bottom:0;width:268px!important;z-index:60;transform:translateX(-105%);overflow-y:auto}.layout.mobile-open .sidebar,.sidebar.mobile-open{transform:none}.support-main{margin-left:0!important}.layout.collapsed .support-main{margin-left:0}.support-menu-toggle{display:grid}.support-app{height:calc(100dvh - 155px)}}
        @media(max-width:680px){.support-main{padding:17px 12px 30px}.support-page-head{margin-bottom:14px}.support-page-head h1{font-size:19px}.support-app{position:relative;display:block;height:calc(100dvh - 145px);min-height:400px;border-radius:16px}.support-inbox,.support-conversation{position:absolute;inset:0}.support-inbox{z-index:2;border-right:0}.support-conversation{z-index:3;display:none;height:auto}.support-app.chat-open .support-inbox{display:none}.support-app.chat-open .support-conversation{display:flex}.chat-back{display:grid;place-items:center}.chat-lock{display:none}.chat-header{gap:8px;padding:11px}.chat-messages{padding:15px 11px}.chat-bubble{max-width:89%}}
        @media(prefers-reduced-motion:reduce){.chat-message{animation:none}}
    </style>
</head>
<body>
    <div class="layout" id="layout">
        @include('admin.include.sidebar')
        <main class="support-main">
            <div class="support-page-head"><button type="button" class="support-menu-toggle" id="menuBtn" aria-label="Open navigation"><i class="ri-menu-line"></i></button><span class="support-page-icon"><i class="ri-customer-service-2-line"></i></span><div><h1>Customer Support</h1><p>Private conversations with your customers</p></div></div>
            <section class="support-app" id="supportApp">
                <aside class="support-inbox">
                    <div class="inbox-head">
                        <div class="inbox-title"><h2>Customers</h2><span class="inbox-live"><i class="live-dot"></i>Live</span></div>
                        <label class="inbox-search"><i class="ri-search-line"></i><input id="inboxSearch" type="search" placeholder="Search all customers…" autocomplete="off" aria-label="Search customers"></label>
                    </div>
                    <div class="inbox-list" id="inboxList"><div class="inbox-empty"><i class="ri-loader-4-line"></i>Loading conversations…</div></div>
                    <div class="inbox-pager" id="inboxPager" hidden>
                        <span id="inboxPageSummary"></span>
                        <div class="inbox-pager-actions">
                            <button type="button" id="inboxPrevious" aria-label="Previous page"><i class="ri-arrow-left-s-line"></i></button>
                            <button type="button" id="inboxNext" aria-label="Next page"><i class="ri-arrow-right-s-line"></i></button>
                        </div>
                    </div>
                </aside>
                <section class="support-conversation" id="supportConversation" aria-live="polite">
                    <div class="chat-empty"><div><div class="chat-empty-icon"><i class="ri-chat-3-line"></i></div><h3>Your support inbox</h3><p>Choose a customer conversation to read their messages and reply directly.</p></div></div>
                </section>
            </section>
        </main>
    </div>
    <dialog class="support-lightbox" id="supportLightbox" aria-label="Image preview">
        <div class="support-lightbox-head">
            <span class="support-lightbox-name" id="supportLightboxName"></span>
            <span class="support-lightbox-actions">
                <a id="supportLightboxDownload" href="#" download aria-label="Download image"><i class="ri-download-2-line"></i></a>
                <button type="button" class="support-lightbox-close" id="supportLightboxClose" aria-label="Close image preview"><i class="ri-close-line"></i></button>
            </span>
        </div>
        <img id="supportLightboxImage" alt="">
    </dialog>
    <script src="{{ asset('js/support-message-actions.js') }}"></script>
    <script>
    (function () {
        if (window.__supportAdminCleanup) window.__supportAdminCleanup();
        var app = document.getElementById('supportApp');
        if (!app) return;
        var inboxList = document.getElementById('inboxList');
        var search = document.getElementById('inboxSearch');
        var chat = document.getElementById('supportConversation');
        var lightbox = document.getElementById('supportLightbox');
        var lightboxImage = document.getElementById('supportLightboxImage');
        var token = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
        var inboxUrl = @json(route('admin.support.inbox'));
        var startConversationUrlTemplate = @json(route('admin.support.conversations.start', '__USER__'));
        var messagesUrlTemplate = @json(route('admin.support.messages', '__ID__'));
        var sendUrlTemplate = @json(route('admin.support.send', '__ID__'));
        var heartbeatUrlTemplate = @json(route('admin.support.heartbeat', '__ID__'));
        var actionUrlTemplates = {
            edit: @json(route('admin.support.messages.edit', ['conversation' => '__CONVERSATION__', 'message' => '__MESSAGE__'])),
            delete: @json(route('admin.support.messages.delete', ['conversation' => '__CONVERSATION__', 'message' => '__MESSAGE__'])),
            pin: @json(route('admin.support.messages.pin', ['conversation' => '__CONVERSATION__', 'message' => '__MESSAGE__'])),
            star: @json(route('admin.support.messages.star', ['conversation' => '__CONVERSATION__', 'message' => '__MESSAGE__'])),
            report: @json(route('admin.support.messages.report', ['conversation' => '__CONVERSATION__', 'message' => '__MESSAGE__'])),
            conversation: @json(route('admin.support.conversations.delete', '__CONVERSATION__'))
        };
        var selectedId = null, selectedUser = null, selectedUserId = null, lastId = 0, changeCursor = null, replyToMessageId = null, initialMessagesLoaded = false, inboxData = [], stopped = false, typing = false, typingTimer = null;
        var inboxPage = 1, inboxPages = {current_page:1,last_page:1,per_page:30,total:0}, inboxLoading = false, inboxRefreshQueued = false, searchTimer = null;
        var requests = new AbortController();

        function cleanup() {
            if (stopped) return;
            stopped = true;
            requests.abort();
            clearInterval(inboxTimer);
            clearInterval(messageTimer);
            clearInterval(heartbeatTimer);
            clearTimeout(typingTimer);
            clearTimeout(searchTimer);
            observer.disconnect();
            if (lightbox.open) lightbox.close();
            if (window.__supportAdminCleanup === cleanup) window.__supportAdminCleanup = null;
        }
        var observer = new MutationObserver(function () { if (!app.isConnected) cleanup(); });
        observer.observe(document.body, { childList: true, subtree: true });
        window.__supportAdminCleanup = cleanup;

        function api(url, options) {
            options = options || {};
            options.credentials = 'same-origin';
            options.headers = Object.assign({
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': token
            }, options.headers || {});
            options.signal = requests.signal;
            return fetch(url, options).then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok) {
                        var errors = data.errors && Object.keys(data.errors);
                        var error = new Error((errors && errors.length && data.errors[errors[0]][0]) || data.message || 'Could not load support messages.');
                        error.status = response.status;
                        throw error;
                    }
                    return data;
                }, function () {
                    throw new Error('Support returned an invalid response. Please retry.');
                });
            });
        }

        function shortTime(value) {
            if (!value) return '';
            var date = new Date(value);
            return isNaN(date.getTime()) ? '' : date.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
        }

        function preview(conversation) {
            if (!conversation.last_message) return 'No messages yet · Start a conversation';
            var body = conversation.last_message.body || '';
            return body || (conversation.last_message.attachment ? '📎 ' + conversation.last_message.attachment.name : 'Attachment');
        }

        function renderInbox() {
            inboxList.replaceChildren();
            if (!inboxData.length) {
                var empty = document.createElement('div');
                empty.className = 'inbox-empty';
                empty.innerHTML = '<i class="ri-user-search-line"></i>' + (search.value.trim() ? 'No customers match your search.' : 'No customer accounts found.');
                inboxList.appendChild(empty);
                renderPager();
                return;
            }
            inboxData.forEach(function (item) {
                var button = document.createElement('button');
                button.type = 'button';
                button.className = 'inbox-conversation' + (String(item.user.id) === String(selectedUserId) ? ' active' : '');
                button.dataset.userId = item.user.id;
                var avatar = document.createElement('span');
                avatar.className = 'inbox-avatar' + (item.user_online ? ' online' : '');
                if (item.user.profile_photo) {
                    var image = document.createElement('img');
                    image.src = item.user.profile_photo;
                    image.alt = '';
                    avatar.appendChild(image);
                } else {
                    avatar.textContent = (item.user.name || 'U').split(/\s+/).map(function (part) { return part.charAt(0); }).slice(0, 2).join('').toUpperCase();
                }
                var dot = document.createElement('i');
                avatar.appendChild(dot);
                var info = document.createElement('span');
                info.className = 'inbox-info';
                var top = document.createElement('span');
                top.className = 'inbox-info-top';
                var name = document.createElement('strong');
                name.textContent = item.user.name;
                var time = document.createElement('span');
                time.className = 'inbox-time';
                time.textContent = shortTime(item.updated_at);
                top.append(name, time);
                var previewRow = document.createElement('span');
                previewRow.className = 'inbox-preview-row';
                var text = document.createElement('span');
                text.className = 'inbox-preview';
                text.textContent = preview(item);
                previewRow.appendChild(text);
                if (item.unread_count > 0) {
                    var badge = document.createElement('span');
                    badge.className = 'inbox-unread';
                    badge.textContent = item.unread_count > 99 ? '99+' : item.unread_count;
                    previewRow.appendChild(badge);
                }
                info.append(top, previewRow);
                button.append(avatar, info);
                button.addEventListener('click', function () { openConversation(item); });
                inboxList.appendChild(button);
            });
            renderPager();
        }

        function renderPager() {
            var pager = document.getElementById('inboxPager');
            var summary = document.getElementById('inboxPageSummary');
            var previous = document.getElementById('inboxPrevious');
            var next = document.getElementById('inboxNext');
            if (!pager || !summary || !previous || !next) return;
            var from = inboxPages.total ? ((inboxPages.current_page - 1) * inboxPages.per_page) + 1 : 0;
            var to = Math.min(inboxPages.current_page * inboxPages.per_page, inboxPages.total);
            pager.hidden = inboxPages.total <= inboxPages.per_page;
            summary.textContent = from + '–' + to + ' of ' + inboxPages.total + ' customers';
            previous.disabled = inboxPages.current_page <= 1;
            next.disabled = inboxPages.current_page >= inboxPages.last_page;
        }

        function updateInbox() {
            if (stopped) return;
            if (inboxLoading) {
                inboxRefreshQueued = true;
                return;
            }
            inboxLoading = true;
            var params = new URLSearchParams({page:String(inboxPage),search:search.value.trim()});
            api(inboxUrl + '?' + params.toString()).then(function (data) {
                if (stopped || !app.isConnected) return;
                inboxData = data.conversations || [];
                inboxPages = data.pagination || inboxPages;
                var unreadTotal = Number(data.unread_total || 0);
                var unreadBadge = document.getElementById('supportUnreadBadge');
                if (unreadBadge) {
                    unreadBadge.textContent = unreadTotal > 99 ? '99+' : String(unreadTotal);
                    unreadBadge.style.display = unreadTotal ? '' : 'none';
                }
                renderInbox();
                if (!selectedUserId) {
                    var firstConversation = inboxData.find(function (item) { return item.id; });
                    if (firstConversation) openConversation(firstConversation);
                }
                var latest = inboxData.find(function (item) { return String(item.user.id) === String(selectedUserId); });
                if (latest && selectedId) {
                    selectedUser = latest.user;
                    var status = document.getElementById('chatUserStatus');
                    if (status) setStatus(latest.user_online ? 'Online' : 'Offline', latest.user_online);
                }
            }).catch(function () {
                if (stopped || !app.isConnected || !inboxData.length) return;
                inboxList.innerHTML = '<div class="inbox-empty"><i class="ri-wifi-off-line"></i>Customer list could not load. Reconnecting…</div>';
            }).finally(function () {
                inboxLoading = false;
                if (inboxRefreshQueued && !stopped) {
                    inboxRefreshQueued = false;
                    updateInbox();
                }
            });
        }

        function setStatus(text, online) {
            var node = document.getElementById('chatUserStatus');
            if (node) {
                node.textContent = text;
                node.classList.toggle('online', !!online);
            }
        }

        function reportHeartbeatError(error) {
            if (stopped || !app.isConnected || error.name === 'AbortError') return;
            var typingLabel = document.getElementById('chatTyping');
            if (typingLabel && selectedId) typingLabel.textContent = error.message;
        }

        function openConversation(item) {
            if (String(item.user.id) === String(selectedUserId)) {
                app.classList.add('chat-open');
                return;
            }
            selectedId = item.id;
            initialMessagesLoaded = false;
            selectedUserId = item.user.id;
            selectedUser = item.user;
            lastId = 0;
            typing = false;
            app.classList.add('chat-open');
            clearInterval(messageTimer);
            chat.innerHTML = '<div class="chat-empty"><div><div class="chat-empty-icon"><i class="ri-loader-4-line"></i></div><h3>Opening conversation</h3><p>Loading this customer’s private support chat…</p></div></div>';
            renderInbox();

            if (selectedId) {
                showConversation(item);
                return;
            }

            api(startConversationUrlTemplate.replace('__USER__', encodeURIComponent(item.user.id)), {method:'POST'})
                .then(function (data) {
                    if (stopped || !app.isConnected || String(selectedUserId) !== String(item.user.id)) return;
                    selectedId = data.id;
                    item.id = data.id;
                    showConversation(item);
                    updateInbox();
                }).catch(function (error) {
                    if (stopped || !app.isConnected || error.name === 'AbortError' || String(selectedUserId) !== String(item.user.id)) return;
                    chat.innerHTML = '<div class="chat-empty"><div><div class="chat-empty-icon"><i class="ri-error-warning-line"></i></div><h3>Could not open chat</h3><p></p></div></div>';
                    chat.querySelector('.chat-empty p').textContent = error.message;
                });
        }

        function showConversation(item) {
            selectedId = item.id;
            var initials = (item.user.name || 'U').split(/\s+/).map(function (part) { return part.charAt(0); }).slice(0, 2).join('').toUpperCase();
            chat.innerHTML = '<div class="chat-pane">' +
                '<header class="chat-header"><button class="chat-back" type="button" aria-label="Back to conversations"><i class="ri-arrow-left-line"></i></button>' +
                '<div class="chat-head-avatar" id="chatUserAvatar"></div>' +
                '<div class="chat-head-info"><strong id="chatUserName"></strong><span id="chatUserStatus">Connecting…</span></div>' +
                '<div class="chat-lock"><i class="ri-lock-2-line"></i>Private conversation</div><button class="chat-delete" type="button" data-support-delete-conversation aria-label="Delete conversation" title="Delete entire conversation"><i class="ri-delete-bin-line"></i></button></header>' +
                '<div class="chat-messages" id="chatMessages" aria-live="polite"></div>' +
                '<div class="chat-typing" id="chatTyping"></div>' +
                '<form class="chat-compose" id="chatForm"><div class="support-reply-preview" id="chatReplyPreview" hidden></div><div class="chat-file-chip" id="chatFileChip"><i class="ri-attachment-2"></i><span id="chatFileName"></span><button class="support-file-clear" type="button" id="chatFileClear" aria-label="Remove attachment"><i class="ri-close-line"></i></button></div>' +
                '<div class="chat-compose-row"><button class="chat-action" type="button" id="chatAttach" aria-label="Attach a file"><i class="ri-attachment-2"></i></button>' +
                '<input type="file" id="chatFile" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.txt,.doc,.docx,.xls,.xlsx" hidden>' +
                '<textarea id="chatInput" maxlength="5000" rows="1" placeholder="" aria-label="Type a reply"></textarea>' +
                '<button class="chat-action chat-submit" id="chatSend" type="submit" aria-label="Send reply"><i class="ri-send-plane-2-fill"></i></button></div>' +
                '<p class="chat-note">Enter to send · Attach an image or document (max 10 MB)</p></form></div>';
            document.getElementById('chatUserName').textContent = item.user.name;
            document.getElementById('chatUserAvatar').textContent = initials;
            document.getElementById('chatInput').placeholder = 'Reply to ' + item.user.name + '…';
            if (item.user.profile_photo) {
                var avatar = document.createElement('img');
                avatar.src = item.user.profile_photo;
                avatar.alt = '';
                document.getElementById('chatUserAvatar').replaceChildren(avatar);
            }
            setStatus(item.user_online ? 'Online' : 'Offline', item.user_online);
            document.querySelector('.chat-back').addEventListener('click', closeConversation);
            replyToMessageId = null;
            wireComposer();
            window.SupportMessageActions.attach(chat, {
                messages: document.getElementById('chatMessages'),
                isAdmin: true,
                request: api,
                urlFor: function (message, action) {
                    return actionUrlTemplates[action]
                        .replace('__CONVERSATION__', encodeURIComponent(selectedId))
                        .replace('__MESSAGE__', encodeURIComponent(message.id));
                },
                deleteConversationUrl: actionUrlTemplates.conversation.replace('__CONVERSATION__', encodeURIComponent(selectedId)),
                onReply: setReplyTo,
                onUpdate: appendMessage,
                onDelete: function (id) {
                    var row = document.querySelector('#chatMessages [data-message-id="' + id + '"]');
                    if (row) row.remove();
                    updateInbox();
                },
                onConversationDelete: function () {
                    closeConversation();
                    updateInbox();
                }
            });
            document.getElementById('chatReplyPreview').addEventListener('support:reply-cancel', resetReply);
            updateMessages();
            heartbeat(false).catch(reportHeartbeatError);
            messageTimer = setInterval(function () {
                if (document.documentElement.getAttribute('aria-busy') !== 'true') updateMessages();
            }, 2000);
            renderInbox();
        }

        function closeConversation() {
            selectedId = null;
            selectedUser = null;
            selectedUserId = null;
            lastId = 0;
            changeCursor = null;
            replyToMessageId = null;
            clearInterval(messageTimer);
            chat.innerHTML = '<div class="chat-empty"><div><div class="chat-empty-icon"><i class="ri-chat-3-line"></i></div><h3>Your support inbox</h3><p>Choose a customer conversation to read their messages and reply directly.</p></div></div>';
            app.classList.remove('chat-open');
            renderInbox();
        }

        function appendMessage(message) {
            var box = document.getElementById('chatMessages');
            if (!box || !message || !message.id) return;
            var existing = box.querySelector('[data-message-id="' + message.id + '"]');
            if (message.deleted) {
                if (existing) existing.remove();
                lastId = Math.max(lastId, Number(message.id));
                return;
            }
            var mine = Number(message.sender_id) === Number(@json(auth()->id()));
            var row = document.createElement('article');
            row.className = 'chat-message' + (mine ? ' mine' : '');
            row.dataset.messageId = message.id;
            row.dataset.mine = mine ? '1' : '0';
            row.__supportMessage = message;
            var bubble = document.createElement('div');
            bubble.className = 'chat-bubble';
            var quote = window.SupportMessageActions.quoteElement(message);
            if (quote) bubble.appendChild(quote);
            if (message.body) {
                var body = document.createElement('div');
                body.className = 'chat-body';
                body.textContent = message.body;
                bubble.appendChild(body);
            }
            if (message.context) {
                var context = document.createElement('div');
                context.className = 'chat-order-context';
                var orderItems = message.context.items || [{
                    image_url: message.context.image_url,
                    product_name: message.context.product_name
                }];
                orderItems.forEach(function (item) {
                    if (!item.image_url) return;
                    var contextPreview = document.createElement('button');
                    contextPreview.type = 'button';
                    contextPreview.className = 'chat-image-preview';
                    contextPreview.dataset.previewUrl = item.image_url;
                    contextPreview.dataset.fileName = item.product_name || 'Order item';
                    contextPreview.setAttribute('aria-label', 'Open product image: ' + (item.product_name || 'Order item'));
                    var contextImage = document.createElement('img');
                    contextImage.src = item.image_url;
                    contextImage.alt = item.product_name || 'Order item';
                    contextPreview.appendChild(contextImage);
                    context.appendChild(contextPreview);
                });
                var contextDetails = document.createElement('div');
                var contextTitle = document.createElement(message.context.product_url ? 'a' : 'strong');
                contextTitle.textContent = (message.context.product_name || 'Order item') + (message.context.order_number ? ' · Order ' + message.context.order_number : '');
                if (message.context.product_url) {
                    contextTitle.href = message.context.product_url;
                    contextTitle.target = '_blank';
                    contextTitle.rel = 'noopener';
                }
                var contextQuantity = document.createElement('span');
                contextQuantity.textContent = 'Quantity: ' + message.context.quantity;
                contextDetails.append(contextTitle, contextQuantity);
                context.appendChild(contextDetails);
                bubble.appendChild(context);
            }
            if (message.attachment) {
                if (message.attachment.is_image && message.attachment.preview_url) {
                    var preview = document.createElement('button');
                    preview.type = 'button';
                    preview.className = 'chat-image-preview';
                    preview.setAttribute('aria-label', 'Open image: ' + (message.attachment.name || 'Attached image'));
                    preview.dataset.previewUrl = message.attachment.preview_url;
                    preview.dataset.downloadUrl = message.attachment.url;
                    preview.dataset.fileName = message.attachment.name || 'support-image';
                    var image = document.createElement('img');
                    image.src = message.attachment.preview_url;
                    image.alt = message.attachment.name || 'Image attached to support message';
                    image.loading = 'lazy';
                    preview.appendChild(image);
                    bubble.appendChild(preview);
                    var download = document.createElement('a');
                    download.className = 'chat-image-download';
                    download.href = message.attachment.url;
                    download.setAttribute('download', '');
                    var downloadIcon = document.createElement('i');
                    downloadIcon.className = 'ri-download-2-line';
                    var downloadName = document.createElement('span');
                    downloadName.textContent = message.attachment.name || 'Download image';
                    download.append(downloadIcon, downloadName);
                    bubble.appendChild(download);
                } else {
                    var link = document.createElement('a');
                    link.className = 'chat-attachment';
                    link.href = message.attachment.url;
                    link.setAttribute('download', '');
                    var icon = document.createElement('i');
                    icon.className = 'ri-file-text-line';
                    var name = document.createElement('span');
                    name.textContent = message.attachment.name || 'Download attachment';
                    link.append(icon, name);
                    bubble.appendChild(link);
                }
            }
            if (message.edited_at || message.is_pinned || message.is_starred || message.report_count) {
                var flags = document.createElement('div');
                flags.className = 'support-message-flags';
                if (message.edited_at) flags.appendChild(document.createTextNode('Edited'));
                if (message.is_pinned) flags.insertAdjacentHTML('beforeend', '<span class="support-message-flag"><i class="ri-pushpin-fill"></i>Pinned</span>');
                if (message.is_starred) flags.insertAdjacentHTML('beforeend', '<span class="support-message-flag"><i class="ri-star-fill"></i>Starred</span>');
                if (message.report_count) flags.insertAdjacentHTML('beforeend', '<span class="support-message-flag"><i class="ri-flag-fill"></i>Reported</span>');
                bubble.appendChild(flags);
            }
            var time = document.createElement('div');
            time.className = 'chat-time';
            time.textContent = message.created_at ? new Date(message.created_at).toLocaleTimeString([], {hour:'numeric',minute:'2-digit'}) : '';
            if (mine) {
                var check = document.createElement('i');
                check.className = message.read_at ? 'ri-check-double-line' : 'ri-check-line';
                time.appendChild(check);
            }
            bubble.appendChild(time);
            row.appendChild(bubble);
            var menuButton = document.createElement('button');
            menuButton.type = 'button';
            menuButton.className = 'support-message-menu-trigger';
            menuButton.setAttribute('aria-label', 'Message actions');
            menuButton.title = 'Message actions';
            menuButton.innerHTML = '<i class="ri-more-2-fill"></i>';
            row.appendChild(menuButton);
            if (existing) existing.replaceWith(row);
            else box.appendChild(row);
            lastId = Math.max(lastId, Number(message.id));
        }

        function setReplyTo(message, forward) {
            replyToMessageId = message.id;
            var preview = document.getElementById('chatReplyPreview');
            preview.replaceChildren(window.SupportMessageActions.replyPreview(message));
            preview.hidden = false;
            preview.dataset.messageId = message.id;
            var input = document.getElementById('chatInput');
            if (forward) {
                var prefix = input.value.trim();
                var forwardedText = 'Forwarded: ' + (message.body || message.attachment?.name || 'Attachment');
                input.value = prefix ? prefix + '\n' + forwardedText : forwardedText;
                input.dispatchEvent(new Event('input', { bubbles: true }));
            }
            input.focus();
        }

        function resetReply() {
            replyToMessageId = null;
            var preview = document.getElementById('chatReplyPreview');
            if (!preview) return;
            preview.hidden = true;
            preview.dataset.messageId = '';
            preview.replaceChildren();
        }

        chat.addEventListener('click', function (event) {
            var trigger = event.target.closest('.chat-image-preview');
            if (!trigger || !app.isConnected) return;
            lightboxImage.src = trigger.dataset.previewUrl;
            lightboxImage.alt = trigger.dataset.fileName || 'Attached image';
            document.getElementById('supportLightboxName').textContent = trigger.dataset.fileName || 'Attached image';
            var download = document.getElementById('supportLightboxDownload');
            download.href = trigger.dataset.downloadUrl;
            download.setAttribute('download', trigger.dataset.fileName || 'support-image');
            lightbox.showModal();
        });
        document.getElementById('supportLightboxClose').addEventListener('click', function () { lightbox.close(); });
        lightbox.addEventListener('click', function (event) {
            if (event.target === lightbox) lightbox.close();
        });

        function updateMessages() {
            if (stopped || !selectedId) return;
            var params = new URLSearchParams({ after: String(lastId) });
            if (changeCursor) params.set('changed_after', changeCursor);
            api(messagesUrlTemplate.replace('__ID__', selectedId) + '?' + params.toString()).then(function (data) {
                if (stopped || !app.isConnected || !selectedId) return;
                var box = document.getElementById('chatMessages');
                var stickToBottom = box && (!initialMessagesLoaded || box.scrollHeight - box.scrollTop - box.clientHeight < 80);
                (data.messages || []).forEach(appendMessage);
                if (data.change_cursor) changeCursor = data.change_cursor;
                if ((data.messages || []).length && stickToBottom) {
                    if (box) box.scrollTop = box.scrollHeight;
                    updateInbox();
                }
                initialMessagesLoaded = true;
                if (data.presence) {
                    setStatus(data.presence.user_online ? 'Online' : 'Offline', data.presence.user_online);
                    var typingLabel = document.getElementById('chatTyping');
                    if (typingLabel) typingLabel.textContent = data.presence.user_typing ? (selectedUser.name + ' is typing…') : '';
                }
            }).catch(function (error) {
                if (stopped || !app.isConnected || error.name === 'AbortError') return;
                if (error.status === 404) {
                    closeConversation();
                    updateInbox();
                    return;
                }
                var typingLabel = document.getElementById('chatTyping');
                if (typingLabel) typingLabel.textContent = error.message;
            });
        }

        function heartbeat(isTyping) {
            if (!selectedId) return Promise.resolve();
            return api(heartbeatUrlTemplate.replace('__ID__', selectedId), {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({typing:!!isTyping})
            });
        }

        function wireComposer() {
            var form = document.getElementById('chatForm');
            var input = document.getElementById('chatInput');
            var file = document.getElementById('chatFile');
            var chip = document.getElementById('chatFileChip');
            document.getElementById('chatAttach').addEventListener('click', function () { file.click(); });
            document.getElementById('chatFileClear').addEventListener('click', function () {
                file.value = '';
                document.getElementById('chatFileName').textContent = '';
                chip.classList.remove('show');
            });
            file.addEventListener('change', function () {
                var selectedFile = file.files[0];
                if (selectedFile && selectedFile.size > 10 * 1024 * 1024) {
                    file.value = '';
                    document.getElementById('chatTyping').textContent = 'That file is too large. Please choose a file under 10 MB.';
                    return;
                }
                if (selectedFile) {
                    document.getElementById('chatFileName').textContent = selectedFile.name;
                    chip.classList.add('show');
                }
            });
            input.addEventListener('input', function () {
                input.style.height = 'auto';
                input.style.height = Math.min(input.scrollHeight, 115) + 'px';
                if (!typing && input.value.trim()) {
                    typing = true;
                    heartbeat(true).catch(reportHeartbeatError);
                }
                clearTimeout(typingTimer);
                typingTimer = setTimeout(function () {
                    typing = false;
                    heartbeat(false).catch(reportHeartbeatError);
                }, 1800);
            });
            input.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    form.requestSubmit();
                }
            });
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                var body = input.value.trim();
                var upload = file.files[0];
                if (!body && !upload) return;
                var payload = new FormData();
                if (body) payload.append('body', body);
                if (upload) payload.append('attachment', upload);
                if (replyToMessageId) payload.append('reply_to_message_id', String(replyToMessageId));
                var sendButton = document.getElementById('chatSend');
                sendButton.disabled = true;
                api(sendUrlTemplate.replace('__ID__', selectedId), {method:'POST',body:payload}).then(function (data) {
                    if (stopped || !app.isConnected) return;
                    appendMessage(data.message);
                    var box = document.getElementById('chatMessages');
                    if (box) box.scrollTop = box.scrollHeight;
                    input.value = '';
                    input.style.height = 'auto';
                    resetReply();
                    file.value = '';
                    document.getElementById('chatFileName').textContent = '';
                    chip.classList.remove('show');
                    typing = false;
                    return heartbeat(false);
                }).then(updateInbox).catch(function (error) {
                    if (stopped || !app.isConnected || error.name === 'AbortError') return;
                    var typingLabel = document.getElementById('chatTyping');
                    if (typingLabel) typingLabel.textContent = error.message;
                }).finally(function () {
                    if (stopped || !app.isConnected) return;
                    var currentSend = document.getElementById('chatSend');
                    if (currentSend) currentSend.disabled = false;
                    var currentInput = document.getElementById('chatInput');
                    if (currentInput) currentInput.focus();
                });
            });
        }

        search.addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                inboxPage = 1;
                updateInbox();
            }, 250);
        });
        document.getElementById('inboxPrevious').addEventListener('click', function () {
            if (inboxPages.current_page <= 1) return;
            inboxPage = inboxPages.current_page - 1;
            updateInbox();
        });
        document.getElementById('inboxNext').addEventListener('click', function () {
            if (inboxPages.current_page >= inboxPages.last_page) return;
            inboxPage = inboxPages.current_page + 1;
            updateInbox();
        });
        updateInbox();
        var inboxTimer = setInterval(function () {
            if (document.documentElement.getAttribute('aria-busy') !== 'true') updateInbox();
        }, 5000);
        var messageTimer = null;
        var heartbeatTimer = setInterval(function () {
            if (document.documentElement.getAttribute('aria-busy') !== 'true') heartbeat(typing).catch(reportHeartbeatError);
        }, 12000);
        function stopSupportPolling() {
            clearInterval(inboxTimer);
            clearInterval(messageTimer);
            clearInterval(heartbeatTimer);
            clearTimeout(searchTimer);
        }
        window.addEventListener('pagehide', stopSupportPolling, { once: true });
        window.addEventListener('ajax:navigating', stopSupportPolling, { once: true });
    })();
    </script>
</body>
</html>
