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



// need to set up reserve process - develop a list of schedule line group ids that are reserved, can not be bid by the active bidder
// use the list to configure buttons for "Bid" and "Bid For" (below line 350)

// is there an active bidder?
// is this user the active bidder (user has role 'bidder-active')
// -or-
// is this user a supervisor (user has role 'supervisor')?
// can the active bidder bid any line group that needs to be reserved, on this bid?
// make the list, use it below

    $need_reserve = false;
    $reserved_line_group_ids = array();  // where we will save these reserved line group ids

    $active_bidder = App\User::role('bidder-active')->first();
    if (isset($active_bidder)){
        // there is an active bidder
        if ($active_bidder->id == Auth::user()->id){
            // this user is active_bidder
            $need_reserve = true;  // might be changed to false, below, if not needed
        }
        if(Auth::user()->hasRole('supervisor')){
            // this user can bid for active user
            $need_reserve = true;  // might be changed to false, below, if not needed
        }
    }
    if ($need_reserve){
        // can the active bidder bid for more than one line group (that is, do they have more than one 'bid-for-' role)?
        $role_names = App\BidderGroup::where('id',$active_bidder->bidder_group_id)->first()->getRoleNames();
        // reduce list to only those roles beginning with 'bid-for-' and collect the line group ids
        $line_group_ids = array();
        foreach($role_names as $role_name){
            if(strpos($role_name,'bid-for-') !== false){
                $line_group_ids[] = App\LineGroup::where('code',strtoupper(str_replace('bid-for-','',$role_name)))->first()->id; 
            }
        }
        if (count($line_group_ids)==1){
            // bidder only has one choice (maybe none, but that's another problem)
            $need_reserve = false;
        }
    }
    if ($need_reserve){
        // bidder would normally have a choice, but we need to see if bidder should be limited for this bid, to ensure others don't become bidless?
        // for each line group code in short list, count remaining lines, 
        // then subtract remaining bidders in bidder groups that can only bid that line group
        // if the result is zero (or less, which indicates a BIG problem), then the active bidder should not bid that line group
        // this limitation may need to apply to multiple line groups, to be a general solution
        $need_reserve = false;
        foreach($line_group_ids as $line_group_id){
            $how_many_lines = count(App\ScheduleLine::where('blackout','!=',1)->where('schedule_id',$schedule->id)->where('line_group_id',$line_group_id)->whereNull('user_id')->get());
            // get a list of bidder groups that can bid ONLY for the line group with this line group id
            // so, get code for this line group, turn that into a 'bid-for-' role, get bidder groups with that role, see if they have other 'bid-for-'' roles
            // if only one role (the one we're checking), subtract the number of remaining bidders in that bidder group

            $critical_role_name = 'bid-for-' . strtolower(App\LineGroup::where('id',$line_group_id)->first()->code);
            $scan_bidder_groups = App\BidderGroup::all();
            foreach($scan_bidder_groups as $scan_bidder_group){
                if ($scan_bidder_group->hasRole($critical_role_name)){
                    // does this bidder group have more than one 'bid-for-' role?
                    // bidder groups only have bidding roles, so we can just count them
                    if ($scan_bidder_group->roles()->count() == 1){
                        // subtract bidders
                        $how_many_lines = $how_many_lines - count(App\User::where('bidder_group_id',$scan_bidder_group->id)->where('has_bid',0)->get());
                    }
                }
            }
            if ($how_many_lines < 1){
                // this line group is critical, should not be able to bid it with this active bidder
                $reserved_line_group_ids[] = $line_group_id;
                $need_reserve = true;
            }
        }
    }
    $reserved = '';
    if ($need_reserve){
        foreach($reserved_line_group_ids as $reserved_line_group_id){
            $reserved = $reserved . App\LineGroup::where('id',$reserved_line_group_id)->first()->code;
        }
    } 

    // debugging aid
    if (!isset($trap)){
        $trap = '?';
    }

@endphp




