<?php

use App\Contact;
use App\Http\Controllers\ContactController;
use Illuminate\Http\Request;
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
        $this->contactController = new ContactController($this->jsonApiMock);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function test_successful_creation()
    {
        $this->jsonApiMock->shouldReceive('respondResourceCreated')->once()->andReturn('Successful Creation');

        $request = Request::create(
            'api/v1/contact',
            'POST',
            ['first-name' => 'Unit', 'last-name' => 'Testing', 'comments' => 'Please test this', 'email' => 'UnitTesting@example.com']
        );
        $response = $this->contactController->store($request);

        $this->assertThat($response, $this->equalTo('Successful Creation'));
    }

    public function test_creation_validation_failed()
    {
        $this->jsonApiMock->shouldReceive('respondValidationFailed')->once()->andReturn('Validation Failed');

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

        $this->assertThat($response, $this->equalTo('Validation Failed'));
    }

    public function test_found()
    {
        $this->jsonApiMock->shouldReceive('respondResourceFound')->once()->andReturn('Contact Found');

        $this->actAsAdministrator();

        $contact = $this->createContact();
        $response = $this->contactController->show($contact->id);

        $this->assertThat($response, $this->equalTo('Contact Found'));
    }

    public function test_not_found()
    {
        $this->jsonApiMock->shouldReceive('respondResourceNotFound')->once()->andReturn('Contact Not Found');

        $this->actAsAdministrator();

        $response = $this->contactController->show(347937472943294);

        $this->assertThat($response, $this->equalTo('Contact Not Found'));
    }

    public function test_finding_authorization()
    {
        $this->jsonApiMock->shouldReceive('respondUnauthorized')->once()->andReturn('Not Authorized to find Contacts');

        $this->actAsStandardUser();

        $contact = $this->createContact();
        $response = $this->contactController->show($contact->id);

        $this->assertThat($response, $this->equalTo('Not Authorized to find Contacts'));
    }

    public function test_listing()
    {
        $this->jsonApiMock->shouldReceive('respondResourcesFound')->once()->andReturn('Contacts Found');

        $this->actAsAdministrator();

        $request = Request::create('v1/contacts');
        $response = $this->contactController->index($request);

        $this->assertThat($response, $this->equalTo('Contacts Found'));
    }

    public function test_list_authorization()
    {
        $this->jsonApiMock->shouldReceive('respondUnauthorized')->once()->andReturn('Not Authorized to list Contacts');

        $request = Request::create('v1/contacts');
        $response = $this->contactController->index($request);

        $this->assertThat($response, $this->equalTo('Not Authorized to list Contacts'));
    }

    private function createContact()
    {
        return $this->keepTryingIntegrityConstraints(function () {
            $contact = new Contact();
            $contact->first_name = 'Unit';
            $contact->last_name = 'Testing';
            $contact->email = 'UnitTesting@example.com';
            $contact->comments = 'Please test this';
            $contact->save();

            return $contact;
        });
    }
}