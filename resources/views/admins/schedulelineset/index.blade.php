@extends('layouts.app')

@section('content')
    @php
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

                        @if( $my_selection == $next_selection )
                            <div class="col">
                                <div class="row">
                                    <div style="font-size:0.8rem;font-weight:500;margin-right:1rem;">&nbsp;</div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="text-right">
                                    <a href="{{ route('schedulelineset.create', $schedule_id) }}"><button type="button" class="btn btn-success">Add Schedule Line</button></a>
                                </div>
                            </div>
                        @else
                            <div class="col">
                                <div class="row">
                                    <div>
                                    <form action="{{ route('schedulelineset.show', $schedule_id) }}" method="GET">
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
                            </div>
                            <div class="col">
                                <div class="text-right">
                                    <a href="{{ route('schedulelineset.create', $schedule_id) }}"><button type="button" class="btn btn-success">Add Schedule Line</button></a>
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
                            <th class="text-center btn-shift" scope="col">

                            @if ($first_day > 1)
                            <form action="{{ route('schedulelineset.show', $schedule_id) }}" method="GET">
                                <input type="hidden" name="schedule_title" value="{{ $schedule_title }}">
                                <input type="hidden" name="start_date" value="{{ $start_date }}">
                                <input type="hidden" name="cycles" value="{{ $cycles }}">
                                <input type="hidden" name="max_days" value="{{ $max_days }}">
                                <input type="hidden" name="first_day" value="{{ $first_day - $delta }}">
                                <input type="hidden" name="page" value="{{ $page }}">
                                <input type="hidden" name="my_selection" value="{{ $my_selection }}">
                                <input type="hidden" name="next_selection" value="{{ $next_selection }}">
                                @csrf
                                <button type="submit" class="btn btn-secondary btn-shift">&#8656;&nbsp;Earlier</button>
                            </form>
                            @else
                            &nbsp;
                            @endif
                            </th>

                            @php
                                $stamp = strtotime( $start_date );
                                if ($first_day>1){
//                                    $offset = '+' . $first_day . ' days';
                                    $offset = '+' . ($max_days -$delta) . ' days';
                                    $stamp = strtotime( date( 'Y/m/d', $stamp ) . $offset);
                                }

                                for ($d = $first_day; $d <= $last_day; $d++) {
                                    echo '<th class="text-center" scope="col">' . $d . '</th>';

//                                    $day = date('D', $stamp);
//                                    if ($day=='Sat' OR $day=='Sun') {
//                                        echo '<th class="text-center week-end" scope="col">' . $d . 'z</th>';
//                                    } else {
//                                        echo '<th class="text-center" scope="col">' . $d . 'x</th>';
//                                    }
                                    $stamp = strtotime( date( 'Y/m/d', $stamp ) . "+1 days");
                                }

                            @endphp    
                            <th class="text-center btn-shift" scope="col">
                            @if ($last_day < $max_days)
                            <form action="{{ route('schedulelineset.show', $schedule_id) }}" method="GET">
                                <input type="hidden" name="schedule_title" value="{{ $schedule_title }}">
                                <input type="hidden" name="start_date" value="{{ $start_date }}">
                                <input type="hidden" name="cycles" value="{{ $cycles }}">
                                <input type="hidden" name="max_days" value="{{ $max_days }}">
                                <input type="hidden" name="first_day" value="{{ $first_day + $delta }}">
                                <input type="hidden" name="page" value="{{ $page }}">
                                <input type="hidden" name="my_selection" value="{{ $my_selection }}">
                                <input type="hidden" name="next_selection" value="{{ $next_selection }}">
                                @csrf
                                <button type="submit" class="btn btn-secondary">Later&nbsp;&#8658;</button>
                            </form>
                            @else
                            &nbsp;
                            @endif
                            </th>
                            </tr>

                            @php
                                echo '<!-- cycle days  -->';
                                $stamp = strtotime( $start_date );
                                if ($first_day>1){
                                    $offset = '+' . ($first_day -1). ' days';
// really wrong here                  $offset = '+' . ($max_days -$delta) . ' days';
                                    $stamp = strtotime( date( 'Y/m/d', $stamp ) . $offset);
                                }

                                for ($c = 1; $c <= $cycles; $c++){

                                    echo '<tr><th class="text-center month-day-row" scope="col">&nbsp;</th>';

                                    for ($n = $first_day; $n <= $last_day; $n++) {
                                        $day = date('D', $stamp);
                                        $d = date('M j', $stamp);
                                        if ($day=='Sat' OR $day=='Sun') {
                                            echo '<th class="text-center week-end month-day-row" scope="col">' . $day . '<br />' . $d . '</th>';
                                        } else {
                                            echo '<th class="text-center month-day-row" scope="col">' . $day . '<br />' . $d . '</th>';
                                        }
                                        $stamp = strtotime( date( 'Y/m/d', $stamp ) . "+1 days");
                                    }

                                    echo '<th class="text-center month-day-row" scope="col">&nbsp;</th></tr>';
//                                    $offset = '+' . $offset_days . ' days';
                                    $offset = '+' . ($max_days -$delta) . ' days';
                                    $stamp = strtotime( date( 'Y/m/d', $stamp ) . $offset);

                                }


//                                echo '<!-- weekday names  -->';
//                                echo '<tr><th class="text-center" scope="col">Bid Line</th>';

//                                $stamp = strtotime( $start_date );

