@component('mail::message')
# Set Your Password
You are receiving this email because an account was created for you. Please set your password by clicking the button below.
If you did not request an account, no further action is required.
Thanks,<br>
@component('mail::button', ['url' => $resetUrl])
Set Password
@endcomponent
@endcomponent
