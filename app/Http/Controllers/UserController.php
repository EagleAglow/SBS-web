<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use App\User;
use App\BidderGroup;
use App\Param;
use App\LogItem;
use Auth;
use DB;

//Importing laravel-permission models
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

//Enables us to output flash messaging
use Session;

// ad lib validaton
use App\Rules\DummyFail;

// SMS messaging
use Dotunj\LaraTwilio\Facades\LaraTwilio;  

// welcome mail
use Illuminate\Support\Facades\Mail;
use App\Mail\NewUserMail;

// bidder mail
use App\Mail\ActiveBidderMail;
use App\Mail\ActiveBidderTestMail;
use App\Mail\NextBidderMail;
use App\Mail\NextBidderTestMail;
use App\Mail\DeferredBidderMail;
use App\Mail\DeferredBidderTestMail;
use App\Mail\UndeferredBidderMail;
use App\Mail\UndeferredBidderTestMail;

class UserController extends Controller {

    public function __construct() {
        //  ManageUsers middleware only passes users who can edit users
        $this->middleware(['auth', 'manageUsers']);
    }

    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(Request $request) {
        //Get all users and pass it to the view
        // can't use "orderBy" with User collection, nested "sortBy" may not work
        $my_selection = $request['my_selection'];
        if(!isset($my_selection)){
            $my_selection = 'bid_order';
        }
        if (!isset($page)){
            $page = 1;
        }

        // display order choices: Alpha, seniority (date only), bid-order (actual field),
        // "g/s/t" bid order (bid group, seniority, tie-breaker)
        // also, selection for deferring bidders - show only active bidder and any deferred bidders
        if ($my_selection == 'bid_order'){
            $users = User::orderBy('bid_order')->paginate(25); 
        } else {
            if ($my_selection == 'alpha'){
                $users = User::orderBy('name')->paginate(25); 
            } else {
                if ($my_selection == 'seniority'){
                    $users = User::orderBy('seniority_date')->paginate(25); 
                } else {
                    if ($my_selection == 'a/db'){
                        $users = User::role(['bidder-active','flag-deferred'])->orderBy('bid_order')->paginate(25); 
                    } else {  // $my_selection = "g/s/t"
                        $users = User::join('bidder_groups','bidder_groups.id','=','users.bidder_group_id')->select('users.*','bidder_groups.order')->orderBy('order')->orderBy('seniority_date')->orderBy('bidder_tie_breaker')->paginate(25); 
                    }
                }
            }
        }
        return view('users.index')->with(['users'=> $users,'my_selection'=>$my_selection]);
    }

    /**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */

//  --- apprently, we need this, not sure why...
    public function create() {
    //Get all roles and pass it to the view
        $roles = Role::get();
        $groups = BidderGroup::get();
        return view('users.create', ['roles'=>$roles], ['groups'=>$groups]);
    }


