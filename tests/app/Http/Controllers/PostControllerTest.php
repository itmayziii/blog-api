<?php

namespace Tests\Http\Controllers;

use App\Http\Controllers\PostController;
use App\Http\JsonApi;
use App\Post;
use App\Repositories\CacheRepository;
use App\Repositories\PostRepository;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\MessageBag;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Contracts\Validation\Validator;
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
     * @var PostRepository | Mock
     */
    private $postRepositoryMock;
    /**
     * @var Gate | Mock
     */
    private $gateMock;
    /**
     * @var Guard | Mock
     */
    private $guardMock;
    /**
     * @var Response | Mock
     */
    private $responseMock;
    /**
     * @var Request | Mock
     */
    private $requestMock;
    /**
     * @var LoggerInterface | Mock
     */
    private $loggerMock;
    /**
     * @var CacheRepository | Mock
     */
    private $cacheRepositoryMock;
    /**
     * @var Post | Mock
     */
    private $postMock;
    /**
     * @var ValidationFactory | Mock
     */
    private $validationFactoryMock;
    /**
     * @var Validator | Mock
     */
    private $validatorMock;
    /**
     * @var MessageBag
     */
    private $messageBagMock;
    /**
     * @var LengthAwarePaginator | Mock
     */
    private $paginatorMock;
    /**
     * @var JsonApi | Mock
     */
    private $jsonApiMock;

    public function setUp()
    {
        parent::setUp();
        $this->jsonApiMock = Mockery::mock(JsonApi::class);
        $this->postRepositoryMock = Mockery::mock(PostRepository::class);
        $this->gateMock = Mockery::mock(Gate::class);
        $this->guardMock = Mockery::mock(Guard::class);
        $this->loggerMock = Mockery::mock(LoggerInterface::class);
        $this->cacheRepositoryMock = Mockery::mock(CacheRepository::class);
        $this->requestMock = Mockery::mock(Request::class);
        $this->responseMock = Mockery::mock(Response::class);
        $this->postMock = Mockery::mock(Post::class);
        $this->validationFactoryMock = Mockery::mock(ValidationFactory::class);
        $this->validatorMock = Mockery::mock(Validator::class);
        $this->messageBagMock = Mockery::mock(MessageBag::class);
        $this->paginatorMock = Mockery::mock(LengthAwarePaginator::class);
        $this->postController = new PostController(
            $this->jsonApiMock,
            $this->postRepositoryMock,
            $this->gateMock,
            $this->guardMock,
            $this->loggerMock,
            $this->validationFactoryMock,
            $this->cacheRepositoryMock
        );
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function test_index_responds_with_live_posts()
    {
        $this->requestMock
            ->shouldReceive('query')
            ->once()
            ->withArgs(['size', 15])
            ->andReturn(20);

        $this->requestMock
            ->shouldReceive('query')
            ->once()
            ->withArgs(['page', 1])
            ->andReturn(2);

        $this->gateMock
            ->shouldReceive('allows')
            ->once()
            ->withArgs(['indexAllPosts', $this->postMock])
            ->andReturn(false);

        $this->cacheRepositoryMock
            ->shouldReceive('remember')
            ->once()
            ->withArgs(['posts.live.page2.size20', 60, Mockery::any()])
            ->andReturn($this->paginatorMock);

        $this->jsonApiMock
            ->shouldReceive('respondResourcesFound')
            ->once()
            ->withArgs([$this->responseMock, $this->paginatorMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->postController->index($this->requestMock, $this->responseMock, $this->postMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_index_responds_with_all_posts()
    {
        $this->requestMock
            ->shouldReceive('query')
            ->once()
            ->withArgs(['size', 15])
            ->andReturn(20);

        $this->requestMock
            ->shouldReceive('query')
            ->once()
            ->withArgs(['page', 1])
            ->andReturn(2);

        $this->gateMock
            ->shouldReceive('allows')
            ->once()
            ->withArgs(['indexAllPosts', $this->postMock])
            ->andReturn(true);

        $this->cacheRepositoryMock
            ->shouldReceive('remember')
            ->once()
            ->withArgs(['posts.all.page2.size20', 60, Mockery::any()])
            ->andReturn($this->paginatorMock);

        $this->jsonApiMock
            ->shouldReceive('respondResourcesFound')
            ->once()
            ->withArgs([$this->responseMock, $this->paginatorMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->postController->index($this->requestMock, $this->responseMock, $this->postMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_show_responds_not_found_when_no_resource_exists()
    {
        $this->cacheRepositoryMock
            ->shouldReceive('remember')
            ->once()
            ->withArgs(['post.a-slug', 60, Mockery::any()])
            ->andReturn(null);

        $this->loggerMock
            ->shouldReceive('debug')
            ->once()
            ->withArgs([PostController::class . ' unable to find post with slug: a-slug']);

        $this->jsonApiMock
            ->shouldReceive('respondResourceNotFound')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->postController->show($this->responseMock, 'a-slug');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_show_responds_with_the_post_when_the_post_is_live()
    {
        $this->cacheRepositoryMock
            ->shouldReceive('remember')
            ->once()
            ->withArgs(['post.a-slug', 60, Mockery::any()])
            ->andReturn($this->postMock);

        $this->postMock
            ->shouldReceive('isLive')
            ->once()
            ->andReturn(true);

        $this->jsonApiMock
            ->shouldReceive('respondResourceFound')
            ->once()
            ->withArgs([$this->responseMock, $this->postMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->postController->show($this->responseMock, 'a-slug');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_show_responds_with_unauthorized_when_guest_user_views_non_live_post()
    {
        $this->cacheRepositoryMock
            ->shouldReceive('remember')
            ->once()
            ->withArgs(['post.a-slug', 60, Mockery::any()])
            ->andReturn($this->postMock);

        $this->postMock
            ->shouldReceive('isLive')
            ->once()
            ->andReturn(false);

        $this->guardMock
            ->shouldReceive('guest')
            ->once()
            ->andReturn(true);

        $this->jsonApiMock
            ->shouldReceive('respondUnauthorized')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->postController->show($this->responseMock, 'a-slug');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_show_responds_with_forbidden_for_non_live_posts_and_user_does_not_have_permission()
    {
        $this->cacheRepositoryMock
            ->shouldReceive('remember')
            ->once()
            ->withArgs(['post.a-slug', 60, Mockery::any()])
            ->andReturn($this->postMock);

        $this->postMock
            ->shouldReceive('isLive')
            ->once()
            ->andReturn(false);

        $this->guardMock
            ->shouldReceive('guest')
            ->once()
            ->andReturn(false);

        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['show', $this->postMock])
            ->andReturn(true);

        $this->jsonApiMock
            ->shouldReceive('respondForbidden')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->postController->show($this->responseMock, 'a-slug');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_show_responds_with_a_post_when_it_is_not_live_but_user_has_permission()
    {
        $this->cacheRepositoryMock
            ->shouldReceive('remember')
            ->once()
            ->withArgs(['post.a-slug', 60, Mockery::any()])
            ->andReturn($this->postMock);

        $this->postMock
            ->shouldReceive('isLive')
            ->once()
            ->andReturn(false);

        $this->guardMock
            ->shouldReceive('guest')
            ->once()
            ->andReturn(false);

        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['show', $this->postMock])
            ->andReturn(false);

        $this->jsonApiMock
            ->shouldReceive('respondResourceFound')
            ->once()
            ->withArgs([$this->responseMock, $this->postMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->postController->show($this->responseMock, 'a-slug');
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

        $actualResult = $this->postController->store($this->requestMock, $this->responseMock, $this->postMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_store_responds_validation_failed_when_validation_fails()
    {
        $this->setUpValidationMock(true);

        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['store', $this->postMock])
            ->andReturn(false);

        $this->jsonApiMock
            ->shouldReceive('respondValidationFailed')
            ->once()
            ->andReturn($this->responseMock);

        $actualResult = $this->postController->store($this->requestMock, $this->responseMock, $this->postMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_store_responds_with_server_error_on_create_exception()
    {
        $this->setUpValidationMock(false);

        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['store', $this->postMock])
            ->andReturn(false);

        $this->jsonApiMock
            ->shouldReceive('respondServerError')
            ->once()
            ->withArgs([$this->responseMock, 'Unable to create the post.'])
            ->andReturn($this->responseMock);

        $this->requestMock
            ->shouldReceive('input')
            ->times(11);

        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->withArgs([Postcontroller::class . ' failed to create a post with exception: an error happened']);

        $this->postMock
            ->shouldReceive('create')
            ->once()
            ->andThrow(new \Exception('an error happened'));

        $actualResult = $this->postController->store($this->requestMock, $this->responseMock, $this->postMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_store_responds_with_resource_on_successful_creation()
    {
        $this->setUpValidationMock(false);

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

        $this->requestMock
            ->shouldReceive('input')
            ->times(11);

        $this->postMock
            ->shouldReceive('create')
            ->once()
            ->andReturn($this->postMock);

        $this->cacheRepositoryMock
            ->shouldReceive('keys')
            ->once()
            ->withArgs(['posts*'])
            ->andReturn(['laravel:posts.page1.size5']);

        $this->cacheRepositoryMock
            ->shouldReceive('deleteMultiple')
            ->once()
            ->withArgs([['laravel:posts.page1.size5']]);

        $this->cacheRepositoryMock
            ->shouldReceive('keys')
            ->once()
            ->withArgs(['categories-posts*'])
            ->andReturn(['laravel:categories-posts.a-slug']);

        $this->cacheRepositoryMock
            ->shouldReceive('deleteMultiple')
            ->once()
            ->withArgs([['laravel:categories-posts.a-slug']]);

        $actualResult = $this->postController->store($this->requestMock, $this->responseMock, $this->postMock);
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

        $actualResult = $this->postController->update($this->requestMock, $this->responseMock, $this->postMock, 'a-slug');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_update_responds_validation_failed_when_validation_fails()
    {
        $this->setUpValidationMock(true);

        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['update', $this->postMock])
            ->andReturn(false);

        $this->requestMock
            ->shouldReceive('input')
            ->times(2);

        $this->postMock
            ->shouldReceive('getAttribute')
            ->times(2);

        $this->jsonApiMock
            ->shouldReceive('respondValidationFailed')
            ->once()
            ->andReturn($this->responseMock);

        $this->postRepositoryMock
            ->shouldReceive('findBySlug')
            ->once()
            ->withArgs(['a-slug'])
            ->andReturn($this->postMock);

        $actualResult = $this->postController->update($this->requestMock, $this->responseMock, $this->postMock, 'a-slug');
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

        $this->postRepositoryMock
            ->shouldReceive('findBySlug')
            ->once()
            ->withArgs(['a-slug'])
            ->andReturn(null);

        $actualResult = $this->postController->update($this->requestMock, $this->responseMock, $this->postMock, 'a-slug');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_update_responds_with_server_error_on_update_exception()
    {
        $this->setUpValidationMock(false);

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

        $this->postRepositoryMock
            ->shouldReceive('findBySlug')
            ->once()
            ->withArgs(['a-slug'])
            ->andReturn($this->postMock);

        $this->requestMock
            ->shouldReceive('input')
            ->times(13);

        $this->postMock
            ->shouldReceive('getAttribute')
            ->times(2);

        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->withArgs([Postcontroller::class . ' failed to update a post with exception: an error happened']);

        $this->postMock
            ->shouldReceive('update')
            ->once()
            ->andThrow(new \Exception('an error happened'));

        $actualResult = $this->postController->update($this->requestMock, $this->responseMock, $this->postMock, 'a-slug');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_update_responds_with_resource_on_successful_update()
    {
        $this->setUpValidationMock(false);

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

        $this->requestMock
            ->shouldReceive('input')
            ->times(13);

        $this->postMock
            ->shouldReceive('getAttribute')
            ->times(2);

        $this->postMock
            ->shouldReceive('update')
            ->once()
            ->andReturn($this->postMock);

        $this->postRepositoryMock
            ->shouldReceive('findBySlug')
            ->once()
            ->withArgs(['a-slug'])
            ->andReturn($this->postMock);

        $this->cacheRepositoryMock
            ->shouldReceive('forget')
            ->once()
            ->withArgs(['post.a-slug']);

        $this->cacheRepositoryMock
            ->shouldReceive('keys')
            ->once()
            ->withArgs(['posts*'])
            ->andReturn(['laravel:posts.page1.size5']);

        $this->cacheRepositoryMock
            ->shouldReceive('deleteMultiple')
            ->once()
            ->withArgs([['laravel:posts.page1.size5']]);

        $this->cacheRepositoryMock
            ->shouldReceive('keys')
            ->once()
            ->withArgs(['categories-posts*'])
            ->andReturn(['laravel:categories-posts.a-slug']);

        $this->cacheRepositoryMock
            ->shouldReceive('deleteMultiple')
            ->once()
            ->withArgs([['laravel:categories-posts.a-slug']]);

        $actualResult = $this->postController->update($this->requestMock, $this->responseMock, $this->postMock, 'a-slug');
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

        $this->postRepositoryMock
            ->shouldReceive('findBySlug')
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

        $this->postRepositoryMock
            ->shouldReceive('findBySlug')
            ->once()
            ->withArgs(['a-slug'])
            ->andReturn($this->postMock);

        $this->postMock
            ->shouldReceive('delete')
            ->once();

        $this->cacheRepositoryMock
            ->shouldReceive('forget')
            ->once()
            ->withArgs(['post.a-slug']);

        $this->cacheRepositoryMock
            ->shouldReceive('keys')
            ->once()
            ->withArgs(['posts*'])
            ->andReturn(['laravel:posts.page1.size5']);

        $this->cacheRepositoryMock
            ->shouldReceive('deleteMultiple')
            ->once()
            ->withArgs([['laravel:posts.page1.size5']]);

        $this->cacheRepositoryMock
            ->shouldReceive('keys')
            ->once()
            ->withArgs(['categories-posts*'])
            ->andReturn(['laravel:categories-posts.a-slug']);

        $this->cacheRepositoryMock
            ->shouldReceive('deleteMultiple')
            ->once()
            ->withArgs([['laravel:categories-posts.a-slug']]);

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

        $this->postRepositoryMock
            ->shouldReceive('findBySlug')
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

    /**
     * @param boolean $doesValidationFail
     */
    private function setUpValidationMock($doesValidationFail)
    {
        $this->requestMock
            ->shouldReceive('all')
            ->once()
            ->andReturn([]);

        $this->validationFactoryMock
            ->shouldReceive('make')
            ->once()
            ->andReturn($this->validatorMock);

        $this->validatorMock
            ->shouldReceive('fails')
            ->once()
            ->andReturn($doesValidationFail);

        if ($doesValidationFail) {
            $this->validatorMock
                ->shouldReceive('getMessageBag')
                ->once()
                ->andReturn($this->messageBagMock);
        }
    }
}