<x-mail::message>
# Password Reset Mail

{{ $name }}
<br>
Your Password Reset PIN is {{ $pin }}


Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
