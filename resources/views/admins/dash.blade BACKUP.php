@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header">Admin - Bidders vs. Schedules</div>

                @include('flash::message')

                <div class="card-body"><b>Bidders By Bidder Group</b><br>
                   <span style="font-size:0.8rem;">TCOM is commercial traffic only, TNON is non-commercial traffic only, TRAFFIC is either commercial or non-commercial.
                    TRAFFIC+TCOM and TRAFFIC+TNON are calculated for comparison with schedule lines.</span>
                </div>
                <div class="card-body my-squash">
                    <table class="table compact">
                        <thead>
                            <tr>
                            @php
                                $groups = App\BidderGroup::where('code','!=','NONE')->orderBy('order')->get();
                                $bidders_by_group = array();
                                foreach($groups as $group){
                                    $bidders_by_group[$group->code] = count(App\User::where('bidder_group_id',$group->id)->get());
                                }
                                // add extra element for TRAFFIC+TCOM
                                $bidders_by_group['TRAFFIC+TCOM'] = $bidders_by_group['TRAFFIC']+$bidders_by_group['TCOM'];
                                // add extra element for TRAFFIC+TNON
                                $bidders_by_group['TRAFFIC+TNON'] = $bidders_by_group['TRAFFIC']+$bidders_by_group['TNON'];
                                
                                foreach($bidders_by_group as $group_code=>$group_count){
                                    echo '<th class="text-center compact">' . $group_code . '</th>';
                                }
                                echo '</tr></thead><tbody><tr>';
                                foreach($bidders_by_group as $group_code=>$group_count){
                                    echo '<td class="text-center compact">' . $group_count . '</td>';
                                }
                            @endphp
                            </tr>
                        </tbody>
                    </table>
                </div>

                <hr>

                <div class="card-body my-squash"><b>Bidders By Bidder Role</b><br>
                   <span style="font-size:0.8rem;">Bidders in the TRAFFIC bidder group have roles of both "bid-for-tcom" and "bid-for-tnon",
                   so the count for "bid-for-tcom" should equal TRAFFIC+TCOM and the count for "bid-for-tnon" should equal the count for "TRAFFIC+TNON".</span>
                </div>

                <div class="card-body my-squash">
                    <table class="table compact">
                        <thead>
                            <tr>
                            @php
                                $bid_roles = DB::table('roles')->where('name','!=','superuser')->where('name','!=','admin')->where('name','!=','supervisor')->where('name','!=','bidder-active')->orderBy('name')->get();

                                $bidders_by_role = array();
                                // set up display order to match bidder group order
                                $groups = App\BidderGroup::where('code','!=','NONE')->where('code','!=','TRAFFIC')->orderBy('order')->get();
                                foreach($groups as $group){
                                    $xref = 'bid-for-' . strtolower($group->code);
                                    $role_id = Spatie\Permission\Models\Role::where('name',$xref)->get();
                                    if (count($role_id) > 0){
                                        // add element to array with dummy value
                                        $bidders_by_role[$xref] = 0;
                                    }
                                }

                                // get actual values
                                foreach($bid_roles as $bid_role){
                                    $bidders_by_role[$bid_role->name] = count(DB::table('model_has_roles')->where('model_type','App\User')->where('role_id',$bid_role->id)->get());
                                }


                                // see if the number of lines is at least the number of bidders by group
                                $flags_by_role = array();
                                foreach($bidders_by_role as $role_name=>$role_count){
                                    if ($role_name == 'bid-for-tcom'){
                                        if ($role_count == $bidders_by_group['TRAFFIC+TCOM']){
                                            $flags_by_role[$role_name] = 'OK';
                                        } else {
                                            $flags_by_role[$role_name] = '<span style="color:red;">CHECK</span>';
                                        }
                                    } else {
                                        if ($role_name == 'bid-for-tnon'){
                                            if ($role_count == $bidders_by_group['TRAFFIC+TNON']){
                                                $flags_by_role[$role_name] = 'OK';
                                            } else {
                                                $flags_by_role[$role_name] = '<span style="color:red;">CHECK</span>';
                                            }
                                        } else {
                                            if ($role_count == $bidders_by_group[ strtoupper(str_replace("bid-for-","",$role_name)  )     ]){
                                                $flags_by_role[$role_name] = 'OK';
                                            } else {
                                                $flags_by_role[$role_name] = '<span style="color:red;">CHECK</span>';
                                            }
                                        }
                                    }
                                }




                                foreach($bidders_by_role as $role_name=>$role_count){
                                    echo '<th class="text-center compact">' . $role_name . '</th>';
                                }
                                echo '</tr></thead><tbody><tr>';
                                foreach($bidders_by_role as $role_name=>$role_count){
                                    echo '<td class="text-center compact">' . $role_count . '</td>';
                                }
                                echo '</tr><tr>';
                                foreach($flags_by_role as $role_name=>$flag){
                                    echo '<td class="text-center compact">' . $flag . '</td>';
                                }



                            @endphp
                            </tr>
                        </tbody>
                    </table>
                </div>

                <hr>




                <div class="card-body my-squash"><b>Schedule Lines By Line Group</b><br>
                   <span style="font-size:0.8rem;">There should be at least as many TCOM schedule lines as there are bidders in the TRAFFIC+TCOM bidder groups,
                   and at least as many TNON schedule lines as there are bidders in the TRAFFIC+TNON bidder groups. For the other groups, there should be at 
                   least as many lines as bidders.</span>
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


                            $groups = App\LineGroup::where('code','!=','NONE')->orderBy('order')->get();
                            $lines_by_group = array();
                            foreach($groups as $group){
                                $lines_by_group[$group->code] = count(App\ScheduleLine::where('blackout','!=',1)->where('schedule_id',$schedule->id)->where('line_group_id',$group->id)->get());
                            }

                            
                            // see if the number of lines is at least the number of bidders by group
                            $flags_by_group = array();


                            foreach($lines_by_group as $group_code=>$group_count){
                                if ($group_code == 'TCOM'){
                                    if ($lines_by_group['TCOM'] >= $bidders_by_group['TRAFFIC+TCOM']){
                                        $flags_by_group[$group_code] = 'OK';
                                    } else {
                                        $flags_by_group[$group_code] = '<span style="color:red;">CHECK</span>';
                                    }
                                } else {
                                    if ($group_code == 'TNON'){
                                        if ($lines_by_group['TNON'] >= $bidders_by_group['TRAFFIC+TNON']){
                                            $flags_by_group[$group_code] = 'OK';
                                        } else {
                                            $flags_by_group[$group_code] = '<span style="color:red;">CHECK</span>';
                                        }
                                    } else {
                                        if ($group_count >= $bidders_by_group[$group_code]){
                                            $flags_by_group[$group_code] = 'OK';
                                        } else {
                                            $flags_by_group[$group_code] = '<span style="color:red;">CHECK</span>';
                                        }
                                    }
                                }
                            }

                            foreach($lines_by_group as $group_code=>$group_count){
                                echo '<th class="text-center compact">' . $group_code . '</th>';
                            }
                            echo '</tr></thead><tbody><tr>';
                            foreach($lines_by_group as $group_code=>$group_count){
                                echo '<td class="text-center compact">' . $group_count . '</td>';
                            }
                            echo '</tr><tr>';
                            foreach($flags_by_group as $group_code=>$flag){
                                echo '<td class="text-center compact">' . $flag . '</td>';
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