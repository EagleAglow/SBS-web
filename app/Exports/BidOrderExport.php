<?php
   
namespace App\Exports;
   
//use App\Models\User;
// need to move model into their own folder - FIX ME LATER

use App\Schedule;
use App\ScheduleLine;
use App\User;
use DB;
 
use Maatwebsite\Excel\Concerns\FromCollection;
    
class BidOrderExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
//        return ScheduleLine::all();

        // return: schedule->title from schedule_id, line_group->code from line_group_id, comment, blackout,	nexus, barge, offsite, line, shift_code->name from day_01 thru day_56

        // an array for a header - group, lastname - firstname
        $first = DB::select(DB::raw("SELECT 'GROUP' as code, 'BIDDER' as bidder_name, 'ORDER' as bid_order;"));

        // another array for the data
        $users = DB::table('users')->whereNotNull('bid_order')->join('bidder_groups', 'bidder_groups.id', '=', 'users.bidder_group_id')
                                            ->select('bidder_groups.code', 'users.name as bidder_name', 'users.bid_order as bid_order')
                                            ->orderBy('bid_order')->get()->toArray();
 
        // make a collection from combined arrays
        $merge = collect(array_merge($first, $users)); 
        return $merge;

    }

}