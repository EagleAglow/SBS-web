<?php
   
namespace App\Exports;
   
use App\Schedule;
use App\ScheduleLine;
use App\LineDay;
use App\User;
use DB;
 
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
    
class BidsExport implements FromArray, WithHeadings
{
    use Exportable;

    public function headings(): array
    {
        $my_header = array('BIDDER','SCHEDULE', 'GROUP', 'LINE', 'BID TIME (GMT)', 'BLACKOUT', 'NEXUS', 'BARGE', 'OFFSITE', 'COMMENT');

        // lookup maximum day number in schedule(s) that were bid
        $day_max = DB::table('schedule_lines')->whereNotNull('user_id')->join('users', 'user_id', '=', 'users.id')
        ->join('schedules', 'schedule_id', '=', 'schedules.id')
        ->join('line_days','schedule_line.id','=','line_days.schedule_line_id')
        ->select('line_days.day_number')
        ->max();

        // add day columns
        for ($n = 1; $n <= day_max; $n++) {
            $d = 'DAY_' . substr(('000' . $n),-3);
            $my_header[] = $d;
        }
        return $my_header;
    }

    public function array(): array
    {
        $lines = DB::table('schedule_lines')->whereNotNull('user_id')->join('users', 'user_id', '=', 'users.id')
                ->join('schedules', 'schedule_id', '=', 'schedules.id')
                ->join('line_groups', 'line_group_id', '=', 'line_groups.id')
                ->select('users.name as bidder_name', 'title', 'code', 'line', 'bid_at', 'blackout', 'nexus', 'barge', 'offsite', 'comment', 'schedule_lines.id')
                ->get();

        // replace 'id' with first day code, add more elements for others - PHP is weird!
        foreach ($lines as $line){
            // safety check - should always pass, but who knows...
            $has_days = DB::table('line_days')->where('line_days.schedule_line_id',$line->id)->count();
            if ($has_days == 0){
                $line->id = '';  // just an empty cell to replace 'id'
            } else {
                // get the codes for the days
                $days = DB::table('line_days')
                ->join('shift_codes','line_days.shift_code_id','=','shift_codes.id')
                ->select('day_number','name')
                ->orderBy('line_days.day_number')
                ->where('line_days.schedule_line_id',$line->id)
                ->get();

                $first = 0; // flag to replace 'id' with 'DAY_001' value
                foreach ($days as $day){
                    if ($first == 0){
                        $line->id = $day->name;
                        $first = 1;
                    } else {
                        $d = 'DAY_' . substr(('000' . $day->day_number),-3);
                        $line->$d = $day->name;    // see, PHP is weird!
                    }
                }
            }
        }
        return $lines->toArray();
    }
}