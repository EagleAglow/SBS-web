<?php

use Illuminate\Database\Seeder;

class ShiftCodesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Truncate the database so we don't repeat the seed
        // first turn off foreign key check to avoid error
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('shift_codes')->delete();
        // restore foreign key check to avoid error
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        DB::table('shift_codes')->insertOrIgnore([ 'name' => '----', 'begin_time' => Null, 'end_time' => Null, ]);
        DB::table('shift_codes')->insertOrIgnore([ 'name' => '06BL', 'begin_time' => '06:45', 'end_time' => '17:15', ]); // need time

        DB::table('shift_codes')->insertOrIgnore([ 'name' => '06BM', 'begin_time' => '06:45', 'end_time' => '17:15', ]);
        DB::table('shift_codes')->insertOrIgnore([ 'name' => '06BO', 'begin_time' => '06:45', 'end_time' => '18:00', ]);

        DB::table('shift_codes')->insertOrIgnore([ 'name' => '06CG', 'begin_time' => '06:45', 'end_time' => '19:00', ]);
        DB::table('shift_codes')->insertOrIgnore([ 'name' => '06CJ', 'begin_time' => '06:54', 'end_time' => '16:00', ]);
        DB::table('shift_codes')->insertOrIgnore([ 'name' => '06CK', 'begin_time' => '06:54', 'end_time' => '16:30', ]);
        DB::table('shift_codes')->insertOrIgnore([ 'name' => '07AW', 'begin_time' => '07:00', 'end_time' => '17:30', ]);
        DB::table('shift_codes')->insertOrIgnore([ 'name' => '07AQ', 'begin_time' => '07:00', 'end_time' => '17:30', ]);  // need time

        DB::table('shift_codes')->insertOrIgnore([ 'name' => '07BB', 'begin_time' => '07:00', 'end_time' => '19:00', ]);
        DB::table('shift_codes')->insertOrIgnore([ 'name' => '08BS', 'begin_time' => '08:30', 'end_time' => '19:00', ]);
        DB::table('shift_codes')->insertOrIgnore([ 'name' => '08FN', 'begin_time' => '08:45', 'end_time' => '19:15', ]);
        DB::table('shift_codes')->insertOrIgnore([ 'name' => '08FZ', 'begin_time' => '08:45', 'end_time' => '19:15', ]);  // need time

        DB::table('shift_codes')->insertOrIgnore([ 'name' => '09AN', 'begin_time' => '09:00', 'end_time' => '21:00', ]);
        DB::table('shift_codes')->insertOrIgnore([ 'name' => '10AL', 'begin_time' => '10:00', 'end_time' => '20:30', ]);
        DB::table('shift_codes')->insertOrIgnore([ 'name' => '10BB', 'begin_time' => '10:30', 'end_time' => '21:00', ]);
        DB::table('shift_codes')->insertOrIgnore([ 'name' => '10BG', 'begin_time' => '10:45', 'end_time' => '21:15', ]);
        DB::table('shift_codes')->insertOrIgnore([ 'name' => '11AT', 'begin_time' => '11:24', 'end_time' => '21:00', ]);  // need time

        DB::table('shift_codes')->insertOrIgnore([ 'name' => '11BY', 'begin_time' => '11:24', 'end_time' => '21:00', ]);
        DB::table('shift_codes')->insertOrIgnore([ 'name' => '12BE', 'begin_time' => '12:45', 'end_time' => '00:00', ]);

        DB::table('shift_codes')->insertOrIgnore([ 'name' => '12CN', 'begin_time' => '12:45', 'end_time' => '23:15', ]);
        DB::table('shift_codes')->insertOrIgnore([ 'name' => '12CT', 'begin_time' => '12:54', 'end_time' => '22:00', ]);
        DB::table('shift_codes')->insertOrIgnore([ 'name' => '12CW', 'begin_time' => '12:54', 'end_time' => '22:00', ]);  // need time

        DB::table('shift_codes')->insertOrIgnore([ 'name' => '13AC', 'begin_time' => '13:00', 'end_time' => '01:00', ]);
        DB::table('shift_codes')->insertOrIgnore([ 'name' => '13AS', 'begin_time' => '13:30', 'end_time' => '00:00', ]);
        DB::table('shift_codes')->insertOrIgnore([ 'name' => '13AZ', 'begin_time' => '13:45', 'end_time' => '00:15', ]);
        DB::table('shift_codes')->insertOrIgnore([ 'name' => '13BB', 'begin_time' => '13:45', 'end_time' => '00:15', ]);  // need time

        DB::table('shift_codes')->insertOrIgnore([ 'name' => '14AS', 'begin_time' => '14:24', 'end_time' => '00:00', ]);
        DB::table('shift_codes')->insertOrIgnore([ 'name' => '14AT', 'begin_time' => '14:24', 'end_time' => '00:00', ]);  // need time

        DB::table('shift_codes')->insertOrIgnore([ 'name' => '14AV', 'begin_time' => '14:30', 'end_time' => '01:00', ]);
        DB::table('shift_codes')->insertOrIgnore([ 'name' => '15BV', 'begin_time' => '15:54', 'end_time' => '01:00', ]);
        DB::table('shift_codes')->insertOrIgnore([ 'name' => '15CP', 'begin_time' => '15:45', 'end_time' => '02:15', ]);
        DB::table('shift_codes')->insertOrIgnore([ 'name' => '18AQ', 'begin_time' => '18:45', 'end_time' => '07:00', ]);
        DB::table('shift_codes')->insertOrIgnore([ 'name' => '19AF', 'begin_time' => '19:00', 'end_time' => '07:00', ]);
        DB::table('shift_codes')->insertOrIgnore([ 'name' => '19AR', 'begin_time' => '19:45', 'end_time' => '07:00', ]);
        DB::table('shift_codes')->insertOrIgnore([ 'name' => '20AR', 'begin_time' => '20:30', 'end_time' => '07:00', ]);
        DB::table('shift_codes')->insertOrIgnore([ 'name' => '20AT', 'begin_time' => '20:30', 'end_time' => '07:00', ]);  // need time

        DB::table('shift_codes')->insertOrIgnore([ 'name' => '20AU', 'begin_time' => '20:45', 'end_time' => '07:15', ]);
    }
}
