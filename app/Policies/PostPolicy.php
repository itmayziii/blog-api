<?php

namespace App\Policies;

use App\Post;
use App\User;

class PostPolicy
{
    /**
     * Determine whether the user can create posts.
     *
     * @param  User $user
     * @param  Post $post
     *
     * @return bool
     */
    public function store(User $user, Post $post)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update posts.
     *
     * @param User $user
     * @param Post $post
     *
     * @return bool
     */
    public function update(User $user, Post $post)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete posts.
     *
     * @param User $user
     * @param Post $post
     *
     * @return bool
     */
    public function delete(User $user, Post $post)
    {
        return $user->isAdmin();
    }

    /**
     * Determine if a user can view all posts, or only live posts.
     *
     * @param User $user
     * @param Post $post
     *
     * @return bool
     */
    public function indexAllPosts(User $user, Post $post)
    {
        return $user->isAdmin();
    }

    /**
     * Determine if a user can show all posts, or only live posts.
     *
     * @param User $user
     * @param Post $post
     *
     * @return bool
     */
    public function showAllPosts(User $user, Post $post)
    {
        return $user->isAdmin();
    }
}