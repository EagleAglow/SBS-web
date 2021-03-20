<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use App\LogItem;

use Session;

class LogItemController extends Controller {

    public function __construct()
    {
        // verify logged in
        $this->middleware('auth');
        // to enable email verification in this controller
        //  $this->middleware(['auth','verified']);

    }

    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response 
    */
    public function index()
    {
        if (Auth::user()->hasRole('admin')){
//            $log_items = LogItem::orderBy('created_at','DESC')->get();
            $log_items = LogItem::orderByDesc('id')->get();  //  sort in reverse order of id - time is only to seconds
            return view('admins.logitems.index',['log_items' => $log_items,]);
        } else {
            abort('401');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        if (Auth::user()->hasRole('admin')){
//            $log_items = LogItem::orderBy('created_at','DESC')->get();
            $log_items = LogItem::orderByDesc('id')->get();  //  sort in reverse order of id - time is only to seconds
            return view('admins.logitems.index',['log_items' => $log_items,]);
        } else {
            abort('401');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update()
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        //
    }


    /**
     * Remove all log entires
     *
     * @return \Illuminate\Http\Response
     */
    public function purge()
    {
        if (Auth::user()->hasRole('admin')){
            LogItem::truncate();
            LogItem::insertOrIgnore([ 'note' => 'Log Cleared By:' . Auth::user()->name  , 'created_at' => date("Y-m-d H:i:s"), 'updated_at' => date("Y-m-d H:i:s") ]);
            flash('Log Purged!')->success();
//            $log_items = LogItem::orderBy('created_at','DESC')->get();
            $log_items = LogItem::orderByDesc('id')->get();  //  sort in reverse order of id - time is only to seconds
            return view('admins.logitems.index',['log_items' => $log_items,]);
        } else {
            abort('401');
        }
    }
}
