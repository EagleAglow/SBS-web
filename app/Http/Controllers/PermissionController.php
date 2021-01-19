<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

//Importing laravel-permission models
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use DB;

use Session;

class PermissionController extends Controller {

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
        $permissions = Permission::all(); //Get all permissions

        return view('permissions.index')->with('permissions', $permissions);
    }

    /**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function create() {
        $roles = Role::get(); //Get all roles

        return view('permissions.create')->with('roles', $roles);
    }

    public function new() {
        //Get all roles and pass it to the view
            $roles = Roles::get();
            return view('permissions.create', ['roles'=>$roles]);
    }

    /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function store(Request $request) {
        $this->validate($request, [
            'name'=>'required|max:40',
        ]);

        $name = $request['name'];
        $permission = new Permission();
        $permission->name = $name;

        $roles = $request['roles'];

        $permission->save();

        if (!empty($request['roles'])) { //If one or more role is selected
            foreach ($roles as $role) {
                $r = Role::where('id', '=', $role)->firstOrFail(); //Match input role to db record

                $permission = Permission::where('name', '=', $name)->first(); //Match input //permission to db record
                $r->givePermissionTo($permission);
            }
        }

        flash('Permission: '. $permission->name .' added!')->success();
        return redirect()->route('permissions.index');

    }

    /**
    * Display the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function show($id) {
        return redirect('permissions');
    }

    /**
    * Show the form for editing the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function edit($id) {
        $permission = Permission::findOrFail($id);
        return view('permissions.edit', compact('permission'));
    }
 
    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function update(Request $request, $id) {
        $permission = Permission::findOrFail($id);
        $this->validate($request, [
            'name'=>'required|max:40',
        ]);
        $input = $request->all();
        $permission->fill($input)->save();

        flash('Permission: '. $permission->name .' updated!')->success();
        return redirect()->route('permissions.index');
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function destroy($id) {
        $permission = Permission::findOrFail($id);

        //Make it impossible to delete this specific permission
        if ($permission->name == "role-permission-manage") {
            flash('You cannot delete poermission to manage permissions!')->warning()->important();
            return redirect()->route('permissions.index');
        }

        // make sure it is not in use
        $count = DB::table('role_has_permissions')->where('permission_id',$id)->count();

        if ($count == 0){
            $permission->delete();
            flash('Permission: '. $permission->name .' deleted!')->success();
            return redirect()->route('permissions.index');
        } else {
            flash('Permission: '. $permission->name .' is in use by one or more roles, and was NOT DELETED!')->warning()->important();
            return redirect()->route('permissions.index');
        }
    }
}