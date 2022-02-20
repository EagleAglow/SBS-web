@extends('layouts.app')


@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header OT">Overtime Dashboard</div>

                    @include('flash::message')

                    @php
                    // get OT-call-state: none, ready (to begin, next to call is no. 1), running, paused, complete (no more to call)
                    $state_param = App\Param::where('param_name','OT-call-state')->first();
                    // get OT-call-next and OT-message
                    $next_param = App\Param::where('param_name','OT-call-next')->first();
                    $msg_param = App\Param::where('param_name','OT-message')->first();

                    if($state_param->string_value == 'running'){
                        $state = 'Call list is in ACTIVE progress.';
                        if(isset($next_param->integer_value)){
                            $state = $state . ' &#9724; Next To Call: ' . $next_param->integer_value;
                        }
                    } else {
                        if($state_param->string_value == 'paused'){
                            $state = 'Call list is paused.';
                            if(isset($next_param->integer_value)){
                                $state = $state . ' &#9724; Next To Call: ' . $next_param->integer_value;
                            }
                        } else {
                            if($state_param->string_value == 'complete'){
                                $state = 'Call list is complete.';
                            } else {
                                if($state_param->string_value == 'ready'){
                                    $state = 'Call list is ready to begin, but NOT active.';
                                    if(isset($next_param->integer_value)){
                                        $state = $state . ' &#9724; Next To Call: ' . $next_param->integer_value;
                                    }
                                } else {
                                    $state = 'Call list is not ready.';
                                }
                            }
                        }
                    }

                    @endphp

                    <div class="card-body">Put some instructions and maybe buttons here...<br>  {!! $state !!}
                        <br>Message: {!! $msg_param->string_value !!} 
                    </div>

                    @php
                        $extras = App\Extra::get(); //Get all 
                        if ($extras->isEmpty($extras)){
                            echo '<div class="card-body my-squash">Currently, the call list is empty.</div>';
                        } 
                    @endphp
                    @if (!$extras->isEmpty($extras))
                        <div class="card-body my-squash">
                            <table class="table">
                                <thead>
                                    <tr>
                                    <th class="text-center" scope="col">Name</th>
                                    <th class="text-center" scope="col">Email</th>
                                    <th class="text-center" scope="col">Text</th>
                                    <th class="text-center" scope="col">Voice</th>
                                    <th class="text-center" scope="col">Button</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($extras as $extra)
                                        <tr>
                                            <td class="text-center">{{ $extra->name }}</td>
                                            <td class="text-center">{{ $extra->name }}</td>
                                            <td class="text-center">{{ $extra->name }}</td>
                                            <td class="text-center">{{ $extra->name }}</td>
                                            <td>
                                                <div style="margin-left:auto;margin-right:auto;">
                                                    <a href="{{ url('#' ) }}"><button type="button" class="btn btn-primary btn-my-edit pull-left">Notify</button></a>

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



