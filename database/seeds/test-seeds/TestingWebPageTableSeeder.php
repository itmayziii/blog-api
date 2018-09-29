<?php

use App\WebPage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestingWebPageTableSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        WebPage::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $webPageFactory = factory(WebPage::class);

        $webPageFactory->create([
            'id'                => 1,
            'created_at'        => strtotime('2018-06-18 12:00:30'),
            'updated_at'        => strtotime('2018-06-18 12:00:30'),
            'created_by'        => 1,
            'last_updated_by'   => 1,
            'category_id'       => 1,
            'slug'              => 'post-one',
            'type_id'           => 1,
            'is_live'           => 1,
            'title'             => 'Post One',
            'short_description' => 'Short description of the web page',
            'image_path_sm'     => '/images/post-one-image-sm',
            'image_path_md'     => '/images/post-one-image-md',
            'image_path_lg'     => '/images/post-one-image-lg',
            'image_path_meta'   => '/images/post-one-image-meta'
        ]);

        $webPageFactory->create([
            'id'                => 2,
            'created_at'        => strtotime('2018-06-18 12:00:30'),
            'updated_at'        => strtotime('2018-06-18 12:00:35'),
            'created_by'        => 1,
            'last_updated_by'   => 1,
            'category_id'       => 1,
            'slug'              => 'post-two',
            'type_id'           => 1,
            'is_live'           => 0,
            'title'             => 'Post Two',
            'short_description' => 'Short description of the web page',
            'image_path_sm'     => '/images/post-one-image-sm',
            'image_path_md'     => '/images/post-one-image-md',
            'image_path_lg'     => '/images/post-one-image-lg',
            'image_path_meta'   => '/images/post-one-image-meta'
        ]);
    }
}