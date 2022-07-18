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
            'name' => 'Demo, Fred',
            'email' => 'Fred@demo.com',
            'text_number' => '313-555-0110',
            'voice_number' => '313-555-0110',
            'offered' => 1.3,
        ]);

        DB::table('extras')->insertOrIgnore([
            'name' => 'Demo, Gracie',
            'email' => '',
            'text_number' => '',
            'voice_number' => '313-555-0184',
            'offered' => 18.3,
        ]);

        DB::table('extras')->insertOrIgnore([
            'name' => 'Demo, Tom',
            'email' => '',
            'text_number' => '313-555-0168',
            'voice_number' => '',
            'offered' => 27.4,
        ]);

        DB::table('extras')->insertOrIgnore([
            'name' => 'Demo, Janet',
            'email' => 'Janet@demo.com',
            'text_number' => '',
            'voice_number' => '',
            'offered' => 57.1,
        ]);

    }
}
