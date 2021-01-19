<?php

use Illuminate\Database\Seeder;

class BidderGroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Truncate the database so we don't repeat the seed
        DB::table('bidder_groups')->delete();

        //
        DB::table('bidder_groups')->insertOrIgnore([
            'code' => 'DEMO',
            'name' => 'Demonstration Bidder',
            'order' => '1',
        ]);
        DB::table('bidder_groups')->insertOrIgnore([
            'code' => 'TSU',
            'name' => 'Trusted Service Unit Bidder',
            'order' => '2',
        ]);
        DB::table('bidder_groups')->insertOrIgnore([
            'code' => 'IRPA',
            'name' => 'IRPA Bidder',
            'order' => '3',
        ]);
        DB::table('bidder_groups')->insertOrIgnore([
            'code' => 'OIDP',
            'name' => 'OIDP Bidder',
            'order' => '4',
        ]);
        DB::table('bidder_groups')->insertOrIgnore([
            'code' => 'TCOM',
            'name' => 'Traffic Bidder, Commercial ONLY',
            'order' => '5',
        ]);
        DB::table('bidder_groups')->insertOrIgnore([
            'code' => 'TNON',
            'name' => 'Traffic Bidder, Non-commercial ONLY',
            'order' => '6',
        ]);
        DB::table('bidder_groups')->insertOrIgnore([
            'code' => 'TRAFFIC',
            'name' => 'Traffic Bidder, Commercial/Non-commercial',
            'order' => '7',
        ]);
        DB::table('bidder_groups')->insertOrIgnore([
            'code' => 'NONE',
            'name' => 'Not A Bidder',
            'order' => '99',
        ]);
    }
}
