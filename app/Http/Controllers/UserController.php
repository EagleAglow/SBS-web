<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use App\User;
use App\BidderGroup;
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
        $my_sort = $request['my_sort'];
        if(!isset($my_sort)){
            $my_sort = 'alpha';
        }

        if ($my_sort == 'alpha'){
            $users = User::all()->sortBy('name'); 
        } else {
            if ($my_sort == 'seniority'){
                $users = User::all()->sortBy('seniority_date'); 
            } else {
                $users = User::all()->sortBy('bid_order'); 
            }
        }
        return view('users.index')->with(['users'=> $users,'my_sort'=>$my_sort]);
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
    public function showshow(Request $request, $id) {
        $my_sort = $request['my_sort'];
        if(!isset($my_sort)){
            $my_sort = 'alpha';
        }

        return redirect('users')->with(['my_sort'=>$my_sort]); 
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

        if (isset($roles)) {        
            $user->roles()->sync($roles);  //If any role is selected associate user to roles          
        }        
        else {
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