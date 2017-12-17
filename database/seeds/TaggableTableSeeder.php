<?php

use App\Post;
use App\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaggablesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('taggables')->truncate();

        $tags = Tag::all();
        $posts = Post::all();
        foreach ($posts as $post) {

            $tags->shuffle();

            $randomTags = $tags->random(3);
            $post->tags()->attach($randomTags);
        }
    }
}
