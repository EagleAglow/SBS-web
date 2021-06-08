@extends('layouts.scoreboard')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header">Bidding Progress Scoreboard</div>

                @include('flash::message')

                @php
                    // ---------------------------------- Bidding
                    // get bidding state: 
                    //     none (bidders may not have bid order numbers yet)
                    //     ready (to begin, next bidder is lowest bidding number not "snapshot" or "deferred")
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
                        // bidding not in progress - set it to 0
                        DB::table('params')->insertOrIgnore([ 'param_name' => 'bidding-next', 'integer_value' => 0, ]);
                    }
                    $bidding_next = App\Param::where('param_name','bidding-next')->first();
                    $bidding_next_began = $bidding_next->updated_at;
                    $bidding_next = $bidding_next->integer_value;
                    $bidder_next_next = '';
                    $next_next_name = '';

                    $next_phone = '';
                    if ($bidding_next == 0){
                        $next_name = 'CURRENT BIDDER NOT YET SET!';
                    } else {
                        $next_bidder_in_order = App\User::where('bid_order',$bidding_next)->get();
                        if (count($next_bidder_in_order) == 0){
                            $next_name = 'CURRENT BIDDER NOT FOUND!';
                        } else {
                            $next_bidder_in_order = $next_bidder_in_order->first();
                            $next_phone = $next_bidder_in_order->phone_number;
                            $next_email = $next_bidder_in_order->email;
                            $next_name = $next_bidder_in_order->name;
                            if (!isset($next_name)){
                                $next_name = 'CURRENT BIDDER HAS NO NAME!';
                            } 
                            // see if we have the following bidder
                            $bidder_next_next = App\User::where('bid_order',$bidding_next +1)->get();
                            if (count($bidder_next_next) == 0){
                                $next_next_name = 'Current Bidder Is Last Bidder';
                            } else {
                                $next_next_phone = $bidder_next_next->first()->phone_number;
                                $next_next_email = $bidder_next_next->first()->email;
                                $next_next_name = $bidder_next_next->first()->name;
                                if (isset($next_name)){
                                    $next_next_name = ($bidding_next +1) . ' - ' . $next_next_name . ' ( ' . $next_next_email . ' ' . $next_next_phone . ' )';
                                } else {
                                    $next_next_name = 'BIDDER FOLLOWING CURRENT BIDDER HAS NO NAME!';
                                }
                            }
                        }
                    }

                    $active = App\Schedule::where('active', 1)->count();
                    if ($active == 0){
                        $state = 'Not Ready <br> No Active Schedule';
                    } else {
                        if($bidding_state_param == 'running'){
                            $state = '<b><span style="color:red;">In Progress</span></b>';
                            $state = $state . '<br>Bidders: ' . App\User::select('id')->where('bid_order','>',0)->get()->count();
                            if(isset($bidding_next)){
                                $state = $state . ' <br>Current: ' . $bidding_next . ' - ' . $next_name . ' ( ' . $next_email . ' ' . $next_phone . ' )';
                                $state = $state . ' <br>Bidder ' . $bidding_next . ' has been current bidder for ' . intval( (time() - strtotime($bidding_next_began))/60 ) . ' minutes.';                                 
                                if (strlen($next_next_name)>0){
                                    $state = $state . ' <br>Next: ' . $next_next_name;
                                }
                            } else {
                                echo '</div>';
                            }
                        } else {
                            if($bidding_state_param == 'paused'){
                                $state = 'Paused';
                                if(isset($bidding_next)){
                                    $state = $state . ' <br> Current: ' . $bidding_next . ' - ' . $next_name . ' ( ' . $next_email . ' ' . $next_phone . ' )';
                                    if (strlen($next_next_name)>0){
                                        $state = $state . ' <br>Next: ' . $next_next_name;
                                    }
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
                                                $state = $state . ' <br> Current: ' . $bidding_next . ' - ' . $next_name . ' ( ' . $next_email . ' ' . $next_phone . ' )';
                                                if (strlen($next_next_name)>0){
                                                    $state = $state . ' <br>Next: ' . $next_next_name;
                                                }
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

                    echo '<div class="card-body squash">' . $state;
                    // special bidders - mirror, snapshot, suspended
                    echo '<br>' . $mirror_list;
                    echo '<br>' . $snapshot_list;
                    echo '<br>' . $deferred_list;
                    echo '</div>';
                @endphp

                <div class="card-body my-squash">
                    <!-- Progress bar HTML -->
                    <div class="progress" onclick="window.location.reload(true);">
                        <div class="progress-bar" style="min-width: 20px;"></div>
                    </div>
                        
                    <!-- jQuery Script - shows progress bar until refresh -->
                    <script type="text/javascript">
                        var i = 40;
                        function makeProgress(){
                            if(i < 100){
                                i = i + 1;
                                $(".progress-bar").css("width", i + "%").text("30 Second Refresh Cycle...");
                            } else {
                                window.location.reload(true); 
                            }
                            // Wait for sometime before running this script again
                            // 100-40 => 60 increments of 0.5 seconds => 30 second refresh
                            setTimeout("makeProgress()", 500);
                        }
                        makeProgress();
                    </script>
                </div>


                <div class="card-body my-squash">
                    <table class="table">
                        <thead>
                            <tr>
                            @php
                                $bid_groups = App\BidderGroup::where('code','!=','NONE')->orderBy('code')->get();
                                $bidders_by_group = array();
                                foreach($bid_groups as $group){
                                    $bidders_by_group[$group->code] = count(App\User::where('bidder_group_id',$group->id)->where('has_bid',0)->get());
                                }

                                
                                echo '<th class="text-center">Bidder Group</th>';
                                foreach($bidders_by_group as $group_code=>$group_count){
                                    echo '<td class="text-center">' . $group_code . '</td>';
                                }
                                echo '</tr></thead><tbody><tr>';
                                echo '<th class="text-center">Line Group(s)</th>';
                                foreach($bidders_by_group as $group_code=>$group_count){
                                    echo '<td class="text-center"><span style="color:red;"><b>';
                                    $role_names = App\BidderGroup::where('code',$group_code)->first()->getRoleNames();
                                    foreach ($role_names as $role_name){
                                        echo '<div>' . strtoupper(str_replace('bid-for-','',$role_name)) . '</div>';
                                    }
                                    echo '</b></span></td>';
                                }
                                echo '</tr><tr>';
                                echo '<th class="text-center">Remaining Bidders</th>';
                                foreach($bidders_by_group as $group_code=>$group_count){
                                    echo '<td class="text-center">' . $group_count . '</td>';
                                }

                            @endphp
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="card-body my-squash">
                    <table class="table">
                        <thead>
                            <tr>
                            @php
                                // get active schedule, if any
                                $schedules = App\Schedule::where('active',1)->get(); //Get all 
                                if (!$schedules->isEmpty($schedules)){
                                    $schedule = $schedules->first();  // should only be one active

                                    $line_groups = App\LineGroup::where('code','!=','NONE')->orderBy('code')->get();
                                    $lines_by_group = array();
                                    foreach($line_groups as $group){
                                        $lines_by_group[$group->code] = count(App\ScheduleLine::where('blackout','!=',1)->where('schedule_id',$schedule->id)->where('line_group_id',$group->id)->whereNull('user_id')->get());
                                    }

                                    // build array from both bid and line groups - needs to identify... 
                                    // which bidder groups need to reserve which line groups, and how many lines
                                    


                                    echo '<th class="text-center">Line Group</th>';
                                    foreach($lines_by_group as $group_code=>$group_count){
                                        echo '<td class="text-center"><span style="color:red;"><b>' . $group_code . '</b></span></td>';
                                    }
                                    echo '</tr></thead><tbody><tr>';

                                    echo '<th class="text-center">Remaining Lines</th>';
                                    foreach($lines_by_group as $group_code=>$group_count){
                                        echo '<td class="text-center">' . $group_count . '</td>';
                                    }
                                }

                            @endphp
                            </tr>
                        </tbody>
                    </table>
                </div>



            </div>
        </div>
    </div>
</div>
@endsection