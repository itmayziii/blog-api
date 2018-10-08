<?php

namespace Tests\Unit\Repositories;

use App\Repositories\UserRepository;
use App\Models\User;
use Mockery;
use Mockery\Mock;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    /**
     * @var User | Mock
     */
    private $userMock;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function setUp()
    {
        parent::setUp();
        $this->userMock = Mockery::mock(User::class);
        $this->userMock->shouldDeferMissing();
        $this->userMock->password = 'ThisPass1';

        $this->userRepository = new UserRepository($this->userMock);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function test_retrieveUserByCredentials_returns_user_on_valid_username_and_password()
    {
        $this->userMock
            ->shouldReceive('where')
            ->once()
            ->withArgs(['email', 'test@test.com'])
            ->andReturn($this->userMock);

        $this->userMock
            ->shouldReceive('get')
            ->once()
            ->andReturn($this->userMock);

        $this->userMock
            ->shouldReceive('first')
            ->once()
            ->andReturn($this->userMock);

        $actualResult = $this->userRepository->retrieveUserByEmail('test@test.com');
        $expectedResult = $this->userMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_retrieveUserByToken_returns_user()
    {
        $this->userMock
            ->shouldReceive('where')
            ->once()
            ->withArgs(['api_token', 'aaabbbccc'])
            ->andReturn($this->userMock);

        $this->userMock
            ->shouldReceive('get')
            ->once()
            ->andReturn($this->userMock);

        $this->userMock
            ->shouldReceive('first')
            ->once()
            ->andReturn($this->userMock);

        $actualResult = $this->userRepository->retrieveUserByToken('aaabbbccc');
        $expectedResult = $this->userMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }
}