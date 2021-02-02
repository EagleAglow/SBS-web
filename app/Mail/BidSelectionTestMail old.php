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
        $name = $this->name;
        $this->garbage = 'Just some text';
        $schedule_line_id = $this->schedule_line_id;
        $schedule_line = ScheduleLine::where('id','=',$schedule_line_id)->get()->first();
        $title = Schedule::where('id','=',$schedule_line->schedule_id)->get()->first()->title;
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
        // 56 days of...  $day_01_date... $day_01_code...  $day_01_on...   $day_01_off...
        
        for ($n = 1; $n <= 56; $n++) {
            $d = 'day_' . substr(('00' . $n),-2);
            $d_date = $d . '_date';
            $d_code = $d . '_code';
            $d_on = $d . '_on';
            $d_off = $d . '_off';
            $shift = ShiftCode::where('id', $schedule_line->$d)->get()->first();
            $$d_date = 
            $$d_code = $shift->name;
            $$d_on = date('H:i',strtotime($shift->begin_time));
            $$d_off = date('H:i',strtotime($shift->end_time));

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
                'day_01_code' => $day_01_code, 'day_01_on' => $day_01_on,'day_01_off' => $day_01_off,
                'day_02_code' => $day_02_code, 'day_02_on' => $day_02_on,'day_02_off' => $day_02_off,
                'day_03_code' => $day_03_code, 'day_03_on' => $day_03_on,'day_03_off' => $day_03_off,
                'day_04_code' => $day_04_code, 'day_04_on' => $day_04_on,'day_04_off' => $day_04_off,
                'day_05_code' => $day_05_code, 'day_05_on' => $day_05_on,'day_05_off' => $day_05_off,
                'day_06_code' => $day_06_code, 'day_06_on' => $day_06_on,'day_06_off' => $day_06_off,
                'day_07_code' => $day_07_code, 'day_07_on' => $day_07_on,'day_07_off' => $day_07_off,
                'day_08_code' => $day_08_code, 'day_08_on' => $day_08_on,'day_08_off' => $day_08_off,
                'day_09_code' => $day_09_code, 'day_09_on' => $day_09_on,'day_09_off' => $day_09_off,
                'day_10_code' => $day_10_code, 'day_10_on' => $day_10_on,'day_10_off' => $day_10_off,
                'day_11_code' => $day_11_code, 'day_11_on' => $day_11_on,'day_11_off' => $day_11_off,
                'day_12_code' => $day_12_code, 'day_12_on' => $day_12_on,'day_12_off' => $day_12_off,
                'day_13_code' => $day_13_code, 'day_13_on' => $day_13_on,'day_13_off' => $day_13_off,
                'day_14_code' => $day_14_code, 'day_14_on' => $day_14_on,'day_14_off' => $day_14_off,
                'day_15_code' => $day_15_code, 'day_15_on' => $day_15_on,'day_15_off' => $day_15_off,
                'day_16_code' => $day_16_code, 'day_16_on' => $day_16_on,'day_16_off' => $day_16_off,
                'day_17_code' => $day_17_code, 'day_17_on' => $day_17_on,'day_17_off' => $day_17_off,
                'day_18_code' => $day_18_code, 'day_18_on' => $day_18_on,'day_18_off' => $day_18_off,
                'day_19_code' => $day_19_code, 'day_19_on' => $day_19_on,'day_19_off' => $day_19_off,
                'day_20_code' => $day_20_code, 'day_20_on' => $day_20_on,'day_20_off' => $day_20_off,
                'day_21_code' => $day_21_code, 'day_21_on' => $day_21_on,'day_21_off' => $day_21_off,
                'day_22_code' => $day_22_code, 'day_22_on' => $day_22_on,'day_22_off' => $day_22_off,
                'day_23_code' => $day_23_code, 'day_23_on' => $day_23_on,'day_23_off' => $day_23_off,
                'day_24_code' => $day_24_code, 'day_24_on' => $day_24_on,'day_24_off' => $day_24_off,
                'day_25_code' => $day_25_code, 'day_25_on' => $day_25_on,'day_25_off' => $day_25_off,
                'day_26_code' => $day_26_code, 'day_26_on' => $day_26_on,'day_26_off' => $day_26_off,
                'day_27_code' => $day_27_code, 'day_27_on' => $day_27_on,'day_27_off' => $day_27_off,
                'day_28_code' => $day_28_code, 'day_28_on' => $day_28_on,'day_28_off' => $day_28_off,
                'day_29_code' => $day_29_code, 'day_29_on' => $day_29_on,'day_29_off' => $day_29_off,
                'day_30_code' => $day_30_code, 'day_30_on' => $day_30_on,'day_30_off' => $day_30_off,
                'day_31_code' => $day_31_code, 'day_31_on' => $day_31_on,'day_31_off' => $day_31_off,
                'day_32_code' => $day_32_code, 'day_32_on' => $day_32_on,'day_32_off' => $day_32_off,
                'day_33_code' => $day_33_code, 'day_33_on' => $day_33_on,'day_33_off' => $day_33_off,
                'day_34_code' => $day_34_code, 'day_34_on' => $day_34_on,'day_34_off' => $day_34_off,
                'day_35_code' => $day_35_code, 'day_35_on' => $day_35_on,'day_35_off' => $day_35_off,
                'day_36_code' => $day_36_code, 'day_36_on' => $day_36_on,'day_36_off' => $day_36_off,
                'day_37_code' => $day_37_code, 'day_37_on' => $day_37_on,'day_37_off' => $day_37_off,
                'day_38_code' => $day_38_code, 'day_38_on' => $day_38_on,'day_38_off' => $day_38_off,
                'day_39_code' => $day_39_code, 'day_39_on' => $day_39_on,'day_39_off' => $day_39_off,
                'day_40_code' => $day_40_code, 'day_40_on' => $day_40_on,'day_40_off' => $day_40_off,
                'day_41_code' => $day_41_code, 'day_41_on' => $day_41_on,'day_41_off' => $day_41_off,
                'day_42_code' => $day_42_code, 'day_42_on' => $day_42_on,'day_42_off' => $day_42_off,
                'day_43_code' => $day_43_code, 'day_43_on' => $day_43_on,'day_43_off' => $day_43_off,
                'day_44_code' => $day_44_code, 'day_44_on' => $day_44_on,'day_44_off' => $day_44_off,
                'day_45_code' => $day_45_code, 'day_45_on' => $day_45_on,'day_45_off' => $day_45_off,
                'day_46_code' => $day_46_code, 'day_46_on' => $day_46_on,'day_46_off' => $day_46_off,
                'day_47_code' => $day_47_code, 'day_47_on' => $day_47_on,'day_47_off' => $day_47_off,
                'day_48_code' => $day_48_code, 'day_48_on' => $day_48_on,'day_48_off' => $day_48_off,
                'day_49_code' => $day_49_code, 'day_49_on' => $day_49_on,'day_49_off' => $day_49_off,
                'day_50_code' => $day_50_code, 'day_50_on' => $day_50_on,'day_50_off' => $day_50_off,
                'day_51_code' => $day_51_code, 'day_51_on' => $day_51_on,'day_51_off' => $day_51_off,
                'day_52_code' => $day_52_code, 'day_52_on' => $day_52_on,'day_52_off' => $day_52_off,
                'day_53_code' => $day_53_code, 'day_53_on' => $day_53_on,'day_53_off' => $day_53_off,
                'day_54_code' => $day_54_code, 'day_54_on' => $day_54_on,'day_54_off' => $day_54_off,
                'day_55_code' => $day_55_code, 'day_55_on' => $day_55_on,'day_55_off' => $day_55_off,
                'day_56_code' => $day_56_code, 'day_56_on' => $day_56_on,'day_56_off' => $day_56_off,
            ]);
    }
}