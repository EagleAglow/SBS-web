<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
 
use App\Exports\ShiftCodesExport;
 
use App\Imports\ShiftCodesImport;
 
use Maatwebsite\Excel\Facades\Excel;

use Auth;
 
use App\ShiftCode;
 
class ImportExportShiftCodesController extends Controller
{


// should use middle ware instead of check on admin role - FIX ME LATER

    /**
    * @return \Illuminate\Support\Collection
    */
    public function index()
    {
       if (Auth::user()->hasRole('admin')){
           return view('admins/excel-csv-import-shift-codes');
        } else {
            abort('401');
        }
    }
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function importExcelCSVShiftCodes(Request $request) 
    {
        if (Auth::user()->hasRole('admin')){
            $validatedData = $request->validate([
            'file' => 'required',
            ]);
            Excel::import(new ShiftCodesImport,$request->file('file'));
            return redirect('admins/excel-csv-file-shift-codes')->with('status', 'The shift codes excel/csv file has been imported.');
        } else {
            abort('401');
        }
    }
 
    /**
    * @return \Illuminate\Support\Collection
    */
    public function exportExcelCSVShiftCodes($slug) 
    {
        if (Auth::user()->hasRole('admin')){
            return Excel::download(new ShiftCodesExport, 'shift_codes.'.$slug);
        } else {
            abort('401');
        }
    }

}
