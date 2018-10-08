<?php

$factory->define(App\Models\Tag::class, function (Faker\Generator $faker) {

    return [
        'created_at' => $faker->unixTime,
        'updated_at' => $faker->unixTime,
        'name'       => $faker->unique()->word
    ];

});