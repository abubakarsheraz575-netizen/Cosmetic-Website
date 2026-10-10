{{ config('app.name', 'Cosmetics') }} — A PERSONAL REPLY

Hello {{ $customerName }},

{{ $reply }}

@if($originalMessage)
YOUR ORIGINAL MESSAGE
{{ $originalMessage }}

@endif
Simply reply to this email if there’s anything else we can do for you.
