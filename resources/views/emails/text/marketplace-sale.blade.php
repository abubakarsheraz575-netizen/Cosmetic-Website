Congratulations, {{ $vendorName }}!

A customer payment has been confirmed for order {{ $orderNumber }}.
@foreach($items as $item)
- {{ $item['name'] }} x {{ $item['quantity'] }}
@endforeach

View your paid sales and request payout: {{ $payoutUrl }}

Your payout becomes available after the customer payment is verified. The marketplace team reviews payout requests.
