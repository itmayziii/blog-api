<?php

namespace Tests\Integration;

use Illuminate\Support\Facades\Artisan;
use TestingDatabaseSeeder;
use Tests\TestCase;

class ContactApiTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        Artisan::call('db:seed', ['--class' => TestingDatabaseSeeder::class]);
    }

    public function test_show_returns_not_found_if_id_does_not_exist()
    {
        $response = $this->get('v1/contacts/9437529754');
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

    public function test_show_responds_unauthenticated_if_user_is_not_logged_in()
    {
        $response = $this->get('v1/contacts/1');
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

    public function test_show_responds_forbidden_if_user_is_not_admin()
    {
        $this->actAsStandardUser();

        $response = $this->get('v1/contacts/1');
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

    public function test_show_responds_with_contact()
    {
        $this->actAsAdministrativeUser();

        $response = $this->get('v1/contacts/1');
        $response->assertResponseStatus(200);
        $response->seeJsonEquals([
            'data' => [
                'id'         => '1',
                'type'       => 'contacts',
                'attributes' => [
                    'created_at' => '2018-06-18T12:00:30+00:00',
                    'updated_at' => '2018-06-18T12:00:30+00:00',
                    'first_name' => 'John',
                    'last_name'  => 'Smith',
                    'email'      => 'johnsmith@example.com',
                    'comments'   => 'Wow what an amazing website :)'
                ],
                'links'      => [
                    'self' => 'http://api.fullheapdeveloper.local:8080/v1/contacts/1'
                ]
            ]
        ]);
    }

    public function test_index_responds_unauthenticated_if_user_is_not_logged_in()
    {
        $response = $this->get('v1/contacts');
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

        $response = $this->get('v1/contacts');
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

    public function test_index_responds_with_contacts()
    {
        $this->actAsAdministrativeUser();

        $response = $this->get('v1/contacts');
        $response->assertResponseStatus(200);
        $response->seeJsonEquals([
            'data'  => [
                [
                    'id'         => '1',
                    'type'       => 'contacts',
                    'attributes' => [
                        'created_at' => '2018-06-18T12:00:30+00:00',
                        'updated_at' => '2018-06-18T12:00:30+00:00',
                        'first_name' => 'John',
                        'last_name'  => 'Smith',
                        'email'      => 'johnsmith@example.com',
                        'comments'   => 'Wow what an amazing website :)'
                    ],
                    'links'      => [
                        'self' => 'http://api.fullheapdeveloper.local:8080/v1/contacts/1'
                    ]
                ]
            ],
            'links' => [
                'first' => 'http://localhost/v1/contacts?page=1',
                'last'  => 'http://localhost/v1/contacts?page=1'
            ]
        ]);
    }

    public function test_store_responds_required_validation_failed()
    {
        $this->actAsAdministrativeUser();

        $response = $this->post('v1/contacts');
        $response->assertResponseStatus(422);
        $response->seeJsonEquals([
            'errors' => [
                [
                    'status' => '422',
                    'title'  => 'Unprocessable Entity',
                    'source' => [
                        'first_name' => ['The first name field is required.'],
                        'last_name'  => ['The last name field is required.'],
                        'email'      => ['The email field is required.'],
                        'comments'   => ['The comments field is required.'],
                    ]
                ]
            ]
        ]);
    }

    public function test_store_responds_other_validation_failed()
    {
        $this->actAsAdministrativeUser();

        $response = $this->post('v1/contacts', [
            'first_name' => self::textOver255Characters,
            'last_name'  => self::textOver255Characters,
            'email'      => self::textOver255Characters,
            'comments'   => self::textOver255Characters . self::textOver255Characters . self::textOver255Characters . self::textOver255Characters
        ]);
        $response->assertResponseStatus(422);
        $response->seeJsonEquals([
            'errors' => [
                [
                    'status' => '422',
                    'title'  => 'Unprocessable Entity',
                    'source' => [
                        'first_name' => ['The first name may not be greater than 255 characters.'],
                        'last_name'  => ['The last name may not be greater than 255 characters.'],
                        'email'      => ['The email may not be greater than 255 characters.', 'The email must be a valid email address.'],
                        'comments'   => ['The comments may not be greater than 1000 characters.'],
                    ]
                ]
            ]
        ]);
    }

    public function test_store_creates_contact_for_everyone()
    {
        $response = $this->post('v1/contacts', [
            'first_name' => 'Jane',
            'last_name'  => 'Doe',
            'email'      => 'janedoe@example.com',
            'comments'   => 'Hello from Jane Doe'
        ]);
        $response->assertResponseStatus(201);
    }

    public function test_update_responds_not_found()
    {
        $response = $this->put('v1/contacts/2');
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

    public function test_delete_responds_not_found()
    {
        $response = $this->delete('v1/contacts/2');
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
}
