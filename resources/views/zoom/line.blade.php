@extends('layouts.app')

@section('content')

@include('flash::message')

<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-9">
			<div class="card mt-7 shadow">
                <div class="card-body">
                    <div class="card-body my-squash">
                        Schedule Title: <b>{{$schedule->title}}</b><br>
                        Schedule Line/Group: &nbsp;<b><span style="color:red;">
                        {{$schedule_line->line}} &nbsp;&nbsp;{{ $line_group->code }}</span></b>&nbsp;&nbsp; ({{$line_group->name}})
                        @php
                            $note = 'Note: ';
                            $note = $note . $schedule_line->comment;
                            if ($schedule_line->nexus==1){ $note = $note . ', NEXUS'; }
                            if ($schedule_line->barge==1){ $note = $note . ', Barge'; }
                            if ($schedule_line->offsite==1){ $note = $note . ', Offsite'; }
                            if ($schedule_line->blackout==1){ $note = $note . ', Blackout (This line can not be bid.)'; }
                            if ($note == 'Note: '){ $note = 'Note: None';}
                        @endphp
                    </div>
                    <div class="card-body my-squash">{{$note}}</div>

                    <div class="card-body my-squash">
                    <table class="table compact">
                        <thead>
                            <tr>
                            <th class="text-center compact" scope="col">Day</th>
                            <th class="text-center compact" scope="col">Weekday</th>
                            <th class="text-center compact" scope="col">Date(s)</th>
                            <th class="text-center compact" scope="col">Shift</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $stamp = strtotime( $schedule->start );  // starting date, numeric

                            for ($n = 1; $n <= $schedule->cycle_days; $n++) {
                                $shift = App\ShiftCode::find($schedule_line->getCodeOfDay($schedule_line->id,$n));

                                $day = date('D', $stamp);
                                echo '<tr><td class="text-center compact">' . $n . '</td>';
                                echo '<td class="text-center compact">' . $day . '</td>';

                                $nextstamp = strtotime( date( 'Y/m/d', $stamp ) . "+1 days");
                                for ($c = 1; $c <= $schedule->cycle_count; $c++){
                                    $d = date('M j', $stamp);
                                    if ($c == 1){
                                        $calendar = $d;
                                    } else {
                                        $calendar = $calendar . ', ' . $d;
                                    }
                                    $stamp = strtotime( date( 'Y/m/d', $stamp ) . "+" . $schedule->cycle_days . " days");
                                }
                                echo '<td class="text-center compact">' . $calendar . '</td>';

                                if ($shift->name=='<<>>'){
                                    $cwt = 'Missing Data';
                                } else {
                                    if ($shift->name=='----'){ $cwt = 'Day Off'; } else {
                                    $cwt = $shift->name . '  (' . $shift->begin_short . ' - ' . $shift->end_short . ')';
                                    }
                                }
                                echo '<td class="text-center compact">' . $cwt . '</td></tr>';
                                $stamp = $nextstamp;
                            }
                            @endphp
                        </tbody>
                    </table>
                    </div>
                    <div class="card-body my-squash">


                    <form action="{{ url('users/scheduleshow' , $schedule->id ) }}" method="GET">
                        <input type="hidden" name="first_day" value="{{ $first_day }}">
                        <input type="hidden" name="last_day" value="{{ $last_day }}">
                        <input type="hidden" name="page" value="{{ $page }}">
                        <input type="hidden" name="trap" value="{{ $trap }}">
                        <input type="hidden" name="schedule" value="{{ $schedule }}">
                        <input type="hidden" name="line_group" value="{{ $line_group }}">
                        <input type="hidden" name="list_codes" value="{{ $list_codes }}">
                        <input type="hidden" name="my_selection" value="{{ $my_selection }}">
                        <input type="hidden" name="next_selection" value="{{ $next_selection }}">
                        <input type="hidden" name="show_all" value={{$show_all}}>
                        @csrf
                        <button type="submit" class="btn btn-primary btn-my-edit float-right">Back</button>
                    </form>
                    </div>
                </div>
			</div>
        </div>
	</div>
</div>

@endsection
