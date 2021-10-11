<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScheduleLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedule_lines', function (Blueprint $table) {
            $table->id(); // Alias of $table->bigIncrements('id').
            $table->foreignId('schedule_id');
            $table->foreignId('line_group_id');
            $table->string('line',4);
            // special handling for "natural sort"
            $table->string('line_natural',8)->nullable;  // two parts of 'line', each part leading "-" filled, used for "natural" sorting

            // composite index, named 'magic'
            $table->unique(['schedule_id', 'line_group_id', 'line'], 'magic');

            $table->foreignId('user_id')->nullable();  // bid set for this line
            $table->timestamp('bid_at')->nullable();  // bid set date/time
            $table->string('comment')->nullable();
            $table->boolean('blackout')->default(false);
            $table->boolean('nexus')->default(false);
            $table->boolean('barge')->default(false);
            $table->boolean('offsite')->default(false);

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
        Schema::dropIfExists('schedule_lines');
    }
}
