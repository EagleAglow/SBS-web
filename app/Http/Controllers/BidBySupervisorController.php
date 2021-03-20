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
use App\LogItem;

use Illuminate\Support\Facades\Mail;
use App\Mail\NextBidderMail;
use App\Mail\NextBidderTestMail;
use App\Mail\ActiveBidderMail;
use App\Mail\ActiveBidderTestMail;
use App\Mail\BidSelectionMail;
use App\Mail\BidSelectionTestMail;

use Dotunj\LaraTwilio\Facades\LaraTwilio;  // SMS messaging

class BidBySupervisorController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // BidBySupervisor middleware only passes supervisor
        $this->middleware(['auth', 'bidBySupervisor']);
    }


    /**
     * Show the bidder dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (Auth::user()->hasRole('supervisor')){
            return view('supervisors.dash');
        } else {
            abort('401');
        }
    }

    // handle a bid as id it were an edit (which it is, for one item...)
    public function show($id) {
    
        if (Auth::user()->can('bid-agent')){
            $schedule_line = ScheduleLine::findOrFail($id);
            $shifts = ShiftCode::all('id','name','begin_time','end_time'); //Get id, code, times for all shift codes
            $schedule = Schedule::findOrFail($schedule_line->schedule_id);
            $line_group = LineGroup::findOrFail($schedule_line->line_group_id);

            return view('supervisors.bidfor', compact('schedule_line','shifts','schedule','line_group'));
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


        return view('supervisor.bidfor', compact('schedule_line','shifts','schedule'));
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
    public function setbidfor($id) {
        if (Auth::user()->can('bid-agent')){
            $schedule_line = ScheduleLine::findOrFail($id);
            // set active bidder user id for this schedule line
            if( count(User::role('bidder-active')->get('id')) > 0 ){
                $who = User::role('bidder-active')->get()->first();

                $schedule_line->user_id = $who->id;
                // set date/time of bid on schedule line
                $when = date('Y-m-d g:i:s');
                $schedule_line->bid_at = $when;
                $schedule_line->save();

                // get next bidder (which is actually THIS bidder, at the moment)
                $next_param = Param::where('param_name','bidding-next')->first();
                $next = $next_param->integer_value;

                // remove active bidder role, mark 'has_bid'
                $who = User::where('bid_order', $next)->first();
                $who->removeRole('bidder-active');
                $who->update(['has_bid' => true]);

                // log bid
                $title = Schedule::findOrFail($schedule_line->schedule_id)->title;
                $line_code = LineGroup::findOrFail($schedule_line->line_group_id)->code;
                $note = 'Bid for: ' . $who->name . ' / Schedule:Group:Line = ' . $title . ':' . $line_code . ':' . $schedule_line->line;
                $note = $note . ' (Bid by supervisor: ' . Auth::user()->name . ')';
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
                                    Mail::to($param_email_test_address)->send(new BidSelectionTestMail($who->name, $schedule_line->id));
                                }
                            }
                        } else {
                            // send to bidder
                            Mail::to($who->email)->send(new BidSelectionMail($who->name, $schedule_line->id));
                        }
                    }
                }

                // increment next bidder number
                $next = $next +1;

                // look for a bidder with that number
                $who = User::where('bid_order', $next)->first();
                if(isset($who) ){
                    // set next bidder 
                    $next_param->update(['integer_value' => $next]);
                    $who->assignRole('bidder-active');

                    // send email to next (now current) bidder
                    $param_next_bidder_email_on_or_off = Param::where('param_name','next-bidder-email-on-or-off')->first()->string_value;
                    if(isset($param_next_bidder_email_on_or_off)){
                        if($param_next_bidder_email_on_or_off == 'on'){
                            $param_all_email_to_test_address_on_or_off = Param::where('param_name','all-email-to-test-address-on-or-off')->first()->string_value;
                            if($param_all_email_to_test_address_on_or_off == 'on'){
                                $param_email_test_address = Param::where('param_name','email-test-address')->first()->string_value;
                                if(isset($param_email_test_address)){
                                    if(strlen($param_email_test_address) > 0){
                                        // send mail to test address
                                        Mail::to($param_email_test_address)->send(new ActiveBidderTestMail($who->name));
                                    }
                                }
                            } else {
                                // send to bidder
                                Mail::to($who->email)->send(new ActiveBidderMail($who->name));

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
                                        LaraTwilio::notify($param_text_test_phone, 'TEST: Hello '. $who->name . '- You can bid now, you are the active bidder.');
                                    }
                                }
                            } else {
                                // send to bidder, if they have a number
                                if (isset($who->phone_number)){
                                    if (strlen($who->phone_number)>0){
                                        LaraTwilio::notify($who->phone_number, 'Hello '. $who->name . '- You can bid now, you are the active bidder.');
                                    }
                                }
                            }
                        }
                    }

                    // look for a bidder with that number
                    $who2 = User::where('bid_order', ($next +1))->first();
                    if(isset($who2) ){

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
                                            Mail::to($param_email_test_address)->send(new NextBidderTestMail($who2->name));
                                        }
                                    }
                                } else {
                                    // send to bidder
                                    Mail::to($who2->email)->send(new NextBidderMail($who2->name));

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
                                            LaraTwilio::notify($param_text_test_phone, 'TEST: Hello '. $who2->name . ' - You will be able to bid soon. You will be notified wihen the current bidder is done.');
                                        }
                                    }
                                } else {
                                    // send to bidder, if they have a number
                                    if (isset($who2->phone_number)){
                                        if (strlen($who2->phone_number)>0){
                                            LaraTwilio::notify($who2->phone_number, 'Hello '. $who2->name . ' - You will be able to bid soon. You will be notified wihen the current bidder is done.');
                                        }
                                    }
                                }
                            }
                        }
                    }

                } else {
                    // complete
                    $next_param->update(['integer_value' => 1]);
                    $state_param = Param::where('param_name','bidding-state')->first();
                    $state_param->update(['string_value' => 'complete']);

                    // log bidding complete
                    $log_item = new LogItem();
                    $log_item->note = 'Bidding complete';
                    $log_item->save();

                }

                flash('Bid Accepted!')->success();
                return redirect()->route('supervisors.dash'); 
            } else {
                flash('Bid FAILED - No current bidder!')->warning()->important();
                return redirect()->route('supervisors.dash'); 
            }

        } else {
            abort('401');
        }
    }

    
}