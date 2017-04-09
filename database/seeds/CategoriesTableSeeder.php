<?php

use App\Category;
use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // Truncating table will not work because of a foreign key constraint in table "blogs"
        Category::truncate();
        factory(Category::class, 8)->create();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
