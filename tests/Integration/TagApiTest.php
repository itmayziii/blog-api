<?php

namespace Tests\Integration;

use Illuminate\Support\Facades\Artisan;
use TestingDatabaseSeeder;
use Tests\TestCase;

class TagApiTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        Artisan::call('db:seed', ['--class' => TestingDatabaseSeeder::class]);
    }

    public function test_show_responds_not_found()
    {
        $response = $this->get('v1/tags/9437529754');
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

    public function test_show_responds_with_tag_by_id()
    {
        $response = $this->get('v1/tags/1');
        $response->assertResponseStatus(200);
        $response->seeJsonEquals([
            'data' => [
                'id'         => '1',
                'type'       => 'tags',
                'attributes' => [
                    'created_at' => '2018-08-15T17:00:00+00:00',
                    'updated_at' => '2018-08-15T17:00:00+00:00',
                    'name'       => 'Angular',
                    'slug'       => 'angular'
                ],
                'links'      => [
                    'self' => 'http://api.fullheapdeveloper.local:8080/v1/tags/angular'
                ]
            ]
        ]);
    }

    public function test_show_responds_with_tag_by_slug()
    {
        $response = $this->get('v1/tags/angular');
        $response->assertResponseStatus(200);
        $response->seeJsonEquals([
            'data' => [
                'id'         => '1',
                'type'       => 'tags',
                'attributes' => [
                    'created_at' => '2018-08-15T17:00:00+00:00',
                    'updated_at' => '2018-08-15T17:00:00+00:00',
                    'name'       => 'Angular',
                    'slug'       => 'angular'
                ],
                'links'      => [
                    'self' => 'http://api.fullheapdeveloper.local:8080/v1/tags/angular'
                ]
            ]
        ]);
    }

    public function test_index_responds_with_contacts()
    {
        $response = $this->get('v1/tags');
        $response->assertResponseStatus(200);
        $response->seeJsonEquals([
            'data'  => [
                [
                    'id'         => '1',
                    'type'       => 'tags',
                    'attributes' => [
                        'created_at' => '2018-08-15T17:00:00+00:00',
                        'updated_at' => '2018-08-15T17:00:00+00:00',
                        'name'       => 'Angular',
                        'slug'       => 'angular'
                    ],
                    'links'      => [
                        'self' => 'http://api.fullheapdeveloper.local:8080/v1/tags/angular'
                    ]
                ],
                [
                    'id'         => '2',
                    'type'       => 'tags',
                    'attributes' => [
                        'created_at' => '2018-08-16T17:00:00+00:00',
                        'updated_at' => '2018-08-16T17:20:00+00:00',
                        'name'       => 'Typescript',
                        'slug'       => 'typescript'
                    ],
                    'links'      => [
                        'self' => 'http://api.fullheapdeveloper.local:8080/v1/tags/typescript'
                    ]
                ]
            ],
            'links' => [
                'first' => 'http://localhost/v1/tags?page=1',
                'last'  => 'http://localhost/v1/tags?page=1'
            ]
        ]);
    }
}
