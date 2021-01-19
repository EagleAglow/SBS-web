<?php

namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
 
use App\Exports\BidsExport;
 
use Maatwebsite\Excel\Facades\Excel;

use Auth;
 
use App\User;
use App\Param; 
use App\Schedule;
use App\ScheduleLine;
 

class ExportBidsController extends Controller
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function index()
    {
       if (Auth::user()->hasRole('admin')){
            return view('admins/dashBidding');
        } else {
            abort('401');
        }
    }

    /**
    * @return \Illuminate\Support\Collection
    */

    public function importExcelBids(Request $request) 
    {
        abort('401');
    }
 
    /**
    * @return \Illuminate\Support\Collection
    */

    public function exportExcelBids($slug) 
    {
        if (Auth::user()->hasRole('admin')){
            // if bidding state is complete, switch from complete to reported
            $state_param = Param::where('param_name','bidding-state')->first();
            if (isset($state_param)){
                $test = $state_param->string_value;
                if ($test == 'complete') {
                    $state_param->update(['string_value' => 'reported']);
                }
            }

            return Excel::download(new BidsExport, 'bids.'.$slug);
        } else {
            abort('401');
        }
    }
}
