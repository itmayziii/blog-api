<?php

namespace Tests\Http\Controllers;

use App\Http\Controllers\PostController;
use App\Http\JsonApi;
use App\Post;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery;
use Mockery\Mock;
use Psr\Log\LoggerInterface;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    /**
     * @var PostController
     */
    private $postController;
    /**
     * @var Gate | Mock;
     */
    private $gateMock;
    /**
     * @var Response | Mock
     */
    private $responseMock;
    /**
     * @var LoggerInterface | Mock
     */
    private $loggerMock;
    /**
     * @var Post | Mock
     */
    private $postMock;
    /**
     * @var JsonApi | Mock;
     */
    protected $jsonApiMock;

    public function setUp()
    {
        parent::setUp();
        $this->jsonApiMock = $this->jsonApiMock = Mockery::mock(JsonApi::class);
        $this->gateMock = Mockery::mock(Gate::class);
        $this->loggerMock = Mockery::mock(LoggerInterface::class);
        $this->responseMock = Mockery::mock(Response::class);
        $this->postMock = Mockery::mock(Post::class);
        $this->postController = new PostController($this->jsonApiMock, $this->gateMock, $this->loggerMock);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function test_index_responds_with_resources()
    {
        $paginationMock = Mockery::mock(LengthAwarePaginator::class);

        $request = Request::create('v1/posts', 'GET', ['size' => 10, 'page' => 2]);
        $this->postMock
            ->shouldReceive('where')
            ->once()
            ->withArgs(['status', 'draft'])
            ->andReturn($this->postMock);

        $this->postMock
            ->shouldReceive('orderBy')
            ->once()
            ->withArgs(['created_at', 'desc'])
            ->andReturn($this->postMock);

        $this->postMock
            ->shouldReceive('paginate')
            ->once()
            ->withArgs([10, null, 'page', 2])
            ->andReturn($paginationMock);

        $this->jsonApiMock
            ->shouldReceive('respondResourcesFound')
            ->once()
            ->withArgs([$this->responseMock, $paginationMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->postController->index($request, $this->responseMock, $this->postMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_show_responds_with_a_resource_when_one_exists()
    {
        $this->postMock
            ->shouldReceive('find')
            ->once()
            ->withArgs(['a-slug'])
            ->andReturn($this->postMock);

        $this->jsonApiMock
            ->shouldReceive('respondResourceFound')
            ->once()
            ->withArgs([$this->responseMock, $this->postMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->postController->show($this->responseMock, $this->postMock, 'a-slug');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_show_responds_not_found_when_no_resource_exists()
    {
        $this->postMock
            ->shouldReceive('find')
            ->once()
            ->withArgs(['a-slug'])
            ->andReturn(null);

        $this->jsonApiMock
            ->shouldReceive('respondResourceNotFound')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $this->loggerMock
            ->shouldReceive('debug')
            ->once()
            ->withArgs([PostController::class . ' unable to find post with slug: a-slug']);

        $actualResult = $this->postController->show($this->responseMock, $this->postMock, 'a-slug');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_store_responds_unauthorized_when_authorization_fails()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['store', $this->postMock])
            ->andReturn(true);

        $this->jsonApiMock
            ->shouldReceive('respondForbidden')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $request = Request::create('v1/posts', 'POST');

        $actualResult = $this->postController->store($request, $this->responseMock, $this->postMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_store_responds_validation_failed_when_validation_fails()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['store', $this->postMock])
            ->andReturn(false);

        $this->jsonApiMock
            ->shouldReceive('respondValidationFailed')
            ->once()
            ->andReturn($this->responseMock);

        $request = Request::create('v1/posts', 'POST');

        $actualResult = $this->postController->store($request, $this->responseMock, $this->postMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_store_responds_with_server_error_on_create_exception()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['store', $this->postMock])
            ->andReturn(false);

        $this->jsonApiMock
            ->shouldReceive('respondServerError')
            ->once()
            ->withArgs([$this->responseMock, 'Unable to create the post'])
            ->andReturn($this->responseMock);

        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->withArgs([Postcontroller::class . ' failed to create a post with exception: an error happened']);

        $this->postMock
            ->shouldReceive('create')
            ->once()
            ->andThrow(new \Exception('an error happened'));

        $request = Request::create('v1/posts', 'POST', [
            'user-id'     => 1,
            'category-id' => 2,
            'slug'        => 'a-slug',
            'title'       => 'A Title',
            'content'     => 'Some Content',
            'image-path'  => '/path/to/images'
        ]);

        $actualResult = $this->postController->store($request, $this->responseMock, $this->postMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_store_responds_with_resource_on_successful_creation()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['store', $this->postMock])
            ->andReturn(false);

        $this->jsonApiMock
            ->shouldReceive('respondResourceCreated')
            ->once()
            ->withArgs([$this->responseMock, $this->postMock])
            ->andReturn($this->responseMock);

        $this->postMock
            ->shouldReceive('create')
            ->once()
            ->andReturn($this->postMock);

        $request = Request::create('v1/posts', 'POST', [
            'user-id'     => 1,
            'category-id' => 2,
            'slug'        => 'a-slug',
            'title'       => 'A Title',
            'content'     => 'Some Content',
            'image-path'  => '/path/to/images'
        ]);

        $actualResult = $this->postController->store($request, $this->responseMock, $this->postMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_update_responds_forbidden_when_authorization_fails()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['update', $this->postMock])
            ->andReturn(true);

        $this->jsonApiMock
            ->shouldReceive('respondForbidden')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);


        $request = Request::create('v1/posts', 'PUT');

        $actualResult = $this->postController->update($request, $this->responseMock, $this->postMock, 'a-slug');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_update_responds_validation_failed_when_validation_fails()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['update', $this->postMock])
            ->andReturn(false);

        $this->jsonApiMock
            ->shouldReceive('respondValidationFailed')
            ->once()
            ->andReturn($this->responseMock);

        $this->postMock
            ->shouldReceive('find')
            ->once()
            ->withArgs(['a-slug'])
            ->andReturn($this->postMock);

        $request = Request::create('v1/posts', 'PUT');

        $actualResult = $this->postController->update($request, $this->responseMock, $this->postMock, 'a-slug');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_update_responds_not_found_when_no_resource_exists()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['update', $this->postMock])
            ->andReturn(false);

        $this->jsonApiMock
            ->shouldReceive('respondResourceNotFound')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $this->postMock
            ->shouldReceive('find')
            ->once()
            ->withArgs(['a-slug'])
            ->andReturn(null);

        $request = Request::create('v1/posts', 'PUT');

        $actualResult = $this->postController->update($request, $this->responseMock, $this->postMock, 'a-slug');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_update_responds_with_server_error_on_update_exception()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['update', $this->postMock])
            ->andReturn(false);

        $this->jsonApiMock
            ->shouldReceive('respondServerError')
            ->once()
            ->withArgs([$this->responseMock, 'Unable to update post'])
            ->andReturn($this->responseMock);

        $this->postMock
            ->shouldReceive('find')
            ->once()
            ->withArgs(['a-slug'])
            ->andReturn($this->postMock);

        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->withArgs([Postcontroller::class . ' failed to update a post with exception: an error happened']);

        $this->postMock
            ->shouldReceive('update')
            ->once()
            ->andThrow(new \Exception('an error happened'));

        $request = Request::create('v1/posts', 'PUT', [
            'user-id'     => 1,
            'category-id' => 2,
            'slug'        => 'a-slug',
            'title'       => 'A Title',
            'content'     => 'Some Content',
            'image-path'  => '/path/to/images'
        ]);

        $actualResult = $this->postController->update($request, $this->responseMock, $this->postMock, 'a-slug');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_update_responds_with_resource_on_successful_update()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['update', $this->postMock])
            ->andReturn(false);

        $this->jsonApiMock
            ->shouldReceive('respondResourceUpdated')
            ->once()
            ->withArgs([$this->responseMock, $this->postMock])
            ->andReturn($this->responseMock);

        $this->postMock
            ->shouldReceive('update')
            ->once()
            ->andReturn($this->postMock);

        $this->postMock
            ->shouldReceive('find')
            ->once()
            ->withArgs(['a-slug'])
            ->andReturn($this->postMock);

        $request = Request::create('v1/posts', 'PUT', [
            'user-id'     => 1,
            'category-id' => 2,
            'slug'        => 'a-slug',
            'title'       => 'A Title',
            'content'     => 'Some Content',
            'image-path'  => '/path/to/images'
        ]);

        $actualResult = $this->postController->update($request, $this->responseMock, $this->postMock, 'a-slug');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_delete_responds_forbidden_when_authorization_fails()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['delete', $this->postMock])
            ->andReturn(true);

        $this->jsonApiMock
            ->shouldReceive('respondForbidden')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->postController->delete($this->responseMock, $this->postMock, 'a-slug');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_delete_responds_not_found_when_post_does_not_exist()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['delete', $this->postMock])
            ->andReturn(false);

        $this->jsonApiMock
            ->shouldReceive('respondResourceNotFound')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $this->postMock
            ->shouldReceive('find')
            ->once()
            ->withArgs(['a-slug'])
            ->andReturn(null);

        $actualResult = $this->postController->delete($this->responseMock, $this->postMock, 'a-slug');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_delete_responds_that_post_was_deleted()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['delete', $this->postMock])
            ->andReturn(false);

        $this->jsonApiMock
            ->shouldReceive('respondResourceDeleted')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $this->postMock
            ->shouldReceive('find')
            ->once()
            ->withArgs(['a-slug'])
            ->andReturn($this->postMock);

        $this->postMock
            ->shouldReceive('delete')
            ->once();

        $actualResult = $this->postController->delete($this->responseMock, $this->postMock, 'a-slug');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_delete_responds_with_server_error_on_delete_failure()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['delete', $this->postMock])
            ->andReturn(false);

        $this->jsonApiMock
            ->shouldReceive('respondServerError')
            ->once()
            ->withArgs([$this->responseMock, 'Unable to delete post'])
            ->andReturn($this->responseMock);

        $this->postMock
            ->shouldReceive('find')
            ->once()
            ->withArgs(['a-slug'])
            ->andReturn($this->postMock);

        $this->postMock
            ->shouldReceive('delete')
            ->once()
            ->andThrow(new \Exception('an error happened'));

        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->withArgs([PostController::class . ' failed to delete a post with exception: an error happened']);

        $actualResult = $this->postController->delete($this->responseMock, $this->postMock, 'a-slug');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }
}