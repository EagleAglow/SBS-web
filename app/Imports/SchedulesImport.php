<?php
namespace App\Imports;
    

// need to move models into their own folder  - FIX ME LATER
use App\Schedule;
use App\ScheduleLine;
use App\LineGroup;
use App\ShiftCode;
use App\LineDay;

use App\LogItem;   // REMOVE ME LATER - for debug logging, see below

//use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
     
//class SchedulesImport implements ToModel, WithHeadingRow, WithUpserts
class SchedulesImport implements ToCollection, WithHeadingRow, WithUpserts
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    // Header: SCHEDULE (Title), (Line) GROUP, LINE, BLACKOUT, NEXUS, BARGE, OFFSITE, COMMENT, DAY_001, DAY_002, etc.
    // expects header row, or will fail due to wrong name in array
    // switches header text to lower case for index (although these are not actually uded for the days data)
    // schedule_lines table uses "upserts" - inserts new, updates old, based on composite index: unique(['schedule_id', 'line_group_id', 'line'], 'magic');

    public function collection(Collection $rows)
    {
        // use missing data code for any errors
        $error_id = ShiftCode::select('id')->where('name','=', '????')->get()->first()->id;

        // look for a schedule titled "Import Errors" - if not found, create it
        $schedules = Schedule::select('id')->where('title','=', 'Import Errors')->get();
        if (count($schedules)>0){
            $error_schedule_id = $schedules->first()->id;
        } else {                                          // create a place to collect errors
            $schedule = new Schedule();
            $schedule->title = 'Import Errors';
            $schedule->cycle_count = 1;
            $schedule->cycle_days = 7;                    // default - update this at end of import process, if there are any lines
            $schedule->start = '2000-01-01';
            $schedule->active = 0;
            $schedule->approved = 0;
            $schedule->save();
            $error_schedule_id = $schedule->id;
        }

        foreach ($rows as $row) 
        {
            // handle CSV export format, which has 1 for true, blank for false, blank causes database error
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
            $schedule_title = $row['schedule'];
            $schedules = Schedule::select('id','cycle_days')->where('title','=', $schedule_title)->get();
            if (count($schedules)>0){
                $schedule_id = $schedules->first()->id;
                $cycle_days = $schedules->first()->cycle_days;
            } else {                                             // title not found - add to  import errors
                $schedule_id = $error_schedule_id;
                $comment = $comment . 'Unknown schedule: ' . $schedule_title;
            }

            $line_group_code = $row['group'];
            $line_groups = LineGroup::select('id')->where('code','=', $line_group_code)->get();
            if (count($line_groups)>0){
                $line_group_id = $line_groups->first()->id;
            } else {                                             // line group not found
                $line_group_id = LineGroup::select('id')->where('code','=', 'NONE')->get()->first()->id;
                $schedule_id = $error_schedule_id;
                if (strlen($comment)>0){ $comment = $comment . ' / ';}
                $comment = $comment . 'Unknown group: ' . $line_group_code;
            }

            // assume 9 columns before data for 'Day_001', etc., so day 1 = column 10 = index 9 
            // get day count from column count - reads the actual row data, not header
            $days = $row->slice(8);  // collection of just days shift code data
            $days = $days->values($days);  // just the values, in order (I assume.....)
            $max_days = $days->count(); 

            // === Important === convert items in array $days from character code to code_id ===
            // check shift codes in columns
            $bad_codes = '';
            for ($n = 0; $n <= ($max_days -1); $n++) {
                $a_code = $days[$n];
                if (!isset($a_code)){   // null?
                    $a_code = '????';
                }
                if ($a_code == ''){  // empty
                    $a_code = '????';
                }
                // accept leading dash for day off  - note same tests below for actual import
                if (substr($a_code,0,1) == '-'){  // leading dash
                    $a_code = '----';
                }
                if (substr($a_code,0,2) == ' -'){  // leading space, followed by dash
                    $a_code = '----';
                }
                $code_ids = ShiftCode::select('id')->where('name','=', $a_code)->get();
                if (count($code_ids) == 0){  // found no matching code
                    $schedule_id = $error_schedule_id;  // put this data in error collection
                    if (strlen($bad_codes)>0){
                        if (strpos($bad_codes, $a_code ) !== false) {
                            // do nothing
                        } else {
                            $bad_codes = $bad_codes . ' ' . $a_code;
                            $days[$n] = $error_id;  // fix up
                        }
                    } else {
                        $bad_codes = $a_code;
                        $days[$n] = $error_id;  // fix up
                    }
                } else { 
                    $days[$n] = $code_ids->first()->id;  // replace four character code with corresponding shift code id
                }
            }
            if (strlen($bad_codes) > 0){
                if (strlen($comment)>0){ $comment = $comment . ' / ';}
                $comment = $comment . 'Unknown shift code(s): ' . $bad_codes;
            }


            ScheduleLine::updateOrCreate(
                // this array is unique, controlling update or insert - may be redundant to "uniqueBy" function?
                [                                 
                    'schedule_id' => $schedule_id,
                    'line_group_id' => $line_group_id,
                    'line'     => $row['line']
                ],
                [
                    // special handling for "natural sort"
                    'line_natural' => ScheduleLine::natural($row['line']),
                    'blackout'     => $blackout,
                    'nexus'     => $nexus,
                    'barge'     => $barge,
                    'offsite'   => $offsite,
                    'comment'   => $comment
                ]);           
                // get schedule_line_id
                $schedule_line_id = ScheduleLine::where('schedule_id',$schedule_id)
                                                ->where('line_group_id',$line_group_id)
                                                ->where('line', $row['line'])->first()->id;

                // import the day codes - first delete, then replace all...
                LineDay::where('schedule_line_id',$schedule_line_id)->delete();

                for ($n = 0; $n <= ($max_days -1); $n++) {
                    $new_day = new LineDay;
                    $new_day->schedule_line_id = $schedule_line_id;
                    $new_day->day_number = ($n +1);
                    $new_day->shift_code_id = $days[$n];
                    $new_day->save();
                }
        }   // end foreach rows

        // adjust cycle_days of schedules to maximum number of days tied to corresponding schedule lines.
        $schedules = Schedule::all();
        foreach ($schedules as $schedule){
            // need to skip schedules without schedule lines !!
            if (ScheduleLine::where('schedule_id',$schedule->id)->count() > 0){
                $schedule->cycle_days = ScheduleLine::where('schedule_lines.schedule_id',$schedule->id)
                            ->join('line_days','schedule_lines.id','=','line_days.schedule_line_id')
                            ->max('day_number');
                $schedule->save();    
            
                // find any schedule lines that are short of full cycle days, then append necessary days with 'missing' tag
                $schedule_lines = ScheduleLine::where('schedule_id',$schedule->id)->get();
                foreach ($schedule_lines as $schedule_line){
                    $line_day_count = LineDay::where('schedule_line_id',$schedule_line->id)->count();
                    if ( $line_day_count < $schedule->cycle_days ){
                        for ($n = ($line_day_count +1); $n <= $schedule->cycle_days; $n++) {
                            $new_day = new LineDay;
                            $new_day->schedule_line_id = $schedule_line->id;
                            $new_day->day_number = $n;
                            $new_day->shift_code_id = $error_id;
                            $new_day->save();
                        }
                    }
                }
            }
        }
    }

    /**
     * @return string|array
     */
    public function uniqueBy()
    {
            return 'magic';  // name of composite index: unique(['schedule_id', 'line_group_id', 'line'], 'magic');
    }
}
