<x-mail::message>
# Account Activation Mail

{{ $name }}
<br>
Your activation PIN is {{ $pin }}


Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
