<?php

namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
 
use App\Exports\SchedulesExport;
 
use App\Imports\SchedulesImport;
 
use Maatwebsite\Excel\Facades\Excel;
 
use Auth;
 
use App\User;

use App\Schedule;
use App\ScheduleLine;
 
class ImportExportSchedulesController extends Controller
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function index()
    {
        if (Auth::user()->hasRole('admin')){
            return view('admins/excel-csv-import-schedules');
        } else {
             abort('401');
         }
    }
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function importExcelCSVSchedules(Request $request) 
    {
        if (Auth::user()->hasRole('admin')){
            $validatedData = $request->validate([
            'file' => 'required',
            ]);
            Excel::import(new SchedulesImport,$request->file('file'));
            return redirect('admins/excel-csv-file-schedules')->with('status', 'The schedules excel/csv file has been imported.');
        } else {
            abort('401');
        }
    }
 
    /**
    * @return \Illuminate\Support\Collection
    */
    public function exportExcelCSVSchedules($slug) 
    {
        if (Auth::user()->hasRole('admin')){
            return Excel::download(new SchedulesExport, 'schedules.'.$slug);
        } else {
            abort('401');
        }
    }

}
