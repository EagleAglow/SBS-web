<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
//Importing laravel-permission models
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use Session;

class RoleController extends Controller {

    public function __construct() {
        //  ManageRolesAndPermissions middleware only passes users who can edit users and schedules
        $this->middleware(['auth', 'manageRolesAndPermissions']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $roles = Role::all();//Get all roles

        return view('roles.index')->with('roles', $roles);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        $permissions = Permission::all();//Get all permissions
        return view('roles.create', ['permissions'=>$permissions]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        //Validate name and permissions field
        $this->validate($request, [
            'name'=>'required|unique:roles|max:15',
            'permissions' =>'required',
            ]
        );

        $name = $request['name'];
        $role = new Role();
        $role->name = $name;
        $role->save();

        $permissions = $request['permissions'];
        //Looping thru selected permissions
        foreach ($permissions as $permission) {
            $p = Permission::where('id', '=', $permission)->firstOrFail(); 
             //Fetch the newly created role and assign permission
            $role = Role::where('name', '=', $name)->first(); 
            $role->givePermissionTo($p);
        }

        flash('Role: '. $role->name.' added!')->success();
        return redirect()->route('roles.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        return redirect('roles');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        $role = Role::findOrFail($id);
        $permissions = Permission::all();

        return view('roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {

        $role = Role::findOrFail($id);//Get role with the given id
        // Make it impossible to change any role used in code:
        // superuser, admin, supervisor, bidder-active, bid-for-tcom, bid-for-tnon, bid-for-oidp, bid-for-irpa, bid-for-tsu, bid-for-demo
        // 
        $keep = array('superuser', 'admin', 'supervisor', 'bidder-active', 'bid-for-tcom', 'bid-for-tnon', 'bid-for-oidp', 'bid-for-irpa', 'bid-for-tsu', 'bid-for-demo');
        if (in_array($role->name,$keep)){
            flash('You cannot change the "' . $role->name . '" role! (Used internally)')->warning()->important();
            return redirect()->route('roles.index');
        }

        //Validate name and permission fields
        $this->validate($request, [
            'name'=>'required|max:15|unique:roles,name,'.$id,
            'permissions' =>'required',
        ]);

        $input = $request->except(['permissions']);
        $permissions = $request['permissions'];
        $role->fill($input)->save();

        $p_all = Permission::all();//Get all permissions

        foreach ($p_all as $p) {
            $role->revokePermissionTo($p); //Remove all permissions associated with role
        }

        foreach ($permissions as $permission) {
            $p = Permission::where('id', '=', $permission)->firstOrFail(); //Get corresponding form //permission in db
            $role->givePermissionTo($p);  //Assign permission to role
        }

        flash('Role'. $role->name.' updated!')->success();
        return redirect()->route('roles.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        // Make it impossible to delete any role used in code:
        // superuser, admin, supervisor, bidder-active, bid-for-tcom, bid-for-tnon, bid-for-oidp, bid-for-irpa, bid-for-tsu, bid-for-demo
        // 
        $keep = array('superuser', 'admin', 'supervisor', 'bidder-active', 'bid-for-tcom', 'bid-for-tnon', 'bid-for-oidp', 'bid-for-irpa', 'bid-for-tsu', 'bid-for-demo');
        if (in_array($role->name,$keep)){
            flash('You cannot delete the "' . $role->name . '" role! (Used internally)')->warning()->important();
            return redirect()->route('roles.index');
        } else {
            $role->delete();
            flash('Role deleted!')->success();
            return redirect()->route('roles.index');
        }

    }
}