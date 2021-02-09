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
                            $next_name = $next_bidder_in_order->first()->name;
                            if (!isset($next_name)){
                                $next_name = 'CURRENT BIDDER HAS NO NAME!';
                            } 
                            // see if we have the following bidder
                            $bidder_next_next = App\User::where('bid_order',$bidding_next +1)->get();
                            if (count($bidder_next_next) == 0){
                                $next_next_name = 'Current Bidder Is Last Bidder';
                            } else {
                                $next_next_name = $bidder_next_next->first()->name;
                                if (isset($next_name)){
                                    $next_next_name = 'Next After Current Bidder: ' . $next_next_name . ' (Order: ' . ($bidding_next +1) . ')';
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
                                $state = $state . ' <br> Current: ' . $next_name . ' (Order: ' . $bidding_next . ')';
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
                                                if($bidding_next == 1){
                                                    $state = $state . ' <br> Current: ' . $next_name . ' (Order: ' . $bidding_next . ')';
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
                                if ($first == true){
                                    $bidder = $bidder . ' ' . $item->name . ',';
                                    $first = false;
                                } else {
                                    $bidder = $bidder . ' ' . $item->name;
                                }
                            }
                        }
                    }
                    echo '<div class="card-body squash">' . $state . '<br>' . $bidder;
                    if (strlen($next_next_name)>0){
                        echo '<br>' . $next_next_name;
                    } else {
                        echo '</div>';
                    }
                @endphp

            </div>
        </div>
    </div>
</div>
@endsection