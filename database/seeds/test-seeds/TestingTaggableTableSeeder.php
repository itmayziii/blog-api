<?php

use App\Models\Tag;
use App\Models\WebPage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestingTaggableTableSeeder extends Seeder
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
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $tags = Tag::all();

        $webPageOne = WebPage::find(1);
        $webPageTwo = WebPage::find(2);

        $webPageOne->tags()->attach($tags->get(0));
        $webPageTwo->tags()->attach($tags->get(1));

    }
}
