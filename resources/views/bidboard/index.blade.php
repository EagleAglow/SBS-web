@extends('layouts.app')

@section('content')

@php
    // modeled after /bidboard/index, for logged in users
    // this page can not provide a reserve process, because we don't know who is viewing it...
    //   this means viewer may or may not be a bidder, and if bidder, be the the active bidder
    //   also, any bid group membership is unknown

    // get total days in cycle
    $max_days = $schedule->cycle_days;
    // days to display
    $delta = '7';
    if (!isset($page)){ $page = 1; }
    // cycles
    $cycles = $schedule->cycle_count;
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

    $param_name_or_taken = App\Param::where('param_name','name-or-taken')->first()->string_value;

    // debugging aid
    if (!isset($trap)){
        $trap = '?';
    }

    if (!isset($page)){
        $page = 1;
    }

@endphp


<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-12">
			<div class="card shadow">
{{-- 
                <div class="card-header">
                    <div class="row" style="color:red;font-size:0.85rem;margin-left:0.5rem;">
                      DEBUGGING - REMOVE LATER - my_selection=> {{$my_selection }} / next_selection=> {{$next_selection}} / show_all=> {{$show_all}} / trap=> {{$trap}}
                    </div>
                </div>
--}}




                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            {{ __('Schedule: ') }}{{ $schedule->title }}
                        </div>
                    </div>
                </div>

                @include('flash::message')
                
                <div class="card-body my-squash">
                    <div class="table-responsive-md">
                        <table class="table table-striped">
                            <thead><div class="pagination-text"></div>
                            <tr>
                            <th class="text-center btn-shift" scope="col">


                            </thead>

                            <tbody>
                                <div class = "container">
                                @foreach($schedule_lines as $schedule_line)
                                    <tr>
                                        <td class="text-center">
                                            @php
                                            // handle blackout
                                            if ($schedule_line->blackout == 1){
                                                echo '<div class="blackout"><span class="blackout"></span><span class="line-number">';
                                            } else {
                                                echo '<div><span class="line-number">';
                                            }
                                            // line_group
                                            $line_group_code = App\LineGroup::where('id',$schedule_line->line_group_id)->get()->first()->code
                                            @endphp
                                            {{ $schedule_line->line }}</span>
                                            <span class="line-group">{{  $line_group_code  }}</span>
                                            @php
                                            // comment
                                            $comment = $schedule_line->comment; 
                                            if ($schedule_line->nexus == 1){
                                                $comment = $comment . ', NEXUS';
                                            }
                                            if ($schedule_line->barge == 1){
                                                $comment = $comment . ', BARGE';
                                            }
                                            if ($schedule_line->offsite == 1){
                                                $comment = $comment . ', OFFSITE';
                                            }
                                            echo '<span class="line-comment">' . $comment . '</span>';
                                            @endphp

                                            @if( (!$schedule->approved == 1) or ($schedule_line->blackout == 1) )
                                                    <!-- no approved schedule, line id tagged "black out"  -->
                                                    <span style="font-size:0.7rem;text-align:center;margin-top:-0.3rem;color:red;"><b>NOT AVAILABLE</b></span>
                                                @else
                                                    @if(isset($schedule_line->user_id))
                                                        <!-- has been bid already -->
                                                        <span style="font-size:0.7rem;text-align:center;margin-top:-0.3rem;color:red;"><b>TAKEN</b></span>
                                                    @endif
                                                @endif


                                            </div>
                                        </td>

                                        <td>
                                            <div class="row">
                                                <div style="margin-left:auto;margin-right:auto;">
                                                <form action="{{ url('bidboard/line', $schedule_line->id ) }}" method="GET">
                                                    <input type="hidden" name="id" value="{{ $schedule_line->id }}">
                                                    <input type="hidden" name="line_group_code" value="{{ $line_group_code }}">
                                                    <input type="hidden" name="first_day" value="{{ $first_day }}">
                                                    <input type="hidden" name="page" value="{{ $page }}">
                                                    <input type="hidden" name="my_selection" value="{{ $my_selection }}">
                                                    <input type="hidden" name="next_selection" value="{{ $next_selection }}">
                                                    <input type="hidden" name="show_all" value={{$show_all}}>
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary btn-my-edit float-right">Zoom</button>
                                                </form>

                                                </div>

                                            </div>
                                        </td>
                                    </tr>
                                    
                                    @endforeach
                                </div>

                                @php
                                    // things to include with pagination 
                                    $params = array( 'first_day'=>$first_day,'my_selection'=>$my_selection,'next_selection'=>$next_selection,'show_all'=>$show_all);
                                @endphp
                                {{ $schedule_lines->appends($params)->links() }}    

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
