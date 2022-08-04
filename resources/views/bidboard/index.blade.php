@extends('layouts.app')

@section('content')

@php
    // modeled after /schedulelineset/index, but visible to anyone
    // this page can not provide a reserve process, because we don't know who is viewing it...
    //   this means viewer may or may not be a bidder, and if bidder, be the the active bidder
    //   also, any bid group membership is unknown

    // get total days in cycle
    if (!isset($max_days)){  $max_days = App\Schedule::where('id',$schedule_id)->first()->cycle_days;  }
    // days to display
    $delta = '7';
    if (!isset($page)){ $page = 1; }
    if (isset($cycles)){
        if (($cycles <= 0 ) || ( 5 <= $cycles )){
            $cycles = 1;
        }
    } else { $cycles = 1; }
    // first day - default to first block
    if (!isset($first_day)){
        $first_day = 1; 
    } else {
        // sanity check
        if ( $first_day <= 0 ){ 
            $first_day = 1; 
        }
        if ( ($first_day + $delta) > $max_days ){ 
            $first_day = $max_days -$delta +1;                                               
        }
    }
    $last_day = $first_day + $delta - 1;
@endphp

    <div class="container">
	<div class="row justify-content-center">
		<div class="col-md-12">
			<div class="card shadow">

{{--
                <div class="card-header">
                    <div class="row" style="color:red;font-size:0.85rem;margin-left:0.5rem;">DEBUGGING - REMOVE LATER - my_selection=> {{$my_selection }} / next_selection=> {{$next_selection}} /  trap=> {{$trap}}
                    </div>
                </div>
--}}

                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            {{ __('Schedule: ') }}{{ $schedule_title }}
                        </div>
{{--
                        button labels depend on values of: $my_selection, $next_selection, $show_all
                        if $my_selection = $next_selection, then only one line group is available - don't show button
--}}                            

                        @if( !($my_selection == $next_selection) )
                            <div class="col">
                                    <div>
                                    <form action="{{ route('bidboard.show', $schedule_id) }}" method="GET">
                                        <input type="hidden" name="first_day" value="{{ $first_day }}">
                                        <input type="hidden" name="page" value="1">
                                        <input type="hidden" name="my_selection" value="{{ $my_selection }}">
                                        <input type="hidden" name="next_selection" value="{{ $next_selection }}">
                                        <input type="hidden" name="go_next" value="yes">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-shift float-right" style="margin-right:1rem;">
                                            @foreach($list_codes as $list_code)
                                                @if($my_selection == $list_code)
                                                    <span style="border:1px solid white; border-radius:0.15rem; padding:0 0.4rem;">{{ $list_code }}</span>
                                                @else
                                                    <span style="padding:0 0.4rem;">{{ $list_code }}</span>
                                                @endif
                                            @endforeach
                                            @if($my_selection == 'all')
                                                <span style="border:1px solid white; border-radius:0.15rem; padding:0 0.4rem;">Combined</span>
                                            @else
                                                <span style="padding:0 0.4rem;">Combined</span>
                                            @endif
                                        </button>
                                    </form>
                                    </div>
                            </div>
                        @endif
                    </div>
                </div>

                @include('flash::message')
                @isset($schedule_lines)

                <div class="card-body my-squash">
                    <div class="table-responsive-md">
                        <table class="table table-striped">
                            <thead><div class="pagination-text"></div>
                            <tr>
                            <th class="text-left btn-shift" scope="col">Group</th>
                            <th class="text-left btn-shift" scope="col">Line</th>
                            <th class="text-left btn-shift" scope="col">Comment</th>
                            <th class="text-left btn-shift" scope="col">Status</th>
                            <th class="text-right btn-shift" scope="col">Detail</th>
                            </tr>
                            </thead>

                            <tbody>
                                <div class = "container">
                                @foreach($schedule_lines as $schedule_line)
                                    <tr>
                                        <td class="text-center">
                                            <span class="line-group">{{ App\LineGroup::where('id',$schedule_line->line_group_id)->get()->first()->code }}</span>
                                        </td>
                                        <td>
                                            <span class="line-number">{{ $schedule_line->line }}</span>
                                            </td>
                                        <td>
                                            @php
                                            $note = '';
                                            if (strlen($schedule_line->comment) > 0){ $note = $note . $schedule_line->comment . ', '; }
                                            if ($schedule_line->nexus==1){ $note = $note . 'NEXUS, '; }
                                            if ($schedule_line->barge==1){ $note = $note . 'Barge, '; }
                                            if ($schedule_line->offsite==1){ $note = $note . 'Offsite, '; }
                                            if ($schedule_line->blackout==1){ $note = $note . 'Blackout (This line can not be bid.), '; }
                                            if ( substr( strrev($note),0,2 ) == ' ,' ){$note = substr($note,0,strlen($note)-2);}
                                            echo '<span class="line-group">' . $note . '</span>';
                                            @endphp
                                        </td>
                                        <td>
                                            @if( ($schedule_line->blackout == 1) )
                                                    <!-- no approved schedule, line id tagged "black out"  -->
                                                    <span style="font-size:0.7rem;text-align:center;margin-top:-0.3rem;color:red;"><b>NOT AVAILABLE</b></span>
                                                @else
                                                    @if(isset($schedule_line->user_id))
                                                        <!-- has been bid already -->
                                                        <span style="font-size:0.7rem;text-align:center;margin-top:-0.3rem;color:red;"><b>TAKEN</b></span>
                                                    @else
                                                        <span style="font-size:0.9rem;text-align:center;margin-top:-0.3rem;color:green;"><b>OPEN</b></span>
                                                    @endif
                                                @endif

                                        </td>




                                        <td>
                                                <div style="margin-left:auto;margin-right:auto;">
                                                <form action="{{ url('bidboard/line', $schedule_line->id ) }}" method="GET">
                                                    <input type="hidden" name="id" value="{{ $schedule_line->id }}">
                                                    <input type="hidden" name="first_day" value="{{ $first_day }}">
                                                    <input type="hidden" name="page" value="{{ $page }}">
                                                    <input type="hidden" name="my_selection" value="{{ $my_selection }}">
                                                    <input type="hidden" name="next_selection" value="{{ $next_selection }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary btn-my-edit float-right">Zoom</button>
                                                </form>


                                            </div>
                                        </td>
                                    </tr>
                                    
                                    @endforeach
                                </div>

                                @php
                                    // things to include with pagination 
                                    $params = array('schedule_id'=>$schedule_id, 'schedule_title'=>$schedule_title, 'start_date'=>$start_date,
                                        'cycles'=>$cycles, 'first_day'=>$first_day, 'max_days'=>$max_days, 'my_selection'=>$my_selection,
                                        'next_selection'=>$next_selection  );
                                @endphp
                                {{$schedule_lines->appends($params)->links() }}    
                            </tbody>
                        </table>
                    </div>
                </div>
                @else
                    <div class="card-body my-squash">Schedules lines not found!</div>
                    </div>
                @endisset
            </div>
        </div>
    </div>
</div>

@endsection