//                                if ($first_day>1){
//       WRONG                      $offset = '+' . $first_day . ' days';
//                                    $offset = '+' . ($max_days -$delta) . ' days';
//                                    $stamp = strtotime( date( 'Y/m/d', $stamp ) . $offset);
//                                    }
//                                $day = date('D', $stamp);
//
//                                for ($d = $first_day; $d <= $last_day; $d++) {
//                                    if ($day=='Sat' OR $day=='Sun') {
//                                        echo '<th class="text-center week-end" scope="col">' . $day . '</th>';
//                                    } else {
//                                        echo '<th class="text-center" scope="col">' . $day . '</th>';
//                                    }
//                                    $stamp = strtotime( date( 'Y/m/d', $stamp ) . "+1 days");
//                                    $day = date('D', $stamp);
//                                }
//
//                                echo '<th class="text-center" scope="col">Action</th></tr>';
                            @endphp
                            </thead>

                            <tbody>
                                <div class = "container">
                                @foreach($schedule_lines as $schedule_line)
                                    <tr>
                                        <td class="text-center">
                                            @php
                                            // handle blackout
                                            if ($schedule_line->blackout == 1){
                                                echo '<div class="blackout"><span class="blackout">BLACK OUT<br></span><span class="line-number">';
                                            } else {
                                                echo '<div><span class="line-number">';
                                            }
                                            @endphp
                                            {{ $schedule_line->line }}</span>
                                            <span class="line-group">{{ App\LineGroup::where('id',$schedule_line->line_group_id)->get()->first()->code }}</span>

                                            @php
                                            // bidder info 
                                            if (isset($schedule_line->user_id)){
                                                echo '<div class="line-bidder">' . App\User::findOrFail($schedule_line->user_id)->name . '</div>';
                                            }

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
                                            echo '<div class="line-comment">' . $comment . '</div>';
                                            echo '<div class="line-comment">' . $schedule_line->schedule->title . '</div>';
                                            @endphp
                                            </div>
                                        </td>

                                        @php
                                            $stamp = strtotime( $start_date );
                                            if ($first_day>1){
//                                                $offset = '+' . $first_day . ' days';
                                                $offset = '+' . ($max_days -$delta) . ' days';
                                                $stamp = strtotime( date( 'Y/m/d', $stamp ) . $offset);
                                            }

                                            for ($d = $first_day; $d <= $last_day; $d++) {
                                                echo '<td class="text-center line-code" scope="col">';

//                                                $day = date('D', $stamp);
//                                                if ($day=='Sat' OR $day=='Sun') {
//                                                    echo '<td class="text-center line-code week-end" scope="col">';
//                                                } else {
//                                                    echo '<td class="text-center line-code" scope="col">';
//                                                }
                                                echo App\ShiftCode::find($schedule_line->getCodeOfDay($schedule_line->id,$d))->shift_divs . '</td>';

                                                $stamp = strtotime( date( 'Y/m/d', $stamp ) . "+1 days");
                                            }
                                        @endphp
                                        <td>
                                            <div class="row float-right">
                                                <div>
                                                    <form action="{{ route('schedulelineset.edit', $schedule_line->id) }}" method="GET" class="get">
                                                        <input type="hidden" name="_method" value="GET">
                                                        <input type="hidden" name="my_selection" value="{{ $my_selection }}">
                                                        <input type="hidden" name="next_selection" value="{{ $next_selection }}">
                                                        @csrf
                                                        {{ method_field('GET') }}
                                                        <button type="submit" class="btn btn-primary btn-my-edit" style="margin-right:0.5rem;">Edit</button>
                                                    </form>
                                                </div>
                                                <div>
                                                    <form action="{{ route('schedulelineset.ics', $schedule_line->id) }}" method="GET" class="get">
                                                        <input type="hidden" name="_method" value="GET">
                                                        <input type="hidden" name="my_selection" value="{{ $my_selection }}">
                                                        <input type="hidden" name="next_selection" value="{{ $next_selection }}">
                                                        @csrf
                                                        {{ method_field('GET') }}
                                                        <button type="submit" class="btn btn-primary btn-my-edit" style="margin-right:0.5rem;">iCal</button>
                                                    </form>
                                                </div>
                                                <div>
                                                    <form action="{{ route('schedulelineset.clone', $schedule_line->id) }}" method="GET" class="get">
                                                        <input type="hidden" name="_method" value="GET">
                                                        <input type="hidden" name="my_selection" value="{{ $my_selection }}">
                                                        <input type="hidden" name="next_selection" value="{{ $next_selection }}">
                                                        @csrf
                                                        {{ method_field('GET') }}
                                                        <button type="submit" class="btn btn-success btn-my-successt" style="margin-right:0.5rem;">Clone</button>
                                                    </form>
                                                </div>
                                                <div>
                                                    <form action="{{ route('schedulelineset.destroy', $schedule_line->id) }}" method="POST" class="delete">
                                                        <input type="hidden" name="_method" value="DELETE">
                                                        <input type="hidden" name="my_selection" value="{{ $my_selection }}">
                                                        <input type="hidden" name="next_selection" value="{{ $next_selection }}">
                                                        @csrf
                                                        {{ method_field('DELETE') }}
                                                        <button type="submit" onclick="return confirm('Delete Line {{$schedule_line->line}}?')" class="btn btn-danger btn-my-delete" style="margin-right:1rem;">Delete</button>
                                                    </form>
                                                <div>
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