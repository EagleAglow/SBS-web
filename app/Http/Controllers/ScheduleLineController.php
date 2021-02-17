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

class ScheduleLineController extends Controller {

    public function __construct() {
        //  only pass users who can edit schedules
        $this->middleware(['auth', 'scheduleEdit']);
    }

    /**
    * Display a listing of the resource - initial page for pagination
    *
    * @return \Illuminate\Http\Response
    */
    public function index() {

        $schedule_lines = ScheduleLine::paginate(5)->onEachSide(13);; //Get first 5 ScheduleLines

        return view('superusers.schedulelines.index',
            ['schedule_lines'=>$schedule_lines,
            'schedule_title'=>'Test This',
            'start_date'=>'20200402',
            'cycles'=>'3',
            ]);
    }
 

    /**
    * Display a listing of the resource, offset from start
    *
    * @return \Illuminate\Http\Response
    */
    public function show(Request $request) {

        $schedule_lines = ScheduleLine::paginate(5)->onEachSide(13);; //Get first 5 ScheduleLines

        $schedule_title = $request['schedule_title'];
        if (!isset($schedule_title)){
            abort('401');
        }
        $start_date = $request['start_date'];
        if (!isset($start_date)){
            abort('401');
        }
        $cycles = $request['cycles'];
        if (!isset($cycles)){
            abort('401');
        }
        $first_day = $request['first_day'];
        if (!isset($first_day)){
            abort('401');
        }
        $last_day = $request['last_day'];
        if (!isset($last_day)){
            abort('401');
        }
        $page = $request['page'];
        if (!isset($page)){
            $page = 1;
        }

        return view('superusers.schedulelines.index',
            ['schedule_lines'=>$schedule_lines,
            'schedule_title'=>$schedule_title,
            'start_date'=>$start_date,
            'cycles'=>$cycles,
            'first_day'=>$first_day,
            'last_day'=>$last_day,
            'page'=>$page,
            ]);
    }


/**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function create() {
        $groups = LineGroup::all('id','code'); //Get id & code for all groups
        $schedules = Schedule::all('id','title'); //Get id & title for all schedules
        $shifts = ShiftCode::all('id','name','begin_time','end_time'); //Get id, code, times for all shift codes
        return view('superusers.schedulelines.create',['groups'=>$groups,'schedules'=>$schedules,'shifts'=>$shifts]);
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

        $schedule_line = new ScheduleLine();
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

        flash('Schedule Line'. $schedule_line->line.' added!')->success();
        return redirect()->route('schedulelines.index');

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
        return view('superusers.schedulelines.edit', compact('schedule_line','groups','schedules','shifts'));
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


    /**
    * Remove the specified resource from storage.
    * 
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function destroy($id) {
        $schedule_line = ScheduleLine::findOrFail($id);
        $schedule_line->delete();

        flash('Schedule Line deleted!')->success();
        return redirect()->route('schedulelines.index');

    }
}