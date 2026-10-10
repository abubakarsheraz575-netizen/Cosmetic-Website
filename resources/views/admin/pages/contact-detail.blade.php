<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $contactMessage->subject ?: 'Contact conversation' }} — Cosmetics Admin</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}?v=3">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <style data-page>
        .contacts-page{padding:28px;max-width:1000px;margin:auto}
        .detail-top{display:flex;justify-content:space-between;align-items:center;gap:14px;margin-bottom:22px}
        .back-link{display:inline-flex;align-items:center;gap:7px;color:var(--muted);font-size:12px;font-weight:700;text-decoration:none}
        .back-link:hover{color:var(--forest)}
        .detail-count{color:var(--muted);font-size:11px}
        .contacts-alert{margin-bottom:16px;padding:12px 15px;border:1px solid #a7f3d0;border-radius:12px;background:#ecfdf5;color:#067647;font-size:12px;font-weight:700}
        .contacts-alert.error{border-color:#fecdd3;background:#fff1f2;color:#be123c}
        .detail-card{overflow:hidden;border:1px solid var(--border);border-radius:17px;background:var(--card);box-shadow:var(--shadow)}
        .detail-heading{padding:22px 24px;border-bottom:1px solid var(--border)}
        .detail-heading h1{margin:0;color:var(--text);font-size:22px;font-weight:800;letter-spacing:-.4px}
        .detail-heading p{margin:7px 0 0;color:var(--muted);font-size:12px}
        .detail-heading a{color:var(--forest);text-decoration:none}
        .detail-heading a:hover{text-decoration:underline}
        .detail-body{padding:24px}
        .message-label{display:flex;justify-content:space-between;gap:12px;color:var(--muted);font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
        .message-date{font-weight:500;letter-spacing:0;text-transform:none}
        .message-content{margin:12px 0 24px;padding:19px 20px;border:1px solid var(--border);border-radius:13px;background:var(--bg);color:var(--text);font-size:13px;line-height:1.75;white-space:pre-wrap;overflow-wrap:anywhere}
        .reply-history{margin:-7px 0 24px;padding:14px 17px;border-left:3px solid #22a06b;border-radius:9px;background:#f0fdf4;color:#166534;font-size:12px;line-height:1.7;white-space:pre-wrap;overflow-wrap:anywhere}
        .reply-history strong{display:block;margin-bottom:5px}
        .reply-title{margin:0 0 12px;color:var(--text);font-size:14px;font-weight:800}
        .contact-reply-form label{display:block;margin-bottom:7px;color:var(--text);font-size:11px;font-weight:700}
        .contact-reply-form textarea{width:100%;min-height:120px;resize:vertical;padding:13px 14px;border:1px solid var(--border);border-radius:11px;background:var(--card);color:var(--text);font:inherit;font-size:12px;line-height:1.6}
        .contact-reply-form textarea::placeholder{color:var(--muted);opacity:.85}
        .contact-reply-form textarea:focus{outline:2px solid rgba(62,197,224,.3);border-color:var(--lime)}
        .detail-actions{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-top:12px}
        .send-reply{display:inline-flex;align-items:center;gap:7px;padding:10px 15px;border:0;border-radius:10px;background:var(--forest);color:#fff;font:inherit;font-size:11px;font-weight:800;cursor:pointer}
        .send-reply:hover{background:var(--forest-2)}
        .delete-form{margin:0}
        .delete-button{display:inline-flex;align-items:center;gap:6px;padding:9px 11px;border:1px solid #fecdd3;border-radius:10px;background:transparent;color:#be123c;font:inherit;font-size:11px;font-weight:700;cursor:pointer}
        .delete-button:hover{background:#fff1f2}
        .detail-hint{margin:9px 0 0;color:var(--muted);font-size:10px}
        @media(max-width:600px){.contacts-page{padding:18px 12px}.detail-heading,.detail-body{padding:18px}.detail-heading h1{font-size:19px}.detail-actions{align-items:flex-start;flex-direction:column-reverse}}
    </style>
    @include('admin.include.shell-layout')
</head>
<body>
    <div class="layout" id="layout">
        @include('admin.include.sidebar')
        <div class="main">
            @include('admin.include.navbar')
            <main class="contacts-page">
                <div class="detail-top">
                    <a class="back-link" href="{{ route('admin.contacts') }}"><i class="ri-arrow-left-line"></i>Back to inbox</a>
                    <span class="detail-count">Conversation #{{ $contactMessage->id }}</span>
                </div>

                @if (session('contact_reply_success'))
                    <div class="contacts-alert" role="status">{{ session('contact_reply_success') }}</div>
                @endif
                @if (session('contact_reply_error') || session('contact_delete_error'))
                    <div class="contacts-alert error" role="alert">{{ session('contact_reply_error') ?: session('contact_delete_error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="contacts-alert error" role="alert">{{ $errors->first() }}</div>
                @endif

                <article class="detail-card">
                    <header class="detail-heading">
                        <h1>{{ $contactMessage->subject ?: 'General inquiry' }}</h1>
                        <p>From <strong>{{ $contactMessage->name }}</strong> · <a href="mailto:{{ $contactMessage->email }}">{{ $contactMessage->email }}</a></p>
                    </header>
                    <div class="detail-body">
                        <div class="message-label">
                            <span>Customer message</span>
                            <time class="message-date" datetime="{{ $contactMessage->created_at->toIso8601String() }}">{{ $contactMessage->created_at->format('M j, Y · g:i A') }}</time>
                        </div>
                        <div class="message-content">{{ $contactMessage->message }}</div>

                        @if ($contactMessage->admin_reply)
                            <div class="reply-history">
                                <strong>Your last reply · {{ $contactMessage->replied_at?->format('M j, Y · g:i A') }}</strong>
                                {{ $contactMessage->admin_reply }}
                            </div>
                        @endif

                        <h2 class="reply-title">Reply to {{ $contactMessage->name }}</h2>
                        <form class="contact-reply-form" method="POST" action="{{ route('admin.contacts.reply', $contactMessage) }}">
                            @csrf
                            <label for="reply">Your reply</label>
                            <textarea id="reply" name="reply" required maxlength="5000" placeholder="Write a reply to {{ $contactMessage->name }}…">{{ old('reply') }}</textarea>
                            <div class="detail-actions">
                                <button class="send-reply" type="submit"><i class="ri-send-plane-line"></i>Send reply by email</button>
                            </div>
                            <p class="detail-hint">Your reply will be sent to {{ $contactMessage->email }} and saved in this conversation.</p>
                        </form>
                        <div class="detail-actions">
                            <span></span>
                            <form class="delete-form" method="POST" action="{{ route('admin.contacts.destroy', $contactMessage) }}" data-confirm-modal data-confirm-title="Delete this contact message?" data-confirm-message="This message will be permanently removed.">
                                @csrf
                                @method('DELETE')
                                <button class="delete-button" type="submit"><i class="ri-delete-bin-line"></i>Delete conversation</button>
                            </form>
                        </div>
                    </div>
                </article>
            </main>
        </div>
    </div>
</body>
</html>
