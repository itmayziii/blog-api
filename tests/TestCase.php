<?php

namespace Tests;

use App\Models\User;

abstract class TestCase extends \Laravel\Lumen\Testing\TestCase
{
    protected const textOver255Characters = 'This text is over 255 characters. This text is over 255 characters. This text is over 255 characters. This text is over 255 characters. This text is over 255 characters. This text is over 255 characters. This text is over 255 characters. This text is over ';

    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__ . '/../bootstrap/app.php';
    }

    public function actAsAdministrativeUser()
    {
        $adminUser = User::find(1);

        $this->actingAs($adminUser);
    }

    public function actAsStandardUser()
    {
        $standardUser = User::find(2);

        $this->actingAs($standardUser);
    }

    protected function assertNotFound(\Laravel\Lumen\Testing\TestCase $response)
    {
        $response->assertResponseStatus(404);
        $response->seeHeader('Content-Type', 'application/vnd.api+json');
        $response->seeJsonEquals([
            'errors' => [
                [
                    'status' => "404",
                    'title'  => 'Not Found'
                ]
            ]
        ]);
    }

    protected function assertUnauthorized(\Laravel\Lumen\Testing\TestCase $response)
    {
        $response->assertResponseStatus(401);
        $response->seeHeader('Content-Type', 'application/vnd.api+json');
        $response->seeJsonEquals([
            'errors' => [
                [
                    'status' => "401",
                    'title'  => 'Unauthorized'
                ]
            ]
        ]);
    }

    protected function assertForbidden(\Laravel\Lumen\Testing\TestCase $response)
    {
        $response->assertResponseStatus(403);
        $response->seeHeader('Content-Type', 'application/vnd.api+json');
        $response->seeJsonEquals(([
            'errors' => [
                [
                    'status' => "403",
                    'title'  => 'Forbidden'
                ]
            ]
        ]));
    }
}
