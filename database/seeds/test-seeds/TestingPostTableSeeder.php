<?php

use App\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestingPostTableSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('posts')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $postFactory = factory(Post::class);

        $postFactory->create([
            'id'              => 1,
            'slug'            => 'post-one',
            'created_at'      => strtotime('2018-06-18 12:00:30'),
            'updated_at'      => strtotime('2018-06-18 12:00:30'),
            'user_id'         => 1,
            'category_id'     => 1,
            'status'          => 'live',
            'title'           => 'Post One',
            'content'         => 'Some really long content',
            'preview'         => 'Some really short content',
            'image_path_sm'   => '/images/post-one-image-sm',
            'image_path_md'   => '/images/post-one-image-md',
            'image_path_lg'   => '/images/post-one-image-lg',
            'image_path_meta' => '/images/post-one-image-meta'
        ]);

        $postFactory->create([
            'id'              => 2,
            'slug'            => 'post-two',
            'created_at'      => strtotime('2018-06-18 12:00:30'),
            'updated_at'      => strtotime('2018-06-18 12:00:30'),
            'user_id'         => 1,
            'category_id'     => 1,
            'status'          => 'draft',
            'title'           => 'Post Two',
            'content'         => 'Some really long content',
            'preview'         => 'Some really short content',
            'image_path_sm'   => '/images/post-one-image-sm',
            'image_path_md'   => '/images/post-one-image-md',
            'image_path_lg'   => '/images/post-one-image-lg',
            'image_path_meta' => '/images/post-one-image-meta'
        ]);
    }
}
