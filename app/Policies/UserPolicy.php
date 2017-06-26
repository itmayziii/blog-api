<?php

namespace App\Policies;

use App\User;

class UserPolicy
{
    /**
     * Determine whether the user can create blogs.
     *
     * @param  User $user
     * @return bool
     */
    public function index(User $user)
    {
        return $user->isAdmin();
    }
}