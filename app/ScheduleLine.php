<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\LineDay;

class ScheduleLine extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'line', 'line_natural', 'schedule_id','line_group_id', 'comment', 'user_id', 'bid_at',
        'blackout', 'nexus', 'barge', 'offsite'
    ];

    //  Accessor does not handle passed parameters
    //  returns shiftcode for particular day...
    //  dreadful design, maybe fix later - hah!
    //
    public function getCodeOfDay($L, $which)     // return id for code for line id, day number
    {
//        return LineDay::where('schedule_line_id', $L)->where('day_number', $which)->first()->shift_code_id;  // original
        if (LineDay::where('schedule_line_id', $L)->where('day_number', $which)->count() > 0){    // debugging - REMOVE ME LATER
            return LineDay::where('schedule_line_id', $L)->where('day_number', $which)->first()->shift_code_id;
        } else {
            return 1;
        }
    }

    public static function natural($original){
        // used to fill field 'line_natural' to produce a "natural" sort like 1, 2, 2a, 2b, 20
        // break original 'line' string into front and back portion where it changes from numeric to alpha
        // each part is leading filled with "-", then they are concatenated
        $numeric_string = '';
        $alpha_string = '';
        $number = true; // changes when transition is detected
        $myArray = str_split($original);
        foreach($myArray as $character){
            $chr_type = 'X';  // neither alpha nor numeric - we will be skipping dashes and dots
            if ((ord($character)>47) And (ord($character)<58)){ $chr_type = 'N'; } // numeric
            if ((ord(strtoupper($character))>64) And (ord(strtoupper($character))<91)){ $chr_type = 'A'; } // alpha
            if ($chr_type == 'N'){
                if ($number){
                    $numeric_string = $numeric_string . $character;
                } 
            }
            if ($chr_type == 'A'){
                if ($number){
                    $number = false; // ignore any further digits
                    $alpha_string = $alpha_string . $character;
                }
            }
        }
        return substr('----' . $numeric_string, -4) . substr('----' . $alpha_string, -4);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function line_group()
    {
        return $this->belongsTo(LineGroup::class);
    }

    public function shift_code()
    {
        return $this->belongsTo(ShiftCode::class);
    }

    public function users() 
    {
        return $this->belongsToMany(User::class,'picks');
    }

}