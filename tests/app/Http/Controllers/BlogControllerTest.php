<?php

use App\Http\Controllers\BlogController;
use Illuminate\Http\Request;
use itmayziii\Laravel\JsonApi;

class BlogControllerTest extends \TestCase
{
    /**
     * @var BlogController
     */
    private $blogController;

    public function setUp()
    {
        parent::setUp();
        $this->blogController = new BlogController($this->jsonApiMock);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function test_create_authorization()
    {
        $this->jsonApiMock->shouldReceive('respondUnauthorized')->once()->andReturn('Blog Creation Authorization Failed');

        $this->actAsStandardUser();
        $request = Request::create('v1/blogs', 'POST');
        $response = $this->blogController->store($request);

        $this->assertThat($response, $this->equalTo('Blog Creation Authorization Failed'));
    }

    public function test_create_validation_failed()
    {
        $this->jsonApiMock->shouldReceive('respondValidationFailed')->once()->andReturn('Blog Create Validation Failed');

        $this->actAsAdministrator();

        $request = Request::create(
            'v1/blogs',
            'POST',
            [
                'user-id'     => 1,
                'category-id' => 1,
                'title'       => '', // title is required
                'content'     => 'This is a blog, and it happens to be the first.'
            ]);
        $response = $this->blogController->store($request);

        $this->assertThat($response, $this->equalTo('Blog Create Validation Failed'));
    }

    public function test_creation_successful()
    {
        $this->jsonApiMock->shouldReceive('respondResourceCreated')->once()->andReturn('Blog Creation Successful');

        $this->actAsAdministrator();

        $request = Request::create(
            'v1/blogs',
            'POST',
            [
                'user-id'     => 1,
                'category-id' => 1,
                'title'       => 'My test blog.',
                'content'     => 'This is a blog, and it happens to be the first.'
            ]);
        $response = $this->blogController->store($request);

        $this->assertThat($response, $this->equalTo('Blog Creation Successful'));
    }

    public function test_()
    {

    }
}
