@component('mail::message')

    # Your Account Has Been Validated
    Hello {{$username}},
    Your account has been successfully validated. You can now access all the features available to validated users.
    Your email: {{ $email }}
    If you did not request this, please contact our support team immediately.

    Thanks,
    {{ config('app.name') }}

@endcomponent

