<?php

namespace Tests\Http\Controllers;

use App\Http\Controllers\PageController;
use App\Http\JsonApi;
use App\Page;
use App\Repositories\CacheRepository;
use App\Repositories\PageRepository;
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

class PageControllerTest extends TestCase
{
    /**
     * @var PageController
     */
    private $pageController;
    /**
     * @var PageRepository | Mock
     */
    private $pageRepositoryMock;
    /**
     * @var Gate | Mock;
     */
    private $gateMock;
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
     * @var Page | Mock
     */
    private $pageMock;
    /**
     * @var CacheRepository | Mock
     */
    private $cacheRepositoryMock;
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
     * @var JsonApi | Mock;
     */
    private $jsonApiMock;

    public function setUp()
    {
        parent::setUp();
        $this->jsonApiMock = Mockery::mock(JsonApi::class);
        $this->pageRepositoryMock = Mockery::mock(PageRepository::class);
        $this->gateMock = Mockery::mock(Gate::class);
        $this->loggerMock = Mockery::mock(LoggerInterface::class);
        $this->cacheRepositoryMock = Mockery::mock(CacheRepository::class);
        $this->requestMock = Mockery::mock(Request::class);
        $this->responseMock = Mockery::mock(Response::class);
        $this->validationFactoryMock = Mockery::mock(ValidationFactory::class);
        $this->validatorMock = Mockery::mock(Validator::class);
        $this->messageBagMock = Mockery::mock(MessageBag::class);
        $this->pageMock = Mockery::mock(Page::class);
        $this->paginatorMock = Mockery::mock(LengthAwarePaginator::class);

        $this->pageController = new PageController($this->jsonApiMock, $this->loggerMock, $this->gateMock, $this->pageRepositoryMock, $this->cacheRepositoryMock,
            $this->validationFactoryMock);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function test_index_responds_with_live_pages()
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
            ->withArgs(['indexAllPages', $this->pageMock])
            ->andReturn(false);

        $this->cacheRepositoryMock
            ->shouldReceive('remember')
            ->once()
            ->withArgs(['pages.live.page2.size20', 60, Mockery::any()])
            ->andReturn($this->paginatorMock);

        $this->jsonApiMock
            ->shouldReceive('respondResourcesFound')
            ->once()
            ->withArgs([$this->responseMock, $this->paginatorMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->pageController->index($this->requestMock, $this->responseMock, $this->pageMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_index_responds_with_all_pages()
    {
        $this->requestMock
            ->shouldReceive('query')
            ->once()
            ->withArgs(['size', 15])
            ->andReturn(15);

        $this->requestMock
            ->shouldReceive('query')
            ->once()
            ->withArgs(['page', 1])
            ->andReturn(1);

        $this->gateMock
            ->shouldReceive('allows')
            ->once()
            ->withArgs(['indexAllPages', $this->pageMock])
            ->andReturn(true);

        $this->cacheRepositoryMock
            ->shouldReceive('remember')
            ->once()
            ->withArgs(['pages.all.page1.size15', 60, Mockery::any()])
            ->andReturn($this->paginatorMock);

        $this->jsonApiMock
            ->shouldReceive('respondResourcesFound')
            ->once()
            ->withArgs([$this->responseMock, $this->paginatorMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->pageController->index($this->requestMock, $this->responseMock, $this->pageMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_show_responds_not_found_when_no_resource_exists()
    {
        $this->cacheRepositoryMock
            ->shouldReceive('remember')
            ->once()
            ->withArgs(['page.a-slug', 60, Mockery::any()])
            ->andReturn(null);

        $this->loggerMock
            ->shouldReceive('debug')
            ->once()
            ->withArgs([PageController::class . ' unable to find page with slug: a-slug']);

        $this->jsonApiMock
            ->shouldReceive('respondResourceNotFound')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->pageController->show($this->responseMock, 'a-slug');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_show_responds_forbidden_when_user_is_forbidden()
    {
        $this->cacheRepositoryMock
            ->shouldReceive('remember')
            ->once()
            ->withArgs(['page.a-slug', 60, Mockery::any()])
            ->andReturn($this->pageMock);

        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['showPage', $this->pageMock])
            ->andReturn(true);

        $this->loggerMock
            ->shouldReceive('debug')
            ->once()
            ->withArgs([PageController::class . ' unauthorized to show page with slug: a-slug']);

        $this->jsonApiMock
            ->shouldReceive('respondForbidden')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->pageController->show($this->responseMock, 'a-slug');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_show_responds_with_a_resource_when_one_exists()
    {
        $this->cacheRepositoryMock
            ->shouldReceive('remember')
            ->once()
            ->withArgs(['page.a-slug', 60, Mockery::any()])
            ->andReturn($this->pageMock);

        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['showPage', $this->pageMock])
            ->andReturn(false);

        $this->jsonApiMock
            ->shouldReceive('respondResourceFound')
            ->once()
            ->withArgs([$this->responseMock, $this->pageMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->pageController->show($this->responseMock, 'a-slug');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_store_responds_forbidden_when_authorization_fails()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['store', $this->pageMock])
            ->andReturn(true);

        $this->loggerMock
            ->shouldReceive('debug')
            ->once()
            ->withArgs([PageController::class . ' unauthorized to create page']);

        $this->jsonApiMock
            ->shouldReceive('respondForbidden')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->pageController->store($this->requestMock, $this->responseMock, $this->pageMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_store_responds_validation_failed_when_validation_fails()
    {
        $this->setUpValidationMock(true);

        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['store', $this->pageMock])
            ->andReturn(false);

        $this->loggerMock
            ->shouldReceive('debug')
            ->once()
            ->withArgs([PageController::class . ' validation failed, unable to create page']);

        $this->jsonApiMock
            ->shouldReceive('respondValidationFailed')
            ->once()
            ->andReturn($this->responseMock);

        $actualResult = $this->pageController->store($this->requestMock, $this->responseMock, $this->pageMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_store_responds_with_server_error_on_create_exception()
    {
        $this->setUpValidationMock(false);

        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['store', $this->pageMock])
            ->andReturn(false);

        $this->pageRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->andThrow(new \Exception('an error happened'));

        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->withArgs([PageController::class . ' failed to create a page with exception: an error happened']);

        $this->jsonApiMock
            ->shouldReceive('respondServerError')
            ->once()
            ->withArgs([$this->responseMock, 'Unable to create the page.'])
            ->andReturn($this->responseMock);

        $actualResult = $this->pageController->store($this->requestMock, $this->responseMock, $this->pageMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_store_responds_with_resource_on_successful_creation()
    {
        $this->setUpValidationMock(false);

        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['store', $this->pageMock])
            ->andReturn(false);

        $this->pageRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->andReturn($this->pageMock);

        $this->cacheRepositoryMock
            ->shouldReceive('keys')
            ->once()
            ->withArgs(['pages*'])
            ->andReturn(['laravel:pages.page1.size5']);

        $this->cacheRepositoryMock
            ->shouldReceive('deleteMultiple')
            ->once()
            ->withArgs([['laravel:pages.page1.size5']]);

        $this->jsonApiMock
            ->shouldReceive('respondResourceCreated')
            ->once()
            ->withArgs([$this->responseMock, $this->pageMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->pageController->store($this->requestMock, $this->responseMock, $this->pageMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_update_responds_not_found_when_no_resource_exists()
    {
        $this->pageRepositoryMock
            ->shouldReceive('findBySlug')
            ->once()
            ->withArgs(['a-slug'])
            ->andReturn(null);

        $this->loggerMock
            ->shouldReceive('debug')
            ->once()
            ->withArgs([PageController::class . ' unable to find page to update with slug: a-slug']);

        $this->jsonApiMock
            ->shouldReceive('respondResourceNotFound')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->pageController->update($this->requestMock, $this->responseMock, 'a-slug');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_update_responds_forbidden_when_authorization_fails()
    {
        $this->pageRepositoryMock
            ->shouldReceive('findBySlug')
            ->once()
            ->withArgs(['a-slug'])
            ->andReturn($this->pageMock);

        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['update', $this->pageMock])
            ->andReturn(true);

        $this->loggerMock
            ->shouldReceive('debug')
            ->once()
            ->withArgs([PageController::class . ' unauthorized to update page with slug: a-slug']);

        $this->jsonApiMock
            ->shouldReceive('respondForbidden')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->pageController->update($this->requestMock, $this->responseMock, 'a-slug');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_update_responds_validation_failed_when_validation_fails()
    {
        $this->setUpValidationMock(true);

        $this->pageRepositoryMock
            ->shouldReceive('findBySlug')
            ->once()
            ->withArgs(['a-slug'])
            ->andReturn($this->pageMock);

        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['update', $this->pageMock])
            ->andReturn(false);

        $this->requestMock
            ->shouldReceive('input')
            ->times(2);

        $this->pageMock
            ->shouldReceive('getAttribute')
            ->times(2);

        $this->loggerMock
            ->shouldReceive('debug')
            ->once()
            ->withArgs([PageController::class . ' validation failed, unable to update page']);

        $this->jsonApiMock
            ->shouldReceive('respondValidationFailed')
            ->once()
            ->andReturn($this->responseMock);

        $actualResult = $this->pageController->update($this->requestMock, $this->responseMock, 'a-slug');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_update_responds_with_server_error_on_update_exception()
    {
        $this->setUpValidationMock(false);

        $this->pageRepositoryMock
            ->shouldReceive('findBySlug')
            ->once()
            ->withArgs(['a-slug'])
            ->andReturn($this->pageMock);

        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['update', $this->pageMock])
            ->andReturn(false);

        $this->requestMock
            ->shouldReceive('input')
            ->times(2);

        $this->pageMock
            ->shouldReceive('getAttribute')
            ->times(2);

        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->withArgs([PageController::class . ' failed to update a page with exception: an error happened']);

        $this->jsonApiMock
            ->shouldReceive('respondServerError')
            ->once()
            ->withArgs([$this->responseMock, 'Unable to update page'])
            ->andReturn($this->responseMock);

        $this->pageRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->andThrow(new \Exception('an error happened'));

        $actualResult = $this->pageController->update($this->requestMock, $this->responseMock, 'a-slug');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_update_responds_with_resource_on_successful_update()
    {
        $this->setUpValidationMock(false);

        $this->pageRepositoryMock
            ->shouldReceive('findBySlug')
            ->once()
            ->withArgs(['a-slug'])
            ->andReturn($this->pageMock);

        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['update', $this->pageMock])
            ->andReturn(false);

        $this->requestMock
            ->shouldReceive('input')
            ->times(2);

        $this->pageMock
            ->shouldReceive('getAttribute')
            ->times(2);

        $this->pageRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->andReturn($this->pageMock);

        $this->cacheRepositoryMock
            ->shouldReceive('forget')
            ->once()
            ->withArgs(['page.a-slug']);

        $this->cacheRepositoryMock
            ->shouldReceive('keys')
            ->once()
            ->withArgs(['pages*'])
            ->andReturn(['laravel:pages.page1.size5']);

        $this->cacheRepositoryMock
            ->shouldReceive('deleteMultiple')
            ->once()
            ->withArgs([['laravel:pages.page1.size5']]);

        $this->jsonApiMock
            ->shouldReceive('respondResourceUpdated')
            ->once()
            ->withArgs([$this->responseMock, $this->pageMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->pageController->update($this->requestMock, $this->responseMock, 'a-slug');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_delete_responds_not_found_when_page_does_not_exist()
    {
        $this->pageRepositoryMock
            ->shouldReceive('findBySlug')
            ->once()
            ->withArgs(['a-slug'])
            ->andReturn(null);

        $this->loggerMock
            ->shouldReceive('debug')
            ->once()
            ->withArgs([PageController::class . ' unable to find page to delete with slug: a-slug']);

        $this->jsonApiMock
            ->shouldReceive('respondResourceNotFound')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->pageController->delete($this->responseMock, 'a-slug');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_delete_responds_forbidden_when_authorization_fails()
    {
        $this->pageRepositoryMock
            ->shouldReceive('findBySlug')
            ->once()
            ->withArgs(['a-slug'])
            ->andReturn($this->pageMock);

        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['delete', $this->pageMock])
            ->andReturn(true);

        $this->loggerMock
            ->shouldReceive('debug')
            ->once()
            ->withArgs([PageController::class . ' unauthorized to update page with slug: a-slug']);

        $this->jsonApiMock
            ->shouldReceive('respondForbidden')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->pageController->delete($this->responseMock, 'a-slug');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_delete_responds_with_server_error_on_delete_failure()
    {
        $this->pageRepositoryMock
            ->shouldReceive('findBySlug')
            ->once()
            ->withArgs(['a-slug'])
            ->andReturn($this->pageMock);

        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['delete', $this->pageMock])
            ->andReturn(false);

        $this->pageMock
            ->shouldReceive('delete')
            ->once()
            ->andThrow(new \Exception('an error happened'));

        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->withArgs([PageController::class . ' failed to delete a page with exception: an error happened']);

        $this->jsonApiMock
            ->shouldReceive('respondServerError')
            ->once()
            ->withArgs([$this->responseMock, 'Unable to delete page'])
            ->andReturn($this->responseMock);

        $actualResult = $this->pageController->delete($this->responseMock, 'a-slug');
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_delete_responds_that_page_was_deleted()
    {
        $this->pageRepositoryMock
            ->shouldReceive('findBySlug')
            ->once()
            ->withArgs(['a-slug'])
            ->andReturn($this->pageMock);

        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['delete', $this->pageMock])
            ->andReturn(false);

        $this->pageMock
            ->shouldReceive('delete')
            ->once();

        $this->jsonApiMock
            ->shouldReceive('respondResourceDeleted')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $this->cacheRepositoryMock
            ->shouldReceive('forget')
            ->once()
            ->withArgs(['page.a-slug']);

        $this->cacheRepositoryMock
            ->shouldReceive('keys')
            ->once()
            ->withArgs(['pages*'])
            ->andReturn(['laravel:pages.page1.size5']);

        $this->cacheRepositoryMock
            ->shouldReceive('deleteMultiple')
            ->once()
            ->withArgs([['laravel:pages.page1.size5']]);

        $actualResult = $this->pageController->delete($this->responseMock, 'a-slug');
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