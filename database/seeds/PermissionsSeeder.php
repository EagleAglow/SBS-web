<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\User;
use App\BidderGroup;


class PermissionsSeeder extends Seeder
{
    /**
     * Create the initial roles and permissions.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        //user can place their own bid for schedule line (self)
        Permission::create(['name' => 'bid-self']);
        //user can place a schedule line bid for someone else (agent)
        Permission::create(['name' => 'bid-agent']);

        // create permissions for bidding 
        Permission::create(['name' => 'bid-demo']);   //user can place bid for demonstration schedule line
        Permission::create(['name' => 'bid-oidp']);   //user can place bid for OIDP schedule line
        Permission::create(['name' => 'bid-tsu']);    //user can place bid for TSU schedule line
        Permission::create(['name' => 'bid-irpa']);   //user can place bid for IRPA schedule line
        Permission::create(['name' => 'bid-tcom']);   //user can place bid for COMMERCIAL Traffic schedule line
        Permission::create(['name' => 'bid-tnon']);   //user can place bid for NON-COMMERCIAL Traffic schedule line

        //active bidder - only one permitted at a time (future, maybe one permitted per bid group?)
        Permission::create(['name' => 'bid-now']);

        // create/copy/edit schedule, schedule lines, shift codes
        Permission::create(['name' => 'schedule-edit']);
        // publish/activate/delete schedule
        Permission::create(['name' => 'schedule-manage']);
        // add/edit/delete users
        Permission::create(['name' => 'user-manage']);
        // add/edit/delete users
        Permission::create(['name' => 'role-permission-manage']);

        // create roles and assign initial permissions - used below for bidder groups
        $role1 = Role::create(['name' => 'bid-for-demo']);   // can bid for demonstration lines
        $role1->givePermissionTo('bid-self');
        $role1->givePermissionTo('bid-demo');
        $bg = BidderGroup::where('code','DEMO')->first();
        $bg->assignRole($role1);

        $role1 = Role::create(['name' => 'bid-for-tsu']);   // can bid for TSU lines
        $role1->givePermissionTo('bid-self');
        $role1->givePermissionTo('bid-tsu');
        $bg = BidderGroup::where('code','TSU')->first();
        $bg->assignRole($role1);

        $role1 = Role::create(['name' => 'bid-for-irpa']);   // can bid for IRPA lines
        $role1->givePermissionTo('bid-self');
        $role1->givePermissionTo('bid-irpa');
        $bg = BidderGroup::where('code','IRPA')->first();
        $bg->assignRole($role1);

        $role1 = Role::create(['name' => 'bid-for-tnon']);   // can bid for non-commercial traffic lines
        $role1->givePermissionTo('bid-self');
        $role1->givePermissionTo('bid-tnon');
        $bg = BidderGroup::where('code','TNON')->first();
        $bg->assignRole($role1);
        $bg = BidderGroup::where('code','TRAFFIC')->first();  // Traffic can bid this also
        $bg->assignRole($role1);

        $role1 = Role::create(['name' => 'bid-for-tcom']);   // can bid for commercial traffic lines
        $role1->givePermissionTo('bid-self');
        $role1->givePermissionTo('bid-tcom');
        $bg = BidderGroup::where('code','TCOM')->first();
        $bg->assignRole($role1);
        $bg = BidderGroup::where('code','TRAFFIC')->first();  // Traffic can bid this also
        $bg->assignRole($role1);

        $role1 = Role::create(['name' => 'bid-for-oidp']);   // can bid for OIDP lines
        $role1->givePermissionTo('bid-self');
        $role1->givePermissionTo('bid-oidp');
//        $bg = BidderGroup::where('code','OIDP')->first();
//        $bg->assignRole($role1);
        

        $role1 = Role::where('name','bid-for-tcom')->first();   // can bid for commercial traffic lines
        $bg = BidderGroup::where('code','OIDP')->first();
        $bg->assignRole($role1);

        $role1 = Role::create(['name' => 'bidder-active']);   // active bidder
        $role1->givePermissionTo('bid-now');

        $role2 = Role::create(['name' => 'supervisor']);
        // supervisor role is ONLY for bidding someone else
        // a real person who is a supervisor can have an additional role for bidding themselves 
        $role2->givePermissionTo('bid-agent');

        $role3 = Role::create(['name' => 'admin']);
        $role3->givePermissionTo('schedule-edit');
        $role3->givePermissionTo('schedule-manage');
        $role3->givePermissionTo('user-manage');

        // manage anything
        // gets all permissions via Gate::before rule; see AuthServiceProvider
        // don't use - causes odd problems???
        $role4 = Role::create(['name' => 'superuser']);
        $role4->givePermissionTo('user-manage');
        $role3->givePermissionTo('schedule-edit');
        $role3->givePermissionTo('schedule-manage');
        $role4->givePermissionTo('role-permission-manage');

    }
}
