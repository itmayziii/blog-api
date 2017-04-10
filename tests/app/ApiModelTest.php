<?php

use App\ApiModel;
use App\Contact;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ApiModelTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var ApiModel
     */
    private $apiModel;

    public function setUp()
    {
        $this->apiModel = new ConcreteApiModel();
    }

    public function test_get_resourcse_url()
    {
        $contact = new Contact();
        $contact->comments = 'Test Comments';
        $contact->save();

        $expectedUrl = "http://localhost:8080/contacts/$contact->id";
        $actualUrl = $contact->getResourceUrl();
        $this->assertEquals($expectedUrl, $actualUrl);
    }
}

class ConcreteApiModel extends ApiModel
{

}