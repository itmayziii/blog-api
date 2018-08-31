<?php

namespace Tests\Integration\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use TestingDatabaseSeeder;
use Tests\TestCase;

class PageControllerTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        Artisan::call('db:seed', ['--class' => TestingDatabaseSeeder::class]);
    }

    public function test_index_responds_unauthenticated()
    {
        $response = $this->json('GET', 'v1/pages');
        $response->assertUnauthorized($response);
    }

    public function test_index_responds_unauthorized()
    {
        $this->actAsAdministrativeUser();
        $response = $this->json('GET', 'v1/pages');
        $response->assertForbidden($response);
    }
}
