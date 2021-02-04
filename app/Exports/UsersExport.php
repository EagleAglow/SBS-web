<?php
   
namespace App\Exports;
   
//use App\Models\User;
// need to move model into their own folder - FIX ME LATER

use App\User;
use DB;
 
use Maatwebsite\Excel\Concerns\FromCollection;
    
class UsersExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
//        return User::all();

        // an array
        $first = DB::select(DB::raw("SELECT 'NAME' as name, 'EMAIL' as email, 'PHONE' as phone_number, 'SENIORITY' as bidder_primary_order, 'GROUP' as code;"));
        // another array
        $users = DB::table('users')->join('bidder_groups', 'bidder_group_id', '=', 'bidder_groups.id')->select('users.name', 'email', 'bidder_primary_order', 'code')->get()->toArray();
        // make a collection from combined arrays
        $merge = collect(array_merge($first, $users)); 
        return $merge;

    }

}