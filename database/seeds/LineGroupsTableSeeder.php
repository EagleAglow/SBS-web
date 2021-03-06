<?php

use Illuminate\Database\Seeder;

class LineGroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Truncate the database so we don't repeat the seed
        DB::table('line_groups')->delete();

        // There are permissions and roles that must correspond to these groups
        // See comments in PermissionsSeeder.php
        DB::table('line_groups')->insertOrIgnore([ 
            'code' => 'DEMO',
            'name' => 'Demonstration Line',
            'order' => '1',
            ]);
        DB::table('line_groups')->insertOrIgnore([
            'code' => 'TSU',
            'name' => 'TSU Line',
            'order' => '2',
            ]);
        DB::table('line_groups')->insertOrIgnore([
            'code' => 'IRPA',
            'name' => 'IRPA Line',
            'order' => '3',
            ]);
        DB::table('line_groups')->insertOrIgnore([
            'code' => 'OIDP',
            'name' => 'OIDP Line',
            'order' => '4',
            ]);
        DB::table('line_groups')->insertOrIgnore([
            'code' => 'TCOM',
            'name' => 'Commercial Traffic Line',
            'order' => '5',
        ]);
        DB::table('line_groups')->insertOrIgnore([
            'code' => 'TNON',
            'name' => 'Non-commercial Traffic Line',
            'order' => '6',
        ]);
        DB::table('line_groups')->insertOrIgnore([
            'code' => 'NONE',
            'name' => 'No one can bid for this line',
            'order' => '99',
        ]);
    }
}