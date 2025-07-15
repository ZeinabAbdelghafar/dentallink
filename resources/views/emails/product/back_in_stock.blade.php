@component('mail::message')
# Good news!

**{{ $product->title }}** is back in stock!

@component('mail::button', ['url' => $url])
View Product
@endcomponent

Hurry — stock may be limited.

Thanks,<br>
{{ config('app.name') }}
@endcomponent