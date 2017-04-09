<?php

use App\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // Truncating table will not work because of a foreign key constraint in table "blogs"
        User::truncate();
        factory(User::class, 100)->create();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
