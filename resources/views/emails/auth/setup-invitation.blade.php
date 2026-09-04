@component('mail::message')
# Complete Your Account Setup

Hello {{ $name }},

You have been invited to set up your account. Please click the button below to create your password and access your account.

@component('mail::button', ['url' => $setupUrl])
Complete Account Setup
@endcomponent

If you did not request this invitation, please ignore this email.

Thanks,  
{{ config('app.name') }}
@endcomponent