<?php

use App\Post;
use App\Http\Controllers\PostController;
use Illuminate\Http\Request;

class PostControllerTest extends \TestCase
{
    /**
     * @var PostController
     */
    private $postController;

    public function setUp()
    {
        parent::setUp();
        $this->postController = new PostController($this->jsonApiMock);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function test_create_authorization()
    {
        $this->jsonApiMock->shouldReceive('respondUnauthorized')->once()->andReturn('Post Creation Authorization Failed');

        $this->actAsStandardUser();
        $request = Request::create('v1/posts', 'POST');
        $response = $this->postController->store($request);

        $this->assertThat($response, $this->equalTo('Post Creation Authorization Failed'));
    }

    public function test_create_validation_failed()
    {
        $this->jsonApiMock->shouldReceive('respondValidationFailed')->once()->andReturn('Post Create Validation Failed');

        $this->actAsAdministrator();

        $request = Request::create(
            'v1/posts',
            'POST',
            [
                'user-id'     => 1,
                'category-id' => 1,
                'title'       => '', // title is required
                'content'     => 'This is a post, and it happens to be the first.'
            ]);
        $response = $this->postController->store($request);

        $this->assertThat($response, $this->equalTo('Post Create Validation Failed'));
    }

    public function test_creation_successful()
    {
        $this->jsonApiMock->shouldReceive('respondResourceCreated')->once()->andReturn('Post Creation Successful');

        $this->actAsAdministrator();

        $request = Request::create(
            'v1/posts',
            'POST',
            [
                'user-id'     => 1,
                'category-id' => 1,
                'title'       => 'My test post.',
                'slug'        => 'my-test-post',
                'content'     => 'This is a post, and it happens to be the first.'
            ]);
        $response = $this->postController->store($request);

        $this->assertThat($response, $this->equalTo('Post Creation Successful'));
    }

    public function test_found()
    {
        $this->jsonApiMock->shouldReceive('respondResourceFound')->once()->andReturn('Post Found');

        $post = $this->createPost();
        $response = $this->postController->show($post->slug);

        $this->assertThat($response, $this->equalTo('Post Found'));
    }

    public function test_listing()
    {
        $this->jsonApiMock->shouldReceive('respondResourcesFound')->once()->andReturn('Posts Found');

        $request = Request::create('v1/posts');
        $response = $this->postController->index($request);

        $this->assertThat($response, $this->equalTo('Posts Found'));
    }

    public function test_update_authorization()
    {
        $this->jsonApiMock->shouldReceive('respondUnauthorized')->once()->andReturn('Post Update Authorization Failed');

        $request = Request::create('v1/posts', 'PATCH');
        $post = $this->createPost();
        $response = $this->postController->update($request, $post->id);

        $this->assertThat($response, $this->equalTo('Post Update Authorization Failed'));
    }

    public function test_update_validation()
    {
        $this->jsonApiMock->shouldReceive('respondValidationFailed')->once()->andReturn('Post Update Validation Failed');

        $this->actAsAdministrator();

        $request = Request::create(
            'v1/posts',
            'PATCH',
            [
                'user-id'     => 1,
                'category-id' => 1,
                'title'       => '', // title is required
                'content'     => 'This is a post, and it happens to be the first.'
            ]);
        $post = $this->createPost();
        $response = $this->postController->update($request, $post->slug);

        $this->assertThat($response, $this->equalTo('Post Update Validation Failed'));
    }

    public function test_update_not_found()
    {
        $this->jsonApiMock->shouldReceive('respondResourceNotFound')->once()->andReturn('Post Not Found');

        $this->actAsAdministrator();

        $request = Request::create(
            'v1/posts',
            'PATCH',
            [
                'user-id'     => 1,
                'category-id' => 1,
                'title'       => 'A title',
                'content'     => 'This is a post, and it happens to be the first.'
            ]);
        $response = $this->postController->update($request, 342364198236413294);

        $this->assertThat($response, $this->equalTo('Post Not Found'));
    }

    public function test_update_successful()
    {
        $this->jsonApiMock->shouldReceive('respondResourceUpdated')->once()->andReturn('Post Updated Successfully');

        $this->actAsAdministrator();

        $request = Request::create(
            'v1/posts',
            'PATCH',
            [
                'user-id'     => 1,
                'category-id' => 1,
                'title'       => 'A title',
                'slug'        => 'a-title',
                'content'     => 'This is a post, and it happens to be the first.'
            ]);
        $post = $this->createPost();
        $response = $this->postController->update($request, $post->slug);

        $this->assertThat($response, $this->equalTo('Post Updated Successfully'));
    }

    public function test_delete_authorization()
    {
        $this->jsonApiMock->shouldReceive('respondUnauthorized')->once()->andReturn('Deleting Post Not Authorized');

        $post = $this->createPost();
        $response = $this->postController->delete($post->id);

        $this->assertThat($response, $this->equalTo('Deleting Post Not Authorized'));
    }

    public function test_delete_not_found()
    {
        $this->jsonApiMock->shouldReceive('respondResourceNotFound')->once()->andReturn('No Post to Delete');

        $this->actAsAdministrator();

        $response = $this->postController->delete(43298574432965923475);

        $this->assertThat($response, $this->equalTo('No Post to Delete'));
    }

    public function test_successful_deletion()
    {
        $this->jsonApiMock->shouldReceive('respondResourceDeleted')->once()->andReturn('No Post to Delete');

        $this->actAsAdministrator();

        $post = $this->createPost();
        $response = $this->postController->delete($post->slug);

        $this->assertThat($response, $this->equalTo('No Post to Delete'));
    }

    private function createPost()
    {
        return factory(Post::class, 1)->create()->first();
    }
}