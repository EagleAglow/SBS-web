<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use App\Param;

use Session;

class SettingController extends Controller {

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

            // make sure param table entries exist - if not, add default
            if (count(Param::where('param_name','name-or-taken')->get()) == 0){
                $param = new Param();
                $param->param_name = 'name-or-taken';
                $param->string_value = 'taken';
                $param->save();
            }

            if (count(Param::where('param_name','next-bidder-email-on-or-off')->get()) == 0){
                $param = new Param();
                $param->param_name = 'next-bidder-email-on-or-off';
                $param->string_value = 'off';
                $param->save();
            }

            if (count(Param::where('param_name','bid-accepted-email-on-or-off')->get()) == 0){
                $param = new Param();
                $param->param_name = 'bid-accepted-email-on-or-off';
                $param->string_value = 'off';
                $param->save();
            }

            if (count(Param::where('param_name','auto-bidding-on-or-off')->get()) == 0){
                $param = new Param();
                $param->param_name = 'auto-bidding-on-or-off';
                $param->string_value = 'off';
                $param->save();
            }

            return view('admins.settings.index');
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
        //
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
     * Turn on display of name on bidding page 
     *
     * @return \Illuminate\Http\Response
     */
    public function name()
    {
        if (Auth::user()->hasRole('admin')){

            // set 'name-or-taken' to 'taken'
            $param = Param::where('param_name','name-or-taken')->first();
            $param->string_value = 'name';
            $param->save();
    
//            flash('Set to show successful bidder name on bid page!')->success();
            return view('admins.settings.index');
        } else {
            abort('401');
        }
    }


    /**
     * Turn on display of "TAKEN" on bidding page 
     *
     * @return \Illuminate\Http\Response
     */
    public function taken()
    {
        if (Auth::user()->hasRole('admin')){

            // set 'name-or-taken' to 'taken'
            $param = Param::where('param_name','name-or-taken')->first();
            $param->string_value = 'taken';
            $param->save();
    
//            flash('Set to show "TAKEN" (instead of successful bidder name) on bid page!')->success();
            return view('admins.settings.index');
        } else {
            abort('401');
        }
    }

    


}
