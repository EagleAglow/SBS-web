<?php
 
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Schedule;
use App\ScheduleLine;
use App\LineGroup;
use App\ShiftCode;

class BidSelectionMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name,$schedule_line_id)
    {
        $this->name = $name;
        $this->schedule_line_id = $schedule_line_id;
        $this->ics = '';  // use this later to attach ics file
        $this->from_name = config('mail.from.name');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->ics = '';  // placeholder for future ics file

        $name = $this->name;  // recipient
        $schedule_line_id = $this->schedule_line_id;
        $schedule_line = ScheduleLine::where('id','=',$schedule_line_id)->get()->first();
        $schedule = Schedule::where('id','=',$schedule_line->schedule_id)->get()->first();

        $start_date = $schedule->start;
        $cycles = $schedule->cycle_count;
        $title = $schedule->title;
        $line_group_name = LineGroup::where('id','=',$schedule_line->line_group_id)->get()->first()->name;
        $line_number = $schedule_line->line;
        // comment
        $comment = $schedule_line->comment; 
        if ($schedule_line->nexus == 1){
            $comment = $comment . ', NEXUS';
        }
        if ($schedule_line->barge == 1){
            $comment = $comment . ', BARGE';
        }
        if ($schedule_line->offsite == 1){
            $comment = $comment . ', OFFSITE';
        }
        // get data for the email table
        $table_rows = array();

        $stamp = strtotime( $start_date );
        $row_number = 0;
/* 
        // not used - see next block
        // one column for date, row count = 56 times number of cycles
        for ($c = 1; $c <= $cycles; $c++){  //cycles
            for ($n = 1; $n <= 56; $n++) {  // 1 to 56 days
                $day = date("l, j F Y", $stamp);   // result like: Saturday, 10 March 2021
                $d = 'day_' . substr(('00' . $n),-2);   // field name
                $shift = ShiftCode::where('id', $schedule_line->$d)->get()->first();
                $shift_code = $shift->name;                              // e.g., 06BX
                if ($shift_code == '----'){
                    $shift_on = '----';
                    $shift_off = '----';
                } else {
                    $shift_on = date('H:i',strtotime($shift->begin_time));
                    $shift_off = date('H:i',strtotime($shift->end_time));
                }
                $row_number = $row_number +1;

                $table_row = array([
                    'row_number'=>$row_number,   // maybe delete later, not used now
                    'day_number'=>$n,            // maybe delete later, not used now
                    'day_text'=>$day,
                    'code'=>$shift_code,
                    'on'=>$shift_on,
                    'off'=>$shift_off
                ]);

                $table_rows[] = $table_row;

                $stamp = strtotime( date( 'Y/m/d', $stamp ) . "+1 days"); // next date
            }
        }
 */

        // one column for date(s), 56 rows
        
        for ($n = 1; $n <= 56; $n++) {  // 1 to 56 days

            if ($cycles > 1){
                $day = date("l \/ j F", $stamp);   // result like: Saturday / 10 March
                $stamp3 = $stamp;
                for ($c = 2; $c <= ($cycles); $c++){  //cycles
                    $stamp3 = strtotime( date( 'Y/m/d', $stamp3 ) . "+56 days"); // next cycle date
                    $day = $day . ' / ' . date("j F", $stamp3);   // result like: Saturday / 10 March
                }
            } else {
                $day = date("l, j F Y", $stamp);   // result like: Saturday, 10 March 2021
            }

            $d = 'day_' . substr(('00' . $n),-2);   // field name
            $shift = ShiftCode::where('id', $schedule_line->$d)->get()->first();
            $shift_code = $shift->name;                              // e.g., 06BX
            if ($shift_code == '----'){
                $shift_on = '----';
                $shift_off = '----';
            } else {
                $shift_on = date('H:i',strtotime($shift->begin_time));
                $shift_off = date('H:i',strtotime($shift->end_time));
            }
            $row_number = $row_number +1;

            $table_row = array([
                'row_number'=>$row_number,   // maybe delete later, not used now
                'day_number'=>$n,            // maybe delete later, not used now
                'day_text'=>$day,
                'code'=>$shift_code,
                'on'=>$shift_on,
                'off'=>$shift_off
            ]);

            $table_rows[] = $table_row;

            $stamp = strtotime( date( 'Y/m/d', $stamp ) . "+1 days"); // next date
        }


        // begin ics file
        $linefeed = chr(13) . chr(10);
        $ics = 'BEGIN:VCALENDAR' . $linefeed;
        $ics = $ics . 'PRODID:-//SBS//Shift Bid System//EN' . $linefeed;
        $ics = $ics . 'VERSION:2.0' . $linefeed;  

        $stamp = strtotime( $start_date );
        $row_number = 0;  // included in UID (unique identifier)
        for ($c = 1; $c <= $cycles; $c++){  //cycles
            for ($n = 1; $n <= 56; $n++) {  // 1 to 56 days
                $day = date("l, j F Y", $stamp);   // result like: Saturday, 10 March 2021
                $d = 'day_' . substr(('00' . $n),-2);   // field name
                $shift = ShiftCode::where('id', $schedule_line->$d)->get()->first();
                $shift_code = $shift->name;                              // e.g., 06BX
                if ($shift_code == '----'){
                    // skipping days off
                } else {
                    // begin event section
                    $ics = $ics . 'BEGIN:VEVENT' . $linefeed;
                    $ics = $ics . 'DTSTAMP:' . gmdate("Ymd\THis\Z",time()) . $linefeed;
                    // unique identifier
                    $ics = $ics . 'UID:' .  gmdate("Ymd\THis\Z",time()) . 'ROW' . $row_number . '@' . $_SERVER['SERVER_ADDR'] . $linefeed;

                    // handle shifts that span (or end on) midnight
                    // use YYYY-MM-DD HH:MM:SS format for date/time comparison!
                    $shift_on = date("Y-m-d H:i:s", strtotime( date( 'Y-m-d', $stamp ) . ' ' . date( 'H:i:s', strtotime($shift->begin_time )  )  ) );
                    $shift_off = date("Y-m-d H:i:s", strtotime( date( 'Y-m-d', $stamp ) . ' ' . date( 'H:i:s', strtotime($shift->end_time )  )  ) );
                    // convert to seconds to compare
                    $date_time_on = date_create($shift_on);
                    $date_time_off = date_create($shift_off);
                    $delta = $date_time_off->format('U') - $date_time_on->format('U');
                    if ($delta > 0){
                        $shift_on = date("Ymd\THis", strtotime( date( 'Y-m-d', $stamp ) . ' ' . date( 'H:i:s', strtotime($shift->begin_time )  )  ) );
                        $shift_off = date("Ymd\THis", strtotime( date( 'Y-m-d', $stamp ) . ' ' . date( 'H:i:s', strtotime($shift->end_time )  )  ) );
                    } else {
                        $shift_on = date("Ymd\THis", strtotime( date( 'Y-m-d', $stamp ) . ' ' . date( 'H:i:s', strtotime($shift->begin_time )  )  ) );
                        $stamp2 = strtotime( date( 'Y/m/d', $stamp ) . "+1 days"); // next date
                        $shift_off = date("Ymd\THis", strtotime( date( 'Y-m-d', $stamp2 ) . ' ' . date( 'H:i:s', strtotime($shift->end_time )  )  ) );
                    }
                    // debugging 
//                    $ics = $ics . $linefeed . 'Delta hours=' .  $delta . $linefeed . $linefeed;
                    
                    // event start
                    $ics = $ics . 'DTSTART;TZID=America/Detroit:' . $shift_on . $linefeed;
                    // event end
                    $ics = $ics . 'DTEND;TZID=America/Detroit:' . $shift_off . $linefeed;
                    // category and summary
                    $ics = $ics . 'CATEGORIES:@Work' . $linefeed;
                    $ics = $ics . 'SUMMARY:' . $shift_code . $linefeed;
                    // close event
                    $ics = $ics . 'END:VEVENT' . $linefeed;

                    // format should be like this:
                    // BEGIN:VEVENT  
                    // DTSTAMP:19960704T120000Z
                    // UID:20210131T212909@atomicwizard.com
                    // DTSTART;TZID=America/Detroit:20210202T020000
                    // DTEND;TZID=America/Detroit:20210202T120000
                    // CATEGORIES:@Work
                    // SUMMARY:02BX
                    // END:VEVENT

                }
                $row_number = $row_number +1;
                $stamp = strtotime( date( 'Y/m/d', $stamp ) . "+1 days"); // next date
            }
        }
        // wrap up file
        $ics = $ics . 'END:VCALENDAR' . $linefeed;
        $this->ics = $ics;
        $from_name = $this->from_name;

        return $this->subject('Bid Selection Mail')
            ->markdown('mailtemplates.bidselection')
            ->attachData($this->ics, 'schedule.ics', [ 'mime' => 'text/calendar', ])
//            ->attachData($this->ics, 'schedule.ics', [ 'mime' => 'text/plain', ])    // for development...
            ->with([
                'name' => $name, 
                'title' => $title,
                'line_group_name' => $line_group_name,
                'line_number' => $line_number,
                'comment' => $comment,
                'table_rows' => $table_rows,
                'from_name' => $from_name,
            ]);
    }
}