<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-12">
			<div class="card shadow">
{{--
                <div class="card-header">
                    <div class="row" style="color:red;font-size:0.85rem;margin-left:0.5rem;">DEBUGGING - REMOVE LATER - my_selection=> {{$my_selection }} / next_selection=> {{$next_selection}} / show_all=> {{$show_all}} / trap=> {{$trap}}
                    </div>
                </div>
--}}

                @if ($schedule_lines->isEmpty($schedule_lines))
                    <div class="card-header">No Schedule Lines.</div>
                    @if(Auth::user()->hasRole('supervisor')) 
                        <div class="card-body squash">Nothing to show. Possibly, you are not a bidder, bidding has
                        not started or is complete, no schedule is approved, or schedule is empty, without schedule lines.</div>
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
                                @if($need_reserve)
                                    <h6>Some line groups are reserved: {{ $reserved }}
                                @endif
                            </div>
{{--
                            message and button(s) depend on values of: $my_selection, $next_selection, $show_all
                            if $my_selection = $next_selection, then only one line group is available
--}}                            

                            @if( $my_selection == $next_selection )
                                <div class="col">
                                    <div class="row">
                                        <div style="font-size:0.8rem;font-weight:500;margin-right:1rem;">&nbsp;</div>
                                    </div>
                                </div>

                                <div class="col">
                                    <div class="row text-right">
                                        <div>
                                        <form action="{{ url('users/scheduleshow' , $schedule->id ) }}" method="GET">
                                            <input type="hidden" name="first_day" value="{{ $first_day - $delta }}">
                                            <input type="hidden" name="last_day" value="{{ $last_day - $delta }}">
                                            <input type="hidden" name="page" value="1">
                                            <input type="hidden" name="my_selection" value="{{ $my_selection }}">
                                            <input type="hidden" name="next_selection" value="{{ $next_selection }}">
                                            <input type="hidden" name="go_next" value="no">
                                            <input type="hidden" name="show_all" value="no">
                                            @if ($show_all == 'yes')
                                                <input type="hidden" name="show_all" value="no">
                                            @else
                                                <input type="hidden" name="show_all" value="yes">
                                            @endif
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-shift float-right" style="margin-right:1rem;">
                                                @if ($show_all == 'yes')
                                                    <span style="border:1px solid white; border-radius:0.15rem; padding:0 0.4rem;">Full List</span><br>
                                                    <span style="padding: 0 0.4rem;">Open Lines</span>
                                                @else
                                                    <span style="padding:0 0.4rem;">Full List</span><br>
                                                    <span style="border:1px solid white; border-radius:0.15rem; padding: 0 0.4rem;">Open Lines</span>
                                                @endif    
                                            </button>
                                        </form>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="col">
                                    <div class="row">
                                        <div>
                                        <form action="{{ url('users/scheduleshow' , $schedule->id ) }}" method="GET">
                                            <input type="hidden" name="first_day" value="{{ $first_day - $delta }}">
                                            <input type="hidden" name="last_day" value="{{ $last_day - $delta }}">
                                            <input type="hidden" name="page" value="1">
                                            <input type="hidden" name="my_selection" value="{{ $my_selection }}">
                                            <input type="hidden" name="next_selection" value="{{ $next_selection }}">
                                            <input type="hidden" name="go_next" value="yes">
                                            <input type="hidden" name="show_all" value={{ $show_all }}>
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-shift float-right" style="margin-right:1rem;">
                                                @foreach($list_codes as $list_code)
                                                    @if($my_selection == $list_code)
                                                        <span style="border:1px solid white; border-radius:0.15rem; padding:0 0.4rem;">{{ $list_code }} Lines</span><br>
                                                    @else
                                                        <span style="padding:0 0.4rem;">{{ $list_code }} Lines</span><br>
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
                                    <div class="row text-right">
                                        <div>
                                        <form action="{{ url('users/scheduleshow' , $schedule->id ) }}" method="GET">
                                            <input type="hidden" name="first_day" value="{{ $first_day - $delta }}">
                                            <input type="hidden" name="last_day" value="{{ $last_day - $delta }}">
                                            <input type="hidden" name="page" value="1">
                                            <input type="hidden" name="my_selection" value="{{ $my_selection }}">
                                            <input type="hidden" name="next_selection" value="{{ $next_selection }}">
                                            <input type="hidden" name="go_next" value="no">
                                            @if ($show_all == 'yes')
                                                <input type="hidden" name="show_all" value="no">
                                            @else
                                                <input type="hidden" name="show_all" value="yes">
                                            @endif
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-shift float-right" style="margin-right:1rem;">
                                                @if ($show_all == 'yes')
                                                    <span style="border:1px solid white; border-radius:0.15rem; padding:0 0.4rem;">Full List</span><br>
                                                    <span style="padding: 0 0.4rem;">Open Lines</span>
                                                @else
                                                    <span style="padding:0 0.4rem;">Full List</span><br>
                                                    <span style="border:1px solid white; border-radius:0.15rem; padding: 0 0.4rem;">Open Lines</span>
                                                @endif    
                                            </button>
                                        </form>
                                        </div>
                                    </div>
                                </div>
                            @endif

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
                                    <input type="hidden" name="my_selection" value="{{ $my_selection }}">
                                    <input type="hidden" name="next_selection" value="{{ $next_selection }}">
                                    <input type="hidden" name="show_all" value={{$show_all}}>
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
                                    <input type="hidden" name="my_selection" value="{{ $my_selection }}">
                                    <input type="hidden" name="next_selection" value="{{ $next_selection }}">
                                    <input type="hidden" name="show_all" value={{$show_all}}>
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
                                                                    <!-- user is a bidder -->
                                                                    @if(in_array($schedule_line->line_group_id,$reserved_line_group_ids))
                                                                        <a href="#"><button type="button" disabled="disabled" class="btn btn-outline-primary btn-my-edit float-right">None</button></a>
                                                                        <br><div style="font-size:0.5rem;text-align:center;margin-top:-0.3rem;color:red;"><b>RESERVED</b></div>
                                                                    @else
                                                                        <a href="{{ url('/bidder/bid', $schedule_line->id) }}"><button type="button" class="btn btn-primary btn-my-edit float-right">Bid</button></a>
                                                                    @endif
                                                                    @if(count(App\Pick::where('user_id',Auth::user()->id)->where('schedule_line_id',$schedule_line->id)->get()) > 0)
                                                                        <div style="font-size:0.85rem;text-align:right;color:#ff0000;">&#9733;
                                                                        {{ App\Pick::where('user_id',Auth::user()->id)->where('schedule_line_id',$schedule_line->id)->get('rank')->first()->rank }}
                                                                        </div>
                                                                    @endif
                                                                @else
                                                                    @if(Auth::user()->hasRole('supervisor'))
                                                                        <!-- user is a supervisor --> 
                                                                        @if(in_array($schedule_line->line_group_id,$reserved_line_group_ids))
                                                                            <a href="#"><button type="button" disabled="disabled" class="btn btn-outline-primary btn-my-edit float-right">None</button></a>
                                                                            <br><div style="font-size:0.5rem;text-align:center;margin-top:-0.3rem;color:red;"><b>RESERVED</b></div>
                                                                        @else
                                                                            <a href="{{ url('/supervisor/bidfor', $schedule_line->id) }}"><button type="button" class="btn btn-primary btn-my-edit float-right">Bid For</button></a>
                                                                        @endif
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
                                                                                <input type="hidden" name="my_selection" value="{{ $my_selection }}">
                                                                                <input type="hidden" name="next_selection" value="{{ $next_selection }}">
                                                                                <input type="hidden" name="show_all" value={{$show_all}}>
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
                                                                                <input type="hidden" name="my_selection" value="{{ $my_selection }}">
                                                                                <input type="hidden" name="next_selection" value="{{ $next_selection }}">
                                                                                <input type="hidden" name="show_all" value={{$show_all}}>
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
                                                                                    <input type="hidden" name="my_selection" value="{{ $my_selection }}">
                                                                                    <input type="hidden" name="next_selection" value="{{ $next_selection }}">
                                                                                    <input type="hidden" name="show_all" value={{$show_all}}>
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
                                                                        <input type="hidden" name="my_selection" value="{{ $my_selection }}">
                                                                        <input type="hidden" name="next_selection" value="{{ $next_selection }}">
                                                                        <input type="hidden" name="show_all" value={{$show_all}}>
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
                                                                        <input type="hidden" name="my_selection" value="{{ $my_selection }}">
                                                                        <input type="hidden" name="next_selection" value="{{ $next_selection }}">
                                                                        <input type="hidden" name="show_all" value={{$show_all}}>
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
                                                                            <input type="hidden" name="my_selection" value="{{ $my_selection }}">
                                                                            <input type="hidden" name="next_selection" value="{{ $next_selection }}">
                                                                            <input type="hidden" name="show_all" value={{$show_all}}>
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
                                                                    <input type="hidden" name="my_selection" value="{{ $my_selection }}">
                                                                    <input type="hidden" name="next_selection" value="{{ $next_selection }}">
                                                                    <input type="hidden" name="show_all" value={{$show_all}}>
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
                                                                    <input type="hidden" name="my_selection" value="{{ $my_selection }}">
                                                                    <input type="hidden" name="next_selection" value="{{ $next_selection }}">
                                                                    <input type="hidden" name="show_all" value={{$show_all}}>
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
                                                                        <input type="hidden" name="my_selection" value="{{ $my_selection }}">
                                                                        <input type="hidden" name="next_selection" value="{{ $next_selection }}">
                                                                        <input type="hidden" name="show_all" value={{$show_all}}>
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
                                        $params = array( 'first_day'=>$first_day,'last_day'=>$last_day,'my_selection'=>$my_selection,'next_selection'=>$next_selection,'show_all'=>$show_all);
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
