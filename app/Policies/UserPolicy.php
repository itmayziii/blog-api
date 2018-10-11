<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can list users.
     *
     * @param  User $user
     *
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
     *
     * @return bool
     */
    public function delete(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can create a user.
     *
     * @param  User $user
     *
     * @return bool
     */
    public function store(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update users.
     *
     * @param  User $user
     *
     * @return bool
     */
    public function update(User $user)
    {
        return $user->isAdmin() || $user->isUser($user);
    }

    /**
     * Determine whether the user can show a specific user.
     *
     * @param User $loggedInUser
     * @param User $user
     *
     * @return bool
     */
    public function show(User $loggedInUser, User $user)
    {
        return $loggedInUser->isAdmin() || $loggedInUser->isUser($user);
    }
}
