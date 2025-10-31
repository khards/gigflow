<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class SoftDeleteBillpayersTable extends Migration
{
    public function up()
    {
        Schema::table('billpayers', function (Blueprint $table) {
            $table->softDeletes();
        });
    }
}
