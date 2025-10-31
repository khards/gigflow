<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Transactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id')->unsigned();
            $table->text('method');
            $table->bigInteger('amount');
            $table->text('currency');
            $table->text('note');
            $table->json('details');

            /*
            id          int     1-129329139
            order_id    int     External reference to orders.id
            method      string  paypal, cash, bank,
            details     json    {}
            amount      int     -1000,      (amount in pennies)
            note        text    Refund in cash.


            */

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
    }
}
