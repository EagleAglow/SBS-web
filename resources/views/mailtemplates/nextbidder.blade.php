@component('mail::message')
Hello **{{$name}}**,  {{-- use double space for line break --}}
You are next, *after the current bidder*.  {{-- use double space for line break --}}
When the current bidder is done, you will {{-- use double space for line break --}}
be notified that it is your turn to bid. {{-- use double space for line break --}}

Regards,  {{-- use double space for line break --}}
{{$from_name}}  {{-- use double space for line break --}}
@endcomponent