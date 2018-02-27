<?php

namespace App\Repositories;

use App\User;

class UserRepository
{
    /**
     * @var User
     */
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @param string $username
     *
     * @return User|null
     */
    public function retrieveUserByEmail($username)
    {
        $user = $this->user
            ->where('email', $username)
            ->get()
            ->first();

        return $user;
    }

    /**
     * @param $apiToken
     *
     * @return User|null
     */
    public function retrieveUserByToken($apiToken)
    {
        $user = $this->user
            ->where('api_token', $apiToken)
            ->get()
            ->first();

        return $user;
    }
}