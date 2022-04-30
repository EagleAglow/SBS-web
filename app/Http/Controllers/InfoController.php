<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use Auth;

//Importing laravel-permission models
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

//Enables us to output flash messaging
use Session;

class InfoController extends Controller {

    public function __construct() {
        // verify logged in
        $this->middleware('auth');
    }
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index() {
        if (Auth::user()->hasRole('superuser')){
        //Get all users and pass it to the view
            $users = User::all(); 
            return view('superusers.info.index')->with('users', $users);
        } else {
            abort('401');
        }
    }

}