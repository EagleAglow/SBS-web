<?php

namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
 
use App\Exports\BidOrderExport;
 
use Maatwebsite\Excel\Facades\Excel;

use Auth;
 
use App\User;
use App\Param; 
use App\Schedule;
use App\ScheduleLine;
 

class ExportBidOrderController extends Controller
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function index()
    {
       if (Auth::user()->hasRole('admin')){
            return view('users');
        } else {
            abort('401');
        }
    }

    /**
    * @return \Illuminate\Support\Collection
    */

    public function importExcelBidOrder(Request $request) 
    {
        abort('401');
    }
 
    /**
    * @return \Illuminate\Support\Collection
    */

    public function exportExcelBidOrder($slug) 
    {
        if (Auth::user()->hasRole('admin')){

            return Excel::download(new BidOrderExport, 'bidorder.'.$slug);
        } else {
            abort('401');
        }
    }
}
