<x-mail::message>
# Account Rejection Notice
Dear {{ $user->first_name }},

Your account has been rejected due to some reasons. Please contact our support team for further assistance.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
