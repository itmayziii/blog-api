<?php

namespace Tests\Integration;

use Illuminate\Support\Facades\Artisan;
use TestingDatabaseSeeder;
use Tests\TestCase;

class WebPageApiTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        Artisan::call('db:seed', ['--class' => TestingDatabaseSeeder::class]);
    }

    public function test_index_returns_live_web_pages_for_everyone()
    {
        $response = $this->json('GET', 'v1/webpages');

        $response->assertResponseStatus(200);
        $response->seeHeader('Content-Type', 'application/vnd.api+json');
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
                'first' => 'http://localhost/v1/webpages?size=15&page=1',
                'last'  => 'http://localhost/v1/webpages?size=15&page=1'
            ]
        ]);
    }

    public function test_index_returns_all_web_pages_for_authorized_users()
    {
        $this->actAsAdministrativeUser();

        $response = $this->json('GET', 'v1/webpages');

        $response->assertResponseStatus(200);
        $response->seeHeader('Content-Type', 'application/vnd.api+json');
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
                        'image_path_sm'     => '/images/post-two-image-sm',
                        'image_path_md'     => '/images/post-two-image-md',
                        'image_path_lg'     => '/images/post-two-image-lg',
                        'image_path_meta'   => '/images/post-two-image-meta'
                    ],
                    'links'      => [
                        'self' => 'http://api.fullheapdeveloper.local:8080/v1/webpages/post-two?category=posts'
                    ]
                ]
            ],
            'links' => [
                'first' => 'http://localhost/v1/webpages?size=15&page=1',
                'last'  => 'http://localhost/v1/webpages?size=15&page=1'
            ]
        ]);
    }

    public function test_show_returns_web_page_by_slug()
    {
        $response = $this->json('GET', 'v1/webpages/post-one?category=posts');

        $response->assertResponseStatus(200);
        $response->seeHeader('Content-Type', 'application/vnd.api+json');
        $response->seeJsonEquals([
            'data' => [
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
        ]);
    }

    public function test_show_returns_web_page_by_id()
    {
        $response = $this->json('GET', 'v1/webpages/1');

        $response->assertResponseStatus(200);
        $response->seeHeader('Content-Type', 'application/vnd.api+json');
        $response->seeJsonEquals([
            'data' => [
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
        ]);
    }

    public function test_show_responds_not_found()
    {
        $response = $this->json('GET', 'v1/webpages/imaginary-post?category=posts');
        $this->assertNotFound($response);
    }

    public function test_show_responds_unauthenticated()
    {
        $response = $this->json('GET', 'v1/webpages/post-two?category=posts');
        $this->assertUnauthorized($response);
    }

    public function test_show_responds_forbidden()
    {
        $this->actAsStandardUser();
        $response = $this->json('GET', 'v1/webpages/post-two?category=posts');
        $this->assertForbidden($response);
    }

    public function test_show_responds_with_non_live_web_page_if_user_is_authorized()
    {
        $this->actAsAdministrativeUser();
        $response = $this->json('GET', 'v1/webpages/post-two?category=posts');

        $response->assertResponseStatus(200);
        $response->seeHeader('Content-Type', 'application/vnd.api+json');
        $response->seeJsonEquals([
            'data' => [
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
                    'image_path_sm'     => '/images/post-two-image-sm',
                    'image_path_md'     => '/images/post-two-image-md',
                    'image_path_lg'     => '/images/post-two-image-lg',
                    'image_path_meta'   => '/images/post-two-image-meta'
                ],
                'links'      => [
                    'self' => 'http://api.fullheapdeveloper.local:8080/v1/webpages/post-two?category=posts'
                ]
            ]
        ]);
    }

    public function test_store_responds_unauthenticated()
    {
        $response = $this->json('POST', 'v1/webpages');
        $this->assertUnauthorized($response);
    }

    public function test_store_responds_forbidden()
    {
        $this->actAsStandardUser();
        $response = $this->json('POST', 'v1/webpages');
        $this->assertForbidden($response);
    }

    public function test_store_has_required_validation()
    {
        $this->actAsAdministrativeUser();

        $response = $this->json('POST', 'v1/webpages');
        $response->assertResponseStatus(422);
        $response->seeHeader('Content-Type', 'application/vnd.api+json');
        $response->seeJsonEquals([
            'errors' => [
                [
                    'status' => '422',
                    'title'  => 'Unprocessable Entity',
                    'source' => [
                        'category_id' => ['The category id field is required.'],
                        'is_live'     => ['The is live field is required.'],
                        'slug'        => ['The slug field is required.'],
                        'title'       => ['The title field is required.']
                    ]
                ]
            ]
        ]);
    }

    public function test_store_has_other_validation()
    {
        $this->actAsAdministrativeUser();

        $response = $this->json('POST', 'v1/webpages', [
            'category_id'       => 'This is not an integer',
            'slug'              => self::textOver255Characters,
            'is_live'           => 'This is not a boolean',
            'title'             => self::textOver255Characters,
            'modules'           => 'This is not an array',
            'short_description' => self::textOver255Characters . self::textOver255Characters . self::textOver255Characters . self::textOver255Characters,
            'image_path_sm'     => self::textOver255Characters,
            'image_path_md'     => self::textOver255Characters,
            'image_path_lg'     => self::textOver255Characters,
            'image_path_meta'   => self::textOver255Characters
        ]);
        $response->assertResponseStatus(422);
        $response->seeHeader('Content-Type', 'application/vnd.api+json');
        $response->seeJsonEquals([
            'errors' => [
                [
                    'status' => '422',
                    'title'  => 'Unprocessable Entity',
                    'source' => [
                        'category_id'       => ['The category id must be an integer.'],
                        'slug'              => ['The slug may not be greater than 255 characters.', 'The slug may only contain letters, numbers, dashes and underscores.'],
                        'is_live'           => ['The is live field must be true or false.'],
                        'title'             => ['The title may not be greater than 255 characters.'],
                        'modules'           => ['The modules must be an array.'],
                        'short_description' => ['The short description may not be greater than 1000 characters.'],
                        'image_path_sm'     => ['The image path sm may not be greater than 255 characters.'],
                        'image_path_md'     => ['The image path md may not be greater than 255 characters.'],
                        'image_path_lg'     => ['The image path lg may not be greater than 255 characters.'],
                        'image_path_meta'   => ['The image path meta may not be greater than 255 characters.']
                    ]
                ]
            ]
        ]);
    }

    public function test_store_creates_web_page()
    {
        $this->actAsAdministrativeUser();

        $response = $this->json('POST', 'v1/webpages', [
            'category_id'     => 1,
            'slug'            => 'new-slug',
            'is_live'         => true,
            'title'           => 'New Slug Title',
            'modules'         => [],
            'image-path-sm'   => 'https://www.fullheapdeveloper/images/test-sm',
            'image-path-md'   => 'https://www.fullheapdeveloper/images/test-md',
            'image-path-lg'   => 'https://www.fullheapdeveloper/images/test-lg',
            'image-path-meta' => 'https://www.fullheapdeveloper/images/test-meta'
        ]);

        $response->assertResponseStatus(201);
        $response->seeHeader('Content-Type', 'application/vnd.api+json');
    }

    public function test_update_responds_not_found()
    {
        $response = $this->json('PUT', 'v1/webpages/post-that-does-not-exist?category=posts');
        $this->assertNotFound($response);
    }

    public function test_update_responds_unauthorized()
    {
        $response = $this->json('PUT', 'v1/webpages/post-two?category=posts');
        $this->assertUnauthorized($response);
    }

    public function test_update_responds_forbidden()
    {
        $this->actAsStandardUser();
        $response = $this->json('PUT', 'v1/webpages/post-two?category=posts');
        $this->assertForbidden($response);
    }

    public function test_update_responds_validation_failed()
    {
        $this->actAsAdministrativeUser();

        $response = $this->json('PUT', 'v1/webpages/post-two?category=posts');
        $response->assertResponseStatus(422);
        $response->seeHeader('Content-Type', 'application/vnd.api+json');
        $response->seeJsonEquals(([
            'errors' => [
                [
                    'status' => '422',
                    'title'  => 'Unprocessable Entity',
                    'source' => [
                        'category_id' => ['The category id field is required.'],
                        'is_live'     => ['The is live field is required.'],
                        'slug'        => ['The slug field is required.'],
                        'title'       => ['The title field is required.']
                    ]
                ]
            ]
        ]));
    }

    public function test_update_updates_web_page()
    {
        $this->actAsAdministrativeUser();

        $response = $this->json('PUT', 'v1/webpages/post-two?category=posts', [
            'category_id'       => 1,
            'slug'              => 'post-two-updated',
            'is_live'           => false,
            'title'             => 'Post Two title',
            'modules'           => [],
            'short_description' => 'Just a little description that was updated',
            'image_path_sm'     => 'https://www.fullheapdeveloper/images/test-sm',
            'image_path_md'     => 'https://www.fullheapdeveloper/images/test-md',
            'image_path_lg'     => 'https://www.fullheapdeveloper/images/test-lg',
            'image_path_meta'   => 'https://www.fullheapdeveloper/images/test-meta'
        ]);
        $response->assertResponseStatus(200);
        $response->seeHeader('Content-Type', 'application/vnd.api+json');
    }

    public function test_delete_responds_not_found()
    {
        $response = $this->json('DELETE', 'v1/webpages/a-post-that-does-not-exist');
        $this->assertNotFound($response);
    }

    public function test_delete_responds_unauthorized()
    {
        $response = $this->json('DELETE', 'v1/webpages/2');
        $this->assertUnauthorized($response);
    }

    public function test_delete_responds_forbidden()
    {
        $this->actAsStandardUser();
        $response = $this->json('DELETE', 'v1/webpages/2');
        $this->assertForbidden($response);
    }

    public function test_delete_deletes_web_page()
    {
        $this->actAsAdministrativeUser();
        $response = $this->json('DELETE', 'v1/webpages/2');
        $response->assertResponseStatus(204);
    }
}
