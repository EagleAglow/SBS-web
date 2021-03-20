<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogItem extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'note', ];
}
