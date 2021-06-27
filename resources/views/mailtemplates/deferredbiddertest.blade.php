@component('mail::message')
Hello **{{$name}}**,  {{-- use double space for line break --}}
Your bid time has passed! YOU CAN NOT BID NOW!  {{-- use double space for line break --}}
To re-enter the bidding queue, call: {{$app_bid_phone}}.

Regards,  {{-- use double space for line break --}}
{{$from_name}}  {{-- use double space for line break --}}

<span style="color:red;">**Admin:** Email is working.
NOTE: If you receive this, the bidder did not!
You can change this configuration at "Settings".</span>
@endcomponent