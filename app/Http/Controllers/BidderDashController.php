<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Schedule;
use App\ScheduleLine;
use App\Pick;

class BidderDashController extends Controller
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
     * Show the bidder dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (Auth::user()->hasAnyRole('bidder-demo','bidder-irpa','bidder-tsu','bidder-oidp','bidder-tcom','bidder-tnon')){
            return view('bidders.dash');
        } else {
            abort('401');
        }
    }

}
