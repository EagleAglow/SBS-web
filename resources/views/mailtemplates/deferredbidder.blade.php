@component('mail::message')
Hello **{{$name}}**,  {{-- use double space for line break --}}
Your bid time has passed! YOU CAN NOT BID NOW!  {{-- use double space for line break --}}
To re-enter the bidding queue, call: {{$app_bid_phone}}.

Regards,  {{-- use double space for line break --}}
{{$from_name}}  {{-- use double space for line break --}}
@endcomponent