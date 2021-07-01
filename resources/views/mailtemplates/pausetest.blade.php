@component('mail::message')
Hello **{{$name}}**,  {{-- use double space for line break --}}
Bidding is temporarily suspended.  {{-- use double space for line break --}}
You will be notified if/when it continues,  {{-- use double space for line break --}}
or call: {{$app_bid_phone}}.

Regards,  {{-- use double space for line break --}}
{{$from_name}}  {{-- use double space for line break --}}

<span style="color:red;">**Admin:** Email is working.
NOTE: If you receive this, the bidder did not!
You can change this configuration at "Settings".</span>
@endcomponent