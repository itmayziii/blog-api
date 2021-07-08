<?php

use App\Models\User;
use Illuminate\Support\Str;

$factory->define(App\Models\Category::class, function (Faker\Generator $faker) {

    $name = $faker->word;
    $slug = Str::slug($name);
    $randomUser = User::all()->shuffle()->first();

    return [
        'created_at'      => $faker->unixTime,
        'updated_at'      => $faker->unixTime,
        'created_by'      => $randomUser->getAttribute('id'),
        'last_updated_by' => $randomUser->getAttribute('id'),
        'name'            => $name,
        'plural_name'     => $name . 's',
        'slug'            => $slug
    ];

});
