<?php

use App\Http\Controllers\UserController;
use App\User;
use Illuminate\Http\Request;

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
        $actualResponse = $this->userController->store($request);

        $this->assertThat($actualResponse, $this->equalTo('User Creation Successful'));
    }

    public function test_listing_authorization()
    {
        $this->jsonApiMock->shouldReceive('respondUnauthorized')->once()->andReturn('User Listing Authorization Failed');

        $request = Request::create('v1/users', 'GET');
        $actualResponse = $this->userController->index($request);

        $this->assertThat($actualResponse, $this->equalTo('User Listing Authorization Failed'));
    }

    public function test_listing_successful()
    {
        $this->jsonApiMock->shouldReceive('respondResourcesFound')->once()->andReturn('Users Found');

        $this->actAsAdministrator();

        $request = Request::create('v1/users', 'GET');
        $actualResponse = $this->userController->index($request);

        $this->assertThat($actualResponse, $this->equalTo('Users Found'));
    }

    public function test_delete_authorization()
    {
        $this->jsonApiMock->shouldReceive('respondUnauthorized')->once()->andReturn('User Delete Authorization Failed');

        $user = $this->createUser();
        $actualResponse = $this->userController->delete($user->id);

        $this->assertThat($actualResponse, $this->equalTo('User Delete Authorization Failed'));
    }

    public function test_delete_not_found()
    {
        $this->jsonApiMock->shouldReceive('respondResourceNotFound')->once()->andReturn('User Not Found');

        $this->actAsAdministrator();

        $actualResponse = $this->userController->delete(9483652957912364012356);

        $this->assertThat($actualResponse, $this->equalTo('User Not Found'));
    }

    public function test_show_authorization()
    {
        $this->jsonApiMock->shouldReceive('respondUnauthorized')->once()->andReturn('User Show Authorization Failed');

        $user = $this->createUser();
        $actualResponse = $this->userController->show($user->id);

        $this->assertThat($actualResponse, $this->equalTo('User Show Authorization Failed'));
    }

    public function test_show_not_found()
    {
        $this->jsonApiMock->shouldReceive('respondResourceNotFound')->once()->andReturn('User Not Found');

        $this->actAsAdministrator();

        $actualResponse = $this->userController->show(246182354123046);

        $this->assertThat($actualResponse, $this->equalTo('User Not Found'));
    }

    public function test_show_successful()
    {
        $this->jsonApiMock->shouldReceive('respondResourceFound')->once()->andReturn('User Found');

        $user = $this->createUser();
        $this->actingAs($user);

        $actualResponse = $this->userController->show($user->id);

        $this->assertThat($actualResponse, $this->equalTo('User Found'));
    }

    public function test_update_authorization()
    {
        $this->jsonApiMock->shouldReceive('respondUnauthorized')->once()->andReturn('User Update Authorization Failed');

        $request = Request::create(
            'v1/users',
            'PATCH',
            [
                'first-name' => 'Tommy',
                'last-name'  => 'May',
                'email'      => 'TommyMay37@gmail.com'
            ]);
        $user = $this->createUser();
        $actualResponse = $this->userController->update($request, $user->id);

        $this->assertThat($actualResponse, $this->equalTo('User Update Authorization Failed'));
    }

    public function test_update_not_found()
    {
        $this->jsonApiMock->shouldReceive('respondResourceNotFound')->once()->andReturn('User Update Not Found');

        $user = $this->createUser();
        $this->actingAs($user);

        $request = Request::create(
            'v1/users',
            'PATCH',
            [
                'first-name' => 'Tommy',
                'last-name'  => 'May',
                'email'      => 'TommyMay37@gmail.com'
            ]);

        $actualResponse = $this->userController->update($request, 45986293456234957);

        $this->assertThat($actualResponse, $this->equalTo('User Update Not Found'));
    }

    public function test_update_validation_failed()
    {
        $this->jsonApiMock->shouldReceive('respondValidationFailed')->once()->andReturn('User Update Validation Failed');

        $user = $this->createUser();
        $this->actingAs($user);

        $request = Request::create(
            'v1/users',
            'PATCH',
            [
                'first-name' => '',
                'last-name'  => 'May',
                'email'      => 'TommyMay37@gmail.com'
            ]);

        $actualResponse = $this->userController->update($request, $user->id);

        $this->assertThat($actualResponse, $this->equalTo('User Update Validation Failed'));
    }

    public function test_update_successful()
    {
        $this->jsonApiMock->shouldReceive('respondResourceUpdated')->once()->andReturn('User Update Successful');

        $user = $this->createUser();
        $this->actingAs($user);

        $request = Request::create(
            'v1/users',
            'PATCH',
            [
                'first-name' => 'Tommy',
                'last-name'  => 'May',
                'email'      => 'TommyMay37@gmail.com'
            ]);

        $actualResponse = $this->userController->update($request, $user->id);

        $this->assertThat($actualResponse, $this->equalTo('User Update Successful'));
    }

    private function createUser()
    {
        return $this->keepTryingIntegrityConstraints(function () {
            return factory(User::class, 1)->create()->first();
        });
    }
}