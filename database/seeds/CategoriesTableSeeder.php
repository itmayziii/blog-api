<?php

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Category::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $categoryFactory = factory(Category::class);

        $categoryFactory->create([
            'name'        => 'Post',
            'plural_name' => 'Posts',
            'slug'        => 'posts'
        ]);

        $categoryFactory->create([
            'name'        => 'Main',
            'plural_name' => 'Main',
            'slug'        => 'main'
        ]);
    }
}
