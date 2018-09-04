<?php

use App\Category;
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
            'id'   => 1,
            'name' => 'Category One',
            'slug' => 'category-one'
        ]);
    }

}
