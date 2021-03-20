<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pick extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'rank', 'user_id', 'schedule_line_id'
    ];

    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedule_line()
    {
        return $this->belongsTo(ScheduleLine::class);
    }

}