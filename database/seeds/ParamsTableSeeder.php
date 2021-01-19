<?php

use Illuminate\Database\Seeder;

class ParamsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        {
            // Truncate the database so we don't repeat the seed
            DB::table('params')->delete();

            // bidding-state: none, ready (to begin, next bidder is no. 1), running, paused, complete (after last bidder), reported
            // note - admin should not move from complete to reset unless results have been reported! 
            DB::table('params')->insertOrIgnore([ 'param_name' => 'bidding-state', 'string_value' => 'none', ]);

            // bidding-next: bid_order value of users table; either 1 or actual next bidder (running/paused).  Wraps to 1 after last bidder
            DB::table('params')->insertOrIgnore([ 'param_name' => 'bidding-next', 'integer_value' => 1, ]);
        }
    }
}
