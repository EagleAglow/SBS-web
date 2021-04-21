<?php
namespace App\Imports;
    

// need to move models into their own folder  - FIX ME LATER
use DB;
use App\ShiftCode;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class ShiftCodesImport implements ToModel, WithHeadingRow, WithUpserts
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    
    // Header: CODE, BEGIN-TIME, END-TIME 
    // expects header row, or will miss an entry...
    // ****** REQUIRES header row in order to index $row array with field names ********
    // switches header text to lower case for index

    // uses "upserts" - inserts new, updates old, based on code

    public function model(array $row)
    {

//        $begin_time = $row['begin-time'];
//        $end_time = $row['end-time'];


        return new ShiftCode([
            'name'         => $row['code'],
            'begin_time'   => date("H:i:s", strtotime($row['begin'])),
            'end_time'     => date("H:i:s", strtotime($row['end'])),
        ]);
    }

    /**
     * @return string|array
     */
    public function uniqueBy()
    {
        return 'name';
    }

}
