<?php

use App\ContactMe;
use Illuminate\Database\Seeder;

class ContactMeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ContactMe::truncate();
        factory(ContactMe::class, 100)->create();
    }
}
