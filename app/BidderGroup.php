<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Traits\HasRoles;

class BidderGroup extends Model
{
    use HasRoles;  // added to be able to control which lines a user can bid

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'code', 'order',
    ];

    // see: https://stackoverflow.com/questions/50984164/the-given-role-or-permission-should-use-guard-instead-of-web-laravel
    protected $guard_name = 'web';

    /**
     * BidderGroup / User relationship: One to Many
     */
    public function users() {
        return $this->hasMany(User::class);
    }    


}