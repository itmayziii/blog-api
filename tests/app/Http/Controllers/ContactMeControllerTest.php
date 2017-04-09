<?php

use App\ContactMe;
use App\Http\Controllers\ContactMeController;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ContactMeControllerTest extends TestCase
{
    use DatabaseTransactions;

    private $contactMeController;

    public function setUp()
    {
        parent::setUp();
        $this->contactMeController = new ContactMeController();
    }

    public function test_successful_creation()
    {
        $request = Request::create(
            'api/v1/contact',
            'POST',
            ['first-name' => 'Unit', 'last-name' => 'Testing', 'comments' => 'Some Comments']
        );
        $this->contactMeController->store($request);

        $contactMes = ContactMe::where(['first_name' => 'Unit', 'last_name' => 'Testing'])->orderBy('created_at', 'desc')->take(1)->get();
        $contactMe = $contactMes->first();
        $this->assertEquals('Unit', $contactMe->first_name);
        $this->assertEquals('Testing', $contactMe->last_name);
        $this->assertEquals('Some Comments', $contactMe->comments);
    }


    public function test_comments_are_required()
    {
        $request = Request::create(
            'api/v1/contact',
            'POST',
            ['comments' => '']
        );

        $this->expectException(ValidationException::class);
        $this->contactMeController->store($request);
    }
}