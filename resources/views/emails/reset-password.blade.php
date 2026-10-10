@extends('emails.layout')

@section('title', 'Reset your password')
@section('preheader', 'A secure link to reset your ' . config('app.name', 'Cosmetics') password.')
@section('eyebrow', 'Account security')
@section('heading', 'Let’s get you back in')
@section('intro')
    Hello {{ $user->name }}, we received a request to reset the password for your {{ config('app.name', 'Cosmetics') }} account.
@endsection

@section('content')
    <table role="presentation" align="center" cellpadding="0" cellspacing="0" border="0" style="margin:0 auto 27px;">
        <tr>
            <td align="center" bgcolor="#287c70" style="border-radius:9px;background:#287c70;">
                <a href="{{ $url }}" style="display:inline-block;padding:16px 30px;color:#fff;font-size:12px;font-weight:bold;letter-spacing:1.4px;text-decoration:none;text-transform:uppercase;">
                    Choose a new password
                </a>
            </td>
        </tr>
    </table>
    <p style="margin:0 0 20px;color:#687875;font-size:13px;line-height:1.7;text-align:center;">
        This secure link expires in <strong style="color:#173d38;">{{ $expire }} minutes</strong>.
    </p>
    <div style="padding:17px 19px;border:1px solid #e8ece7;border-radius:11px;background:#fafaf7;">
        <div style="margin-bottom:7px;color:#747f7b;font-size:12px;line-height:1.6;">If the button doesn’t work, copy and paste this link into your browser:</div>
        <a href="{{ $url }}" style="color:#39877b;font-size:12px;line-height:1.7;word-break:break-all;">{{ $url }}</a>
    </div>
    <p style="margin:22px 0 0;color:#88938f;font-size:12px;line-height:1.7;text-align:center;">If you didn’t request a password reset, you can ignore this email. Your password will remain unchanged.</p>
@endsection
