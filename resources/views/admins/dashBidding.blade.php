@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card shadow">
                <div class="card-header">Admin - Manage Bidding</div>

                @include('flash::message')

                @php
                    // ---------------------------------- Bidding
                    // get bidding state: 
                    //     none (bidders may not have bid order numbers yet)
                    //     ready (to begin, next bidder is lowest bid order, not flagged snapshot or deferred) 
                    //     running, paused
                    //     complete (after last bidder, not flagged snapshot or deferred) 
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
                        $next_name = 'CURRENT BIDDER NOT YET SET!';
                    } else {
                        $next_bidder_in_order = App\User::where('bid_order',$bidding_next)->get();
                        if (count($next_bidder_in_order) == 0){
                            $next_name = 'CURRENT BIDDER NOT FOUND!';
                        } else {
                            $next_name = $next_bidder_in_order->first()->name;
                            if (!isset($next_name)){
                                $next_name = 'CURRENT BIDDER HAS NO NAME!';
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
                                $state = $state . '<br> Current: ' . $next_name . ' (Order: ' . $bidding_next . ')';
                            }
                        } else {
                            if($bidding_state_param == 'paused'){
                                $state = 'Paused';
                                if(isset($bidding_next)){
                                    $state = $state . ' <br> Current: ' . $next_name . ' (Order: ' . $bidding_next . ')';
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
                                                $state = $state . ' <br> Current: ' . $next_name . ' (Order: ' . $bidding_next . ')';
                                            } else {
                                                $state = $state . ' <br> Current: <span style="color:red;">Unexpected Error: No Value For Current Bidder</span>';
                                            }
                                        } else {
                                            // state is none of: running, paused, complete, ready, reported
                                            if ($active == 0){
                                                $state = 'Not Ready &#9724; No Active Schedule';
                                            } else {
                                                $state = 'Not Ready &#9724; Schedule Is Active';
                                            }
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
                            $bidder = 'Active: ' . $items->first()->name;

                            if (isset($items->first()->bid_order)){
                            $bidder = $bidder . ' (Order: ' . $items->first()->bid_order . ')';
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
                    echo '<br>' . $state . ' <br> ' . $bidder;
                    
                    // collect mirror bidders
                    $mirror_list = 'Mirror Bidders: ';
                    $mirrors = App\User::role('flag-mirror')->get();
                    if ($mirrors->count(0) == 0){
                        $mirror_list = $mirror_list . 'None';
                    } else {
                        foreach ($mirrors as $mirror){
                            $mirror_list = $mirror_list . $mirror->name . ', ';
                        }
                        $mirror_list = substr_replace($mirror_list ,"",-2);
                    }

                    // collect snapshot bidders
                    $snapshot_list = 'Snapshot Bidders: ';
                    $snapshots = App\User::role('flag-snapshot')->get();
                    if ($snapshots->count() == 0){
                        $snapshot_list = $snapshot_list . 'None';
                    } else {
                        foreach ($snapshots as $snapshot){
                            $snapshot_list = $snapshot_list . $snapshot->name . ', ';
                        }
                        $snapshot_list = substr_replace($snapshot_list ,"",-2);
                    }
                    echo '<br>' . $mirror_list . '<br>' . $snapshot_list . '</div>';

                    // see if all users have consistent bidding groups and bidding roles
                    $problem_count = 0;
                    $bidder_count = 0;
                    $first = true;
                    $bidder_roles = DB::table('roles')->where('name','like', 'bid-for-%')->get('name');
                    $users = App\User::all()->sortBy('name');
                    foreach($users as $user){
                        // build user/bidder role list
                        $user_roles = $user->roles;
                        $user_bidrole_list = array();
                        foreach($user_roles as $user_role){
                            if ( str_starts_with($user_role->name,'bid-for-') ){
                                array_push($user_bidrole_list, $user_role->name);
                            }
                        }

                        // does user have a bidding group?
                        $bg = $user->bidder_group;
                        if (!isset($bg)){
                            $bidder_count = $bidder_count +1;
                            if($first == true){
                                $msg = '&nbsp;&nbsp;&nbsp;' . $user->name . ' has no bidding group';
                                $first = false;
                            } else {
                                $msg = $msg . '<br>&nbsp;&nbsp;&nbsp;' . $user->name . ' has no bidding group';
                            }
                        } else {
                            // has bidding group - crosscheck bg roles against user roles, except for NONE
                            if ($bg->code == 'NONE'){
                                // user should have no bidder roles
                                if (!( Auth::user()->roles->where('name','like', 'bid-for-%')->count() == 0 )){
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
                                foreach($bidder_roles as $bidder_role){
                                    if ($user->hasRole($bidder_role->name)){ $u_has_role = 1; } else { $u_has_role = 0; }
                                    if ($bg->hasRole($bidder_role->name)){ $bg_has_role = 1; } else { $bg_has_role = 0; }
                                    if ( $u_has_role <> $bg_has_role ){
                                        // mismatch
                                        $bidder_count = $bidder_count +1;
                                        if($first == true){
                                            $msg = '&nbsp;&nbsp;&nbsp;' . $user->name . ' has bidding role/group mismatch';
                                            $first = false;
                                        } else {
                                            $msg = $msg . '<br>&nbsp;&nbsp;&nbsp;' . $user->name . ' has bidding role/group mismatch';
                                        }
                                        break;  // only mismatch once...
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
                            if ( str_starts_with($user_role->name,'bid-for-') ){
                                $is_bidder = true;
                                break;
                            }
                        }
                        if ($is_bidder){
                            if(!isset($user->seniority_date)){
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
                    $users = DB::table('users')
                        ->join('bidder_groups','users.bidder_group_id', '=', 'bidder_groups.id')
                        ->orderBy('bidder_groups.order')->orderBy('users.seniority_date')->orderBy('users.bidder_tie_breaker')
                        ->select('users.id', 'users.name', 'bidder_groups.code', 'bidder_group_id', 'seniority_date', 'bidder_tie_breaker')->get();
                    $bidder_count = 0;
                    $first = true;
                    foreach($users as $user){
                        $u = App\User::find($user->id);
                        // see if this user has a bidder role
                        $u_roles = $u->roles;
                        $is_bidder = false;
                        foreach($u_roles as $u_role){
                            if ( str_starts_with($u_role->name,'bid-for-') ){
                                $is_bidder = true;
                                break;
                            }
                        }

                        if ($is_bidder){
                            if(isset($user->seniority_date)){
                                if( count(App\User::where('bidder_group_id',$user->bidder_group_id)->where('seniority_date',$user->seniority_date)->where('bidder_tie_breaker',$user->bidder_tie_breaker)->get()) > 1){
                                    $bidder_count = $bidder_count +1;
                                    if($first == true){
                                        $msg = '&nbsp;&nbsp;&nbsp;' . $user->name . ' (' . $user->code . '.' . $user->seniority_date . '.' . $user->bidder_tie_breaker . ')';
                                        $first = false;
                                    } else {
                                        $msg = $msg . '<br>&nbsp;&nbsp;&nbsp;' . $user->name . ' (' . $user->code . '.' . $user->seniority_date . '.' . $user->bidder_tie_breaker . ')';
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

                    // if no problem, set actual bid_order according to: bidding group order, seniority date and tie-breaker
                    // if there is a problem, it gets fixed with the other issues by "Fix"
                    // sortBy doesn't seem to work for this, with Users model?
                    // collection returned by DB does not have role/permission link
                    // so, a combination of both
                    if($bidder_count == 0){
                        // this produces the right order
                        $users = DB::table('users')
                            ->join('bidder_groups','users.bidder_group_id', '=', 'bidder_groups.id')
                            ->orderBy('bidder_groups.order')->orderBy('users.seniority_date')->orderBy('users.bidder_tie_breaker')
                            ->select('users.id')->get();

                        $bidder_count = 1;
                        foreach($users as $user){
                            $u = App\User::find($user->id);
                            // see if this user has a bidder role
                            $u_roles = $u->roles;
                            $is_bidder = false;
                            foreach($u_roles as $u_role){
                                if ( str_starts_with($u_role->name,'bid-for-') ){
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
                                <span><a href="{{ url('admins/reset') }}" onclick="if(confirm('This deletes any bids, any mirrored lines and any snapshots.\n\nAre you sure you want to RESET BIDDING?')){$('#cover-spin').show(0);return true;} else {return false;}" class="btn btn-danger">Reset</a></span>
                            @else
                                @if($bidding_state_param == 'complete')
                                    <span><a href="{{url('admins/export-excel-file-bids/xlsx')}}" class="btn btn-primary">Excel Report</a></span> &nbsp; &nbsp;
                                @else
                                    @if($bidding_state_param == 'reported')
                                        <span><a href="{{url('admins/export-excel-file-bids/xlsx')}}" class="btn btn-primary">Excel Report</a></span> &nbsp; &nbsp;
                                        <span><a href="{{ url('admins/reset') }}" onclick="if(confirm('This deletes any bids, any mirrored lines and any snapshots.\n\nAre you sure you want to RESET BIDDING?')){$('#cover-spin').show(0);return true;} else {return false;}" class="btn btn-danger">Reset</a></span>
                                    @else
                                        @if($bidding_state_param == 'ready')
                                            @if($active == 0)
                                                <b>Make a schedule active to begin the bidding process...</b>
                                            @else
                                                <span><a href="{{ url('admins/start') }}" class="btn btn-primary" onclick="$('#cover-spin').show(0)">Start</a></span> &nbsp; &nbsp;
                                                <span><a href="{{ url('admins/tieclear') }}" onclick="if(confirm('All tie-breaker values will be erased.\nTo get new random tie-breaker values, run FIX.\nAre you sure you want to CLEAR SENIORITY TIES?')){$('#cover-spin').show(0);return true;} else {return false;}" class="btn btn-danger">Clear Seniority Ties</a></span>
                                            @endif
                                        @else
                                            <span><a href="{{ url('admins/reset') }}" onclick="if(confirm('This deletes any bids, any mirrored lines and any snapshots.\n\nAre you sure you want to RESET BIDDING?')){$('#cover-spin').show(0);return true;} else {return false;}" class="btn btn-danger">Reset To Ready</a></span>
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