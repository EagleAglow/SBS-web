<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use DB;
use App\User;
use App\Param;
use App\Extra;
use Notification;
use Illuminate\Notifications\Notifiable;

use Dotunj\LaraTwilio\Facades\LaraTwilio;  // SMS messaging
use Illuminate\Support\Facades\Mail;
//use App\Mail\BulkTestMail;
//use App\Mail\BulkMail;

//Importing laravel-permission models
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

//Enables us to output flash messaging
use Session;

// logging
use App\LogItem;

class OvertimeController extends Controller
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
     * Show the overtime dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (Auth::user()->can('OT-manage')){
            return view('supervisors.overtime');
        } else {
            abort('401');
        }
    }


// stubs 

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

    /* 
     *   set or clear overtime message
     */

    public function setmsg(Request $request) {
        if (Auth::user()->hasRole('supervisor')){

            $action = $request->action;
            if (isset($action)){
                if ($action == 'set'){
                    $msg_ot = $request->msg_ot;
                    if (isset($msg_ot)){
                        //Validate 
// js script limits text - see layout.overtime.blade
// controller disables process if no text is defined                        
//                        $this->validate($request, [
//                            'msg_ot'=>'max:279',
//                        ]);
        
                        // set 'msg_ot'
                        $param = Param::where('param_name','OT-message')->first();
                        $param->string_value = $msg_ot;
                        $param->save();
                        flash('Message set successfully.')->success();
                    } else {
                        flash('Failed to set message.')->error();
                    }
                } else {
                    if ($action == 'clear'){
                        // clear 'msg_ot'
                        $param = Param::where('param_name','OT-message')->first();
                        $param->string_value = '';
                        $param->save();
                        // calling state is none, if no message 
                        $param = Param::where('param_name','OT-call-state')->first();
                        $param->string_value = 'none';
                        $param->save();
                        flash('Message cleared, process disabled.')->success();
                    } else {
                        flash('Programmer error: No action.')->warning();
                    }
                }
            }

            return view('supervisors.overtime');
        } else {
            abort('401');
        }
    }

    public function reset(Request $request) {
        if (Auth::user()->hasRole('supervisor')){
            // see if we have a call list
            $call_list = Extra::orderBy('offered')->get();
            if (!isset($call_list)){
                flash('Missing call list!')->error();
                return view('supervisors.overtime');
            } else {
                if ($call_list->count() == 0){
                    flash('Empty call list!')->error();
                    return view('supervisors.overtime');
                } else {
                    // clear some fields...
                    $affected = DB::table('extras')->update(['active' => 0, 'notified' => 0, 'active_at' => null, 'email_sent_at' => null, 'text_sent_at' => null, 'voice_call_at' => null]);
                    $affected = DB::table('params')->where('param_name','OT-call-next')->update(['integer_value' => 0]);
                    // set 'OT-call-state'
                    $param = Param::where('param_name','OT-call-state')->first();
                    $param->string_value = 'ready';
                    $param->save();
                    flash('Ready...')->success();
                    return view('supervisors.overtime');
                }
            }
        } else {
            abort('401');
        }
    }


// start pause resume reset    
            // overtime calling: OT-call-state: none (initial or after overtime table erased), ready (to begin, next to call is no. 1),
            //                                  running, paused, complete (after last one called)

    public function start(Request $request) {
        if (Auth::user()->hasRole('supervisor')){
            // check/set 'OT-call-state'
            $param = Param::where('param_name','OT-call-state')->first();
            if($param->string_value == 'ready'){
                // set first call list entry to active and set time for "active_at",
                // assumes 'ready' implies call list exists and has been reset - LATER, add checks to this?????????????????????
                $next_up = Extra::orderBy('offered')->first();
                $next_up->active = '1';
                $next_up->active_at = now();
                $next_up->save();
                $param->string_value = 'running';
                $param->save();
                flash('Started...')->success();
            } else {
                if($param->string_value == 'running'){
                    // see if we are done yet - count how many are not notified
                    $how_many = Extra::where('notified','0')->count();
                    if ($how_many == 0){
                        // done, everyone notified
                        $param = Param::where('param_name','OT-call-state')->first();
                        $param->string_value = 'complete';
                        $param->save();
                        flash('Complete...')->success();
                    } else {
                        // see if any are active
                        $how_many = Extra::where('active','1')->count();
                        if ($how_many == 0){
                            // none active, try to set next one active
                            $next_up = Extra::where('notified','0')->orderBy('offered')->first();
                            // log
                            $log_item = new LogItem();
                            $log_item->note = 'Set: ' . $next_up->name;
                            $log_item->save();
                            // do it
                            $next_up->active = '1';
                            $next_up->active_at = now();
                            $next_up->save();
                        } else {
                            // one (at least) is active - if the time is up, mark it as notified, turn 'active' off
                            // get timing parameter
                            $param = Param::where('param_name','OT-cycle-time')->first();
                            $cycle_seconds = $param->integer_value;
                            // get the beginning time for the (first) active one, test
                            $next_up = Extra::where('active','1')->first();
                            $cycle_start = strtotime($next_up->active_at);
                            if (time() > ($cycle_seconds + $cycle_start)){
                                $next_up->notified = '1';
                                $next_up->active = '0';

// remove this later - it should be button driven....
                                $next_up->voice_call_at = now();



                                $next_up->save();
                                // log
                                $log_item = new LogItem();
                                $log_item->note = 'Notified: ' . $next_up->name;
                                $log_item->save();

                                // find the next to be set active
                                $next_up = Extra::where('notified','0')->orderBy('offered')->first();
                                // log
                                $log_item = new LogItem();
                                $log_item->note = 'Set: ' . $next_up->name;
                                $log_item->save();
                                // turn off active flag
                                $next_up->active = '1';
                                $next_up->active_at = now();
                                $next_up->save();
                            }


                        }
                                       
                    }


                } else {
                    flash('NOT Started, not in "ready" state...')->error();
                }
            }
            return view('supervisors.overtime');




        } else {
            abort('401');
        }
    }


    public function pause(Request $request) {
        if (Auth::user()->hasRole('supervisor')){
            // check/set 'OT-call-state'
            $param = Param::where('param_name','OT-call-state')->first();
            if($param->string_value == 'running'){
                $param->string_value = 'paused';
                $param->save();
                flash('Paused...')->success();
            } else {
                flash('NOT Paused, was not in "running" state...')->error();
            }
            return view('supervisors.overtime');
        } else {
            abort('401');
        }
    }

    public function resume(Request $request) {
        if (Auth::user()->hasRole('supervisor')){
            // check/set 'OT-call-state'
            $param = Param::where('param_name','OT-call-state')->first();
            if($param->string_value == 'paused'){
                $param->string_value = 'running';
                $param->save();
                flash('Running...')->success();
            } else {
                if($param->string_value == 'running'){
                    // do nothing, don't want flash message if page refresh from javascript for progress bar
                } else {
                    flash('Did NOT Resume, was not in "paused" state...')->error();
                }
            }
            return view('supervisors.overtime');
        } else {
            abort('401');
        }
    }

}