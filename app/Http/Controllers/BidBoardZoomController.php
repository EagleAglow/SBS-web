<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use App\Schedule;
use App\ScheduleLine;
use App\LineGroup;
use App\User;
use DB;

//Importing laravel-permission models
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use Session;

class BidBoardZoomController extends Controller {
     // shows a single line complete calendar

    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
    public function store(Request $request)
    {
        //
    }



    // show entire line schedule on one page
    public function line($id, Request $request)
    {
        $id = $request['id'];
        if (!isset($id)){
            abort('401');
        }

        $schedule_line = ScheduleLine::findOrFail($id);
        $schedule = Schedule::findOrFail( $schedule_line->schedule_id );
        $line_group = LineGroup::findOrFail ($schedule_line->line_group_id);





        // pass variables through, so we get them on return to original page
        $schedule_lines = $request['schedule_lines'];
//        $line_group = $request['line_group'];
        $first_day = $request['first_day'];
        $last_day = $request['last_day'];
        $page = $request['page'];
        $my_selection = $request['my_selection'];
        $next_selection = $request['next_selection'];
        $show_all = $request['show_all'];
        $trap = $request['trap'];
        $list_codes = $request['list_codes'];

        return view('bidboard/line',
            ['schedule'=> $schedule,
            'schedule_lines'=> $schedule_lines,
            'schedule_line'=> $schedule_line,
            'line_group' => $line_group, 
            'first_day' => $first_day,
            'last_day'=> $last_day,
            'page' => $page,
            'id' => $id,
            'my_selection' => $my_selection,
            'next_selection' => $next_selection,
            'show_all' => $show_all,
            'trap' => $trap,
            'list_codes' => $list_codes
            ]);
    }








    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }



}