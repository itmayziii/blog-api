<?php

use App\User;
use itmayziii\Laravel\JsonApi;
use Laravel\Lumen\Testing\DatabaseTransactions;

abstract class TestCase extends Laravel\Lumen\Testing\TestCase
{
    use DatabaseTransactions;

    /**
     * @var \Mockery\Mock|JsonApi
     */
    protected $jsonApiMock;

    public function setUp()
    {
        parent::setUp();
        $this->jsonApiMock = $this->jsonApiMock = \Mockery::mock(JsonApi::class)->shouldDeferMissing();
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

    protected function actAsAdministrator()
    {
        $admin = User::create([
            'first_name' => 'Tommy',
            'last_name'  => 'May',
            'email'      => 'tommymay37@gmail.com'
        ]);
        $admin->setAttribute('role', 'Administrator');
        $admin->save();

        $this->actingAs($admin);
    }

    protected function actAsStandardUser()
    {
        $user = User::create([
            'first_name' => 'John',
            'last_name'  => 'Smith',
            'email'      => 'johnsmith@example.com'
        ]);

        $this->actingAs($user);
    }
}

