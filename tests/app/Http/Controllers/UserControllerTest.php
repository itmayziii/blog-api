<?php
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;

/**
 * Created by IntelliJ IDEA.
 * User: txm241
 * Date: 6/25/17
 * Time: 11:40 PM
 */
class UserControllerTest extends \TestCase
{
    /**
     * @var UserController
     */
    private $userController;

    public function setUp()
    {
        parent::setUp();
        $this->userController = new UserController($this->jsonApiMock);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function test_create_validation_failed()
    {
        $this->jsonApiMock->shouldReceive('respondValidationFailed')->once()->andReturn('User Create Validation Failed');

        $request = Request::create(
            'v1/users',
            'POST',
            [
                'first-name'            => 'Tommy',
                'last-name'             => 'May',
                'email'                 => 'TommyMay37@gmail.com',
                'password'              => 'MyPassword1', // Passwords should match
                'password_confirmation' => 'MyPassword2'
            ]);
        $response = $this->userController->store($request);

        $this->assertThat($response, $this->equalTo('User Create Validation Failed'));
    }

    public function test_successful_creation()
    {
        $this->jsonApiMock->shouldReceive('respondResourceCreated')->once()->andReturn('User Creation Successful');

        $request = Request::create(
            'v1/users',
            'POST',
            [
                'first-name'            => 'Tommy',
                'last-name'             => 'May',
                'email'                 => 'TommyMay37@gmail.com',
                'password'              => 'MyPassword1',
                'password_confirmation' => 'MyPassword1'
            ]);
        $response = $this->userController->store($request);

        $this->assertThat($response, $this->equalTo('User Creation Successful'));
    }
}