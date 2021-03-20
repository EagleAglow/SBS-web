@component('mail::message')
Hello **{{$name}}**,  {{-- use double space for line break --}}
You can now login to bid. You are the active (current) bidder.  {{-- use double space for line break --}}

@component('mail::button', ['url' => $url,])
Login Here
@endcomponent

Regards,  {{-- use double space for line break --}}
{{$from_name}}  {{-- use double space for line break --}}

<span style="color:red;">**Admin:** Email is working.
NOTE: If you receive this, the bidder did not!
You can change this configuration at "Settings".</span>

If you’re having trouble clicking the \"Login Here\" button,
copy and paste the URL below into your web browser:

{{ $url }}
@endcomponent