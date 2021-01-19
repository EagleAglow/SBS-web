<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\ScheduleLine;
use App\Schedule;
use App\LineGroup;

class UniqueLineGroupSchedule implements Rule
{
    public $line;
    public $line_group_id;
    public $schedule_id;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($xline,$xgroup,$xschedule,$xaction)
    {
        $this->line = $xline;
        $this->line_group_id = $xgroup;
        $this->schedule_id = $xschedule;
        $this->action = $xaction;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    // $value and $attribute not used
    // return true if the combination of line, group_id & schedule_id is unique
    public function passes($attribute, $value)
    {
        $count = ScheduleLine::select('id')->where('line_group_id', $this->line_group_id)->where('schedule_id', $this->schedule_id)->where('line', $this->line)->get();
        if ($this->action == 'update'){
            return count($count) < 2;
        } else {
            return count($count) == 0;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The combination of: Line=' . $this->line . ', Group=' . LineGroup::find($this->line_group_id)->code .
                ', Schedule=' . Schedule::find($this->schedule_id)->title . ' is already in use. The combination must be unique.';
    }
}
