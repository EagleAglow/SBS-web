<?php

use Illuminate\Database\Seeder;

class SchedulesTableSeeder extends Seeder
{
    public function run()
    {
        // Truncate the database so we don't repeat the seed
        DB::table('schedules')->delete();

        DB::table('schedules')->insert([ 'title' => 'Begin September 17, 2020',
            // native mySQL format?  - 'YYYY-MM-DD'
            'start' => '2020-09-17',
            'cycle_count' => '3',
            // note FALSE = 0
            'approved' => '0',
            'active' => '0',
         ]);
    }
}