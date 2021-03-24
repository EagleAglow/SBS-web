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
            $table->string('line_with_fill',4);  // same as 'line' except leading zero filled, used for better sorting

            // composite index, named 'magic'
            $table->unique(['schedule_id', 'line_group_id', 'line'], 'magic');

            $table->foreignId('user_id')->nullable();  // bid set for this line
            $table->timestamp('bid_at')->nullable();  // bid set date/time
            $table->string('comment')->nullable();
            $table->boolean('blackout')->default(false);
            $table->boolean('nexus')->default(false);
            $table->boolean('barge')->default(false);
            $table->boolean('offsite')->default(false);

            // 56 day shift code pattern
            for ($n = 1; $n <= 56; $n++) {
                $d = 'day_' . substr(('00' . $n),-2);
                $table->unsignedBigInteger($d)->nullable()->default(Null);
                $table->foreign($d)->references('id')->on('shift_codes');
            }
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
