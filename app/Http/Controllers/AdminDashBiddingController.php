<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Param;
use App\Schedule;
use App\ScheduleLine;
use App\BidderGroup;
use App\LineGroup;
use App\Snapshot;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

use Illuminate\Support\Facades\Mail;
use App\Mail\NextBidderMail;
use App\Mail\NextBidderTestMail;
use App\Mail\ActiveBidderMail;
use App\Mail\ActiveBidderTestMail;
use App\Mail\PauseMail;
use App\Mail\PauseTestMail;

use App\LogItem;
use DB;

use Dotunj\LaraTwilio\Facades\LaraTwilio;  // SMS messaging

class AdminDashBiddingController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // verify logged in
        $this->middleware('auth');
        // to enable email verification in this controller
        //  $this->middleware(['auth','verified']);

    }

    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (Auth::user()->hasRole('admin')){
            return view('admins.dashBidding');
        } else {
            abort('401');
        }
    }


    public function fix()  // fix problems
    {
        if (Auth::user()->hasRole('admin')){

            // log start
            $log_item = new LogItem();
            $log_item->note = 'Begin bidder group/role fix';
            $log_item->save();

            // see if all bidders have matching bidding groups and roles, fix errors
            $bidder_roles = DB::table('roles')->where('name','like', 'bid-for-%')->get('name');
            $users = User::all()->sortBy('name');
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
                    // do they have a bidding group?
//                    $bg = $user->bidder_group->code;
                    $bg = $user->bidder_group;
                    if (!isset($bg)){
                        // this user has no bidding group code, remove all roles that begin with 'bid-for-' (already collected, above)
                        foreach($bidder_roles as $bidder_role){
                            if ($user->hasRole($bidder_role->name)){
                                $user->removeRole($bidder_role->name);
                            }
                        }
                        // log action
                        $log_item = new LogItem();
                        $log_item->note = 'Removed bidding role(s) from bidder (' . $user->name . ') without group.';
                        $log_item->save();
                    } else {
                        // build user/bidder role list
                        $user_roles = $user->roles;
                        $user_bidrole_list = array();
                        foreach($user_roles as $user_role){
                            if ( str_starts_with($user_role->name,'bid-for-') ){
                                array_push($user_bidrole_list, $user_role->name);
                            }
                        }

 
                        // build bid group role list
                        $bg_roles = $user->bidder_group->roles;

                        // if this user is the active bidder, skip check, just assume data is OK
                        // really don't want to (or need to) change that role in the middle of bidding
                        if (!$user->hasRole('bidder-active')){
                            // does this user have the bidding role(s) that goes with the code?
                            $mismatch = false;
                            foreach($bidder_roles as $bidder_role){
                                if ($user->hasRole($bidder_role->name)){ $u_has_role = 1; } else { $u_has_role = 0; }
                                if ($bg->hasRole($bidder_role->name)){ $bg_has_role = 1; } else { $bg_has_role = 0; }
                                if ( $u_has_role <> $bg_has_role ){
                                    $mismatch = true;
                                    break;  // only mismatch once...
                                }
                            }
                            if ($mismatch){
                                // fix mismatch - remove all bidding roles, then restore correct one(s)
                                foreach($bidder_roles as $bidder_role){
                                    if ($user->hasRole($bidder_role->name)){
                                        $user->removeRole($bidder_role->name);
                                    }
                                }
                                foreach($bg_roles as $bg_role){
                                    $user->assignRole($bg_role->name);
                                }
                                // log action
                                $log_item = new LogItem();
                                $log_item->note = 'Set bidding roles for ' . $user->bidder_group->code . ' bidder (' . $user->name . ')';
                                $log_item->save();
                            }
                        }
                    }
                }
            }
            // log end
            $log_item = new LogItem();
            $log_item->note = 'Done with bidder group/role fix';
            $log_item->save();

            // log start bid order fix
            $log_item = new LogItem();
            $log_item->note = 'Begin bid order fix';
            $log_item->save();

            // fix up any blank seniority (primary order) entries - set them tied to bid last
            // get last bidder
            $last_person_number = User::all()->sortByDesc('seniority_date')->first()->seniority_date;
            // process any null entries, but only for bidders
            $users = User::whereNull('seniority_date')->get();
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
                    $user->seniority_date = $last_person_number +1;
                    $user->save();
                    // log action
                    $log_item = new LogItem();
                    $log_item->note = 'Bidder (' . $user->name . ') with blank seniority set to: ' . ($last_person_number +1);
                    $log_item->save();
                }
            }

            // fix any seniority ties, enter random number tie-breaker
            $users = User::all();
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
                    // checking bidder_group (id), seniority_date, tie_breaker to find ties
                    $ties = User::where('bidder_group_id',$user->bidder_group_id)->where('seniority_date',$user->seniority_date)->where('bidder_tie_breaker',$user->bidder_tie_breaker)->get();
                    if(count($ties) > 1 ){
                        // assign a random tie-breaker number to every user with this bidder_group_id and seniority number
                        foreach($ties as $tie){
                            $x = mt_rand(1,100000);
                            $tie->update(['bidder_tie_breaker' => $x]);
                            // log action
                            $log_item = new LogItem();
                            $bidder_group_code = BidderGroup::where('id',$tie->bidder_group_id)->first()->code;
                            $log_item->note = 'Bidding Tie: ' . $tie->name . ' (Group/Seniority=' . $bidder_group_code . '/' . $tie->seniority_date . '), Tie-breaker set to: ' . $x;
                            $log_item->save();
                        }
                    }
                }
            }

            // log step
            $log_item = new LogItem();
            $log_item->note = 'Done assigning random tie-breaker values, begin setting bid order';
            $log_item->save();

            // set actual bid_order according to: bidding group order, seniority date and tie-breaker
            // sortBy doesn't seem to work for this, with Users model?
            // collection returned by DB does not have role/permission link, nor update function
            // so, a combination of both
            $users = DB::table('users')
                ->join('bidder_groups','users.bidder_group_id', '=', 'bidder_groups.id')
                ->orderBy('bidder_groups.order')->orderBy('seniority_date')->orderBy('bidder_tie_breaker')
                ->select('users.id','users.name','bidder_groups.code','bidder_tie_breaker','seniority_date')->get();
            $bidder_count = 1;
            foreach($users as $user){
                $u = User::find($user->id);
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
                    $secondary = $user->bidder_tie_breaker;
                    if (!isset($secondary)){
                        $secondary = 'None';
                    }
                    $primary = $user->seniority_date;
                    $note = 'Set Bid Order: ' . $user->name . ' (Group/Seniority=' . $user->code . '/' . $primary . ', Tie-breaker=' . $secondary . '), bid order set to: ' . $bidder_count;
                    $u->update(['bid_order' => $bidder_count]);
                    // log action
                    $log_item = new LogItem();
                    $log_item->note = $note;
                    $log_item->save();
                    // adjust for next
                    $bidder_count = $bidder_count +1;
                }
            }

            // log complete
            $log_item = new LogItem();
            $log_item->note = 'Complete bid order fix';
            $log_item->save();

            flash('Bidding Problems FIXED!')->success();
            return view('admins.dashBidding');
        } else {
            abort('401');
        }
    }

    public function reset()  // assumes seniority and tie-breakers are OK
    // resets bid order (again, probably overkill)
    // clears all bids: all users set 'has_bid' to false/zero
    //                  all schedule lines set 'user_id' to null
    //                  all schedule lines 'bid_at' set to null
    // deletes any snapshots
    // deletes any mirrored schedule lines
    // sets parameters: 'bidding-next' to lowest (non-zero) bid order, skipping bidders flagged snapshot or deferred
    //                  'bidding_state' to 'ready'
    // clears role: 'bidder-active' from all users
    {
        if (Auth::user()->hasRole('admin')){

            $state_param = Param::where('param_name','bidding-state')->first();
            $test = $state_param->string_value;
            if ( ($test == 'running') or ($test == 'ready') ) {
                // do nothing, except complain
                flash('Unable to reset!')->warning()->important();
                return redirect()->route('admins.dashBidding');
            } else {

                // log start
                $log_item = new LogItem();
                $log_item->note = 'Begin reset bidding';
                $log_item->save();

                // set actual bid_order according to: bidding group order, seniority date and tie-breaker
                // and remove active bidder role
                // sortBy doesn't seem to work for this, with Users model?
                // collection returned by DB does not have role/permission link, nor update function
                // so, a combination of both
                $users = DB::table('users')
                    ->join('bidder_groups','users.bidder_group_id', '=', 'bidder_groups.id')
                    ->orderBy('bidder_groups.order')->orderBy('seniority_date')->orderBy('bidder_tie_breaker')
                    ->select('users.id','users.name','bidder_groups.code','bidder_tie_breaker','seniority_date')->get();

                $bidder_count = 1;
                foreach($users as $user){
                    $u = User::find($user->id);
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
                        $primary = $user->seniority_date;
                        $secondary = $user->bidder_tie_breaker;
                        $note = 'Confirm Bid Order: ' . $user->name . ' (group/seniority/tie-breaker=' . $user->code . '/' . $primary . '/' . $secondary . '), bid order set to: ' . $bidder_count;
                        $u->update(['bid_order' => $bidder_count, 'has_bid' => false]);
                        // log action
                        $log_item = new LogItem();
                        $log_item->note = $note;
                        $log_item->save();
                        // adjust for next
                        $bidder_count = $bidder_count +1;
                    }
                    if($u->hasRole('bidder-active')){
                        $u->removeRole('bidder-active');
                    }
                }

                // clear bidders from schedule lines
                $schedule_lines = ScheduleLine::all();
                foreach($schedule_lines as $schedule_line){
                    $schedule_line->update(['user_id' => null, 'bid_at' => null]);
                }

                // clear "has_snapshot" from users
                $users = User::where('has_snapshot',1);
                foreach($users as $user){
                    $user->update(['has_snapshot' => 0]);
                }

                // delete any snapshots
                DB::table('snapshots')->delete();

                // delete mirror schedule lines
                DB::table('schedule_lines')->where('mirror',1)->delete();

                // get id list of bidders to skip
                $skip_ids = array();  //empty array
                $uids = User::role(['flag-snapshot','flag-deferred'])->select('id')->get();
                $skip_ids = array();
                foreach($uids as $uid){
                    $skip_ids[] = $uid->id;
                }

                // find first bidder (a bidder that is not snapshot or deferred)
                $user = User::whereNotIn('id',$skip_ids)->where('bid_order','>',0)->orderBy('bid_order')->first();                

                // reset parameters
                $state_param = Param::where('param_name','bidding-state')->first();
                $state_param->update(['string_value' => 'ready']);
                $next_param = Param::where('param_name','bidding-next')->first();
                $next_param->update(['integer_value' => $user->bid_order]);

                // log done
                $log_item = new LogItem();
                $log_item->note = 'Finish reset bidding';
                $log_item->save();

                flash('Bidding has been RESET!')->success();
                return view('admins.dashBidding');
            }
        } else {
            abort('401');
        }
    }

    public function start()  // sets parameter: 'bidding_state' to 'running'
    // assigns role: 'bidder-active' to bidder with lowest bidding order
    // skipping bidders with flag-deferred or flag-snapshot
    
    {
        if (Auth::user()->hasRole('admin')){

            $state_param = Param::where('param_name','bidding-state')->first();
            $test = $state_param->string_value;
            if ($test <> 'ready') {
                // do nothing, except complain
                flash('Unable to start!')->warning()->important();
                return redirect()->route('admins.dashBidding');
            } else {

                // get id list of bidders to skip
                $skip_ids = array();  //empty array
                $uids = User::role(['flag-snapshot','flag-deferred'])->select('id')->get();
                $skip_ids = array();
                foreach($uids as $uid){
                    $skip_ids[] = $uid->id;
                }

                // find first bidder that is not skipped
                $user = User::whereNotIn('id',$skip_ids)->where('bid_order','>',0)->orderBy('bid_order')->first();

                // handle snapshot bidders (with bid orders before this bidder) that have not yet been "snapshotted"
                $snap_users = User::role(['flag-snapshot'])->where('has_snapshot',0)->where('bid_order','<',$user->bid_order)->orderBy('bid_order')->get();
                foreach($snap_users as $snap_user){
                    // create snapshot of lines that this user could bid
                    // identify correct line groups - store ids in $list_codes
                    $role_names = $snap_user->getRoleNames();
                    $list_ids = array();  //empty array for line group ids
                    foreach ($role_names as $role_name) {
                        if (strpos($role_name, 'bid-for-') !== false) {
                            $look4 = strtoupper(str_replace('bid-for-','',$role_name));
                            $list_ids[] = LineGroup::where('code',$look4)->first()['id'];
                        }
                    }
                    // get active schedule
                    $active_sched = Schedule::select('id')->where('active', 1)->get();
                    if ($active_sched->count() > 0){
                        $active_sched_id = $active_sched->first()->id;
                        // get schedule lines (not yet taken) for those groups
                        $schedule_lines = ScheduleLine::where('schedule_id',$active_sched_id)->whereIn('line_group_id',$list_ids)
                        ->whereNull('user_id')->orderBy('line_natural')->get();
                        foreach ($schedule_lines as $schedule_line){
                            // put line in snapshots
                            $snapshot = new Snapshot();
                            $snapshot->schedule_line_id = $schedule_line->id;
                            $snapshot->user_id = $snap_user->id;
                            $snapshot->save();
                        }
                    }
                    // tag user
                    $snap_user->update(['has_snapshot' => 1]);
                    // log
                    $log_item = new LogItem();
                    $log_item->note = 'Saved snapshot for: ' . $snap_user->name;
                    $log_item->save();
                }

                // done with snapshots, proceed to real bidding
                $user->assignRole('bidder-active');
                // set parameter
                $state_param = Param::where('param_name','bidding-state')->first();
                $state_param->update(['string_value' => 'running']);
                $next_param = Param::where('param_name','bidding-next')->first();
                // need to toggle value to ensure "updated_at" is changed
                $next_param->update(['integer_value' => 0]);
                $next_param->update(['integer_value' => $user->bid_order]);

                // get following bidder - assumes always at least two that are not skipped
                $user2 = User::whereNotIn('id',$skip_ids)->where('bid_order','>',$user->bid_order)->orderBy('bid_order')->first();

                // send email to bidders?
                $param_next_bidder_email_on_or_off = Param::where('param_name','next-bidder-email-on-or-off')->first()->string_value;
                if(isset($param_next_bidder_email_on_or_off)){
                    if($param_next_bidder_email_on_or_off == 'on'){
                        $param_all_email_to_test_address_on_or_off = Param::where('param_name','all-email-to-test-address-on-or-off')->first()->string_value;
                        if($param_all_email_to_test_address_on_or_off == 'on'){
                            $param_email_test_address = Param::where('param_name','email-test-address')->first()->string_value;
                            if(isset($param_email_test_address)){
                                if(strlen($param_email_test_address) > 0){
                                    // send mail to test address
                                    Mail::to($param_email_test_address)->send(new ActiveBidderTestMail($user->name));
                                    Mail::to($param_email_test_address)->send(new NextBidderTestMail($user2->name));
                                }
                            }
                        } else {
                            // send to bidders
                            Mail::to($user->email)->send(new ActiveBidderMail($user->name));     
                            $note = 'Email for active bidder sent to: ' . $user->name . ' (' . $user->email . ')';
                            $log_item = new LogItem();
                            $log_item->note = $note;
                            $log_item->save();

                            Mail::to($user2->email)->send(new NextBidderMail($user2->name));
                            $note = 'Email for "next" bidder sent to: ' . $user2->name . ' (' . $user2->email . ')';
                            $log_item = new LogItem();
                            $log_item->note = $note;
                            $log_item->save();
                        }
                    }
                }

                // send text to bidders?
                $param_next_bidder_text_on_or_off = Param::where('param_name','next-bidder-text-on-or-off')->first()->string_value;
                if(isset($param_next_bidder_text_on_or_off)){
                    if($param_next_bidder_text_on_or_off == 'on'){
                        $param_all_text_to_test_phone_on_or_off = Param::where('param_name','all-text-to-test-phone-on-or-off')->first()->string_value;
                        if($param_all_text_to_test_phone_on_or_off == 'on'){
                            $param_text_test_phone = Param::where('param_name','text-test-phone')->first()->string_value;
                            if(isset($param_text_test_phone)){
                                if(strlen($param_text_test_phone) > 0){
                                    // send texts to test phone number
                                    // LaraTwilio::notify($param_text_test_phone, 'TEST: Hello '. $user->name . ' - You can bid now, you are the active bidder.  Login at: ' . config('extra.login_url') . ' or call: ' . config('extra.app_bid_phone'));
                                    LaraTwilio::notify($param_text_test_phone, 'TEST: Hello '. $user->name . ' - You can bid now, you are the active bidder.  Call: ' . config('extra.app_bid_phone') . ", or attend the Boardroom if you are on site.);
                                    LaraTwilio::notify($param_text_test_phone, 'TEST: Hello '. $user2->name . ' - You will be able to bid soon. You will be notified when the current bidder is done.');
                                }
                            }
                        } else {
                            // send to active bidder, if they have a number
                            if (isset($user->phone_number)){
                                if (strlen($user->phone_number)>0){
                                    // LaraTwilio::notify($user->phone_number, 'Hello '. $user->name . ' - You can bid now, you are the active bidder.  Login at: ' . config('extra.login_url') . ' or call: ' . config('extra.app_bid_phone'));
                                    LaraTwilio::notify($user->phone_number, 'Hello '. $user->name . ' - You can bid now, you are the active bidder.  Call: ' . config('extra.app_bid_phone') . ", or attend the Boardroom if you are on site.);
                                    $note = 'Text for active bidder sent to: ' . $user->name . ' (' . $user->phone_number . ')';
                                    $log_item = new LogItem();
                                    $log_item->note = $note;
                                    $log_item->save();
                                }
                            }
                            // send to next bidder, if they have a number
                            if (isset($user2->phone_number)){
                                if (strlen($user2->phone_number)>0){
                                    LaraTwilio::notify($user2->phone_number, 'Hello '. $user2->name . ' - You will be able to bid soon. You will be notified when the current bidder is done.');
                                    $note = 'Text for "next" bidder sent to: ' . $user2->name . ' (' . $user2->phone_number . ')';
                                    $log_item = new LogItem();
                                    $log_item->note = $note;
                                    $log_item->save();
                                }
                            }
                        }
                    }
                }

                // log
                $log_item = new LogItem();
                $log_item->note = 'Start bidding';
                $log_item->save();

                flash('Bidding Started...')->success();
                return view('admins.dashBidding');
            }
        } else {
            abort('401');
        }
    }

    public function pause()  // sets parameter: 'bidding_state' to 'paused'
    {
        if (Auth::user()->hasRole('admin')){

            $state_param = Param::where('param_name','bidding-state')->first();
            $test = $state_param->string_value;
            if ($test <> 'running') {
                // do nothing, except complain
                flash('Unable to pause!')->warning()->important();
                return redirect()->route('admins.dashBidding');
            } else {
                // remove role from active bidder (or bidders, although not likely)
                $active_bidders = User::role('bidder-active')->get();
                foreach ($active_bidders as $active_bidder){
                    $active_bidder->removeRole('bidder-active');                    
                }

                // set parameter
                $state_param = Param::where('param_name','bidding-state')->first();
                $state_param->update(['string_value' => 'paused']);

                // log
                $log_item = new LogItem();
                $log_item->note = 'Pause bidding';
                $log_item->save();

                // notifications to next bidders (by bid order) that have not yet bid
                // get id list of bidders to skip
                $skip_ids = array();  //empty array
                $uids = User::role(['flag-snapshot','flag-deferred'])->select('id')->get();
                $skip_ids = array();
                foreach($uids as $uid){
                    $skip_ids[] = $uid->id;
                }

                $users = User::whereNotIn('id',$skip_ids)->where('has_bid',0)->where('bid_order','>',0)->orderBy('bid_order')->limit(2)->get();
                foreach ($users as $user){
                    // send pause email
                    $param_next_bidder_email_on_or_off = Param::where('param_name','next-bidder-email-on-or-off')->first()->string_value;
                    if(isset($param_next_bidder_email_on_or_off)){
                        if($param_next_bidder_email_on_or_off == 'on'){
                            $param_all_email_to_test_address_on_or_off = Param::where('param_name','all-email-to-test-address-on-or-off')->first()->string_value;
                            if($param_all_email_to_test_address_on_or_off == 'on'){
                                $param_email_test_address = Param::where('param_name','email-test-address')->first()->string_value;
                                if(isset($param_email_test_address)){
                                    if(strlen($param_email_test_address) > 0){
                                        // send mail to test address
                                        Mail::to($param_email_test_address)->send(new PauseTestMail($user->name));
                                    }
                                }
                            } else {
                                // send to bidder
                                Mail::to($user->email)->send(new PauseMail($user->name));
                                $note = 'Pause email sent to: ' . $user->name . ' (' . $user->email . ')';
                                $log_item = new LogItem();
                                $log_item->note = $note;
                                $log_item->save();
                            }
                        }
                    }

                    // send pause text
                    $param_next_bidder_text_on_or_off = Param::where('param_name','next-bidder-text-on-or-off')->first()->string_value;
                    if(isset($param_next_bidder_text_on_or_off)){
                        if($param_next_bidder_text_on_or_off == 'on'){
                            $param_all_text_to_test_phone_on_or_off = Param::where('param_name','all-text-to-test-phone-on-or-off')->first()->string_value;
                            if($param_all_text_to_test_phone_on_or_off == 'on'){
                                $param_text_test_phone = Param::where('param_name','text-test-phone')->first()->string_value;
                                if(isset($param_text_test_phone)){
                                    if(strlen($param_text_test_phone) > 0){
                                        // send text to test phone number
                                        LaraTwilio::notify($param_text_test_phone, 'TEST: Hello '. $user->name . ' - Bidding is temporarily suspended. You will be notified if/when it resumes, or call: ' . config('extra.app_bid_phone'));
                                    }
                                }
                            } else {
                                // send to bidder, if they have a number
                                if (isset($user->phone_number)){
                                    if (strlen($user->phone_number)>0){
                                        LaraTwilio::notify($user->phone_number, 'Hello '. $user->name . ' - Bidding is temporarily suspended. You will be notified if/when it resumes, or call: ' . config('extra.app_bid_phone'));
                                        $note = 'Pause text sent to: ' . $user->name . ' (' . $user->phone_number . ')';
                                        $log_item = new LogItem();
                                        $log_item->note = $note;
                                        $log_item->save();
                                    }
                                }
                            }
                        }
                    }
                }

                flash('Bidding Paused...')->success();
                return view('admins.dashBidding');
            }
        } else {
            abort('401');
        }
    }

    public function continue()  // sets parameter: 'bidding_state' to 'running'
    {
        if (Auth::user()->hasRole('admin')){

            $state_param = Param::where('param_name','bidding-state')->first();
            $test = $state_param->string_value;
            if ($test <> 'paused') {
                // do nothing, except complain
                flash('Unable to continue!')->warning()->important();
                return redirect()->route('admins.dashBidding');
            } else {
                // make current bidder active
                $next_param = Param::where('param_name','bidding-next')->first();
                if (!isset($next_param)){
                    // do nothing, except complain
                    flash('Missing next bidder order, unable to continue!')->warning()->important();
                    return redirect()->route('admins.dashBidding');
                } else {
                    $user = User::where('bid_order','=',$next_param->integer_value)->first();
                    if (!isset($user)){
                    // do nothing, except complain
                        flash('Missing bidder, unable to continue!')->warning()->important();
                        return redirect()->route('admins.dashBidding');
                    } else {
                        // assign role, move on
                        $user->assignRole('bidder-active');
                        // set parameter
                        $state_param = Param::where('param_name','bidding-state')->first();
                        $state_param->update(['string_value' => 'running']);

                        // send email to next (now the current/active) bidder
                        $param_next_bidder_email_on_or_off = Param::where('param_name','next-bidder-email-on-or-off')->first()->string_value;
                        if(isset($param_next_bidder_email_on_or_off)){
                            if($param_next_bidder_email_on_or_off == 'on'){
                                $param_all_email_to_test_address_on_or_off = Param::where('param_name','all-email-to-test-address-on-or-off')->first()->string_value;
                                if($param_all_email_to_test_address_on_or_off == 'on'){
                                    $param_email_test_address = Param::where('param_name','email-test-address')->first()->string_value;
                                    if(isset($param_email_test_address)){
                                        if(strlen($param_email_test_address) > 0){
                                            // send mail to test address
                                            Mail::to($param_email_test_address)->send(new ActiveBidderTestMail($user->name));
                                        }
                                    }
                                } else {
                                    // send to bidder
                                    Mail::to($user->email)->send(new ActiveBidderMail($user->name));
                                    $note = 'Email for active bidder sent to: ' . $user->name . ' (' . $user->email . ')';
                                    $log_item = new LogItem();
                                    $log_item->note = $note;
                                    $log_item->save();
                                }
                            }
                        }

                        // send text to next (now the current/active) bidder
                        $param_next_bidder_text_on_or_off = Param::where('param_name','next-bidder-text-on-or-off')->first()->string_value;
                        if(isset($param_next_bidder_text_on_or_off)){
                            if($param_next_bidder_text_on_or_off == 'on'){
                                $param_all_text_to_test_phone_on_or_off = Param::where('param_name','all-text-to-test-phone-on-or-off')->first()->string_value;
                                if($param_all_text_to_test_phone_on_or_off == 'on'){
                                    $param_text_test_phone = Param::where('param_name','text-test-phone')->first()->string_value;
                                    if(isset($param_text_test_phone)){
                                        if(strlen($param_text_test_phone) > 0){
                                            // send text to test phone number
                                            // LaraTwilio::notify($param_text_test_phone, 'TEST: Hello '. $user->name . ' - You can bid now, you are the active bidder.  Login at: ' . config('extra.login_url') . ' or call: ' . config('extra.app_bid_phone'));
                                            LaraTwilio::notify($param_text_test_phone, 'TEST: Hello '. $user->name . ' - You can bid now, you are the active bidder.  Call: ' . config('extra.app_bid_phone') . ", or attend the Boardroom if you are on site.);
                                        }
                                    }
                                } else {
                                    // send to bidder, if they have a number
                                    if (isset($user->phone_number)){
                                        if (strlen($user->phone_number)>0){
                                            // LaraTwilio::notify($user->phone_number, 'Hello '. $user->name . ' - You can bid now, you are the active bidder.  Login at: ' . config('extra.login_url') . ' or call: ' . config('extra.app_bid_phone'));
                                            LaraTwilio::notify($user->phone_number, 'Hello '. $user->name . ' - You can bid now, you are the active bidder.  Call: ' . config('extra.app_bid_phone') . ", or attend the Boardroom if you are on site.);
                                            $note = 'Text for active bidder sent to: ' . $user->name . ' (' . $user->phone_number . ')';
                                            $log_item = new LogItem();
                                            $log_item->note = $note;
                                            $log_item->save();
                                        }
                                    }
                                }
                            }
                        }

                        // look for a following bidder, skipping the one above...
                        $skip_ids[] = $user->id;
                        $user2 = User::whereNotIn('id',$skip_ids)->where('has_bid',0)->where('bid_order','>',0)->orderBy('bid_order')->first();
                        if(isset($user2) ){

                            // send email to next bidder?
                            $param_next_bidder_email_on_or_off = Param::where('param_name','next-bidder-email-on-or-off')->first()->string_value;
                            if(isset($param_next_bidder_email_on_or_off)){
                                if($param_next_bidder_email_on_or_off == 'on'){
                                    $param_all_email_to_test_address_on_or_off = Param::where('param_name','all-email-to-test-address-on-or-off')->first()->string_value;
                                    if($param_all_email_to_test_address_on_or_off == 'on'){
                                        $param_email_test_address = Param::where('param_name','email-test-address')->first()->string_value;
                                        if(isset($param_email_test_address)){
                                            if(strlen($param_email_test_address) > 0){
                                                // send mail to test address
                                                Mail::to($param_email_test_address)->send(new NextBidderTestMail($user2->name));
                                            }
                                        }
                                    } else {
                                        // send to bidder
                                        Mail::to($user2->email)->send(new NextBidderMail($user2->name));
                                        $note = 'Email for "next" bidder sent to: ' . $user2->name . ' (' . $user2->email . ')';
                                        $log_item = new LogItem();
                                        $log_item->note = $note;
                                        $log_item->save();
                                    }
                                }
                            }

                            // send text to next bidder?
                            $param_next_bidder_text_on_or_off = Param::where('param_name','next-bidder-text-on-or-off')->first()->string_value;
                            if(isset($param_next_bidder_text_on_or_off)){
                                if($param_next_bidder_text_on_or_off == 'on'){
                                    $param_all_text_to_test_phone_on_or_off = Param::where('param_name','all-text-to-test-phone-on-or-off')->first()->string_value;
                                    if($param_all_text_to_test_phone_on_or_off == 'on'){
                                        $param_text_test_phone = Param::where('param_name','text-test-phone')->first()->string_value;
                                        if(isset($param_text_test_phone)){
                                            if(strlen($param_text_test_phone) > 0){
                                                // send text to test phone number
                                                LaraTwilio::notify($param_text_test_phone, 'TEST: Hello '. $user2->name . ' - You will be able to bid soon. You will be notified when the current bidder is done.');
                                            }
                                        }
                                    } else {
                                        // send to bidder, if they have a number
                                        if (isset($user2->phone_number)){
                                            if (strlen($user2->phone_number)>0){
                                                LaraTwilio::notify($user2->phone_number, 'Hello '. $user2->name . ' - You will be able to bid soon. You will be notified when the current bidder is done.');
                                                $note = 'Text for "next" bidder sent to: ' . $user2->name . ' (' . $user2->phone_number . ')';
                                                $log_item = new LogItem();
                                                $log_item->note = $note;
                                                $log_item->save();
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        // log
                        $log_item = new LogItem();
                        $log_item->note = 'Continue bidding';
                        $log_item->save();

                        flash('Bidding Continued...')->success();
                        return view('admins.dashBidding');
                    }
                }
            }
        } else {
            abort('401');
        }
    }

    public function tieclear()  // clears seniority tie-breakers
    {
        if (Auth::user()->hasRole('admin')){

            $state_param = Param::where('param_name','bidding-state')->first();
            $test = $state_param->string_value;
            if ( $test == 'running'){
                // do nothing, except complain
                flash('Unable to reset!')->warning()->important();
                return redirect()->route('admins.dashBidding');
            } else {

                // log start
                $log_item = new LogItem();
                $log_item->note = 'Start seniority tie-breaker reset';
                $log_item->save();

                $users = User::get();
                foreach($users as $user){
                    $user->update(['bidder_tie_breaker' => null]);
                }

                // log done
                $log_item = new LogItem();
                $log_item->note = 'Finish  seniority tie-breaker reset';
                $log_item->save();

                flash('Seniority tie-breakers have been RESET!')->success();
                return view('admins.dashBidding');
            }
        } else {
            abort('401');
        }
    }





}
