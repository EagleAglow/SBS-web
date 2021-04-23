<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use App\Schedule;
use App\ScheduleLine;
use App\LineGroup;
use App\User;
use App\Pick;
use DB;

//Importing laravel-permission models
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use Session;

class UserScheduleShowController extends Controller {

    public function __construct() {
        // verify logged in
        $this->middleware('auth');
    }

    /**
    * Display a listing of the resource - initial page for pagination
    *
    * @return \Illuminate\Http\Response
    */
    public function index(Request $request, $id) {
        if (!isset($id)){
            abort('401');
        }
        return view('users.scheduleshow.index',
            ['schedule'=>$schedule,
            'schedule_lines'=>$schedule_lines,
            'first_day'=>$first_day,
            'last_day'=>$last_day,
            'page'=>$page,
            'id'=>$id,
            'my_selection'=>$my_selection,
            'next_selection'=>$next_selection,
            'show_all'=>$show_all,
            'trap'=>'0',
            ]);
    }
 
 
    public function show(Request $request, $id) {
        if (!isset($id)){
            abort('401');
        }

        $user = auth()->user();  // actual user who is loading page - used to handle picks
        // first, identify the appropriate user
        $appropriate_user = auth()->user();  // unless the user is a supervisor (while not bidding for themselves)
        if ($appropriate_user->hasRole('supervisor')){
            if (!$appropriate_user->hasRole('bidder-active')){
                // find the user that is the active bidder (use first, even though  should be only one)
                $active_bidder_role = Role::where('name','bidder-active')->first();
                $active_bidders = User::Role($active_bidder_role)->get();
                if (count($active_bidders) > 0 ){
                    $appropriate_user = $active_bidders->first();
                }
            }
        }
        $pick_uid = $appropriate_user->id;    // get user id for later join to user picks
        // identify correct line groups
        $role_names = $appropriate_user->getRoleNames();
        $list_ids = array();  //empty array for line group ids
        $list_codes = array();  //empty array for line group codes (field = 'name')
        foreach ($role_names as $role_name) {
            if (strpos($role_name, 'bid-for-') !== false) {
                $look4 = strtoupper(str_replace('bid-for-','',$role_name));
                $list_ids[] = LineGroup::where('code',$look4)->first()['id'];
                $list_codes[] = $look4;
            }
        }

        // presentation selection = which line groups to show
        // if the bidder can only bid one line group, set 'my_selection' and 'next_selection' to that group code
        // if the bidder can bid more than one line group, rotate 'my_selection' through 'all' (lowercase to differ from any
        //    line group names), and then each line group name (uppercase).  'next_selection' shows subsequent choice
        // view page returns the values for 'my_selection' and 'next_selection' (but next_selection is not used by controller)
        // if request field 'go_next' is 'yes',  rotate to next group selection

        // debugging aid
        $trap = $request['trap'];
        if (!isset($trap)){ $trap = 'undefined'; }

        // passed to switch to display next group choice
        $go_next = $request['go_next'];
        if (!isset($go_next)){ $go_next = 'no'; }

        $my_selection = $request['my_selection'];
        if(!isset($my_selection)){
            if (count($list_ids) == 0){
                // should not get here
                $my_selection = 'all';
                $next_selection = 'all';
            } else {
                if (count($list_ids) == 1){
                    $my_selection = $list_codes[0];  // first, and only, code
                    $next_selection = $list_codes[0];
                    $key_id = $list_ids[0];     // if my_selection is not 'all', we will need a Key_id for selecting records
                    $trap = '5';
                } else {
                    $my_selection = 'all';
                    $next_selection = $list_codes[0];  // first code
                    $trap = '6';
                }
            }
        } else { 
            if (count($list_ids) == 0){
                // should not get here
                $my_selection = 'all';
                $next_selection = 'all';
            } else {
                if (count($list_ids) == 1){
                    $my_selection = $list_codes[0];  // first, and only, code
                    $next_selection = $list_codes[0];
                    $key_id = $list_ids[0];     // if my_selection is not 'all', we will need a Key_id for selecting records
                    $trap = '7';
                } else {
                    // there is more than one line group
                    if ($go_next == 'yes'){
                        // change groups
                        if ($my_selection == 'all'){
                            // go to first group code
                            $my_selection = $list_codes[0];  // first code
                            $next_selection = $list_codes[1];  // second code
                            $key_id = $list_ids[0];     // if my_selection is not 'all', we will need a Key_id for selecting records
                            $trap = '3';
                        } else {
                            // need id for 'my_selection', and next code in rotation, or if this is last code, then 'all'
                            $key_id = LineGroup::where('code',$my_selection)->first()['id'];
                            if (isset($key_id)){
                                $key = array_search($key_id,$list_ids);
                                if (($key +1) >= count($list_ids)){
                                    // wrap my_selection to 'all'
                                    $my_selection = 'all';
                                    $next_selection = $list_codes[0];
                                    $trap = '9';
                                } else {
                                    $my_selection = $list_codes[$key +1];
                                    $key_id = $list_ids[$key +1];
                                    if (($key +2) >= count($list_ids)){
                                        // wrap next_selection to 'all'
                                        $next_selection = 'all';
                                        $trap = '10';
                                    } else {
                                        $next_selection = $list_codes[$key +2];
                                        $key_id = $list_ids[$key +1];     // if my_selection is not 'all', we will need a Key_id for selecting records
                                        $trap = '11';
                                    }
                                }
                            } else {
                                // unlikely error - my_selection not in list
                                $my_selection = 'all';
                                $next_selection = 'all';
                            }
                        }
                    } else {
                        // don't change groups
                        if ($my_selection == 'all'){
                            $next_selection = $list_codes[0];  // first code
                            $trap = '1';
                        } else {
                            // need id for 'my_selection', to  find next code in rotation, or if this is last code, then 'all'
                            // if my_selection is not 'all', we will need a Key_id for selecting records
                            $key_id = LineGroup::where('code',$my_selection)->first()['id'];
                            if (isset($key_id)){
                                $key = array_search($key_id,$list_ids);
                                if (($key +1) >= count($list_ids)){
                                    // wrap next_selection to 'all'
                                    $next_selection = 'all';
                                    $trap = '8';
                                } else {
                                    $next_selection = $list_codes[$key +1];
                                    $trap = '2';
                                }
                            } else {
                                // unlikely error - my_selection not in list
                                $my_selection = 'all';
                                $next_selection = 'all';
                            }
                        }
                    }
                }
            }
        }

        // presentation filtering = whether or not to show only the lines are left for bidding
        // set 'show_all' to 'yes' or 'no'
        $show_all = $request['show_all'];
        if(!isset($show_all)){
            $show_all = 'yes';
        }
 
        $schedule = Schedule::findOrFail($id);//Get schedule with the given id
        if ($schedule->approved == true){
            if($my_selection == 'all'){
                if($show_all == 'yes'){
                    $schedule_lines = ScheduleLine::where('schedule_id',$id)->whereIn('line_group_id',$list_ids)
                    ->orderBy('line_natural')->paginate(5)->onEachSide(13);; //Get first 5 ScheduleLines;
                } else {
                    $schedule_lines = ScheduleLine::where('schedule_id',$id)->whereIn('line_group_id',$list_ids)
                    ->where('blackout',0)->whereNull('schedule_lines.user_id')->orderBy('line_natural')->paginate(5)->onEachSide(13);; //Get first 5 ScheduleLines;
                }
            } else {
                // filter to a single line group
                // collection of picks 
                $user_picks = Pick::select('schedule_line_id')->where('user_id','=',$pick_uid)->get()->toArray();
                if($show_all == 'yes'){
                    // get lines that have not been tagged for the user
                    $schedule_lines_not_tagged = ScheduleLine::whereNotIn('id', $user_picks)->where('schedule_id',$id)->where('line_group_id', $key_id)
                    ->select('schedule_lines.*', DB::raw('99999 as rank'));
                    // get lines that have been tagged for the user - join succeeds
                    $schedule_lines = ScheduleLine::where('schedule_id',$id)->where('line_group_id',$key_id)
                    ->join('picks','schedule_lines.id','=','picks.schedule_line_id')->where('picks.user_id','=', $pick_uid)->select('schedule_lines.*','rank')
                    ->union($schedule_lines_not_tagged)->orderBy('rank')->orderBy('line_natural')
                    ->paginate(5)->onEachSide(13);; //Get first 5 ScheduleLines
                } else {
                    // get lines that have not been tagged for the user
                    $schedule_lines_not_tagged = ScheduleLine::whereNotIn('id', $user_picks)->where('schedule_id',$id)->where('line_group_id', $key_id)->where('blackout',0)->whereNull('schedule_lines.user_id')
                    ->select('schedule_lines.*', DB::raw('99999 as rank'));
                    // get lines that have been tagged for the user - join succeeds
                    $schedule_lines = ScheduleLine::where('schedule_id',$id)->where('line_group_id',$key_id)->where('blackout',0)->whereNull('schedule_lines.user_id')
                    ->join('picks','schedule_lines.id','=','picks.schedule_line_id')->where('picks.user_id','=', $pick_uid)->select('schedule_lines.*','rank')
                    ->union($schedule_lines_not_tagged)->orderBy('rank')->orderBy('line_natural')
                    ->paginate(5)->onEachSide(13);; //Get first 5 ScheduleLines
                }
            }
        } else {
            // not approved - return an empty object
            $schedule_lines = collect([]);
        }


        $first_day = $request['first_day'];
        if (!isset($first_day)){
            $first_day = 1;
        }
        $last_day = $request['last_day'];
        if (!isset($last_day)){
            $last_day = 7;
        }

        $page = $request['page'];
        if (!isset($page)){
            $page = '1';
        }        

        // deal with tag/untag requests
        $pick = $request['pick'];
        $uid = $user->id;
        $schedule_line_id = $request['schedule_line_id'];
        if (isset($schedule_line_id)){
            if (isset($pick)){
//                if ($user->hasAnyRole('bid-for-demo','bid-for-irpa','bid-for-tsu','bid-for-oidp','bid-for-tcom','bid-for-tnon')){
                if ($user->hasPermissionTo('bid-self')){
                    if ($pick == 'tag'){
                        // if already tagged, this is a page refresh
                        if (!Pick::where('schedule_line_id',$schedule_line_id)->where('picks.user_id',$uid)->get()->count() > 0){
                            // attempt to tag
                            $schedule_line = ScheduleLine::findOrFail($schedule_line_id);
                            // get highest rank, for lines in the same schedule, if already set
                            $picks = Pick::join('schedule_lines','schedule_line_id','=','schedule_lines.id')
                                    ->where('schedule_lines.schedule_id',$schedule_line->schedule_id)->where('picks.user_id',$uid)->orderBy('picks.rank','DESC')->get();

                                    if (count($picks) == 0){
                                $rank = 1;
                            } else {
                                $rank = $picks->first()->rank +1;
                            }
                            // set this user id for this schedule line
                            $pick = new Pick();
                            $pick->schedule_line_id = $schedule_line->id;
                            $pick->user_id = $uid;
                            $pick->rank = $rank;
                            $pick->save();
                        };
                    }

                    if ($pick == 'untag'){
                        // if not already tagged, this is a page refresh
                        if (!Pick::where('schedule_line_id',$schedule_line_id)->where('picks.user_id',$uid)->get()->count() == 0){
                            // attempt to untag
                            $schedule_line = ScheduleLine::findOrFail($schedule_line_id);
                            $pick = Pick::where('user_id',$uid)->where('schedule_line_id',$schedule_line_id)->get()->first();
                            if (isset($pick)){
                                $pick->delete();
                                // re-rank remaining picks in same schedule
                                $pick_ids = Pick::select('picks.id')->where('picks.user_id',$uid)->join('schedule_lines','schedule_line_id','=','schedule_lines.id')
                                        ->where('schedule_lines.schedule_id',$schedule_line->schedule_id)->orderBy('picks.rank')->get()->toArray();
                                $rank = count($pick_ids);
                                if ($rank > 0){
                                    $rank = 0;
                                    foreach($pick_ids as $pick_id){
                                        $pick = Pick::where('id','=',$pick_id)->get()->first();
                                        $rank = $rank +1;
                                        $pick->rank = $rank;
                                        $pick->update();
                                    }
                                }
                            }
                        }
                    }

                    if ($pick == 'boost'){
                        // if rank 2 or greater, switch with next lower number rank, in same schedule
                        $schedule_line = ScheduleLine::findOrFail($schedule_line_id);
                        $uid = $user->id;
                        $pick = Pick::where('user_id',$uid)->where('schedule_line_id',$schedule_line_id)->get()->first();
                        if (isset($pick)){
                            $hold = $pick->rank;
                            if ($hold > 1){
                                $other_pick_id = Pick::select('picks.id')->where('picks.user_id','=',$uid)->where('picks.rank','<',$hold)
                                        ->join('schedule_lines','schedule_line_id','=','schedule_lines.id')->where('schedule_lines.schedule_id',$schedule_line->schedule_id)
                                        ->orderByDesc('picks.rank')->get()->first()->id;
                                if (isset($other_pick_id)){
                                    $pick->rank = $hold -1;
                                    $pick->update();
                                    $other_pick = Pick::where('id','=',$other_pick_id)->get()->first();
                                    $other_pick->rank = $hold;
                                    $other_pick->update();
                                }
                            }
                        }
                    }
                }
            }  
        }  

        return view('users.scheduleshow.index',
            ['schedule'=>$schedule,
            'schedule_lines'=>$schedule_lines,
            'first_day'=>$first_day,
            'last_day'=>$last_day,
            'page'=>$page,
            'id'=>$id,
            'my_selection'=>$my_selection,
            'next_selection'=>$next_selection,
            'show_all'=>$show_all,
            'trap' => $trap,
            'list_codes' => $list_codes
            ]);

    }
 
}
