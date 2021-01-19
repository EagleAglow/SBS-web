<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BidderGroup extends Model
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
     * BidderGroup / User relationship: One to Many
     */
    public function users() {
        return $this->hasMany(User::class);
    }    


}