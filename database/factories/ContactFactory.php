<?php

$factory->define(App\Contact::class, function (Faker\Generator $faker) {

    return [
        'created_at' => $faker->unixTime,
        'updated_at' => $faker->unixTime,
        'first_name' => $faker->firstName,
        'last_name'  => $faker->lastName,
        'email'      => $faker->safeEmail,
        'comments'   => $faker->paragraphs(3, true)
    ];

});