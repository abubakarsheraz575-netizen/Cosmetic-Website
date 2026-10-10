@extends('emails.layout')

@section('title', 'New contact inquiry')
@section('preheader', 'A new message has arrived from your store contact form.')
@section('eyebrow', 'A new conversation')
@section('heading', 'A customer would love to hear from you')
@section('intro', 'A new note has arrived through your website. Here are the details to help you reply personally.')

@section('content')
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 20px;border:1px solid #e9eeea;border-radius:14px;">
        <tr>
            <td style="padding:18px 20px;border-bottom:1px solid #e9eeea;">
                <div style="color:#9a8267;font-size:10px;font-weight:bold;letter-spacing:1.8px;text-transform:uppercase;">From</div>
                <div style="margin-top:7px;color:#173d38;font-size:16px;font-weight:bold;">{{ $contactMessage->name }}</div>
                <div style="margin-top:4px;font-size:13px;">
                    <a href="mailto:{{ $contactMessage->email }}" style="color:#39877b;text-decoration:none;">{{ $contactMessage->email }}</a>
                </div>
            </td>
        </tr>
        <tr>
            <td style="padding:16px 20px;">
                <div style="color:#9a8267;font-size:10px;font-weight:bold;letter-spacing:1.8px;text-transform:uppercase;">Subject</div>
                <div style="margin-top:7px;color:#263b39;font-size:14px;line-height:1.6;">{{ $contactMessage->subject ?: 'General inquiry' }}</div>
            </td>
        </tr>
    </table>

    <div style="margin:0 0 9px;color:#9a8267;font-size:10px;font-weight:bold;letter-spacing:1.8px;text-transform:uppercase;">Their message</div>
    <div style="padding:19px 21px;border-left:3px solid #d6a976;border-radius:0 12px 12px 0;background:#f8f7f3;color:#425652;font-size:14px;line-height:1.8;white-space:pre-wrap;">{{ $contactMessage->message }}</div>

    <table role="presentation" align="center" cellpadding="0" cellspacing="0" border="0" style="margin:28px auto 0;">
        <tr>
            <td align="center" bgcolor="#287c70" style="border-radius:9px;background:#287c70;">
                <a href="mailto:{{ $contactMessage->email }}?subject={{ rawurlencode('Re: ' . ($contactMessage->subject ?: 'Your inquiry')) }}" style="display:inline-block;padding:14px 24px;color:#fff;font-size:12px;font-weight:bold;letter-spacing:1px;text-decoration:none;text-transform:uppercase;">
                    Reply to {{ $contactMessage->name }}
                </a>
            </td>
        </tr>
    </table>
    <p style="margin:16px 0 0;color:#88938f;font-size:12px;line-height:1.6;text-align:center;">You can also reply directly to this email to reach the customer.</p>
@endsection
