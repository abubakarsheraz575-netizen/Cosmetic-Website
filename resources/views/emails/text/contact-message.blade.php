NEW WEBSITE INQUIRY — {{ config('app.name', 'Cosmetics') }}

A customer would love to hear from you.

FROM
{{ $contactMessage->name }} <{{ $contactMessage->email }}>

SUBJECT
{{ $contactMessage->subject ?: 'General inquiry' }}

MESSAGE
{{ $contactMessage->message }}

Reply directly to this email to reach the customer.
