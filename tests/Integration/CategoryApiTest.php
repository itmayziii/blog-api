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
                    'created_at'  => '2018-06-18T16:00:30+00:00',
                    'updated_at'  => '2018-06-18T17:00:00+00:00',
                    'name'        => 'Post',
                    'plural_name' => 'Posts',
                    'slug'        => 'posts'
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
                    'created_at'  => '2018-06-18T16:00:30+00:00',
                    'updated_at'  => '2018-06-18T17:00:00+00:00',
                    'name'        => 'Post',
                    'plural_name' => 'Posts',
                    'slug'        => 'posts'
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
                        'created_at'  => '2018-06-18T16:00:30+00:00',
                        'updated_at'  => '2018-06-18T17:00:00+00:00',
                        'name'        => 'Post',
                        'plural_name' => 'Posts',
                        'slug'        => 'posts'
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
}
