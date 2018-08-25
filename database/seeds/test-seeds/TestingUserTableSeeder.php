<?php

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestingUserTableSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $userFactory = factory(User::class);

        $userFactory->create([
            'id'         => 1,
            'first_name' => 'Tommy',
            'last_name'  => 'May',
            'email'      => 'tommymay37@gmail.com',
            'role'       => 'Administrator'
        ]);

        $userFactory->create([
            'id'         => 2,
            'first_name' => 'Test',
            'last_name'  => 'One',
            'email'      => 'testuser1@example.com',
            'role'       => 'Standard'
        ]);
    }

}
