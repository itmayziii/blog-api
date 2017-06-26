<?php

namespace App\Repositories;

use App\User;

class UserRepository
{
    /**
     * @param string $username
     * @param string $password
     * @return User|bool
     */
    public function retrieveUserByCredentials($username, $password)
    {
        $user = (new User())
            ->where('email', $username)
            ->where('password', $password)
            ->get()
            ->first();

        if ($user) {
            return $user;
        } else {
            return false;
        }
    }
}