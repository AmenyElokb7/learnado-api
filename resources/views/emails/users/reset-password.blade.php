@component('mail::message')
    # Reset Your Password

    You are receiving this email because we received a password reset request for your account. Please set your password by clicking the button below.

    Thanks,<br>

    @component('mail::button', ['url' => $resetUrl])
        Reset Password
    @endcomponent

@endcomponent
