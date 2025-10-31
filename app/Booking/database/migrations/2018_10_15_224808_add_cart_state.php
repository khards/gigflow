<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Vanilo\Cart\Models\CartStateProxy;

class AddCartState extends Migration
{
    public const ACTIVE = 'active';

    public const CHECKOUT = 'checkout';

    public const COMPLETED = 'completed';

    public const ABANDONDED = 'abandoned';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->string('state')->default(self::ACTIVE);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn('state');
        });
    }
}
