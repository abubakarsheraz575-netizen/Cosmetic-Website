<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Your product sold</title></head>
<body style="margin:0;padding:28px 12px;background:#f3f7f6;color:#233b37;font:15px/1.6 Arial,sans-serif">
    <main style="max-width:620px;margin:0 auto;padding:30px;border:1px solid #e1ebe7;border-radius:16px;background:#fff">
        <p style="margin:0 0 10px;color:#278477;font-size:11px;font-weight:700;letter-spacing:1.8px;text-transform:uppercase">Cosmetics · Marketplace sale</p>
        <h1 style="margin:0 0 8px;color:#173d38;font-size:24px;line-height:1.3">Congratulations, {{ $vendorName }}!</h1>
        <p style="margin:0 0 18px;color:#65746f">A customer payment has been confirmed for order <strong>{{ $orderNumber }}</strong>.</p>
        <ul style="margin:0;padding:0 0 0 20px;color:#263f39">
            @foreach($items as $item)
                <li>{{ $item['name'] }} × {{ $item['quantity'] }}</li>
            @endforeach
        </ul>
        <p style="margin:22px 0 0"><a href="{{ $payoutUrl }}" style="display:inline-block;padding:12px 18px;border-radius:8px;background:#147b70;color:#fff;text-decoration:none;font-weight:700">View your paid sales and request payout</a></p>
        <p style="margin:22px 0 0;color:#82908b;font-size:12px">Your payout becomes available after the customer payment is verified. The marketplace team reviews payout requests.</p>
    </main>
</body>
</html>
