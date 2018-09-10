<?php

use App\User;
use App\WebPageType;

$factory->define(WebPageType::class, function (Faker\Generator $faker) {
    $randomUser = User::all()->shuffle()->first();

    return [
        'created_at'      => $faker->unixTime,
        'updated_at'      => $faker->unixTime,
        'created_by'      => $randomUser->getAttribute('id'),
        'last_updated_by' => $randomUser->getAttribute('id'),
        'name'            => $faker->word
    ];
});
