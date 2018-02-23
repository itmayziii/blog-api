<?php

namespace App\Repositories;

use App\User;
use Illuminate\Contracts\Hashing\Hasher;

class UserRepository
{
    /**
     * @var User
     */
    private $user;
    /**
     * @var Hasher
     */
    private $hasher;

    public function __construct(User $user, Hasher $hasher)
    {
        $this->user = $user;
        $this->hasher = $hasher;
    }

    /**
     * @param string $username
     * @param string $password
     *
     * @return User|null
     */
    public function retrieveUserByCredentials($username, $password)
    {
        $user = $this->user
            ->where('email', $username)
            ->get()
            ->first();

        if (is_null($user)) {
            return null;
        }

        $isPasswordValid = $this->hasher->check($password, $user->password);
        if ($isPasswordValid === true) {
            return $user;
        }

        return null;
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