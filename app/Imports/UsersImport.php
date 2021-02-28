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
                // existing user
                $bg_code = $row['group'];
                $bidder_group_id = BidderGroup::select('id')->where('code','=', $bg_code)->first()->id;

                $this_user = new User([
                    'name'     => $row['name'],
                    'email'    => $row['email'],
                    'phone_number'    => $phone,
                    // existing user - keep old password hash
                    'password' => $user->password,
                    'bidder_primary_order' => $row['seniority'],
                    'bidder_group_id' => $bidder_group_id,
                ]);

                // assign bidding roles based on bidding group, special handling for NONE and TRAFFIC
                if (isset($bg_code)){
                    $bidder_groups = BidderGroup::all();
                    foreach($bidder_groups as $bidder_group){
                        // remove any existing bidding role
                        if(!$bidder_group->code == 'NONE'){
                            $this_user->removeRole('bidder-' . strtolower($bidder_group->code));
                        }
                        if($bidder_group->code == $bg_code){
                            if($bidder_group->code == 'TRAFFIC'){
                                // assign both TNON and TCOM
                                $this_user->assignRole('bidder-tcom');
                                $this_user->assignRole('bidder-tnon');
                            } else {
                                if($bidder_group->code == 'NONE'){
                                    // do nothing
                                } else {
                                    $this_user->assignRole('bidder-' . strtolower($bidder_group->code));
                                }
                            }
                        }
                    }
                }
                return $this_user;

            } else {
                //new user - generate a dummy password
                $pw = User::generatePassword();
                $bg_code = $row['group'];
                $bidder_group_id = BidderGroup::select('id')->where('code','=', $bg_code)->first()->id;

                $new_user = new User([
                    'name'     => $row['name'],
                    'email'    => $row['email'],
                    'phone_number'    => $phone,
                    'password' => \Hash::make($pw),
                    'bidder_primary_order' => $row['seniority'],
                    'bidder_group_id' => $bidder_group_id,
                ]);

                // assign bidding roles based on bidding group, special handling for NONE and TRAFFIC
                if (isset($bg_code)){
                    $bidder_groups = BidderGroup::all();
                    foreach($bidder_groups as $bidder_group){
                        if($bidder_group->code == $bg_code){
                            if($bidder_group->code == 'TRAFFIC'){
                                // assign both TNON and TCOM
                                $new_user->assignRole('bidder-tcom');
                                $new_user->assignRole('bidder-tnon');
                            } else {
                                if($bidder_group->code == 'NONE'){
                                    // do nothing
                                } else {
                                    $new_user->assignRole('bidder-' . strtolower($bidder_group->code));
                                }
                            }
                        }
                    }
                }

                // don'tsend mail
//                User::sendWelcomeEmail($new_user);

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
