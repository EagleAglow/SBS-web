@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header">Admin - Bidders vs. Schedules</div>

                @include('flash::message')

                <div class="card-body"><b>Bidders By Bidder Group</b><br>
                   <span style="font-size:0.8rem;">The table shows the number of bidders for each bidder group, and the line groups for which they can bid.</span>
                </div>
                <div class="card-body my-squash">
                    <table class="table compact">
                        <thead>
                            <tr>
                            @php
                                $groups = App\BidderGroup::where('code','!=','NONE')->orderBy('code')->get();
                                $bidders_by_group = array();
                                $bidder_sum = 0;
                                foreach($groups as $group){
                                    $bidders_by_group[$group->code] = count(App\User::where('bidder_group_id',$group->id)->get());
                                }
                                echo '<th class="text-center compact">Bidder Group</th>';
                                foreach($bidders_by_group as $group_code=>$group_count){
                                    echo '<td class="text-center compact">' . $group_code . '</td>';
                                }
                                echo '<td class="text-center compact">&sum;</td>';
                                echo '</tr></thead><tbody><tr>';
                                echo '<th class="text-center compact">Bidder Count</th>';
                                foreach($bidders_by_group as $group_code=>$group_count){
                                    echo '<td class="text-center compact">' . $group_count . '</td>';
                                    $bidder_sum = $bidder_sum + $group_count;
                                }
                                echo '<td class="text-center compact">' . $bidder_sum . '</td>';
                                echo '</tr><tr>';
