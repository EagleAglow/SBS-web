<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LineDay extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'schedule_line_id', 'shift_code_id', 'day_number',
    ];

    public function schedule_line()
    {
        return $this->belongsTo(ScheduleLine::class);
    }

    public function shift_code()
    {
        return $this->belongsTo(ShiftCode::class);
    }

}