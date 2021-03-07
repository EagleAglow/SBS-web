@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header">Admin - Bidders vs. Schedules</div>

                @include('flash::message')

                <div class="card-body"><b>Bidders By Bidder Group</b><br>
                   <span style="font-size:0.8rem;">The table shows bidder role(s) and number of bidders for each bidder group.
                   A bidder role determines which line group can be bid by a user with that role.</span>
                </div>
                <div class="card-body my-squash">
                    <table class="table compact">
                        <thead>
                            <tr>
                            @php
                                $groups = App\BidderGroup::where('code','!=','NONE')->orderBy('code')->get();
                                $bidders_by_group = array();
                                foreach($groups as $group){
                                    $bidders_by_group[$group->code] = count(App\User::where('bidder_group_id',$group->id)->get());
                                }
                                
                                foreach($bidders_by_group as $group_code=>$group_count){
                                    echo '<th class="text-center compact">' . $group_code . '</th>';
                                }
                                echo '</tr></thead><tbody><tr>';
                                foreach($bidders_by_group as $group_code=>$group_count){
                                    echo '<td class="text-center compact">';
                                    $role_names = App\BidderGroup::where('code',$group_code)->first()->getRoleNames();
                                    foreach ($role_names as $role_name){
                                        echo '<div>' . $role_name . '</div>';
                                    }
                                    echo '</td>';
                                }
                                echo '</tr><tr>';
                                foreach($bidders_by_group as $group_code=>$group_count){
                                    echo '<td class="text-center compact">' . $group_count . '</td>';
                                }
                            @endphp
                            </tr>
                        </tbody>
                    </table>
                </div>

                <hr>


                <div class="card-body my-squash"><b>Schedule Lines By Line Group</b><br>
                   <span style="font-size:0.8rem;"> For each line group, the table shows the bidding role and the number of lines that can be bid.
                   The number of lines with a bidding role needs to be large enough to handle the number of bidders with that role, unless some bidders
                   can bid an alternate role.<span style="color:red;">This program does not check to see if there are enough lines for the number of bidders.</span></span>
                </div>
                @php
                    // list schedules, if any
                    $schedules = App\Schedule::get(); //Get all 
                    if ($schedules->isEmpty($schedules)){
                        echo '<div class="card-body my-squash">Currently, no there are no schedules in the database.</div>';
                    } else {
                        foreach($schedules as $schedule){
                            echo '<div class="card-body my-squash">&nbsp; ' . $schedule->title; 
                            if ($schedule->approved==1){  echo ' &#9724; Approved';} else {echo ' &#9724; Not Approved';}
                            if ($schedule->active==1){  echo ' &#9724; <b><span style="color:red;">Active</span></b>';} else {echo ' &#9724; Not Active';}
                            echo '</div>';

                            echo '<div class="card-body my-squash"><table class="table compact">';
                            echo '<thead><tr>';


                            $groups = App\LineGroup::where('code','!=','NONE')->orderBy('code')->get();
                            $lines_by_group = array();
                            foreach($groups as $group){
                                $lines_by_group[$group->code] = count(App\ScheduleLine::where('blackout','!=',1)->where('schedule_id',$schedule->id)->where('line_group_id',$group->id)->get());
                            }

                            foreach($lines_by_group as $group_code=>$group_count){
                                echo '<th class="text-center compact">' . $group_code . '</th>';
                            }
                            echo '</tr></thead><tbody><tr>';

                            foreach($lines_by_group as $group_code=>$group_count){
                                echo '<td class="text-center compact">bid-for-' . strtolower($group_code) . '</td>';
                            }
                            echo '</tr><tr>';


                            foreach($lines_by_group as $group_code=>$group_count){
                                echo '<td class="text-center compact">' . $group_count . '</td>';
                            }
                            echo '</tr></tbody></table></div>';
                        }
                    }
                @endphp
                </div>
            </div>
        </div>
    </div>
</div>
@endsection