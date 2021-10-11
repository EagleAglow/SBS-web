<?php
   
namespace App\Exports;
   
use App\Schedule;
use App\ScheduleLine;
use App\User;
use App\Snapshot;
use DB;
 
use Maatwebsite\Excel\Concerns\FromCollection;
    
class SnapshotsExport implements FromCollection
{
    public function collection()
    {
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