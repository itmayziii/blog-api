<?php

namespace itmayziii\tests\app\Http\Controllers;

use App\Http\Controllers\BlogController;
use Illuminate\Http\Request;
use itmayziii\Laravel\JsonApi;
use itmayziii\tests\TestCase;
use itmayziii\tests\ResponseVerifier;

class BlogControllerTest extends TestCase
{
    /**
     * @var JsonApi
     */
    private $jsonApi;

    /**
     * @var BlogController
     */
    private $blogController;

    /**
     * @var ResponseVerifier
     */
    private $responseTester;

    public function setUp()
    {
        parent::setUp();
        $this->jsonApi = app(JsonApi::class);
        $this->blogController = app(BlogController::class);
        $this->responseTester = new ResponseVerifier();
    }

    public function test_create_authorization()
    {
        $this->actAsStandardUser();
        $request = Request::create('v1/blogs', 'POST');
        $response = $this->blogController->store($request);

        $this->responseTester->testUnauthorized($response);
    }
}
