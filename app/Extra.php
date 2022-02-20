<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Extra extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'name', 'email', 'text_number', 'voice_number', 'call_order',  ];
}