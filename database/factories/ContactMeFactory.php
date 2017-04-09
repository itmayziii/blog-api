<?php

$factory->define(App\ContactMe::class, function (Faker\Generator $faker) {

    return [
        'created_at' => $faker->dateTime,
        'updated_at' => $faker->dateTime,
        'first_name' => $faker->firstName,
        'last_name'  => $faker->lastName,
        'email'      => $faker->safeEmail,
        'comments'   => $faker->paragraphs(3, true)
    ];

});