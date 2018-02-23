<?php

namespace Tests\Repositories;

use App\Repositories\UserRepository;
use App\User;
use Illuminate\Contracts\Hashing\Hasher;
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
     * @var Hasher | Mock
     */
    private $hasherMock;
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
        $this->hasherMock = Mockery::mock(Hasher::class);

        $this->userRepository = new UserRepository($this->userMock, $this->hasherMock);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function test_retrieveUserByCredentials_returns_user_on_valid_username_and_password()
    {
        $this->setupUserMock($this->userMock);

        $this->hasherMock
            ->shouldReceive('check')
            ->once()
            ->withArgs(['ThisPass1', 'ThisPass1'])
            ->andReturn(true);

        $actualResult = $this->userRepository->retrieveUserByCredentials('testUser', 'ThisPass1');
        $expectedResult = $this->userMock;

        $this->assertThat($actualResult, $this->equalTo($expectedResult));
    }

    public function test_retrieveUserByCredentials_returns_null_if_no_user()
    {
        $this->setupUserMock(null);

        $actualResult = $this->userRepository->retrieveUserByCredentials('testUser', 'ThisPass1');
        $this->assertThat($actualResult, $this->isNull());
    }

    public function test_retrieveUserByCredentails_returns_null_if_passwords_do_not_match()
    {
        $this->setupUserMock($this->userMock);

        $this->hasherMock
            ->shouldReceive('check')
            ->once()
            ->withArgs(['ThisPass1', 'ThisPass1'])
            ->andReturn(false);

        $actualResult = $this->userRepository->retrieveUserByCredentials('testUser', 'ThisPass1');
        $this->assertThat($actualResult, $this->isNull());
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

    private function setupUserMock($returnValue)
    {
        $this->userMock
            ->shouldReceive('where')
            ->once()
            ->withArgs(['email', 'testUser'])
            ->andReturn($this->userMock);

        $this->userMock
            ->shouldReceive('get')
            ->once()
            ->andReturn($this->userMock);

        $this->userMock
            ->shouldReceive('first')
            ->once()
            ->andReturn($returnValue);
    }
}