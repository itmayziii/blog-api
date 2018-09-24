<?php

use App\Category;
use App\User;
use App\WebPage;
use App\WebPageType;
use Illuminate\Support\Str;

$factory->define(WebPage::class, function (Faker\Generator $faker) {

    $title = $faker->sentence;
    $slug = Str::slug($title);
    $randomUser = User::all()->shuffle()->first();
    $randomType = WebPageType::all()->shuffle()->first();
    $randomCategory = Category::all()->shuffle()->first();

    return [
        'created_at'        => $faker->unixTime,
        'updated_at'        => $faker->unixTime,
        'created_by'        => $randomUser->getAttribute('id'),
        'last_updated_by'   => $randomUser->getAttribute('id'),
        'category_id'       => $randomCategory->getAttribute('id'),
        'slug'              => $slug,
        'type_id'           => $randomType->getAttribute('id'),
        'is_live'           => rand(0, 1),
        'title'             => $title,
        'short_description' => $faker->paragraphs(1, true),
        'image_path_sm'     => $faker->imageUrl(768, 300),
        'image_path_md'     => $faker->imageUrl(992, 400),
        'image_path_lg'     => $faker->imageUrl(1920, 500),
        'image_path_meta'   => $faker->imageUrl(1200, 630)
    ];
});
