<?php

use App\Contact;
use App\Http\Controllers\Controller;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var Controller
     */
    private $controller;

    /**
     * @var Contact
     */
    private $contact;

    public function setUp()
    {
        parent::setUp();
        $this->controller = new Controller();

        $this->contact = new Contact();
        $this->contact->first_name = 'Unit';
        $this->contact->last_name = 'Testing';
        $this->contact->comments = 'Some Comments';
        $this->contact->save();
    }

    public function test_respond_created_status_code()
    {
        $response = $this->controller->respondResourceCreated($this->contact);
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function test_respond_created_content()
    {
        $response = $this->controller->respondResourceCreated($this->contact);

        $responseContent = $response->getOriginalContent();
        $responseAttributes = $responseContent['data']['attributes'];

        $this->assertEquals($responseAttributes['first_name'], $this->contact->first_name);
        $this->assertEquals($responseAttributes['last_name'], $this->contact->last_name);
        $this->assertEquals($responseAttributes['comments'], $this->contact->comments);
        $this->assertEquals($responseAttributes['id'], $this->contact->id);
    }

    public function test_respond_created_headers()
    {
        $response = $this->controller->respondResourceCreated($this->contact);
        $this->assertEquals('application/vnd.api+json', $response->headers->get('Content-Type'));
        $this->assertEquals($this->contact->getResourceUrl(), $response->headers->get('Location'));

        $expectedDate = $this->contact->updated_at->format(DateTime::RFC850);
        $actualDate = $response->headers->get('Last-Modified');
        $this->assertEquals($expectedDate, $actualDate);
    }

    public function test_respond_created_links()
    {
        $response = $this->controller->respondResourceCreated($this->contact);
        $responseContent = $response->getOriginalContent();
        $links = $responseContent['data']['links'];

        $expectedSelfUrl = $this->contact->getResourceUrl();
        $actualSelfUrl = $links['self'];
        $this->assertEquals($expectedSelfUrl, $actualSelfUrl);
    }

    public function test_respond_found_status_code()
    {
        $response = $this->controller->respondResourceFound($this->contact);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_respond_found_content()
    {
        $response = $this->controller->respondResourceFound($this->contact);
        $responseContent = $response->getOriginalContent();
        $responseContent['data'];
    }


}