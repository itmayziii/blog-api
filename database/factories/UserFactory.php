<?php

$factory->define(App\User::class, function (Faker\Generator $faker) {

    return [
        'created_at' => $faker->dateTime,
        'updated_at' => $faker->dateTime,
        'first_name' => $faker->firstName,
        'last_name'  => $faker->lastName,
        'email'      => $faker->unique()->safeEmail,
        'password'   => app('hash')->make('ThisPass1'),
        'api_token'  => str_random(60),
        'api_limit'  => 1000,
//        'role' => 'Standard' // Default in DB
    ];

});
