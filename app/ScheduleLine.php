<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScheduleLine extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'line', 'line_natural', 'schedule_id','line_group_id', 'comment', 'user_id', 'bid_at',
        'blackout', 'nexus', 'barge', 'offsite', 'day_01',
        'day_02', 'day_03', 'day_04', 'day_05', 'day_06',
        'day_07', 'day_08', 'day_09', 'day_10', 'day_11',
        'day_12', 'day_13', 'day_14', 'day_15', 'day_16',
        'day_17', 'day_18', 'day_19', 'day_20', 'day_21',
        'day_22', 'day_23', 'day_24', 'day_25', 'day_26',
        'day_27', 'day_28', 'day_29', 'day_30', 'day_31',
        'day_32', 'day_33', 'day_34', 'day_35', 'day_36',
        'day_37', 'day_38', 'day_39', 'day_40', 'day_41',
        'day_42', 'day_43', 'day_44', 'day_45', 'day_46',
        'day_47', 'day_48', 'day_49', 'day_50', 'day_51',
        'day_52', 'day_53', 'day_54', 'day_55', 'day_56',
    ];


    //  Accessor does not handle passed parameters
    //  returns shiftcode for particular day...
    //  dreadful design, maybe fix later - hah!
    //
    public function getCode($which)
    {
        switch ($which) {
            case 1:
                return $this->day_01;
            case 2:
                return $this->day_02;
            case 3:
                return $this->day_03;
            case 4:
                return $this->day_04;
            case 5:
                return $this->day_05;
            case 6:
                return $this->day_06;
            case 7:
                return $this->day_07;
            case 8:
                return $this->day_08;
            case 9:
                return $this->day_09;
            case 10:
                return $this->day_10;
            case 11:
                return $this->day_11;
            case 12:
                return $this->day_12;
            case 13:
                return $this->day_13;
            case 14:
                return $this->day_14;
            case 15:
                return $this->day_15;
            case 16:
                return $this->day_16;
            case 17:
                return $this->day_17;
            case 18:
                return $this->day_18;
            case 19:
                return $this->day_19;
            case 20:
                return $this->day_20;
            case 21:
                return $this->day_21;
            case 22:
                return $this->day_22;
            case 23:
                return $this->day_23;
            case 24:
                return $this->day_24;
            case 25:
                return $this->day_25;
            case 26:
                return $this->day_26;
            case 27:
                return $this->day_27;
            case 28:
                return $this->day_28;
            case 29:
                return $this->day_29;
            case 30:
                return $this->day_30;
            case 31:
                return $this->day_31;
            case 32:
                return $this->day_32;                
            case 33:
                return $this->day_33;
            case 34:
                return $this->day_34;
            case 35:
                return $this->day_35;
            case 36:
                return $this->day_36;
            case 37:
                return $this->day_37;
            case 38:
                return $this->day_38;
            case 39:
                return $this->day_39;
            case 40:
                return $this->day_40;
            case 41:
                return $this->day_41;
            case 42:
                return $this->day_42;
            case 43:
                return $this->day_43;
            case 44:
                return $this->day_44;
            case 45:
                return $this->day_45;
            case 46:
                return $this->day_46;
            case 47:
                return $this->day_47;
            case 48:
                return $this->day_48;
            case 49:
                return $this->day_49;
            case 50:
                return $this->day_50;
            case 51:
                return $this->day_51;
            case 52:
                return $this->day_52;
            case 53:
                return $this->day_53;
            case 54:
                return $this->day_54;
            case 55:
                return $this->day_55;
            case 56:
                return $this->day_56;
            default:
                $day_code = '----'; // dummy value, normally indicates day off
                return $day_code;
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