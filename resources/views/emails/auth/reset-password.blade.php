@component('mail::message')
# Reset Your Password

Hello {{ $name }},

You are receiving this email because we received a password reset request for your account.

Please click the button below to reset your password:

@component('mail::button', ['url' => $resetUrl])
Reset Password
@endcomponent

This password reset link will expire in {{ $expiresInMinutes }} minutes.

If you did not request a password reset, no further action is required. Your password will remain unchanged.

Thanks,  
{{ config('app.name') }}
@endcomponent
