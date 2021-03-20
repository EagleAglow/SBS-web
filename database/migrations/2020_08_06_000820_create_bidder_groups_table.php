<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBidderGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bidder_groups', function (Blueprint $table) {
            $table->id(); // Alias of $table->bigIncrements('id')
            $table->string('code',12)->unique();
            $table->string('name');
            $table->integer('order')->nullable;  // display order in bidding process
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
        Schema::dropIfExists('bidder_groups');
    }
}
