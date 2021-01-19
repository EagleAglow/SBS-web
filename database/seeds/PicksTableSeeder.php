<?php

use Illuminate\Database\Seeder;

class PicksTableSeeder extends Seeder
{

    public function run()
    {
        // Truncate the database so we don't repeat the seed
        DB::table('picks')->delete();

        // and then don't seed - start with empty table

    }
}