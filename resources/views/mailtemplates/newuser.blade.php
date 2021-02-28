@component('mail::message')
Hello **{{$name}}**,  {{-- use double space for line break --}}
You have just been added to this system, and in order to use it, {{-- use double space for line break --}}
you need to set your password. {{-- use double space for line break --}}

@component('mail::button', ['url' => $url,])
Set Password
@endcomponent

Regards,  {{-- use double space for line break --}}
{{$from_name}}  {{-- use double space for line break --}}

If you’re having trouble clicking the \"Set Password\" button,
copy and paste the URL below into your web browser:

{{ $url }}
@endcomponent