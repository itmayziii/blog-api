<?php

namespace App\Repositories;

use App\User;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Facades\Log;

class UserRepository
{
    /**
     * @var Hasher
     */
    private $hasher;

    public function __construct(Hasher $hasher)
    {
        $this->hasher = $hasher;
    }

    /**
     * @param string $username
     * @param string $password
     * @return User|bool
     */
    public function retrieveUserByCredentials($username, $password)
    {
        $user = (new User())
            ->where('email', $username)
            ->get()
            ->first();

        $successfulAuthentication = false;
        if ($user) {
            Log::info(print_r($user));
            if ($this->hasher->check($password, $user->password)) {
                $successfulAuthentication = true;
            }
        }

        if ($successfulAuthentication) {
            return $user;
        } else {
            return false;
        }
    }
}