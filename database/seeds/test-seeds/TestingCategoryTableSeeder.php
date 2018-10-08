<?php

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestingCategoryTableSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Category::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $categoryFactory = factory(Category::class);

        $categoryFactory->create([
            'id'          => 1,
            'name'        => 'Post',
            'plural_name' => 'Posts',
            'slug'        => 'posts'
        ]);
    }

}
