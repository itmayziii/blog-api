<?php

$factory->define(App\Category::class, function (Faker\Generator $faker) {

    return [
        'created_at' => $faker->unixTime,
        'updated_at' => $faker->unixTime,
        'name'       => $faker->unique()->sentence(3, true),
        'slug'       => $faker->slug
    ];

});