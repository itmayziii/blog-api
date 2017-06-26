<?php

namespace App\Policies;

use App\User;

class UserPolicy
{
    /**
     * Determine whether the user can list users.
     *
     * @param  User $user
     * @return bool
     */
    public function index(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete users.
     *
     * @param  User $user
     * @return bool
     */
    public function delete(User $user)
    {
        return $user->isAdmin();
    }
}