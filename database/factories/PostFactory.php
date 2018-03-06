<?php

use App\Category;
use App\User;

$factory->define(App\Post::class, function (Faker\Generator $faker) {

    $user = User::all()->shuffle()->first(); // Random user
    $category = Category::all()->shuffle()->first(); // Random category
    $sentence = $faker->unique()->sentence;
    \Illuminate\Support\Facades\Log::info($sentence);
    return [
        'slug'          => str_slug($sentence),
        'created_at'    => $faker->unixTime,
        'updated_at'    => $faker->unixTime,
        'user_id'       => $user->id,
        'category_id'   => $category->id,
        'status'        => $faker->randomElement(['draft', 'live']),
        'title'         => $sentence,
        'content'       => $faker->paragraphs(5, true),
        'preview'       => $faker->paragraph(),
        'image_path_sm' => $faker->imageUrl(768, 300),
        'image_path_md' => $faker->imageUrl(992, 400),
        'image_path_lg' => $faker->imageUrl(1920, 500)
    ];

});
