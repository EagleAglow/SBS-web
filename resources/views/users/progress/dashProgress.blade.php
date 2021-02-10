@extends('layouts.scoreboard')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card shadow">
                <div class="card-header">Bidding Progress Scoreboard</div>

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
                    $bidder_next_next = '';
                    $next_next_name = '';

                    if ($bidding_next == 0){
                        $next_name = 'CURRENT BIDDER NOT YET SET!';
                    } else {
                        $next_bidder_in_order = App\User::where('bid_order',$bidding_next)->get();
                        if (count($next_bidder_in_order) == 0){
                            $next_name = 'CURRENT BIDDER NOT FOUND!';
                        } else {
                            $next_bidder_in_order = $next_bidder_in_order->first();
                            $next_phone = $next_bidder_in_order->phone;
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
                                    $next_next_name = 'Next: ' . ($bidding_next +1) . ' - ' . $next_next_name . ' ( ' . $next_next_email . ' ' . $next_next_phone . ' )';
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
                                $state = $state . ' <br> Current: ' . $bidding_next . ' - ' . $next_name . ' ( ' . $next_email . ' ' . $next_phone . ' )';
                            }
                        } else {
                            if($bidding_state_param == 'paused'){
                                $state = 'Paused';
                                if(isset($bidding_next)){
                                    $state = $state . ' <br> Current: ' . $bidding_next . ' - ' . $next_name . ' ( ' . $next_email . ' ' . $next_phone . ' )';
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
                                                    $state = $state . ' <br> Current: ' . $bidding_next . ' - ' . $next_name . ' ( ' . $next_email . ' ' . $next_phone . ' )';
                                                } else {
                                                    $state = $state . ' <br> Current: ' . $next_name . ' (<span style="color:red;">Unexpected Error: Not 1</span>)';
                                                }
                                            } else {
                                                $state = $state . ' <br> Current: <span style="color:red;">Unexpected Error: No Value For Current Bidder</span>';
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
                    echo '<div class="card-body squash">' . $state;
                    if (strlen($next_next_name)>0){
                        echo '<br>' . $next_next_name;
                    } else {
                        echo '</div>';
                    }
                @endphp
                </div>
                <div class="card-body my-squash">
                    <!-- Progress bar HTML -->
                    <div class="progress" onclick="window.location.reload(true);">
                        <div class="progress-bar" style="min-width: 20px;"></div>
                    </div>
                        
                    <!-- jQuery Script - shows progress bar until refresh -->
                    <script type="text/javascript">
                        var i = 30;
                        function makeProgress(){
                            if(i < 200){
                                i = i + 1;
                                $(".progress-bar").css("width", i/2 + "%").text("Refresh...");
                            } else {
                                window.location.reload(true); 
                            }
                            // Wait for sometime before running this script again
                            // 75 increments of 0.8 seconds
                            setTimeout("makeProgress()", 300);
                        }
                        makeProgress();
                    </script>
                </div>
        </div>
    </div>
</div>
@endsection