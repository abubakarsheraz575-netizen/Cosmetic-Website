<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order {{ $order->order_number }} confirmed</title>
</head>
<body style="margin:0;padding:28px 12px;background:#f3f7f6;color:#233b37;font:15px/1.6 Arial,sans-serif">
    <main style="max-width:620px;margin:0 auto;padding:30px;border:1px solid #e1ebe7;border-radius:16px;background:#fff">
        <p style="margin:0 0 10px;color:#278477;font-size:11px;font-weight:700;letter-spacing:1.8px;text-transform:uppercase">Cosmetics · Order confirmation</p>
        <h1 style="margin:0 0 8px;color:#173d38;font-size:24px;line-height:1.3">Thank you for your order, {{ $order->name }}.</h1>
        <p style="margin:0 0 22px;color:#65746f">Payment is confirmed for order <strong>{{ $order->order_number }}</strong>.</p>
        <table role="presentation" style="width:100%;border-collapse:collapse">
            <thead>
                <tr>
                    <th scope="col" style="padding:10px 8px;border-bottom:2px solid #dce8e3;color:#60726b;font-size:11px;text-align:left">ITEM</th>
                    <th scope="col" style="padding:10px 8px;border-bottom:2px solid #dce8e3;color:#60726b;font-size:11px;text-align:center">QTY</th>
                    <th scope="col" style="padding:10px 8px;border-bottom:2px solid #dce8e3;color:#60726b;font-size:11px;text-align:right">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td style="padding:11px 8px;border-bottom:1px solid #edf2ef;color:#263f39">
                        {{ $item->product_name }}
                        @if($item->variant_options)
                            <br><small>{{ collect($item->variant_options)->pluck('value')->implode(', ') }}</small>
                        @endif
                    </td>
                    <td style="padding:11px 8px;border-bottom:1px solid #edf2ef;color:#263f39;text-align:center">{{ $item->qty }}</td>
                    <td style="padding:11px 8px;border-bottom:1px solid #edf2ef;color:#263f39;text-align:right">£{{ number_format((float) $item->line_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="2" style="padding:14px 8px;color:#173d38;text-align:left">Paid total</th>
                    <td style="padding:14px 8px;color:#173d38;font-weight:700;text-align:right">£{{ number_format((float) $order->total, 2) }}</td>
                </tr>
            </tfoot>
        </table>
        <section style="margin-top:22px;padding:16px;border-radius:12px;background:#f5f8f6">
            <strong style="color:#173d38">Delivery address</strong>
            <p style="margin:6px 0 0;color:#53645e">{{ $order->address }}<br>{{ $order->city }}, {{ $order->postal_code }}<br>{{ $order->country }}</p>
        </section>
        <p style="margin:22px 0 0;color:#82908b;font-size:12px">We’ll keep you updated as your order is prepared and shipped.</p>
    </main>
</body>
</html>
