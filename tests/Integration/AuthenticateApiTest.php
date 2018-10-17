<?php

namespace Tests\Integration;

use Illuminate\Support\Facades\Artisan;
use TestingDatabaseSeeder;
use Tests\TestCase;

class AuthenticateApiTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        Artisan::call('db:seed', ['--class' => TestingDatabaseSeeder::class]);
    }

    public function test_authenticate_responds_with_error_if_type_and_value_are_not_set()
    {
        $response = $this->json('POST', 'v1/token');
        $response->assertResponseStatus(400);
        $response->seeJsonEquals([
            'errors' => [
                [
                    'status' => '400',
                    'title'  => 'Bad Request',
                    'detail' => 'Authorization header must have a type and value defined.'
                ]
            ]
        ]);
    }

    public function test_authentication_responds_with_error_if_basic_auth_is_invalid()
    {
        $response = $this->json('POST', 'v1/token', [], [
            'Authorization' => 'Not-Basic Apples'
        ]);
        $response->assertResponseStatus(400);
        $response->seeJsonEquals([
            'errors' => [
                [
                    'status' => '400',
                    'title'  => 'Bad Request',
                    'detail' => 'Authorization header must be of "Basic" type.'
                ],
                [
                    'status' => '400',
                    'title'  => 'Bad Request',
                    'detail' => 'Authorization header value has an invalid username:password format.'
                ],
            ]
        ]);
    }

    public function test_authentication_responds_not_found_if_user_email_does_not_exist()
    {
        $response = $this->json('POST', 'v1/token', [], [
            'Authorization' => 'Basic bm90cmVhbEBleGFtcGxlLmNvbTpUaGlzUGFzczE='
        ]);
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

    public function test_authentication_responds_unauthorized_if_password_is_incorrect()
    {
        $response = $this->json('POST', 'v1/token', [], [
            'Authorization' => 'Basic dGVzdHVzZXIxQGV4YW1wbGUuY29tOkZha2VQYXNzd29yZA=='
        ]);
        $response->assertResponseStatus(401);
        $response->seeJsonEquals([
            'errors' => [
                [
                    'status' => '401',
                    'title'  => 'Unauthorized',
                ]
            ]
        ]);
    }

    public function test_authentication_responds_with_user_on_success()
    {
        $response = $this->json('POST', 'v1/token', [], [
            'Authorization' => 'Basic dGVzdHVzZXIxQGV4YW1wbGUuY29tOlRoaXNQYXNzMQ=='
        ]);
        $response->assertResponseStatus(201);
        $response->seeJson([
            'type' => 'users',
            'id'   => '2'
        ]);
    }

    public function test_validateToken_responds_unauthenticated_without_header_or_cookie_set()
    {
        $response = $this->json('PUT', 'v1/token');
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

    public function test_validateToken_responds_unauthorized_if_token_does_not_match()
    {
        $response = $this->json('PUT', 'v1/token', [], [
            'API-Token' => 'not-a-real-api-token'
        ]);
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

    public function test_validateToken_responds_with_user_if_token_is_valid()
    {
        $validateTokenResponse = $this->json('POST', 'v1/token', [], [
            'Authorization' => 'Basic dGVzdHVzZXIxQGV4YW1wbGUuY29tOlRoaXNQYXNzMQ=='
        ]);
        $validateTokenResponseContents = json_decode($this->response->getContent());
        $response = $this->json('PUT', 'v1/token', [], [
            'API-Token' => $validateTokenResponseContents->data->attributes->api_token
        ]);
        $response->assertResponseStatus(200);
        $response->seeJson([
            'type' => 'users',
            'id'   => '2'
        ]);
    }

    public function test_logout_responds_unauthorized_without_header_or_cookie_set()
    {
        $response = $this->json('DELETE', 'v1/token');
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

    public function test_logout_responds_unauthorized_if_token_does_not_match()
    {
        $response = $this->json('PUT', 'v1/token', [], [
            'API-Token' => 'not-a-real-token'
        ]);
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

    public function test_logout_deletes_token_from_user()
    {
        $deleteAuthResponse = $this->json('POST', 'v1/token', [], [
            'Authorization' => 'Basic dGVzdHVzZXIxQGV4YW1wbGUuY29tOlRoaXNQYXNzMQ=='
        ]);
        $deleteResponseContents = json_decode($this->response->getContent());
        $response = $this->json('DELETE', 'v1/token', [], [
            'API-Token' => $deleteResponseContents->data->attributes->api_token
        ]);
        $response->assertResponseStatus(204);
    }
}
