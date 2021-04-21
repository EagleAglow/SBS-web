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

    // Header: SCHEDULE (Title), (Line) GROUP, LINE, BLACKOUT, NEXUS, BARGE, OFFSITE, COMMENT, DAY_01, DAY_02, ...	DAY_55, DAY_56

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

        $comment = $row['comment'];
        if (!isset($comment)) { $comment = ''; }

        // process record, looking for errors - if any problem, record goes to an "Import Errors" schedule
        // look for a schedule titled "Import Errors" - if not found, create it
        $schedules = Schedule::select('id')->where('title','=', 'Import Errors')->get();
        if (count($schedules)>0){
            $error_schedule_id = $schedules->first()->id;
        } else {
            $schedule = new Schedule();
            $schedule->title = 'Import Errors';
            $schedule->cycle_count = 1;
            $schedule->start = '2000-01-01';
            $schedule->active = 0;
            $schedule->approved = 0;
            $schedule->save();
            $error_schedule_id = $schedule->id;
        }
        
        $schedule_title = $row['schedule'];
        $schedules = Schedule::select('id')->where('title','=', $schedule_title)->get();
        if (count($schedules)>0){
            $schedule_id = $schedules->first()->id;
        } else {
            $schedule_id = $error_schedule_id;
            $comment = $comment . 'Unknown schedule: ' . $schedule_title;
        }

        $line_group_code = $row['group'];
        $line_groups = LineGroup::select('id')->where('code','=', $line_group_code)->get();
        if (count($line_groups)>0){
            $line_group_id = $line_groups->first()->id;
        } else {
            $line_group_id = LineGroup::select('id')->where('code','=', 'NONE')->get()->first()->id;
            $schedule_id = $error_schedule_id;
            if (strlen($comment)>0){ $comment = $comment . ' / ';}
            $comment = $comment . 'Unknown group: ' . $line_group_code;
        }

        // use day off code for any errors
        $error_id = ShiftCode::select('id')->where('name','=', '----')->get()->first()->id;
        // check shift codes
        $bad_codes = '';
        for ($n = 1; $n <= 56; $n++) {
            $d = 'day_' . substr(('00' . $n),-2);
            $a_code = $row[$d];
            // accept null/blank/leading dash for day off
            if (!isset($a_code)){
                $a_code = '----';
            }
            if ($a_code) == ''){
                $a_code = '----';
            }
            if (substr($a_code,0,1) == '-'){
                $a_code = '----';
            }
            $code_ids = ShiftCode::select('id')->where('name','=', $a_code)->get();
            if (count($code_ids)>0){
                $$d = $code_ids->first()->id;
            } else {
                $$d = $error_id;
                $schedule_id = $error_schedule_id;
                if (strlen($bad_codes)>0){
                    if (strpos($bad_codes, $a_code ) !== false) {
                        // do nothing
                    } else {
                        $bad_codes = $bad_codes . ' ' . $a_code;
                    }
                } else {
                    $bad_codes = $a_code;
                }
            }
        }
        if (strlen($bad_codes) > 0){
            if (strlen($comment)>0){ $comment = $comment . ' / ';}
            $comment = $comment . 'Unknown shift code(s): ' . $bad_codes;
        }
    



        return new ScheduleLine([
            'schedule_id' => $schedule_id,
            'line'     => $row['line'],
            // special handling for "natural sort"
            'line_natural' => ScheduleLine::natural($row['line']),
            'line_group_id' => $line_group_id,
            'blackout'     => $blackout,
            'nexus'     => $nexus,
            'barge'     => $barge,
            'offsite'   => $offsite,
            'comment'   => $comment,

            'day_01' => $day_01,
            'day_02' => $day_02,
            'day_03' => $day_03,
            'day_04' => $day_04,
            'day_05' => $day_05,
            'day_06' => $day_06,
            'day_07' => $day_07,
            'day_08' => $day_08,
            'day_09' => $day_09,
            'day_10' => $day_10,
            'day_11' => $day_11,
            'day_12' => $day_12,
            'day_13' => $day_13,
            'day_14' => $day_14,
            'day_15' => $day_15,
            'day_16' => $day_16,
            'day_17' => $day_17,
            'day_18' => $day_18,
            'day_19' => $day_19,
            'day_20' => $day_20,
            'day_21' => $day_21,
            'day_22' => $day_22,
            'day_23' => $day_23,
            'day_24' => $day_24,
            'day_25' => $day_25,
            'day_26' => $day_26,
            'day_27' => $day_27,
            'day_28' => $day_28,
            'day_29' => $day_29,
            'day_30' => $day_30,
            'day_31' => $day_31,
            'day_32' => $day_32,
            'day_33' => $day_33,
            'day_34' => $day_34,
            'day_35' => $day_35,
            'day_36' => $day_36,
            'day_37' => $day_37,
            'day_38' => $day_38,
            'day_39' => $day_39,
            'day_40' => $day_40,
            'day_41' => $day_41,
            'day_42' => $day_42,
            'day_43' => $day_43,
            'day_44' => $day_44,
            'day_45' => $day_45,
            'day_46' => $day_46,
            'day_47' => $day_47,
            'day_48' => $day_48,
            'day_49' => $day_49,
            'day_50' => $day_50,
            'day_51' => $day_51,
            'day_52' => $day_52,
            'day_53' => $day_53,
            'day_54' => $day_54,
            'day_55' => $day_55,
            'day_56' => $day_56,

/*            
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

*/            

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
