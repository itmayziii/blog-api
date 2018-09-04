<?php

namespace Tests\Integration\Http\Controllers;

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

    public function test_index_returns_live_posts_for_everyone()
    {
        $response = $this->json('GET', 'v1/posts');

        $response->assertResponseStatus(200);
        $response->seeHeader('Content-Type', 'application/vnd.api+json');
        $response->seeJsonEquals([
            'data'  => [
                [
                    'type'       => 'posts',
                    'id'         => '1',
                    'attributes' => [
                        'createdAt'     => '2018-06-18T12:00:30+00:00',
                        'updatedAt'     => '2018-06-18T12:00:30+00:00',
                        'status'        => 'live',
                        'title'         => 'Post One',
                        'slug'          => 'post-one',
                        'content'       => 'Some really long content',
                        'preview'       => 'Some really short content',
                        'imagePathSm'   => '/images/post-one-image-sm',
                        'imagePathMd'   => '/images/post-one-image-md',
                        'imagePathLg'   => '/images/post-one-image-lg',
                        'imagePathMeta' => '/images/post-one-image-meta',
                        'categoryId'    => 1,
                        'userId'        => 1
                    ],
                    'links'      => [
                        'self' => 'http://api.fullheapdeveloper.local:8080/v1/posts/post-one'
                    ]
                ]
            ],
            'links' => [
                'first' => 'http://localhost/v1/posts?page=1',
                'last'  => 'http://localhost/v1/posts?page=1'
            ]
        ]);
    }

    public function test_index_returns_all_posts_for_authorized_users()
    {
        $this->actAsAdministrativeUser();

        $response = $this->json('GET', 'v1/posts');

        $response->assertResponseStatus(200);
        $response->seeHeader('Content-Type', 'application/vnd.api+json');
        $response->seeJsonEquals([
            'data'  => [
                [
                    'type'       => 'posts',
                    'id'         => '1',
                    'attributes' => [
                        'createdAt'     => '2018-06-18T12:00:30+00:00',
                        'updatedAt'     => '2018-06-18T12:00:30+00:00',
                        'status'        => 'live',
                        'title'         => 'Post One',
                        'slug'          => 'post-one',
                        'content'       => 'Some really long content',
                        'preview'       => 'Some really short content',
                        'imagePathSm'   => '/images/post-one-image-sm',
                        'imagePathMd'   => '/images/post-one-image-md',
                        'imagePathLg'   => '/images/post-one-image-lg',
                        'imagePathMeta' => '/images/post-one-image-meta',
                        'categoryId'    => 1,
                        'userId'        => 1
                    ],
                    'links'      => [
                        'self' => 'http://api.fullheapdeveloper.local:8080/v1/posts/post-one'
                    ]
                ],
                [
                    'type'       => 'posts',
                    'id'         => '2',
                    'attributes' => [
                        'createdAt'     => '2018-06-18T12:00:30+00:00',
                        'updatedAt'     => '2018-06-18T12:00:30+00:00',
                        'status'        => 'draft',
                        'title'         => 'Post Two',
                        'slug'          => 'post-two',
                        'content'       => 'Some really long content',
                        'preview'       => 'Some really short content',
                        'imagePathSm'   => '/images/post-one-image-sm',
                        'imagePathMd'   => '/images/post-one-image-md',
                        'imagePathLg'   => '/images/post-one-image-lg',
                        'imagePathMeta' => '/images/post-one-image-meta',
                        'categoryId'    => 1,
                        'userId'        => 1
                    ],
                    'links'      => [
                        'self' => 'http://api.fullheapdeveloper.local:8080/v1/posts/post-two'
                    ]
                ]
            ],
            'links' => [
                'first' => 'http://localhost/v1/posts?page=1',
                'last'  => 'http://localhost/v1/posts?page=1'
            ]
        ]);
    }

    public function test_show_returns_post()
    {
        $response = $this->json('GET', 'v1/posts/post-one');

        $response->assertResponseStatus(200);
        $response->seeHeader('Content-Type', 'application/vnd.api+json');
        $response->seeJsonEquals([
            'data' => [
                'type'       => 'posts',
                'id'         => '1',
                'attributes' => [
                    'createdAt'     => '2018-06-18T12:00:30+00:00',
                    'updatedAt'     => '2018-06-18T12:00:30+00:00',
                    'status'        => 'live',
                    'title'         => 'Post One',
                    'slug'          => 'post-one',
                    'content'       => 'Some really long content',
                    'preview'       => 'Some really short content',
                    'imagePathSm'   => '/images/post-one-image-sm',
                    'imagePathMd'   => '/images/post-one-image-md',
                    'imagePathLg'   => '/images/post-one-image-lg',
                    'imagePathMeta' => '/images/post-one-image-meta',
                    'categoryId'    => 1,
                    'userId'        => 1
                ],
                'links'      => [
                    'self' => 'http://api.fullheapdeveloper.local:8080/v1/posts/post-one'
                ]
            ]
        ]);
    }

    public function test_show_responds_not_found()
    {
        $response = $this->json('GET', 'v1/posts/imaginary-post');
        $this->assertNotFound($response);
    }

    public function test_show_responds_unauthenticated()
    {
        $response = $this->json('GET', 'v1/posts/post-two');
        $this->assertUnauthorized($response);
    }

    public function test_show_responds_forbidden()
    {
        $this->actAsStandardUser();
        $response = $this->json('GET', 'v1/posts/post-two');
        $this->assertForbidden($response);
    }

    public function test_show_responds_with_resource()
    {
        $this->actAsAdministrativeUser();
        $response = $this->json('GET', 'v1/posts/post-two');

        $response->assertResponseStatus(200);
        $response->seeHeader('Content-Type', 'application/vnd.api+json');
        $response->seeJsonEquals([
            'data' => [
                'type'       => 'posts',
                'id'         => '2',
                'attributes' => [
                    'createdAt'     => '2018-06-18T12:00:30+00:00',
                    'updatedAt'     => '2018-06-18T12:00:30+00:00',
                    'status'        => 'draft',
                    'title'         => 'Post Two',
                    'slug'          => 'post-two',
                    'content'       => 'Some really long content',
                    'preview'       => 'Some really short content',
                    'imagePathSm'   => '/images/post-one-image-sm',
                    'imagePathMd'   => '/images/post-one-image-md',
                    'imagePathLg'   => '/images/post-one-image-lg',
                    'imagePathMeta' => '/images/post-one-image-meta',
                    'categoryId'    => 1,
                    'userId'        => 1
                ],
                'links'      => [
                    'self' => 'http://api.fullheapdeveloper.local:8080/v1/posts/post-two'
                ]
            ]
        ]);
    }

    public function test_store_responds_unauthenticated()
    {
        $response = $this->json('POST', 'v1/posts');
        $this->assertUnauthorized($response);
    }

    public function test_store_responds_forbidden()
    {
        $this->actAsStandardUser();
        $response = $this->json('POST', 'v1/posts');
        $this->assertForbidden($response);
    }

    public function test_store_has_validation()
    {
        $this->actAsAdministrativeUser();

        $response = $this->json('POST', 'v1/posts');
        $response->assertResponseStatus(422);
        $response->seeHeader('Content-Type', 'application/vnd.api+json');
        $response->seeJsonEquals(([
            'errors' => [
                [
                    'detail' => "The category-id field is required.",
                    'status' => '422',
                    'title'  => 'Unprocessable Entity'
                ],
                [
                    'detail' => "The slug field is required.",
                    'status' => '422',
                    'title'  => 'Unprocessable Entity'
                ],
                [
                    'detail' => "The status field is required.",
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

    public function test_store_creates_post()
    {
        $this->actAsAdministrativeUser();

        $response = $this->json('POST', 'v1/posts', [
            'category-id'     => 1,
            'slug'            => 'test-post-creation',
            'status'          => 'live',
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
        $response = $this->json('PUT', 'v1/posts/post-that-does-not-exist');
        $this->assertNotFound($response);
    }

    public function test_update_responds_unauthorized()
    {
        $response = $this->json('PUT', 'v1/posts/post-two');
        $this->assertUnauthorized($response);
    }

    public function test_update_responds_forbidden()
    {
        $this->actAsStandardUser();
        $response = $this->json('PUT', 'v1/posts/post-two');
        $this->assertForbidden($response);
    }

    public function test_update_responds_validation_failed()
    {
        $this->actAsAdministrativeUser();

        $response = $this->json('PUT', 'v1/posts/post-two');
        $response->assertResponseStatus(422);
        $response->seeHeader('Content-Type', 'application/vnd.api+json');
        $response->seeJsonEquals(([
            'errors' => [
                [
                    'detail' => "The category-id field is required.",
                    'status' => '422',
                    'title'  => 'Unprocessable Entity'
                ],
                [
                    'detail' => "The slug field is required.",
                    'status' => '422',
                    'title'  => 'Unprocessable Entity'
                ],
                [
                    'detail' => "The status field is required.",
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

    public function test_update_updates_post()
    {
        $this->actAsAdministrativeUser();

        $response = $this->json('PUT', 'v1/posts/post-two', [
            'category-id'     => 1,
            'slug'            => 'test-post-update',
            'status'          => 'live',
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
        $response = $this->json('DELETE', 'v1/posts/a-post-that-does-not-exist');
        $this->assertNotFound($response);
    }

    public function test_delete_responds_unauthorized()
    {
        $response = $this->json('DELETE', 'v1/posts/post-two');
        $this->assertUnauthorized($response);
    }

    public function test_delete_responds_forbidden()
    {
        $this->actAsStandardUser();
        $response = $this->json('DELETE', 'v1/posts/post-two');
        $this->assertForbidden($response);
    }
}
