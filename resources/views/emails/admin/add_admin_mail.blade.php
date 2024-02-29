<x-mail::message>
# Account Activation {{ $name }}

Click on the link below to activate your account.

<a href="{{ env('ADMIN_URL').'/activate/'.$token }}">Activate Account</a>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
