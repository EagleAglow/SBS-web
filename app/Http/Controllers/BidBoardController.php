<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\ShiftCode;
use App\LineGroup;
use App\BidderGroup;
use App\Schedule; 
use App\ScheduleLine;
use App\User; 
use App\Param;
use App\Pick;
use App\LogItem;
use App\Snapshot;

class BidBoardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }
  

    /**
     * Show the bidder dashboard. 
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {

        $schedule = Schedule::where('active','1')->first();
        if (!isset($schedule)){
            abort('404');
        } else {
            $id = $schedule->id;
            if (!isset($id)){
                abort('404');
            } else {

                $schedule_lines = ScheduleLine::join('line_groups','line_groups.id','=','schedule_lines.line_group_id')->where('schedule_id',$id)->orderBy('line_groups.order')->orderBy('line_natural')->paginate(25);
                if (!isset($first_day)){
                    $first_day = 1;
                }
                if (!isset($delta)){
                    $delta = 7;
                }
                $last_day = $first_day + $delta - 1;
                if (!isset($page)){
                    $page = 1;
                }
                if (!isset($my_selection)){
                    $my_selection = 'all';
                }
                if (!isset($next_selection)){
                    $next_selection = 'all';
                }
                if(!isset($show_all)){
                    $show_all = 'yes';
                }
            }     

/* 

    // get total days in cycle
    $max_days = $schedule->cycle_days;
    // days to display
    $delta = '7';
    if (!isset($page)){ $page = 1; }
    // cycles
    $cycles = $schedule->cycle_count;
    if (isset($cycles)){
        if (($cycles <= 0 ) || ( 5 <= $cycles )){
            $cycles = 1;
        }
    } else { $cycles = 1; }
    // first day - default to first block
    if (!isset($first_day)){
        $first_day = 1; 
    } else {
        // sanity check
        if ( $first_day <= 0 ){ 
            $first_day = 1; 
        }
        if ( ($first_day + $delta) > $max_days ){ 
            $first_day = $max_days -$delta +1;                                               
        }
    }
    $last_day = $first_day + $delta - 1;

 */

            return view('bidboard.index',
            ['id'=>$id,
            'schedule'=>$schedule,
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
    }


    public function show(Request $request, $id) {


abort('401');

        if (!isset($id)){
            abort('401');
        }

abort('401');

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
//            $show_all = 'yes';
            $show_all = 'no';
        }
 
        $schedule = Schedule::findOrFail($id);//Get schedule with the given id
        if ($schedule->approved == true){
            // collection of picks 
            $user_picks = Pick::select('schedule_line_id')->where('user_id','=',$pick_uid)->get()->toArray();

            if($my_selection == 'all'){
                if($show_all == 'yes'){
                    // get lines that have not been tagged for the user
                    $schedule_lines_not_tagged = ScheduleLine::whereNotIn('id', $user_picks)->where('schedule_id',$id)->whereIn('line_group_id',$list_ids)
                    ->select('schedule_lines.*', DB::raw('99999 as rank'));
                    // get lines that have been tagged for the user and join
                    $schedule_lines = ScheduleLine::where('schedule_id',$id)->whereIn('line_group_id',$list_ids)
                    ->join('picks','schedule_lines.id','=','picks.schedule_line_id')->where('picks.user_id','=', $pick_uid)->select('schedule_lines.*','rank')
                    // combine and sort
                    ->union($schedule_lines_not_tagged)->orderBy('rank')->orderBy('line_natural')
                    ->paginate(5)->onEachSide(13);; //Get first 5 ScheduleLines
                } else {
                    // get lines that have not been tagged for the user
                    $schedule_lines_not_tagged = ScheduleLine::whereNotIn('id', $user_picks)->where('schedule_id',$id)->whereIn('line_group_id',$list_ids)
                    ->where('blackout',0)->whereNull('schedule_lines.user_id')
                    ->select('schedule_lines.*', DB::raw('99999 as rank'));
                    // get lines that have been tagged for the user
                    $schedule_lines = ScheduleLine::where('schedule_id',$id)->whereIn('line_group_id',$list_ids)
                    ->where('blackout',0)->whereNull('schedule_lines.user_id')
                    ->join('picks','schedule_lines.id','=','picks.schedule_line_id')->where('picks.user_id','=', $pick_uid)->select('schedule_lines.*','rank')
                    // combine and sort
                    ->union($schedule_lines_not_tagged)->orderBy('rank')->orderBy('line_natural')
                    ->paginate(5)->onEachSide(13);; //Get first 5 ScheduleLines
                }



            } else {
                // filter to a single line group
                if($show_all == 'yes'){
                    // get lines that have not been tagged for the user
                    $schedule_lines_not_tagged = ScheduleLine::whereNotIn('id', $user_picks)->where('schedule_id',$id)->where('line_group_id', $key_id)
                    ->select('schedule_lines.*', DB::raw('99999 as rank'));
                    // get lines that have been tagged for the user
                    $schedule_lines = ScheduleLine::where('schedule_id',$id)->where('line_group_id',$key_id)
                    ->join('picks','schedule_lines.id','=','picks.schedule_line_id')->where('picks.user_id','=', $pick_uid)->select('schedule_lines.*','rank')
                    // combine and sort
                    ->union($schedule_lines_not_tagged)->orderBy('rank')->orderBy('line_natural')
                    ->paginate(5)->onEachSide(13);; //Get first 5 ScheduleLines
                } else {
                    // get lines that have not been tagged for the user
                    $schedule_lines_not_tagged = ScheduleLine::whereNotIn('id', $user_picks)->where('schedule_id',$id)->where('line_group_id', $key_id)->where('blackout',0)->whereNull('schedule_lines.user_id')
                    ->select('schedule_lines.*', DB::raw('99999 as rank'));
                    // get lines that have been tagged for the user
                    $schedule_lines = ScheduleLine::where('schedule_id',$id)->where('line_group_id',$key_id)->where('blackout',0)->whereNull('schedule_lines.user_id')
                    ->join('picks','schedule_lines.id','=','picks.schedule_line_id')->where('picks.user_id','=', $pick_uid)->select('schedule_lines.*','rank')
                    // combine and sort
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


        return view('bidboard.index',
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
 

    public function edit($id) {
        abort('401'); 
    }

 
    public function update(Request $request, $id) {
        abort('401'); 
    }

    
}
