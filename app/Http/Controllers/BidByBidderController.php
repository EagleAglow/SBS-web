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
use App\Snapshot;

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
        if (Auth::user()->hasPermission('bid-self')){
                return view('bidders.dash');
        } else {
            abort('401');
        }
    }


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
        
        $schedule_line->line = $line;
        $schedule_line->schedule_id = $schedule_id;
        $schedule_line->line_group_id = $line_group_id;
        $schedule_line->comment = $comment;
        $schedule_line->blackout = $blackout;
        $schedule_line->nexus = $nexus;
        $schedule_line->barge = $barge;
        $schedule_line->offsite = $offsite;
        $schedule_line->save();

        flash('Schedule Line: '. $schedule_line->line.' updated!')->success();
        return redirect()->route('schedulelines.index'); 
    }


    // handle a bid as if it were an edit update (which it is, for one item...)
    // except for "mirror" bidders - clone the schedule line, assign it to them, leave the original unchanged
    public function setbid($id) {
        if (Auth::user()->can('bid-now')){
            $schedule_line = ScheduleLine::findOrFail($id);

            // clone the line for mirror bidder
            if (Auth::user()->hasRole('flag-mirror')){
                $schedule_id = $schedule_line->schedule_id;
                $line_group_id = $schedule_line->line_group_id;
                $line = $schedule_line->line;
                $schedule_line_clone = new ScheduleLine();
                // need to set a unique line number/letter for the clone - append lowercase a, b, c, etc.
                // if already has a letter, start with next unused
                if ((ord(substr($line,-1)) >= 97) && (ord(substr($line,-1)) <= 122)) {
                    $chr_number = (ord(substr($line,-1)) );
                    $line_number = substr($line,0,(strlen($line) -1));
                } else {
                    $chr_number = 97;  // start here, produces lowercase "a"
                    $line_number = $line;
                }
        
                do {
                    $test_line = $line_number . chr($chr_number);
                    $matches = ScheduleLine::where('schedule_id',$schedule_id)->where('line_group_id',$line_group_id)->where('line',$test_line)->count();
                    $chr_number = $chr_number +1;    
                } while (($matches != 0) && ($chr_number < (97 + 25)));
        
                if ($chr_number >= (97 + 25)){
                    // failed
                    flash('Bid Failed! (Unable to clone original line)')->danger()->important();
                    return redirect()->route('bidders.dash'); 
                }
        
                $schedule_line_clone->line = $test_line;
                $schedule_line_clone->line_natural = ScheduleLine::natural($test_line);
                $schedule_line_clone->schedule_id = $schedule_line->schedule_id;
                $schedule_line_clone->line_group_id = $schedule_line->line_group_id;
                $schedule_line_clone->comment = $schedule_line->comment . ', Mirror Of Line ' . $line;
                $schedule_line_clone->blackout = $schedule_line->blackout;
                $schedule_line_clone->nexus = $schedule_line->nexus;
                $schedule_line_clone->barge = $schedule_line->barge;
                $schedule_line_clone->offsite = $schedule_line->offsite;
                // set this user id for this cloned schedule line
                $schedule_line_clone->user_id = Auth::user()->id;
                // set date/time of bid on schedule line
                $when = date('Y-m-d g:i:s');
                $schedule_line_clone->bid_at = $when;
                $schedule_line_clone->save();
            } else {
                // set this user id for this schedule line
                $schedule_line->user_id = Auth::user()->id;
                // set date/time of bid on schedule line
                $when = date('Y-m-d g:i:s');
                $schedule_line->bid_at = $when;
                $schedule_line->save();
            }

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

            // get id list of bidders to skip
            $skip_ids = array();  //empty array for ids to skip
            $uids = User::role(['flag-snapshot','flag-deferred'])->select('id')->get();
            $skip_ids = array();
            foreach($uids as $uid){
                $skip_ids[] = $uid->id;
            }

            // find next bidder, lowest bid order that has not bid, and not one to be skipped
            $user = User::whereNotIn('id',$skip_ids)->where('has_bid',0)->where('bid_order','>',0)->orderBy('bid_order')->first();
            if(isset($user) ){

                // handle snapshot bidders (with bid orders before this bidder) that have not yet been "snapshotted"
                $snap_users = User::role(['flag-snapshot'])->where('has_snapshot',0)->where('bid_order','<',$user->bid_order)->select('id','bid_order')->orderBy('bid_order')->get();
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
                $next = $user->bid_order;
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
                                    // LaraTwilio::notify($param_text_test_phone, 'TEST: Hello '. $user->name . ' - You can bid now, you are the active bidder.  Login at: ' . config('extra.login_url') . ' or call: ' . config('extra.app_bid_phone'));
                                    LaraTwilio::notify($param_text_test_phone, 'TEST: Hello '. $user->name . ' - You can bid now, you are the active bidder.  Call: ' . config('extra.app_bid_phone') . ', or attend the Boardroom if you are on site.');
                                }
                            }
                        } else {
                            // send to bidder, if they have a number
                            if (isset($user->phone_number)){
                                if (strlen($user->phone_number)>0){
                                    // LaraTwilio::notify($user->phone_number, 'Hello '. $user->name . ' - You can bid now, you are the active bidder.  Login at: ' . config('extra.login_url') . ' or call: ' . config('extra.app_bid_phone'));
                                    LaraTwilio::notify($user->phone_number, 'Hello '. $user->name . ' - You can bid now, you are the active bidder.  Call: ' . config('extra.app_bid_phone') . ', or attend the Boardroom if you are on site.');
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
