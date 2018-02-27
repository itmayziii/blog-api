<?php

namespace Tests\app\Http\Controllers;

use App\Contact;
use App\Http\Controllers\ContactController;
use App\Http\JsonApi;
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

class ContactControllerTest extends TestCase
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
     * @var Contact | Mock
     */
    private $contactMock;
    /**
     * @var LengthAwarePaginator | Mock
     */
    private $paginatorMock;
    /**
     * @var ContactController
     */
    private $contactController;

    public function setUp()
    {
        parent::setUp();
        $this->requestMock = Mockery::mock(Request::class);
        $this->responseMock = Mockery::mock(Response::class);
        $this->jsonApiMock = Mockery::mock(JsonApi::class);
        $this->gateMock = Mockery::mock(Gate::class);
        $this->validationFactoryMock = Mockery::mock(ValidationFactory::class);
        $this->validatorMock = Mockery::mock(Validator::class);
        $this->messageBagMock = Mockery::mock(MessageBag::class);
        $this->loggerMock = Mockery::mock(LoggerInterface::class);
        $this->contactMock = Mockery::mock(Contact::class);
        $this->paginatorMock = Mockery::mock(LengthAwarePaginator::class);

        $this->contactController = new ContactController($this->jsonApiMock, $this->gateMock, $this->loggerMock, $this->validationFactoryMock);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function test_index_responds_forbidden_if_user_is_not_allowed()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['index', $this->contactMock])
            ->andReturn(true);

        $this->jsonApiMock
            ->shouldReceive('respondForbidden')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->contactController->index($this->requestMock, $this->responseMock, $this->contactMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_index_returns_contacts()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['index', $this->contactMock])
            ->andReturn(false);

        $this->requestMock
            ->shouldReceive('query')
            ->once()
            ->withArgs(['size', 15])
            ->andReturn(20);

        $this->requestMock
            ->shouldReceive('query')
            ->once()
            ->withArgs(['page', 1])
            ->andReturn(3);

        $this->contactMock
            ->shouldReceive('orderBy')
            ->once()
            ->withArgs(['created_at', 'desc'])
            ->andReturn($this->contactMock);

        $this->contactMock
            ->shouldReceive('paginate')
            ->once()
            ->withArgs([20, null, 'page', 3])
            ->andReturn($this->paginatorMock);

        $this->jsonApiMock
            ->shouldReceive('respondResourcesFound')
            ->once()
            ->withArgs([$this->responseMock, $this->paginatorMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->contactController->index($this->requestMock, $this->responseMock, $this->contactMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_store_responds_validation_failed_on_validation_failure()
    {
        $this->setUpValidationMock(true);

        $this->jsonApiMock
            ->shouldReceive('respondValidationFailed')
            ->once()
            ->andReturn($this->responseMock);

        $actualResult = $this->contactController->store($this->requestMock, $this->responseMock, $this->contactMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_store_responds_with_server_error_when_contact_will_not_create()
    {
        $this->setUpValidationMock(false);

        $this->contactMock
            ->shouldReceive('create')
            ->once()
            ->andThrow(new \Exception('an error occurred'));

        $this->requestMock
            ->shouldReceive('input')
            ->times(4);

        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->withArgs(['Failed to create contact with exception: an error occurred']);

        $this->jsonApiMock
            ->shouldReceive('respondServerError')
            ->once()
            ->withArgs([$this->responseMock, 'Unable to create contact.'])
            ->andReturn($this->responseMock);

        $actualResult = $this->contactController->store($this->requestMock, $this->responseMock, $this->contactMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_store_responds_contact_was_created_when_everything_is_successful()
    {
        $this->setUpValidationMock(false);

        $this->contactMock
            ->shouldReceive('create')
            ->once()
            ->andReturn($this->contactMock);

        $this->requestMock
            ->shouldReceive('input')
            ->times(4);

        $this->jsonApiMock
            ->shouldReceive('respondResourceCreated')
            ->once()
            ->withArgs([$this->responseMock, $this->contactMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->contactController->store($this->requestMock, $this->responseMock, $this->contactMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_show_responds_forbidden_if_authorization_fails()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['show', $this->contactMock])
            ->andReturn(true);

        $this->jsonApiMock
            ->shouldReceive('respondForbidden')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->contactController->show($this->responseMock, $this->contactMock, 2);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_show_responds_not_found_if_contact_does_not_exist()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['show', $this->contactMock])
            ->andReturn(false);

        $this->contactMock
            ->shouldReceive('find')
            ->once()
            ->withArgs([2])
            ->andReturn(null);

        $this->jsonApiMock
            ->shouldReceive('respondResourceNotFound')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->contactController->show($this->responseMock, $this->contactMock, 2);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_show_with_contact_if_everything_is_successful()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['show', $this->contactMock])
            ->andReturn(false);

        $this->contactMock
            ->shouldReceive('find')
            ->once()
            ->withArgs([2])
            ->andReturn($this->contactMock);

        $this->jsonApiMock
            ->shouldReceive('respondResourceFound')
            ->once()
            ->withArgs([$this->responseMock, $this->contactMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->contactController->show($this->responseMock, $this->contactMock, 2);
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