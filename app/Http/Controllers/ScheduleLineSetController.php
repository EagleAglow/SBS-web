<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use DB;
use App\ShiftCode;
use App\LineDay;
use App\ScheduleLine; 
use App\LineGroup;
use App\Schedule; 
//use App\Rules\DummyFail;
use App\Rules\UniqueLineGroupSchedule;

//Importing laravel-permission models
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use Session;

class ScheduleLineSetController extends Controller {

    public function __construct() {
        //  only pass users who can edit schedules
        $this->middleware(['auth', 'scheduleEdit']);
    }

    /**
    * Display a listing of the resource - initial page for pagination
    **
    * @return \Illuminate\Http\Response
    */
        public function index(Request $request) {

         $schedule_id = $request['schedule_id'];  
         if (!isset($schedule_id)){
             // try to get it from session (used by destroy and create)
             $schedule_id = session('schedule_id');
         }

         $my_selection = $request['my_selection'];
         if (!isset($my_selection)){
            // try to get it from session (used by destroy and create)
            $my_selection = session('my_selection');
            if (!isset($my_selection)){
                // final fallback
                $my_selection = 'all';
            }
        }
        $next_selection = $request['next_selection'];
        if (!isset($next_selection)){
            // try to get it from session (used by destroy and create)
            $next_selection = session('next_selection');
        }

        $line_groups = LineGroup::all();
        $key_id = 0;  // arbitrary default, should not actually be used

        $list_codes = array();  //empty array for line group codes (field = 'name')
        foreach ($line_groups as $line_group) {
            if ( ScheduleLine::where('schedule_id',$schedule_id)->where('line_group_id',$line_group->id)->count() > 0){
                $list_ids[] = $line_group->id;
                $list_codes[] = $line_group->code;
                if ($line_group->code == $my_selection){
                    $key_id = $line_group->id;
                }
            }
        }

        $schedule = Schedule::where('id',$schedule_id)->first();

        $schedule_lines = ScheduleLine::where('schedule_id',$schedule_id)->orderBy('line_natural')->paginate(5)->onEachSide(13); //Get first 5 ScheduleLines

        if($my_selection == 'all'){
            $schedule_lines = ScheduleLine::where('schedule_id',$schedule_id)->whereIn('line_group_id',$list_ids)
            ->orderBy('line_natural')->paginate(5)->onEachSide(13); //Get first 5 ScheduleLines;
        } else {
            // filter to a single line group

            $schedule_lines = ScheduleLine::where('schedule_id',$schedule_id)->where('line_group_id', $key_id)
            ->orderBy('line_natural')->paginate(5)->onEachSide(13); //Get first 5 ScheduleLines;
        }

        return view('admins.schedulelineset.index',
            ['schedule_lines'=>$schedule_lines,
             'schedule_title'=>$schedule->title,
             'start_date'=>$schedule->start,
             'cycles'=>$schedule->cycle_count,
            'schedule_id' => $schedule_id,
            'my_selection'=>$my_selection,
            'next_selection'=>$next_selection,
            'trap'=>'0',
            'list_codes' => $list_codes,
            'line_groups' => $line_groups,
            ])->with(['schedule_id' => $schedule_id, 'my_selection' => $my_selection, 'next_selection' => $next_selection]);
    }
 

