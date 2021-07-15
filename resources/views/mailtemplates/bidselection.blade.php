@component('mail::message')
Hello **{{$name}}**,  {{-- use double space for line break or <br> --}}
You have completed bidding.  Your selection is shown below.  {{-- use double space for line break --}}
The shift schedule is in the attached "ics" file.  {{-- use double space for line break --}}
You can also login to view or print the shift schedule.  {{-- use double space for line break --}}
Please note that the displayed/attached schedule is static, and  {{-- use double space for line break --}}
changes such as shift swaps need to be manually adjusted by user.  {{-- use double space for line break --}}
Regards,  {{-- use double space for line break --}}
{{$from_name}}  {{-- use double space for line break --}}

Schedule: {{ $title }}
<br>Group: {{ $line_group_name }}
<br>Line: {{ $line_number }}
<br>Note: {{ $comment }}
<br><hr> 

@component('mail::button', ['url' => $url,])
Login Here
@endcomponent

If you’re having trouble clicking the \"Login Here\" button,
copy and paste the URL below into your web browser:

{{ $url }}
@endcomponent