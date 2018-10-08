<?php

$factory->define(App\Models\User::class, function (Faker\Generator $faker) {

    return [
        'created_at'           => $faker->unixTime,
        'updated_at'           => $faker->unixTime,
        'first_name'           => $faker->firstName,
        'last_name'            => $faker->lastName,
        'email'                => $faker->unique()->safeEmail,
        'password'             => app('hash')->make('ThisPass1'),
        'api_token'            => null,
        'api_token_expiration' => null,
        'api_limit'            => 1000,
        'role'                 => $faker->randomElement(['Standard', 'Administrator'])
    ];

});