    /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function store(Request $request) {
        //Validate name, email and password fields
        $this->validate($request, [
            'name'=>'required|max:120',
            'email'=>'required|email|unique:users',
            'seniority_date'=>'nullable|date',
        ]);

        //Validate phone number for ten digits - error if not
        $phone = $request['phone_number'];
        if (isset($phone)){
            if (strlen($phone)>0){
                if(!preg_match("/\d{10}/",$phone)) {
                    // dummy validation function - if called, just returns message
                    $this->validate($request, [ 
                        'phone'=>new DummyFail( 'Number should be 10 digits!')
                    ]);
                }
            }
        } else {
            $phone = '';
        }
        $request['phone_number'] = $phone;

        $bidder_group_id = $request['bidder_group_id']; 

        $pwd_in_request = $request->password;
        // hash password for storage 
        $request['password'] = Hash::make($pwd_in_request);

        //new user - generate a dummy password and put it in request
        $pw = User::generatePassword();
        $request['password'] = Hash::make($pw);

        // use name, email, bidder_group_id and password data from request
        $user = User::create($request->only('email', 'name', 'password', 'bidder_group_id', 'phone_number', 'seniority_date')); 

        $roles = $request['roles']; //Retrieving the roles field
        //Checking if a user role was selected (supervisor, admin, superuser)
        if (isset($roles)) {
            foreach ($roles as $role) {
            $role_r = Role::where('id', '=', $role)->firstOrFail();            
            $user->assignRole($role_r); //Assigning role to user
            }
        }        

        // assign user bidding roles based on selected bidding group
        if (isset($bidder_group_id)){
            $bidder_group = BidderGroup::where('id',$bidder_group_id)->first();
            $role_names = $bidder_group->getRoleNames();
            foreach ($role_names as $role_name) {
                $user->assignRole($role_name); //Assigning role to user
            }
        }

        // deal with welcome message options
        $welcome = $request['welcome'];
        $sms = $request['sms'];
        if (($welcome == 'welcome') Or ($sms == 'sms')){
            // Generate a new reset password token - need the same for both, if we do both
            $token = app('auth.password.broker')->createToken($user);
        
            if ($welcome == 'welcome'){
                // send mail
                Mail::to($user->email)->send(new NewUserMail($user->name, $user->email, $token));
            }
                
            if ($sms == 'sms'){
                // send SMS, if they have a number
                if (isset($user->phone_number)){
                    if (strlen($user->phone_number)>0){
                        $url= url(config('url').route('password.reset', ['email' => $user->email, $token ]));
                        $msg =  'Hello '. $user->name . '- You have just been added to this system, and in order to use it, ';
                        $msg = $msg . 'you need to set your password at this link: ';
                        $msg = $msg . $url;
                        LaraTwilio::notify($user->phone_number, $msg);
                    }
                }
            }
        }

        //Redirect to the users.index view and display message
        flash('User successfully added.')->success();
        return redirect()->route('users.index');
    }

