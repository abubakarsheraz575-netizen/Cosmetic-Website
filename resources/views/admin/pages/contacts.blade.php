<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Contact Inbox — Cosmetics Admin</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}?v=3">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <style data-page>
        .contacts-page{padding:28px;max-width:1240px;margin:auto}
        .contacts-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:24px}
        .contacts-head h1{margin:0;color:var(--text);font-size:25px;font-weight:800;letter-spacing:-.5px}
        .contacts-head p{margin:6px 0 0;color:var(--muted);font-size:12px}
        .contacts-count{display:inline-flex;align-items:center;gap:8px;padding:9px 13px;border:1px solid var(--border);border-radius:12px;background:var(--card);color:var(--muted);font-size:12px;font-weight:700;white-space:nowrap}
        .contacts-count i{color:var(--lime);font-size:16px}
        .contacts-alert{margin-bottom:16px;padding:12px 15px;border:1px solid #a7f3d0;border-radius:12px;background:#ecfdf5;color:#067647;font-size:12px;font-weight:700}
        .contacts-alert.error{border-color:#fecdd3;background:#fff1f2;color:#be123c}
        .inbox-card{overflow:hidden;border:1px solid var(--border);border-radius:17px;background:var(--card);box-shadow:var(--shadow)}
        .inbox-toolbar{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:17px 20px;border-bottom:1px solid var(--border)}
        .inbox-toolbar strong{color:var(--text);font-size:13px}
        .inbox-toolbar span{color:var(--muted);font-size:11px}
        .inbox-table{width:100%;border-collapse:collapse;text-align:left}
        .inbox-table th{padding:12px 18px;background:var(--bg);color:var(--muted);font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
        .inbox-table td{padding:15px 18px;border-top:1px solid var(--border);color:var(--text);font-size:12px;vertical-align:middle}
        .inbox-table tbody tr:first-child td{border-top:0}
        .inbox-table tbody tr{transition:background .16s}
        .inbox-table tbody tr:hover{background:var(--bg)}
        .inbox-table tr.unread td:first-child{box-shadow:inset 3px 0 var(--lime)}
        .sender-link{display:inline-flex;align-items:center;gap:10px;color:var(--text);font-weight:800;text-decoration:none}
        .sender-link:hover{color:var(--forest)}
        .sender-avatar{display:grid;width:34px;height:34px;flex:none;place-items:center;border-radius:11px;background:var(--lime-soft);color:var(--forest);font-size:12px;font-weight:800}
        .sender-info{display:grid;gap:3px}
        .sender-email{color:var(--muted);font-size:10px;font-weight:500}
        .subject-title{display:block;color:var(--text);font-weight:700}
        .message-preview{display:block;max-width:370px;margin-top:4px;overflow:hidden;color:var(--muted);font-size:11px;text-overflow:ellipsis;white-space:nowrap}
        .contact-status{display:inline-flex;align-items:center;gap:5px;padding:5px 8px;border-radius:999px;background:var(--bg);color:var(--muted);font-size:10px;font-weight:700;white-space:nowrap}
        .contact-status.unread{background:var(--lime-soft);color:var(--forest)}
        .contact-status i{font-size:12px}
        .contact-date{color:var(--muted);font-size:10px;white-space:nowrap}
        .open-message{display:inline-flex;align-items:center;gap:5px;color:var(--forest);font-size:11px;font-weight:800;text-decoration:none;white-space:nowrap}
        .open-message:hover{text-decoration:underline}
        .contact-empty{padding:58px 20px;color:var(--muted);text-align:center}
        .contact-empty i{display:block;margin-bottom:10px;color:var(--lime);font-size:30px}
        .contact-empty strong{display:block;margin-bottom:5px;color:var(--text);font-size:14px}
        .contact-empty p{margin:0;font-size:11px}
        .contact-pagination{margin-top:18px}
        @media(max-width:760px){.contacts-page{padding:20px 14px}.inbox-table th,.inbox-table td{padding:12px}.inbox-table th:nth-child(3),.inbox-table td:nth-child(3){display:none}.message-preview{max-width:220px}}
        @media(max-width:520px){.contacts-page{padding:16px 10px}.contacts-head{align-items:flex-start}.contacts-head h1{font-size:21px}.inbox-toolbar{padding:14px}.inbox-table th,.inbox-table td{padding:11px 9px}.inbox-table th:last-child,.inbox-table td:last-child{display:none}.sender-avatar{width:30px;height:30px}.message-preview{max-width:155px}}
    </style>
    @include('admin.include.shell-layout')
</head>
<body>
    <div class="layout" id="layout">
        @include('admin.include.sidebar')
        <div class="main">
            @include('admin.include.navbar')
            <main class="contacts-page">
                <header class="contacts-head">
                    <div>
                        <h1>Contact inbox</h1>
                        <p>Every customer message has its own conversation. Open one to read and reply.</p>
                    </div>
                    <span class="contacts-count"><i class="ri-inbox-2-line"></i>{{ $messages->total() }} {{ \Illuminate\Support\Str::plural('message', $messages->total()) }}</span>
                </header>

                @if (session('contact_reply_success') || session('contact_delete_success'))
                    <div class="contacts-alert" role="status">{{ session('contact_reply_success') ?: session('contact_delete_success') }}</div>
                @endif
                @if (session('contact_reply_error') || session('contact_delete_error'))
                    <div class="contacts-alert error" role="alert">{{ session('contact_reply_error') ?: session('contact_delete_error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="contacts-alert error" role="alert">{{ $errors->first() }}</div>
                @endif

                <section class="inbox-card" aria-label="Contact messages">
                    <div class="inbox-toolbar">
                        <strong>All conversations</strong>
                        <span>Newest first · 20 per page</span>
                    </div>
                    @if ($messages->isNotEmpty())
                        <div style="overflow-x:auto">
                            <table class="inbox-table">
                                <thead>
                                    <tr><th>Sender</th><th>Message</th><th>Status</th><th>Received</th><th></th></tr>
                                </thead>
                                <tbody>
                                    @foreach ($messages as $contactMessage)
                                        <tr class="{{ $contactMessage->read_at ? '' : 'unread' }}">
                                            <td>
                                                <a class="sender-link" href="{{ route('admin.contacts.show', $contactMessage) }}">
                                                    <span class="sender-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($contactMessage->name, 0, 1)) }}</span>
                                                    <span class="sender-info">
                                                        <span>{{ $contactMessage->name }}</span>
                                                        <span class="sender-email">{{ $contactMessage->email }}</span>
                                                    </span>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="subject-title">{{ $contactMessage->subject ?: 'General inquiry' }}</span>
                                                <span class="message-preview">{{ $contactMessage->message }}</span>
                                            </td>
                                            <td>
                                                @if (! $contactMessage->read_at)
                                                    <span class="contact-status unread"><i class="ri-mail-unread-line"></i>New</span>
                                                @elseif ($contactMessage->admin_reply)
                                                    <span class="contact-status"><i class="ri-reply-line"></i>Replied</span>
                                                @else
                                                    <span class="contact-status"><i class="ri-mail-open-line"></i>Read</span>
                                                @endif
                                            </td>
                                            <td><time class="contact-date" datetime="{{ $contactMessage->created_at->toIso8601String() }}">{{ $contactMessage->created_at->format('M j, Y · g:i A') }}</time></td>
                                            <td><a class="open-message" href="{{ route('admin.contacts.show', $contactMessage) }}">Open <i class="ri-arrow-right-line"></i></a></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="contact-empty">
                            <i class="ri-mail-open-line"></i>
                            <strong>Your inbox is clear</strong>
                            <p>New messages submitted through the contact form will appear here.</p>
                        </div>
                    @endif
                </section>
                <div class="contact-pagination">{{ $messages->links() }}</div>
            </main>
        </div>
    </div>
</body>
</html>
