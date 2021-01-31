<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use App\Param;

use Session;


use App\User;
use Notification;
use Illuminate\Notifications\Notifiable;
use App\Notifications\NextBidderMail;
use App\Notifications\BidSelectionMail;



class SettingController extends Controller {

    public function __construct()
    {
        // verify logged in
        $this->middleware('auth');
        // to enable email verification in this controller
        //  $this->middleware(['auth','verified']);

    }

    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response 
    */
    public function index()
    {
        if (Auth::user()->hasRole('admin')){

            return view('admins.settings.index');
        } else {
            abort('401');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update()
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        //
    }


    /**
     * Turn on display of name on bidding page 
     *
     * @return \Illuminate\Http\Response
     */
    public function name()
    {
        if (Auth::user()->hasRole('admin')){

            $param = Param::where('param_name','name-or-taken')->first();
            $param->string_value = 'name';
            $param->save();
    
            flash('Set to show successful bidder name on bid page!')->success();
            return view('admins.settings.index');
        } else {
            abort('401');
        }
    }


    /**
     * Turn on display of "TAKEN" on bidding page 
     *
     * @return \Illuminate\Http\Response
     */
    public function taken()
    {
        if (Auth::user()->hasRole('admin')){

            $param = Param::where('param_name','name-or-taken')->first();
            $param->string_value = 'taken';
            $param->save();
    
            flash('Set to show "TAKEN" (instead of successful bidder name) on bid page!')->success();
            return view('admins.settings.index');
        } else {
            abort('401');
        }
    }

    /**
     * Turn on next bidder email
     *
     * @return \Illuminate\Http\Response
     */
    public function nextbidderemailon()
    {
        if (Auth::user()->hasRole('admin')){

            $param = Param::where('param_name','next-bidder-email-on-or-off')->first();
            $param->string_value = 'on';
            $param->save();
    
            flash('Email to next bidder is ON!')->success();
            return view('admins.settings.index');
        } else {
            abort('401');
        }
    }

    /**
     * Turn off next bidder email
     *
     * @return \Illuminate\Http\Response
     */
    public function nextbidderemailoff()
    {
        if (Auth::user()->hasRole('admin')){

            $param = Param::where('param_name','next-bidder-email-on-or-off')->first();
            $param->string_value = 'off';
            $param->save();
    
            flash('Email to next bidder is OFF!')->success();
            return view('admins.settings.index');
            return view('admins.settings.index');
        } else {
            abort('401');
        }
    }

    /**
     * Turn on "bid accepted" email
     *
     * @return \Illuminate\Http\Response
     */
    public function bidacceptedemailon()
    {
        if (Auth::user()->hasRole('admin')){

            $param = Param::where('param_name','bid-accepted-email-on-or-off')->first();
            $param->string_value = 'on';
            $param->save();
    
            flash('Email after accepted bid is ON!')->success();
            return view('admins.settings.index');
        } else {
            abort('401');
        }
    }

    /**
     * Turn off "bid accepted" email
     *
     * @return \Illuminate\Http\Response
     */
    public function bidacceptedemailoff()
    {
        if (Auth::user()->hasRole('admin')){

            $param = Param::where('param_name','bid-accepted-email-on-or-off')->first();
            $param->string_value = 'off';
            $param->save();

            flash('Email after accepted bid is OFF!')->success();
            return view('admins.settings.index');
        } else {
            abort('401');
        }
    }

    /**
     * Turn on "use test email"
     *
     * @return \Illuminate\Http\Response
     */
    public function testmailon()
    {
        if (Auth::user()->hasRole('admin')){
            // do we have an address?
            $param = Param::where('param_name','email-test-address')->first();
            if (strlen($param->string_value)>0){
                $param = Param::where('param_name','all-email-to-test-address-on-or-off')->first();
                $param->string_value = 'on';
                $param->save();
                flash('Bidding email to test address is ON!')->success();
            } else {
                flash('Missing test address!')->error();
            }
            return view('admins.settings.index');
        } else {
            abort('401');
        }
    }

    /**
     * Turn off "use test email"
     *
     * @return \Illuminate\Http\Response
     */
    public function testmailoff()
    {
        if (Auth::user()->hasRole('admin')){

            $param = Param::where('param_name','all-email-to-test-address-on-or-off')->first();
            $param->string_value = 'off';
            $param->save();

            flash('Bidding email to test address is OFF!')->success();
            return view('admins.settings.index');
        } else {
            abort('401');
        }
    }

    public function testmailsetaddress(Request $request) {
        if (Auth::user()->hasRole('admin')){

            $action = $request->action;
            if (isset($action)){
                if ($action == 'set'){
                    $email = $request->email;
                    if (isset($email)){
                        //Validate 
                        $this->validate($request, [
                            'email'=>'email',
                        ]);
        
                        // set 'email-test-address'
                        $param = Param::where('param_name','email-test-address')->first();
                        $param->string_value = $email;
                        $param->save();
                        flash('Test email address set successfully.')->success();
                    } else {
                        flash('Failed to set test email address.')->error();
                    }
                } else {
                    if ($action == 'clear'){
                        // clear 'email-test-address'
                        $param = Param::where('param_name','email-test-address')->first();
                        $param->string_value = '';
                        $param->save();
                        // turn off using test address
                        $param = Param::where('param_name','all-email-to-test-address-on-or-off')->first();
                        $param->string_value = 'off';
                        $param->save();
                        flash('Test email address cleared, and bidding email to test address is OFF.')->success();
                    } else {
                        flash('Programmer error: No action.')->warning();
                    }
                }
            }

            return view('admins.settings.index');
        } else {
            abort('401');
        }
    }



/* 
// code for sending email - save this!!!!!!!!!!!!!!!!!

            $user = User::where('email','randy@atomicwizard.com')->first();
            if (isset($user)){
//                $user->notify(new NextBidderMail());
                $user->notify(new BidSelectionMail($user->id));
            }

            // do we have an address?
            $param = Param::where('param_name','email-test-address')->first();
            if (strlen($param->string_value)>0){
                $param = Param::where('param_name','all-email-to-test-address-on-or-off')->first();
                $param->string_value = 'on';

                        // set 'email-test-address'
                        $param = Param::where('param_name','email-test-address')->first();
                        $param->string_value = $email;
                        $param->save();
                        if (count(Param::where('param_name','next-bidder-email-on-or-off')->get()) == 0){
                            $param = new Param();
                            $param->param_name = 'next-bidder-email-on-or-off';
                            $param->string_value = 'off';
                            $param->save();
                        }
            
                        if (count(Param::where('param_name','bid-accepted-email-on-or-off')->get()) == 0){
                            $param = new Param();
                            $param->param_name = 'bid-accepted-email-on-or-off';
                            $param->string_value = 'off';
            
*/


}
