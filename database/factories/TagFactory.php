<?php

$factory->define(App\Tag::class, function (Faker\Generator $faker) {

    return [
        'created_at' => $faker->dateTime,
        'updated_at' => $faker->dateTime,
        'name'       => $faker->unique()->word
    ];

});