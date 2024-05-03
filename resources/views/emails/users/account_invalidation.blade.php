@component('mail::message')
    # Account Invalidation Notice
    Dear {{ $user->first_name }},

    Your account has been invalidated due to some reasons. Please contact our support team for further assistance.

    Thanks,
    {{ config('app.name') }}
@endcomponent
