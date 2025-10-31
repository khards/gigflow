<?php

use App\Booking\Product;
use Faker\Generator as Faker;

$factory->define(Product::class, function (Faker $faker) {
    return [
        'name'  => $faker->words(3, true),
        'sku'   => $faker->randomNumber(),
        'price' => $faker->randomFloat(2, 1, 10000),
        'type' => Product::class,
        'owner_id' => 1,
        'owner_type' => \App\Booking\Business::class,
    ];
});
