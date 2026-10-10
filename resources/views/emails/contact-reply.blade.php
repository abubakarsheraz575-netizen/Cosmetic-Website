@extends('emails.layout')

@section('title', 'A reply to your message')
@section('preheader', 'The Cosmetics team has replied to your message.')
@section('eyebrow', 'A personal reply')
@section('heading', 'We’re glad you got in touch')
@section('intro')
    Hello {{ $customerName }}, our team has responded to your message. We hope we can help.
@endsection

@section('content')
    <div style="padding:21px 22px;border-radius:14px;background:#f8f7f3;color:#425652;font-size:14px;line-height:1.85;white-space:pre-wrap;">{{ $reply }}</div>

    @if($originalMessage)
        <div style="margin:24px 0 8px;color:#9a8267;font-size:10px;font-weight:bold;letter-spacing:1.8px;text-transform:uppercase;">Your original message</div>
        <div style="padding:16px 19px;border-left:3px solid #d6a976;border-radius:0 10px 10px 0;background:#fbfaf8;color:#798581;font-size:13px;line-height:1.75;white-space:pre-wrap;">{{ $originalMessage }}</div>
    @endif

    <p style="margin:24px 0 0;color:#687875;font-size:13px;line-height:1.7;text-align:center;">Simply reply to this email if there’s anything else we can do for you.</p>
@endsection
