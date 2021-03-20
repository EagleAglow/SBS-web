@extends('layouts.app')


@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header">Supervisor Dashboard</div>

                    @include('flash::message')

                    @php
                    // get bidding-state: none, ready (to begin, next bidder is no. 1), running, paused, complete (after last bidder)
                    $state_param = App\Param::where('param_name','bidding-state')->first();
                    $next_param = App\Param::where('param_name','bidding-next')->first();
                    if($state_param->string_value == 'running'){
                        $state = 'Bidding is in ACTIVE progress.';
                        if(isset($next_param->integer_value)){
                            $state = $state . ' &#9724; Next Bidder Order: ' . $next_param->integer_value;
                        }
                    } else {
                        if($state_param->string_value == 'paused'){
                            $state = 'Bidding is paused.';
                            if(isset($next_param->integer_value)){
                                $state = $state . ' &#9724; Next Bidder Order: ' . $next_param->integer_value;
                            }
                        } else {
                            if($state_param->string_value == 'complete'){
                                $state = 'Bidding is complete.';
                            } else {
                                if($state_param->string_value == 'ready'){
                                    $state = 'Bidding is ready to begin, but NOT active.';
                                    if(isset($next_param->integer_value)){
                                        $state = $state . ' &#9724; Next Bidder Order: ' . $next_param->integer_value;
                                    }
                                } else {
                                    $state = 'Bidding is not ready.';
                                }
                            }
                        }
                    }

                    @endphp

                    @if( count(App\User::role('bidder-active')->get('id')) > 0 )
                        @php
                            $who = App\User::role('bidder-active')->get('name')->first();


                        @endphp
                        <div class="card-body">Select a schedule to review.<br>You can <b>bid for</b> the <b>active bidder</b>: 
                            <span style="color:red;"><b> {{ $who->name }} </b></span>
                        <br> {!! $state !!}
                        </div>
                    @else
                        <div class="card-body">There are no active bidders<br>  {!! $state !!}
                        </div>
                    @endif
                    @php
                        $schedules = App\Schedule::get(); //Get all 
                        if ($schedules->isEmpty($schedules)){
                            echo '<div class="card-body my-squash">Currently, no there are no schedules in the database.</div>';
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
@endsection



