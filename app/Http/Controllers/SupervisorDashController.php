<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupervisorDashController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // verify logged in
        $this->middleware('auth');
        // to enable email verification in this controller
        //  $this->middleware(['auth','verified']);
    }

    /**
     * Show the supervisor dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (Auth::user()->hasRole('supervisor')){
            return view('supervisors.dash');
        } else {
            abort('401');
        }
    }
}