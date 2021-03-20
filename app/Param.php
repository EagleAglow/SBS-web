<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Param extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'param_name', 'date_value', 'integer_value', 'boolean_value', 'string_value',
    ];


    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'date_value' => 'datetime',
    ];

}
