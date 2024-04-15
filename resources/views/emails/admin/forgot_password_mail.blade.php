<x-mail::message>
# Reset Password {{ $name }}

Your Password Reset PIN is {{ $token }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