    public function show(Request $request, $schedule_id) {
        if (!isset($schedule_id)){
            // try to get it from session (used by destroy and create)
            $schedule_id = session('schedule_id');
        }



        

//////


        // identify line groups used in this schedule
        $line_groups = LineGroup::all();
        $list_ids = array();  //empty array for line group ids
        $list_codes = array();  //empty array for line group codes (field = 'name')
        foreach ($line_groups as $line_group) {
            if ( ScheduleLine::where('schedule_id',$schedule_id)->where('line_group_id',$line_group->id)->count() > 0){
                $list_ids[] = $line_group->id;
                $list_codes[] = $line_group->code;
            }
        }

        // presentation selection = which line groups to show
        // if there is only one one line group, set 'my_selection' and 'next_selection' to that group code
        // otherwise, rotate 'my_selection' through 'all' (lowercase to differ from any
        //    line group names), and then each line group name (uppercase).  'next_selection' shows subsequent choice
        // view page returns the values for 'my_selection' and 'next_selection' (but next_selection is not used by controller)
        // if request field 'go_next' is 'yes',  rotate to next group selection

        // debugging aid
        $trap = $request['trap'];
        if (!isset($trap)){ $trap = 'undefined'; }

        // passed to switch to display next group choice
        $go_next = $request['go_next'];
        if (!isset($go_next)){ $go_next = 'no'; }

        $my_selection = $request['my_selection'];
        if(!isset($my_selection)){
            if (count($list_ids) == 0){
                // should not get here
                $my_selection = 'all';
                $next_selection = 'all';
            } else {
                if (count($list_ids) == 1){
                    $my_selection = $list_codes[0];  // first, and only, code
                    $next_selection = $list_codes[0];
                    $key_id = $list_ids[0];     // if my_selection is not 'all', we will need a Key_id for selecting records
                    $trap = '5';
                } else {
                    $my_selection = 'all';
                    $next_selection = $list_codes[0];  // first code
                    $trap = '6';
                }
            }
        } else { 
            if (count($list_ids) == 0){
                // should not get here
                $my_selection = 'all';
                $next_selection = 'all';
            } else {
                if (count($list_ids) == 1){
                    $my_selection = $list_codes[0];  // first, and only, code
                    $next_selection = $list_codes[0];
                    $key_id = $list_ids[0];     // if my_selection is not 'all', we will need a Key_id for selecting records
                    $trap = '7';
                } else {
                    // there is more than one line group
                    if ($go_next == 'yes'){
                        // change groups
                        if ($my_selection == 'all'){
                            // go to first group code
                            $my_selection = $list_codes[0];  // first code
                            $next_selection = $list_codes[1];  // second code
                            $key_id = $list_ids[0];     // if my_selection is not 'all', we will need a Key_id for selecting records
                            $trap = '3';
                        } else {
                            // need id for 'my_selection', and next code in rotation, or if this is last code, then 'all'
                            $key_id = LineGroup::where('code',$my_selection)->first()['id'];
                            if (isset($key_id)){
                                $key = array_search($key_id,$list_ids);
                                if (($key +1) >= count($list_ids)){
                                    // wrap my_selection to 'all'
                                    $my_selection = 'all';
                                    $next_selection = $list_codes[0];
                                    $trap = '9';
                                } else {
                                    $my_selection = $list_codes[$key +1];
                                    $key_id = $list_ids[$key +1];
                                    if (($key +2) >= count($list_ids)){
                                        // wrap next_selection to 'all'
                                        $next_selection = 'all';
                                        $trap = '10';
                                    } else {
                                        $next_selection = $list_codes[$key +2];
                                        $key_id = $list_ids[$key +1];     // if my_selection is not 'all', we will need a Key_id for selecting records
                                        $trap = '11';
                                    }
                                }
                            } else {
                                // unlikely error - my_selection not in list
                                $my_selection = 'all';
                                $next_selection = 'all';
                            }
                        }
                    } else {
                        // don't change groups
                        if ($my_selection == 'all'){
                            $next_selection = $list_codes[0];  // first code
                            $trap = '1';
                        } else {
                            // need id for 'my_selection', to  find next code in rotation, or if this is last code, then 'all'
                            // if my_selection is not 'all', we will need a Key_id for selecting records
                            $key_id = LineGroup::where('code',$my_selection)->first()['id'];
                            if (isset($key_id)){
                                $key = array_search($key_id,$list_ids);
                                if (($key +1) >= count($list_ids)){
                                    // wrap next_selection to 'all'
                                    $next_selection = 'all';
                                    $trap = '8';
                                } else {
                                    $next_selection = $list_codes[$key +1];
                                    $trap = '2';
                                }
                            } else {
                                // unlikely error - my_selection not in list
                                $my_selection = 'all';
                                $next_selection = 'all';
                            }
                        }
                    }
                }
            }
        }

        if($my_selection == 'all'){
            $schedule_lines = ScheduleLine::where('schedule_id',$schedule_id)->whereIn('line_group_id',$list_ids)
            ->orderBy('line_natural')->paginate(5)->onEachSide(13); //Get first 5 ScheduleLines;
        } else {
            // filter to a single line group
            $schedule_lines = ScheduleLine::where('schedule_id',$schedule_id)->where('line_group_id', $key_id)
            ->orderBy('line_natural')->paginate(5)->onEachSide(13); //Get first 5 ScheduleLines;
        }
        

////         
   
//        $schedule_lines = ScheduleLine::where('schedule_id',$schedule_id)->orderBy('line_natural')->paginate(5)->onEachSide(13); //Get first 5 ScheduleLines

        $schedule_title = $request['schedule_title'];
        if (!isset($schedule_title)){
            $schedule_title = Schedule::where('id',$schedule_id)->first()->title;
        }
        $start_date = $request['start_date'];
        if (!isset($start_date)){
            $start_date = Schedule::where('id',$schedule_id)->first()->start;
        }
        $cycles = $request['cycles'];
        if (!isset($cycles)){
            $cycles = Schedule::where('id',$schedule_id)->first()->cycle_count;
        }
        $max_days = $request['max_days'];
        if (!isset($max_days)){
            $max_days = Schedule::where('id',$schedule_id)->first()->cycle_days;
        }

        $first_day = $request['first_day'];
        if (!isset($first_day)){
            $first_day = 1;
        }
        $last_day = $request['last_day'];
        if (!isset($last_day)){
            $last_day = 7;
        }
        $page = $request['page'];
        if (!isset($page)){
            $page = 1;
        }
        
        return view('admins.schedulelineset.index',
            ['schedule_lines'=>$schedule_lines,
            'schedule_title'=>$schedule_title,
            'start_date'=>$start_date,
            'cycles'=>$cycles,
            'max_days'=>$max_days,
            'first_day'=>$first_day,
            'last_day'=>$last_day,
            'page'=>$page,
            'schedule_id' => $schedule_id,
            'my_selection'=>$my_selection,
            'next_selection'=>$next_selection,
            'trap' => $trap,
            'list_codes' => $list_codes
            ]);
    }


/**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function create($schedule_id) {

        $schedules = Schedule::all('id','title'); //Get id & title code for all schedules
        $groups = LineGroup::all('id','code')->sortBy('code'); //Get id & code for all groups
        $shifts = ShiftCode::all('id','name','begin_time','end_time')->sortBy('name');  //Get id, code, times for all shift codes

        return view('admins.schedulelineset.create',['schedule_id'=>$schedule_id, 'groups'=>$groups,'schedules'=>$schedules,'shifts'=>$shifts]);

    }

    /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function store(Request $request) {
        $line = $request['line'];
        $schedule_id = $request['schedule_id'];
        $line_group_id = $request['line_group_id'];
        $action = 'store';

        if (!isset($schedule_id)){
            // try to get it from session (used by destroy and create)
            $schedule_id = session('schedule_id');
        }

        $my_selection = $request['my_selection'];
        if (!isset($my_selection)){
           // try to get it from session (used by destroy and create)
           $my_selection = session('my_selection');
           if (!isset($my_selection)){
               // final fallback
               $my_selection = 'all';
           }
       }
       $next_selection = $request['next_selection'];
       if (!isset($next_selection)){
           // try to get it from session (used by destroy and create)
           $next_selection = session('next_selection');
       }


// the following works, keep for fall back
//        $this->validate($request, [ 
//          'line'=>new DummyFail( 'Message passed to rule class')
//        ]);

        $this->validate($request, ['line'=>'required|alpha_num|max:4', ]);
        $this->validate($request, [ 
            'line'=>new UniqueLineGroupSchedule( $line, $line_group_id, $schedule_id, $action )
        ]);
        $comment = $request['comment'];
        if (!isset($comment)) { $comment = ''; }

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

        $schedule_line = new ScheduleLine();
        // special handling for "natural sort"
        $schedule_line->line = $line;
        $schedule_line->line_natural = ScheduleLine::natural($line);
        $schedule_line->schedule_id = $schedule_id;
        $schedule_line->line_group_id = $line_group_id;
        $schedule_line->comment = $comment;
        $schedule_line->blackout = $blackout;
        $schedule_line->nexus = $nexus;
        $schedule_line->barge = $barge;
        $schedule_line->offsite = $offsite;
        $schedule_line->save();
        $schedule_line_id = $schedule_line->id;  // capture id in cast it is not static - may not be necessary?

        // get shift for each day - update the days
        $max_days = Schedule::where('id',$schedule_id)->first()->cycle_days;
        for ($n = 1; $n <= $max_days; $n++) {
            $d = 'day_' . substr(('000' . $n),-3);
            $$d = $request[$d];
            $line_day = new LineDay();
            $line_day->schedule_line_id = $schedule_line_id;
            $line_day->day_number = $n;
            $line_day->shift_code_id = $$d;
            $line_day->save();
        }

        // put schedule_id in session
        flash('Schedule Line'. $schedule_line->line.' added!')->success();
        return redirect()->route('schedulelineset.index')->with(['schedule_id' => $schedule_id, 'my_selection' => $my_selection, 'next_selection' => $next_selection]);
    }
 
 

    /**
    * Show the form for editing the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function edit(Request $request, $id) {
        $schedule_line = ScheduleLine::findOrFail($id);

        $my_selection = $request['my_selection'];
        if (!isset($my_selection)){
           // try to get it from session (used by destroy and create)
           $my_selection = session('my_selection');
           if (!isset($my_selection)){
               // final fallback
               $my_selection = 'all';
           }
        }
        $next_selection = $request['next_selection'];
        if (!isset($next_selection)){
           // try to get it from session (used by destroy and create)
           $next_selection = session('next_selection');
        }

        $groups = LineGroup::all('id','code'); //Get id & code for all groups
        $schedules = Schedule::all('id','title'); //Get id & title for all schedules
        $shifts = ShiftCode::all('id','name','begin_time','end_time'); //Get id, code, times for all shift codes
        return view('admins.schedulelineset.edit', compact('schedule_line','groups','schedules','shifts'))->with(['my_selection' => $my_selection, 'next_selection' => $next_selection]);
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

        $this->validate($request, ['line'=>'required|alpha_num|max:4', ]);
        $this->validate($request, [ 
            'line'=>new UniqueLineGroupSchedule( $line, $line_group_id, $schedule_id, $action )
        ]); 

        $comment = $request['comment'];
        if (!isset($comment)) { $comment = ''; }

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
        // special handling for "natural sort"
        $schedule_line->line_natural = ScheduleLine::natural($line);
        $schedule_line->schedule_id = $schedule_id;
        $schedule_line->line_group_id = $line_group_id;
        $schedule_line->comment = $comment;
        $schedule_line->blackout = $blackout;
        $schedule_line->nexus = $nexus;
        $schedule_line->barge = $barge;
        $schedule_line->offsite = $offsite;
 
        $schedule_line->save();
        $schedule_line_id = $schedule_line->id;  // capture id in cast it is not static - may not be necessary?

        // get shift for each day - update the days
        $max_days = Schedule::where('id',$schedule_id)->first()->cycle_days;
        for ($n = 1; $n <= $max_days; $n++) {
            $d = 'day_' . substr(('000' . $n),-3);
            $$d = $request[$d];
            $line_day = LineDay::where('schedule_line_id',$schedule_line_id)->where('day_number',$n)->first();
            $line_day->shift_code_id = $$d;
            $line_day->save();
        }

        $my_selection = $request['my_selection'];
        if (!isset($my_selection)){
           // try to get it from session (used by destroy and create)
           $my_selection = session('my_selection');
           if (!isset($my_selection)){
               // final fallback
               $my_selection = 'all';
           }
       }
       $next_selection = $request['next_selection'];
       if (!isset($next_selection)){
           // try to get it from session (used by destroy and create)
           $next_selection = session('next_selection');
       }

        // put schedule_id in session
        flash('Schedule Line: '. $schedule_line->line.' updated!')->success();
        return redirect()->route('schedulelineset.index')->with(['schedule_id' => $schedule_id, 'my_selection' => $my_selection, 'next_selection' => $next_selection]);

    }


    public function clone(Request $request, $id) {
        $schedule_line = ScheduleLine::findOrFail($id);
        $schedule_id = $schedule_line->schedule_id;
        $line_group_id = $schedule_line->line_group_id;
        $line = $schedule_line->line;

        $my_selection = $request['my_selection'];
        if (!isset($my_selection)){
           // try to get it from session (used by destroy and create)
           $my_selection = session('my_selection');
           if (!isset($my_selection)){
               // final fallback
               $my_selection = 'all';
           }
        }
        $next_selection = $request['next_selection'];
        if (!isset($next_selection)){
           // try to get it from session (used by destroy and create)
           $next_selection = session('next_selection');
        }

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
            // put schedule_id in session
            flash('Schedule Line: '. $schedule_line->line.' was not cloned! Could not generate unique line number.')->warning()->important();
            return redirect()->route('schedulelineset.index')->with(['schedule_id' => $schedule_id, 'my_selection' => $my_selection, 'next_selection' => $next_selection]);
        }

        $schedule_line_clone->line = $test_line;
        $schedule_line_clone->line_natural = ScheduleLine::natural($test_line);
        $schedule_line_clone->schedule_id = $schedule_line->schedule_id;
        $schedule_line_clone->line_group_id = $schedule_line->line_group_id;
        $schedule_line_clone->comment = $schedule_line->comment;
        $schedule_line_clone->blackout = $schedule_line->blackout;
        $schedule_line_clone->nexus = $schedule_line->nexus;
        $schedule_line_clone->barge = $schedule_line->barge;
        $schedule_line_clone->offsite = $schedule_line->offsite;

        $schedule_line_clone->save();
        $schedule_line_clone_id = $schedule_line_clone->id;  // id of new schedule line
        // clone line_days that belong to $schedule_line (i.e., original schedule_line)

        $days = LineDay::where('schedule_line_id',$schedule_line->id)->get();
        foreach ($days as $day){
            $line_day_clone = $day->replicate();
            $line_day_clone->schedule_line_id = $schedule_line_clone_id;
            $line_day_clone->save();
        }

        $my_selection = $request['my_selection'];
        $next_selection = $request['next_selection'];

        // put schedule_id in session
        flash('Schedule Line: '. $schedule_line->line.' cloned to: ' . $test_line . '.')->success();
        return redirect()->route('schedulelineset.index')->with(['schedule_id' => $schedule_id, 'my_selection' => $my_selection, 'next_selection' => $next_selection]);
    }


    /**
    * Remove the specified resource from storage.
    * 
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function destroy(Request $request, $id) {
        $schedule_line = ScheduleLine::findOrFail($id);
        // get schedule_id, so we can return it.
        $schedule_id = $schedule_line->schedule_id;

        LineDay::where('schedule_line_id',$schedule_line->id)->delete();  // remove all linked days
        $schedule_line->delete();

        $my_selection = $request['my_selection'];
        if (!isset($my_selection)){
           // try to get it from session (used by destroy and create)
           $my_selection = session('my_selection');
           if (!isset($my_selection)){
               // final fallback
               $my_selection = 'all';
           }
        }
        $next_selection = $request['next_selection'];
        if (!isset($next_selection)){
           // try to get it from session (used by destroy and create)
           $next_selection = session('next_selection');
        }

        // put schedule_id in session
        flash('Schedule Line deleted!')->success();
        return redirect()->route('schedulelineset.index')->with(['schedule_id' => $schedule_id, 'my_selection' => $my_selection, 'next_selection' => $next_selection]);

    }
}
