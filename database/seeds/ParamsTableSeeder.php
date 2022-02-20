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

            // control display of bidder name on bidding page
            DB::table('params')->insertOrIgnore([ 'param_name' => 'name-or-taken', 'string_value' => 'taken', ]);

            // control email to next bidder
            DB::table('params')->insertOrIgnore([ 'param_name' => 'next-bidder-email-on-or-off', 'string_value' => 'off', ]);

            // control email to bidder after bid accepted
            DB::table('params')->insertOrIgnore([ 'param_name' => 'bid-accepted-email-on-or-off', 'string_value' => 'off', ]);

            // use test address instead of actually sending email to bidders
            DB::table('params')->insertOrIgnore([ 'param_name' => 'all-email-to-test-address-on-or-off', 'string_value' => 'off', ]);
 
            // address for test emails
            DB::table('params')->insertOrIgnore([ 'param_name' => 'email-test-address', 'string_value' => '', ]);

            // control text to next bidder
            DB::table('params')->insertOrIgnore([ 'param_name' => 'next-bidder-text-on-or-off', 'string_value' => 'off', ]);

            // use test phone instead of actually sending text to bidders
            DB::table('params')->insertOrIgnore([ 'param_name' => 'all-text-to-test-phone-on-or-off', 'string_value' => 'off', ]);
 
            // phone for test texts
            DB::table('params')->insertOrIgnore([ 'param_name' => 'text-test-phone', 'string_value' => '', ]);

            // control "auto bidding" (takes highest ranking user pick for bid)
            DB::table('params')->insertOrIgnore([ 'param_name' => 'autobid-on-or-off', 'string_value' => 'off', ]);

            // overtime calling: OT-call-state: none (initial or after overtime table erased), ready (to begin, next to call is no. 1),
            //                                  running, paused, complete (after last one called)
            DB::table('params')->insertOrIgnore([ 'param_name' => 'OT-call-state', 'string_value' => 'none', ]);

            // next to call for OT: order value of overtime table; either 1 or actual next counter number (running/paused). Wraps to 1 after last call
            DB::table('params')->insertOrIgnore([ 'param_name' => 'OT-call-next', 'integer_value' => 1, ]);

            // message for OT: message to be sent by text or email
            DB::table('params')->insertOrIgnore([ 'param_name' => 'OT-message', 'string_value' => 'Undefined message!', ]);

        }
    }
}
