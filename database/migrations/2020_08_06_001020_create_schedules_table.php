<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id(); // Alias of $table->bigIncrements('id')
            $table->string('title')->default('Title Missing!');
            $table->date('start');
            // native format maybe?  - 'YYYY-MM-DD'
            $table->tinyInteger('cycle_count')->default(1);  // usually 3
            $table->boolean('approved')->default(false);
            $table->boolean('active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedules');
    }
}
