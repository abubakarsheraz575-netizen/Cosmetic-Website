<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $activityTitle }}</title>
</head>
<body style="margin:0;padding:28px 12px;background:#f3f7f6;color:#233b37;font:15px/1.6 Arial,sans-serif">
    <main style="max-width:620px;margin:0 auto;padding:30px;border:1px solid #e1ebe7;border-radius:16px;background:#fff">
        <p style="margin:0 0 10px;color:#278477;font-size:11px;font-weight:700;letter-spacing:1.8px;text-transform:uppercase">Cosmetics · Admin alert</p>
        <h1 style="margin:0 0 22px;color:#173d38;font-size:24px;line-height:1.3">{{ $activityTitle }}</h1>
        <table role="presentation" style="width:100%;border-collapse:collapse">
            @foreach($details as $label => $value)
            <tr>
                <th scope="row" style="width:145px;padding:11px 12px;border-bottom:1px solid #edf2ef;color:#73827d;font-size:12px;text-align:left;vertical-align:top">{{ $label }}</th>
                <td style="padding:11px 12px;border-bottom:1px solid #edf2ef;color:#263f39;font-size:14px;white-space:pre-wrap;overflow-wrap:anywhere">{{ $value }}</td>
            </tr>
            @endforeach
        </table>
        <p style="margin:22px 0 0;color:#82908b;font-size:12px">This is an automatic notification from the Cosmetics website.</p>
    </main>
</body>
</html>
