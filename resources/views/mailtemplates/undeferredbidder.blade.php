@component('mail::message')
Hello **{{$name}}**,  {{-- use double space for line break --}}
You have been re-entered into the bidding queue, and you will be able to bid soon.  {{-- use double space for line break --}}
For information, call: {{$app_bid_phone}}.

Regards,  {{-- use double space for line break --}}
{{$from_name}}  {{-- use double space for line break --}}
@endcomponent