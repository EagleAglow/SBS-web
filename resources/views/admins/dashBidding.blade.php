@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card shadow">
                <div class="card-header">Admin Dashboard - Manage Bidding</div>

                @include('flash::message')

                @php
                    // ---------------------------------- Bidding
                    // get bidding state: 
                    //     none (bidders may not have bid order numbers yet)
                    //     ready (to begin, next bidder is no. 1)
                    //     running, paused
                    //     complete (after last bidder, next bidder is next number)
                    //     reported, supposedly - NOTE: there is no good way to confirm download was saved!
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

                    if ($bidding_next == 0){
                        $next_name = 'NEXT BIDDER NOT YET SET!';
                    } else {
                        $next_bidder_in_order = App\User::where('bid_order',$bidding_next)->get();
                        if (count($next_bidder_in_order) == 0){
                            $next_name = 'NEXT BIDDER NOT FOUND!';
                        } else {
                            $next_name = $next_bidder_in_order->first()->name;
                            if (!isset($next_name)){
                                $next_name = 'NEXT BIDDER HAS NO NAME!';
                            } 
                        }
                    }

                    $active = App\Schedule::where('active', 1)->count();
                    if ($active == 0){
                        $state = 'Not Ready &#9724; No Active Schedule';
                    } else {
                        if($bidding_state_param == 'running'){
                            $state = '<b><span style="color:red;">In Progress</span></b>';
                            if(isset($bidding_next)){
                                $state = $state . ' &#9724; Next Bidder: ' . $bidding_next . ' (' .$next_name . ')';
                            }
                        } else {
                            if($bidding_state_param == 'paused'){
                                $state = 'Paused';
                                if(isset($bidding_next)){
                                    $state = $state . ' &#9724; Next Bidder: ' . $bidding_next . ' (' .$next_name . ')';
                                }
                            } else {
                                if($bidding_state_param == 'complete'){
                                    $state = 'Complete, Not Yet Reported';
                                } else {
                                    if($bidding_state_param == 'reported'){
                                        $state = 'Complete And Reported';
                                    } else {
                                        if($bidding_state_param == 'ready'){
                                            $state = 'Ready To Begin';
                                            if(isset($bidding_next)){
                                                if($bidding_next == 1){
                                                    $state = $state . ' &#9724; Next Bidder: ' . $bidding_next . ' (' .$next_name . ')';
                                                } else {
                                                    $state = $state . ' &#9724; Next Bidder: ' . $bidding_next . ' (<span style="color:red;">Unexpected Error: Not 1</span>)';
                                                }
                                            } else {
                                                $state = $state . ' &#9724; Next Bidder: <span style="color:red;">Unexpected Error: No Value For Next Bidder</span>';
                                            }
                                        } else {
                                            // state is none of: running, paused, complete, ready, reported
                                            $state = 'Not Ready' . $bidding_state_param;
                                        }
                                    }
                                }
                            }

                        }
                    }

                    // get active bidder
                    $items = App\User::role('bidder-active')->get();  // should be only one bidder
                    if (count($items) == 0){
                        $bidder = 'No Active Bidder';
                    } else {
                        if (count($items) == 1){
                            $bidder = 'Active Bidder: ' . $items->first()->name;

                            if (isset($items->first()->bid_order)){
                            $bidder = $bidder . ' (Bidder Number: ' . $items->first()->bid_order . ')';
                            } else {
                                $bidder = $bidder . ' <span style="color:red;">(ERROR: Bid order number is missing!)</span>';
                            }
                        } else {
                            $bidder = '<span style="color:red;">ERROR: More than one "Active Bidder"!</span> CHECK:';
                            $first = true;
                            foreach($items as $item){
                                if($first == true){
                                    $bidder = $bidder . ' ' . $item->name . ',';
                                    $first = false;
                                } else {
                                    $bidder = $bidder . ' ' . $item->name;
                                }
                            }
                        }
                    }
                    echo '<div class="card-body my-squish"><b>Bidding</b>';
                    echo '<br>' . $state . ' &#9724; ' . $bidder . '</div>';

                    // see if all users have consistent bidding groups and bidding roles
                    $problem_count = 0;
                    $bidder_count = 0;
                    $first = true;
                    $users = App\User::all()->sortBy('name');
                    foreach($users as $user){
                        // build user/bidder role list
                        $user_roles = $user->roles;
                        $user_bidrole_list = array();
                        foreach($user_roles as $user_role){
                            if ( str_starts_with($user_role->name,'bidder-') ){
                                // skip 'bidder-active'
                                if ($user_role->name != 'bidder-active'){
                                    array_push($user_bidrole_list, $user_role->name);
                                }
                            }
                        }

                        // does user have a bidding group?
                        $bgg = $user->bidder_group;
                        if (isset($bgg)){
                            $bg = $user->bidder_group->code;
                        }
                        if (!isset($bg)){
                            $bidder_count = $bidder_count +1;
                            if($first == true){
                                $msg = '&nbsp;&nbsp;&nbsp;' . $user->name . ' has no bidding group';
                                $first = false;
                            } else {
                                $msg = $msg . '<br>&nbsp;&nbsp;&nbsp;' . $user->name . ' has no bidding group';
                            }
                        } else {
                            // has bidding group - crosscheck against role(s)
                            
                            if ($bg == 'TRAFFIC'){
                                // user should have only two bidder roles 'bidder-tnon' and 'bidder-tcom'
                                if ( !( ((count($user_bidrole_list)) == 2) and (in_array('bidder-tnon', $user_bidrole_list)) and (in_array('bidder-tnon', $user_bidrole_list)) ) ){
                                    // mismatch
                                    $bidder_count = $bidder_count +1;
                                    if($first == true){
                                        $msg = '&nbsp;&nbsp;&nbsp;' . $user->name . ' (TRAFFIC group should only have roles: bidder-tnon and bidder-tcom)';
                                        $first = false;
                                    } else {
                                        $msg = $msg . '<br>&nbsp;&nbsp;&nbsp;' . $user->name . ' (TRAFFIC group should only have roles: bidder-tnon and bidder-tcom)';
                                    }
                                }

                            } else {
                                if ($bg == 'NONE'){
                                    // user should have no bidder roles
                                    if ( !((count($user_bidrole_list)) == 0) ){
                                        // mismatch
                                        $bidder_count = $bidder_count +1;
                                        if($first == true){
                                            $msg = '&nbsp;&nbsp;&nbsp;' . $user->name . ' (NONE group should not have any bidder roles';
                                            $first = false;
                                        } else {
                                            $msg = $msg . '<br>&nbsp;&nbsp;&nbsp;' . $user->name . ' (NONE group should not have any bidder roles';
                                        }
                                    }

                                } else {
                                    // user should only have one bidder role that matches
                                    if ( !( ((count($user_bidrole_list)) == 1) and (in_array( 'bidder-' . strtolower($bg), $user_bidrole_list)) ) ){
                                        // mismatch
                                        $bidder_count = $bidder_count +1;
                                        if($first == true){
                                            $msg = '&nbsp;&nbsp;&nbsp;' . $user->name . ' (' . $bg . ' group should only have role: bidder-' . strtolower($bg) . ')';
                                            $first = false;
                                        } else {
                                            $msg = $msg . '<br>&nbsp;&nbsp;&nbsp;' . $user->name . ' (' . $bg . ' group should only have role: bidder-' . strtolower($bg) . ')';
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if($bidder_count > 0){
                        $problem_count = $problem_count +1;
                        echo '<div class="card-body my-squash2">The following users have some group/role error:';
                        echo '<div style="line-height:0.75rem;font-size:0.75rem;margin:0;">' . $msg . '</div>';
                        echo '</div>';
                    }

                    // see if all bidders have seniority value
                    $bidder_count = 0;
                    $users = App\User::all()->sortBy('name');
                    $first = true;
                    foreach($users as $user){
                        // see if user has a bidder role
                        $user_roles = $user->roles;
                        $is_bidder = false;
                        foreach($user_roles as $user_role){
                            if ( str_starts_with($user_role->name,'bidder-') ){
                                $is_bidder = true;
                                break;
                            }
                        }
                        if ($is_bidder){
                            if(!isset($user->bidder_primary_order)){
                                $bidder_count = $bidder_count +1;
                                if($first == true){
                                    $msg = '&nbsp;&nbsp;&nbsp;' . $user->name;
                                    $first = false;
                                } else {
                                    $msg = $msg . '<br>&nbsp;&nbsp;&nbsp;' . $user->name;
                                }
                            }
                        }
                    }
                    if($bidder_count > 0){
                        $problem_count = $problem_count +1;
                        echo '<div class="card-body my-squash2">The following bidders lack a seniority value:';
                        echo '<div style="line-height:0.75rem;font-size:0.75rem;margin:0;">' . $msg . '</div>';
                        echo '</div>';
                    }

                    // see if seniority and tie-breaker sort has problems
                    $users = DB::table('users')->orderBy('bidder_primary_order')->orderBy('bidder_secondary_order')->get();
                    $bidder_count = 0;
                    $first = true;
                    foreach($users as $user){
                        $u = App\User::find($user->id);
                        // see if this user has a bidder role
                        $u_roles = $u->roles;
                        $is_bidder = false;
                        foreach($u_roles as $u_role){
                            if ( str_starts_with($u_role->name,'bidder-') ){
                                $is_bidder = true;
                                break;
                            }
                        }
                        if ($is_bidder){
                            if(isset($user->bidder_primary_order)){
                                if( count(App\User::where('bidder_primary_order',$user->bidder_primary_order)->where('bidder_secondary_order',$user->bidder_secondary_order)->get()) > 1){
                                    $bidder_count = $bidder_count +1;
                                    if($first == true){
                                        $msg = '&nbsp;&nbsp;&nbsp;' . $user->name . ' (' . $user->bidder_primary_order . '.' . $user->bidder_secondary_order . ')';
                                        $first = false;
                                    } else {
                                        $msg = $msg . '<br>&nbsp;&nbsp;&nbsp;' . $user->name . ' (' . $user->bidder_primary_order . '.' . $user->bidder_secondary_order . ')';
                                    }
                                }
                            }
                        }
                    }
                    if($bidder_count > 0){
                        $problem_count = $problem_count +1;
                        echo '<div class="card-body my-squash2">The following bidders have seniority ties:';
                        echo '<div style="line-height:0.75rem;font-size:0.75rem;margin:0;">' . $msg . '</div>';
                        echo '</div>';
                    }

                    // if no problem, set actual bid_order according to primary and secondary order
                    // if there is a problem, it gets fixed with the other issues by "Fix"
                    // sortBy doesn't seem to work for this, with Users model?
                    // collection returned by DB does not have role/permission link
                    // so, a combination of both
                    if($bidder_count == 0){
                        // this produces the right order
                        $users = DB::table('users')->orderBy('bidder_primary_order')->orderBy('bidder_secondary_order')->get();
                        $bidder_count = 1;
                        foreach($users as $user){
                            $u = App\User::find($user->id);
                            // see if this user has a bidder role
                            $u_roles = $u->roles;
                            $is_bidder = false;
                            foreach($u_roles as $u_role){
                                if ( str_starts_with($u_role->name,'bidder-') ){
                                    $is_bidder = true;
                                    break;
                                }
                            }
                            if ($is_bidder){
                                $u->update(['bid_order' => $bidder_count]);
                                $bidder_count = $bidder_count +1;
                            }
                        }
                    }

                @endphp
                @if($problem_count ==  0)
                    
                    <div class="card-body my-squash">Bidding Order: No Problems</div>
                    <div class="card-body my-squash">
                        @if($bidding_state_param == 'running')
                            <span><a href="{{ url('admins/pause') }}" class="btn btn-primary" onclick="$('#cover-spin').show(0)">Pause</a></span> &nbsp; &nbsp;
                        @else
                            @if($bidding_state_param == 'paused')
                                <span><a href="{{ url('admins/continue') }}" class="btn btn-primary" onclick="$('#cover-spin').show(0)">Continue</a></span> &nbsp; &nbsp;
                                <span><a href="{{ url('admins/reset') }}" onclick="if(confirm('This clears all bids. Are you sure you want to RESET BIDDING?')){$('#cover-spin').show(0);return true;} else {return false;}" class="btn btn-danger">Reset</a></span>
                            @else
                                @if($bidding_state_param == 'complete')
                                    <span><a href="{{url('admins/export-excel-file-bids/xlsx')}}" class="btn btn-primary">Excel Report</a></span> &nbsp; &nbsp;
                                @else
                                    @if($bidding_state_param == 'reported')
                                        <span><a href="{{url('admins/export-excel-file-bids/xlsx')}}" class="btn btn-primary">Excel Report</a></span> &nbsp; &nbsp;
                                        <span><a href="{{ url('admins/reset') }}" onclick="if(confirm('This clears all bids.\n \n Have you saved and printed a report?\n \n Are you sure you want to RESET BIDDING?')){$('#cover-spin').show(0);return true;} else {return false;}" class="btn btn-danger">Reset</a></span>
                                    @else
                                        @if($bidding_state_param == 'ready')
                                            @if($active == 0)
                                                <b>Make a schedule active to begin the bidding process...</b>
                                            @else
                                                <span><a href="{{ url('admins/start') }}" class="btn btn-primary" onclick="$('#cover-spin').show(0)">Start</a></span> &nbsp; &nbsp;
                                            @endif
                                        @else
                                            <span><a href="{{ url('admins/reset') }}" onclick="if(confirm('This clears all bids. Are you sure you want to RESET BIDDING?')){$('#cover-spin').show(0);return true;} else {return false;}" class="btn btn-danger">Reset To Ready</a></span>
                                        @endif
                                    @endif
                                @endif
                            @endif
                        @endif
                    </div>    
                @else
                    <div class="card-body">
                        <a href="{{ url('admins/fix') }}" class="btn btn-primary" onclick="$('#cover-spin').show(0)">Fix</a>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection