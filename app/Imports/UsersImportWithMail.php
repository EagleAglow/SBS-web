<?php
namespace App\Imports;
    

// need to move models into their own folder  - FIX ME LATER
use App\User;
use App\BidderGroup;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

//use Auth;
//use Spatie\Permission\Models\Role;
     
// ===================================================================
//   Sends welcome mail to new users
// ===================================================================
class UsersImportWithMail implements ToModel, WithHeadingRow, WithUpserts
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    
    // Header: NAME, EMAIL, PHONE, SENIORITY, GROUP
    // expects header row, or will miss an entry...
    // ****** REQUIRES header row in order to index $row array with field names ********
    // switches header text to lower case for index

    // uses "upserts" - inserts new, updates old, based on email

    public function model(array $row)
    {
        // is this a new user?
        $new_user = true;
        $users = User::where('email',$row['email'])->get();
        if (count($users) > 0){ $new_user = false; }
            
        // is this user an existing admin or superuser?
        $admin_flag = false;
        if (!$new_user ){
            $user = $users->first();
            if ($user->hasRole('admin')){ $admin_flag = true; }
            if ($user->hasRole('superuser')){ $admin_flag = true; }
        }

        // validate phone number - only use it if ten digits, otherwise blank
        $phone = $row['phone'];
        if(!preg_match("/\d{10}/",$phone)) {
            $phone = '';
        }

        // setup for upsert - skip if admin
        // new users get random password, don't change password for others
        if (!$admin_flag){
            if (!$new_user){
                // keep old password hash
                return new User([
                    'name'     => $row['name'],
                    'email'    => $row['email'],
                    'phone_number'    => $phone,
                    'password' => $user->password,
                    'bidder_primary_order' => $row['seniority'],
                    'bidder_group_id' => BidderGroup::select('id')->where('code','=', $row['group'])->first()->id,
                ]);
            } else {
                //generate a password for the new users
                $pw = User::generatePassword();
//                return new User([
                $new_user = new User([
                    'name'     => $row['name'],
                    'email'    => $row['email'],
                    'phone_number'    => $phone,
                    'password' => \Hash::make($pw),
                    'bidder_primary_order' => $row['seniority'],
                    'bidder_group_id' => BidderGroup::select('id')->where('code','=', $row['group'])->first()->id,
                ]);

                // send mail
                User::sendWelcomeEmail($new_user);

                // return new user
                return $new_user;
            }
        }
    }

    /**
     * @return string|array
     */
    public function uniqueBy()
    {
        return 'email';
    }

}
