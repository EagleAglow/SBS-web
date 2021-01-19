<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use App\LineGroup; 
use App\ScheduleLine;

//Importing laravel-permission models
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use Session;

class LineGroupController extends Controller {

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
        $line_groups = LineGroup::all(); //Get all line groups

        return view('admins.linegroups.index')->with('line_groups', $line_groups);
    }

    /**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function create() {

        return view('admins.linegroups.create');
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
            'code'=>'required|min:3|max:4|unique:line_groups,code',
            'order'=>'integer',
        ]);

        $code = $request['code'];
        $order = $request['order'];
        $name = $request['name'];
        $line_group = new LineGroup();
        $line_group->code = $code;
        $line_group->order = $order;
        $line_group->name = $name;

        $line_group->save();

        flash('Line Group'. $line_group->code.' added!')->success();
        return redirect()->route('linegroups.index');
    }
 
    /**
    * Display the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function show($id) {
        return redirect('linegroups');
    }

    /**
    * Show the form for editing the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function edit($id) {
        $line_group = LineGroup::findOrFail($id);

        return view('admins.linegroups.edit', compact('line_group'));
    }
 
    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function update(Request $request, $id) {
        $line_group = LineGroup::findOrFail($id);

        $this->validate($request, [
            'code'=>'required|min:3|max:8|unique:line_groups,code,'.$id,
        ]);

        $input = $request->only(['code', 'order', 'name', ]);
        $line_group->fill($input)->save();

        flash('Line Group: '. $line_group->code.' updated!')->success();
        return redirect()->route('linegroups.index');
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function destroy($id) {
        $line_group = LineGroup::findOrFail($id);

        if ($line_group->code == 'NONE'){
            flash('Shift Code: NONE is used internally and CAN NOT BE DELETED!')->warning()->important();
            return redirect()->route('linegroups.index');
        }
        
        if ($line_group->code == 'TCOM'){
            flash('Shift Code: TCOM is used internally and CAN NOT BE DELETED!')->warning()->important();
            return redirect()->route('linegroups.index');
        }
        
        if ($line_group->code == 'TNON'){
            flash('Shift Code: TNON is used internally and CAN NOT BE DELETED!')->warning()->important();
            return redirect()->route('linegroups.index');
        }
        
        // make sure it is not in use
        $count = ScheduleLine::where('line_group_id',$id)->count();
        if ($count == 0){
            $line_group->delete();
            flash('Shift Code: '. $line_group->code.' deleted!')->success();
            return redirect()->route('linegroups.index');
        } else {
            flash('Shift Code: '. $line_group->code.' is used by at least one schedule line, and was NOT DELETED!')->warning()->important();
            return redirect()->route('linegroups.index');
        }

    }
}