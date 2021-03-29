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

        $my_selection = $request['my_selection'];
        if(!isset($my_selection)){
            $my_selection = 'filter';
        }
        $tandc = $request['tandc'];  // show traffic and commercial
        if(!isset($tandc)){
            $tandc = 'yes';
        }


        return view('users.scheduleshow.index',
            ['schedule'=>$schedule,
            'schedule_lines'=>$schedule_lines,
            'first_day'=>$first_day,
            'last_day'=>$last_day,
            'page'=>$page,
            'id'=>$id,
            'tandc'=>$tandc,
            ]);
    }
 
 
    public function show(Request $request, $id) {
        if (!isset($id)){
            abort('401');
        }

        $my_selection = $request['my_selection'];
        if(!isset($my_selection)){
            $my_selection = 'filter';
        }
        $tandc = $request['tandc'];
        if(!isset($tandc)){
            $tandc = 'yes';
        }

        $user = auth()->user();
        // original idea...
        // bidder group codes: DEMO, OIDP, TSU, IRPA, TRAFFIC, COM, TANDC
        // not using bidder groups for this, to permit some odd people that don't doe both TCOM and TNON
        // select lines that this user can bid, based on role(s)
        // roles: bid-for-demo, bid-for-oidp, bid-for-tsu, bid-for-irpa, bid-for-tcom, bid-for-tnon
        // line group codes: DEMO, OIDP, TSU, IRPA, TCOM, TNON
        // also, flag when both TRAFFIC and COM are in list, then capture id
        //
        // rewritten - still has odd handling for TRAFFIC, but otherwise
        // role like 'bid-for-xyz' goes with line group 'XYZ'
        $tcount = 0;  // tracks TRAFFIC + COM in order to show "TANDC" for filter
        $list = array();  //empty array
        $role_names = $user->getRoleNames();
        foreach ($role_names as $role_name) {
            if (strpos($role_name, 'bid-for-') !== false) {
                $look4 = strtoupper(str_replace('bid-for-','',$role_name));
                if (($look4 == 'TRAFFIC') Or ($look4 == 'COMMERCIAL')){
                    $tcount = $tcount +1;
                }
                $list[] = LineGroup::where('code',$look4)->first()['id'];
            }
        }
        // get user id for later join to user picks
        $pick_uid = $user->id;

        // deal with bidding by supervisor... if there is an active bidder, use their role to get line group(s)
        if ($user->hasRole('supervisor')){
            $other_role = Role::where('name','bidder-active')->first();
            $who = User::Role($other_role)->get();
            if (count($who) > 0 ){
                $who = $who->first();
                $role_names = $who->getRoleNames();
                $list = array();  //empty array
                foreach ($role_names as $role_name) {
                    if (strpos($role_name, 'bid-for-') !== false) {
                        $look4 = strtoupper(str_replace('bid-for-','',$role_name));
                        if (($look4 == 'TRAFFIC') Or ($look4 == 'COMMERCIAL')){
                            $tcount = $tcount +1;
                        }
                        $list[] = LineGroup::where('code',$look4)->first()['id'];
                    }
                }
                // get user id for later join to user picks
                $pick_uid = $who->id;
            }
        } 

        // cycle $my_selection between 'filter', 'TRAFFIC', 'COMMERCIAL', all', but only use 'TRAFFIC' and 'COMMERCIAL' if both are in $list
        // use $tandc = 'yes' or 'no' to handle that - 
        // getting ugly in here - would not want this for a more flexible configuration
        if ($tcount >1 ){
            $tandc = 'yes';
        } else {
            $tandc = 'no';
        }
        $tcom_id = LineGroup::where('code','COMMERCIAL')->first()['id'];
        $tnon_id = LineGroup::where('code','TRAFFIC')->first()['id'];
 
        $schedule = Schedule::findOrFail($id);//Get schedule with the given id
        if ($schedule->approved == true){
            if($my_selection == 'filter'){
                // collection of picks
                $user_picks = Pick::select('schedule_line_id')->where('user_id','=',$pick_uid)->get()->toArray();
                // get lines that have not been tagged for the user
                $schedule_lines_not_tagged = ScheduleLine::whereNotIn('id', $user_picks)->where('schedule_id',$id)->whereIn('line_group_id',$list)->where('blackout',0)->whereNull('schedule_lines.user_id')
                ->select('schedule_lines.*', DB::raw('99999 as rank'));
                // get lines that have been tagged for the user - join succeeds
                $schedule_lines = ScheduleLine::where('schedule_id',$id)->whereIn('line_group_id',$list)->where('blackout',0)->whereNull('schedule_lines.user_id')
                ->join('picks','schedule_lines.id','=','picks.schedule_line_id')->where('picks.user_id','=', $pick_uid)->select('schedule_lines.*','rank')
                ->union($schedule_lines_not_tagged)->orderBy('rank')->orderBy('line_natural')
                ->paginate(5)->onEachSide(13);; //Get first 5 ScheduleLines
            } else {
                if($my_selection == 'COMMERCIAL'){
                    // collection of picks
                    $user_picks = Pick::select('schedule_line_id')->where('user_id','=',$pick_uid)->get()->toArray();
                    // get lines that have not been tagged for the user
                    $schedule_lines_not_tagged = ScheduleLine::whereNotIn('id', $user_picks)->where('schedule_id',$id)->where('line_group_id', $tcom_id)->where('blackout',0)->whereNull('schedule_lines.user_id')
                    ->select('schedule_lines.*', DB::raw('99999 as rank'));
                    // get lines that have been tagged for the user - join succeeds
                    $schedule_lines = ScheduleLine::where('schedule_id',$id)->whereIn('line_group_id',$list)->where('blackout',0)->whereNull('schedule_lines.user_id')
                    ->join('picks','schedule_lines.id','=','picks.schedule_line_id')->where('picks.user_id','=', $pick_uid)->select('schedule_lines.*','rank')
                    ->union($schedule_lines_not_tagged)->orderBy('rank')->orderBy('line_natural')
                    ->paginate(5)->onEachSide(13);; //Get first 5 ScheduleLines
                } else {
                    if($my_selection == 'TRAFFIC'){
                        // collection of picks
                        $user_picks = Pick::select('schedule_line_id')->where('user_id','=',$pick_uid)->get()->toArray();
                        // get lines that have not been tagged for the user
                        $schedule_lines_not_tagged = ScheduleLine::whereNotIn('id', $user_picks)->where('schedule_id',$id)->where('line_group_id', $tnon_id)->where('blackout',0)->whereNull('schedule_lines.user_id')
                        ->select('schedule_lines.*', DB::raw('99999 as rank'));
                        // get lines that have been tagged for the user - join succeeds
                        $schedule_lines = ScheduleLine::where('schedule_id',$id)->whereIn('line_group_id',$list)->where('blackout',0)->whereNull('schedule_lines.user_id')
                        ->join('picks','schedule_lines.id','=','picks.schedule_line_id')->where('picks.user_id','=', $pick_uid)->select('schedule_lines.*','rank')
                        ->union($schedule_lines_not_tagged)->orderBy('rank')->orderBy('line_natural')
                        ->paginate(5)->onEachSide(13);; //Get first 5 ScheduleLines
                    } else {
                        // all
                        $schedule_lines = ScheduleLine::where('schedule_id',$id)->whereIn('line_group_id',$list)->paginate(5)->onEachSide(13);; //Get first 5 ScheduleLines; //Get first 5 ScheduleLines
                    }
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
                if ($user->hasAnyRole('bid-for-demo','bid-for-irpa','bid-for-tsu','bid-for-oidp','bid-for-tcom','bid-for-tnon')){
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
            'tandc'=>$tandc,
            ]);

    }
 
}
