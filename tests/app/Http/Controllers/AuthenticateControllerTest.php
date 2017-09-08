<?php

use App\Http\Controllers\AuthenticateController;
use App\User;
use Illuminate\Http\Request;

class AuthenticateControllerTest extends \TestCase
{
    /**
     * @var AuthenticateController
     */
    private $authenticateController;

    /**
     * @var Request|\Mockery\Mock
     */
    private $requestMock;

    public function setUp()
    {
        parent::setUp();
        $this->authenticateController = app(AuthenticateController::class);
        $this->requestMock = Mockery::mock(Request::class);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function test_authenticate_invalid_header()
    {
        $this->requestMock->shouldReceive('header')->with('Authorization')->once();
        $actualResponse = $this->authenticateController->authenticate($this->requestMock)->getContent();
        $this->assertThat($actualResponse, $this->equalTo('{"error":"No authorization header set."}'));

        $this->requestMock->shouldReceive('header')->with('Authorization')->once()->andReturn('Bearer');
        $actualResponse = $this->authenticateController->authenticate($this->requestMock)->getContent();
        $this->assertThat($actualResponse, $this->equalTo('{"error":"Authorization header must be of \"Basic\" type."}'));

        $this->requestMock->shouldReceive('header')->with('Authorization')->once()->andReturn('Basic');
        $actualResponse = $this->authenticateController->authenticate($this->requestMock)->getContent();
        $this->assertThat($actualResponse, $this->equalTo('{"error":"Authorization header must have a value"}'));
    }

    public function test_authenticate_user_not_found()
    {
        $this->requestMock->shouldReceive('header')->with('Authorization')->once()->andReturn('Basic TommyMay37@gmail.com:Password1');
        $actualResponse = $this->authenticateController->authenticate($this->requestMock)->getContent();
        $this->assertThat($actualResponse, $this->equalTo('{"error":"Authentication failed."}'));
    }

    public function test_successful_authentication()
    {
        $user = $this->createUser();

        $credentials = $user->getAttribute('email') . ':' . 'ThisPass1';
        $this->requestMock->shouldReceive('header')->with('Authorization')->once()->andReturn("Basic $credentials");
        $actualResponse = $this->authenticateController->authenticate($this->requestMock)->getContent();

        $user = User::find($user->id); // Refresh user
        $userApiToken = $user->getAttribute('api_token');
        $this->assertThat($actualResponse, $this->equalTo("{\"API-Token\":\"$userApiToken\"}"));
    }

    public function test_validate_token_invalid_header()
    {
        $this->requestMock->shouldReceive('header')->with('API-Token')->once();
        $actualResponse = $this->authenticateController->validateToken($this->requestMock);
        $this->assertThat($actualResponse->getContent(), $this->equalTo('{"error":"API-Token header is not set"}'));
        $this->assertThat($actualResponse->getStatusCode(), $this->equalTo(400));
    }

    public function test_validate_token_does_not_exist()
    {
        $this->requestMock->shouldReceive('header')->with('API-Token')->once()->andReturn("NonExistentToken");
        $actualResponse = $this->authenticateController->validateToken($this->requestMock);
        $this->assertThat($actualResponse->getContent(), $this->equalTo('{"invalid":"User could not be authenticated"}'));
        $this->assertThat($actualResponse->getStatusCode(), $this->equalTo(200));
    }

    public function test_validate_token_expired()
    {
        $token = sha1(str_random());
        $this->requestMock->shouldReceive('header')->with('API-Token')->once()->andReturn($token);

        $user = $this->createUser();
        $user->update([
            'api_token'            => $token,
            'api_token_expiration' => (new DateTime())->modify('-1 day')
        ]);

        $actualResponse = $this->authenticateController->validateToken($this->requestMock);
        $this->assertThat($actualResponse->getContent(), $this->equalTo('{"invalid":"API-Token has expired"}'));
        $this->assertThat($actualResponse->getStatusCode(), $this->equalTo(200));
    }

    public function test_successful_validate_token()
    {
        $token = sha1(str_random());
        $this->requestMock->shouldReceive('header')->with('API-Token')->once()->andReturn($token);

        $user = $this->createUser();
        $user->update([
            'api_token'            => $token,
            'api_token_expiration' => (new DateTime())->modify('+1 day')
        ]);

        $actualResponse = $this->authenticateController->validateToken($this->requestMock);
        $this->assertThat($actualResponse->getContent(), $this->equalTo("{\"valid\":\"$token\"}"));
        $this->assertThat($actualResponse->getStatusCode(), $this->equalTo(200));
    }

    private function createUser()
    {
        return $this->keepTryingIntegrityConstraints(function () {
            return factory(User::class, 1)->create()->first();
        });
    }

}