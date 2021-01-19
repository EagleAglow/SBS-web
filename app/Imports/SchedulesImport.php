<?php
namespace App\Imports;
    

// need to move models into their own folder  - FIX ME LATER
use App\Schedule;
use App\ScheduleLine;
use App\LineGroup;
use App\ShiftCode;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
     
class SchedulesImport implements ToModel, WithHeadingRow, WithUpserts
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    // Header: SCHEDULE, SCHEDULE_ID, GROUP, GROUP_ID, LINE, BLACKOUT, NEXUS, BARGE, OFFSITE, COMMENT, DAY_01, DAY_02, ...	DAY_55, DAY_56

    // expects header row, or will fail due to wrong name in array
    // switches header text to lower case for index

    // uses "upserts" - inserts new, updates old, based on email

    public function model(array $row)
    {
        // CSV export shows 1 for true, blank for false, blank causes database error
        $blackout = $row['blackout'];
        if (!$blackout == 1){ $blackout = 0;}
        $nexus    = $row['nexus'];
        if (!$nexus == 1){ $nexus = 0;}
        $barge    = $row['barge'];
        if (!$barge == 1){ $barge = 0;}
        $offsite  = $row['offsite'];
        if (!$offsite == 1){ $offsite = 0;}

        return new ScheduleLine([
            'schedule_id' => $row['schedule_id'],
            'line'     => $row['line'],
            'line_group_id' => $row['group_id'],
            'blackout'     => $blackout,
            'nexus'     => $nexus,
            'barge'     => $barge,
            'offsite'   => $offsite,
            'comment'     => $row['comment'],

            'day_01' => ShiftCode::select('id')->where('name','=', $row['day_01'])->first()->id,
            'day_02' => ShiftCode::select('id')->where('name','=', $row['day_02'])->first()->id,
            'day_03' => ShiftCode::select('id')->where('name','=', $row['day_03'])->first()->id,
            'day_04' => ShiftCode::select('id')->where('name','=', $row['day_04'])->first()->id,
            'day_05' => ShiftCode::select('id')->where('name','=', $row['day_05'])->first()->id,
            'day_06' => ShiftCode::select('id')->where('name','=', $row['day_06'])->first()->id,
            'day_07' => ShiftCode::select('id')->where('name','=', $row['day_07'])->first()->id,
            'day_08' => ShiftCode::select('id')->where('name','=', $row['day_08'])->first()->id,
            'day_09' => ShiftCode::select('id')->where('name','=', $row['day_09'])->first()->id,
            'day_10' => ShiftCode::select('id')->where('name','=', $row['day_10'])->first()->id,
            'day_11' => ShiftCode::select('id')->where('name','=', $row['day_11'])->first()->id,
            'day_12' => ShiftCode::select('id')->where('name','=', $row['day_12'])->first()->id,
            'day_13' => ShiftCode::select('id')->where('name','=', $row['day_13'])->first()->id,
            'day_14' => ShiftCode::select('id')->where('name','=', $row['day_14'])->first()->id,
            'day_15' => ShiftCode::select('id')->where('name','=', $row['day_15'])->first()->id,
            'day_16' => ShiftCode::select('id')->where('name','=', $row['day_16'])->first()->id,
            'day_17' => ShiftCode::select('id')->where('name','=', $row['day_17'])->first()->id,
            'day_18' => ShiftCode::select('id')->where('name','=', $row['day_18'])->first()->id,
            'day_19' => ShiftCode::select('id')->where('name','=', $row['day_19'])->first()->id,
            'day_20' => ShiftCode::select('id')->where('name','=', $row['day_20'])->first()->id,
            'day_21' => ShiftCode::select('id')->where('name','=', $row['day_21'])->first()->id,
            'day_22' => ShiftCode::select('id')->where('name','=', $row['day_22'])->first()->id,
            'day_23' => ShiftCode::select('id')->where('name','=', $row['day_23'])->first()->id,
            'day_24' => ShiftCode::select('id')->where('name','=', $row['day_24'])->first()->id,
            'day_25' => ShiftCode::select('id')->where('name','=', $row['day_25'])->first()->id,
            'day_26' => ShiftCode::select('id')->where('name','=', $row['day_26'])->first()->id,
            'day_27' => ShiftCode::select('id')->where('name','=', $row['day_27'])->first()->id,
            'day_28' => ShiftCode::select('id')->where('name','=', $row['day_28'])->first()->id,
            'day_29' => ShiftCode::select('id')->where('name','=', $row['day_29'])->first()->id,
            'day_30' => ShiftCode::select('id')->where('name','=', $row['day_30'])->first()->id,
            'day_31' => ShiftCode::select('id')->where('name','=', $row['day_31'])->first()->id,
            'day_32' => ShiftCode::select('id')->where('name','=', $row['day_32'])->first()->id,
            'day_33' => ShiftCode::select('id')->where('name','=', $row['day_33'])->first()->id,
            'day_34' => ShiftCode::select('id')->where('name','=', $row['day_34'])->first()->id,
            'day_35' => ShiftCode::select('id')->where('name','=', $row['day_35'])->first()->id,
            'day_36' => ShiftCode::select('id')->where('name','=', $row['day_36'])->first()->id,
            'day_37' => ShiftCode::select('id')->where('name','=', $row['day_37'])->first()->id,
            'day_38' => ShiftCode::select('id')->where('name','=', $row['day_38'])->first()->id,
            'day_39' => ShiftCode::select('id')->where('name','=', $row['day_39'])->first()->id,
            'day_40' => ShiftCode::select('id')->where('name','=', $row['day_40'])->first()->id,
            'day_41' => ShiftCode::select('id')->where('name','=', $row['day_41'])->first()->id,
            'day_42' => ShiftCode::select('id')->where('name','=', $row['day_42'])->first()->id,
            'day_43' => ShiftCode::select('id')->where('name','=', $row['day_43'])->first()->id,
            'day_44' => ShiftCode::select('id')->where('name','=', $row['day_44'])->first()->id,
            'day_45' => ShiftCode::select('id')->where('name','=', $row['day_45'])->first()->id,
            'day_46' => ShiftCode::select('id')->where('name','=', $row['day_46'])->first()->id,
            'day_47' => ShiftCode::select('id')->where('name','=', $row['day_47'])->first()->id,
            'day_48' => ShiftCode::select('id')->where('name','=', $row['day_48'])->first()->id,
            'day_49' => ShiftCode::select('id')->where('name','=', $row['day_49'])->first()->id,
            'day_50' => ShiftCode::select('id')->where('name','=', $row['day_50'])->first()->id,
            'day_51' => ShiftCode::select('id')->where('name','=', $row['day_51'])->first()->id,
            'day_52' => ShiftCode::select('id')->where('name','=', $row['day_52'])->first()->id,
            'day_53' => ShiftCode::select('id')->where('name','=', $row['day_53'])->first()->id,
            'day_54' => ShiftCode::select('id')->where('name','=', $row['day_54'])->first()->id,
            'day_55' => ShiftCode::select('id')->where('name','=', $row['day_55'])->first()->id,
            'day_56' => ShiftCode::select('id')->where('name','=', $row['day_56'])->first()->id,

        ]);
    }

    /**
     * @return string|array
     */
    public function uniqueBy()
    {
            return 'magic';  // name of composite index: unique(['schedule_id', 'line_group_id', 'line'], 'magic');
    }
}
