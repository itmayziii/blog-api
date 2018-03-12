<?php

namespace Tests\Http\Controllers;

use App\Http\Controllers\AuthenticateController;
use App\Http\JsonApi;
use App\Repositories\UserRepository;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery;
use Mockery\Mock;
use Psr\Log\LoggerInterface;
use Tests\TestCase;

class AuthenticateControllerTest extends TestCase
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
     * @var UserRepository | Mock
     */
    private $userRepositoryMock;
    /**
     * @var LoggerInterface | Mock
     */
    private $loggerMock;
    /**
     * @var Hasher | Mock
     */
    private $hasherMock;
    /**
     * @var User | Mock
     */
    private $userMock;
    /**
     * @var Carbon | Mock
     */
    private $carbonMock;
    /**
     * @var AuthenticateController
     */
    private $authenticateController;
    /**
     * @var ConfigRepository | Mock
     */
    private $configRepositoryMock;

    public function setUp()
    {
        parent::setUp();
        $this->requestMock = Mockery::mock(Request::class);
        $this->responseMock = Mockery::mock(Response::class);
        $this->jsonApiMock = Mockery::mock(JsonApi::class);
        $this->userRepositoryMock = Mockery::mock(UserRepository::class);
        $this->loggerMock = Mockery::mock(LoggerInterface::class);
        $this->hasherMock = Mockery::mock(Hasher::class);
        $this->userMock = Mockery::mock(User::class);
        $this->carbonMock = Mockery::mock(Carbon::class);
        $this->configRepositoryMock = Mockery::mock(ConfigRepository::class);
        $this->authenticateController = new AuthenticateController(
            $this->jsonApiMock,
            $this->userRepositoryMock,
            $this->loggerMock,
            $this->hasherMock,
            $this->configRepositoryMock
        );
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function test_authenticate_responds_bad_request_if_missing_authorization_header_values()
    {
        $this->requestMock
            ->shouldReceive('header')
            ->once()
            ->withArgs(['Authorization'])
            ->andReturn(null);

        $this->jsonApiMock
            ->shouldReceive('respondBadRequest')
            ->once()
            ->withArgs([$this->responseMock, ['Authorization header must have a type and value defined.']])
            ->andReturn($this->responseMock);

        $actualResult = $this->authenticateController->authenticate($this->requestMock, $this->responseMock, $this->carbonMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_authenticate_checks_for_basic_type_and_valid_username_and_password()
    {
        $this->requestMock
            ->shouldReceive('header')
            ->once()
            ->withArgs(['Authorization'])
            ->andReturn('NotBasic test');

        $this->jsonApiMock
            ->shouldReceive('respondBadRequest')
            ->once()
            ->withArgs([$this->responseMock, ['Authorization header must be of "Basic" type.', 'Authorization header value has an invalid username:password format.']])
            ->andReturn($this->responseMock);

        $actualResult = $this->authenticateController->authenticate($this->requestMock, $this->responseMock, $this->carbonMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_authenticate_responds_not_found_if_user_does_not_exist()
    {
        $this->requestMock
            ->shouldReceive('header')
            ->once()
            ->withArgs(['Authorization'])
            ->andReturn('Basic dGVzdEB0ZXN0LmNvbTpUaGlzUGFzczE=');

        $this->userRepositoryMock
            ->shouldReceive('retrieveUserByEmail')
            ->once()
            ->withArgs(['test@test.com'])
            ->andReturn(null);

        $this->jsonApiMock
            ->shouldReceive('respondResourceNotFound')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->authenticateController->authenticate($this->requestMock, $this->responseMock, $this->carbonMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_authenticate_responds_forbidden_if_password_does_not_match()
    {
        $this->requestMock
            ->shouldReceive('header')
            ->once()
            ->withArgs(['Authorization'])
            ->andReturn('Basic dGVzdEB0ZXN0LmNvbTpUaGlzUGFzczE=');

        $this->userRepositoryMock
            ->shouldReceive('retrieveUserByEmail')
            ->once()
            ->withArgs(['test@test.com'])
            ->andReturn($this->userMock);

        $this->jsonApiMock
            ->shouldReceive('respondUnauthorized')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $this->userMock
            ->shouldReceive('getAttribute')
            ->once()
            ->withArgs(['password'])
            ->andReturn('ThisPass1');

        $this->hasherMock
            ->shouldReceive('check')
            ->once()
            ->withArgs(['ThisPass1', 'ThisPass1'])
            ->andReturn(false);

        $actualResult = $this->authenticateController->authenticate($this->requestMock, $this->responseMock, $this->carbonMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_authenticate_responds_with_a_server_error_if_user_fails_to_save()
    {
        $this->requestMock
            ->shouldReceive('header')
            ->once()
            ->withArgs(['Authorization'])
            ->andReturn('Basic dGVzdEB0ZXN0LmNvbTpUaGlzUGFzczE=');

        $this->userRepositoryMock
            ->shouldReceive('retrieveUserByEmail')
            ->once()
            ->withArgs(['test@test.com'])
            ->andReturn($this->userMock);

        $this->jsonApiMock
            ->shouldReceive('respondServerError')
            ->once()
            ->withArgs([$this->responseMock, 'Unable to save user with new token.'])
            ->andReturn($this->responseMock);

        $this->userMock
            ->shouldReceive('getAttribute')
            ->once()
            ->withArgs(['password'])
            ->andReturn('ThisPass1');

        $this->userMock
            ->shouldReceive('setAttribute')
            ->twice();

        $this->userMock
            ->shouldReceive('save')
            ->once()
            ->andThrow(new Exception('an error occurred'));

        $this->carbonMock
            ->shouldReceive('copy')
            ->once()
            ->andReturn($this->carbonMock);

        $this->carbonMock
            ->shouldReceive('addDay')
            ->once();

        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->withArgs(['Failed to update user with API Token with exception: an error occurred']);

        $this->hasherMock
            ->shouldReceive('check')
            ->once()
            ->withArgs(['ThisPass1', 'ThisPass1'])
            ->andReturn(true);

        $actualResult = $this->authenticateController->authenticate($this->requestMock, $this->responseMock, $this->carbonMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_authenticate_responds_successful_authentication_when_username_and_password_are_valid()
    {
        $this->requestMock
            ->shouldReceive('header')
            ->once()
            ->withArgs(['Authorization'])
            ->andReturn('Basic dGVzdEB0ZXN0LmNvbTpUaGlzUGFzczE=');

        $this->userRepositoryMock
            ->shouldReceive('retrieveUserByEmail')
            ->once()
            ->withArgs(['test@test.com'])
            ->andReturn($this->userMock);

        $this->jsonApiMock
            ->shouldReceive('respondResourceCreated')
            ->once()
            ->andReturn($this->responseMock);

        $this->userMock
            ->shouldReceive('getAttribute')
            ->once()
            ->withArgs(['password'])
            ->andReturn('ThisPass1');

        $this->userMock
            ->shouldReceive('setAttribute')
            ->twice();

        $this->userMock
            ->shouldReceive('save')
            ->once();

        $this->carbonMock
            ->shouldReceive('copy')
            ->once()
            ->andReturn($this->carbonMock);

        $this->carbonMock
            ->shouldReceive('addDay')
            ->once()
            ->andReturn(86400);

        $this->hasherMock
            ->shouldReceive('check')
            ->once()
            ->withArgs(['ThisPass1', 'ThisPass1'])
            ->andReturn(true);

        $this->configRepositoryMock
            ->shouldReceive('get')
            ->once()
            ->withArgs(['cookies'])
            ->andReturn([
                'domain' => 'localhost',
                'secure' => false,
                'http_only' => false
            ]);

        $this->responseMock
            ->shouldReceive('withCookie')
            ->once()
            ->withAnyArgs()
            ->andReturn($this->responseMock);

        $actualResult = $this->authenticateController->authenticate($this->requestMock, $this->responseMock, $this->carbonMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_validateToken_responds_bad_request_if_api_token_header_or_cookie_is_not_set()
    {
        $this->requestMock
            ->shouldReceive('hasHeader')
            ->once()
            ->withArgs(['API-Token'])
            ->andReturn(false);

        $this->requestMock
            ->shouldReceive('cookie')
            ->once()
            ->withArgs(['API-Token'])
            ->andReturn(null);

        $this->jsonApiMock
            ->shouldReceive('respondBadRequest')
            ->once()
            ->withArgs([$this->responseMock, 'Neither API-Token header or cookie is set.'])
            ->andReturn($this->responseMock);

        $actualResult = $this->authenticateController->validateToken($this->requestMock, $this->responseMock, $this->carbonMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_validateToken_responds_unauthorized_if_user_is_not_found()
    {
        $this->requestMock
            ->shouldReceive('hasHeader')
            ->once()
            ->withArgs(['API-Token'])
            ->andReturn(true);

        $this->requestMock
            ->shouldReceive('header')
            ->once()
            ->withArgs(['API-Token'])
            ->andReturn('a1b2c3');

        $this->userRepositoryMock
            ->shouldReceive('retrieveUserByToken')
            ->once()
            ->withArgs(['a1b2c3'])
            ->andReturn(null);

        $this->jsonApiMock
            ->shouldReceive('respondUnauthorized')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->authenticateController->validateToken($this->requestMock, $this->responseMock, $this->carbonMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_validateToken_responds_unauthorized_if_token_is_expired()
    {
        $this->requestMock
            ->shouldReceive('hasHeader')
            ->once()
            ->withArgs(['API-Token'])
            ->andReturn(false);

        $this->requestMock
            ->shouldReceive('cookie')
            ->once()
            ->withArgs(['API-Token'])
            ->andReturn('a1b2c3');

        $this->userRepositoryMock
            ->shouldReceive('retrieveUserByToken')
            ->once()
            ->withArgs(['a1b2c3'])
            ->andReturn($this->userMock);

        $this->userMock
            ->shouldReceive('getAttribute')
            ->once()
            ->withArgs(['api_token_expiration'])
            ->andReturn('02/26/2018');

        $this->carbonMock
            ->shouldReceive('getTimestamp')
            ->once()
            ->andReturn('1519603300');

        $this->jsonApiMock
            ->shouldReceive('respondUnauthorized')
            ->once()
            ->withArgs([$this->responseMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->authenticateController->validateToken($this->requestMock, $this->responseMock, $this->carbonMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_validateToken_responds_with_user_if_token_is_valid()
    {
        $this->requestMock
            ->shouldReceive('hasHeader')
            ->once()
            ->withArgs(['API-Token'])
            ->andReturn(true);

        $this->requestMock
            ->shouldReceive('header')
            ->once()
            ->withArgs(['API-Token'])
            ->andReturn('a1b2c3');

        $this->userRepositoryMock
            ->shouldReceive('retrieveUserByToken')
            ->once()
            ->withArgs(['a1b2c3'])
            ->andReturn($this->userMock);

        $this->userMock
            ->shouldReceive('getAttribute')
            ->once()
            ->withArgs(['api_token_expiration'])
            ->andReturn('02/26/2018');

        $this->carbonMock
            ->shouldReceive('getTimestamp')
            ->once()
            ->andReturn('1519603000');

        $this->jsonApiMock
            ->shouldReceive('respondResourceFound')
            ->once()
            ->withArgs([$this->responseMock, $this->userMock])
            ->andReturn($this->responseMock);

        $actualResult = $this->authenticateController->validateToken($this->requestMock, $this->responseMock, $this->carbonMock);
        $expectedResult = $this->responseMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }
}