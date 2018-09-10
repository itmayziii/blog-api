<?php

use App\WebPageType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestingWebPageTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        WebPageType::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $webPageTypeFactory = factory(WebPageTypeTableSeeder::class);
        $webPageTypeFactory->create([
            'name' => 'post'
        ]);
        $webPageTypeFactory->create([
            'name' => 'page'
        ]);
    }
}
