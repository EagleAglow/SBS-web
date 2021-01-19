<?php
   
namespace App\Exports;
   
//use App\Models\User;
// need to move model into their own folder - FIX ME LATER

use App\Schedule;
use App\ScheduleLine;
use App\User;
use DB;
 
use Maatwebsite\Excel\Concerns\FromCollection;
    
class BidsExport implements FromCollection
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
                                        'LINE', 'BID TIME (GMT)' as bid_at, 'BLACKOUT' as blackout, 'NEXUS' as nexus, 
                                        'BARGE' as barge, 'OFFSITE' as offsite, 'COMMENT' as comment,
                                        'DAY_01' as n_01, 'DAY_02' as n_02, 'DAY_03' as n_03, 'DAY_04' as n_04,
                                        'DAY_05' as n_05, 'DAY_06' as n_06, 'DAY_07' as n_07, 'DAY_08' as n_08,
                                        'DAY_09' as n_09, 'DAY_10' as n_10, 'DAY_11' as n_11, 'DAY_12' as n_12,
                                        'DAY_13' as n_13, 'DAY_14' as n_14, 'DAY_15' as n_15, 'DAY_16' as n_16,
                                        'DAY_17' as n_17, 'DAY_18' as n_18, 'DAY_19' as n_19, 'DAY_20' as n_20,
                                        'DAY_21' as n_21, 'DAY_22' as n_22, 'DAY_23' as n_23, 'DAY_24' as n_24,
                                        'DAY_25' as n_25, 'DAY_26' as n_26, 'DAY_27' as n_27, 'DAY_28' as n_28,
                                        'DAY_29' as n_29, 'DAY_30' as n_30, 'DAY_31' as n_31, 'DAY_32' as n_32,
                                        'DAY_33' as n_33, 'DAY_34' as n_34, 'DAY_35' as n_35, 'DAY_36' as n_36,
                                        'DAY_37' as n_37, 'DAY_38' as n_38, 'DAY_39' as n_39, 'DAY_40' as n_40,
                                        'DAY_41' as n_41, 'DAY_42' as n_42, 'DAY_43' as n_43, 'DAY_44' as n_44,
                                        'DAY_45' as n_45, 'DAY_46' as n_46, 'DAY_47' as n_47, 'DAY_48' as n_48,
                                        'DAY_49' as n_49, 'DAY_50' as n_50, 'DAY_51' as n_51, 'DAY_52' as n_52,
                                        'DAY_53' as n_53, 'DAY_54' as n_54, 'DAY_55' as n_55, 'DAY_56' as n_56;"));

        // another array for the data
        $lines = DB::table('schedule_lines')->whereNotNull('user_id')->join('users', 'user_id', '=', 'users.id')
                                            ->join('schedules', 'schedule_id', '=', 'schedules.id')
                                            ->join('line_groups', 'line_group_id', '=', 'line_groups.id')
                                            ->join('shift_codes as sc_01', 'day_01', '=', 'sc_01.id')
                                            ->join('shift_codes as sc_02', 'day_02', '=', 'sc_02.id')
                                            ->join('shift_codes as sc_03', 'day_03', '=', 'sc_03.id')
                                            ->join('shift_codes as sc_04', 'day_04', '=', 'sc_04.id')
                                            ->join('shift_codes as sc_05', 'day_05', '=', 'sc_05.id')
                                            ->join('shift_codes as sc_06', 'day_06', '=', 'sc_06.id')
                                            ->join('shift_codes as sc_07', 'day_07', '=', 'sc_07.id')
                                            ->join('shift_codes as sc_08', 'day_08', '=', 'sc_08.id')
                                            ->join('shift_codes as sc_09', 'day_09', '=', 'sc_09.id')
                                            ->join('shift_codes as sc_10', 'day_10', '=', 'sc_10.id')
                                            ->join('shift_codes as sc_11', 'day_11', '=', 'sc_11.id')
                                            ->join('shift_codes as sc_12', 'day_12', '=', 'sc_12.id')
                                            ->join('shift_codes as sc_13', 'day_13', '=', 'sc_13.id')
                                            ->join('shift_codes as sc_14', 'day_14', '=', 'sc_14.id')
                                            ->join('shift_codes as sc_15', 'day_15', '=', 'sc_15.id')
                                            ->join('shift_codes as sc_16', 'day_16', '=', 'sc_16.id')
                                            ->join('shift_codes as sc_17', 'day_17', '=', 'sc_17.id')
                                            ->join('shift_codes as sc_18', 'day_18', '=', 'sc_18.id')
                                            ->join('shift_codes as sc_19', 'day_19', '=', 'sc_19.id')
                                            ->join('shift_codes as sc_20', 'day_20', '=', 'sc_20.id')
                                            ->join('shift_codes as sc_21', 'day_21', '=', 'sc_21.id')
                                            ->join('shift_codes as sc_22', 'day_22', '=', 'sc_22.id')
                                            ->join('shift_codes as sc_23', 'day_23', '=', 'sc_23.id')
                                            ->join('shift_codes as sc_24', 'day_24', '=', 'sc_24.id')
                                            ->join('shift_codes as sc_25', 'day_25', '=', 'sc_25.id')
                                            ->join('shift_codes as sc_26', 'day_26', '=', 'sc_26.id')
                                            ->join('shift_codes as sc_27', 'day_27', '=', 'sc_27.id')
                                            ->join('shift_codes as sc_28', 'day_28', '=', 'sc_28.id')
                                            ->join('shift_codes as sc_29', 'day_29', '=', 'sc_29.id')
                                            ->join('shift_codes as sc_30', 'day_30', '=', 'sc_30.id')
                                            ->join('shift_codes as sc_31', 'day_31', '=', 'sc_31.id')
                                            ->join('shift_codes as sc_32', 'day_32', '=', 'sc_32.id')
                                            ->join('shift_codes as sc_33', 'day_33', '=', 'sc_33.id')
                                            ->join('shift_codes as sc_34', 'day_34', '=', 'sc_34.id')
                                            ->join('shift_codes as sc_35', 'day_35', '=', 'sc_35.id')
                                            ->join('shift_codes as sc_36', 'day_36', '=', 'sc_36.id')
                                            ->join('shift_codes as sc_37', 'day_37', '=', 'sc_37.id')
                                            ->join('shift_codes as sc_38', 'day_38', '=', 'sc_38.id')
                                            ->join('shift_codes as sc_39', 'day_39', '=', 'sc_39.id')
                                            ->join('shift_codes as sc_40', 'day_40', '=', 'sc_40.id')
                                            ->join('shift_codes as sc_41', 'day_41', '=', 'sc_41.id')
                                            ->join('shift_codes as sc_42', 'day_42', '=', 'sc_42.id')
                                            ->join('shift_codes as sc_43', 'day_43', '=', 'sc_43.id')
                                            ->join('shift_codes as sc_44', 'day_44', '=', 'sc_44.id')
                                            ->join('shift_codes as sc_45', 'day_45', '=', 'sc_45.id')
                                            ->join('shift_codes as sc_46', 'day_46', '=', 'sc_46.id')
                                            ->join('shift_codes as sc_47', 'day_47', '=', 'sc_47.id')
                                            ->join('shift_codes as sc_48', 'day_48', '=', 'sc_48.id')
                                            ->join('shift_codes as sc_49', 'day_49', '=', 'sc_49.id')
                                            ->join('shift_codes as sc_50', 'day_50', '=', 'sc_50.id')
                                            ->join('shift_codes as sc_51', 'day_51', '=', 'sc_51.id')
                                            ->join('shift_codes as sc_52', 'day_52', '=', 'sc_52.id')
                                            ->join('shift_codes as sc_53', 'day_53', '=', 'sc_53.id')
                                            ->join('shift_codes as sc_54', 'day_54', '=', 'sc_54.id')
                                            ->join('shift_codes as sc_55', 'day_55', '=', 'sc_55.id')
                                            ->join('shift_codes as sc_56', 'day_56', '=', 'sc_56.id')
                                            ->select('users.name as bidder_name', 'title', 'code', 'line', 'bid_at',
                                            'blackout', 'nexus', 'barge', 'offsite', 'comment',
                                            'sc_01.name as n_01', 'sc_02.name as n_02', 'sc_03.name as n_03', 'sc_04.name as n_04',
                                            'sc_05.name as n_05', 'sc_06.name as n_06', 'sc_07.name as n_07', 'sc_08.name as n_08',
                                            'sc_09.name as n_09', 'sc_10.name as n_10', 'sc_11.name as n_11', 'sc_12.name as n_12',
                                            'sc_13.name as n_13', 'sc_14.name as n_14', 'sc_15.name as n_15', 'sc_16.name as n_16',
                                            'sc_17.name as n_17', 'sc_18.name as n_18', 'sc_19.name as n_19', 'sc_20.name as n_20',
                                            'sc_21.name as n_21', 'sc_22.name as n_22', 'sc_23.name as n_23', 'sc_24.name as n_24',
                                            'sc_25.name as n_25', 'sc_26.name as n_26', 'sc_27.name as n_27', 'sc_28.name as n_28',
                                            'sc_29.name as n_29', 'sc_30.name as n_30', 'sc_31.name as n_31', 'sc_32.name as n_32',
                                            'sc_33.name as n_33', 'sc_34.name as n_34', 'sc_35.name as n_35', 'sc_36.name as n_36',
                                            'sc_37.name as n_37', 'sc_38.name as n_38', 'sc_39.name as n_39', 'sc_40.name as n_40',
                                            'sc_41.name as n_41', 'sc_42.name as n_42', 'sc_43.name as n_43', 'sc_44.name as n_44',
                                            'sc_45.name as n_45', 'sc_46.name as n_46', 'sc_47.name as n_47', 'sc_48.name as n_48',
                                            'sc_49.name as n_49', 'sc_50.name as n_50', 'sc_51.name as n_51', 'sc_52.name as n_52',
                                            'sc_53.name as n_53', 'sc_54.name as n_54', 'sc_55.name as n_55', 'sc_56.name as n_56')
                                            ->orderBy('schedule_id')->orderBy('line_group_id')->orderBy('line')->get()->toArray();
 
        // make a collection from combined arrays
        $merge = collect(array_merge($first, $lines)); 
        return $merge;

    }

}