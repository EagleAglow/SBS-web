<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use App\Schedule;
use App\ScheduleLine;
use App\LogItem;

//Importing laravel-permission models
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use Session;

class ScheduleController extends Controller {

    public function __construct() {
        //  only pass users who can edit schedules
        $this->middleware(['auth', 'scheduleEdit']);
    }

    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index() {
        $schedules = Schedule::all(); //Get all 
        return view('admins.schedules.index')->with('schedules', $schedules);
    }

    /**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function create() {
        return view('admins.schedules.create');
    }

    /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function store(Request $request) {

        $title = $request['title'];
        $start = $request['start'];
        $cycle_count = $request['cycle_count'];
        $active = $request['active'];
        $approved = $request['approved'];

        $this->validate($request, [
            'title'=>'required|unique:schedules,title',
            'cycle_count'=>'required|numeric|min:1|max:4',
            'start'=>'required|date',            
        ]);

        // form only sends values for 'checked'
        // checkboxes - mySQL stores 1/0 (tinyInt) for True/False
        if (isset($active)) { $active = 1; } else { $active = 0; }
        if (isset($approved)) { $approved = 1; } else { $approved = 0; }

        $schedule = new Schedule();
        $schedule->title = $title;
        $schedule->cycle_count = $cycle_count;
        $schedule->start = $start;
        $schedule->active = $active;
        $schedule->approved = $approved;
        $schedule->save();

        // log action
        $note = 'New Schedule: ' . $title . ' ID/Start/Cycles/Approved/Active=' . $schedule->id . '/' .$start . '/' . $cycle_count . '/' . $approved . '/' . $active;
        $log_item = new LogItem();
        $log_item->note = $note;
        $log_item->save();

        flash('Schedule: '. $schedule->title.' added!')->success();
        return redirect()->route('schedules.index');
    }

    
    /**
    * Show the form for editing the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function edit($id) {
        $schedule = Schedule::findOrFail($id);
        $start = $schedule->start;
        $cycle_count = $schedule->cycle_count;
        $active = $schedule->active;
        $approved = $schedule->approved;
        return view('admins.schedules.edit', compact('schedule'));
    }
 
    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function update(Request $request, $id) {
        $schedule = Schedule::findOrFail($id);

        $this->validate($request, [
            'title'=>'required|unique:schedules,title,'.$id,
            'cycle_count'=>'required|numeric|min:1|max:4',
            'start'=>'required|date', 
        ]);           

        $input = $request->only(['title', 'cycle_count', 'start']);
        $schedule->fill($input);
        
        // fix for checkboxes
        $active = $request['active'];
        $approved = $request['approved'];
        // form only sends values for 'checked'
        // checkboxes - mySQL stores 1/0 (tinyInt) for True/False
        if (isset($active)) {
            $active = 1;
            $active_txt = 'ACTIVE';
        } else { 
            $active = 0; 
            $active_txt = 'NOT Active';
        }
        if (isset($approved)) { 
            $approved = 1; 
            $approved_txt = 'APPROVED';
        } else {
            $approved = 0; 
            $approved_txt = 'NOT Approved';
        }
        $schedule->active = $active;
        $schedule->approved = $approved;

        // log action
        $note = 'Schedule Edit (ID=' . $schedule->id  . '): ' . $schedule->title . ' set Start=' . $schedule->start->format('Y-m-d') . ', Cycles=' . $schedule->cycle_count . ', ' . $approved_txt . ', ' . $active_txt;
        $log_item = new LogItem();
        $log_item->note = $note;
        $log_item->save();

        $schedule->save();

        flash('Schedule: '. $schedule->title.' updated!')->success();
        return redirect()->route('schedules.index');
    }


    /**
    * CLONE the selected schedule, with schedule lines, then show the form for editing the new schedule
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function clone($id) {
        $schedule = Schedule::findOrFail($id);
        $title_old = $schedule->title;
        $start = $schedule->start;
        // set new start date
        $n = (($schedule->cycle_count) * 56 ) . ' days';
        $start = date_add( date_create( $start ), date_interval_create_from_date_string($n) );
        $cycle_count = $schedule->cycle_count;
        $active = 0;
        $approved = 0;
        $title = 'Begin ' . $start->format('F j, Y');
        // put $start back into mySQL format
        $start = $start->format('Y-m-d');

        // no validation, assumes original schedule was valid
         
        $schedule = new Schedule();
        $schedule->title = $title;
        $schedule->cycle_count = $cycle_count;
        $schedule->start = $start;
        $schedule->active = $active;
        $schedule->approved = $approved;
        $schedule->save();

        // retrieve id (of last saved schedule record) to be used by schedule lines
        $schedule_id = $schedule->id;
        $items = ScheduleLine::all()->except('id','created_at','updated_at')->where('schedule_id',$id);

        foreach($items as $item){
            $schedule_line = new ScheduleLine();
            $schedule_line->line = $item->line;
            // change schedule_id
            $schedule_line->schedule_id = $schedule->id;
            $schedule_line->line_group_id = $item->line_group_id;
            $schedule_line->comment = $item->comment;
            $schedule_line->blackout = $item->blackout;
            $schedule_line->barge = $item->barge;
            $schedule_line->offsite = $item->offsite;
            // set 56 days of shift codes
            for ($n = 1; $n <= 56; $n++) {
                $d = 'day_' . substr(('00' . $n),-2);
                $schedule_line->$d = $item->$d;
            }            
            $schedule_line->save();
        }

        // log action
        $note = 'New (Clone) Schedule: ' . $schedule->title . ' ID/Start/Cycles/Approved/Active=' . $schedule->id . '/' .$schedule->start . '/' . $schedule->cycle_count . '/' . $approved . '/' . $active;
        $log_item = new LogItem();
        $log_item->note = $note;
        $log_item->save();

        flash('Schedule: '. $schedule->title.' cloned from: ' . $title_old)->success();
        return redirect()->route('schedules.index');

    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function destroy($id) {
        $schedule = Schedule::findOrFail($id);
        $title = $schedule->title;
        $start_date = $schedule->start;

        // avoid deleting the only schedule
        if (Schedule::get()->count() < 2){
            // complain
            flash('Schedule WAS NOT DELETED. You can not remove the only schedule from system.')->warning()->important();
            return redirect()->route('schedules.index');
        }
        // remove all associated schedulelines...
        $res=ScheduleLine::where('schedule_id',$id)->delete();

        // fianlly, remove schedule
        $schedule->delete();

        // log action
        $note = 'Deleted Schedule: ' . $title . ' ID/Starte=' . $schedule->id . '/' .$start_date;
        $log_item = new LogItem();
        $log_item->note = $note;
        $log_item->save();        

        flash('Schedule deleted!')->success();
        return redirect()->route('schedules.index');
    }
}