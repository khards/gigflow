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
        /*
         * Can be products, schedule or user availability Schedule!!
         */
        Schema::create('schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('model');
            $table->string('summary')->nullable();
            $table->string('state')->nullable();

            $table->dateTime('start_datetime')->nullable();
            $table->dateTime('end_datetime')->nullable();

            $table->boolean('is_recurring')->nullable();
            $table->string('extuid')->nullable(); // uid from the calendar
            $table->string('rrule')->nullable(); // The RRULE.
            $table->string('timezone')->nullable(); // The timezone

            // Calendar can be used to override prices, etc..
            $table->schemalessAttributes('properties');

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
        Schema::dropIfExists('schedules');
    }
}
