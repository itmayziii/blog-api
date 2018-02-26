<?php

namespace Tests\app\Http\Controllers;

use App\Http\Controllers\UserController;
use App\Http\JsonApi;
use App\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Hashing\Hasher;
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

class UserControllerTest extends TestCase
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
     * @var LoggerInterface | Mock
     */
    private $loggerMock;
    /**
     * @var Gate | Mock
     */
    private $gateMock;
    /**
     * @var User | Mock
     */
    private $userMock;
    /**
     * @var UserController | Mock
     */
    private $userController;
    /**
     * @var LengthAwarePaginator | Mock
     */
    private $paginatorMock;
    /**
     * @var ValidationFactory | Mock
     */
    private $validationFactoryMock;
    /**
     * @var Validator | Mock
     */
    private $validatorMock;
    /**
     * @var MessageBag | Mock
     */
    private $messageBagMock;
    /**
     * @var Hasher | Mock
     */
    private $hasherMock;

    public function setUp()
    {
        parent::setUp();
        $this->requestMock = Mockery::mock(Request::class);
        $this->responseMock = Mockery::mock(Response::class);
        $this->jsonApiMock = Mockery::mock(JsonApi::class);
        $this->loggerMock = Mockery::mock(LoggerInterface::class);
        $this->gateMock = Mockery::mock(Gate::class);
        $this->userMock = Mockery::mock(User::class);
        $this->paginatorMock = Mockery::mock(LengthAwarePaginator::class);
        $this->validationFactoryMock = Mockery::mock(ValidationFactory::class);
        $this->validatorMock = Mockery::mock(Validator::class);
        $this->messageBagMock = Mockery::mock(MessageBag::class);
        $this->hasherMock = Mockery::mock(Hasher::class);

        $this->userController = new UserController($this->jsonApiMock, $this->validationFactoryMock, $this->gateMock, $this->loggerMock, $this->hasherMock);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function test_index_responds_forbidden_when_unauthorized()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['index', $this->userMock])
            ->andReturn(true);

        $this->jsonApiMock
            ->shouldReceive('respondForbidden')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->userController->index($this->requestMock, $this->responseMock, $this->userMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_index_responds_with_users()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['index', $this->userMock])
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
            ->andReturn(2);

        $this->jsonApiMock
            ->shouldReceive('respondResourcesFound')
            ->once()
            ->withArgs([$this->responseMock, $this->paginatorMock])
            ->andReturn($this->responseMock);

        $this->userMock
            ->shouldReceive('orderBy')
            ->once()
            ->withArgs(['created_at', 'desc'])
            ->andReturn($this->userMock);

        $this->userMock
            ->shouldReceive('paginate')
            ->once()
            ->withArgs([20, null, 'page', 2])
            ->andReturn($this->paginatorMock);

        $actualResult = $this->userController->index($this->requestMock, $this->responseMock, $this->userMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_show_responds_forbidden_if_user_is_not_authorized()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['show', $this->userMock])
            ->andReturn(true);

        $this->jsonApiMock
            ->shouldReceive('respondForbidden')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->userController->show($this->responseMock, $this->userMock, 3);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_show_responds_not_found_if_user_does_not_exist()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['show', $this->userMock])
            ->andReturn(false);

        $this->userMock
            ->shouldReceive('find')
            ->once()
            ->withArgs([3])
            ->andReturn(null);

        $this->jsonApiMock
            ->shouldReceive('respondResourceNotFound')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->userController->show($this->responseMock, $this->userMock, 3);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_show_responds_with_user()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['show', $this->userMock])
            ->andReturn(false);

        $this->userMock
            ->shouldReceive('find')
            ->once()
            ->withArgs([3])
            ->andReturn($this->userMock);

        $this->jsonApiMock
            ->shouldReceive('respondResourceFound')
            ->once()
            ->withArgs([$this->responseMock, $this->userMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->userController->show($this->responseMock, $this->userMock, 3);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_store_responds_validation_failed_if_validation_failed()
    {
        $this->setUpValidationMock(true);

        $this->jsonApiMock
            ->shouldReceive('respondValidationFailed')
            ->once()
            ->andReturn($this->responseMock);

        $actualResult = $this->userController->store($this->requestMock, $this->responseMock, $this->userMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_store_responds_server_error_if_user_fails_to_create()
    {
        $this->setUpValidationMock(false);

        $this->userMock
            ->shouldReceive('create')
            ->once()
            ->andThrow(new \Exception('an error occurred'));

        $this->requestMock
            ->shouldReceive('input')
            ->times(4);

        $this->hasherMock
            ->shouldReceive('make')
            ->once();

        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->withArgs(['Failed to create a user with exception: an error occurred']);

        $this->jsonApiMock
            ->shouldReceive('respondServerError')
            ->once()
            ->withArgs([$this->responseMock, 'Unable to create the user.'])
            ->andReturn($this->responseMock);

        $actualResult = $this->userController->store($this->requestMock, $this->responseMock, $this->userMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_store_responds_user_created_if_everything_is_successful()
    {
        $this->setUpValidationMock(false);

        $this->userMock
            ->shouldReceive('create')
            ->once()
            ->andReturn($this->userMock);

        $this->requestMock
            ->shouldReceive('input')
            ->times(4);

        $this->hasherMock
            ->shouldReceive('make')
            ->once();

        $this->jsonApiMock
            ->shouldReceive('respondResourceCreated')
            ->once()
            ->withArgs([$this->responseMock, $this->userMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->userController->store($this->requestMock, $this->responseMock, $this->userMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_update_responds_forbidden_if_user_is_not_authorized()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['update', $this->userMock])
            ->andReturn(true);

        $this->jsonApiMock
            ->shouldReceive('respondForbidden')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->userController->update($this->requestMock, $this->responseMock, $this->userMock, 2);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_update_responds_not_found_if_user_does_not_exist()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['update', $this->userMock])
            ->andReturn(false);

        $this->userMock
            ->shouldReceive('find')
            ->once()
            ->withArgs([2])
            ->andReturn(null);

        $this->jsonApiMock
            ->shouldReceive('respondResourceNotFound')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->userController->update($this->requestMock, $this->responseMock, $this->userMock, 2);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_update_responds_validation_failed_if_validation_fails()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['update', $this->userMock])
            ->andReturn(false);

        $this->userMock
            ->shouldReceive('find')
            ->once()
            ->withArgs([2])
            ->andReturn($this->userMock);

        $this->setUpValidationMock(true);

        $this->jsonApiMock
            ->shouldReceive('respondValidationFailed')
            ->once()
            ->andReturn($this->responseMock);

        $actualResult = $this->userController->update($this->requestMock, $this->responseMock, $this->userMock, 2);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_update_responds_with_server_error_if_user_fails_to_update()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['update', $this->userMock])
            ->andReturn(false);

        $this->userMock
            ->shouldReceive('find')
            ->once()
            ->withArgs([2])
            ->andReturn($this->userMock);

        $this->setUpValidationMock(false);

        $this->userMock
            ->shouldReceive('update')
            ->once()
            ->andThrow(new \Exception('an error occurred'));

        $this->requestMock
            ->shouldReceive('input')
            ->times(3);

        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->withArgs(['Failed to update a user with exception: an error occurred']);

        $this->jsonApiMock
            ->shouldReceive('respondServerError')
            ->once()
            ->withArgs([$this->responseMock, 'Unable to update user.'])
            ->andReturn($this->responseMock);

        $actualResult = $this->userController->update($this->requestMock, $this->responseMock, $this->userMock, 2);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_update_responds_with_user()
    {
        $this->gateMock
            ->shouldReceive('denies')
            ->once()
            ->withArgs(['update', $this->userMock])
            ->andReturn(false);

        $this->userMock
            ->shouldReceive('find')
            ->once()
            ->withArgs([2])
            ->andReturn($this->userMock);

        $this->setUpValidationMock(false);

        $this->userMock
            ->shouldReceive('update')
            ->once()
            ->andReturn($this->userMock);

        $this->requestMock
            ->shouldReceive('input')
            ->times(3);

        $this->jsonApiMock
            ->shouldReceive('respondResourceUpdated')
            ->once()
            ->withArgs([$this->responseMock, $this->userMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->userController->update($this->requestMock, $this->responseMock, $this->userMock, 2);
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