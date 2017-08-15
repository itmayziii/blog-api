<?php

use App\Repositories\UserRepository;
use App\User;

class UserRepositoryTest extends \TestCase
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function setUp()
    {
        parent::setUp();
        $this->userRepository = new UserRepository(app('hash'));
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function test_retrieve_user_by_credentials()
    {
        $user = $this->userRepository->retrieveUserByCredentials('ThisDoesNotExist@example.com', 'NonsensePassword123');
        $this->assertThat($user, $this->equalTo(false));

        $newUser = $this->createUser();
        $user = $this->userRepository->retrieveUserByCredentials($newUser->email, 'ThisPass1');
        $this->assertThat($user, $this->isInstanceOf(User::class));
    }

    private function createUser()
    {
        return $this->keepTryingIntegrityConstraints(function () {
            return factory(User::class, 1)->create()->first();
        });
    }
}