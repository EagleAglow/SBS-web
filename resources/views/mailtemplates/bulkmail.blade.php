@component('mail::message')
Hello **{{$name}}**,  {{-- use double space for line break --}}

{{$bulkmailmsg}}

Regards,  {{-- use double space for line break --}}
{{$from_name}}  {{-- use double space for line break --}}
@endcomponent