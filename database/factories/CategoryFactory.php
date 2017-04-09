<?php

$factory->define(App\Category::class, function (Faker\Generator $faker) {

    return [
        'created_at' => $faker->dateTime,
        'updated_at' => $faker->dateTime,
        'name'       => $faker->unique()->sentence(3, true)
    ];

});