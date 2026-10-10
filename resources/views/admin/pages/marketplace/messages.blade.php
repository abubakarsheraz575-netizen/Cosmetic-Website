@extends('admin.layouts.ecommerce')

@section('title', 'Vendor messages')

@section('content')
<style>
    .market-message-layout{display:grid;grid-template-columns:minmax(230px,310px) minmax(0,1fr);min-height:600px;padding:0;overflow:hidden}
    .vendor-inbox{border-right:1px solid var(--border);min-width:0}.vendor-inbox-head{padding:20px;border-bottom:1px solid var(--border)}.vendor-inbox-head h2{margin:0;font-size:16px}.vendor-inbox-head p{margin:5px 0 0;color:var(--muted);font-size:11px}
    .vendor-inbox-list{max-height:680px;overflow:auto}.vendor-contact{display:flex;align-items:center;gap:10px;padding:14px 16px;border-bottom:1px solid var(--border);color:var(--text);text-decoration:none}.vendor-contact:hover,.vendor-contact.active{background:rgba(62,197,224,.1)}.vendor-contact-avatar{width:40px;height:40px;flex:none;display:grid;place-items:center;overflow:hidden;border-radius:50%;background:#e4f6fb;color:#0e5a75;font-weight:800}.vendor-contact-avatar img{width:100%;height:100%;object-fit:cover}.vendor-contact-copy{min-width:0;flex:1}.vendor-contact-copy strong,.vendor-contact-copy small{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.vendor-contact-copy small{margin-top:4px;color:var(--muted);font-size:11px}.unread-dot{min-width:20px;height:20px;padding:0 6px;display:grid;place-items:center;border-radius:20px;background:#0e8fb5;color:#fff;font-size:10px;font-weight:800}
    .vendor-chat{display:flex;flex-direction:column;min-width:0;background:linear-gradient(180deg,#f8fbfb,#f5f7f9)}.vendor-chat-head{display:flex;align-items:center;gap:11px;padding:17px 20px;border-bottom:1px solid var(--border);background:var(--card)}.vendor-chat-head strong,.vendor-chat-head small{display:block}.vendor-chat-head small{margin-top:3px;color:var(--muted);font-size:11px}.vendor-chat-log{display:flex;flex:1;flex-direction:column;gap:12px;min-height:400px;max-height:630px;overflow:auto;padding:22px}.vendor-message{max-width:min(78%,560px);padding:11px 14px;border:1px solid var(--border);border-radius:15px 15px 15px 4px;background:var(--card);box-shadow:0 4px 12px rgba(15,23,42,.04);overflow-wrap:anywhere;line-height:1.5}.vendor-message.mine{align-self:flex-end;border-color:#bce3e3;border-radius:15px 15px 4px 15px;background:#e7f6f3}.vendor-message time{display:block;margin-top:6px;color:var(--muted);font-size:10px}.vendor-chat-compose{display:flex;gap:10px;padding:14px;border-top:1px solid var(--border);background:var(--card)}.vendor-chat-compose textarea{flex:1;min-width:0;min-height:44px;max-height:140px;resize:vertical;padding:11px 13px;border:1px solid var(--border);border-radius:11px;background:var(--bg);color:var(--text);font:inherit}.vendor-chat-compose button{align-self:flex-end;min-height:44px;padding:0 16px;border:0;border-radius:11px;background:var(--brand);color:white;font:inherit;font-weight:700;cursor:pointer}.vendor-chat-empty{display:grid;place-items:center;flex:1;min-height:420px;padding:30px;color:var(--muted);text-align:center}.vendor-chat-empty i{display:block;color:var(--brand);font-size:38px;margin-bottom:10px}
    @media(max-width:760px){.market-message-layout{grid-template-columns:1fr}.vendor-inbox{border-right:0;border-bottom:1px solid var(--border)}.vendor-inbox-list{display:flex;max-height:none;overflow:auto}.vendor-contact{min-width:210px;border-right:1px solid var(--border)}.vendor-chat-log{min-height:360px;padding:15px}.vendor-message{max-width:90%}}
</style>
<header class="page-head"><div><h1><i class="ri-chat-3-line"></i>Vendor messages</h1><p>A private inbox for marketplace vendors, separate from customer support.</p></div></header>
@if(session('success'))<div class="flash-success" role="status">{{ session('success') }}</div>@endif
@if($errors->any())<div class="errors" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<section class="card market-message-layout">
    <aside class="vendor-inbox">
        <div class="vendor-inbox-head"><h2>Vendors</h2><p>{{ number_format($vendors->count()) }} vendor conversations</p></div>
        <nav class="vendor-inbox-list" aria-label="Vendor conversations">
            @forelse($vendors as $vendor)
                <a class="vendor-contact {{ $selected?->id===$vendor->id ? 'active' : '' }}" href="{{ route('admin.marketplace.messages',['vendor'=>$vendor->id]) }}">
                    <span class="vendor-contact-avatar">@if($vendor->profile_photo)<img src="{{ asset('storage/'.$vendor->profile_photo) }}" alt="">@else{{ mb_strtoupper(mb_substr($vendor->name,0,1)) }}@endif</span>
                    <span class="vendor-contact-copy"><strong>{{ $vendor->name }}</strong><small>{{ $vendor->email }}</small></span>
                    @if($unreadCounts[$vendor->id]??0)<span class="unread-dot">{{ $unreadCounts[$vendor->id] }}</span>@endif
                </a>
            @empty<span class="empty">No vendors with submitted products yet.</span>@endforelse
        </nav>
    </aside>
    <section class="vendor-chat" aria-label="Marketplace vendor conversation">
        @if($selected)
            <header class="vendor-chat-head"><span class="vendor-contact-avatar">@if($selected->profile_photo)<img src="{{ asset('storage/'.$selected->profile_photo) }}" alt="">@else{{ mb_strtoupper(mb_substr($selected->name,0,1)) }}@endif</span><div><strong>{{ $selected->name }}</strong><small>{{ $selected->email }} · Marketplace conversation</small></div></header>
            <div class="vendor-chat-log" id="vendorChatLog" aria-live="polite">
                @forelse($messages as $message)
                    <article class="vendor-message {{ $message->sender_id==auth()->id() ? 'mine' : '' }}" data-message-id="{{ $message->id }}"><div>{{ $message->body }}</div><time>{{ $message->created_at->format('M j, Y · g:i A') }} · {{ $message->sender_id==auth()->id() ? 'You' : $selected->name }}</time></article>
                @empty<div class="vendor-chat-empty"><div><i class="ri-chat-3-line"></i><strong>Start a vendor conversation</strong><div>Only this vendor and marketplace administrators can see these messages.</div></div></div>@endforelse
            </div>
            <form class="vendor-chat-compose" id="marketplaceAdminComposer" method="POST" action="{{ route('admin.marketplace.messages.send') }}">@csrf<input type="hidden" name="vendor_id" value="{{ $selected->id }}"><label class="sr-only" for="vendorMessage">Message to {{ $selected->name }}</label><textarea id="vendorMessage" name="body" maxlength="5000" required placeholder="Write a private message to {{ $selected->name }}..."></textarea><button type="submit" aria-label="Send message"><i class="ri-send-plane-fill"></i> Send</button></form>
        @else
            <div class="vendor-chat-empty"><div><i class="ri-message-2-line"></i><strong>Select a vendor</strong><div>Choose a vendor to read or send marketplace-only messages.</div></div></div>
        @endif
    </section>
</section>
@if($selected)
<script>
(() => {
    const log=document.getElementById('vendorChatLog');
    const form=document.getElementById('marketplaceAdminComposer');
    const feedUrl=@json(route('admin.marketplace.messages.feed',['vendor'=>$selected->id]));
    const csrf=document.querySelector('meta[name="csrf-token"]')?.content||'';
    let lastId=Math.max(0,...Array.from(log.querySelectorAll('[data-message-id]'),item=>Number(item.dataset.messageId)||0));
    let stopped=false;
    const addMessage=message=>{
        if(!message||!message.id||log.querySelector('[data-message-id="'+message.id+'"]'))return;
        const item=document.createElement('article');item.className='vendor-message'+(message.mine?' mine':'');item.dataset.messageId=message.id;
        const body=document.createElement('div');body.textContent=message.body;
        const time=document.createElement('time');time.textContent=(message.created_at?new Date(message.created_at).toLocaleString():'')+' · '+(message.mine?'You':@json($selected->name));
        item.append(body,time);log.querySelector('.vendor-chat-empty')?.remove();log.appendChild(item);lastId=Math.max(lastId,Number(message.id));log.scrollTop=log.scrollHeight;
    };
    const refresh=()=>fetch(feedUrl+'&after='+lastId,{headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest'},credentials:'same-origin'})
        .then(response=>{if(!response.ok)throw new Error('Could not refresh the vendor conversation.');return response.json()})
        .then(data=>(data.messages||[]).forEach(addMessage));
    form.addEventListener('submit',event=>{
        event.preventDefault();
        const button=form.querySelector('button');button.disabled=true;
        fetch(form.action,{method:'POST',body:new FormData(form),headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':csrf},credentials:'same-origin'})
            .then(response=>response.json().then(data=>{if(!response.ok)throw new Error(data.message||'Could not send this message.');return data}))
            .then(data=>{addMessage(data.message);form.querySelector('textarea').value='';})
            .catch(error=>{window.alert(error.message);})
            .finally(()=>{button.disabled=false;form.querySelector('textarea').focus();});
    });
    refresh().catch(error=>console.error(error));
    const timer=window.setInterval(()=>{if(!document.hidden&&document.documentElement.getAttribute('aria-busy')!=='true')refresh().catch(error=>console.error(error));},4000);
    const stopPolling=()=>{stopped=true;window.clearInterval(timer);};
    window.addEventListener('pagehide',stopPolling,{once:true});
    window.addEventListener('ajax:navigating',stopPolling,{once:true});
    log.scrollTop=log.scrollHeight;
})();
</script>
@endif
@endsection
