<?php

namespace App\Policies;

use App\Blog;
use App\User;

class BlogPolicy
{
    /**
     * Determine whether the user can create blogs.
     *
     * @param  User $user
     * @param  Blog $blog
     * @return bool
     */
    public function store(User $user, Blog $blog)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update blogs.
     *
     * @param User $user
     * @param Blog $blog
     * @return bool
     */
    public function update(User $user, Blog $blog)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete blogs.
     *
     * @param User $user
     * @param Blog $blog
     * @return bool
     */
    public function delete(User $user, Blog $blog)
    {
        return $user->isAdmin();
    }
}