<?php

use App\Blog;
use App\Http\Controllers\BlogController;
use Illuminate\Http\Request;

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

    public function test_found()
    {
        $this->jsonApiMock->shouldReceive('respondResourceFound')->once()->andReturn('Blog Found');

        $blog = $this->createBlog();
        $response = $this->blogController->show($blog->slug);

        $this->assertThat($response, $this->equalTo('Blog Found'));
    }

    public function test_listing()
    {
        $this->jsonApiMock->shouldReceive('respondResourcesFound')->once()->andReturn('Blogs Found');

        $request = Request::create('v1/blogs');
        $response = $this->blogController->index($request);

        $this->assertThat($response, $this->equalTo('Blogs Found'));
    }

    public function test_update_authorization()
    {
        $this->jsonApiMock->shouldReceive('respondUnauthorized')->once()->andReturn('Blog Update Authorization Failed');

        $request = Request::create('v1/blogs', 'PATCH');
        $blog = $this->createBlog();
        $response = $this->blogController->update($request, $blog->id);

        $this->assertThat($response, $this->equalTo('Blog Update Authorization Failed'));
    }

    public function test_update_validation()
    {
        $this->jsonApiMock->shouldReceive('respondValidationFailed')->once()->andReturn('Blog Update Validation Failed');

        $this->actAsAdministrator();

        $request = Request::create(
            'v1/blogs',
            'PATCH',
            [
                'user-id'     => 1,
                'category-id' => 1,
                'title'       => '', // title is required
                'content'     => 'This is a blog, and it happens to be the first.'
            ]);
        $blog = $this->createBlog();
        $response = $this->blogController->update($request, $blog->slug);

        $this->assertThat($response, $this->equalTo('Blog Update Validation Failed'));
    }

    public function test_update_not_found()
    {
        $this->jsonApiMock->shouldReceive('respondResourceNotFound')->once()->andReturn('Blog Not Found');

        $this->actAsAdministrator();

        $request = Request::create(
            'v1/blogs',
            'PATCH',
            [
                'user-id'     => 1,
                'category-id' => 1,
                'title'       => 'A title',
                'content'     => 'This is a blog, and it happens to be the first.'
            ]);
        $response = $this->blogController->update($request, 342364198236413294);

        $this->assertThat($response, $this->equalTo('Blog Not Found'));
    }

    public function test_update_successful()
    {
        $this->jsonApiMock->shouldReceive('respondResourceUpdated')->once()->andReturn('Blog Updated Successfully');

        $this->actAsAdministrator();

        $request = Request::create(
            'v1/blogs',
            'PATCH',
            [
                'user-id'     => 1,
                'category-id' => 1,
                'title'       => 'A title',
                'content'     => 'This is a blog, and it happens to be the first.'
            ]);
        $blog = $this->createBlog();
        $response = $this->blogController->update($request, $blog->slug);

        $this->assertThat($response, $this->equalTo('Blog Updated Successfully'));
    }

    public function test_delete_authorization()
    {
        $this->jsonApiMock->shouldReceive('respondUnauthorized')->once()->andReturn('Deleting Blog Not Authorized');

        $blog = $this->createBlog();
        $response = $this->blogController->delete($blog->id);

        $this->assertThat($response, $this->equalTo('Deleting Blog Not Authorized'));
    }

    public function test_delete_not_found()
    {
        $this->jsonApiMock->shouldReceive('respondResourceNotFound')->once()->andReturn('No Blog to Delete');

        $this->actAsAdministrator();

        $response = $this->blogController->delete(43298574432965923475);

        $this->assertThat($response, $this->equalTo('No Blog to Delete'));
    }

    public function test_successful_deletion()
    {
        $this->jsonApiMock->shouldReceive('respondResourceDeleted')->once()->andReturn('No Blog to Delete');

        $this->actAsAdministrator();

        $blog = $this->createBlog();
        $response = $this->blogController->delete($blog->slug);

        $this->assertThat($response, $this->equalTo('No Blog to Delete'));
    }

    private function createBlog()
    {
        return factory(Blog::class, 1)->create()->first();
    }
}
