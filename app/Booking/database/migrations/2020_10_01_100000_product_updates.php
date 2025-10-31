<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ProductUpdates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('delivery_method')->nullable();     //  (bitmasked) 0=none, 1=delviered,2=collected,4=shipped  (3=deliverd+collected)
            $table->text('price_type')->nullable();             //  scheduled / fixed    - (Uses Schedule table unit to hold price and unit (booking/hour))
            $table->integer('price_fixed_price')->nullable();   //  The fixed price (optional)
            $table->integer('staff_quantity')->nullable();      //  Number of staff needed for this product (also powers yes/no 0=no)
            $table->text('availability_type')->nullable();      //  scheduled/available
            $table->unsignedBigInteger('availability_schedule')->nullable(); // Availability schedule id (fk=schedule.schedule_id)
            $table->integer('available_quantity')->nullable();  //  Number of units in stock
            $table->text('travelling_limit')->nullable();    //  yes/no select
            $table->text('travelling_type')->nullable();        //  miles/km/minutes/hours/etc..
            $table->integer('travelling_value')->nullable();        //  12
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
