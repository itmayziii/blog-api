<?php

namespace Tests\Http\Controllers;

use App\Category;
use App\Http\Controllers\CategoryController;
use App\Http\JsonApi;
use App\Post;
use App\Repositories\CacheRepository;
use App\Repositories\CategoryRepository;
use Illuminate\Contracts\Auth\Access\Gate;
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

class CategoryControllerTest extends TestCase
{
    /**
     * @var Request | Mock
     */
    private $requestMock;
    /**
     * @var Response | Mock
     */
    private $responseMock;
    /**
     * @var JsonApi | Mock
     */
    private $jsonApiMock;
    /**
     * @var Category | Mock
     */
    private $categoryMock;
    /**
     * @var Post | Mock
     */
    private $postMock;
    /**
     * @var Gate | Mock
     */
    private $gateMock;
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
     * @var LoggerInterface | Mock
     */
    private $loggerMock;
    /**
     * @var LengthAwarePaginator | Mock
     */
    private $paginatorMock;
    /**
     * @var CategoryController
     */
    private $categoryController;
    /**
     * @var CategoryRepository | Mock
     */
    private $categoryRepositoryMock;
    /**
     * @var CacheRepository | Mock
     */
    private $cacheRepositoryMock;

    public function setUp()
    {
        parent::setUp();
        $this->requestMock = Mockery::mock(Request::class);
        $this->responseMock = Mockery::mock(Response::class);
        $this->jsonApiMock = Mockery::mock(JsonApi::class);
        $this->categoryRepositoryMock = Mockery::mock(CategoryRepository::class);
        $this->categoryMock = Mockery::mock(Category::class);
        $this->postMock = Mockery::mock(Post::class);
        $this->gateMock = Mockery::mock(Gate::class);
        $this->validationFactoryMock = Mockery::mock(ValidationFactory::class);
        $this->validatorMock = Mockery::mock(Validator::class);
        $this->messageBagMock = Mockery::mock(MessageBag::class);
        $this->loggerMock = Mockery::mock(LoggerInterface::class);
        $this->paginatorMock = Mockery::mock(LengthAwarePaginator::class);
        $this->cacheRepositoryMock = Mockery::mock(CacheRepository::class);

        $this->categoryController = new CategoryController(
            $this->categoryRepositoryMock,
            $this->jsonApiMock,
            $this->gateMock,
            $this->loggerMock,
            $this->validationFactoryMock,
            $this->cacheRepositoryMock
        );
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function test_index_returns_categories()
    {
        $this->requestMock
            ->shouldReceive('query')
            ->once()
            ->withArgs(['size', 15])
            ->andReturn(30);

        $this->requestMock
            ->shouldReceive('query')
            ->once()
            ->withArgs(['page', 1])
            ->andReturn(2);

        $this->cacheRepositoryMock
            ->shouldReceive('remember')
            ->once()
            ->andReturn($this->paginatorMock);

        $this->jsonApiMock
            ->shouldReceive('respondResourcesFound')
            ->once()
            ->withArgs([$this->responseMock, $this->paginatorMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->categoryController->index($this->requestMock, $this->responseMock, $this->categoryMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_show_responds_not_found_if_category_does_not_exist()
    {
        $this->categoryRepositoryMock
            ->shouldReceive('findBySlug')
            ->once()
            ->withArgs([2])
            ->andReturn(null);

        $this->jsonApiMock
            ->shouldReceive('respondResourceNotFound')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->categoryController->show($this->responseMock, 2);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_show_responds_with_category()
    {
        $this->categoryRepositoryMock
            ->shouldReceive('findBySlug')
            ->once()
            ->withArgs([2])
            ->andReturn($this->categoryMock);

        $this->jsonApiMock
            ->shouldReceive('respondResourceFound', $this->categoryMock)
            ->once()
            ->withArgs([$this->responseMock, $this->categoryMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->categoryController->show($this->responseMock, 2);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_store_responds_forbidden_if_not_allowed()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['store', $this->categoryMock])
            ->andReturn(true);

        $this->jsonApiMock
            ->shouldReceive('respondForbidden')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->categoryController->store($this->requestMock, $this->responseMock, $this->categoryMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_store_responds_validation_failed_if_validation_fails()
    {
        $this->setUpValidationMock(true);

        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['store', $this->categoryMock])
            ->andReturn(false);

        $this->jsonApiMock
            ->shouldReceive('respondValidationFailed')
            ->once()
            ->andReturn($this->responseMock);

        $actualResult = $this->categoryController->store($this->requestMock, $this->responseMock, $this->categoryMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_store_responds_server_error_if_category_can_not_be_created()
    {
        $this->setUpValidationMock(false);

        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['store', $this->categoryMock])
            ->andReturn(false);

        $this->categoryMock
            ->shouldReceive('create')
            ->once()
            ->andThrow(new \Exception('an error occurred'));

        $this->requestMock
            ->shouldReceive('input')
            ->times(2);

        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->withArgs(['Failed to create a category with exception: an error occurred']);

        $this->jsonApiMock
            ->shouldReceive('respondServerError')
            ->once()
            ->withArgs([$this->responseMock, 'Unable to create the category'])
            ->andReturn($this->responseMock);

        $actualResult = $this->categoryController->store($this->requestMock, $this->responseMock, $this->categoryMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_store_responds_with_created_resource_on_success()
    {
        $this->setUpValidationMock(false);

        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['store', $this->categoryMock])
            ->andReturn(false);

        $this->categoryMock
            ->shouldReceive('create')
            ->once()
            ->andReturn($this->categoryMock);

        $this->requestMock
            ->shouldReceive('input')
            ->times(2);

        $this->jsonApiMock
            ->shouldReceive('respondResourceCreated')
            ->once()
            ->withArgs([$this->responseMock, $this->categoryMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->categoryController->store($this->requestMock, $this->responseMock, $this->categoryMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_update_responds_forbidden_if_user_is_not_allowed()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['update', $this->categoryMock])
            ->andReturn(true);

        $this->jsonApiMock
            ->shouldReceive('respondForbidden')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->categoryController->update($this->requestMock, $this->responseMock, $this->categoryMock, 4);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_update_responds_not_found_if_category_does_not_exist()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['update', $this->categoryMock])
            ->andReturn(false);

        $this->categoryRepositoryMock
            ->shouldReceive('findBySlug')
            ->once()
            ->withArgs([4])
            ->andReturn(null);

        $this->jsonApiMock
            ->shouldReceive('respondResourceNotFound')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->categoryController->update($this->requestMock, $this->responseMock, $this->categoryMock, 4);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_update_responds_validation_failed_when_validation_fails()
    {
        $this->setUpValidationMock(true);

        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['update', $this->categoryMock])
            ->andReturn(false);

        $this->categoryMock
            ->shouldReceive('getAttribute')
            ->times(2);

        $this->requestMock
            ->shouldReceive('input')
            ->times(2);

        $this->categoryRepositoryMock
            ->shouldReceive('findBySlug')
            ->once()
            ->withArgs([4])
            ->andReturn($this->categoryMock);

        $this->jsonApiMock
            ->shouldReceive('respondValidationFailed')
            ->once()
            ->andReturn($this->responseMock);

        $actualResult = $this->categoryController->update($this->requestMock, $this->responseMock, $this->categoryMock, 4);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_update_responds_with_server_error_on_failed_update()
    {
        $this->setUpValidationMock(false);

        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['update', $this->categoryMock])
            ->andReturn(false);

        $this->requestMock
            ->shouldReceive('input')
            ->times(4);

        $this->categoryMock
            ->shouldReceive('getAttribute')
            ->times(2);

        $this->categoryRepositoryMock
            ->shouldReceive('findBySlug')
            ->once()
            ->withArgs([4])
            ->andReturn($this->categoryMock);

        $this->jsonApiMock
            ->shouldReceive('respondServerError')
            ->once()
            ->withArgs([$this->responseMock, 'Unable to update category.'])
            ->andReturn($this->responseMock);

        $this->categoryMock
            ->shouldReceive('update')
            ->once()
            ->andThrow(new \Exception('an error occurred'));

        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->withArgs(['Failed to update a category with exception: an error occurred']);

        $actualResult = $this->categoryController->update($this->requestMock, $this->responseMock, $this->categoryMock, 4);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_update_responds_resource_updated_if_everything_is_successful()
    {
        $this->setUpValidationMock(false);

        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['update', $this->categoryMock])
            ->andReturn(false);

        $this->categoryRepositoryMock
            ->shouldReceive('findBySlug')
            ->once()
            ->withArgs([4])
            ->andReturn($this->categoryMock);

        $this->requestMock
            ->shouldReceive('input')
            ->times(4);

        $this->categoryMock
            ->shouldReceive('getAttribute')
            ->times(2);

        $this->jsonApiMock
            ->shouldReceive('respondResourceUpdated')
            ->once()
            ->withArgs([$this->responseMock, $this->categoryMock])
            ->andReturn($this->responseMock);

        $this->categoryMock
            ->shouldReceive('update')
            ->once()
            ->andReturn($this->categoryMock);

        $actualResult = $this->categoryController->update($this->requestMock, $this->responseMock, $this->categoryMock, 4);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_delete_responds_forbidden_if_user_is_not_allowed()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['delete', $this->categoryMock])
            ->andReturn(true);

        $this->jsonApiMock
            ->shouldReceive('respondForbidden')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->categoryController->delete($this->responseMock, $this->categoryMock, $this->postMock, 4);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_delete_responds_not_found_if_category_does_not_exist()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['delete', $this->categoryMock])
            ->andReturn(false);

        $this->categoryRepositoryMock
            ->shouldReceive('findBySlug')
            ->once()
            ->withArgs([4])
            ->andReturn(null);

        $this->jsonApiMock
            ->shouldReceive('respondResourceNotFound')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->categoryController->delete($this->responseMock, $this->categoryMock, $this->postMock, 4);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_delete_responds_server_error_if_category_can_not_be_deleted()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['delete', $this->categoryMock])
            ->andReturn(false);

        $this->categoryRepositoryMock
            ->shouldReceive('findBySlug')
            ->once()
            ->withArgs([4])
            ->andReturn($this->categoryMock);

        $this->jsonApiMock
            ->shouldReceive('respondServerError')
            ->once()
            ->withArgs([$this->responseMock, 'Unable to delete category.'])
            ->andReturn($this->responseMock);

        $this->postMock
            ->shouldReceive('where')
            ->once()
            ->withArgs(['category_id', 4])
            ->andReturn($this->postMock);

        $this->postMock
            ->shouldReceive('update')
            ->once()
            ->withArgs([['category_id' => null]])
            ->andReturn($this->postMock);

        $this->categoryMock
            ->shouldReceive('delete')
            ->once()
            ->andThrow(new \Exception('an error occurred'));

        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->withArgs(['Failed to delete a category with exception: an error occurred']);

        $actualResult = $this->categoryController->delete($this->responseMock, $this->categoryMock, $this->postMock, 4);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_delete_responds_category_deleted_if_everything_is_successful()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['delete', $this->categoryMock])
            ->andReturn(false);

        $this->categoryRepositoryMock
            ->shouldReceive('findBySlug')
            ->once()
            ->withArgs([4])
            ->andReturn($this->categoryMock);

        $this->jsonApiMock
            ->shouldReceive('respondResourceDeleted')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $this->postMock
            ->shouldReceive('where')
            ->once()
            ->withArgs(['category_id', 4])
            ->andReturn($this->postMock);

        $this->postMock
            ->shouldReceive('update')
            ->once()
            ->withArgs([['category_id' => null]])
            ->andReturn($this->postMock);

        $this->categoryMock
            ->shouldReceive('delete')
            ->once();

        $actualResult = $this->categoryController->delete($this->responseMock, $this->categoryMock, $this->postMock, 4);
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