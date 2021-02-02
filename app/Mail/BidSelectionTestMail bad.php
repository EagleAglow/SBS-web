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

class BidSelectionTestMail extends Mailable
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
        $this->garbage = '';  // use this later
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->garbage = 'Just some text';  // placeholder for future ics file

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
        $day_number = array(); // just numbers
        $day_text = array();
        $code = array();
        $on = array();
        $off = array();
        $stamp = strtotime( $start_date );
        $first = true;
        for ($c = 1; $c <= $cycles; $c++){  //cycles
            for ($n = 1; $n <= 56; $n++) {  // 1 to 56 days
                $day = date("l, j F Y", $stamp);   // result like: Saturday, 10 March 2021
                $d = 'day_' . substr(('00' . $n),-2);   // field name
                $shift = ShiftCode::where('id', $schedule_line->$d)->get()->first();
                $shift_code = $shift->name;                              // e.g., 06BX
                $shift_on = date('H:i',strtotime($shift->begin_time));
                $shift_off = date('H:i',strtotime($shift->end_time));

                array_push($day_number, $n);
                array_push($day_text, $day);
                array_push($code, $shift_code);
                array_push($on, $shift_on);
                array_push($off, $shift_off);

                $stamp = strtotime( date( 'Y/m/d', $stamp ) . "+1 days"); // next date
            }
        }

        return $this->subject('Bid Selection Test Mail')
            ->markdown('mailtemplates.bidselectiontest')
            ->attachData($this->garbage, 'some.txt', [ 'mime' => 'text/plain', ])
            ->with([
                'name' => $name, 
                'title' => $title,
                'line_group_name' => $line_group_name,
                'line_number' => $line_number,
                'comment' => $comment,
                'day_number' => $day_number,
                'day_text' => $day_text = array();
$code = array();
$on = array();
$off = array();


            ]);
    }
}