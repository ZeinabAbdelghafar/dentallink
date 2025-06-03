@component('mail::message')
# {{ $text }}

Hello {{ $user->username }},

{{ $text }}

@component('mail::button', ['url' => $url])
Verify Email
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent