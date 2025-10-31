<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCalendarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
         * This represents that something (model/id) is booked between a certain date.
         * This could be equipment or staff.
         *
         * In the case of copying kwikevent.com, it would be a User model with the ID.
         *
         */
        Schema::create('calendar', function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('model');
            $table->dateTime('start');
            $table->dateTime('end');
            $table->string('timezone')->default('Europe/London');
            $table->morphs('booked_by');
            $table->unsignedInteger('created_by')->nullable()->index();
            $table->unsignedInteger('updated_by')->nullable()->index();
            $table->softDeletes();
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
        Schema::dropIfExists('calendar');
    }
}
