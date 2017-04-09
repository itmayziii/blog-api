<?php

use App\ContactMe;
use App\Http\Controllers\Controller;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ControllerTest extends TestCase
{
    use DatabaseTransactions;

    private $controller;
    private $contactMe;

    public function setUp()
    {
        parent::setUp();
        $this->controller = new Controller();

        $this->contactMe = new ContactMe();
        $this->contactMe->first_name = 'Unit';
        $this->contactMe->last_name = 'Testing';
        $this->contactMe->comments = 'Some Comments';
        $this->contactMe->save();
    }

    public function test_successful_respond_created_status_code()
    {
        $response = $this->controller->respondCreated('contact', $this->contactMe);
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function test_successful_respond_created_content()
    {
        $response = $this->controller->respondCreated('contact', $this->contactMe);
        $appUrl = env('APP_URL');
        $path = $appUrl . '/contact/' . $this->contactMe->id;

        $responseContent = $response->getOriginalContent();
        $actual = $responseContent['data']['created'];

        $this->assertEquals($path, $actual);
    }
}