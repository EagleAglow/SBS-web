<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\BidderGroup;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewUserMail;

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
        'bid_order', 'seniority_date', 'bidder_tie_breaker',
        'phone_number', 'has_snapshot',
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


  public static function generatePassword()
    {
      // Generate random 25 character string - it is hashed elsewhere 
      // from: https://thisinterestsme.com/php-random-password/
      $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!-.[]?*()';
      //Create a blank string.
      $password = '';
      //Get the index of the last character in our $characters string.
      $characterListLength = mb_strlen($characters, '8bit') - 1;
      //Loop from 1 to the $length that was specified.
      foreach(range(1, 25) as $i){
          $password .= $characters[random_int(0, $characterListLength)];
      }
      return $password;
    }

    public static function sendWelcomeEmail($user)
    {
      // Generate a new reset password token
      $token = app('auth.password.broker')->createToken($user);
      
      // Send email
      Mail::to($user->email)->send(new NewUserMail($user->name, $user->email, $token));
    }
}