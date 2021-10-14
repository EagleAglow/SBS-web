<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use App\Schedule;
use App\ScheduleLine;
use App\LineGroup;
use App\ShiftCode;
use App\Pick;

class BidderDashController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // verify logged in
        $this->middleware('auth');
        // to enable email verification in this controller
        //  $this->middleware(['auth','verified']);
    }

    /**
     * Show the bidder dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
//        if (Auth::user()->hasAnyRole('bid-for-demo','bid-for-irpa','bid-for-tsu','bid-for-oidp','bid-for-tcom','bid-for-tnon')){
        if (Auth::user()->hasPermissionTo('bid-self')){
            return view('bidders.dash');
        } else {
            abort('401');
        }
    }


//    generate and download ics file for this schedule_line id 
    public function ics($id) 
    {
        // see if this line exists and user bid on the line
        $schedule_line = ScheduleLine::where('id',$id)->where('user_id',Auth::user()->id)->get();
        if (count($schedule_line) >0){
            $schedule_line =$schedule_line->first();

            // get values from schedule
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

            // build ics file
            $linefeed = chr(13) . chr(10);
            $ics = 'BEGIN:VCALENDAR' . $linefeed;
            $ics = $ics . 'PRODID:-//SBS//Shift Bid System//EN' . $linefeed; 
            $ics = $ics . 'VERSION:2.0' . $linefeed;  

            $stamp = strtotime( $start_date );
            $row_number = 0;  // included in UID (unique identifier)
            for ($c = 1; $c <= $cycles; $c++){  //cycles
                for ($n = 1; $n <= $schedule->cycle_days; $n++) {  
                    $day = date("l, j F Y", $stamp);   // result like: Saturday, 10 March 2021

                    $shift = ShiftCode::find($schedule_line->getCodeOfDay($schedule_line->id,$n));
                    $shift_code = $shift->name;                              // e.g., 06BX

                    if (($shift_code == '----') or ($shift_code == '????')){
                        // skipping days off or missing data
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
            $fileName = "schedule.ics";
            // set headers for the download
            $headers = [
                'Content-type' => 'text/plain', 
                'Content-Disposition' => sprintf('attachment; filename="%s"', $fileName),
                'Content-Length' => strlen($ics),
            ];

            // make a response, with the content, a 200 response code and the headers
            return Response::make($ics, 200, $headers);

        } else {
            abort('401');
        }
    }
}
