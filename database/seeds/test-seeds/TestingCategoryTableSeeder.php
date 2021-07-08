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
            'id'              => 1,
            'created_at'      => strtotime('2018-06-18 16:00:30'),
            'updated_at'      => strtotime('2018-06-18 17:00:00'),
            'created_by'      => 1,
            'last_updated_by' => 1,
            'name'            => 'Post',
            'plural_name'     => 'Posts',
            'slug'            => 'posts'
        ]);
    }
}
