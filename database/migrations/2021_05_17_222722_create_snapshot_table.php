<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSnapshotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('snapshot', function (Blueprint $table) {
            $table->id();
            $table->timestamp('snapshot_at')->nullable();
            $table->unsignedBigInteger('schedule_line_id')->unsigned();
            $table->foreign('schedule_line_id')->references('id')->on('schedule_lines')
                ->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade');
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
        Schema::dropIfExists('snapshot');
    }
}