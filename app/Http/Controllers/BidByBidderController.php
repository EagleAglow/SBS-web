<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\ShiftCode;
use App\ScheduleLine; 
use App\LineGroup;
use App\BidderGroup;
use App\Schedule; 
use App\User; 
use App\Param;
use App\Pick;
use App\LogItem;

use Illuminate\Support\Facades\Mail;
use App\Mail\NextBidderMail;
use App\Mail\NextBidderTestMail;
use App\Mail\ActiveBidderMail;
use App\Mail\ActiveBidderTestMail;
use App\Mail\BidSelectionMail;
use App\Mail\BidSelectionTestMail;

// SMS messaging
use Dotunj\LaraTwilio\Facades\LaraTwilio;  

class BidByBidderController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // BidByBidder middleware only passes active bidder
        $this->middleware(['auth', 'bidByBidder']);
    }
  

    /**
     * Show the bidder dashboard. 
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
//        if (Auth::user()->hasAnyRole('bid-for-demo','bid-for-irpa','bid-for-tsu','bid-for-oidp','bid-for-tcom','bid-for-tnon')){
        if (Auth::user()->hasPermission('bid-self')){
                return view('bidders.dash');
        } else {
            abort('401');
        }
    }


    // handle a bid as id it were an edit (which it is, for one item...)
    public function show($id) {
    
        if (Auth::user()->can('bid-now')){
            $schedule_line = ScheduleLine::findOrFail($id);
            $shifts = ShiftCode::all('id','name','begin_time','end_time'); //Get id, code, times for all shift codes
            $schedule = Schedule::findOrFail($schedule_line->schedule_id);
            $line_group = LineGroup::findOrFail($schedule_line->line_group_id);

            return view('bidder.bid', compact('schedule_line','shifts','schedule','line_group'));
        } else {
            abort('401');
        }
    }

    /**
    * Show the form for bidding
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function edit($id) {
        $schedule_line = ScheduleLine::findOrFail($id);
        $shifts = ShiftCode::all('id','name','begin_time','end_time'); //Get id, code, times for all shift codes
        $schedule = Schedule::findOrFail($schedule_line->schedule_id);
//        $request['schedule']=$schedule;

abort('401');  // test to see if we are hitting this


        return view('bidder.bid', compact('schedule_line','shifts','schedule'));
}
 
    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function update(Request $request, $id) {
        $schedule_line = ScheduleLine::findOrFail($id);

        $line = $request['line'];
        $schedule_id = $request['schedule_id'];
        $line_group_id = $request['line_group_id'];
        $action = 'update';

        // the following works, keep for fall back
        //        $this->validate($request, [ 
        //          'line'=>new DummyFail( 'Message passed to rule class')
        //        ]);

        $this->validate($request, ['comment'=>'required','line'=>'required|numeric', ]);
        $this->validate($request, [ 
            'line'=>new UniqueLineGroupSchedule( $line, $line_group_id, $schedule_id, $action )
        ]);

        $comment = $request['comment'];
        
        // checkboxes
        $blackout = $request['blackout'];
        $nexus = $request['nexus'];
        $barge = $request['barge'];
        $offsite = $request['offsite'];
        // form only sends values for 'checked'
        // checkboxes - mySQL stores 1/0 (tinyInt) for True/False
        if (isset($blackout)) { $blackout = 1; } else { $blackout = 0; }
        if (isset($nexus)) { $nexus = 1; } else { $nexus = 0; }
        if (isset($barge)) { $barge = 1; } else { $barge = 0; }
        if (isset($offsite)) { $offsite = 1; } else { $offsite = 0; }
        
        // get shift for each day
        for ($n = 1; $n <= 56; $n++) {
            $d = 'day_' . substr(('00' . $n),-2);
            $$d = $request[$d];
        }
        
        $schedule_line->line = $line;
        $schedule_line->schedule_id = $schedule_id;
        $schedule_line->line_group_id = $line_group_id;
        $schedule_line->comment = $comment;
        $schedule_line->blackout = $blackout;
        $schedule_line->nexus = $nexus;
        $schedule_line->barge = $barge;
        $schedule_line->offsite = $offsite;
        // get shift for each day
        for ($n = 1; $n <= 56; $n++) {
            $d = 'day_' . substr(('00' . $n),-2);
            $schedule_line->$d = $$d;
        }

        $schedule_line->save();

        flash('Schedule Line: '. $schedule_line->line.' updated!')->success();
        return redirect()->route('schedulelines.index'); 
    }


    // handle a bid as if it were an edit update (which it is, for one item...)
    public function setbid($id) {

        
        if (Auth::user()->can('bid-now')){
            $schedule_line = ScheduleLine::findOrFail($id);
            // set this user id for this schedule line
            $schedule_line->user_id = Auth::user()->id;
            // set date/time of bid on schedule line
            $when = date('Y-m-d g:i:s');
            $schedule_line->bid_at = $when;
            $schedule_line->save();

            // get next bidder (which is actually THIS bidder, at the moment)
            $next_param = Param::where('param_name','bidding-next')->first();
            $next = $next_param->integer_value;

            // remove active bidder role, mark 'has_bid'
            $user = User::where('bid_order', $next)->first();
            $user->removeRole('bidder-active');
            $user->update(['has_bid' => true]);

            // log bid
            $title = Schedule::findOrFail($schedule_line->schedule_id)->title;
            $line_code = LineGroup::findOrFail($schedule_line->line_group_id)->code;
            $note = 'Bid by: ' . $user->name . ' / Schedule:Group:Line = ' . $title . ':' . $line_code . ':' . $schedule_line->line;
            $log_item = new LogItem();
            $log_item->note = $note;
            $log_item->save();


            // send email to successful bidder?
            $bid_accepted_email_on_or_off = Param::where('param_name','bid-accepted-email-on-or-off')->first()->string_value;
            if(isset($bid_accepted_email_on_or_off)){
                if($bid_accepted_email_on_or_off == 'on'){
                    $param_all_email_to_test_address_on_or_off = Param::where('param_name','all-email-to-test-address-on-or-off')->first()->string_value;
                    if($param_all_email_to_test_address_on_or_off == 'on'){
                        $param_email_test_address = Param::where('param_name','email-test-address')->first()->string_value;
                        if(isset($param_email_test_address)){
                            if(strlen($param_email_test_address) > 0){
                                // send mail to test address
                                Mail::to($param_email_test_address)->send(new BidSelectionTestMail($user->name, $schedule_line->id));
                            }
                        }
                    } else {
                        // send to bidder
                        Mail::to($user->email)->send(new BidSelectionMail($user->name, $schedule_line->id));
                        $note = 'Email for completed bid sent to: ' . $user->name . ' (' . $user->email . ')';
                        $log_item = new LogItem();
                        $log_item->note = $note;
                        $log_item->save();
                    }
                }
            }

            // increment next bidder number
            $next = $next +1;

            // look for a bidder with that number
            $user = User::where('bid_order', $next)->first();
            if(isset($user) ){
                // set next bidder
                $next_param->update(['integer_value' => $next]);
                $user->assignRole('bidder-active');

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
                                    LaraTwilio::notify($param_text_test_phone, 'TEST: Hello '. $user->name . '- You can bid now, you are the active bidder.');
                                }
                            }
                        } else {
                            // send to bidder, if they have a number
                            if (isset($user->phone_number)){
                                if (strlen($user->phone_number)>0){
                                    LaraTwilio::notify($user->phone_number, 'Hello '. $user->name . '- You can bid now, you are the active bidder.');
                                    $note = 'Text for active bidder sent to: ' . $user->name . ' (' . $user->phone_number . ')';
                                    $log_item = new LogItem();
                                    $log_item->note = $note;
                                    $log_item->save();
                                }
                            }
                        }
                    }
                }

///// begin second up

                // look for a following bidder
                $user2 = User::where('bid_order', ($next +1))->first();
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
                                        LaraTwilio::notify($param_text_test_phone, 'TEST: Hello '. $user2->name . ' - You will be able to bid soon. You will be notified wihen the current bidder is done.');
                                    }
                                }
                            } else {
                                // send to bidder, if they have a number
                                if (isset($user2->phone_number)){
                                    if (strlen($user2->phone_number)>0){
                                        LaraTwilio::notify($user2->phone_number, 'Hello '. $user2->name . ' - You will be able to bid soon. You will be notified wihen the current bidder is done.');
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

////// end second up

            } else {
                // complete
                $next_param->update(['integer_value' => 1]);
                $state_param = Param::where('param_name','bidding-state')->first();
                $state_param->update(['string_value' => 'complete']);

                // log complete
                $log_item = new LogItem();
                $log_item->note = 'Bidding complete';
                $log_item->save();
            }
            // go back to bidder dash
            flash('Bid Accepted!')->success();
            return redirect()->route('bidders.dash'); 

        } else {
            abort('401');
        }
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
