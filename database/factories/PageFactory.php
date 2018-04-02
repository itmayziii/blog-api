<?php

use App\Page;

$factory->define(Page::class, function (Faker\Generator $faker) {

    $sentence = $faker->unique()->sentence;
    return [
        'created_at' => $faker->unixTime,
        'updated_at' => $faker->unixTime,
        'title'      => $sentence,
        'slug'       => str_slug($sentence),
        'content'    => $faker->paragraphs(5, true),
        'is_live'    => $faker->boolean
    ];

});
