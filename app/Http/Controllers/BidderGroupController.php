<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use App\BidderGroup; 
use App\User;

//Importing laravel-permission models
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use Session;

class BidderGroupController extends Controller {

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
        $bidder_groups = BidderGroup::all(); //Get all bidder groups

        return view('admins.biddergroups.index')->with('bidder_groups', $bidder_groups);
    }

    /**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function create() {

        return view('admins.biddergroups.create');
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
            'code'=>'required|min:3|max:8|alpha_dash|unique:bidder_groups,code',
            'order'=>'integer',
        ]);

        $code = strtoupper($request['code']);
        $order = $request['order'];
        $name = $request['name'];
        $bidder_group = new BidderGroup();
        $bidder_group->code = $code;
        $bidder_group->order = $order;
        $bidder_group->name = $name;

        $bidder_group->save();

        flash('Bidder Group: '. $bidder_group->code.' added!')->success();
        return redirect()->route('biddergroups.index');
    }
 
    /**
    * Display the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function show($id) {
        return redirect('biddergroups');
    }

    /**
    * Show the form for editing the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function edit($id) {
        $bidder_group = BidderGroup::findOrFail($id);
        $roles = Role::get(); //Get all roles
        return view('admins.biddergroups.edit', compact('bidder_group', 'roles')); //pass group and roles data to view
    }
 
    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function update(Request $request, $id) {
        $bidder_group = BidderGroup::findOrFail($id);

        $this->validate($request, [
            'code'=>'required|min:3|max:8|alpha_dash|unique:bidder_groups,code,'.$id,
        ]);

        $code = strtoupper($request['code']);
        $request['code'] = $code;
        $input = $request->only(['code', 'order', 'name', ]);
        $bidder_group->fill($input)->save();










        

        flash('Bidder Group: '. $bidder_group->code.' updated!')->success();
        return redirect()->route('biddergroups.index'); 
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function destroy($id) {
        $bidder_group = BidderGroup::findOrFail($id);

        if ($bidder_group->code == 'NONE'){
            flash('Group Code: NONE is used internally and CAN NOT BE DELETED!')->warning()->important();
            return redirect()->route('biddergroups.index');
        }
        
        // make sure it is not in use
        $count = User::where('bidder_group_id',$id)->count();
        if ($count == 0){
            $bidder_group->delete();
            flash('Shift Code: '. $bidder_group->code.' deleted!')->success();
            return redirect()->route('biddergroups.index');
        } else {
            flash('Shift Code: '. $bidder_group->code.' is set for at least one user, and was NOT DELETED!')->warning()->important();
            return redirect()->route('biddergroups.index');
        }

    }
}