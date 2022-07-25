@extends('layouts.overtime')


@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header OT">Overtime Dashboard - A Work In Progress...</div>

                    @include('flash::message')

                    @php
                    // get OT-call-state: none, ready (to begin, next to call is no. 1), running, paused, complete (no more to call)
                    $state_param = App\Param::where('param_name','OT-call-state')->first();
                    // get OT-call-next and OT-message
                    $next_param = App\Param::where('param_name','OT-call-next')->first();
                    $msg_param = App\Param::where('param_name','OT-message')->first();
                    $msg_ot = $msg_param->string_value;
                    // get cycle length and timer start time (which may be null or not valid, if not running)
                    $time_length = App\Param::where('param_name','OT-cycle-time')->first()->integer_value;
                    $time_unit = App\Param::where('param_name','OT-cycle-time')->first()->string_value;
                    $time_start = App\Param::where('param_name','OT-ref-time')->first()->date_value;


                    if($state_param->string_value == 'running'){
                        $state = 'RUNNING';
                        if(isset($next_param->integer_value)){
                            $state_msg = $state . ' &#9724; Next To Call: ' . $next_param->integer_value;
                        }
                    } else {
                        if($state_param->string_value == 'paused'){
                            $state = 'PAUSED';
                            if(isset($next_param->integer_value)){
                                $state_msg = $state . ' &#9724; Next To Call: ' . $next_param->integer_value;
                            }
                        } else {
                            if($state_param->string_value == 'complete'){
                                $state = 'COMPLETE';
                                $state_msg = 'COMPLETE';
                            } else {
                                if($state_param->string_value == 'ready'){
                                    $state = 'READY';
                                    if(isset($next_param->integer_value)){
                                        $state_msg = $state . ' &#9724; Next To Call: ' . $next_param->integer_value;
                                    }
                                } else {
                                    $state = 'NONE';
                                    $state_msg = 'NOT Ready';
                                }
                            }
                        }
                    }
                    @endphp

                        <div class="card-body">
                        <form method="POST" action="{{ route('supervisors.overtime.setmsg') }}" accept-charset="UTF-8">
                            @csrf
                            @method('POST')
                            <div class="form-group setting-squash row">
                                <label for="msg_ot" class="col-md-2 col-form-label text-md-right">Message
                                    <div style="font-size:0.7rem;">
                                    <span id="available_count"> </span>
                                    <span>Characters Available</span>
                                    </div>
                                </label>

                                <div class="col-md-8 float-left">
                                    <textarea rows="3" id="msg_ot" class="form-control @error('msg_ot') is-invalid @enderror" name="msg_ot" value="{{ old('msg_ot') ? old('msg_ot') : $msg_ot }}" required autocomplete="msg_ot">{!! $msg_ot !!}</textarea>
                                    @error('msg_ot')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $msg_ot }}</strong>
                                    </span>
                                    @enderror
                                </div>

                                <div class="col-md-2">
                                    @if(strlen($msg_ot) > 0)
                                        <input class="btn btn-primary btn-settings float-right" type="submit" value="Clear">
                                        <input type="hidden" name="action" value="clear">
                                    @else
                                        <input class="btn btn-primary btn-settings float-right" type="submit" value="&nbsp;Set&nbsp;">
                                        <input type="hidden" name="action" value="set">
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="card-body my-squash2">
                            
                        <!-- jQuery Script for timer, see: https://stackoverflow.com/questions/20618355/how-to-write-a-countdown-timer-in-javascript -->
                        <script type="text/javascript">
                            function startTimer(delta, reference, display) {
                                var diff,
                                    minutes,
                                    seconds;
                                function timer() {
                                    // get the number of seconds that have elapsed since 
                                    // startTimer() was called
                                    // Note: Date.now is millisec, reference is seconds
                                    //diff = (((Date.now() - reference) / 1000) | 0);
                                    diff = ((Date.now()/1000 - reference) | 0);

                                    // change appearance
                                    if (diff > delta){
                                        display.style.color = "red";
                                    }


                                    // does the same job as parseInt truncates the float
                                    minutes = (diff / 60) | 0;
                                    seconds = (diff % 60) | 0;

//                                    minutes = minutes < 10 ? "0" + minutes : minutes;
                                    seconds = seconds < 10 ? "0" + seconds : seconds;
                                    display.textContent = minutes + ":" + seconds;
                                    
                                };
                                // we don't want to wait a full second before the timer starts
                                timer();
                                setInterval(timer, 1000);

                            }
                        </script>
                    </div>


                    <div class="card-body my-squash">{!! $state !!}


                    @if($state_param->string_value=='running')
                    <a href="{{ url('supervisors/overtime/pause' ) }}"><button type="button" class="btn btn-primary">Pause</button></a>
                    <a href="{{ url('supervisors/overtime/resume' ) }}"><button type="button" class="btn btn-primary">Resume</button></a>
                    <a href="{{ url('supervisors/overtime/reset' ) }}"><button type="button"  onclick="if(confirm('This only resets the call list. It does not change the message.\n\nAre you sure you want to RESET?')){return true;} else {return false;}" class="btn btn-primary">Reset</button></a>

                    <div style="display:inline-block;float:right;"><span id="time" style="font-size:200%;">05:00</span></div>

                    @else
                        @if($state_param->string_value=='paused')
                        <a href="{{ url('supervisors/overtime/resume' ) }}"><button type="button" class="btn btn-primary">Resume</button></a>
                        <a href="{{ url('supervisors/overtime/reset' ) }}"><button type="button"  onclick="if(confirm('This only resets the call list. It does not change the message.\n\nAre you sure you want to RESET?')){return true;} else {return false;}" class="btn btn-primary">Reset</button></a>
                        @else
                            @if($state_param->string_value=='complete')
                            <a href="{{ url('supervisors/overtime/reset' ) }}"><button type="button"  onclick="if(confirm('This only resets the call list. It does not change the message.\n\nAre you sure you want to RESET?')){return true;} else {return false;}" class="btn btn-primary">Reset</button></a>
                            @else
                                @if($state_param->string_value=='ready')
                                <a href="{{ url('supervisors/overtime/start' ) }}"><button type="button" class="btn btn-primary">Start</button></a>
                                <a href="{{ url('supervisors/overtime/reset' ) }}"><button type="button"  onclick="if(confirm('This only resets the call list. It does not change the message.\n\nAre you sure you want to RESET?')){return true;} else {return false;}" class="btn btn-primary">Reset</button></a>
                                @else
                                <a href="{{ url('supervisors/overtime/reset' ) }}"><button type="button"  onclick="if(confirm('This only resets the call list. It does not change the message.\n\nAre you sure you want to RESET?')){return true;} else {return false;}" class="btn btn-primary">Reset</button></a>
                                @endif
                            @endif
                        @endif
                    @endif
                    </div>

                    @php
                        $extras = App\Extra::OrderBy('offered')->get(); //Get all 
                        if ($extras->isEmpty($extras)){
                            echo '<div class="card-body my-squash">Currently, the call list is empty.</div>';
                        } 
                    @endphp
                    @if (!$extras->isEmpty($extras))
                        <div class="card-body my-squash">
                            <table class="table">
                                <thead>
                                    <tr>
                                    <th class="text-center" scope="col">Offered</th>
                                    <th class="text-center" scope="col">Name</th>
                                    <th class="text-center" scope="col">Method</th>
                                    @if ($state_param->string_value == 'running')
                                        <th class="text-center" scope="col">&nbsp;</th>
                                    @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($extras as $extra)
                                        @if (($extra->active == 1) and ($state_param->string_value == 'running'))
                                        <tr id="active_person" style="background-color:#9af089;">
                                            <td class="text-center">{{ $extra->offered }}</td>
                                            <td class="text-center">{{ $extra->name }}</td>
                                            <td class="text-center">
                                                @if ((strlen($extra->text_number) > 0) and (strlen($extra->email) > 0))
                                                    <div style="margin-left:auto;margin-right:auto;">
                                                        <a href="{{ url('#' ) }}"><button type="button" class="btn btn-primary btn-my-edit pull-left">Send Text/Email<br>{{ $extra->text_number }}<br>{{ $extra->email }}</button></a>
                                                    </div>
                                                @else
                                                    @if (strlen($extra->text_number) > 0)
                                                        <div style="margin-left:auto;margin-right:auto;">
                                                            <a href="{{ url('#' ) }}"><button type="button" class="btn btn-primary btn-my-edit pull-left">Send Text<br>{{ $extra->text_number }}</button></a>
                                                        </div>
                                                    @endif
                                                    @if (strlen($extra->email) > 0)
                                                        <div style="margin-left:auto;margin-right:auto;">
                                                            <a href="{{ url('#' ) }}"><button type="button" class="btn btn-primary btn-my-edit pull-left">Send Email<br>{{ $extra->email }}</button></a>
                                                        </div>
                                                    @endif
                                                @endif
                                                @if (strlen($extra->voice_number) > 0)
                                                    <div style="margin-left:auto;margin-right:auto;">
                                                        <a href="{{ url('#' ) }}"><button type="button" class="btn btn-primary btn-my-edit pull-left">Log Phone Call<br>{{ $extra->voice_number }}</button></a>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div style="margin-left:auto;margin-right:auto;">
                                                    <a href="{{ url('#' ) }}"><button type="button" class="btn btn-primary btn-my-edit pull-left">>Next!</button></a>
                                                    <div id="next_active_person" style="display:none;">Click me now...</div>
                                                </div>
                                            </td>
                                        </tr>
                                        @else 
                                        <tr>
                                            <td class="text-center">{{ $extra->offered }}</td>
                                            <td class="text-center">{{ $extra->name }}</td>
                                            <td class="text-center">
                                                <div>
                                                @if (strlen($extra->text_number) > 0)
                                                    <div>Text: {{ $extra->text_number }}</div>
                                                @endif
                                                @if (strlen($extra->email) > 0)
                                                    <div>Email: {{ $extra->email }}</div>
                                                @endif
                                                @if (strlen($extra->voice_number) > 0)
                                                    <div>Phone: {{ $extra->voice_number }}</div>
                                                @endif
                                            </td>
                                        </tr>
                                        @endif
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