//                                echo '<th class="text-left compact">Role(s)</th>';
//                                foreach($bidders_by_group as $group_code=>$group_count){
//                                    echo '<td class="text-center compact">';
//                                    $role_names = App\BidderGroup::where('code',$group_code)->first()->getRoleNames();
//                                    foreach ($role_names as $role_name){
//                                        echo '<div>' . $role_name . '</div>';
//                                    }
//                                    echo '</td>';
//                                }

                                echo '</tr><tr>';
                                echo '<th class="text-center compact">Line Group(s)</th>';
                                foreach($bidders_by_group as $group_code=>$group_count){
                                    echo '<td class="text-center compact">';
                                    $role_names = App\BidderGroup::where('code',$group_code)->first()->getRoleNames();
                                    foreach ($role_names as $role_name){
                                        echo '<div>' . strtoupper(str_replace('bid-for-','',$role_name)) . '</div>';
                                    }
                                }
                                echo '</td><td>&nbsp;</td>';
                            @endphp
                            </tr>
                        </tbody>
                    </table>
                </div>

                <hr>


                <div class="card-body my-squash"><b>Schedule Lines By Line Group For Each System Schedule</b><br>
                   <span style="font-size:0.8rem;"> The table shows the number of lines that can be bid for each line group.</span>
                </div>
                @php
                    // list schedules, if any
                    $schedules = App\Schedule::get(); //Get all 
                    if ($schedules->isEmpty($schedules)){
                        echo '<div class="card-body my-squash">Currently, no there are no schedules in the database.</div>';
                    } else {
                        foreach($schedules as $schedule){
                            echo '<div class="card-body my-squash"><b>Schedule:</b> ' . $schedule->title; 
                            if ($schedule->approved==1){  echo ' &#9724; Approved';} else {echo ' &#9724; Not Approved';}
                            if ($schedule->active==1){  echo ' &#9724; <b><span style="color:red;">Active</span></b>';} else {echo ' &#9724; Not Active';}
                            echo '</div>';

                            echo '<div class="card-body my-squash"><table class="table compact">';
                            echo '<thead><tr>';

                            $groups = App\LineGroup::where('code','!=','NONE')->orderBy('code')->get();
                            $lines_by_group = array();
                            $line_sum = 0;
                            foreach($groups as $group){
                                $lines_by_group[$group->code] = count(App\ScheduleLine::where('blackout','!=',1)->where('schedule_id',$schedule->id)->where('line_group_id',$group->id)->get());
                            }
                            // need a place to put review results (with same group order)
                            $results_by_group = array();
                            foreach($groups as $group){
                                $results_by_group[$group->code] = '<span style="colr:red;">Uncertain</span>';
                            }

                            echo '<th class="text-center compact">Line Group</th>';
                            foreach($lines_by_group as $group_code=>$group_count){
                                echo '<td class="text-center compact">' . $group_code . '</td>';
                            }
                            echo '<td class="text-center compact">&sum;</td>';
                            echo '</tr></thead><tbody><tr>';

//                            foreach($lines_by_group as $group_code=>$group_count){
//                                echo '<td class="text-center compact">bid-for-' . strtolower($group_code) . '</td>';
//                            }
//                            echo '</tr><tr>';

                            echo '<th class="text-center compact">Line Count</th>';
                            foreach($lines_by_group as $group_code=>$group_count){
                                echo '<td class="text-center compact">' . $group_count . '</td>';
                                $line_sum = $line_sum + $group_count;
                            }
                            echo '<td class="text-center compact">' . $line_sum . '</td>';
                            echo '</tr><tr>';

                            // check as much as we can...
                            // check grand total of bidders vs. lines
                            if ($line_sum >= $bidder_sum){
                                $result_for_sum = '<b>OK</b>';
                            } else {
                                $result_for_sum = '<span style="color:red;font-weight:bold;">Problem</span>';
                            }
                            // check line groups that only have one bidder group (and that bidder only has one role)
                            // first, get a list of them...
                            $simple_ones = array();
                            // process line groups
                            foreach($groups as $group){
                                // get code for this line group, turn that into a 'bid-for-' role, get bidder groups with that role, see if they have other 'bid-for-'' roles
                                $simple_code = $group->code;
                                $simple_role_name = 'bid-for-' . strtolower($simple_code);
                                $scan_bidder_groups = App\BidderGroup::all();
                                $count_bidder_groups_for_this_line_group = 0;
                                foreach($scan_bidder_groups as $scan_bidder_group){
                                    if ($scan_bidder_group->hasRole($simple_role_name)){
                                        $count_bidder_groups_for_this_line_group = $count_bidder_groups_for_this_line_group +1;
                                        // capture this bidder group, used if there is only one
                                        $bg = $scan_bidder_group;
                                    }
                                }

                                if ($count_bidder_groups_for_this_line_group == 1){
//                                    $results_by_group[$simple_code] = 'fred';

                                    // does this bidder group have more than one 'bid-for-' role?
                                    // bidder groups only have bidding roles, so we can just count them
                                    if ($bg->roles()->count() == 1){
                                        // add to list
                                        $simple_ones[] = $simple_code;
                                        // do the math and save review result
                                        if ( $lines_by_group[$simple_code] >= count(App\User::where('bidder_group_id',$bg->id)->where('has_bid',0)->get()) ){
                                            $results_by_group[$simple_code] = '<b>OK</b>';
                                        } else {
                                            $results_by_group[$simple_code] = '<span style="color:red;font-weight:bold;">Problem</span>';
                                        }
                                    }

                                }
                                // if only one role (the one we're checking), subtract the number of remaining bidders in that bidder group

                            }

/////   also need to handle line groups with no bidders ////////////////////////////////////////////////////////                            


                            echo '<th class="text-center compact">Review</th>';
                            foreach($results_by_group as $group_code=>$result){
                                echo '<td class="text-center compact">' . $result . '</td>';
                            }
                            echo '<td class="text-center compact">' . $result_for_sum . '</td>';

                            echo '</tr></tbody></table></div>';

                            // begin an atttempt to analyze this mess
                            

                            // check total bidders against lines - stop until that is fixed

                            //check bidder groups that only have one line group - stop until that is fixed
                            // for other groups... ?
                            // remove previous bidder groups and counts from problem

                            // matrix-
                            //        bidders ->   TRAFFIC   BOZOS
                            // line groups
                            //        TNON = 7-1 = 6
                            //        TCOM = 11-5-1=5






                        }
                    }
                @endphp
                </div>
            </div>
        </div>
    </div>
</div>
@endsection