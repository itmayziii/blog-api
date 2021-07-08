<?php

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestingTagTableSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Tag::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $tagFactory = factory(Tag::class);

        $tagFactory->create([
            'id'         => 1,
            'created_at' => strtotime('2018-08-15 17:00:00'),
            'updated_at' => strtotime('2018-08-15 17:00:00'),
            'name'       => 'Angular',
            'slug'       => 'angular'
        ]);

        $tagFactory->create([
            'id'         => 2,
            'created_at' => strtotime('2018-08-16 17:00:00'),
            'updated_at' => strtotime('2018-08-16 17:20:00'),
            'name'       => 'Typescript',
            'slug'       => 'typescript'
        ]);
    }
}
