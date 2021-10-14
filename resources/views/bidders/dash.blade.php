@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header d-print-none">Bidder Dashboard</div>
 
                    @include('flash::message')

                    @if(Auth::user()->has_bid == true)
                        @php 
                            $schedule_line = App\ScheduleLine::where('user_id',Auth::user()->id)->first();
                            $schedule = App\Schedule::where('id',$schedule_line->schedule_id)->first();
                            $line_group = App\LineGroup::where('id', $schedule_line->line_group_id)->first();
                            $note = 'Note: ';
                            $note = $note . $schedule_line->comment;
                            if ($schedule_line->nexus==1){ $note = $note . ', NEXUS'; }
                            if ($schedule_line->barge==1){ $note = $note . ', Barge'; }
                            if ($schedule_line->offsite==1){ $note = $note . ', Offsite'; }
                            if ($schedule_line->blackout==1){ $note = $note . ', Blackout (This line can not be bid and this text should never appear.)'; }
                            if ($note == 'Note: '){ $note = 'Note: None';}
                        @endphp

                        <div class="card-body">Schedule: {{ $schedule->title }} <br>
                        Bidder:<span style="color:red;"> {{ Auth::user()->name  }} </span>has selected line/group: <span style="color:red;"> {{ $schedule_line->line }} &nbsp; {{ $line_group->code }} </span></b>&nbsp;( {{ $line_group->name }} )<br>{{ $note }} 
                        </div>    

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

                                if ($shift->name=='????'){
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
                    <div class="card-body my-squash d-print-none">
                        <a href="#"><button type="button" class="btn btn-primary btn-my-edit" onclick="window.print();">Print Schedule</button></a>
                        <a href="{{ url('bidders/dash/ics/' . $schedule_line->id ) }}"><button type="button" class="btn btn-primary btn-my-edit float-right">Download Schedule</button></a>
                    </div>
                    <hr class="d-print-none">

                    @endif
                    <div class="d-print-none">
                        
                        @php
                        // get bidding state: 
                        //     none (bidders may not have bid order numbers yet)
                        //     ready (to begin, next bidder is no. 1)
                        //     running, paused
                        //     complete (after last bidder, next bidder is next number)
                        $bidding_state_param = App\Param::where('param_name','bidding-state')->get();
                        if (count($bidding_state_param) == 0){
                            // should not happen - set it to 'none' and try again
                            DB::table('params')->insertOrIgnore([ 'param_name' => 'bidding-state', 'string_value' => 'none', ]);
                        }
                        $bidding_state_param = App\Param::where('param_name','bidding-state')->first()->string_value;

                        // get bid_order for next bidder
                        $bidding_next = App\Param::where('param_name','bidding-next')->get();
                        if (count($bidding_next) == 0){
                            // bidding not in progress - set it to 0 and refresh
                            DB::table('params')->insertOrIgnore([ 'param_name' => 'bidding-next', 'integer_value' => 0, ]);
                        }
                        $bidding_next = App\Param::where('param_name','bidding-next')->first()->integer_value;

                        if($bidding_state_param == 'running'){
                            $state = 'Bidding is ACTIVE.';
                            if(isset($bidding_next)){
                                $state = $state . ' &#9724; Next bidder order number: ' . $bidding_next;
                            }
                        } else {
                            if($bidding_state_param == 'paused'){
                                $state = 'Bidding is paused.';
                                if(isset($bidding_next)){
                                    $state = $state . ' &#9724; Next bidder order number: ' . $bidding_next;
                                }
                            } else {
                                if($bidding_state_param == 'complete'){
                                    $state = 'Bidding is complete.';
                                } else {
                                    if($bidding_state_param == 'ready'){
                                        $state = 'Bidding is ready to begin, but is NOT active.';
                                        if(isset($bidding_next)){
                                            $state = $state . ' &#9724; Next bidder order number: ' . $bidding_next;
                                        }                                    } else {
                                        $state = 'Bidding is not ready. ';
                                    }
                                }
                            }
                        }
                        $you_are = Auth::user()->bid_order;
                        if(isset($you_are)){
                            $you_are = 'You are not the active bidder. &#9724; You are bidder order number: ' . $you_are;
                        } else {
                            $you_are = 'You are not the active bidder.';
                        }

                        @endphp

                        @if( count(App\User::role('bidder-active')->get('id')) > 0 )
                            @if ( Auth::user()->id == App\User::role('bidder-active')->get('id')->first()->id) 
                                <div class="card-body squash">Select a schedule to review.<br>You can <b>tag</b> lines for any <b>approved</b> schedule.<br>
                                The <b>active bidder</b> can <b>bid</b> a line for an <b>active</b> schedule, <b>if bidding is active</b>.<br>
                                <b><span style="color:red;">You are the active bidder, number {{ Auth::user()->bid_order }}.</span></b>
                                <br> {!! $state !!}
                                </div>
                            @else
                                <div class="card-body squash">Select a schedule to review.<br>You can <b>tag</b> lines for any <b>approved</b> schedule.<br>
                                The <b>active bidder</b> can <b>bid</b> a line for an <b>active</b> schedule, <b>if bidding is active</b>.<br>
                                {!! $you_are !!}<br> {!! $state  !!}
                                </div>
                            @endif
                        @else
                            <div class="card-body squash">Select a schedule to review.<br>You can <b>tag</b> lines for any <b>approved</b> schedule.<br>
                            The <b>active bidder</b> can <b>bid</b> a line for an <b>active</b> schedule, <b>if bidding is active</b>.<br>
                            {!! $you_are !!}<br>  {!! $state !!}
                            </div>
                        @endif
                        @php
                            $schedules = App\Schedule::get(); //Get all 
                            if ($schedules->isEmpty($schedules)){
                                echo '<div class="card-body my-squash">There are no schedules in the database.</div>';
                            } 
                        @endphp
                        @if (!$schedules->isEmpty($schedules))
                            <div class="card-body my-squash">
                                <table class="table">
                                    <thead>
                                        <tr>
                                        <th class="text-center" scope="col">Schedule Title</th>
                                        <th class="text-center" scope="col">Start Date</th>
                                        <th class="text-center" scope="col">Approved</th>
                                        <th class="text-center" scope="col">Active</th>
                                        <th class="text-center" scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($schedules as $schedule)
                                            <tr>
                                                <td class="text-center">{{ $schedule->title }}</td>
                                                <td class="text-center">{{ date('d-M-Y', strtotime($schedule->start)) }}</td>
                                                <td class="text-center">
                                                @php
                                                    if ($schedule->approved==1){  echo 'Yes';} else { echo 'No';}
                                                @endphp
                                                </td>
                                                <td class="text-center">
                                                @php
                                                    if ($schedule->active==1){  echo '<b><span style="color:red;">Yes</span></b>';} else { echo 'No';}
                                                @endphp
                                                </td>
                                                <td>
                                                    <div style="margin-left:auto;margin-right:auto;">
                                                        <a href="{{ url('users/scheduleshow/'  .  $schedule->id ) }}"><button type="button" class="btn btn-primary btn-my-edit pull-left">Select</button></a>

                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection



