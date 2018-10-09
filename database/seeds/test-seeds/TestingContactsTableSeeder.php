<?php

use App\Models\Contact;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestingContactsTableSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Contact::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $contactFactory = factory(Contact::class);

        $contactFactory->create([
            'id'         => 1,
            'created_at' => strtotime('2018-06-18 12:00:30'),
            'updated_at' => strtotime('2018-06-18 12:00:30'),
            'first_name' => 'John',
            'last_name'  => 'Smith',
            'email'      => 'johnsmith@example.com',
            'comments'   => 'Wow what an amazing website :)'
        ]);
    }

}
