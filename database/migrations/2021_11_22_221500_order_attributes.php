<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OrderAttributes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_addon')->default(0);
            $table->boolean('is_required')->default(0);

            //Currently untested
            $table->string('dispatchPrice')->default('999.99');
            $table->string('totalProductPrice')->default('99999.99');
            $table->string('adjustments')->default('0.00');
            $table->string('totalPrice')->default('999999.11');
            $table->string('deposit')->default('999999.11');

            $table->integer('business_id')->nullable();

            $table->string('start')->nullable();
            $table->string('end')->nullable();
            $table->string('location')->nullable();
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
