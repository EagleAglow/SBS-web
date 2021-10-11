<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShiftCode extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'begin_time', 'end_time',
    ];

    /**
     * return hours:minutes
     */
    public function getBeginShortAttribute()
    {
        if ($this->name=='----'){   // day off
            return '----';
        } else {
            if ($this->name=='<<>>'){    // missing data
                return '<<>>';
            }
            return date('H:i',strtotime($this->begin_time));
        }
    }

    public function getEndShortAttribute()
    {
        if ($this->name=='----'){   // day off
            return '----';
        } else {
            if ($this->name=='<<>>'){    // missing data
                return '<<>>';
            }
            return date('H:i',strtotime($this->end_time));
        }
    }

    // Accessor for shift_divs
    //  returns html...
    //
    // <div class="shift-code">{{ App\ShiftCode::find($scheduleline->day_01)->shift_code  }}</div>
    // <div class="shift-begin">{!! App\ShiftCode::find($scheduleline->day_01)->shift_begin_short  !!}</div>
    // <div class="shift-end">{!! App\ShiftCode::find($scheduleline->day_01)->shift_end_short !!}</div>
    //
    public function getShiftDivsAttribute()
    {
        if (($this->shift_code) == '----'){
            $divs = '&nbsp;';
        } else {
            if (($this->shift_code) == '<<>>') {
                $divs = '&nbsp;';
            } else {
                $divs = '<div class="shift-code">' . $this->name . '</div>';
                $divs = $divs . '<div class="shift-begin">' . $this->begin_short . '</div>';
                $divs = $divs . '<div class="shift-end">' . $this->end_short . '</div>';
            }
        }
        return $divs;
    }

    // Accessor for shift_name 
    //
    public function getShiftName()
    {
        return $this->name;
    }


    // Accessor for code_with_times - not used?
    //  returns code with times in parentheses, like...
    //  06SD (06:03 - 17:45)
    //
    public function getCodeWithTimesAttribute()
    {
        if (($this->shift_code) == '----'){
            $cwt = '---- (Off)';
        } else {
            if (($this->shift_code) == '<<>>') {
                $cwt = '(Missing Data)';
            } else {
                $cwt = $this->name . '(' . $this->begin_short . ' - ' . $this->end_short . ')';
            }
        }
        return $cwt;
    }
}
