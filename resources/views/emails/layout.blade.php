<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>@yield('title', config('app.name', 'Cosmetics'))</title>
    <style>
        @media only screen and (max-width: 640px) {
            .email-shell { padding: 18px 10px !important; }
            .email-card { width: 100% !important; }
            .email-content { padding: 30px 22px !important; }
            .email-header { padding: 26px 20px !important; }
            .email-footer { padding: 24px 20px !important; }
            .email-title { font-size: 27px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background:#f4f2ed;color:#263b39;font-family:Arial,Helvetica,sans-serif;">
    <div style="display:none!important;visibility:hidden;opacity:0;color:transparent;height:0;width:0;overflow:hidden;">
        @yield('preheader', 'A note from ' . config('app.name', 'Cosmetics'))
    </div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f4f2ed" style="background:#f4f2ed;">
        <tr>
            <td align="center" class="email-shell" style="padding:42px 16px;">
                <table role="presentation" width="620" cellpadding="0" cellspacing="0" border="0" class="email-card" style="width:620px;max-width:620px;border-collapse:separate;border-spacing:0;">
                    <tr>
                        <td class="email-header" align="center" bgcolor="#123d38" style="padding:34px 34px 30px;background:#123d38;border-radius:20px 20px 0 0;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" width="38" height="38" bgcolor="#d6a976" style="width:38px;height:38px;border-radius:13px;background:#d6a976;color:#123d38;font-family:Georgia,serif;font-size:24px;font-weight:bold;line-height:38px;">
                                        C
                                    </td>
                                    <td style="padding-left:12px;color:#fff;font-family:Georgia,'Times New Roman',serif;font-size:22px;letter-spacing:2px;">
                                        {{ config('app.name', 'Cosmetics') }}
                                    </td>
                                </tr>
                            </table>
                            <div style="margin-top:12px;color:#c9ded5;font-size:10px;letter-spacing:3px;text-transform:uppercase;">
                                Beauty, thoughtfully chosen
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td height="4" bgcolor="#d6a976" style="height:4px;background:#d6a976;font-size:0;line-height:4px;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="email-content" bgcolor="#ffffff" style="padding:42px 48px 38px;background:#fff;">
                            <div style="margin-bottom:12px;color:#ad8150;font-size:10px;font-weight:bold;letter-spacing:2.5px;text-align:center;text-transform:uppercase;">
                                @yield('eyebrow', 'A note from our team')
                            </div>
                            <h1 class="email-title" style="margin:0 0 14px;color:#173d38;font-family:Georgia,'Times New Roman',serif;font-size:32px;font-weight:normal;line-height:1.25;text-align:center;">
                                @yield('heading')
                            </h1>
                            <p style="margin:0 auto 28px;max-width:440px;color:#687875;font-size:15px;line-height:1.8;text-align:center;">
                                @yield('intro')
                            </p>
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td class="email-footer" align="center" bgcolor="#123d38" style="padding:28px 36px;background:#123d38;border-radius:0 0 20px 20px;">
                            <div style="color:#fff;font-family:Georgia,'Times New Roman',serif;font-size:18px;">
                                {{ config('app.name', 'Cosmetics') }}
                            </div>
                            <p style="margin:9px 0 0;color:#c9ded5;font-size:12px;line-height:1.7;">
                                @if(config('mail.from.address'))
                                    Need a hand? <a href="mailto:{{ config('mail.from.address') }}" style="color:#ecd0a5;text-decoration:underline;">{{ config('mail.from.address') }}</a>
                                @else
                                    Thank you for being part of our community.
                                @endif
                            </p>
                            <p style="margin:13px 0 0;color:#91aaa2;font-size:11px;line-height:1.6;">
                                &copy; {{ date('Y') }} {{ config('app.name', 'Cosmetics') }}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
