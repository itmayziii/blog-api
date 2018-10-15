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

    public function test_store_responds_unauthenticated()
    {
        $response = $this->post('v1/tags');
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

    public function test_store_responds_forbidden()
    {
        $this->actAsStandardUser();

        $response = $this->post('v1/tags');
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

    public function test_store_responds_required_validation_failed()
    {
        $this->actAsAdministrativeUser();

        $response = $this->post('v1/tags');
        $response->assertResponseStatus(422);
        $response->seeJsonEquals([
            'errors' => [
                [
                    'status' => '422',
                    'title'  => 'Unprocessable Entity',
                    'source' => [
                        'name' => ['The name field is required.'],
                        'slug' => ['The slug field is required.']
                    ]
                ]
            ]
        ]);
    }

    public function test_store_responds_other_validation_failed()
    {
        $this->actAsAdministrativeUser();

        $response = $this->post('v1/tags', [
            'name' => self::textOver255Characters,
            'slug' => self::textOver255Characters
        ]);
        $response->assertResponseStatus(422);
        $response->seeJsonEquals([
            'errors' => [
                [
                    'status' => '422',
                    'title'  => 'Unprocessable Entity',
                    'source' => [
                        'name' => ['The name may not be greater than 50 characters.'],
                        'slug' => ['The slug may not be greater than 255 characters.', 'The slug may only contain letters, numbers, dashes and underscores.']
                    ]
                ]
            ]
        ]);
    }

    public function test_store_creates_tag()
    {
        $this->actAsAdministrativeUser();

        $response = $this->post('v1/tags', [
            'name' => 'PHP',
            'slug' => 'php'
        ]);
        $response->assertResponseStatus(201);
    }

    public function test_update_responds_unauthenticated()
    {
        $response = $this->put('v1/tags/2');
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

    public function test_update_responds_forbidden()
    {
        $this->actAsStandardUser();

        $response = $this->put('v1/tags/2');
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

    public function test_update_responds_required_validation_failed()
    {
        $this->actAsAdministrativeUser();

        $response = $this->put('v1/tags/2');
        $response->assertResponseStatus(422);
        $response->seeJsonEquals([
            'errors' => [
                [
                    'status' => '422',
                    'title'  => 'Unprocessable Entity',
                    'source' => [
                        'name' => ['The name field is required.'],
                        'slug' => ['The slug field is required.']
                    ]
                ]
            ]
        ]);
    }

    public function test_update_responds_other_validation_failed()
    {
        $this->actAsAdministrativeUser();

        $response = $this->put('v1/tags/2', [
            'name' => self::textOver255Characters,
            'slug' => self::textOver255Characters
        ]);
        $response->assertResponseStatus(422);
        $response->seeJsonEquals([
            'errors' => [
                [
                    'status' => '422',
                    'title'  => 'Unprocessable Entity',
                    'source' => [
                        'name' => ['The name may not be greater than 50 characters.'],
                        'slug' => ['The slug may not be greater than 255 characters.', 'The slug may only contain letters, numbers, dashes and underscores.']
                    ]
                ]
            ]
        ]);
    }

    public function test_update_updates_tag()
    {
        $this->actAsAdministrativeUser();

        $response = $this->put('v1/tags/2', [
            'name' => 'React',
            'slug' => 'react'
        ]);
        $response->assertResponseStatus(200);
    }

    public function test_delete_responds_unauthenticated()
    {
        $response = $this->delete('v1/tags/2');
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

    public function test_delete_responds_forbidden()
    {
        $this->actAsStandardUser();

        $response = $this->delete('v1/tags/2');
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

    public function test_delete_deletes_tag()
    {
        $this->actAsAdministrativeUser();

        $response = $this->delete('v1/tags/2');
        $response->assertResponseStatus(204);
    }
}
