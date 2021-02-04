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
     
class UsersImport implements ToModel, WithHeadingRow, WithUpserts
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    
    // Header: NAME, EMAIL, PHONE, SENIORITY, GROUP, PASSWORD (optional)
    // expects header row, or will miss an entry...
    // ****** REQUIRES header row in order to index $row array with field names ********
    // switches header text to lower case for index

    // uses "upserts" - inserts new, updates old, based on email

    public function model(array $row)
    {
        // is there password text in fifth column, with "PASSWORD" in header row?
        $pwd_flag = false;
        if (count($row) > 5){
            // is text longer than 5 characters?
            if (strlen($row['password']) > 5){
                $pwd_flag = true;
            }
        }
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

        // setup for upsert - skip if admin, change password if >5 characters
        // do not add new user without a password, so skip those
        if (!$admin_flag){
            if ($pwd_flag){
                // not an admin and the password is OK
                return new User([
                    'name'     => $row['name'],
                    'email'    => $row['email'],
                    'phone_number'    => $phone,
                    'password' => \Hash::make($row['password']),
                    'bidder_primary_order' => $row['seniority'],
                    'bidder_group_id' => BidderGroup::select('id')->where('code','=', $row['group'])->first()->id,
                ]);
            } else {
                // skip new user, don't change password for others
                if (!$new_user){
                    return new User([
                        'name'     => $row['name'],
                        'email'    => $row['email'],
                        'phone_number'    => $phone,
                        'password' => $user->password,
                        'bidder_primary_order' => $row['seniority'],
                        'bidder_group_id' => BidderGroup::select('id')->where('code','=', $row['group'])->first()->id,
                    ]);
                }
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
