<?php

use App\Models\Tag;
use App\Models\WebPage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaggableTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('taggables')->truncate();

        $tags = Tag::all();
        $webPages = WebPage::all();
        foreach ($webPages as $webPage) {

            $tags->shuffle();

            $randomTags = $tags->random(3);
            $webPage->tags()->attach($randomTags);
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
