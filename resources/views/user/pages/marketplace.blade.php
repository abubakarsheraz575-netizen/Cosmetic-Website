<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vendor Marketplace — Cosmetics Store</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    @include('user.include.base')
    <style>
        .vendor-main{padding-top:30px!important}
        .vendor-topbar{display:flex;align-items:center;gap:14px;margin-bottom:18px}
        .vendor-topbar-title{min-width:0;flex:1}
        .vendor-topbar-title h1{color:var(--ink);font-size:21px;font-weight:600}
        .vendor-topbar-title p{margin-top:4px;color:var(--txt);font-size:12px}
        .vendor-head{position:relative;display:flex;align-items:center;gap:16px;margin:0 0 20px;padding:22px 24px;border:1px solid rgba(56,170,180,.17);border-radius:20px;background:linear-gradient(120deg,rgba(255,255,255,.94),rgba(230,247,246,.83) 60%,rgba(239,235,255,.7));box-shadow:0 14px 36px rgba(31,51,89,.08);overflow:hidden}
        .vendor-head:after{content:"";position:absolute;width:190px;height:190px;right:-45px;top:-105px;border-radius:50%;background:rgba(56,170,180,.12);pointer-events:none}
        .vendor-head-icon{display:grid;place-items:center;flex:none;width:52px;height:52px;border:1px solid rgba(56,170,180,.22);border-radius:16px;background:linear-gradient(145deg,#e5f8f5,#e8f1ff);color:var(--teal-2);font-size:24px}
        .vendor-head-copy{position:relative;z-index:1;min-width:0;flex:1}
        .vendor-eyebrow{display:inline-flex;align-items:center;gap:6px;margin-bottom:5px;color:var(--teal-2);font-size:9px;font-weight:800;letter-spacing:1.5px;text-transform:uppercase}
        .vendor-head h1{color:var(--ink);font-size:23px;font-weight:700;letter-spacing:-.4px}.vendor-head p{margin-top:5px;color:var(--txt);font-size:12px;line-height:1.6}
        .vendor-head-actions{position:relative;z-index:1;display:flex;align-items:center;gap:9px;flex:none}
        .vendor-private{display:inline-flex;align-items:center;gap:7px;padding:8px 11px;border:1px solid rgba(56,170,180,.2);border-radius:999px;background:rgba(255,255,255,.7);color:#19725f;font-size:10px;font-weight:700}
        .vendor-withdrawals-link{display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:10px 12px;border:0;border-radius:11px;background:linear-gradient(135deg,var(--teal),var(--teal-2));color:#fff;font-size:10px;font-weight:700;box-shadow:0 7px 16px rgba(43,127,184,.16)}
        .vendor-withdrawals-link:hover{transform:translateY(-1px);box-shadow:0 10px 20px rgba(43,127,184,.23)}
        .vendor-grid{display:grid;grid-template-columns:minmax(0,1fr);gap:18px;align-items:start;max-width:1080px}
        .vendor-panel{min-width:0;padding:21px;border:1px solid rgba(22,35,60,.08);border-radius:19px;background:rgba(255,255,255,.85);backdrop-filter:blur(14px);box-shadow:0 12px 32px rgba(31,51,89,.07)}
        .vendor-panel-heading{display:flex;align-items:center;gap:11px;margin-bottom:5px}
        .vendor-panel-icon{display:grid;place-items:center;flex:none;width:37px;height:37px;border-radius:12px;background:rgba(56,170,180,.11);color:var(--teal-2);font-size:18px}
        .vendor-panel h2{margin:0;color:var(--ink);font-size:15px;letter-spacing:-.15px}.vendor-panel>p{margin:0 0 16px;color:var(--txt);font-size:11px;line-height:1.55}
        .vendor-chat{display:flex;flex-direction:column;gap:11px;max-height:470px;min-height:240px;overflow:auto;padding:15px;border:1px solid rgba(22,35,60,.07);border-radius:15px;background:linear-gradient(145deg,#f7fbfc,#f3f7f7);overscroll-behavior:contain}
        .vendor-chat-empty{display:grid;place-items:center;align-content:center;gap:8px;max-width:330px;margin:auto;padding:20px;text-align:center;color:var(--txt);font-size:12px;line-height:1.6}
        .vendor-chat-empty i{display:grid;place-items:center;width:46px;height:46px;border-radius:15px;background:#e3f5f3;color:var(--teal-2);font-size:22px}
        .vendor-chat-empty strong{color:var(--ink);font-size:13px}
        .vendor-chat-message{max-width:min(85%,520px);align-self:flex-start;padding:11px 14px;border:1px solid rgba(22,35,60,.07);border-radius:15px 15px 15px 5px;background:#fff;color:var(--ink);overflow-wrap:anywhere;font-size:12px;line-height:1.6;box-shadow:0 4px 12px rgba(31,51,89,.04)}
        .vendor-chat-message.mine{align-self:flex-end;border-color:rgba(56,170,180,.2);border-radius:15px 15px 5px 15px;background:linear-gradient(145deg,#e8f8f4,#eef6ff)}
        .vendor-chat-message time{display:block;margin-top:5px;color:var(--txt);font-size:10px}
        .vendor-compose{display:flex;gap:9px;margin-top:12px;padding:5px;border:1px solid rgba(22,35,60,.08);border-radius:13px;background:#fff;box-shadow:0 4px 14px rgba(31,51,89,.04)}.vendor-compose textarea{flex:1;min-width:0;min-height:46px;padding:11px 12px;border:0;border-radius:10px;background:transparent;color:var(--ink);font:inherit;font-size:12px;resize:vertical}.vendor-compose textarea:focus{outline:2px solid rgba(56,170,180,.22);outline-offset:-2px}
        .vendor-compose button{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:42px;padding:10px 15px;border:0;border-radius:11px;background:linear-gradient(135deg,var(--teal),var(--teal-2));color:#fff;font:700 11px/1.2 Poppins,sans-serif;cursor:pointer;box-shadow:0 7px 16px rgba(43,127,184,.19);transition:transform .18s,box-shadow .18s}.vendor-compose button:hover{transform:translateY(-1px);box-shadow:0 10px 20px rgba(43,127,184,.25)}.vendor-compose button:disabled{opacity:.6;cursor:wait;transform:none}
        .vendor-feedback{padding:12px 14px;margin-bottom:13px;border:1px solid #b9ebd4;border-radius:12px;background:#eaf9f0;color:#176a3b;font-size:12px}.vendor-feedback.error,.vendor-errors{padding:12px 14px;margin-bottom:13px;border:1px solid #fecaca;border-radius:12px;background:#fff1f2;color:#a52b40;font-size:12px}.vendor-feedback[hidden]{display:none}
        @media(max-width:860px){.vendor-topbar .hamburger{display:grid}}
        @media(max-width:520px){.vendor-main{padding-top:18px!important}.vendor-topbar{gap:10px}.vendor-topbar-title h1{font-size:18px}.vendor-topbar-title p{font-size:10px}.vendor-head{align-items:flex-start;padding:17px}.vendor-head-icon{width:44px;height:44px;border-radius:13px;font-size:21px}.vendor-head h1{font-size:20px}.vendor-head-actions{align-items:flex-end;flex-direction:column}.vendor-private{display:none}.vendor-withdrawals-link{padding:9px;font-size:0}.vendor-withdrawals-link i{font-size:17px}.vendor-panel{padding:15px}.vendor-chat{min-height:200px;padding:11px}.vendor-compose{flex-wrap:wrap;padding:4px}.vendor-compose textarea{flex:1 1 100%}.vendor-compose button{width:100%}}
    </style>
</head>
<body>
    <div class="scene" aria-hidden="true"><span class="orb orb-1"></span><span class="orb orb-2"></span><span class="orb orb-3"></span></div>
    <div class="layout">
        @include('user.include.sidebar')
        <main class="main vendor-main">
            <header class="vendor-topbar">
                <button class="hamburger" type="button" id="vendorMenuToggle" aria-label="Open navigation" aria-controls="sidebar" aria-expanded="false">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                </button>
                <div class="vendor-topbar-title"><h1>Marketplace</h1><p>Message the team managing your vendor account.</p></div>
            </header>
            <header class="vendor-head"><span class="vendor-head-icon" aria-hidden="true"><i class="ri-store-2-line"></i></span><div class="vendor-head-copy"><span class="vendor-eyebrow"><i class="ri-sparkling-2-line"></i> Vendor workspace</span><h1>Marketplace messages</h1><p>Send a private message to the marketplace team about your vendor account or product submission.</p></div><div class="vendor-head-actions"><span class="vendor-private"><i class="ri-shield-check-line"></i> Private workspace</span><a class="vendor-withdrawals-link" href="{{ route('user.marketplace.withdrawals') }}"><i class="ri-wallet-3-line" aria-hidden="true"></i> Withdrawals</a></div></header>
            @if(session('success'))<div class="vendor-feedback" role="status">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="vendor-errors" role="alert"><ul style="margin:0;padding-left:18px">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <div class="vendor-feedback" id="vendorFeedback" role="status" aria-live="polite" tabindex="-1" hidden></div>
            <div class="vendor-grid">
                <section class="vendor-panel">
                    <div class="vendor-panel-heading"><span class="vendor-panel-icon"><i class="ri-message-3-line" aria-hidden="true"></i></span><h2>Marketplace messages</h2></div><p>Only you and marketplace administrators can view this conversation.</p>
                    <div class="vendor-chat" id="vendorChat" aria-live="polite">
                        @forelse($messages as $message)
                            <article class="vendor-chat-message {{ $message->sender_id===auth()->id() ? 'mine' : '' }}" data-message-id="{{ $message->id }}"><div>{{ $message->body }}</div><time>{{ $message->created_at->format('M j, Y · g:i A') }} · {{ $message->sender_id===auth()->id() ? 'You' : 'Marketplace team' }}</time></article>
                        @empty<div class="vendor-chat-empty"><i class="ri-chat-smile-3-line" aria-hidden="true"></i><strong>Your conversation starts here</strong><span>No messages yet. Send a message to contact the marketplace team.</span></div>@endforelse
                    </div>
                    <form class="vendor-compose" id="marketplaceVendorComposer" method="POST" action="{{ route('user.marketplace.messages.send') }}">@csrf<label class="sr-only" for="vendorMessage">Message marketplace team</label><textarea id="vendorMessage" name="body" maxlength="5000" required placeholder="Write a message..."></textarea><button type="submit"><i class="ri-send-plane-fill"></i> Send</button></form>
                </section>
            </div>
        </main>
    </div>
    <div class="overlay" id="overlay"></div>
    <script>
    (() => {
        const sidebar=document.getElementById('sidebar');
        const overlay=document.getElementById('overlay');
        const menu=document.getElementById('vendorMenuToggle');
        if(sidebar&&overlay&&menu){
            const closeSidebar=()=>{sidebar.classList.remove('open');overlay.classList.remove('show');menu.setAttribute('aria-expanded','false')};
            menu.addEventListener('click',()=>{const open=!sidebar.classList.contains('open');sidebar.classList.toggle('open',open);overlay.classList.toggle('show',open);menu.setAttribute('aria-expanded',String(open))});
            overlay.addEventListener('click',closeSidebar);
            sidebar.querySelectorAll('a').forEach(link=>link.addEventListener('click',closeSidebar));
        }
        const log=document.getElementById('vendorChat');
        const form=document.getElementById('marketplaceVendorComposer');
        const feedback=document.getElementById('vendorFeedback');
        const feedUrl=@json(route('user.marketplace.messages'));
        const csrf=document.querySelector('meta[name="csrf-token"]')?.content||'';
        let lastId=Math.max(0,...Array.from(log.querySelectorAll('[data-message-id]'),item=>Number(item.dataset.messageId)||0));
        const addMessage=message=>{
            if(!message||!message.id||log.querySelector('[data-message-id="'+message.id+'"]'))return;
            const item=document.createElement('article');item.className='vendor-chat-message'+(message.mine?' mine':'');item.dataset.messageId=message.id;
            const body=document.createElement('div');body.textContent=message.body;
            const time=document.createElement('time');time.textContent=(message.created_at?new Date(message.created_at).toLocaleString():'')+' · '+(message.mine?'You':'Marketplace team');
            item.append(body,time);log.querySelector('.vendor-chat-empty')?.remove();log.appendChild(item);lastId=Math.max(lastId,Number(message.id));log.scrollTop=log.scrollHeight;
        };
        const refresh=()=>fetch(feedUrl+'?after='+lastId,{headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest'},credentials:'same-origin'})
            .then(response=>{if(!response.ok)throw new Error('Could not refresh marketplace messages.');return response.json()})
            .then(data=>(data.messages||[]).forEach(addMessage));
        form.addEventListener('submit',event=>{
            event.preventDefault();
            const button=form.querySelector('button');button.disabled=true;feedback.hidden=true;
            fetch(form.action,{method:'POST',body:new FormData(form),headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':csrf},credentials:'same-origin'})
                .then(response=>response.json().then(data=>{if(!response.ok)throw new Error(data.message||'Could not send your message.');return data}))
                .then(data=>{addMessage(data.message);form.querySelector('textarea').value='';})
                .catch(error=>{feedback.textContent=error.message||'Could not send your message. Please try again.';feedback.classList.add('error');feedback.hidden=false;feedback.focus();})
                .finally(()=>{button.disabled=false;form.querySelector('textarea').focus();});
        });
        refresh().catch(error=>console.error(error));
        const timer=window.setInterval(()=>{if(!document.hidden&&document.documentElement.getAttribute('aria-busy')!=='true')refresh().catch(error=>console.error(error));},4000);
        const stopPolling=()=>window.clearInterval(timer);
        window.addEventListener('pagehide',stopPolling,{once:true});
        window.addEventListener('ajax:navigating',stopPolling,{once:true});
        log.scrollTop=log.scrollHeight;
    })();
    </script>
</body>
</html>
