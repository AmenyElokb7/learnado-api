@component('mail::message')
# Hello {{ $user->name }}

We have received your support request:

**Subject:** {{ $message->subject }}

"{{ $message->message }}"
We are taking it under consideration. Thank you for reaching out to us.


Thanks,<br>
{{ config('app.name') }}
@endcomponent
