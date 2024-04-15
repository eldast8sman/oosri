<x-mail::message>
# Introduction {{ $name }}

@if ($status == 1)
    Your Business, {{ $business_name }} has been verified on Oosri
@else
    The Verfication status of your business, {{ $business_name }}, on Oosri has been revoked
@endif
The body of your message.

<x-mail::button :url="''">
Button Text
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
