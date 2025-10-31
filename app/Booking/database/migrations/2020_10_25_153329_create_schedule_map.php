<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScheduleMap extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

//        taggables               schedule_maps
//        tag_id - integer        schedule_id
//        taggable_id - integer   schedule_map_id         // product id
//        taggable_type - string  schedule_map_type       // product type

        Schema::create('schedule_maps', function (Blueprint $table) {
            $table->bigIncrements('id');               // ID

            $table->unsignedBigInteger('schedule_id'); // The schedule ID
            $table->morphs('schedule_map');             // Model type (product), Product Id this schedule relates to

            $table->string('key')->nullable();      // (booking/hour/day/person etc...)
            $table->integer('value')->nullable();   // (Price, qty etc.)
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
        Schema::dropIfExists('schedule_maps');
    }
}
