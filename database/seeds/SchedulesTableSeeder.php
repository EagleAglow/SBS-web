<?php

use Illuminate\Database\Seeder;

class SchedulesTableSeeder extends Seeder
{
    public function run()
    {
        // Truncate the database so we don't repeat the seed
        DB::table('schedules')->delete();

        DB::table('schedules')->insert([ 'title' => 'Begin April 29, 2021',  // note title used in ScheduleLinesSeeder
            // native mySQL format?  - 'YYYY-MM-DD'
            'start' => '2021-04-29',
            'cycle_count' => '3',
            'cycle_days' => '56',
            // note FALSE = 0
            'approved' => '0',
            'active' => '0',
         ]);
    }
}