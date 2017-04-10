<?php

use App\Contact;
use App\Http\Controllers\ContactController;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ContactControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var ContactController
     */
    private $contactController;

    public function setUp()
    {
        parent::setUp();
        $this->contactController = new ContactController();
    }

    public function test_successful_creation()
    {
        $request = Request::create(
            'api/v1/contact',
            'POST',
            ['first-name' => 'Unit', 'last-name' => 'Testing', 'comments' => 'Some Comments']
        );
        $this->contactController->store($request);

        $contact = Contact::where(['first_name' => 'Unit', 'last_name' => 'Testing'])->orderBy('created_at', 'desc')->take(1)->get();
        $contact = $contact->first();
        $this->assertEquals('Unit', $contact->first_name);
        $this->assertEquals('Testing', $contact->last_name);
        $this->assertEquals('Some Comments', $contact->comments);
    }


    public function test_comments_are_required()
    {
        $request = Request::create(
            'api/v1/contact',
            'POST',
            ['comments' => '']
        );

        $this->expectException(ValidationException::class);
        $this->contactController->store($request);
    }

    public function test_found()
    {
        $contact = new Contact();
        $contact->comments = 'Test Comments';
        $contact->save();

        $response = $this->contactController->show($contact->id);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_not_found()
    {
        $response = $this->contactController->show(347937472943294);
        $this->assertEquals(404, $response->getStatusCode());
    }
}