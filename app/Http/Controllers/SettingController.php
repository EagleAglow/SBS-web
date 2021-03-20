<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use App\Param;

use Session;

use App\User;
use Notification;
use Illuminate\Notifications\Notifiable;

use Dotunj\LaraTwilio\Facades\LaraTwilio;  // SMS messaging
use Illuminate\Support\Facades\Mail;
use App\Mail\BulkTestMail;
use App\Mail\BulkMail;

use App\Rules\DummyFail;

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

    /**
     * Turn on "use test text"
     *
     * @return \Illuminate\Http\Response
     */
    public function testtexton()
    {
        if (Auth::user()->hasRole('admin')){
            // do we have an address?
            $param = Param::where('param_name','text-test-phone')->first();
            if (strlen($param->string_value)>0){
                $param = Param::where('param_name','all-text-to-test-phone-on-or-off')->first();
                $param->string_value = 'on';
                $param->save();
                flash('Bidding texts to test phone is ON!')->success();
            } else {
                flash('Missing phone number!')->error();
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
    public function testtextoff()
    {
        if (Auth::user()->hasRole('admin')){

            $param = Param::where('param_name','all-text-to-test-phone-on-or-off')->first();
            $param->string_value = 'off';
            $param->save();

            flash('Bidding texts to test phone is OFF!')->success();
            return view('admins.settings.index');
        } else {
            abort('401');
        }
    }

    public function testtextsetphone(Request $request) {
        if (Auth::user()->hasRole('admin')){

            $action = $request->action;
            if (isset($action)){
                if ($action == 'set'){
                    $phone = $request->phone;
                    if (isset($phone)){
                        //Validate for ten digits - error if not
                        if(!preg_match("/\d{10}/",$phone)) {
                            // dummy validation function - if called, just returns message
                            $this->validate($request, [ 
                                'phone'=>new DummyFail( 'Number should be 10 digits!')
                            ]);
                        }

                        // set 'text-test-address'
                        $param = Param::where('param_name','text-test-phone')->first();
                        $param->string_value = $phone;
                        $param->save();
                        flash('Test texting phone set successfully.')->success();
                    } else {
                        flash('Failed to set test texting phone number.')->error()->important();
                    }
                } else {
                    if ($action == 'clear'){
                        // clear 'email-test-address'
                        $param = Param::where('param_name','text-test-phone')->first();
                        $param->string_value = '';
                        $param->save();
                        // turn off using test address
                        $param = Param::where('param_name','all-text-to-test-phone-on-or-off')->first();
                        $param->string_value = 'off';
                        $param->save();
                        flash('Test texting phone number cleared, and bidding texting to test phone number is OFF.')->success();
                    } else {
                        flash('Programmer error: No action.')->warning()->important();
                    }
                }
            }

            return view('admins.settings.index');
        } else {
            abort('401');
        }
    }


    /**
     * Turn on "auto-bidding"
     *
     * @return \Illuminate\Http\Response
     */
    public function autobidon()
    {
        if (Auth::user()->hasRole('admin')){
            $param = Param::where('param_name','autobid-on-or-off')->first();
            $param->string_value = 'on';
            $param->save();

            flash('Auto-bidding is ON!')->success();
            return view('admins.settings.index');
        } else {
            abort('401');
        }
    }

    /**
     * Turn off auto-bidding"
     *
     * @return \Illuminate\Http\Response
     */
    public function autobidoff()
    {
        if (Auth::user()->hasRole('admin')){

            $param = Param::where('param_name','autobid-on-or-off')->first();
            $param->string_value = 'off';
            $param->save();

            flash('Auto-bidding is OFF!')->success();
            return view('admins.settings.index');
        } else {
            abort('401');
        }
    }
/*

    /**
     * Turn on next bidder text
     *
     * @return \Illuminate\Http\Response
     */
    public function nextbiddertexton()
    {
        if (Auth::user()->hasRole('admin')){

            $param = Param::where('param_name','next-bidder-text-on-or-off')->first();
            $param->string_value = 'on';
            $param->save();
    
            flash('Texting to next bidder is ON!')->success();
            return view('admins.settings.index');
        } else {
            abort('401');
        }
    }

    /**
     * Turn off next bidder text
     *
     * @return \Illuminate\Http\Response
     */
    public function nextbiddertextoff()
    {
        if (Auth::user()->hasRole('admin')){

            $param = Param::where('param_name','next-bidder-text-on-or-off')->first();
            $param->string_value = 'off';
            $param->save();
    
            flash('Texting to next bidder is OFF!')->success();
            return view('admins.settings.index');
        } else {
            abort('401');
        }
    }


    public function sendbulktext(Request $request) {
        if (Auth::user()->hasRole('admin')){

            $this->validate($request, [
                'bulktextmsg'=>'required',
            ]);
            $bulktextmsg = $request->bulktextmsg;
            $count = 0;
            $flash_msg = 'Error: No text sent!';
            // send conditions
            $param_all_text_to_test_phone_on_or_off = Param::where('param_name','all-text-to-test-phone-on-or-off')->first()->string_value;
            if($param_all_text_to_test_phone_on_or_off == 'on'){
                $param_text_test_phone = Param::where('param_name','text-test-phone')->first()->string_value;
                if(isset($param_text_test_phone)){
                    if(strlen($param_text_test_phone) > 0){
                        // send text to test phone number
                        LaraTwilio::notify($param_text_test_phone, 'TEST: Hello LASTNAME, Firstname - ' . $bulktextmsg);
                        $flash_msg = 'Text sent to test phone!';
                    }
                }
            } else {
                // send to each user, if they have a number
                $users = User::select('name','phone_number')->where('phone_number','>','0')->get();
                foreach ($users as $user){
                    if (isset($user->phone_number)){
                        if (strlen($user->phone_number)>0){
                            LaraTwilio::notify($user->phone_number, 'Hello '. $user->name . ' - ' . $bulktextmsg);
                            $count = $count +1;
                        }
                    }
                }
                if ($count == 0){
                    $flash_msg = 'Error: No text sent to users!';
                } else {
                    $flash_msg = $count . ' texts sent to users!';
                }
            }
            if (strpos($flash_msg,'Error')>0){
                flash($flash_msg)->warning()->important();
            } else {
                flash($flash_msg)->success();
            }
            return view('admins.settings.index');
        } else {
            abort('401');
        }
    }


    public function sendbulkmail(Request $request) {
        if (Auth::user()->hasRole('admin')){

            $this->validate($request, [
                'bulkmailmsg'=>'required',
            ]);
            $bulkmailmsg = $request->bulkmailmsg;
            $count = 0;
            $flash_msg = 'Error: No mail sent!';
            // send conditions
            $param_all_email_to_test_address_on_or_off = Param::where('param_name','all-email-to-test-address-on-or-off')->first()->string_value;
            if($param_all_email_to_test_address_on_or_off == 'on'){
                $param_email_test_address = Param::where('param_name','email-test-address')->first()->string_value;
                if(isset($param_email_test_address)){
                    if(strlen($param_email_test_address) > 0){
                        // send mail to test address
                        Mail::to($param_email_test_address)->send(new BulkTestMail('LASTNAME, Firstname', $bulkmailmsg));
                        $flash_msg = 'Mail sent to test address!';
                    }
                }
            } else {
                // send to each user, guaranteed to have an address
                $users = User::select('name','email')->get();
                foreach ($users as $user){
                    Mail::to($user->email)->send(new BulkMail($user->name, $bulkmailmsg));
                    $count = $count +1;
                }
                if ($count == 0){
                    $flash_msg = 'Error: No mail sent to users!';
                } else {
                    $flash_msg = $count . ' emails sent to users!';
                }
            }
            if (strpos($flash_msg,'Error')>0){
                flash($flash_msg)->warning()->important();
            } else {
                flash($flash_msg)->success();
            }
            return view('admins.settings.index');
        } else {
            abort('401');
        }
    }

}