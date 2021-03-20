<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // Alias of $table->bigIncrements('id')
            $table->string('name');   // Last, First - both in this field
            $table->string('email')->unique();  // example: Thomas.Jefferson@cbsa-asfc.gc.ca
            $table->unsignedBigInteger('bidder_group_id')->nullable();
            $table->boolean('has_bid')->default(false);   
            $table->unsignedBigInteger('bid_order')->nullable();   // used to select next bidder, lowest number bids first
            // bid_order is generated from bid group bidding order, and next two fields, with when a schedule is made active
            // when a schedule is made inactive, bid_order and tie-breaker are cleared
            $table->date('seniority_date')->nullable();  // earliest date in a bidding group bids first
            $table->unsignedBigInteger('bidder_tie_breaker')->nullable();   // tie-breaker, lowest number bids first
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