    /**
    * Display the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function show(Request $request, $id) {
        $my_selection = $request['my_selection'];
        if(!isset($my_selection)){
            $my_selection = 'bid_order';
        }
        $page = $request['page'];
        if (!isset($page)){
            $page = 1;
        }

        if ($my_selection == 'bid_order'){
            $users = User::orderBy('bid_order')->paginate(25); 
        } else {
            if ($my_selection == 'alpha'){
                $users = User::orderBy('name')->paginate(25); 
            } else {
                if ($my_selection == 'seniority'){
                    $users = User::orderBy('seniority_date')->paginate(25); 
                } else {  // $my_selection = "s/t"
                    $users = User::orderBy('bidder_tie_breaker')->orderBy('seniority_date')->paginate(25); 
                }
            }
        }
        return view('users.index')->with(['users'=> $users,'my_selection'=>$my_selection]);
//        return redirect('users')->with(['my_selection'=>$my_selection, 'page'=>$page])->paginate(25); 
    }

    /**
    * Show the form for editing the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function edit($id) {
        $user = User::findOrFail($id); //Get user with specified id
        $roles = Role::get(); //Get all roles
        $groups = BidderGroup::all('id','code'); //Get id & code for all groups
        return view('users.edit', compact('user', 'roles', 'groups')); //pass user and roles data to view
    }

    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function update(Request $request, $id) {
        $user = User::findOrFail($id); //Get user specified by id
        $bidder_group_id = $request['bidder_group_id'];

        $pwd_in_request = $request->password;
        if (isset($pwd_in_request)){
            //Validate 
            $this->validate($request, [
                'name'=>'required|max:120',
//                'email'=>'required|email|unique:users,email,'.$id,

                'email'=>'required|email:rfc,filter|unique:users,email,'.$id,
                'password'=>'required|min:6|confirmed',
                'bid_order'=>'nullable|integer',
                'seniority_date'=>'nullable|date',
                'bidder_tie_breaker'=>'nullable|integer',
            ]);
        
            // hash password for storage
            $request['password'] = Hash::make($pwd_in_request);

            //Validate phone number for ten digits - error if not
            $phone = $request['phone_number'];
            if (isset($phone)){
                if (strlen($phone)>0){
                    if(!preg_match("/\d{10}/",$phone)) {
                        // dummy validation function - if called, just returns message
                        $this->validate($request, [ 
                            'phone_number'=>new DummyFail( 'Number should be 10 digits or blank!')
                        ]);
                    }
                }
            } else {
                $phone = '';
            }
            $request['phone_number'] = $phone;
        } else {
            // password field was empty
            // does a password hash for this email already exist?
            $pwd = $user->password;
            if(isset($pwd)){
                //Validate - skip password
                $this->validate($request, [
                    'name'=>'required|max:120',
                    'email'=>'required|email|unique:users,email,'.$id,
                    'bid_order'=>'nullable|integer',
                    'seniority_date'=>'nullable|date',
                    'bidder_tie_breaker'=>'nullable|integer',
                ]);
                // store it unchanged
                $request['password'] = $pwd;

                //Validate phone number for ten digits - error if not
                $phone = $request['phone_number'];
                if (isset($phone)){
                    if (strlen($phone)>0){
                        if(!preg_match("/\d{10}/",$phone)) {
                            // dummy validation function - if called, just returns message
                            $this->validate($request, [ 
                                'phone_number'=>new DummyFail( 'Number should be 10 digits or blank!')
                            ]);
                        }
                    }
                } else {
                    $phone = '';
                }
                $request['phone_number'] = $phone;

            } else {
                // should fail validation - should not actually get to this code, anyway...
                //Validate 
                $this->validate($request, [
                    'name'=>'required|max:120',
                    'email'=>'required|email|unique:users,email,'.$id,
                    'password'=>'required|min:6|confirmed',
                    'bid_order'=>'nullable|integer',
                    'seniority_date'=>'nullable|date',
                    'bidder_tie_breaker'=>'nullable|integer',
                ]);
        
                // hash password for storage
                $request['password'] = Hash::make($pwd_in_request);

                //Validate phone number for ten digits - error if not
                $phone = $request['phone_number'];
                if (isset($phone)){
                    if (strlen($phone)>0){
                        if(!preg_match("/\d{10}/",$phone)) {
                            // dummy validation function - if called, just returns message
                            $this->validate($request, [ 
                                'phone_number'=>new DummyFail( 'Number should be 10 digits or blank!')
                            ]);
                        }
                    }
                } else {
                    $phone = '';
                }
                $request['phone_number'] = $phone;
            }
        }

        // count number of superusers in system - test later to avoid removing last superuser
        $superusers = User::role('superuser')->get()->count();

        $input = $request->only(['name', 'email', 'password','bidder_group_id','bid_order', 'seniority_date',
            'bidder_tie_breaker', 'phone_number']); 

        $user->fill($input)->save();

        // Retrieve all 'checked' roles in request
        $roles = $request['roles'];
        // are there any active bidders already?
        $other_bidders = User::role('bidder-active')->get('name');

        // if this change would result in two active bidders, we need to block setting this user active bidder role
        $block_msg = false;
        if (isset($roles)){
            $block_id = Role::where('name','bidder-active')->get()->first()->id;   // id of bidder-active role

            if ( (!$user->hasRole('bidder-active')) and (in_array($block_id, $roles)) ){
                // request would add the active bidder role to this user
                if ( !count($other_bidders) == 0 ) {
                    // there is at least one active bidder already, remove that role from $roles array
                    if (($key = array_search($block_id, $roles)) !== false) {
                        unset($roles[$key]);
                        $block_msg = true;
                    }
                }
            }
        }

        // set up list of any active bidders
        $msg = '';
        if ($block_msg == true){
            $msg = 'Change to active bidder was blocked. Active bidder is ';
            foreach($other_bidders as $other_bidder){
                $msg = $msg . $other_bidder->name;
            } 
        }

        // if bidding is in progress or paused, handle changes to flag-deferred
        // if newly deferred or "un-deferred", notify user of state change
        // also, if active bidder, shift to next bidder and notify other bidders
        // if bidding is not in progress, no special handling, just update flag 'role'
        // Note: detecting flag change by comparing roles before/after "sync" does not work
        $state_param = Param::where('param_name','bidding-state')->first();
        $test = $state_param->string_value;
        if (($test == 'running') Or ($test == 'paused')) {
            // get flag state before edit
            if ($user->hasRole('flag-deferred')){
                $before_edit = 'Y';
            } else {
                $before_edit = 'N';
            }        
            // get flag state passed to controller
            $flag_id = Role::where('name','flag-deferred')->first()->id;
            // were any roles passed?
            if (isset($roles)){
                if (in_array($flag_id,$roles)){
                    $after_edit = 'Y';
                } else {
                    $after_edit = 'N';
                }
            } else {
                $after_edit = 'N';
            }
            if (($before_edit == 'N') And ($after_edit=='Y')){
                // newly deferred, bidding in progress

// add a pile of code similar to BidByBidder controller... =============================================================================================

                $user->assignRole('flag-deferred');

                // get next bidder (which is expected to be this user, if they are being deferred)
                // but need to handle case where they are not next bidder, also
                $next_param = Param::where('param_name','bidding-next')->first();
                $next = $next_param->integer_value;
                if ($user->bid_order == $next){
                    // remove active bidder role
                    $user->removeRole('bidder-active');
                }

                // log deferment
                $note = 'Bidder Deferred: ' . $user->name;
                $log_item = new LogItem();
                $log_item->note = $note;
                $log_item->save();


                // send email to deferred bidder?
                $param_next_bidder_email_on_or_off = Param::where('param_name','next-bidder-email-on-or-off')->first()->string_value;
                if(isset($param_next_bidder_email_on_or_off)){
                    if($param_next_bidder_email_on_or_off == 'on'){
                        $param_all_email_to_test_address_on_or_off = Param::where('param_name','all-email-to-test-address-on-or-off')->first()->string_value;
                        if($param_all_email_to_test_address_on_or_off == 'on'){
                            $param_email_test_address = Param::where('param_name','email-test-address')->first()->string_value;
                            if(isset($param_email_test_address)){
                                if(strlen($param_email_test_address) > 0){
                                    // send mail to test address

                                    Mail::to($param_email_test_address)->send(new DeferredBidderTestMail($user->name));
                                }
                            }
                        } else {
                            // send to deferred bidder

                            Mail::to($user->email)->send(new DeferredBidderMail($user->name));
                            $note = 'Email for deferred bidder sent to: ' . $user->name . ' (' . $user->email . ')';
                            $log_item = new LogItem();
                            $log_item->note = $note;
                            $log_item->save();
                        }
                    }
                }

                // send text to deferred bidder?
                $param_next_bidder_text_on_or_off = Param::where('param_name','next-bidder-text-on-or-off')->first()->string_value;
                if(isset($param_next_bidder_text_on_or_off)){
                    if($param_next_bidder_text_on_or_off == 'on'){
                        $param_all_text_to_test_phone_on_or_off = Param::where('param_name','all-text-to-test-phone-on-or-off')->first()->string_value;
                        if($param_all_text_to_test_phone_on_or_off == 'on'){
                            $param_text_test_phone = Param::where('param_name','text-test-phone')->first()->string_value;
                            if(isset($param_text_test_phone)){
                                if(strlen($param_text_test_phone) > 0){
                                    // send text to test phone number
                                    LaraTwilio::notify($param_text_test_phone, 'TEST: Hello '. $user->name . ' - Your bid time has passed! YOU CAN NOT BID NOW! CALL: ' . config('extra.app_bid_phone'));
                                }
                            }
                        } else {
                            // send to bidder, if they have a number
                            if (isset($user->phone_number)){
                                if (strlen($user->phone_number)>0){
                                    LaraTwilio::notify($user->phone_number, 'Hello '. $user->name . ' - Your bid time has passed! YOU CAN NOT BID NOW! CALL: ' . config('extra.app_bid_phone'));
                                    $note = 'Text for deferred bidder sent to: ' . $user->name . ' (' . $user->phone_number . ')';
                                    $log_item = new LogItem();
                                    $log_item->note = $note;
                                    $log_item->save();
                                }
                            }
                        }
                    }
                }

                // go to next bidder
                // get id list of bidders to skip
                $skip_ids = array();  //empty array for ids to skip
                $uids = User::role(['flag-snapshot','flag-deferred'])->select('id')->get();
                $skip_ids = array();
                foreach($uids as $uid){
                    $skip_ids[] = $uid->id;
                }

                // find next bidder, lowest bid order that has not bid, and not one to be skipped
                // variable "$user" is the one being edited, so the rest of this section will use "$bid_user"
                $bid_user = User::whereNotIn('id',$skip_ids)->where('has_bid',0)->where('bid_order','>',0)->orderBy('bid_order')->first();
                if(isset($bid_user) ){

                    // handle snapshot bidders (with bid orders before this bidder) that have not yet been "snapshotted"
                    $snap_users = User::role(['flag-snapshot'])->where('has_snapshot',0)->where('bid_order','<',$bid_user->bid_order)->select('id','bid_order')->orderBy('bid_order')->get();
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
                        // log
                        $log_item = new LogItem();
                        $log_item->note = 'Saved snapshot for: ' . $snap_user->name;
                        $log_item->save();
                    }

                    // set next bidder
                    $next = $bid_user->bid_order;
                    $next_param->update(['integer_value' => $next]);
                    $bid_user->assignRole('bidder-active');

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
                                        Mail::to($param_email_test_address)->send(new ActiveBidderTestMail($bid_user->name));
                                    }
                                }
                            } else {
                                // send to bidder
                                Mail::to($bid_user->email)->send(new ActiveBidderMail($bid_user->name));
                                $note = 'Email for active bidder sent to: ' . $bid_user->name . ' (' . $bid_user->email . ')';
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
                                        // LaraTwilio::notify($param_text_test_phone, 'TEST: Hello '. $bid_user->name . ' - You can bid now, you are the active bidder.  Login at: ' . config('extra.login_url') . ' or call: ' . config('extra.app_bid_phone'));
                                        LaraTwilio::notify($param_text_test_phone, 'TEST: Hello '. $bid_user->name . ' - You can bid now, you are the active bidder.  Call: ' . config('extra.app_bid_phone') . ", or attend the Boardroom if you are on site.);
                                    }
                                }
                            } else {
                                // send to bidder, if they have a number
                                if (isset($bid_user->phone_number)){
                                    if (strlen($bid_user->phone_number)>0){
                                        // LaraTwilio::notify($user->phone_number, 'Hello '. $bid_user->name . ' - You can bid now, you are the active bidder.  Login at: ' . config('extra.login_url') . ' or call: ' . config('extra.app_bid_phone'));
                                        LaraTwilio::notify($user->phone_number, 'Hello '. $bid_user->name . ' - You can bid now, you are the active bidder.  Call: ' . config('extra.app_bid_phone') . ", or attend the Boardroom if you are on site.);
                                        $note = 'Text for active bidder sent to: ' . $bid_user->name . ' (' . $bid_user->phone_number . ')';
                                        $log_item = new LogItem();
                                        $log_item->note = $note;
                                        $log_item->save();
                                    }
                                }
                            }
                        }
                    }

                    // look for a following bidder, skipping the one above...
                    $skip_ids[] = $bid_user->id;
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

                } else {

                    // handle left-over snapshot bidders that have not yet been "snapshotted"
                    $snap_users = User::role(['flag-snapshot'])->where('has_snapshot',0)->orderBy('bid_order')->get();
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

                    // also, snapshot any left-over deferred bidders - reusing variable names!!!
                    $snap_users = User::role(['flag-deferred'])->where('has_bid',0)->select('id','bid_order')->orderBy('bid_order')->get();
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
                        // log
                        $log_item = new LogItem();
                        $log_item->note = 'Saved snapshot for deferred bidder: ' . $snap_user->name;
                        $log_item->save();
                    }

                    // complete
                    $next_param->update(['integer_value' => 0]);
                    $state_param = Param::where('param_name','bidding-state')->first();
                    $state_param->update(['string_value' => 'complete']);

                    // log complete
                    $log_item = new LogItem();
                    $log_item->note = 'Bidding complete';
                    $log_item->save();
                }

// end of pile of code ================================================================================================================================

            }

            if (($before_edit == 'Y') And ($after_edit=='N')){
                // newly "un-deferred", bidding in progress
                // note: if bidding was completed before the "un-defer" edit, this bidder was included in snapshots
                $user->removeRole('flag-deferred');

                // log "un-deferment"
                $note = 'Bidder No Longer Deferred: ' . $user->name;
                $log_item = new LogItem();
                $log_item->note = $note;
                $log_item->save();

                // send email to un-deferred bidder?
                $param_next_bidder_email_on_or_off = Param::where('param_name','next-bidder-email-on-or-off')->first()->string_value;
                if(isset($param_next_bidder_email_on_or_off)){
                    if($param_next_bidder_email_on_or_off == 'on'){
                        $param_all_email_to_test_address_on_or_off = Param::where('param_name','all-email-to-test-address-on-or-off')->first()->string_value;
                        if($param_all_email_to_test_address_on_or_off == 'on'){
                            $param_email_test_address = Param::where('param_name','email-test-address')->first()->string_value;
                            if(isset($param_email_test_address)){
                                if(strlen($param_email_test_address) > 0){
                                    // send mail to test address

                                    Mail::to($param_email_test_address)->send(new UndeferredBidderTestMail($user->name));
                                }
                            }
                        } else {
                            // send to un-deferred bidder

                            Mail::to($user->email)->send(new UndeferredBidderMail($user->name));
                            $note = 'Email for no longer deferred bidder sent to: ' . $user->name . ' (' . $user->email . ')';
                            $log_item = new LogItem();
                            $log_item->note = $note;
                            $log_item->save();
                        }
                    }
                }

                // send text to un-deferred bidder?
                $param_next_bidder_text_on_or_off = Param::where('param_name','next-bidder-text-on-or-off')->first()->string_value;
                if(isset($param_next_bidder_text_on_or_off)){
                    if($param_next_bidder_text_on_or_off == 'on'){
                        $param_all_text_to_test_phone_on_or_off = Param::where('param_name','all-text-to-test-phone-on-or-off')->first()->string_value;
                        if($param_all_text_to_test_phone_on_or_off == 'on'){
                            $param_text_test_phone = Param::where('param_name','text-test-phone')->first()->string_value;
                            if(isset($param_text_test_phone)){
                                if(strlen($param_text_test_phone) > 0){
                                    // send text to test phone number
                                    LaraTwilio::notify($param_text_test_phone, 'TEST: Hello '. $user->name . ' - You have been re-entered into the bidding queue, and you will be able to bid soon. For information: ' . config('extra.app_bid_phone'));
                                }
                            }
                        } else {
                            // send to bidder, if they have a number
                            if (isset($user->phone_number)){
                                if (strlen($user->phone_number)>0){
                                    LaraTwilio::notify($user->phone_number, 'Hello '. $user->name . ' - You have been re-entered into the bidding queue, and you will be able to bid soon. For information: ' . config('extra.app_bid_phone'));
                                    $note = 'Text for deferred bidder sent to: ' . $user->name . ' (' . $user->phone_number . ')';
                                    $log_item = new LogItem();
                                    $log_item->note = $note;
                                    $log_item->save();
                                }
                            }
                        }
                    }
                }
            }
        }

        // handle roles (including bidding and flag 'roles')
        if (isset($roles)) {        
            $user->roles()->sync($roles);  //If any role is selected associate user to roles          
        } else {
            $user->roles()->detach(); //If no role is selected remove existing role associated to a user
        }

        // assign user bidding roles based on selected bidding group
        if (isset($bidder_group_id)){
            $bidder_group = BidderGroup::where('id',$bidder_group_id)->first();
            $role_names = $bidder_group->getRoleNames();
            foreach ($role_names as $role_name) {
                $user->assignRole($role_name); //Assigning role to user
            }
        }

        if ($superusers < '2'){ // we may have removed the last one
             // recount superusers in system
            $superusers = User::role('superuser')->get()->count();
            if ($superusers < '1'){ // we did - put it back!
                $user->assignRole('superuser');
                // complain
                flash('User successfully edited, except for removing only "superuser" permission from system. ' . $msg)->success();
                return redirect()->route('users.index');
            }
        }

        flash('User successfully edited. ' . $msg)->success();
        return redirect()->route('users.index');
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function destroy($id) {
        //Find a user with the given id
        $user = User::findOrFail($id); 

        // see if this user has 'superuser' role
        if ($user->hasRole('superuser')){
            // see if this is the only one
            $superusers = User::role('superuser')->get()->count();
            if ($superusers < '2'){ // this is the only user with the role
                // complain
                flash('User NOT deleted. You can not remove the only "superuser" permission from system.')->warning()->important();
                return redirect()->route('users.index');
            }
        }

        //delete user
        $user->delete();

        flash('User successfully deleted!')->success();
        return redirect()->route('users.index');
    }
}