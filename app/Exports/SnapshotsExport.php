<?php
   
namespace App\Exports;
   
//use App\Models\User;
// need to move model into their own folder - FIX ME LATER

use App\Schedule;
use App\ScheduleLine;
use App\User;
use App\Snapshot;
use DB;
 
use Maatwebsite\Excel\Concerns\FromCollection;
    
class SnapshotsExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
//        return ScheduleLine::all();

        // return: schedule->title from schedule_id, line_group->code from line_group_id, comment, blackout,	nexus, barge, offsite, line, shift_code->name from day_01 thru day_56

        // an array for a header
        $first = DB::select(DB::raw("SELECT 'BIDDER' as bidder_name, 'SCHEDULE' as title, 'GROUP' as code,
                                        'LINE', 'BLACKOUT' as blackout, 'NEXUS' as nexus, 
                                        'BARGE' as barge, 'OFFSITE' as offsite, 'COMMENT' as comment;"));

        // another array for the data
        $lines = DB::table('snapshots')->join('users', 'user_id', '=', 'users.id')
                                       ->join('schedule_lines', 'schedule_line_id', '=', 'schedule_lines.id')
                                       ->join('schedules', 'schedule_id', '=', 'schedules.id')
                                       ->join('line_groups', 'line_group_id', '=', 'line_groups.id')
                                       ->select('users.name as bidder_name', 'title', 'code', 'line', 
                                                'blackout', 'nexus', 'barge', 'offsite', 'comment')
                                       ->get()->toArray();  // natural table orde is OK
 
        // make a collection from combined arrays
        $merge = collect(array_merge($first, $lines)); 
        return $merge;

    }

}