<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Param;
use App\ScheduleLine;
use App\Role;

use Illuminate\Support\Facades\Mail;
use App\Mail\NextBidderMail;
use App\Mail\NextBidderTestMail;

use App\LogItem;
use DB;

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
            // build cross ref arrays (will include 'bidder-active', but skipped below )
            $bidder_roles = DB::table('roles')->where('name','like', 'bidder-%')->get('name');
            $bidder_group_xref = array();
            foreach($bidder_roles as $bidder_role){
                // skip 'bidder-active'
                if ($bidder_role->name != 'bidder-active'){
                    $bidder_group_xref[ strtoupper(str_replace('bidder-',"",$bidder_role->name)) ] = $bidder_role->name;
                }
            }
            $users = User::all();
            foreach($users as $user){
                // see if user has a bidder role
                $user_roles = $user->roles;
                $is_bidder = false;
                foreach($user_roles as $user_role){
                    if ( str_starts_with($user_role->name,'bidder-') ){
                        $is_bidder = true;
                        break;
                    }
                }
                if ($is_bidder){
                    // do they have a bidding group?
                    $bg = $user->bidder_group->code;
                    if (!isset($bg)){
                        // this user has no bidding group code, remove all roles that begin with 'bidder-' (already collected, above)
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
                            if ( str_starts_with($user_role->name,'bidder-') ){
                                // skip 'bidder-active'
                                if ($user_role->name != 'bidder-active'){
                                    array_push($user_bidrole_list, $user_role->name);
                                }
                            }
                        }

                        // if this user is the active bidder, skip check, just assume data is OK
                        // really don't want to (or need to) change that role in the middle of bidding
                        if (!$user->hasRole('bidder-active')){
                            // does this user have the bidding role(s) that goes with the code?
                            if ($bg == 'TRAFFIC'){
                                // user should have only two bidder roles 'bidder-tnon' and 'bidder-tcom'
                                if ( !( ((count($user_bidrole_list)) == 2) and (in_array('bidder-tnon', $user_bidrole_list)) and (in_array('bidder-tnon', $user_bidrole_list)) ) ){
                                    // fix mismatch - remove bidding roles, then restore tcom and tnon
                                    foreach($bidder_roles as $bidder_role){
                                        if ($user->hasRole($bidder_role->name)){
                                            $user->removeRole($bidder_role->name);
                                        }
                                    }
                                    $user->assignRole('bidder-tnon');
                                    $user->assignRole('bidder-tcom');
                                    // log action
                                    $log_item = new LogItem();
                                    $log_item->note = 'Set bidding roles for TRAFFIC bidder (' . $user->name . ')';
                                    $log_item->save();
                                }
                            } else {
                                if ($bg == 'NONE'){
                                    // user should have no bidder roles, remove any
                                    foreach($bidder_roles as $bidder_role){
                                        if ($user->hasRole($bidder_role->name)){
                                            $user->removeRole($bidder_role->name);
                                        }
                                    }
                                    // log action
                                    $log_item = new LogItem();
                                    $log_item->note = 'Remove bidding roles for ' . $user->bidder_group->code . ' bidder (' . $user->name . ')';
                                    $log_item->save();
                                } else {
                                    // user should have only one role, that matches bidder group
                                    if ( !( ((count($user_bidrole_list)) == 1) and (in_array('bidder-' . strtolower($user->bidder_group->code), $user_bidrole_list)) ) ){
                                        // fix mismatch - remove bidding roles, then restore correct one
                                        foreach($bidder_roles as $bidder_role){
                                            if ($user->hasRole($bidder_role->name)){
                                                $user->removeRole($bidder_role->name);
                                            }
                                        }
                                        $user->assignRole($bidder_group_xref[$user->bidder_group->code]);
                                        // log action
                                        $log_item = new LogItem();
                                        $log_item->note = 'Set bidding roles for ' . $user->bidder_group->code . ' bidder (' . $user->name . ')';
                                        $log_item->save();
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // log start bid order fix
            $log_item = new LogItem();
            $log_item->note = 'Begin bid order fix';
            $log_item->save();

            // fix up any blank seniority (primary order) entries - set them tied to bid last
            // get last bidder
            $last_person_number = User::all()->sortByDesc('bidder_primary_order')->first()->bidder_primary_order;
            // process any null entries, but only for bidders
            $users = User::whereNull('bidder_primary_order')->get();
            foreach($users as $user){
                // see if user has a bidder role
                $user_roles = $user->roles;
                $is_bidder = false;
                foreach($user_roles as $user_role){
                    if ( str_starts_with($user_role->name,'bidder-') ){
                        $is_bidder = true;
                        break;
                    }
                }
                if ($is_bidder){
                    $user->bidder_primary_order = $last_person_number +1;
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
                    if ( str_starts_with($user_role->name,'bidder-') ){
                        $is_bidder = true;
                        break;
                    }
                }
                if ($is_bidder){
                    $ties = User::where('bidder_primary_order',$user->bidder_primary_order)->where('bidder_secondary_order',$user->bidder_secondary_order)->get();
                    if(count($ties) > 1 ){
                        // assign a random tie-breaker number to every user with this primary order number
                        foreach($ties as $tie){
                            $x = mt_rand(1,100000);
                            $tie->update(['bidder_secondary_order' => $x]);
                            // log action
                            $log_item = new LogItem();
                            $log_item->note = 'Bidding Tie: ' . $tie->name . ' (Seniority=' . $tie->bidder_primary_order . '), Tie-breaker set to: ' . $x;
                            $log_item->save();
                        }
                    }
                }
            }

            // log step
            $log_item = new LogItem();
            $log_item->note = 'Done assigning random tie-breaker values, begin setting bid order';
            $log_item->save();

            // set actual bid_order according to primary and secondary order
            // sortBy doesn't seem to work for this, with Users model?
            // collection returned by DB does not have role/permission link, nor update function
            // so, a combination of both
            $users = DB::table('users')->orderBy('bidder_primary_order')->orderBy('bidder_secondary_order')->get();
            $bidder_count = 1;
            foreach($users as $user){
                $u = User::find($user->id);
                // see if this user has a bidder role
                $u_roles = $u->roles;
                $is_bidder = false;
                foreach($u_roles as $u_role){
                    if ( str_starts_with($u_role->name,'bidder-') ){
                        $is_bidder = true;
                        break;
                    }
                }
                if ($is_bidder){
                    $secondary = $user->bidder_secondary_order;
                    if (!isset($secondary)){
                        $secondary = 'None';
                    }
                    $primary = $user->bidder_primary_order;
                    $note = 'Set Bid Order: ' . $user->name . ' (Seniority=' . $primary . ', Tie-breaker=' . $secondary . '), bid order set to: ' . $bidder_count;
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
    // sets parameters: 'bidding-next' to 1
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

                // set actual bid_order according to primary and secondary order, remove active bidder role
                // sortBy doesn't seem to work for this, with Users model?
                // collection returned by DB does not have role/permission link, nor update function
                // so, a combination of both
                $users = DB::table('users')->orderBy('bidder_primary_order')->orderBy('bidder_secondary_order')->get();
                $bidder_count = 1;
                foreach($users as $user){
                    $u = User::find($user->id);
                    // see if this user has a bidder role
                    $u_roles = $u->roles;
                    $is_bidder = false;
                    foreach($u_roles as $u_role){
                        if ( str_starts_with($u_role->name,'bidder-') ){
                            $is_bidder = true;
                            break;
                        }
                    }
                    if ($is_bidder){
                        $primary = $user->bidder_primary_order;
                        $secondary = $user->bidder_secondary_order;
                        $note = 'Confirm Bid Order: ' . $user->name . ' (seniority/tie-breaker=' . $primary . '/' . $secondary . '), bid order set to: ' . $bidder_count;
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
                // reset parameters
                $state_param = Param::where('param_name','bidding-state')->first();
                $state_param->update(['string_value' => 'ready']);
                $next_param = Param::where('param_name','bidding-next')->first();
                $next_param->update(['integer_value' => 1]);

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
    // assigns role: 'bidder-active' to bidder number 1
    
    {
        if (Auth::user()->hasRole('admin')){

            $state_param = Param::where('param_name','bidding-state')->first();
            $test = $state_param->string_value;
            if ($test <> 'ready') {
                // do nothing, except complain
                flash('Unable to start!')->warning()->important();
                return redirect()->route('admins.dashBidding');
            } else {

                // give bidding role to first bidder
                $user = User::where('bid_order',1)->first();
                $user->assignRole('bidder-active');
                // set parameter
                $state_param = Param::where('param_name','bidding-state')->first();
                $state_param->update(['string_value' => 'running']);
                $next_param = Param::where('param_name','bidding-next')->first();
                $next_param->update(['integer_value' => 1]);


                // send email to this bidder?
                $param_next_bidder_email_on_or_off = Param::where('param_name','next-bidder-email-on-or-off')->first()->string_value;
                if(isset($param_next_bidder_email_on_or_off)){
                    if($param_next_bidder_email_on_or_off == 'on'){
                        $param_all_email_to_test_address_on_or_off = Param::where('param_name','all-email-to-test-address-on-or-off')->first()->string_value;
                        if($param_all_email_to_test_address_on_or_off == 'on'){
                            $param_email_test_address = Param::where('param_name','email-test-address')->first()->string_value;
                            if(isset($param_email_test_address)){
                                if(strlen($param_email_test_address) > 0){
                                    // send mail to test address
                                    Mail::to($param_email_test_address)->send(new NextBidderTestMail($user->name));
                                }
                            }
                        } else {
                            // send to bidder
                            Mail::to($user->email)->send(new NextBidderMail($user->name));     
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

                // set parameter
                $state_param = Param::where('param_name','bidding-state')->first();
                $state_param->update(['string_value' => 'paused']);

                // log
                $log_item = new LogItem();
                $log_item->note = 'Pause bidding';
                $log_item->save();

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

                // set parameter
                $state_param = Param::where('param_name','bidding-state')->first();
                $state_param->update(['string_value' => 'running']);

                // log
                $log_item = new LogItem();
                $log_item->note = 'Continue bidding';
                $log_item->save();

                flash('Bidding Continued...')->success();
                return view('admins.dashBidding');
            }
        } else {
            abort('401');
        }
    }

}
