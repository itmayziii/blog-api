<?php

use App\Category;
use App\User;
use App\WebPage;

$factory->define(WebPage::class, function (Faker\Generator $faker) {

    $user = User::all()->shuffle()->first(); // Random user
    $category = Category::all()->shuffle()->first(); // Random category

    return [
        'created_at'      => $faker->unixTime,
        'updated_at'      => $faker->unixTime,
        'created_by'      => $user->getAttribute('id'),
        'last_updated_by' => $user->getAttribute('id'),
        'category_id'     => $category->getAttribute('id'),
        'path'            => "/posts/{$faker->slug}",
        'is_live'         => rand(0, 1),
        'title'           => $faker->title,
        'content'         => $faker->paragraphs(5, true),
        'preview'         => $faker->paragraphs(1, true),
        'image_path_sm'   => $faker->imageUrl(768, 300),
        'image_path_md'   => $faker->imageUrl(992, 400),
        'image_path_lg'   => $faker->imageUrl(1920, 500),
        'image_path_meta' => $faker->imageUrl(1200, 630)
    ];
});
