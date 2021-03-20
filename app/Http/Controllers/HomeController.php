<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class HomeController extends Controller
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
        // reroute to different dashboards
        if (Auth::user()->hasAnyRole('bid-for-demo','bid-for-irpa','bid-for-tsu','bid-for-oidp','bid-for-tcom','bid-for-tnon')){
                return view('bidders.dash');
        } else {
            if ( Auth::user()->hasRole('supervisor')) {
                return view('supervisors.dash');
            } else {
                if ( Auth::user()->hasRole('admin')) {
                    return view('admins.dash');
                } else {
                    if ( Auth::user()->hasRole('superuser')) {
                        return view('superusers.dash');
                    } else {
                        // no role ?
                        return view('home');
                    }
                }
            }
        }
    }
}