<?php

namespace Tests;

use App\User;

abstract class TestCase extends \Laravel\Lumen\Testing\TestCase
{
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
}

