@extends('layouts.app')

@section('content')

@php
    // days to display
    $delta = '7';

    $cycles = $schedule->cycle_count;
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

    $param_name_or_taken = App\Param::where('param_name','name-or-taken')->first()->string_value;

@endphp

<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-12">
			<div class="card shadow">

                @if ($schedule_lines->isEmpty($schedule_lines))
                    <div class="card-header">No Schedule Lines.</div>
                    @if(Auth::user()->hasRole('supervisor')) 
                        <div class="card-body squash">Bidding has not started or schedule is not approved, or schedule is empty, without schedule lines.</div>
                    @else
                        <div class="card-body squash">Schedule is not approved, or is empty, without schedule lines.</div>
                    @endif
                    @include('flash::message')
                @else
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-6">
                                {{ __('Schedule: ') }}{{ $schedule->title }}
                                @if(Auth::user()->hasRole('supervisor')) 
                                    @if( count(App\User::role('bidder-active')->get('id')) > 0 )
                                        <h6>You can place a bid for:<span style="color:red;"> {{ App\User::role('bidder-active')->get('name')->first()->name }} </span></h6>
                                    @endif
                                @endif
                            </div>
                            <div class="col-md-6">
{{--
                                cycle $my_sort between 'filter', 'tnon', 'tcom', all', but only use 'tnon' and 'tcom' if both are in $list
                                $traffic is 'yes' if both are in sequence
--}}                            
                                @if($traffic == 'yes')
                                    @if($my_sort == 'all')
                                        <div style="text-align:right;font-size:0.8rem;font-weight:500;">Showing all commercial and traffic lines</div>
                                        <div>
                                        <form action="{{ url('users/scheduleshow' , $schedule->id ) }}" method="GET">
                                            <input type="hidden" name="first_day" value="{{ $first_day - $delta }}">
                                            <input type="hidden" name="last_day" value="{{ $last_day - $delta }}">
                                            <input type="hidden" name="page" value="1">
                                            <input type="hidden" name="my_sort" value="tnon">
                                            @csrf
                                            <button type="submit" class="btn btn-secondary btn-shift float-right">&nbsp;Show Traffic Lines You Can Bid</button>
                                        </form>
                                        </div>
                                    @else
                                        @if($my_sort == 'tnon')
                                            <div style="text-align:right;font-size:0.8rem;font-weight:500;">Showing traffic lines you can bid</div>
                                            <div>
                                            <form action="{{ url('users/scheduleshow' , $schedule->id ) }}" method="GET">
                                                <input type="hidden" name="first_day" value="{{ $first_day - $delta }}">
                                                <input type="hidden" name="last_day" value="{{ $last_day - $delta }}">
                                                <input type="hidden" name="page" value="1">
                                                <input type="hidden" name="my_sort" value="tcom">
                                                @csrf
                                                <button type="submit" class="btn btn-secondary btn-shift float-right">&nbsp;Show Commercial Lines You Can Bid</button>
                                            </form>
                                            </div>
                                        @else
                                            @if($my_sort == 'tcom')
                                                <div style="text-align:right;font-size:0.8rem;font-weight:500;">Showing commercial lines you can bid</div>
                                                <div>
                                                <form action="{{ url('users/scheduleshow' , $schedule->id ) }}" method="GET">
                                                    <input type="hidden" name="first_day" value="{{ $first_day - $delta }}">
                                                    <input type="hidden" name="last_day" value="{{ $last_day - $delta }}">
                                                    <input type="hidden" name="page" value="1">
                                                    <input type="hidden" name="my_sort" value="filter">
                                                    @csrf
                                                    <button type="submit" class="btn btn-secondary btn-shift float-right">&nbsp;Show Commercial And Traffic Lines You Can Bid</button>
                                                </form>
                                                </div>
                                            @else
                                                <!-- "filter" -->
                                                <div style="text-align:right;font-size:0.8rem;font-weight:500;">Showing commecial and traffic lines you can bid</div>
                                                <div>
                                                <form action="{{ url('users/scheduleshow' , $schedule->id ) }}" method="GET">
                                                    <input type="hidden" name="first_day" value="{{ $first_day - $delta }}">
                                                    <input type="hidden" name="last_day" value="{{ $last_day - $delta }}">
                                                    <input type="hidden" name="page" value="1">
                                                    <input type="hidden" name="my_sort" value="all">
                                                    @csrf
                                                    <button type="submit" class="btn btn-secondary btn-shift float-right">Show All Commercial And Traffic Lines</button>
                                                </form>
                                                </div>
                                            @endif
                                        @endif
                                    @endif
                                @else
                                    @if($my_sort == 'filter')
                                        <div style="text-align:right;font-size:0.8rem;font-weight:500;">Showing lines you can bid</div>
                                        <div>
                                        <form action="{{ url('users/scheduleshow' , $schedule->id ) }}" method="GET">
                                            <input type="hidden" name="first_day" value="{{ $first_day - $delta }}">
                                            <input type="hidden" name="last_day" value="{{ $last_day - $delta }}">
                                            <input type="hidden" name="page" value="1">
                                            <input type="hidden" name="my_sort" value="all">
                                            @csrf
                                            <button type="submit" class="btn btn-secondary btn-shift float-right">&nbsp;Show All Your Group Lines</button>
                                        </form>
                                        </div>
                                    @else
                                        <div style="text-align:right;font-size:0.8rem;font-weight:500;">Showing all your group lines</div>
                                        <div>
                                        <form action="{{ url('users/scheduleshow' , $schedule->id ) }}" method="GET">
                                            <input type="hidden" name="first_day" value="{{ $first_day - $delta }}">
                                            <input type="hidden" name="last_day" value="{{ $last_day - $delta }}">
                                            <input type="hidden" name="page" value="1">
                                            <input type="hidden" name="my_sort" value="filter">
                                            @csrf
                                            <button type="submit" class="btn btn-secondary btn-shift float-right">Show Lines You Can Bid</button>
                                        </form>
                                        </div>
                                    @endif
                                @endif
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

                                @if ($first_day > 1)
                                <form action="{{ url('users/scheduleshow' , $schedule->id ) }}" method="GET">
                                    <input type="hidden" name="first_day" value="{{ $first_day - $delta }}">
                                    <input type="hidden" name="last_day" value="{{ $last_day - $delta }}">
                                    <input type="hidden" name="page" value="{{ $page }}">
                                    <input type="hidden" name="my_sort" value={{$my_sort}}>
                                    <input type="hidden" name="traffic" value={{$traffic}}>
                                    @csrf
                                    <button type="submit" class="btn btn-secondary btn-shift">&#8656;&nbsp;Earlier</button>
                                </form>
                                @else
                                &nbsp;
                                @endif
                                </th>

                                @php
                                    $stamp = strtotime( $schedule->start );
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
                                <form action="{{ url('users/scheduleshow' , $schedule->id ) }}" method="GET">
                                    <input type="hidden" name="first_day" value="{{ $first_day + $delta }}">
                                    <input type="hidden" name="last_day" value="{{ $last_day + $delta }}">
                                    <input type="hidden" name="page" value="{{ $page }}">
                                    <input type="hidden" name="my_sort" value={{$my_sort}}>
                                    <input type="hidden" name="traffic" value={{$traffic}}>
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
                                    $stamp = strtotime( $schedule->start );
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

                                    $stamp = strtotime( $schedule->start );
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
                                                <span class="line-group">{{  App\LineGroup::where('id',$schedule_line->line_group_id)->get()->first()->code  }}</span>
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
                                                echo '<div class="line-comment">' . $comment . '</div>';

                                                @endphp
                                                </div>

                                            </td>

                                            @php
                                                $stamp = strtotime( $schedule->start );
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

                                                    $day_field_name =  'day_' . substr(('00' . $d),-2);
                                                    $day_shiftcode_id = $schedule_line->$day_field_name;
                                                    echo App\ShiftCode::find($day_shiftcode_id)->shift_divs . '</td>';

                                                    $stamp = strtotime( date( 'Y/m/d', $stamp ) . "+1 days");
                                                }
                                            @endphp
                                            <td>
                                                <div class="row">
                                                    <div style="margin-left:auto;margin-right:auto;">
{{--                                                Modify Button for conditions:  (assumes only approved schedules can be set active)
                                                    show "None" (and modify style) for schedule not approved, line with user_id (already bid) or blackout
                                                    show "Bid" for approved, active schedule, line without user_id, line not blackout, active bidder
                                                    show "Bid For" for approved, active schedule, line without user_id, line not blackout, active bidder, supervisor role
                                                    show "Tag" for approved, active or inactive schedule, line without user_id, line not blackout
--}}
                                                    @if( (!$schedule->approved == 1) or ($schedule_line->blackout == 1) or (isset($schedule_line->user_id)) )
                                                        <!-- no approved schedule, line id tagged "black out" or line already taken -->
                                                        @if(isset($schedule_line->user_id))
                                                            @if($param_name_or_taken == 'taken')
                                                                <a href="#"><button type="button" disabled="disabled" class="btn btn-outline-primary btn-my-edit float-right">None</button></a>
                                                                <br><div style="font-size:0.7rem;text-align:center;margin-top:-0.3rem;color:red;"><b>TAKEN</b></div>
                                                            @else
                                                                <div style="font-size:0.7rem;text-align:center;margin-top:0rem;color:red;"><b>TAKEN</b><br>({{ App\User::where('id',$schedule_line->user_id)->first()->name   }})</div>
                                                            @endif
                                                        @endif
                                                    @else
                                                        @if($schedule->active == 1)
                                                            @if( count(App\User::role('bidder-active')->get('id')) > 0 )
                                                                @if(Auth::user()->id == App\User::role('bidder-active')->get('id')->first()->id)
                                                                    <a href="{{ url('/bidder/bid', $schedule_line->id) }}"><button type="button" class="btn btn-primary btn-my-edit float-right">Bid</button></a>
                                                                    @if(count(App\Pick::where('user_id',Auth::user()->id)->where('schedule_line_id',$schedule_line->id)->get()) > 0)
                                                                        <div style="font-size:0.85rem;text-align:right;color:#ff0000;">&#9733;
                                                                        {{ App\Pick::where('user_id',Auth::user()->id)->where('schedule_line_id',$schedule_line->id)->get('rank')->first()->rank }}
                                                                        </div>
                                                                    @endif
                                                                @else
                                                                    @if(Auth::user()->hasRole('supervisor'))
                                                                        <!-- user is a supervisor --> 
                                                                        <a href="{{ url('/supervisor/bidfor', $schedule_line->id) }}"><button type="button" class="btn btn-primary btn-my-edit float-right">Bid For</button></a>
                                                                        @php
                                                                            // BEWARE - Don't cut/paste, this differs from other similar sections
                                                                            $pick = App\Pick::where('user_id', App\User::role('bidder-active')->get('id')->first()->id )->where('schedule_line_id',$schedule_line->id)->get('rank')->first();
                                                                            if (isset($pick)){
                                                                                $pick = '&#9733; ' . $pick->rank;
                                                                            }
                                                                        @endphp
                                                                        @if(count(App\Pick::where('user_id', App\User::role('bidder-active')->get('id')->first()->id )->where('schedule_line_id',$schedule_line->id)->get()) > 0)
                                                                            <div style="font-size:0.8rem;text-align:right;color:#ff0000;">{!! $pick !!}
                                                                            </div>
                                                                        @endif
                                                                    @else
                                                                        @if(count(App\Pick::where('user_id',Auth::user()->id)->where('schedule_line_id',$schedule_line->id)->get()) == 0)   
                                                                            <form action="{{ url('users/scheduleshow' , $schedule->id ) }}" method="GET">
                                                                                <input type="hidden" name="first_day" value="{{ $first_day }}">
                                                                                <input type="hidden" name="last_day" value="{{ $last_day }}">
                                                                                <input type="hidden" name="page" value="{{ $page }}">
                                                                                <input type="hidden" name="pick" value="tag">
                                                                                <input type="hidden" name="schedule_line_id" value="{{ $schedule_line->id }}">
                                                                                <input type="hidden" name="my_sort" value={{$my_sort}}>
                                                                                <input type="hidden" name="traffic" value={{$traffic}}>
                                                                                @csrf
                                                                                <button type="submit" class="btn btn-primary btn-my-edit float-right">Tag</button>
                                                                            </form>
                                                                        @else
                                                                            <form action="{{ url('users/scheduleshow' , $schedule->id ) }}" method="GET">
                                                                                <input type="hidden" name="first_day" value="{{ $first_day }}">
                                                                                <input type="hidden" name="last_day" value="{{ $last_day }}">
                                                                                <input type="hidden" name="page" value="{{ $page }}">
                                                                                <input type="hidden" name="pick" value="untag">
                                                                                <input type="hidden" name="schedule_line_id" value="{{ $schedule_line->id }}">
                                                                                <input type="hidden" name="my_sort" value={{$my_sort}}>
                                                                                <input type="hidden" name="traffic" value={{$traffic}}>
                                                                                @csrf
                                                                                <button type="submit" class="btn btn-primary btn-my-edit pull-left">Untag</button>
                                                                            </form>
                                                                            @if(count(App\Pick::where('user_id',Auth::user()->id)->where('schedule_line_id',$schedule_line->id)->get()) > 0)
                                                                                <form action="{{ url('users/scheduleshow' , $schedule->id ) }}" method="GET">
                                                                                    <input type="hidden" name="first_day" value="{{ $first_day }}">
                                                                                    <input type="hidden" name="last_day" value="{{ $last_day }}">
                                                                                    <input type="hidden" name="page" value="{{ $page }}">
                                                                                    <input type="hidden" name="pick" value="boost">
                                                                                    <input type="hidden" name="schedule_line_id" value="{{ $schedule_line->id }}">
                                                                                    <input type="hidden" name="my_sort" value={{$my_sort}}>
                                                                                    <input type="hidden" name="traffic" value={{$traffic}}>
                                                                                    @csrf
                                                                                    <button type="submit" class="btn btn-outline-primary btn-my-tag float-right">
                                                                                    {{ App\Pick::where('user_id',Auth::user()->id)->where('schedule_line_id',$schedule_line->id)->get('rank')->first()->rank }}
                                                                                    </button>
                                                                                </form>
                                                                            @endif
                                                                        @endif
                                                                    @endif
                                                                @endif
                                                            @else
                                                                @if(count(App\Pick::where('user_id',Auth::user()->id)->where('schedule_line_id',$schedule_line->id)->get()) == 0)
                                                                    <form action="{{ url('users/scheduleshow' , $schedule->id ) }}" method="GET">
                                                                        <input type="hidden" name="first_day" value="{{ $first_day }}">
                                                                        <input type="hidden" name="last_day" value="{{ $last_day }}">
                                                                        <input type="hidden" name="page" value="{{ $page }}">
                                                                        <input type="hidden" name="pick" value="tag">
                                                                        <input type="hidden" name="schedule_line_id" value="{{ $schedule_line->id }}">
                                                                        <input type="hidden" name="my_sort" value={{$my_sort}}>
                                                                        <input type="hidden" name="traffic" value={{$traffic}}>
                                                                        @csrf
                                                                        <button type="submit" class="btn btn-primary btn-my-edit float-right">Tag</button>
                                                                    </form>
                                                                @else
                                                                    <form action="{{ url('users/scheduleshow' , $schedule->id ) }}" method="GET">
                                                                        <input type="hidden" name="first_day" value="{{ $first_day }}">
                                                                        <input type="hidden" name="last_day" value="{{ $last_day }}">
                                                                        <input type="hidden" name="page" value="{{ $page }}">
                                                                        <input type="hidden" name="pick" value="untag">
                                                                        <input type="hidden" name="schedule_line_id" value="{{ $schedule_line->id }}">
                                                                        <input type="hidden" name="my_sort" value={{$my_sort}}>
                                                                        <input type="hidden" name="traffic" value={{$traffic}}>
                                                                        @csrf
                                                                        <button type="submit" class="btn btn-primary btn-my-edit pull-left">Untag</button>
                                                                    </form>
                                                                    @if(count(App\Pick::where('user_id',Auth::user()->id)->where('schedule_line_id',$schedule_line->id)->get()) > 0)
                                                                        <form action="{{ url('users/scheduleshow' , $schedule->id ) }}" method="GET">
                                                                            <input type="hidden" name="first_day" value="{{ $first_day }}">
                                                                            <input type="hidden" name="last_day" value="{{ $last_day }}">
                                                                            <input type="hidden" name="page" value="{{ $page }}">
                                                                            <input type="hidden" name="pick" value="boost">
                                                                            <input type="hidden" name="schedule_line_id" value="{{ $schedule_line->id }}">
                                                                            <input type="hidden" name="my_sort" value={{$my_sort}}>
                                                                            <input type="hidden" name="traffic" value={{$traffic}}>
                                                                            @csrf
                                                                            <button type="submit" class="btn btn-outline-primary btn-my-tag float-right">
                                                                            {{ App\Pick::where('user_id',Auth::user()->id)->where('schedule_line_id',$schedule_line->id)->get('rank')->first()->rank }}
                                                                            </button>
                                                                        </form>
                                                                    @endif
                                                                @endif
                                                            @endif
                                                        @else
                                                            @if(count(App\Pick::where('user_id',Auth::user()->id)->where('schedule_line_id',$schedule_line->id)->get()) == 0)
                                                                <form action="{{ url('users/scheduleshow' , $schedule->id ) }}" method="GET">
                                                                    <input type="hidden" name="first_day" value="{{ $first_day }}">
                                                                    <input type="hidden" name="last_day" value="{{ $last_day }}">
                                                                    <input type="hidden" name="page" value="{{ $page }}">
                                                                    <input type="hidden" name="pick" value="tag">
                                                                    <input type="hidden" name="schedule_line_id" value="{{ $schedule_line->id }}">
                                                                    <input type="hidden" name="my_sort" value={{$my_sort}}>
                                                                    <input type="hidden" name="traffic" value={{$traffic}}>
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-primary btn-my-edit float-right">Tag</button>
                                                                </form>
                                                            @else
                                                                <form action="{{ url('users/scheduleshow' , $schedule->id ) }}" method="GET">
                                                                    <input type="hidden" name="first_day" value="{{ $first_day }}">
                                                                    <input type="hidden" name="last_day" value="{{ $last_day }}">
                                                                    <input type="hidden" name="page" value="{{ $page }}">
                                                                    <input type="hidden" name="pick" value="untag">
                                                                    <input type="hidden" name="schedule_line_id" value="{{ $schedule_line->id }}">
                                                                    <input type="hidden" name="my_sort" value={{$my_sort}}>
                                                                    <input type="hidden" name="traffic" value={{$traffic}}>
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-primary btn-my-edit pull-left">Untag</button>
                                                                </form>
                                                                @if(count(App\Pick::where('user_id',Auth::user()->id)->where('schedule_line_id',$schedule_line->id)->get()) > 0)
                                                                    <form action="{{ url('users/scheduleshow' , $schedule->id ) }}" method="GET">
                                                                        <input type="hidden" name="first_day" value="{{ $first_day }}">
                                                                        <input type="hidden" name="last_day" value="{{ $last_day }}">
                                                                        <input type="hidden" name="page" value="{{ $page }}">
                                                                        <input type="hidden" name="pick" value="boost">
                                                                        <input type="hidden" name="schedule_line_id" value="{{ $schedule_line->id }}">
                                                                        <input type="hidden" name="my_sort" value={{$my_sort}}>
                                                                        <input type="hidden" name="traffic" value={{$traffic}}>
                                                                        @csrf
                                                                        <button type="submit" class="btn btn-outline-primary btn-my-tag float-right">
                                                                        {{ App\Pick::where('user_id',Auth::user()->id)->where('schedule_line_id',$schedule_line->id)->get('rank')->first()->rank }}
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                            @endif
                                                        @endif
                                                    @endif

                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        
                                        @endforeach
                                    </div>

                                    @php
                                        // things to include with pagination 
                                        $params = array( 'first_day'=>$first_day,'last_day'=>$last_day,'my_sort'=>$my_sort,'traffic'=>$traffic);
                                    @endphp

                                    {{ $schedule_lines->appends($params)->links() }}    
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>


@endsection
