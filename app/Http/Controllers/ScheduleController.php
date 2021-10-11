<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use App\Schedule;
use App\ScheduleLine;
use App\LineDay;
use App\ShiftCode;
use App\LogItem;
use DB;

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
        $cycle_days = $request['cycle_days'];
        $active = $request['active'];
        $approved = $request['approved'];

        $this->validate($request, [
            'title'=>'required|unique:schedules,title',
            'cycle_count'=>'required|numeric|min:1|max:4',
            'cycle_days'=> 'required|numeric|min:7|max:366',
            'start'=>'required|date',            
        ]);

        // form only sends values for 'checked'
        // checkboxes - mySQL stores 1/0 (tinyInt) for True/False
        if (isset($active)) { $active = 1; } else { $active = 0; }
        if (isset($approved)) { $approved = 1; } else { $approved = 0; }

        $schedule = new Schedule();
        $schedule->title = $title;
        $schedule->cycle_count = $cycle_count;
        $schedule->cycle_days = $cycle_days;
        $schedule->start = $start;
        $schedule->active = $active;
        $schedule->approved = $approved;
        $schedule->save();

        // log action
        $note = 'New Schedule: ' . $title . ' ID/Start/Days/Cycles/Approved/Active=' . $schedule->id . '/' .$start . '/' . $cycle_days . '/' . $cycle_count . '/' . $approved . '/' . $active;
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
        $cycle_days = $schedule->cycle_days;
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
            'cycle_days'=> 'required|numeric|min:7|max:366',
            'start'=>'required|date', 
        ]);           

        $input = $request->only(['title', 'cycle_count', 'cycle_days', 'start']);
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

        // see if we need to modify the schedule_line day count
        // ASSUMPTION! - all schedule lines have the same number of days
        // another ASSUMPTION! - Line_days are 'sane' (e.g., for 5 days, there are 5 records, 1 thru 5)
        // first see if any day records exist...
        $how_many = DB::table('line_days')
                    ->join('schedule_lines','line_days.schedule_line_id','=','schedule_lines.id')
                    ->where('schedule_lines.schedule_id',$schedule->id)->count();
        if ($how_many > 0){
            // get the highest day number
            $high_day = DB::table('line_days')
            ->join('schedule_lines','line_days.schedule_line_id','=','schedule_lines.id')
            ->where('schedule_lines.schedule_id',$schedule->id)->max('day_number');

            if ( $high_day != $schedule->cycle_days ){
                // make LineDays match - delete any extras, fill new days with 'day off' code
                $day_off = ShiftCode::where('name','----')->first()->id;
                $lines = ScheduleLine::where('schedule_id',$schedule->id)->get();
                foreach ($lines as $line){
                    LineDay::where('schedule_line_id',$line->id)->where('day_number','>',$schedule->cycle_days)->delete();
                    $top = LineDay::where('schedule_line_id',$line->id)->max('day_number');
                    if ($top < $schedule->cycle_days){
                        for ($n = ($top +1); $n <= $schedule->cycle_days; $n++) {
                            $line_day = new LineDay();
                            $line_day->schedule_line_id = $line->id;
                            $line_day->day_number = $n;
                            $line_day->shift_code_id = $day_off;
                            $line_day->save();           
                        }
                    }
                }
            }
        }

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
        $n = (($schedule->cycle_count) * $schedule->cycle_days ) . ' days';
        $start = date_add( date_create( $start ), date_interval_create_from_date_string($n) );
        $cycle_count = $schedule->cycle_count;
        $cycle_days = $schedule->cycle_days;
        $active = 0;
        $approved = 0;
        $title = 'Begin ' . $start->format('F j, Y');
        // put $start back into mySQL format
        $start = $start->format('Y-m-d');

        // no validation, assumes original schedule was valid
         
        $schedule = new Schedule();
        $schedule->title = $title;
        $schedule->cycle_count = $cycle_count;
        $schedule->cycle_days = $cycle_days;
        $schedule->start = $start;
        $schedule->active = $active;
        $schedule->approved = $approved;
        $schedule->save();

        // capture id (of last saved schedule record) to be used by schedule lines
        $schedule_id = $schedule->id;
        $items = ScheduleLine::all()->except('id','created_at','updated_at')->where('schedule_id',$id);

        foreach($items as $item){
            $schedule_line = new ScheduleLine();
            $schedule_line->line = $item->line;
            $schedule_line->line_natural = ScheduleLine::natural($item->line);
            // change schedule_id
            $schedule_line->schedule_id = $schedule->id;
            $schedule_line->line_group_id = $item->line_group_id;
            $schedule_line->comment = $item->comment;
            $schedule_line->blackout = $item->blackout;
            $schedule_line->barge = $item->barge;
            $schedule_line->offsite = $item->offsite;
            $schedule_line->save();
            // capture id (of last saved schedule_line record) to be used by new LineDays
            $new_schedule_line_id = $schedule_line->id;
            // clone line_days that belong to $item (i.e., old schedule_line)
            $old_schedule_line_id = $item->id;

            $days = LineDay::where('schedule_line_id',$old_schedule_line_id)->get();
            foreach ($days as $day){
                $line_day_clone = $day->replicate();
                $line_day_clone->schedule_line_id = $new_schedule_line_id;
                $line_day_clone->save();
            }
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
        // remove all associated linedays
        $res = DB::table('line_days')
        ->join('schedule_lines','line_days.schedule_line_id','=','schedule_lines.id')
        ->where('schedule_lines.schedule_id',$schedule->id)->delete();

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