<?php
   
namespace App\Exports;
   
use App\ShiftCode;
use DB;
 
use Maatwebsite\Excel\Concerns\FromCollection;
    
class ShiftCodesExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
//        return User::all();

        // an array
        $first = DB::select(DB::raw("SELECT 'CODE' as name, 'BEGIN' as begin_time, 'END' as end_time;"));
        // another array
//        $codes = DB::table('shift_codes')->select('name', 'begin_time', 'end_time')->get()->toArray();
//        $codes = DB::select(DB::raw( "SELECT name, DATE_FORMAT('begin_time', '%H:%i') AS 'begin-time', DATE_FORMAT('end_time', '%H:%i') AS 'end-time' FROM 'shift_codes';" ))->get()->toArray();
        $codes = DB::select(DB::raw( "SELECT name, DATE_FORMAT(begin_time, '%H:%i'), DATE_FORMAT(end_time, '%H:%i') FROM shift_codes WHERE ((name <> '----') AND (name <> '????')) ORDER BY name;" ));
        // make a collection from combined arrays
        $merge = collect(array_merge($first, $codes)); 
        return $merge;

    }

}
