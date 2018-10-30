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
                'id'            => '1',
                'type'          => 'categories',
                'attributes'    => [
                    'created_at'      => '2018-06-18T16:00:30+00:00',
                    'updated_at'      => '2018-06-18T17:00:00+00:00',
                    'created_by'      => 1,
                    'last_updated_by' => 1,
                    'name'            => 'Post',
                    'plural_name'     => 'Posts',
                    'slug'            => 'posts'
                ],
                'relationships' => [
                    'webpages' => [
                        'links' => [
                            'related' => 'http://api.fullheapdeveloper.local:8080/v1/categories/posts/webpages'
                        ]
                    ]
                ],
                'links'         => [
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
                'id'            => '1',
                'type'          => 'categories',
                'attributes'    => [
                    'created_at'      => '2018-06-18T16:00:30+00:00',
                    'updated_at'      => '2018-06-18T17:00:00+00:00',
                    'created_by'      => 1,
                    'last_updated_by' => 1,
                    'name'            => 'Post',
                    'plural_name'     => 'Posts',
                    'slug'            => 'posts'
                ],
                'relationships' => [
                    'webpages' => [
                        'links' => [
                            'related' => 'http://api.fullheapdeveloper.local:8080/v1/categories/posts/webpages'
                        ]
                    ]
                ],
                'links'         => [
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
                    'type'          => 'categories',
                    'id'            => '1',
                    'attributes'    => [
                        'created_at'      => '2018-06-18T16:00:30+00:00',
                        'updated_at'      => '2018-06-18T17:00:00+00:00',
                        'created_by'      => 1,
                        'last_updated_by' => 1,
                        'name'            => 'Post',
                        'plural_name'     => 'Posts',
                        'slug'            => 'posts'
                    ],
                    'relationships' => [
                        'webpages' => [
                            'links' => [
                                'related' => 'http://api.fullheapdeveloper.local:8080/v1/categories/posts/webpages'
                            ]
                        ]
                    ],
                    'links'         => [
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
            'name'        => 'City',
            'plural_name' => 'Cities',
            'slug'        => 'cities'
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

    public function test_live_webpages_get_included()
    {
        $response = $this->json('GET', 'v1/categories/posts?included=webpages');
        $response->assertResponseStatus(200);
        $response->seeJsonEquals([
            'data'     => [
                'type'          => 'categories',
                'id'            => '1',
                'attributes'    => [
                    'created_at'      => '2018-06-18T16:00:30+00:00',
                    'updated_at'      => '2018-06-18T17:00:00+00:00',
                    'created_by'      => 1,
                    'last_updated_by' => 1,
                    'name'            => 'Post',
                    'plural_name'     => 'Posts',
                    'slug'            => 'posts'
                ],
                'relationships' => [
                    'webpages' => [
                        'data'  => [
                            [
                                'type' => 'webpages',
                                'id'   => '1'
                            ]
                        ],
                        'links' => [
                            'first'   => 'http://api.fullheapdeveloper.local:8080/v1/categories/posts?size=15&included=webpages&page=1',
                            'last'    => 'http://api.fullheapdeveloper.local:8080/v1/categories/posts?size=15&included=webpages&page=1',
                            'related' => 'http://api.fullheapdeveloper.local:8080/v1/categories/posts/webpages'
                        ]
                    ]
                ],
                'links'         => [
                    'self' => 'http://api.fullheapdeveloper.local:8080/v1/categories/posts'
                ]
            ],
            'included' => [
                [
                    'type'       => 'webpages',
                    'id'         => '1',
                    'attributes' => [
                        'created_at'        => '2018-06-18T12:00:30+00:00',
                        'updated_at'        => '2018-06-18T12:00:30+00:00',
                        'created_by'        => 1,
                        'updated_by'        => 1,
                        'category_id'       => 1,
                        'slug'              => 'post-one',
                        'is_live'           => true,
                        'title'             => 'Post One',
                        'modules'           => [],
                        'short_description' => 'Short description of the web page',
                        'image_path_sm'     => '/images/post-one-image-sm',
                        'image_path_md'     => '/images/post-one-image-md',
                        'image_path_lg'     => '/images/post-one-image-lg',
                        'image_path_meta'   => '/images/post-one-image-meta'
                    ],
                    'links'      => [
                        'self' => 'http://api.fullheapdeveloper.local:8080/v1/webpages/post-one?category=posts'
                    ]
                ]
            ]
        ]);
    }

    public function test_all_webpages_get_included_when_related_posts_are_requests_and_user_is_admin()
    {
        $this->actAsAdministrativeUser();

        $response = $this->json('GET', 'v1/categories/posts?included=webpages');
        $response->assertResponseStatus(200);
        $response->seeJsonEquals([
            'data'     => [
                'type'          => 'categories',
                'id'            => '1',
                'attributes'    => [
                    'created_at'      => '2018-06-18T16:00:30+00:00',
                    'updated_at'      => '2018-06-18T17:00:00+00:00',
                    'created_by'      => 1,
                    'last_updated_by' => 1,
                    'name'            => 'Post',
                    'plural_name'     => 'Posts',
                    'slug'            => 'posts'
                ],
                'relationships' => [
                    'webpages' => [
                        'data'  => [
                            [
                                'type' => 'webpages',
                                'id'   => '1'
                            ],
                            [
                                'type' => 'webpages',
                                'id'   => '2'
                            ]
                        ],
                        'links' => [
                            'first'   => 'http://api.fullheapdeveloper.local:8080/v1/categories/posts?size=15&included=webpages&page=1',
                            'last'    => 'http://api.fullheapdeveloper.local:8080/v1/categories/posts?size=15&included=webpages&page=1',
                            'related' => 'http://api.fullheapdeveloper.local:8080/v1/categories/posts/webpages'
                        ]
                    ]
                ],
                'links'         => [
                    'self' => 'http://api.fullheapdeveloper.local:8080/v1/categories/posts'
                ]
            ],
            'included' => [
                [
                    'type'       => 'webpages',
                    'id'         => '1',
                    'attributes' => [
                        'created_at'        => '2018-06-18T12:00:30+00:00',
                        'updated_at'        => '2018-06-18T12:00:30+00:00',
                        'created_by'        => 1,
                        'updated_by'        => 1,
                        'category_id'       => 1,
                        'slug'              => 'post-one',
                        'is_live'           => true,
                        'title'             => 'Post One',
                        'modules'           => [],
                        'short_description' => 'Short description of the web page',
                        'image_path_sm'     => '/images/post-one-image-sm',
                        'image_path_md'     => '/images/post-one-image-md',
                        'image_path_lg'     => '/images/post-one-image-lg',
                        'image_path_meta'   => '/images/post-one-image-meta'
                    ],
                    'links'      => [
                        'self' => 'http://api.fullheapdeveloper.local:8080/v1/webpages/post-one?category=posts'
                    ]
                ],
                [
                    'type'       => 'webpages',
                    'id'         => '2',
                    'attributes' => [
                        'created_at'        => '2018-06-18T12:00:30+00:00',
                        'updated_at'        => '2018-06-18T12:00:35+00:00',
                        'created_by'        => 1,
                        'updated_by'        => 1,
                        'category_id'       => 1,
                        'slug'              => 'post-two',
                        'is_live'           => false,
                        'title'             => 'Post Two',
                        'modules'           => [],
                        'short_description' => 'Short description of the web page',
                        'image_path_sm'     => '/images/post-one-image-sm',
                        'image_path_md'     => '/images/post-one-image-md',
                        'image_path_lg'     => '/images/post-one-image-lg',
                        'image_path_meta'   => '/images/post-one-image-meta'
                    ],
                    'links'      => [
                        'self' => 'http://api.fullheapdeveloper.local:8080/v1/webpages/post-two?category=posts'
                    ]
                ]
            ]
        ]);
    }

    public function test_related_webpages_could_be_retrieved_via_a_relationship_related_link()
    {
        $response = $this->json('GET', 'v1/categories/posts/webpages');
        $response->assertResponseStatus(200);
        $response->seeJsonEquals([
            'data'  => [
                [
                    'type'       => 'webpages',
                    'id'         => '1',
                    'attributes' => [
                        'created_at'        => '2018-06-18T12:00:30+00:00',
                        'updated_at'        => '2018-06-18T12:00:30+00:00',
                        'created_by'        => 1,
                        'updated_by'        => 1,
                        'category_id'       => 1,
                        'slug'              => 'post-one',
                        'is_live'           => true,
                        'title'             => 'Post One',
                        'modules'           => [],
                        'short_description' => 'Short description of the web page',
                        'image_path_sm'     => '/images/post-one-image-sm',
                        'image_path_md'     => '/images/post-one-image-md',
                        'image_path_lg'     => '/images/post-one-image-lg',
                        'image_path_meta'   => '/images/post-one-image-meta'
                    ],
                    'links'      => [
                        'self' => 'http://api.fullheapdeveloper.local:8080/v1/webpages/post-one?category=posts'
                    ]
                ]
            ],
            'links' => [
                'first' => 'http://localhost/v1/categories/posts?size=15&page=1',
                'last'  => 'http://localhost/v1/categories/posts?size=15&page=1'
            ]
        ]);
    }
}
