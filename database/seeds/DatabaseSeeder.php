<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UserSeeder::class);
        $this->call(BidderGroupsTableSeeder::class);
        $this->call(LineGroupsTableSeeder::class);
        $this->call(ShiftCodesTableSeeder::class);
        $this->call(SchedulesTableSeeder::class);
        $this->call(ScheduleLinesTableSeeder::class);
        // run PermissionsSeeder BEFORE UsersTableSeeder
        $this->call(PermissionsSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(PicksTableSeeder::class);
        $this->call(ParamsTableSeeder::class);
        $this->call(LogItemsTableSeeder::class);
    }
}
