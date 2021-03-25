<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use DB;
use App\ShiftCode;
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
    *
    * @return \Illuminate\Http\Response
    */
        public function index(Request $request) {

         $schedule_id = $request['schedule_id'];  
         if (!isset($schedule_id)){
             // try to get it from session (used by destroy and create)
             $schedule_id = session('schedule_id');
         }

        $schedule = Schedule::where('id',$schedule_id)->first();

        $schedule_lines = ScheduleLine::where('schedule_id',$schedule_id)->orderBy('line_with_fill')->paginate(5)->onEachSide(13); //Get first 5 ScheduleLines

        return view('admins.schedulelineset.index',
            ['schedule_lines'=>$schedule_lines,
             'schedule_title'=>$schedule->title,
             'start_date'=>$schedule->start,
             'cycles'=>$schedule->cycle_count,
            'schedule_id' => $schedule_id,
            ]);
    }
 

    /**
    * Display a listing of the resource, offset from start
    *
    * @return \Illuminate\Http\Response
    */
    public function show(Request $request, $schedule_id) {
        if (!isset($schedule_id)){
            // try to get it from session (used by destroy and create)
            $schedule_id = session('schedule_id');
        }
   
        $schedule_lines = ScheduleLine::where('schedule_id',$schedule_id)->orderBy('line_with_fill')->paginate(5)->onEachSide(13); //Get first 5 ScheduleLines

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
            'first_day'=>$first_day,
            'last_day'=>$last_day,
            'page'=>$page,
            'schedule_id' => $schedule_id,
            ]);
    }


/**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function create($schedule_id) {

        $schedules = Schedule::all('id','title'); //Get id & title code for all schedules
        $groups = LineGroup::all('id','code'); //Get id & code for all groups
        $shifts = ShiftCode::all('id','name','begin_time','end_time'); //Get id, code, times for all shift codes

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

// the following works, keep for fall back
//        $this->validate($request, [ 
//          'line'=>new DummyFail( 'Message passed to rule class')
//        ]);

        $this->validate($request, ['comment'=>'required','line'=>'required|alpha_num|max:4', ]);
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
        // get shift for each day
        for ($n = 1; $n <= 56; $n++) {
            $d = 'day_' . substr(('00' . $n),-2);
            $schedule_line->$d = $$d;
        }

        $schedule_line->save();

        // put schedule_id in session
        flash('Schedule Line'. $schedule_line->line.' added!')->success();
        return redirect()->route('schedulelineset.index')->with(['schedule_id' => $schedule_id]);
    }
 
 

    /**
    * Show the form for editing the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function edit($id) {
        $schedule_line = ScheduleLine::findOrFail($id);

        $groups = LineGroup::all('id','code'); //Get id & code for all groups
        $schedules = Schedule::all('id','title'); //Get id & title for all schedules
        $shifts = ShiftCode::all('id','name','begin_time','end_time'); //Get id, code, times for all shift codes
        return view('admins.schedulelineset.edit', compact('schedule_line','groups','schedules','shifts'));
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

        $this->validate($request, ['comment'=>'required','line'=>'required|alpha_num|max:4', ]);
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
        // special handling for "natural sort"
        $schedule_line->line_natural = ScheduleLine::natural($line);
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

        // put schedule_id in session
        flash('Schedule Line: '. $schedule_line->line.' updated!')->success();
        return redirect()->route('schedulelineset.index')->with(['schedule_id' => $schedule_id]);

    }


    /**
    * Remove the specified resource from storage.
    * 
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function destroy($id) {
        $schedule_line = ScheduleLine::findOrFail($id);
        // get schedule_id, so we can return it.
        $schedule_id = $schedule_line->schedule_id;

        $schedule_line->delete();

        // put schedule_id in session
        flash('Schedule Line deleted!')->success();
        return redirect()->route('schedulelineset.index')->with(['schedule_id' => $schedule_id]);

    }
}
