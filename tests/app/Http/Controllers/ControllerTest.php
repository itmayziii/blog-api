<?php

use App\Contact;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\MessageBag;
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

    /**
     * @var MessageBag
     */
    private $errorMessageBag;

    public function setUp()
    {
        parent::setUp();
        $this->controller = new Controller();

        $this->contact = new Contact();
        $this->contact->comments = 'Some Comments';
        $this->contact->save();

        $this->errorMessageBag = new MessageBag([
            'first-name' => ['First name should be longer'],
            'last-name'  => ['Last name should be shorter']
        ]);
    }

    /*******************************************************************************************************************
     * Created new resource
     ******************************************************************************************************************/

    public function test_respond_created_status_code()
    {
        $response = $this->controller->respondResourceCreated($this->contact);
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function test_respond_created_content()
    {
        $response = $this->controller->respondResourceCreated($this->contact);
        $responseContent = $response->getOriginalContent();

        $this->assertArrayHasKey('data', $responseContent);
        $this->assertArrayHasKey('id', $responseContent['data']);
        $this->assertArrayHasKey('type', $responseContent['data']);
        $this->assertArrayHasKey('attributes', $responseContent['data']);
    }

    public function test_respond_created_headers()
    {
        $response = $this->controller->respondResourceCreated($this->contact);
        $this->basicCreatedResponseHeaders($response);
    }

    public function test_respond_created_links()
    {
        $response = $this->controller->respondResourceCreated($this->contact);
        $this->selfLink($response);
    }

    /*******************************************************************************************************************
     * Validation failed for resource
     ******************************************************************************************************************/

    public function test_validation_failed_status_code()
    {
        $response = $this->controller->respondValidationFailed($this->errorMessageBag);
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_validation_failed_content()
    {
        $response = $this->controller->respondValidationFailed($this->errorMessageBag);
        $responseContent = $response->getOriginalContent()['errors'];
        $this->assertEquals(422, $responseContent['status']);
        $this->assertEquals('Validation Failed', $responseContent['title']);
        $this->assertEquals('Validation failed for the following input (first-name, last-name), check the source member for more details.', $responseContent['detail']);

        // The actual content of the source will vary heavily on the validation being performed, and therefore should be
        // tested for each specific case, just verifying the source member will work for this test
        $this->assertArrayHasKey('source', $responseContent);
    }

    public function test_validation_failed_headers()
    {
        $response = $this->controller->respondValidationFailed($this->errorMessageBag);
        $this->basicResponseHeaders($response);
    }

    /*******************************************************************************************************************
     * Found a single resource
     ******************************************************************************************************************/

    public function test_respond_found_status_code()
    {
        $response = $this->controller->respondResourceFound($this->contact);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_respond_found_content()
    {
        $response = $this->controller->respondResourceFound($this->contact);
        $responseContent = $response->getOriginalContent();

        $this->assertArrayHasKey('data', $responseContent);
        $this->assertArrayHasKey('id', $responseContent['data']);
        $this->assertArrayHasKey('type', $responseContent['data']);
        $this->assertArrayHasKey('attributes', $responseContent['data']);
    }

    public function test_respond_found_headers()
    {
        $response = $this->controller->respondResourceFound($this->contact);
        $this->basicResponseHeaders($response);
    }

    public function test_respond_found_links()
    {
        $response = $this->controller->respondResourceFound($this->contact);
        $this->selfLink($response);
    }

    /*******************************************************************************************************************
     * Did not find a resource
     ******************************************************************************************************************/

    public function test_respond_not_found_status_code()
    {
        $response = $this->controller->respondResourceNotFound();
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function test_respond_not_found_content()
    {
        $response = $this->controller->respondResourceNotFound();
        $responseContent = $response->getOriginalContent();
        $responseErrors = $responseContent['errors'];

        $this->assertEquals(404, $responseErrors[0]['status']);
        $this->assertEquals('Not Found', $responseErrors[0]['title']);
        $this->assertEquals('Could not find the requested resource.', $responseErrors[0]['detail']);
    }

    public function test_respond_not_found_headers()
    {
        $response = $this->controller->respondResourceNotFound();
        $this->basicResponseHeaders($response);
    }


    private function basicResponseHeaders(Response $response)
    {
        $this->assertEquals('application/vnd.api+json', $response->headers->get('Content-Type'));
    }

    private function basicCreatedResponseHeaders(Response $response)
    {
        $this->assertEquals($this->contact->getResourceUrl(), $response->headers->get('Location'));

        $expectedDate = $this->contact->updated_at->format(DateTime::RFC850);
        $actualDate = $response->headers->get('Last-Modified');
        $this->assertEquals($expectedDate, $actualDate);

        $this->basicResponseHeaders($response);
    }

    private function selfLink(Response $response)
    {
        $responseContent = $response->getOriginalContent();
        $links = $responseContent['data']['links'];

        $expectedSelfUrl = $this->contact->getResourceUrl();
        $actualSelfUrl = $links['self'];
        $this->assertEquals($expectedSelfUrl, $actualSelfUrl);
    }
}