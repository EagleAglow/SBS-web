@extends('layouts.app')

@section('content')
    @php
        // days to display
        $delta = '7';
        if (!isset($page)){ $page = 1; }
        if (isset($cycles)){
            if (($cycles <= 0 ) || ( 5 <= $cycles )){
                $cycles = 1;
            }
        } else { $cycles = 1; }

        if (isset($first_day)){
            if (($first_day <= 0 ) || ( 57 <= $first_day )){
                $first_day = 1;
            }
        } else { $first_day = 1; }

        if (isset($last_day)){
            if (($last_day <= 9 ) || ( 57 <= $last_day )){
                $last_day = $first_day + $delta - 1;
            }
        } else {
            $last_day = $first_day + $delta - 1;
        }
        
        if ( $last_day > 56 ){
                $last_day = 56;
                $first_day = $last_day - $delta + 1;                                
        }
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
                                        <input type="hidden" name="first_day" value="{{ $first_day - $delta }}">
                                        <input type="hidden" name="last_day" value="{{ $last_day - $delta }}">
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
                                <input type="hidden" name="first_day" value="{{ $first_day - $delta }}">
                                <input type="hidden" name="last_day" value="{{ $last_day - $delta }}">
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
                                    $offset = '+' . ($first_day - 1) . ' days';
                                    $stamp = strtotime( date( 'Y/m/d', $stamp ) . $offset);

                                }

                                for ($d = $first_day; $d <= $last_day; $d++) {
                                    $day = date('D', $stamp);
                                    if ($day=='Sat' OR $day=='Sun') {
                                        echo '<th class="text-center week-end" scope="col">' . $d . '</th>';
                                    } else {
                                        echo '<th class="text-center" scope="col">' . $d . '</th>';
                                    }
                                    $stamp = strtotime( date( 'Y/m/d', $stamp ) . "+1 days");
                                }

                            @endphp    
                            <th class="text-center btn-shift" scope="col">
                            @if ($last_day < 56)
                            <form action="{{ route('schedulelineset.show', $schedule_id) }}" method="GET">
                                <input type="hidden" name="schedule_title" value="{{ $schedule_title }}">
                                <input type="hidden" name="start_date" value="{{ $start_date }}">
                                <input type="hidden" name="cycles" value="{{ $cycles }}">
                                <input type="hidden" name="first_day" value="{{ $first_day + $delta }}">
                                <input type="hidden" name="last_day" value="{{ $last_day + $delta }}">
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
                                echo '<!-- cycle dayes  -->';
                                $stamp = strtotime( $start_date );
                                if ($first_day>1){
                                    $offset = '+' . ($first_day - 1) . ' days';
                                    $stamp = strtotime( date( 'Y/m/d', $stamp ) . $offset);
                                }

                                for ($c = 1; $c <= $cycles; $c++){

                                    echo '<tr><th class="text-center month-day-row" scope="col">&nbsp;</th>';

                                    for ($n = $first_day; $n <= $last_day; $n++) {
                                        $day = date('D', $stamp);
                                        $d = date('M j', $stamp);
                                        if ($day=='Sat' OR $day=='Sun') {
                                            echo '<th class="text-center week-end month-day-row" scope="col">' . $d . '</th>';
                                        } else {
                                            echo '<th class="text-center month-day-row" scope="col">' . $d . '</th>';
                                        }
                                        $stamp = strtotime( date( 'Y/m/d', $stamp ) . "+1 days");
                                    }

                                    echo '<th class="text-center month-day-row" scope="col">&nbsp;</th></tr>';
                                    $offset = '+' . ( 55 + $first_day - $last_day ) . ' days';
                                    $stamp = strtotime( date( 'Y/m/d', $stamp ) . $offset);

                                }

                                echo '<!-- weekday names  -->';
                                echo '<tr><th class="text-center" scope="col">Bid Line</th>';

                                $stamp = strtotime( $start_date );
                                if ($first_day>1){
                                    $offset = '+' . ($first_day - 1) . ' days';
                                    $stamp = strtotime( date( 'Y/m/d', $stamp ) . $offset);
                                    }
                                $day = date('D', $stamp);

                                for ($d = $first_day; $d <= $last_day; $d++) {
                                    if ($day=='Sat' OR $day=='Sun') {
                                        echo '<th class="text-center week-end" scope="col">' . $day . '</th>';
                                    } else {
                                        echo '<th class="text-center" scope="col">' . $day . '</th>';
                                    }
                                    $stamp = strtotime( date( 'Y/m/d', $stamp ) . "+1 days");
                                    $day = date('D', $stamp);
                                }

                                echo '<th class="text-center" scope="col">Action</th></tr>';
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
                                                $offset = '+' . ($first_day - 1) . ' days';
                                                $stamp = strtotime( date( 'Y/m/d', $stamp ) . $offset);
                                            }

                                            for ($d = $first_day; $d <= $last_day; $d++) {
                                                $day = date('D', $stamp);
                                                if ($day=='Sat' OR $day=='Sun') {
                                                    echo '<td class="text-center line-code week-end" scope="col">';
                                                } else {
                                                    echo '<td class="text-center line-code" scope="col">';
                                                }
                                                echo App\ShiftCode::find($schedule_line->getCode($d))->shift_divs . '</td>';

                                                $stamp = strtotime( date( 'Y/m/d', $stamp ) . "+1 days");
                                            }
                                        @endphp
                                        <td>
                                            <div class="row">
                                                <div style="margin-left:auto;margin-right:auto;">

                                                    <a href="{{ route('schedulelineset.edit', $schedule_line->id) }}"><button type="button" class="btn btn-primary btn-my-edit pull-left">Edit</button></a>
                                                </div>
                                                <div style="margin-left:auto;margin-right:auto;">

                                                    <form action="{{ route('schedulelineset.destroy', $schedule_line->id) }}" method="POST" class="delete">
                                                        <input type="hidden" name="_method" value="DELETE">
                                                        <input type="hidden" name="my_selection" value="{{ $my_selection }}">
                                                        <input type="hidden" name="next_selection" value="{{ $next_selection }}">
                                                        @csrf
                                                        {{ method_field('DELETE') }}
                                                        <button type="submit" onclick="return confirm('Delete Line {{$schedule_line->line}}?')" class="btn btn-danger btn-my-delete">Delete</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    
                                    @endforeach
                                </div>

                                @php
                                    // things to include with pagination 
                                    $params = array('schedule_id'=>$schedule_id, 'schedule_title'=>$schedule_title, 'start_date'=>$start_date,
                                        'cycles'=>$cycles, 'first_day'=>$first_day, 'last_day'=>$last_day, 'my_selection'=>$my_selection,
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
