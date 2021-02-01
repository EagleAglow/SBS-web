@component('mail::message')
Hello **{{$name}}**,  {{-- use double space for line break or <br> --}}
You have completed bidding.  Your schedule is shown below.  {{-- use double space for line break --}}
Regards,  {{-- use double space for line break --}}
Schedule Bid System  {{-- use double space for line break --}}
<br><span style="color:red">**Admin:** Email is working.
<br>NOTE: If you receive this, the bidder did not!
<br>You can change this configuration at "Settings".</span>

Schedule: {{ $title }}
<br>Group: {{ $line_group_name }}
<br>Line: {{ $line_number }}
<br>Note: {{ $comment }}

@component('mail::table')
| Day | Date                          | Code | On    | Off   |
|:---:|:------------------------------|:----:|:-----:|:-----:|
| 1   | Thursday, March 4, 2020       |{{$day_01_code}}|{{$day_01_on}}|{{$day_01_off}}|
| 2   | Wednesday, September 24, 2020 |{{$day_02_code}}|{{$day_02_on}}|{{$day_02_off}}|
| 3   | Friday, June 3, 2020          |{{$day_03_code}}|{{$day_03_on}}|{{$day_03_off}}|
| 4   | Friday, June 3, 2020          |{{$day_04_code}}|{{$day_04_on}}|{{$day_04_off}}|
@endcomponent

@endcomponent
