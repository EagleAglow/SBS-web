<?php

use Illuminate\Database\Seeder;

class LogItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() 
    {
        // Truncate the database so we don't repeat the seed
        DB::table('log_items')->delete();
        DB::table('log_items')->insertOrIgnore([ 'note' => 'Log Created', 'created_at' => date("Y-m-d H:i:s"), 'updated_at' => date("Y-m-d H:i:s") ]);
    }
}