<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\BidderGroup;

//class User extends Authenticatable
// changed for verify email address
class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'bidder_group_id','has_bid',
        'bid_order', 'bidder_primary_order', 'bidder_secondary_order',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function bidder_group()
    {
        return $this->belongsTo(BidderGroup::class);
    }    

    public function schedule_lines()
    {
        return $this->belongsToMany(ScheduleLine::class,'picks');
    }

}

