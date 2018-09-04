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
                        'created_at'      => '2018-06-18T12:00:30+00:00',
                        'updated_at'      => '2018-06-18T12:00:30+00:00',
                        'created_by'      => 1,
                        'updated_by'      => 1,
                        'category_id'     => 1,
                        'path'            => '/posts/post-one',
                        'is_live'         => true,
                        'title'           => 'Post One',
                        'content'         => 'Some really long content',
                        'preview'         => 'Some really short content',
                        'image_path_sm'   => '/images/post-one-image-sm',
                        'image_path_md'   => '/images/post-one-image-md',
                        'image_path_lg'   => '/images/post-one-image-lg',
                        'image_path_meta' => '/images/post-one-image-meta'
                    ],
                    'links'      => [
                        'self' => 'http://api.fullheapdeveloper.local:8080/v1/webpages/posts/post-one'
                    ]
                ]
            ],
            'links' => [
                'first' => 'http://localhost/v1/webpages?page=1',
                'last'  => 'http://localhost/v1/webpages?page=1'
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
                        'created_at'      => '2018-06-18T12:00:30+00:00',
                        'updated_at'      => '2018-06-18T12:00:30+00:00',
                        'created_by'      => 1,
                        'updated_by'      => 1,
                        'category_id'     => 1,
                        'path'            => '/posts/post-one',
                        'is_live'         => true,
                        'title'           => 'Post One',
                        'content'         => 'Some really long content',
                        'preview'         => 'Some really short content',
                        'image_path_sm'   => '/images/post-one-image-sm',
                        'image_path_md'   => '/images/post-one-image-md',
                        'image_path_lg'   => '/images/post-one-image-lg',
                        'image_path_meta' => '/images/post-one-image-meta'
                    ],
                    'links'      => [
                        'self' => 'http://api.fullheapdeveloper.local:8080/v1/webpages/posts/post-one'
                    ]
                ],
                [
                    'type'       => 'webpages',
                    'id'         => '2',
                    'attributes' => [
                        'created_at'      => '2018-06-18T12:00:30+00:00',
                        'updated_at'      => '2018-06-18T12:00:35+00:00',
                        'created_by'      => 1,
                        'updated_by'      => 1,
                        'category_id'     => 1,
                        'path'            => '/posts/post-two',
                        'is_live'         => false,
                        'title'           => 'Post Two',
                        'content'         => 'Some really long content',
                        'preview'         => 'Some really short content',
                        'image_path_sm'   => '/images/post-one-image-sm',
                        'image_path_md'   => '/images/post-one-image-md',
                        'image_path_lg'   => '/images/post-one-image-lg',
                        'image_path_meta' => '/images/post-one-image-meta'
                    ],
                    'links'      => [
                        'self' => 'http://api.fullheapdeveloper.local:8080/v1/webpages/posts/post-two'
                    ]
                ]
            ],
            'links' => [
                'first' => 'http://localhost/v1/webpages?page=1',
                'last'  => 'http://localhost/v1/webpages?page=1'
            ]
        ]);
    }

    public function test_show_returns_webpage()
    {
        $response = $this->json('GET', 'v1/webpages/posts/post-one');

        $response->assertResponseStatus(200);
        $response->seeHeader('Content-Type', 'application/vnd.api+json');
        $response->seeJsonEquals([
            'data' => [
                'type'       => 'webpages',
                'id'         => '1',
                'attributes' => [
                    'created_at'      => '2018-06-18T12:00:30+00:00',
                    'updated_at'      => '2018-06-18T12:00:30+00:00',
                    'created_by'      => 1,
                    'updated_by'      => 1,
                    'category_id'     => 1,
                    'path'            => '/posts/post-one',
                    'is_live'         => true,
                    'title'           => 'Post One',
                    'content'         => 'Some really long content',
                    'preview'         => 'Some really short content',
                    'image_path_sm'   => '/images/post-one-image-sm',
                    'image_path_md'   => '/images/post-one-image-md',
                    'image_path_lg'   => '/images/post-one-image-lg',
                    'image_path_meta' => '/images/post-one-image-meta'
                ],
                'links'      => [
                    'self' => 'http://api.fullheapdeveloper.local:8080/v1/webpages/posts/post-one'
                ]
            ]
        ]);
    }

    public function test_show_responds_not_found()
    {
        $response = $this->json('GET', 'v1/webpages/imaginary-post');
        $this->assertNotFound($response);
    }

    public function test_show_responds_unauthenticated()
    {
        $response = $this->json('GET', 'v1/webpages/posts/post-two');
        $this->assertUnauthorized($response);
    }

    public function test_show_responds_forbidden()
    {
        $this->actAsStandardUser();
        $response = $this->json('GET', 'v1/webpages/posts/post-two');
        $this->assertForbidden($response);
    }

    public function test_show_responds_with_non_live_web_page_if_user_authorized()
    {
        $this->actAsAdministrativeUser();
        $response = $this->json('GET', 'v1/webpages/posts/post-two');

        $response->assertResponseStatus(200);
        $response->seeHeader('Content-Type', 'application/vnd.api+json');
        $response->seeJsonEquals([
            'data' => [
                'type'       => 'webpages',
                'id'         => '2',
                'attributes' => [
                    'created_at'      => '2018-06-18T12:00:30+00:00',
                    'updated_at'      => '2018-06-18T12:00:35+00:00',
                    'created_by'      => 1,
                    'updated_by'      => 1,
                    'category_id'     => 1,
                    'path'            => '/posts/post-two',
                    'is_live'         => false,
                    'title'           => 'Post Two',
                    'content'         => 'Some really long content',
                    'preview'         => 'Some really short content',
                    'image_path_sm'   => '/images/post-one-image-sm',
                    'image_path_md'   => '/images/post-one-image-md',
                    'image_path_lg'   => '/images/post-one-image-lg',
                    'image_path_meta' => '/images/post-one-image-meta'
                ],
                'links'      => [
                    'self' => 'http://api.fullheapdeveloper.local:8080/v1/webpages/posts/post-two'
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

    public function test_store_has_validation()
    {
        $this->actAsAdministrativeUser();

        $response = $this->json('POST', 'v1/webpages');
        $response->assertResponseStatus(422);
        $response->seeHeader('Content-Type', 'application/vnd.api+json');
        $response->seeJsonEquals(([
            'errors' => [
                [
                    'detail' => "The category id field is required.",
                    'status' => '422',
                    'title'  => 'Unprocessable Entity'
                ],
                [
                    'detail' => "The path field is required.",
                    'status' => '422',
                    'title'  => 'Unprocessable Entity'
                ],
                [
                    'detail' => "The is live field is required.",
                    'status' => '422',
                    'title'  => 'Unprocessable Entity'
                ],
                [
                    'detail' => "The title field is required.",
                    'status' => '422',
                    'title'  => 'Unprocessable Entity'
                ]
            ]
        ]));
    }

    public function test_store_creates_web_page()
    {
        $this->actAsAdministrativeUser();

        $response = $this->json('POST', 'v1/webpages', [
            'category_id'     => 1,
            'path'            => '/posts/test-post-creation',
            'is_live'         => true,
            'title'           => 'A title',
            'content'         => 'Some test content',
            'preview'         => 'Just a preview',
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
        $response = $this->json('PUT', 'v1/webpages/post-that-does-not-exist');
        $this->assertNotFound($response);
    }

    public function test_update_responds_unauthorized()
    {
        $response = $this->json('PUT', 'v1/webpages/2');
        $this->assertUnauthorized($response);
    }

    public function test_update_responds_forbidden()
    {
        $this->actAsStandardUser();
        $response = $this->json('PUT', 'v1/webpages/2');
        $this->assertForbidden($response);
    }

    public function test_update_responds_validation_failed()
    {
        $this->actAsAdministrativeUser();

        $response = $this->json('PUT', 'v1/webpages/2');
        $response->assertResponseStatus(422);
        $response->seeHeader('Content-Type', 'application/vnd.api+json');
        $response->seeJsonEquals(([
            'errors' => [
                [
                    'detail' => "The category id field is required.",
                    'status' => '422',
                    'title'  => 'Unprocessable Entity'
                ],
                [
                    'detail' => "The path field is required.",
                    'status' => '422',
                    'title'  => 'Unprocessable Entity'
                ],
                [
                    'detail' => "The is live field is required.",
                    'status' => '422',
                    'title'  => 'Unprocessable Entity'
                ],
                [
                    'detail' => "The title field is required.",
                    'status' => '422',
                    'title'  => 'Unprocessable Entity'
                ]
            ]
        ]));
    }

    public function test_update_updates_web_page()
    {
        $this->actAsAdministrativeUser();

        $response = $this->json('PUT', 'v1/webpages/2', [
            'category_id'     => 1,
            'path'            => '/posts/test-post-update',
            'is_live'         => false,
            'title'           => 'A title',
            'content'         => 'Some test content',
            'preview'         => 'Just a preview',
            'image-path-sm'   => 'https://www.fullheapdeveloper/images/test-sm',
            'image-path-md'   => 'https://www.fullheapdeveloper/images/test-md',
            'image-path-lg'   => 'https://www.fullheapdeveloper/images/test-lg',
            'image-path-meta' => 'https://www.fullheapdeveloper/images/test-meta'
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
        $this->assertResponseStatus(204);
    }
}
