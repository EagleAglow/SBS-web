<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtrasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('extras', function (Blueprint $table) {
            $table->id();; // Alias of $table->bigIncrements('id')
            $table->string('name');   // Last, First - both in this field
            $table->string('email')->nullable()->default(''); // used to send email (contact info 3)
            $table->string('text_number')->nullable()->default(''); // used to send text (contact info 2)
            $table->string('voice_number')->nullable()->default(''); // actual human phone call (contact info 1)
            $table->boolean('active')->default(false);    // notification in progress, not yet complete, or not yet timed out
            $table->timestamp('active_at')->nullable();   // start time for notifying this person
            $table->boolean('notified')->default(false);  // by one or more methods
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamp('text_sent_at')->nullable();
            $table->timestamp('voice_call_at')->nullable();
            $table->unsignedBigInteger('call_order')->nullable();   // used to select next to be called, lowest number first
            // call_order is natural table order, or maybe... future, OT offered value?
            $table->decimal('offered', 6, 2)->nullable();  // three decimal precision, two decimal display?
            $table->string('response')->nullable();   // call in result, future?
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
        Schema::dropIfExists('extras');
    }
}
