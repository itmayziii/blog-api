<?php

use App\Category;
use App\User;

$factory->define(App\Blog::class, function (Faker\Generator $faker) {

    $user = User::all()->shuffle()->first(); // Random user
    $category = Category::all()->shuffle()->first(); // Random category
    $sentence = $faker->unique()->sentence;
    \Illuminate\Support\Facades\Log::info($sentence);
    return [
        'slug'        => str_slug($sentence),
        'created_at'  => $faker->dateTime,
        'updated_at'  => $faker->dateTime,
        'user_id'     => $user->id,
        'category_id' => $category->id,
//        'status'      => 'draft', // Default in DB
        'title'       => $sentence,
        'content'     => $faker->paragraphs(5, true),
        'image_path'  => $faker->imageUrl(183, 183)
    ];

});
