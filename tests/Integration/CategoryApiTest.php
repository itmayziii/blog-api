<?php

namespace Tests\Integration;

use Illuminate\Support\Facades\Artisan;
use TestingDatabaseSeeder;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        Artisan::call('db:seed', ['--class' => TestingDatabaseSeeder::class]);
    }

    public function test_show_responds_not_found()
    {
        $response = $this->json('GET', 'v1/categories/not-a-category');
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

    public function test_show_find_category_by_id()
    {
        $response = $this->json('GET', 'v1/categories/1');
        $response->assertResponseStatus(200);
        $response->seeJsonEquals([
            'data' => [
                'id'         => '1',
                'type'       => 'categories',
                'attributes' => [
                    'created_at'      => '2018-06-18T16:00:30+00:00',
                    'updated_at'      => '2018-06-18T17:00:00+00:00',
                    'created_by'      => 1,
                    'last_updated_by' => 1,
                    'name'            => 'Post',
                    'plural_name'     => 'Posts',
                    'slug'            => 'posts'
                ],
                'links'      => [
                    'self' => 'http://api.fullheapdeveloper.local:8080/v1/categories/posts'
                ]
            ]
        ]);
    }

    public function test_show_find_category_by_slug()
    {
        $response = $this->json('GET', 'v1/categories/posts');
        $response->assertResponseStatus(200);
        $response->seeJsonEquals([
            'data' => [
                'id'         => '1',
                'type'       => 'categories',
                'attributes' => [
                    'created_at'      => '2018-06-18T16:00:30+00:00',
                    'updated_at'      => '2018-06-18T17:00:00+00:00',
                    'created_by'      => 1,
                    'last_updated_by' => 1,
                    'name'            => 'Post',
                    'plural_name'     => 'Posts',
                    'slug'            => 'posts'
                ],
                'links'      => [
                    'self' => 'http://api.fullheapdeveloper.local:8080/v1/categories/posts'
                ]
            ]
        ]);
    }

    public function test_index_returns_all_categories()
    {
        $response = $this->json('GET', 'v1/categories');

        $response->assertResponseStatus(200);
        $response->seeHeader('Content-Type', 'application/vnd.api+json');
        $response->seeJsonEquals([
            'data'  => [
                [
                    'type'       => 'categories',
                    'id'         => '1',
                    'attributes' => [
                        'created_at'      => '2018-06-18T16:00:30+00:00',
                        'updated_at'      => '2018-06-18T17:00:00+00:00',
                        'created_by'      => 1,
                        'last_updated_by' => 1,
                        'name'            => 'Post',
                        'plural_name'     => 'Posts',
                        'slug'            => 'posts'
                    ],
                    'links'      => [
                        'self' => 'http://api.fullheapdeveloper.local:8080/v1/categories/posts'
                    ]
                ]
            ],
            'links' => [
                'first' => 'http://localhost/v1/categories?page=1',
                'last'  => 'http://localhost/v1/categories?page=1'
            ]
        ]);
    }

    public function test_store_responds_not_authenticated()
    {
        $response = $this->json('POST', 'v1/categories');

        $response->assertResponseStatus(401);
        $response->seeHeader('Content-Type', 'application/vnd.api+json');
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

        $response = $this->json('POST', 'v1/categories');

        $response->assertResponseStatus(403);
        $response->seeHeader('Content-Type', 'application/vnd.api+json');
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

        $response = $this->json('POST', 'v1/categories');

        $response->assertResponseStatus(422);
        $response->seeHeader('Content-Type', 'application/vnd.api+json');
        $response->seeJsonEquals([
            'errors' => [
                [
                    'status' => '422',
                    'title'  => 'Unprocessable Entity',
                    'source' => [
                        'name'        => ['The name field is required.'],
                        'plural_name' => ['The plural name field is required.'],
                        'slug'        => ['The slug field is required.']
                    ]
                ]
            ]
        ]);
    }

    public function test_store_responds_other_validation_failed()
    {
        $this->actAsAdministrativeUser();

        $response = $this->json('POST', 'v1/categories', [
            'name'        => self::textOver255Characters,
            'plural_name' => self::textOver255Characters,
            'slug'        => self::textOver255Characters
        ]);

        $response->assertResponseStatus(422);
        $response->seeHeader('Content-Type', 'application/vnd.api+json');
        $response->seeJsonEquals([
            'errors' => [
                [
                    'status' => '422',
                    'title'  => 'Unprocessable Entity',
                    'source' => [
                        'name'        => ['The name may not be greater than 255 characters.'],
                        'plural_name' => ['The plural name may not be greater than 255 characters.'],
                        'slug'        => ['The slug may not be greater than 255 characters.', 'The slug may only contain letters, numbers, dashes and underscores.']
                    ]
                ]
            ]
        ]);
    }

    public function test_store_creates_category()
    {
        $this->actAsAdministrativeUser();

        $response = $this->json('POST', 'v1/categories', [
            'name'        => 'City',
            'plural_name' => 'Cities',
            'slug'        => 'cities'
        ]);

        $response->assertResponseStatus(201);
        $response->seeHeader('Content-Type', 'application/vnd.api+json');
    }

    public function test_update_responds_not_found()
    {
        $response = $this->json('PUT', 'v1/categories/not-a-real-category');
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

    public function test_update_responds_unauthenticated()
    {
        $response = $this->json('PUT', 'v1/categories/posts');
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

        $response = $this->json('PUT', 'v1/categories/posts');
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

    public function test_update_updates_category()
    {
        $this->actAsAdministrativeUser();

        $response = $this->json('PUT', 'v1/categories/posts', [
            'name' => 'City',
            'plural_name' => 'Cities',
            'slug' => 'cities'
        ]);
        $response->assertResponseStatus(200);
    }

    public function test_delete_responds_not_found()
    {
        $response = $this->json('DELETE', 'v1/categories/category-that-does-not-exist');
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

    public function test_delete_responds_not_authenticated()
    {
        $response = $this->json('DELETE', 'v1/categories/posts');
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

        $response = $this->json('DELETE', 'v1/categories/posts');
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

    public function test_delete_deletes_category()
    {
        $this->actAsAdministrativeUser();

        $response = $this->json('DELETE', 'v1/categories/posts');
        $response->assertResponseStatus(204);
    }
}
