<?php

use App\Blog;
use App\Tag;
use Illuminate\Database\Seeder;

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
        $blogs = Blog::all();
        foreach ($blogs as $blog) {

            $tags->shuffle();

            $randomTags = $tags->random(3);
            $blog->tags()->attach($randomTags);
        }
    }
}
