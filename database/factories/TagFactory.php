<?php

use Illuminate\Support\Str;

$factory->define(App\Models\Tag::class, function (Faker\Generator $faker) {

    $name = $faker->unique()->word;
    $slug = Str::slug($name);

    return [
        'created_at' => $faker->unixTime,
        'updated_at' => $faker->unixTime,
        'name'       => $faker->unique()->word,
        'slug'       => $slug
    ];

});