<?php

/** @var Factory $factory */

use App\Booking\Holiday;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Holiday::class, function (Faker $faker) {
    return [
        'id' => $faker->unique(),
        'title' => $faker->paragraph(),
        'description' => $faker->sentence(),
    ];
});
