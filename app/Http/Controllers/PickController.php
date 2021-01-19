<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use App\BidderGroup;
use App\Pick;
use Auth;

//Importing laravel-permission models
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

//Enables us to output flash messaging
use Session;

class PickController extends Controller {

    public function __construct() {
        //  ManageUsers middleware only passes users who can edit users
        $this->middleware(['auth', 'manageUsers']);
    }

    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index() {
    //Get all users and pass it to the view
        $users = User::all(); 
        return view('superusers.picks.index')->with('users', $users);
    }

    /**
    * Display the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function show($id) {
        return redirect('picks'); 
    }
}