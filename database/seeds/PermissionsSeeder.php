<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\User;


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

        //user can place bid for demonstration schedule line
        Permission::create(['name' => 'bid-demo']);
        //user can place bid for OIDP schedule line
        Permission::create(['name' => 'bid-oidp']);
        //user can place bid for TSU schedule line
        Permission::create(['name' => 'bid-tsu']);
        //user can place bid for IRPA schedule line
        Permission::create(['name' => 'bid-irpa']);
        //user can place bid for COMMERCIAL Traffic schedule line
        Permission::create(['name' => 'bid-tcom']);
        //user can place bid for NON-COMMERCIAL Traffic schedule line
        Permission::create(['name' => 'bid-tnon']);

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

        // create roles and assign existing permissions
        $role1 = Role::create(['name' => 'bidder-demo']);   // can bid demonstration lines
        $role1->givePermissionTo('bid-self');
        $role1->givePermissionTo('bid-demo');
        $role1 = Role::create(['name' => 'bidder-tsu']);   // can bid TSU lines
        $role1->givePermissionTo('bid-self');
        $role1->givePermissionTo('bid-tsu');
        $role1 = Role::create(['name' => 'bidder-irpa']);   // can bid IRPA lines
        $role1->givePermissionTo('bid-self');
        $role1->givePermissionTo('bid-irpa');
        $role1 = Role::create(['name' => 'bidder-oidp']);   // can bid OIDP lines
        $role1->givePermissionTo('bid-self');
        $role1->givePermissionTo('bid-oidp');
        $role1 = Role::create(['name' => 'bidder-tnon']);   // can bid non-commercial traffic lines
        $role1->givePermissionTo('bid-self');
        $role1->givePermissionTo('bid-tnon');
        $role1 = Role::create(['name' => 'bidder-tcom']);   // can bid commercial traffic lines
        $role1->givePermissionTo('bid-self');
        $role1->givePermissionTo('bid-tcom');

        $role1 = Role::create(['name' => 'bidder-active']);   // active bidder
        $role1->givePermissionTo('bid-now');

        $role2 = Role::create(['name' => 'supervisor']);
        // supervisor role is ONLY for bidding someone else
        // a real person who is a supervisor can have an additiona role for bidding themselves 
        // $role2->givePermissionTo('bid-self');
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

        // create demo users - not used, see UserTableSeeder
        // $user = Factory(App\User::class)->create([
        //     'name' => 'Example User',
        //     'email' => 'test@example.com',
        // ]);
        // $user->assignRole($role1);

        // $user = Factory(App\User::class)->create([
        //     'name' => 'Example Admin User',
        //     'email' => 'admin@example.com',
        // ]);
        // $user->assignRole($role2);

        // $user = Factory(App\User::class)->create([
        //     'name' => 'Example Super-Admin User',
        //     'email' => 'superadmin@example.com',
        // ]);
        // $user->assignRole($role3);


    }
}
