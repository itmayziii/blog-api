<?php

use Illuminate\Support\Str;

$factory->define(App\Models\Category::class, function (Faker\Generator $faker) {

    $name = $faker->word;
    $slug = Str::slug($name);

    return [
        'created_at'  => $faker->unixTime,
        'updated_at'  => $faker->unixTime,
        'name'        => $name,
        'plural_name' => $name . 's',
        'slug'        => $slug
    ];

});