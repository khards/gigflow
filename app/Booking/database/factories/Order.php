<?php

/** @var Factory $factory */

use App\Domains\Order\OrderStatus;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Vanilo\Order\Models\Order;

$factory->define(Order::class, function (Faker $faker) {
    return [
        'number' => $faker->randomNumber(5),
        'status' => OrderStatus::NEW,
    ];
});
