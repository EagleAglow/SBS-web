<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class ProgressScoreboardController extends Controller
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
     * Show the 'home' view (dashboard) for the role with least permissions
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // show to roles: supervisor, admin and superuser
        if (Auth::user()->hasAnyRole('supervisor','admin','superuser')){
            return view('users.progress.dashProgress');
        } else {
            abort('401');
        }
    }
}