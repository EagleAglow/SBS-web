<?php
namespace App\Imports;
    

// need to move models into their own folder  - FIX ME LATER
use DB;
use DateTime;
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
                $email = $row['email'];
                $bg_code = strtoupper($row['group']);
                $bidder_groups_with_code = BidderGroup::select('id')->where('code','=', $bg_code)->get();
                if (count($bidder_groups_with_code)>0){
                    $bidder_group_id = $bidder_groups_with_code->first()->id;
                } else {
                    $bidder_group_id = null;
                }

                //  check/fix date format: 1999-03-31 works, 3/31/1999 fails - too bad Excel dumps the second one (at least for USA settings)
                $seniority = (new DateTime($row['seniority']))->format('Y-m-d');

                $this_user = new User([
                    'name'     => $row['name'],
                    'email'    => $email,
                    'phone_number'    => $phone,
                    // existing user - keep old password hash
                    'password' => $user->password,
                    'seniority_date' => $seniority,
                    'bidder_group_id' => $bidder_group_id,
                ]);

                // assign bidding roles based on bidding group
                if (isset($bg_code)){
                    // stupid way to do this, but $this_user does not play well with roles
                    $clone_user = User::where('email',$email)->get()->first();
                    // remove any existing bidding role
                    $bidder_roles = DB::table('roles')->where('name','like', 'bid-for-%')->get('name');
                    foreach($bidder_roles as $bidder_role){
                        if ($clone_user->hasRole($bidder_role->name)){
                            $clone_user->removeRole($bidder_role->name);
                        }
                    }

                    // set bidding role based on bidding group
                    $bidder_groups = BidderGroup::all();
                    foreach($bidder_groups as $bidder_group){
                        if($bidder_group->code == $bg_code){
                            $role_names = $bidder_group->getRoleNames();
                            foreach ($role_names as $role_name) {
                                $clone_user->assignRole($role_name); //Assigning role to user
                            }
                        }
                    }
                }

                return $this_user;

            } else {
                //new user - generate a dummy password
                $pw = User::generatePassword();
                $pw_hash = \Hash::make($pw);
                $name = $row['name'];
                $email = $row['email'];
                $bg_code = strtoupper($row['group']);
                $bidder_groups_with_code = BidderGroup::select('id')->where('code','=', $bg_code)->get();
                if (count($bidder_groups_with_code)>0){
                    $bidder_group_id = $bidder_groups_with_code->first()->id;
                } else {
                    $bidder_group_id = null;
                }

                //  check/fix date format: 1999-03-31 works, 3/31/1999 fails - too bad Excel dumps the second one (at least for USA settings)
                $seniority = (new DateTime($row['seniority']))->format('Y-m-d');

                $new_user = new User([
                    'name'     => $name,
                    'email'    => $email,
                    'phone_number'    => $phone,
                    'password' => $pw_hash,
                    'seniority_date' => $seniority,
                    'bidder_group_id' => $bidder_group_id,
                ]);

                // at this point, $new_user is not yet a record in the users table, so can't assign roles
                // so, make a new user record....
                // stupid way to do this, but $new_user does not play well with roles
                // future - put this in User model
                $clone_user = User::create(['email'=>$email, 'name'=>$name, 'password'=>$pw_hash, 'bidder_group_id'=>$bidder_group_id, 'phone_number'=>$phone,]); 

                // assign bidding roles based on bidding group, special handling for NONE and TRAFFIC
                if (isset($bg_code)){
                    $clone_user = User::where('email',$email)->get()->first();
                    // remove any existing bidding role
                    $bidder_roles = DB::table('roles')->where('name','like', 'bid-for-%')->get('name');
                    foreach($bidder_roles as $bidder_role){
                        if ($clone_user->hasRole($bidder_role->name)){
                            $clone_user->removeRole($bidder_role->name);
                        }
                    }

                    // set bidding role based on bidding group
                    $bidder_groups = BidderGroup::all();
                    foreach($bidder_groups as $bidder_group){
                        if($bidder_group->code == $bg_code){
                            $role_names = $bidder_group->getRoleNames();
                            foreach ($role_names as $role_name) {
                                $clone_user->assignRole($role_name); //Assigning role to user
                            }
                        }
                    }
                }

/*    don't....             
                // send mail
                User::sendWelcomeEmail($new_user);

                // send SMS, if they have a number
                if (isset($new_user->phone_number)){
                    if (strlen($new_user->phone_number)>0){
                        // Generate a new reset password token
                        $token = app('auth.password.broker')->createToken($new_user);
                        $url= url(config('url').route('password.reset', ['email' => $new_user->email, $token ]));
                        $msg =  'Hello '. $new_user->name . '- You have just been added to this system, and in order to use it, ';
                        $msg = $msg . 'you need to set your password at this link: ';
                        $msg = $msg . $url;
                        LaraTwilio::notify($new_user->phone_number, $msg);
                        flash('SMS sent.')->success();
                    }
                }
    
 */
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
