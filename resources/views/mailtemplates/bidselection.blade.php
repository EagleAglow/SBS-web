@component('mail::message')
Hello **{{$name}}**,  {{-- use double space for line break or <br> --}}
You have completed bidding.  Your schedule is shown below,  {{-- use double space for line break --}}
and it is also in the attached "ics" file.  {{-- use double space for line break --}}
Regards,  {{-- use double space for line break --}}
{{$from_name}}  {{-- use double space for line break --}}
<br>
Schedule: {{ $title }}
<br>Group: {{ $line_group_name }}
<br>Line: {{ $line_number }}
<br>Note: {{ $comment }}
<br><hr> 
@component('mail::table')
| Weekday, Date                             | Code | On    | Off   |
|:------------------------------------------|:----:|:-----:|:-----:|
@foreach ($table_rows as $row_number=>$row_value)
@foreach ($row_value as $v)
|{{ $v['day_text'] }}|{{ $v['code'] }}|{{ $v['on'] }}|{{ $v['off'] }}|
@endforeach
@endforeach
@endcomponent
@endcomponent