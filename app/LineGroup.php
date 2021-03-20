<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LineGroup extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'code', 'order',
    ];

    /**
     * LineGroup / ScheduleLine relationship: One to Many
     * Tie schedule lines to this schedule.
     */
    public function schedule_lines() {
        return $this->hasMany(ScheduleLine::class);
    }    


}