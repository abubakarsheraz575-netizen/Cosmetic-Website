Thank you for your order, {{ $order->name }}.

Payment is confirmed for order {{ $order->order_number }}.

Items:
@foreach($order->items as $item)
- {{ $item->product_name }}{{ $item->variant_options ? ' ('.collect($item->variant_options)->pluck('value')->implode(', ').')' : '' }} × {{ $item->qty }}: £{{ number_format((float) $item->line_total, 2) }}
@endforeach

Paid total: £{{ number_format((float) $order->total, 2) }}

Delivery address:
{{ $order->address }}
{{ $order->city }}, {{ $order->postal_code }}
{{ $order->country }}
