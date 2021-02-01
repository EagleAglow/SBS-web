@component('mail::message')
Hello **{{$name}}**,  {{-- use double space for line break or <br> --}}
You have completed bidding.  Here is your schedule (not complete, need data here).  {{-- use double space for line break --}}
Regards,  {{-- use double space for line break --}}
Schedule Bid System  {{-- use double space for line break --}}
<br><span style="color:red">**Admin:** Email is working.
<br>NOTE: If you receive this, the bidder did not!
<br>You can change this configuration at "Settings".</span>

@endcomponent