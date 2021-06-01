<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMirrorToScheduleLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedule_lines', function (Blueprint $table) {
            $table->boolean('mirror')->default(false);   //  set True if this is a line cloned for a mirror bidder
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedule_lines', function (Blueprint $table) {
            $table->dropColumn('mirror');
        });
    }
}
