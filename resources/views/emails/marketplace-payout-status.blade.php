<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Payout request update</title></head>
<body style="margin:0;padding:28px 12px;background:#f3f7f6;color:#233b37;font:15px/1.6 Arial,sans-serif">
    <main style="max-width:620px;margin:0 auto;padding:30px;border:1px solid #e1ebe7;border-radius:16px;background:#fff">
        <p style="margin:0 0 10px;color:#278477;font-size:11px;font-weight:700;letter-spacing:1.8px;text-transform:uppercase">Cosmetics · Marketplace payout</p>
        <h1 style="margin:0 0 8px;color:#173d38;font-size:24px;line-height:1.3">Your payout request is {{ ucfirst($withdrawal->status) }}.</h1>
        <p style="margin:0;color:#65746f">Order <strong>{{ $withdrawal->order_number }}</strong> · £{{ number_format((float) $withdrawal->amount, 2) }}</p>
        <p style="margin:22px 0 0;color:#82908b;font-size:12px">Sign in to your marketplace account to review your payout history.</p>
    </main>
</body>
</html>
