@component('mail::message')
Hello **{{$name}}**,  {{-- use double space for line break --}}
You are a new user of this system,  {{-- use double space for line break --}}
and you should set your initial password. {{-- use double space for line break --}}

@component('mail::button', ['url' => $url, 'token' => $token,])
Set Password
@endcomponent

Regards,  {{-- use double space for line break --}}
{{$from_name}}  {{-- use double space for line break --}}

If you’re having trouble clicking the \"Set Password\" button,
copy and paste the URL below into your web browser:

{{ $url }}
@endcomponent