@component('mail::message')
# You requested a pin reset token

Use this token to reset your {{ config('app.name') }} dating section pin: <b>{{ $token }}</b><br>

NB: This token will expire in 2 hours, and you will be required to request a new one if you still
want to reset your password.<br>

Ignore this message if you did not request for a pin reset.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
