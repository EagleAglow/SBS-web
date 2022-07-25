<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('params', function (Blueprint $table) {
            $table->id();
            $table->string('param_name');  // e.g next-bidder-number
            $table->date('date_value')->nullable();  // for date parameters
            // native format maybe?  - 'YYYY-MM-DD'
            $table->dateTime('date_time_value')->nullable();  // for date-time parameters
            // native format maybe?  - 'YYYY-MM-DD hh:mm:ss'
            $table->integer('integer_value')->nullable();  // e.g., next bidder in bidding process
            $table->boolean('boolean_value')->default(false);
            $table->string('string_value')->nullable();
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
        Schema::dropIfExists('params');
    }
}
