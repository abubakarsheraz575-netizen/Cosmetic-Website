<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Support — Cosmetics</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}?v=3">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    @include('user.include.base')
    <style data-page>
        .support-content{max-width:none;min-width:0}
        .support-heading{display:flex;align-items:center;gap:14px;margin-bottom:22px}
        .support-heading .support-heading-icon{width:50px;height:50px;display:grid;place-items:center;border-radius:16px;color:#fff;background:linear-gradient(135deg,#38aab4,#2b7fb8);font-size:23px;box-shadow:0 10px 24px rgba(56,170,180,.25)}
        .support-heading h1{font-size:22px;color:var(--ink);font-weight:700}
        .support-heading p{font-size:12.5px;color:var(--txt);margin-top:3px}
        .support-chat{height:min(710px,calc(100vh - 180px));min-height:460px;overflow:hidden;display:flex;flex-direction:column;border-radius:22px;background:rgba(255,255,255,.9);border:1px solid rgba(22,35,60,.09);box-shadow:0 20px 55px rgba(31,51,89,.12)}
        .support-chat-head{min-height:78px;display:flex;align-items:center;gap:13px;padding:15px 22px;background:#fff;border-bottom:1px solid rgba(22,35,60,.08)}
        .support-chat-delete{width:36px;height:36px;flex:none;display:grid;place-items:center;border:1px solid #e6ecee;border-radius:11px;background:#fff;color:#8b5960;font-size:17px;cursor:pointer}
        .support-chat-delete:hover{border-color:#f2c3c3;background:#fff3f3;color:#b42318}
        body.dark .support-chat-delete{border-color:#26364c;background:#111c2e;color:#e99797}
        .support-avatar{width:46px;height:46px;flex:none;display:grid;place-items:center;border-radius:50%;color:#fff;font-weight:700;background:linear-gradient(135deg,#38aab4,#7567ca);position:relative}
        .support-presence{position:absolute;width:12px;height:12px;right:0;bottom:1px;border:2px solid #fff;border-radius:50%;background:#aeb9c6}
        .support-presence.online{background:#22c385}
        .support-person{min-width:0;flex:1}
        .support-person strong{display:block;color:var(--ink);font-size:14px}
        .support-person span{display:block;margin-top:3px;color:#778394;font-size:11.5px}
        .support-person .online-label{color:#159a69}
        .support-secure{display:flex;align-items:center;gap:7px;padding:8px 11px;border-radius:11px;background:#f1faf8;color:#438b7c;font-size:11px;font-weight:600}
        .support-secure i{font-size:15px}
        .support-order-context{position:relative;z-index:1;flex:0 0 auto;display:flex;align-items:center;gap:14px;margin:12px clamp(16px,5vw,54px) 0;padding:13px 15px;border:1px solid rgba(56,170,180,.24);border-radius:16px;background:linear-gradient(110deg,#effaf7,#f6fbff);color:#31475a;box-shadow:0 7px 20px rgba(31,91,101,.06)}
        .support-order-context img{width:64px;height:64px;flex:0 0 64px;object-fit:cover;border-radius:11px;background:#fff}
        .support-order-context-items{display:flex;flex:0 0 auto;gap:8px;max-width:min(52vw,360px);overflow-x:auto;overscroll-behavior:contain}
        .support-order-item{display:grid;justify-items:center;gap:4px;min-width:68px;color:#526778;font-size:9px;text-align:center}
        .support-order-item img,.support-order-item-placeholder{width:52px;height:52px;display:grid;place-items:center;object-fit:cover;border-radius:9px;background:#fff}
        .support-order-item span{max-width:70px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .support-order-item.is-selected{padding:4px;border:1px solid rgba(56,170,180,.48);border-radius:12px;background:#fff;box-shadow:0 3px 10px rgba(56,170,180,.1)}
        .support-order-item .support-image-preview{width:52px;height:52px;flex:0 0 52px}
        .support-order-item.is-selected .support-image-preview{border:2px solid #38aab4}
        .support-order-item.is-selected span{color:#1d7180;font-weight:700}
        .support-context-no-image{width:64px;height:64px;flex:0 0 64px;display:grid;place-items:center;border-radius:11px;background:#e3f1ec;color:#438b7c;font-size:23px}
        .support-order-context-copy{min-width:0}
        .support-order-context .support-image-preview{width:64px;height:64px;max-height:64px;flex:0 0 64px}
        .support-order-context .support-image-preview img{width:100%;height:100%;max-height:none}
        .support-order-context strong{display:block;color:#205e69;font-size:12px}
        .support-order-context span{display:block;margin-top:4px;color:#526778;font-size:11px;line-height:1.55}
        .support-bubble .support-order-context{margin:8px 0;padding:8px;gap:10px;font-size:10px}
        .support-bubble .support-order-context img{width:44px;height:44px}
        .support-bubble .support-order-context .support-image-preview{width:44px;height:44px;max-height:44px;flex-basis:44px}
        .support-bubble .support-order-context a{display:block;color:#205e69;font-weight:700}
        .support-messages{position:relative;flex:1;min-height:0;overflow:auto;padding:24px clamp(16px,5vw,54px);background-color:#f4f6f5;background-image:radial-gradient(rgba(56,170,180,.065) .8px,transparent .8px);background-size:17px 17px;scroll-behavior:smooth}
        .support-welcome{max-width:410px;margin:24px auto;padding:18px 20px;border:1px solid rgba(56,170,180,.15);border-radius:16px;background:rgba(255,255,255,.9);text-align:center;color:#6e7a87;font-size:12px;line-height:1.65;box-shadow:0 8px 24px rgba(31,51,89,.06)}
        .support-welcome i{display:block;margin:0 auto 7px;color:#38aab4;font-size:24px}
        .support-message{display:flex;margin:13px 0;animation:supportPop .2s ease both}
        .support-message.mine{justify-content:flex-end}
        .support-bubble{max-width:min(76%,540px);padding:10px 13px 7px;border:1px solid rgba(22,35,60,.06);border-radius:15px 15px 15px 5px;background:#fff;color:#273343;box-shadow:0 3px 12px rgba(31,51,89,.07);overflow-wrap:anywhere}
        .support-message.mine .support-bubble{border-color:rgba(56,170,180,.14);border-radius:15px 15px 5px 15px;background:linear-gradient(135deg,#e2f7f0,#d7f0ed)}
        .support-body{font-size:13px;line-height:1.55;white-space:pre-wrap}
        .support-time{display:flex;justify-content:flex-end;align-items:center;gap:4px;margin-top:4px;color:#89939e;font-size:9.5px}
        .support-time i{font-size:13px;color:#36aeb1}
        .support-attachment{display:flex;align-items:center;gap:10px;margin:2px 0 6px;padding:9px 11px;border:1px solid rgba(22,35,60,.08);border-radius:11px;background:rgba(255,255,255,.7);color:#286e83;text-decoration:none}
        .support-attachment>i{font-size:21px}
        .support-attachment span{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:11.5px;font-weight:600}
        .support-image-preview{display:block;width:min(100%,300px);padding:0;overflow:hidden;border:1px solid rgba(22,35,60,.1);border-radius:12px;background:#fff;cursor:zoom-in}
        .support-image-preview img{display:block;width:100%;max-height:230px;object-fit:cover}
        .support-image-download{display:flex;align-items:center;gap:7px;margin-top:7px;color:#286e83;font-size:11px;font-weight:600;text-decoration:none}
        .support-lightbox{position:fixed;inset:50% auto auto 50%;transform:translate(-50%,-50%);width:min(92vw,1100px);max-width:none;max-height:90dvh;margin:0;overflow:auto;padding:12px;border:1px solid rgba(255,255,255,.2);border-radius:18px;background:#111c2e;color:#fff;box-shadow:0 24px 80px rgba(0,0,0,.45)}
        .support-lightbox::backdrop{background:rgba(9,16,28,.82);backdrop-filter:blur(5px)}
        .support-lightbox-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:2px 2px 10px}
        .support-lightbox-name{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12px}
        .support-lightbox-actions{display:flex;align-items:center;gap:8px}
        .support-lightbox-actions a,.support-lightbox-close{width:34px;height:34px;display:grid;place-items:center;border:1px solid rgba(255,255,255,.16);border-radius:10px;background:rgba(255,255,255,.08);color:#fff;text-decoration:none;font-size:17px;cursor:pointer}
        .support-lightbox img{display:block;max-width:100%;max-height:calc(90dvh - 74px);margin:auto;border-radius:10px;object-fit:contain}
        .support-typing{min-height:23px;padding:0 22px;color:#82909c;font-size:11px}
        .support-compose{padding:12px 16px 15px;border-top:1px solid rgba(22,35,60,.08);background:#fff}
        .support-file-chip{display:none;align-items:center;gap:7px;margin:0 2px 9px;padding:6px 10px;border-radius:9px;background:#eef8f7;color:#367f82;font-size:11px}
        .support-file-chip.show{display:inline-flex}
        .support-file-chip span{max-width:230px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .support-file-clear{border:0;background:none;color:#667783;font-size:16px;cursor:pointer}
        .support-request-context{display:flex;align-items:center;gap:11px;margin:0 2px 10px;padding:9px 11px;border:1px solid rgba(56,170,180,.2);border-radius:14px;background:linear-gradient(100deg,#f1faf8,#f8fbff)}
        .support-request-photo{width:58px!important;height:58px!important;max-height:58px!important;flex:0 0 58px;padding:0!important;border-radius:11px!important;cursor:zoom-in}
        .support-request-photo img{width:100%;height:100%;max-height:none;object-fit:cover}
        .support-request-photo-fallback{display:grid;place-items:center;width:58px;height:58px;flex:0 0 58px;border-radius:11px;background:#e5f2ef;color:#438b7c;font-size:22px}
        .support-request-copy{min-width:0;flex:1}.support-request-copy small{display:block;color:#718091;font-size:9px;font-weight:700;letter-spacing:.8px;text-transform:uppercase}.support-request-copy strong{display:block;margin-top:2px;overflow:hidden;color:#285c67;font-size:11px;text-overflow:ellipsis;white-space:nowrap}.support-request-copy span{display:block;margin-top:3px;color:#7c8996;font-size:9px;line-height:1.4}
        .support-request-linked{display:inline-flex;align-items:center;gap:4px;flex:none;padding:6px 8px;border:1px solid rgba(32,139,105,.14);border-radius:999px;background:#eff9f4;color:#318563;font-size:9px;font-weight:700;white-space:nowrap}.support-request-remove{width:27px;height:27px;display:grid;place-items:center;flex:none;margin-left:2px;border:1px solid #dce8e6;border-radius:9px;background:#fff;color:#71818b;font-size:15px;cursor:pointer;transition:.18s ease}.support-request-remove:hover,.support-request-remove:focus-visible{border-color:#efb6b1;background:#fff5f4;color:#b83d35;outline:0;transform:rotate(4deg)}
        .support-compose-row{display:flex;align-items:flex-end;gap:9px;padding:7px 8px 7px 12px;border:1px solid rgba(22,35,60,.11);border-radius:16px;background:#fbfcfc;transition:border-color .2s,box-shadow .2s}
        .support-compose-row:focus-within{border-color:#52b9bd;box-shadow:0 0 0 3px rgba(56,170,180,.1)}
        .support-attach-btn,.support-send-btn{width:38px;height:38px;flex:none;display:grid;place-items:center;border:0;border-radius:12px;cursor:pointer}
        .support-attach-btn{background:transparent;color:#637180;font-size:21px}
        .support-attach-btn:hover{background:#eff7f7;color:#277f87}
        .support-compose textarea{flex:1;min-width:0;max-height:120px;min-height:38px;resize:none;border:0;outline:0;padding:9px 3px;background:transparent;color:#263444;font:400 13px 'Poppins',sans-serif}
        .support-compose textarea::placeholder{color:#9aa5ae}
        .support-send-btn{background:linear-gradient(135deg,#38aab4,#2b7fb8);color:#fff;font-size:18px;box-shadow:0 4px 11px rgba(56,170,180,.23);transition:transform .2s,opacity .2s}
        .support-send-btn:hover{transform:translateY(-1px)}
        .support-send-btn:disabled{opacity:.5;cursor:wait;transform:none}
        .support-compose-note{margin:8px 3px 0;color:#99a3ac;font-size:10px}
        @keyframes supportPop{from{opacity:0;transform:translateY(5px)}to{opacity:1;transform:none}}
        @media(max-width:992px){.support-chat{height:calc(100dvh - 205px);min-height:430px}.support-secure{font-size:0;padding:9px}.support-secure i{font-size:16px}}
        @media(max-width:600px){.main{padding:16px 12px 30px}.support-heading{margin-bottom:15px}.support-heading h1{font-size:19px}.support-heading .support-heading-icon{width:44px;height:44px}.support-chat{height:calc(100dvh - 158px);min-height:390px;border-radius:17px}.support-chat-head{min-height:68px;padding:11px 14px}.support-order-context{gap:11px;margin:9px 10px 0;padding:10px}.support-order-context img,.support-context-no-image{width:54px;height:54px;flex-basis:54px}.support-order-context span{font-size:10px}.support-messages{padding:16px 12px}.support-bubble{max-width:88%}.support-compose{padding:9px 9px 12px}.support-request-context{gap:8px;padding:7px}.support-request-photo,.support-request-photo-fallback{width:46px!important;height:46px!important;max-height:46px!important;flex-basis:46px}.support-request-copy span{font-size:8px}.support-request-linked{padding:5px 6px;font-size:8px}.support-compose-note{font-size:9px}}
        @media(prefers-reduced-motion:reduce){.support-message{animation:none}}
    </style>
</head>
<body>
    <div class="scene" aria-hidden="true"><div class="orb orb-1"></div><div class="orb orb-2"></div><div class="orb orb-3"></div></div>
    <div class="layout">
        @include('user.include.sidebar')
        <main class="main support-content">
            <div class="support-heading">
                <span class="support-heading-icon"><i class="ri-customer-service-2-line"></i></span>
                <div><h1>Customer Support</h1><p>Send us a message and our team will be happy to help.</p></div>
            </div>
            <section class="support-chat" id="supportChatRoot" data-conversation="{{ $conversation->id }}" @if($orderContext) data-order-item="{{ $orderContext['item_id'] }}" @endif>
                <header class="support-chat-head">
                    <div class="support-avatar">C<span class="support-presence" id="supportPresence"></span></div>
                    <div class="support-person"><strong>Cosmetics Support</strong><span id="supportStatus">Connecting…</span></div>
                    <div class="support-secure"><i class="ri-lock-2-line"></i>Private conversation</div>
                    <button type="button" class="support-chat-delete" data-support-delete-conversation aria-label="Delete conversation" title="Delete entire conversation"><i class="ri-delete-bin-line"></i></button>
                </header>
                <div class="support-messages" id="supportMessages" aria-live="polite">
                    @unless ($orderContext)
                    <div class="support-welcome"><i class="ri-chat-smile-3-line"></i>Welcome to Cosmetics Support. Ask us about your order, products, or anything else we can help with.</div>
                    @endunless
                </div>
                <div class="support-typing" id="supportTyping" aria-live="polite"></div>
                <form class="support-compose" id="supportForm">
                    <div class="support-reply-preview" id="supportReplyPreview" hidden></div>
                    @if ($orderContext)
                    <div class="support-request-context" id="supportRequestContext" aria-label="Selected product linked to this support request">
                        @if ($orderContext['image_url'])
                        <button type="button" class="support-image-preview support-request-photo" data-preview-url="{{ $orderContext['image_url'] }}" data-file-name="{{ $orderContext['product_name'] }}" aria-label="View product photo for {{ $orderContext['product_name'] }}"><img src="{{ $orderContext['image_url'] }}" alt="Product photo: {{ $orderContext['product_name'] }}"></button>
                        @else
                        <span class="support-request-photo-fallback" aria-hidden="true"><i class="ri-image-line"></i></span>
                        @endif
                        <div class="support-request-copy"><small>Selected product</small><strong>{{ $orderContext['product_name'] }}</strong><span>Product photo and order details will accompany your message.</span></div>
                        <span class="support-request-linked"><i class="ri-check-line" aria-hidden="true"></i> Linked</span>
                        <button type="button" class="support-request-remove" id="supportRequestRemove" aria-label="Remove selected product from this message" title="Remove selected product"><i class="ri-close-line" aria-hidden="true"></i></button>
                    </div>
                    @endif
                    <div class="support-file-chip" id="supportFileChip"><i class="ri-attachment-2"></i><span id="supportFileName"></span><button type="button" class="support-file-clear" id="supportFileClear" aria-label="Remove attachment"><i class="ri-close-line"></i></button></div>
                    <div class="support-compose-row">
                        <button type="button" class="support-attach-btn" id="supportAttach" aria-label="Attach a file" title="Attach a file"><i class="ri-attachment-2"></i></button>
                        <input type="file" id="supportFile" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.txt,.doc,.docx,.xls,.xlsx" hidden>
                        <textarea id="supportInput" rows="1" maxlength="5000" placeholder="Type a message…" aria-label="Type a support message">@if($orderContext)I need help with this product.@endif</textarea>
                        <button type="submit" class="support-send-btn" id="supportSend" aria-label="Send message"><i class="ri-send-plane-2-fill"></i></button>
                    </div>
                    <p class="support-compose-note">Press Enter to send · Attach an image or document (max 10 MB)</p>
                </form>
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
        if (window.__supportChatCleanup) window.__supportChatCleanup();
        var root = document.getElementById('supportChatRoot');
        if (!root) return;
        var messages = document.getElementById('supportMessages');
        var form = document.getElementById('supportForm');
        var input = document.getElementById('supportInput');
        var fileInput = document.getElementById('supportFile');
        var fileChip = document.getElementById('supportFileChip');
        var sendButton = document.getElementById('supportSend');
        var lightbox = document.getElementById('supportLightbox');
        var lightboxImage = document.getElementById('supportLightboxImage');
        var token = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
        var lastId = 0, changeCursor = null, replyToMessageId = null, initialMessagesLoaded = false, stopped = false, typingTimer = null, typing = false;
        var requests = new AbortController();
        var messageUrl = @json(route('user.support.messages'));
        var sendUrl = @json(route('user.support.send'));
        var heartbeatUrl = @json(route('user.support.heartbeat'));
        var actionUrls = {
            edit: @json(route('user.support.messages.edit', '__MESSAGE__')),
            delete: @json(route('user.support.messages.delete', '__MESSAGE__')),
            pin: @json(route('user.support.messages.pin', '__MESSAGE__')),
            star: @json(route('user.support.messages.star', '__MESSAGE__')),
            report: @json(route('user.support.messages.report', '__MESSAGE__'))
        };
        var deleteConversationUrl = @json(route('user.support.conversation.delete'));

        function cleanup() {
            if (stopped) return;
            stopped = true;
            requests.abort();
            clearInterval(pollTimer);
            clearInterval(heartbeatTimer);
            clearTimeout(typingTimer);
            observer.disconnect();
            if (lightbox.open) lightbox.close();
            if (window.__supportChatCleanup === cleanup) window.__supportChatCleanup = null;
        }
        var observer = new MutationObserver(function () {
            if (!root.isConnected) cleanup();
        });
        observer.observe(document.body, { childList: true, subtree: true });
        window.__supportChatCleanup = cleanup;

        function request(url, options) {
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
                        var validation = data.errors && Object.keys(data.errors).length ? data.errors[Object.keys(data.errors)[0]][0] : null;
                        var error = new Error(validation || data.message || 'Could not connect to support.');
                        error.status = response.status;
                        throw error;
                    }
                    return data;
                }, function () {
                    throw new Error('Support returned an invalid response. Please retry.');
                });
            });
        }

        function reportHeartbeatError(error) {
            if (stopped || !root.isConnected || error.name === 'AbortError') return;
            var status = document.getElementById('supportStatus');
            status.textContent = 'Connection issue: ' + error.message;
            status.classList.remove('online-label');
        }

        function updatePresence(presence) {
            if (stopped || !root.isConnected) return;
            var indicator = document.getElementById('supportPresence');
            var online = !!presence.admin_online;
            if (indicator) indicator.classList.toggle('online', online);
            var status = document.getElementById('supportStatus');
            if (!status) return;
            status.textContent = online ? 'Online' : 'Usually replies as soon as possible';
            status.classList.toggle('online-label', online);
            var typingStatus = document.getElementById('supportTyping');
            if (typingStatus) typingStatus.textContent = presence.admin_typing ? 'Support is typing…' : '';
        }

        function appendMessage(message) {
            if (stopped || !root.isConnected || !message || !message.id) return;
            var existing = messages.querySelector('[data-message-id="' + message.id + '"]');
            if (message.deleted) {
                if (existing) existing.remove();
                lastId = Math.max(lastId, Number(message.id));
                return;
            }
            var mine = String(message.sender_id) === @json((string) auth()->id());
            var row = document.createElement('article');
            row.className = 'support-message' + (mine ? ' mine' : '');
            row.dataset.messageId = message.id;
            row.dataset.mine = mine ? '1' : '0';
            row.__supportMessage = message;
            var bubble = document.createElement('div');
            bubble.className = 'support-bubble';
            var quote = window.SupportMessageActions.quoteElement(message);
            if (quote) bubble.appendChild(quote);
            if (message.body) {
                var body = document.createElement('div');
                body.className = 'support-body';
                body.textContent = message.body;
                bubble.appendChild(body);
            }
            if (message.context) {
                var context = document.createElement('div');
                context.className = 'support-order-context';
                var orderItems = message.context.items || [{
                    image_url: message.context.image_url,
                    product_name: message.context.product_name
                }];
                orderItems.forEach(function (item) {
                    if (!item.image_url) return;
                    var contextPreview = document.createElement('button');
                    contextPreview.type = 'button';
                    contextPreview.className = 'support-image-preview';
                    contextPreview.dataset.previewUrl = item.image_url;
                    contextPreview.dataset.fileName = item.product_name || 'Order item';
                    contextPreview.setAttribute('aria-label', 'Open product image');
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
                var contextOrder = document.createElement('span');
                contextOrder.textContent = 'Order number: ' + (message.context.order_number || 'Not available');
                var contextQuantity = document.createElement('span');
                contextQuantity.textContent = 'Quantity: ' + message.context.quantity + (message.context.line_total ? ' · Line total: £' + Number(message.context.line_total).toFixed(2) : '');
                contextDetails.append(contextTitle, contextOrder, contextQuantity);
                context.appendChild(contextDetails);
                bubble.appendChild(context);
            }
            if (message.attachment) {
                if (message.attachment.is_image && message.attachment.preview_url) {
                    var preview = document.createElement('button');
                    preview.type = 'button';
                    preview.className = 'support-image-preview';
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
                    download.className = 'support-image-download';
                    download.href = message.attachment.url;
                    download.setAttribute('download', '');
                    var downloadIcon = document.createElement('i');
                    downloadIcon.className = 'ri-download-2-line';
                    var downloadName = document.createElement('span');
                    downloadName.textContent = message.attachment.name || 'Download image';
                    download.append(downloadIcon, downloadName);
                    bubble.appendChild(download);
                } else {
                    var attachment = document.createElement('a');
                    attachment.className = 'support-attachment';
                    attachment.href = message.attachment.url;
                    attachment.setAttribute('download', '');
                    var fileIcon = document.createElement('i');
                    fileIcon.className = 'ri-file-text-line';
                    var attachmentName = document.createElement('span');
                    attachmentName.textContent = message.attachment.name || 'Download attachment';
                    attachment.append(fileIcon, attachmentName);
                    bubble.appendChild(attachment);
                }
            }
            if (message.edited_at || message.is_pinned || message.is_starred || message.report_count) {
                var flags = document.createElement('div');
                flags.className = 'support-message-flags';
                if (message.edited_at) flags.appendChild(document.createTextNode('Edited'));
                if (message.is_pinned) flags.insertAdjacentHTML('beforeend', '<span class="support-message-flag"><i class="ri-pushpin-fill"></i>Pinned</span>');
                if (message.is_starred) flags.insertAdjacentHTML('beforeend', '<span class="support-message-flag"><i class="ri-star-fill"></i>Starred</span>');
                if (message.report_count && String(message.sender_role) === 'user') flags.insertAdjacentHTML('beforeend', '<span class="support-message-flag"><i class="ri-flag-fill"></i>Reported</span>');
                bubble.appendChild(flags);
            }
            var time = document.createElement('div');
            time.className = 'support-time';
            time.textContent = message.created_at ? new Date(message.created_at).toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' }) : '';
            if (mine) {
                var read = document.createElement('i');
                read.className = message.read_at ? 'ri-check-double-line' : 'ri-check-line';
                time.appendChild(read);
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
            else messages.appendChild(row);
            lastId = Math.max(lastId, Number(message.id));
        }

        function setReplyTo(message, forward) {
            replyToMessageId = message.id;
            var preview = document.getElementById('supportReplyPreview');
            preview.replaceChildren(window.SupportMessageActions.replyPreview(message));
            preview.hidden = false;
            preview.dataset.messageId = message.id;
            input.focus();
        }

        function resetReply() {
            replyToMessageId = null;
            var preview = document.getElementById('supportReplyPreview');
            preview.hidden = true;
            preview.dataset.messageId = '';
            preview.replaceChildren();
        }

        window.SupportMessageActions.attach(root, {
            messages: messages,
            isAdmin: false,
            request: request,
            urlFor: function (message, action) { return actionUrls[action].replace('__MESSAGE__', encodeURIComponent(message.id)); },
            deleteConversationUrl: deleteConversationUrl,
            onReply: setReplyTo,
            onUpdate: appendMessage,
            onDelete: function (id) {
                var row = messages.querySelector('[data-message-id="' + id + '"]');
                if (row) row.remove();
            },
            onConversationDelete: function () {
                lastId = 0;
                changeCursor = null;
                messages.replaceChildren();
                resetReply();
            }
        });
        document.getElementById('supportReplyPreview').addEventListener('support:reply-cancel', resetReply);

        root.addEventListener('click', function (event) {
            var trigger = event.target.closest('.support-image-preview');
            if (!trigger || !root.isConnected) return;
            lightboxImage.src = trigger.dataset.previewUrl;
            lightboxImage.alt = trigger.dataset.fileName || 'Attached image';
            document.getElementById('supportLightboxName').textContent = trigger.dataset.fileName || 'Attached image';
            var download = document.getElementById('supportLightboxDownload');
            download.href = trigger.dataset.downloadUrl || trigger.dataset.previewUrl;
            download.setAttribute('download', trigger.dataset.fileName || 'support-image');
            lightbox.showModal();
        });
        document.getElementById('supportLightboxClose').addEventListener('click', function () { lightbox.close(); });
        lightbox.addEventListener('click', function (event) {
            if (event.target === lightbox) lightbox.close();
        });

        function loadMessages() {
            if (stopped) return;
            var params = new URLSearchParams({ after: String(lastId) });
            if (changeCursor) params.set('changed_after', changeCursor);
            request(messageUrl + '?' + params.toString()).then(function (data) {
                if (stopped || !root.isConnected) return;
                var stickToBottom = !initialMessagesLoaded || messages.scrollHeight - messages.scrollTop - messages.clientHeight < 80;
                (data.messages || []).forEach(appendMessage);
                if (data.change_cursor) changeCursor = data.change_cursor;
                if (data.presence) updatePresence(data.presence);
                if ((data.messages || []).length && stickToBottom) messages.scrollTop = messages.scrollHeight;
                initialMessagesLoaded = true;
            }).catch(function (error) {
                if (stopped || error.name === 'AbortError') return;
                var status = document.getElementById('supportStatus');
                if (status) status.textContent = 'Reconnecting…';
            });
        }

        function heartbeat(isTyping) {
            return request(heartbeatUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ typing: !!isTyping })
            });
        }

        function resetFile() {
            fileInput.value = '';
            document.getElementById('supportFileName').textContent = '';
            fileChip.classList.remove('show');
        }

        document.getElementById('supportAttach').addEventListener('click', function () { fileInput.click(); });
        document.getElementById('supportFileClear').addEventListener('click', resetFile);
        var requestContext = document.getElementById('supportRequestContext');
        var requestRemove = document.getElementById('supportRequestRemove');
        if (requestRemove) requestRemove.addEventListener('click', function () {
            delete root.dataset.orderItem;
            if (requestContext) requestContext.remove();
            input.focus();
        });
        fileInput.addEventListener('change', function () {
            var file = fileInput.files[0];
            if (file && file.size > 10 * 1024 * 1024) {
                resetFile();
                document.getElementById('supportTyping').textContent = 'That file is too large. Please choose a file under 10 MB.';
                return;
            }
            if (file) {
                document.getElementById('supportFileName').textContent = file.name;
                fileChip.classList.add('show');
            }
        });
        input.addEventListener('input', function () {
            input.style.height = 'auto';
            input.style.height = Math.min(input.scrollHeight, 120) + 'px';
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
            var file = fileInput.files[0];
            if (!body && !file) return;
            var payload = new FormData();
            if (body) payload.append('body', body);
            if (file) payload.append('attachment', file);
            if (replyToMessageId) payload.append('reply_to_message_id', String(replyToMessageId));
            if (root.dataset.orderItem) payload.append('order_item_id', root.dataset.orderItem);
            sendButton.disabled = true;
            request(sendUrl, { method: 'POST', body: payload }).then(function (data) {
                if (stopped || !root.isConnected) return;
                appendMessage(data.message);
                messages.scrollTop = messages.scrollHeight;
                initialMessagesLoaded = true;
                input.value = '';
                input.style.height = 'auto';
                resetFile();
                resetReply();
                if (root.dataset.orderItem) {
                    delete root.dataset.orderItem;
                    var contextBanner = document.getElementById('supportOrderContext');
                    if (contextBanner) contextBanner.remove();
                }
                typing = false;
                return heartbeat(false);
            }).catch(function (error) {
                if (stopped || error.name === 'AbortError') return;
                var typingStatus = document.getElementById('supportTyping');
                if (typingStatus) typingStatus.textContent = error.message;
            }).finally(function () {
                if (stopped || !root.isConnected) return;
                sendButton.disabled = false;
                input.focus();
            });
        });

        heartbeat(false).catch(reportHeartbeatError);
        loadMessages();
        var pollTimer = setInterval(function () {
            if (document.documentElement.getAttribute('aria-busy') !== 'true') loadMessages();
        }, 2500);
        var heartbeatTimer = setInterval(function () {
            if (document.documentElement.getAttribute('aria-busy') !== 'true') heartbeat(typing && !!input.value.trim()).catch(reportHeartbeatError);
        }, 12000);
        function stopSupportPolling() {
            stopped = true;
            clearInterval(pollTimer);
            clearInterval(heartbeatTimer);
        }
        window.addEventListener('pagehide', stopSupportPolling, { once: true });
        window.addEventListener('ajax:navigating', stopSupportPolling, { once: true });
    })();
    </script>
</body>
</html>
