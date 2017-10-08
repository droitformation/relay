<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewsletterTrackingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('newsletter_tracking', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sendgrid_internal_message_id')->nullable();
            $table->string('email')->nullable();
            $table->dateTime('timestamp')->nullable();
            $table->string('smtp-id')->nullable();
            $table->string('event')->nullable();
            $table->integer('asm_group_id')->nullable();
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
        Schema::dropIfExists('newsletter_tracking');
    }
}
