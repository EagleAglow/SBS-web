@extends('layouts.app')

@section('title', '| Supervisor Bid For Schedule Line')

@section('content')

<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-9">
			<div class="card mt-7 shadow">
                <div class="card-body">
                    <div class="card-body my-squash">
                        @php
                            if( count(App\User::role('bidder-active')->get('name')) > 0 ){
                                $who = App\User::role('bidder-active')->get('name')->first()->name;
                            } else {
                                $who = 'ERROR: Current bidder not found';
                            }
                        @endphp
                        Supervisor <b><span style = "color:red;">Bidding For: {{$who}}</span></b><br>
                        Schedule Title: <b>{{$schedule->title}}</b><br>
                        Schedule Line/Group: &nbsp;<b><span style="color:red;">
                        {{$schedule_line->line}} &nbsp;&nbsp;{{ $line_group->code }}</span></b>&nbsp;&nbsp; ({{$line_group->name}})
                        @php
                            $note = 'Note: ';
                            $note = $note . $schedule_line->comment;
                            if ($schedule_line->nexus==1){ $note = $note . ', NEXUS'; }
                            if ($schedule_line->barge==1){ $note = $note . ', Barge'; }
                            if ($schedule_line->offsite==1){ $note = $note . ', Offsite'; }
                            if ($schedule_line->blackout==1){ $note = $note . ', Blackout (This line can not be bid and this text should never appear.)'; }
                            if ($note == 'Note: '){ $note = 'Note: None';}
                            // following used for confirmation message
                            $confirm_this = 'This bid assigns Schedule Line: ' . $schedule_line->line . ' ' . $line_group->code . ' (' . $note . ') to: ' . $who;
                        @endphp
                    </div>
                    <div class="card-body my-squash">{{$note}}</div>

                    <div class="card-body my-squash">
                    <table class="table">
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

                            for ($n = 1; $n <= 56; $n++) {
                                $d = 'day_' . substr(('00' . $n),-2);
                                $shift = App\ShiftCode::find($schedule_line->$d);

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
                                    $stamp = strtotime( date( 'Y/m/d', $stamp ) . "+56 days");
                                }
                                echo '<td class="text-center compact">' . $calendar . '</td>';

                                if ($shift->name=='----'){ $cwt = 'Day Off'; } else {
                                    $cwt = $shift->name . '  (' . $shift->begin_short . ' - ' . $shift->end_short . ')';
                                }
                                echo '<td class="text-center compact">' . $cwt . '</td></tr>';
                                $stamp = $nextstamp;
                            }
                            @endphp
                            <tr>
                                <td colspan="4"><b>Bid For: &nbsp;<span style = "color:red;">{{$who}}</span><br>Schedule Line/Group: &nbsp;<b><span style="color:red;">
                                {{$schedule_line->line}} &nbsp;&nbsp;{{ $line_group->code }}</span> &nbsp;&nbsp; ({{$line_group->name}})</b></td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                    <div class="card-body my-squash">
                    <form method="POST" action="{{ route('supervisor.setbidfor', $schedule_line->id) }}" accept-charset="UTF-8">
                        @csrf
                        @method('POST')
                        <input class="btn btn-primary float-right" type="submit" value="Confirm Bid" onclick="if(confirm('{{$confirm_this }} \n\nThis action is NOT reversible! Are you sure you want this line?')){return true;}else{return false;}" >
                    </form>
                    <button type="button" class="btn btn-primary" style="padding: 0.375rem 0.75rem;" onclick="window.location='{{ URL::previous() }}'">Back / Cancel</button>
                    </div>
                </div>
			</div>
        </div>
	</div>
</div>

@endsection
