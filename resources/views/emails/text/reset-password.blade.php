PASSWORD RESET — {{ config('app.name', 'Cosmetics') }}

Hello {{ $user->name }},

We received a request to reset your password. Use this secure link within {{ $expire }} minutes:
{{ $url }}

If you didn’t request a password reset, you can ignore this email. Your password will remain unchanged.
