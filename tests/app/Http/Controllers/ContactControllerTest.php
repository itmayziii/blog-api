<?php

use App\Contact;
use App\Http\Controllers\ContactController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
            ['first-name' => 'Unit', 'last-name' => 'Testing', 'comments' => 'Please test this', 'email' => 'UnitTesting@example.com']
        );
        $response = $this->contactController->store($request);

        $contact = Contact::where(['first_name' => 'Unit', 'last_name' => 'Testing'])->orderBy('created_at', 'desc')->take(1)->get();
        $contact = $contact->first();

        $this->assertEquals('Unit', $contact->first_name);
        $this->assertEquals('Testing', $contact->last_name);
        $this->assertEquals('Please test this', $contact->comments);
        $this->assertEquals('UnitTesting@example.com', $contact->email);

        $this->verifyResponseData($response, $contact);
    }

    public function test_creation_validation_failed()
    {
        $request = Request::create(
            'v1/contacts',
            'POST',
            [
                // testing first / last name, and email can't be over 100 characters
                'first-name' => 'aaaaabbbbbcccccdddddeeeeefffffggggghhhhhiiiiijjjjjjkkkkkllllllmmmmmmnnnnnnooooooppppppqqqqqqrrrrrrssssssttttttuuuuuuvvvvvvvvwwwwwwwxxxxxyyyyyzzzzz',
                'last-name'  => 'aaaaabbbbbcccccdddddeeeeefffffggggghhhhhiiiiijjjjjjkkkkkllllllmmmmmmnnnnnnooooooppppppqqqqqqrrrrrrssssssttttttuuuuuuvvvvvvvvwwwwwwwxxxxxyyyyyzzzzz',
                'email'      => 'aaaaabbbbbcccccdddddeeeeefffffggggghhhhhiiiiijjjjjjkkkkkllllllmmmmmmnnnnnnooooooppppppqqqqqqrrrrrrssssssttttttuuuuuuvvvvvvvvwwwwwwwxxxxxyyyyyzzzzz',
                'comments'   => '']
        );

        $response = $this->contactController->store($request);
        $responseContent = $response->getOriginalContent()['errors'];

        $this->assertArrayHasKey('first-name', $responseContent['source']);
        $this->assertContains('The first-name may not be greater than 100 characters.', $responseContent['source']['first-name']);

        $this->assertArrayHasKey('last-name', $responseContent['source']);
        $this->assertContains('The last-name may not be greater than 100 characters.', $responseContent['source']['last-name']);

        $this->assertArrayHasKey('email', $responseContent['source']);
        $this->assertContains('The email may not be greater than 100 characters.', $responseContent['source']['email']);

        $this->assertArrayHasKey('comments', $responseContent['source']);
        $this->assertContains('The comments field is required.', $responseContent['source']['comments']);
    }

    public function test_found()
    {
        $this->actAsAdministrator();

        $contact = $this->createContact();
        $response = $this->contactController->show($contact->id);
        $this->verifyResponseData($response, $contact);
    }

    public function test_not_found()
    {
        $this->actAsAdministrator();
        $response = $this->contactController->show(347937472943294);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function test_finding_authorization()
    {
        $this->actAsStandardUser();

        $contact = $this->createContact();

        $response = $this->contactController->show($contact->id);
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_listing()
    {

    }

    private function verifyResponseData(Response $response, Contact $contact)
    {
        $responseContent = $response->getOriginalContent()['data'];

        $this->assertEquals($contact->id, $responseContent['id']);
        $this->assertEquals($contact->getResourceName(), $responseContent['type']);
        $this->assertEquals('Unit', $responseContent['attributes']['first_name']);
        $this->assertEquals('Testing', $responseContent['attributes']['last_name']);
        $this->assertEquals('UnitTesting@example.com', $responseContent['attributes']['email']);
        $this->assertEquals('Please test this', $responseContent['attributes']['comments']);
    }

    private function createContact()
    {
        $contact = new Contact();
        $contact->first_name = 'Unit';
        $contact->last_name = 'Testing';
        $contact->email = 'UnitTesting@example.com';
        $contact->comments = 'Please test this';
        $contact->save();

        return $contact;
    }
}