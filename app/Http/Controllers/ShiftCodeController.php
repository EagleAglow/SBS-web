<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use App\ShiftCode;
use App\ScheduleLine;

//Importing laravel-permission models
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use Session;

class ShiftCodeController extends Controller {

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
        $shift_codes = ShiftCode::all()->sortBy('name'); //Get all shiftcodes

        return view('admins.shiftcodes.index')->with('shift_codes', $shift_codes);
    }

    /**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function create() {

        return view('admins.shiftcodes.create');
    }

    // public function new() {
    //     //Get all schedulelines and pass to the view
    //         return view('admins.shiftcodes.create');
    // }
 
    /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function store(Request $request) {
        $this->validate($request, [
            'name'=>'required|min:4|max:4|unique:shift_codes,name,',
            'begin_time'=>'required|date_format:H:i',
            'end_time'=>'required|date_format:H:i',
        ]);
        $name = $request['name'];
        $begin_time = $request['begin_time'];
        $end_time = $request['end_time'];
        $shift_code = new ShiftCode();
        $shift_code->name = $name;
        $shift_code->begin_time = $begin_time;
        $shift_code->end_time = $end_time;

        $shift_code->save();

        flash('Shift Code: '. $shift_code->name.' added!')->success();
        return redirect()->route('shiftcodes.index');
    }
 
    /**
    * Display the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function show($id) {
        return redirect('shiftcodes');
    }

    /**
    * Show the form for editing the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function edit($id) {
        $shift_code = ShiftCode::findOrFail($id);
        // only want HH:mm, not HH:mm:ss from database
        $shift_code->begin_time = substr($shift_code->begin_time, 0, 5);
        $shift_code->end_time = substr($shift_code->end_time, 0, 5);

        if ($shift_code->name == '----'){
            flash('Shift Code: '. $shift_code->name.' indicates "Day Off", and CAN NOT BE CHANGED!')->warning()->important();
            return redirect()->route('shiftcodes.index');
        } else {
            return view('admins.shiftcodes.edit', compact('shift_code'));
        }
    }
 
    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function update(Request $request, $id) {
        $shift_code = ShiftCode::findOrFail($id);

        $this->validate($request, [
            'name'=>'required|min:4|max:4|unique:shift_codes,name,'.$id,
            'begin_time'=>'required|date_format:H:i',
            'end_time'=>'required|date_format:H:i',
        ]);

        $input = $request->only(['name', 'begin_time', 'end_time']);
        $shift_code->fill($input)->save();

        flash('Shift Code: '. $shift_code->name.' updated!')->success();
        return redirect()->route('shiftcodes.index');
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function destroy($id) {
        $shift_code = ShiftCode::findOrFail($id);
        if ($shift_code->name == '----'){
            flash('Shift Code: '. $shift_code->name.' indicates "Day Off", and CAN NOT BE DELETED!')->warning()->important();
            return redirect()->route('shiftcodes.index');
        }
        $count = 0;
        for ($n = 1; $n <= 56; $n++) {
            $d = 'day_' . substr(('00' . $n),-2);
            $count = $count + ScheduleLine::where($d,$id)->count();
        }
        if ($count == 0){
            $shift_code->delete();
            flash('Shift Code: '. $shift_code->name.' deleted!')->success();
            return redirect()->route('shiftcodes.index');
        } else {
            flash('Shift Code: '. $shift_code->name.' is used by at least one schedule line, and was NOT DELETED!')->warning()->important();
            return redirect()->route('shiftcodes.index');
        }
    }
}