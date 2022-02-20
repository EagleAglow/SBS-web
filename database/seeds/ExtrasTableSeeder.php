<?php

//namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Extra;

class ExtrasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Truncate the database so we don't repeat the seed
        DB::table('extras')->delete();

        DB::table('extras')->insertOrIgnore([
            'name' => 'fred',
            'email' => '',
            'text_number' => '',
            'voice_number' => '',
            'call_order' => '1',
        ]);

        DB::table('extras')->insertOrIgnore([
            'name' => 'gracie',
            'email' => '',
            'text_number' => '',
            'voice_number' => '',
            'call_order' => '2',
        ]);

        DB::table('extras')->insertOrIgnore([
            'name' => 'tom',
            'email' => '',
            'text_number' => '',
            'voice_number' => '',
            'call_order' => '3',
        ]);

        DB::table('extras')->insertOrIgnore([
            'name' => 'janet',
            'email' => '',
            'text_number' => '',
            'voice_number' => '',
            'call_order' => '4',
        ]);

    }
}
