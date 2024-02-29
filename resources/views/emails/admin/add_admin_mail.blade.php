<x-mail::message>
# Account Activation {{ $name }}

Click on the link below to activate your account.

<x-mail::button :url="'{{ env('ADMIN_URL').'/admin/activate/'.$token }}'">
Button Text
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
