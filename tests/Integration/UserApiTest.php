<?php

namespace Tests\Integration;

use Illuminate\Support\Facades\Artisan;
use TestingDatabaseSeeder;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        Artisan::call('db:seed', ['--class' => TestingDatabaseSeeder::class]);
    }

    public function test_show_returns_not_found()
    {
        $response = $this->json('GET', 'v1/users/873459234572349857234');
        $response->assertResponseStatus(404);
        $response->seeJsonEquals([
            'errors' => [
                [
                    'status' => '404',
                    'title'  => 'Not Found'
                ]
            ]
        ]);
    }

    public function test_show_returns_authenticated()
    {
        $response = $this->json('GET', 'v1/users/1');
        $response->assertResponseStatus(401);
        $response->seeJsonEquals([
            'errors' => [
                [
                    'status' => '401',
                    'title'  => 'Unauthorized'
                ]
            ]
        ]);
    }

    public function test_show_returns_forbidden()
    {
        $this->actAsStandardUser();

        $response = $this->json('GET', 'v1/users/1');
        $response->assertResponseStatus(403);
        $response->seeJsonEquals([
            'errors' => [
                [
                    'status' => '403',
                    'title'  => 'Forbidden'
                ]
            ]
        ]);
    }

    public function test_show_returns_user()
    {
        $this->actAsStandardUser();

        $response = $this->json('GET', 'v1/users/2');
        $response->assertResponseStatus(200);
        $response->seeJsonEquals([
            'data' => [
                'type'       => 'users',
                'id'         => '2',
                'attributes' => [
                    'created_at' => '2018-06-23T12:00:30+00:00',
                    'updated_at' => '2018-06-24T12:00:30+00:00',
                    'first_name' => 'Test',
                    'last_name'  => 'One',
                    'email'      => 'testuser1@example.com',
                    'role'       => 'Standard',
                    'api_token'  => null
                ],
                'links'      => [
                    'self' => 'http://api.fullheapdeveloper.local:8080/v1/users/2'
                ]
            ]
        ]);
    }

    public function test_index_responds_unauthenticated()
    {
        $response = $this->json('GET', 'v1/users');
        $response->assertResponseStatus(401);
        $response->seeJsonEquals([
            'errors' => [
                [
                    'status' => '401',
                    'title'  => 'Unauthorized'
                ]
            ]
        ]);
    }

    public function test_index_responds_forbidden()
    {
        $this->actAsStandardUser();

        $response = $this->json('GET', 'v1/users');
        $response->assertResponseStatus(403);
        $response->seeJsonEquals([
            'errors' => [
                [
                    'status' => '403',
                    'title'  => 'Forbidden'
                ]
            ]
        ]);
    }

    public function test_index_returns_users()
    {
        $this->actAsAdministrativeUser();

        $response = $this->json('GET', 'v1/users');
        $response->assertResponseStatus(200);
        $response->seeJsonEquals([
            'data'  => [
                [
                    'type'       => 'users',
                    'id'         => '1',
                    'attributes' => [
                        'created_at' => '2018-06-20T12:00:30+00:00',
                        'updated_at' => '2018-06-21T12:00:30+00:00',
                        'first_name' => 'Tommy',
                        'last_name'  => 'May',
                        'email'      => 'tommymay37@gmail.com',
                        'role'       => 'Administrator',
                        'api_token'  => null
                    ],
                    'links'      => [
                        'self' => 'http://api.fullheapdeveloper.local:8080/v1/users/1'
                    ]
                ],
                [
                    'type'       => 'users',
                    'id'         => '2',
                    'attributes' => [
                        'created_at' => '2018-06-23T12:00:30+00:00',
                        'updated_at' => '2018-06-24T12:00:30+00:00',
                        'first_name' => 'Test',
                        'last_name'  => 'One',
                        'email'      => 'testuser1@example.com',
                        'role'       => 'Standard',
                        'api_token'  => null
                    ],
                    'links'      => [
                        'self' => 'http://api.fullheapdeveloper.local:8080/v1/users/2'
                    ]
                ],
            ],
            'links' => [
                'first' => 'http://localhost/v1/users?page=1',
                'last'  => 'http://localhost/v1/users?page=1'
            ]
        ]);
    }
}
