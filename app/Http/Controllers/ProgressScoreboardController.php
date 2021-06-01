<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use App\User;
use Spatie\Permission\Traits\HasRoles;

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
            // collect mirror bidders
            $mirror_list = 'Mirror Bidders: ';
            $mirrors = User::role('flag-mirror')->get();
            if (count($mirrors) == 0){
                $mirror_list = $mirror_list . 'None';
            } else {
                foreach ($mirrors as $mirror){
                    $mirror_list = $mirror_list . $mirror->name . ', ';
                }
                $mirror_list = substr_replace($mirror_list ,"",-2);
            }

            // collect snapshot bidders
            $snapshot_list = 'Snapshot Bidders: ';
            $snapshots = User::role('flag-snapshot')->get();
            if ($mirrors->count() == 0){
                $snapshot_list = $snapshot_list . 'None';
            } else {
                foreach ($snapshots as $snapshot){
                    $snapshot_list = $snapshot_list . $snapshot->name . ', ';
                }
                $snapshot_list = substr_replace($snapshot_list ,"",-2);
            }

            // collect deferred bidders
            $deferred_list = 'Deferred Bidders: ';
            $deferred = User::role('flag-deferred')->get();
            if ($deferred->count() == 0){
                $deferred_list = $deferred_list . 'None';
            } else {
                foreach ($deferred as $deferred_one){
                    $deferred_list = $deferred_list . $deferred_one->name . ', ';
                }
                $deferred_list = substr_replace($deferred_list ,"",-2);
            }



            return view('users.progress.dashProgress',['mirror_list' => $mirror_list, 'deferred_list' => $deferred_list, 'snapshot_list' => $snapshot_list]);
        } else {
            abort('401');
        }
    }
}