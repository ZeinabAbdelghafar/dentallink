@component('mail::message')
# {{ $subject ?? 'Email Verification' }}

Dear {{ $user->username }},

{{ $text }}

@component('mail::button', ['url' => $url])
Verify Email
@endcomponent

If you did not create an account or request this verification, please disregard this email.

Best regards,  
{{ config('app.name') }}
@endcomponent
