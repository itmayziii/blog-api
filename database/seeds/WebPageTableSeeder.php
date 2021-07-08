<?php

use App\Models\WebPage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WebPageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        WebPage::truncate();
        factory(WebPage::class, 100)->create();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
