<?php

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestingUserTableSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $userFactory = factory(User::class);

        $userFactory->create([
            'id'         => 1,
            'created_at' => strtotime('2018-06-20 12:00:30'),
            'updated_at' => strtotime('2018-06-21 12:00:30'),
            'first_name' => 'Tommy',
            'last_name'  => 'May',
            'email'      => 'tommymay37@gmail.com',
            'role'       => 'Administrator'
        ]);

        $userFactory->create([
            'id'         => 2,
            'created_at' => strtotime('2018-06-23 12:00:30'),
            'updated_at' => strtotime('2018-06-24 12:00:30'),
            'first_name' => 'Test',
            'last_name'  => 'One',
            'email'      => 'testuser1@example.com',
            'role'       => 'Standard'
        ]);
    }

}
