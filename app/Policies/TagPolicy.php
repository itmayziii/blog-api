<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;

class TagPolicy
{
    /**
     * Determine whether the user can create a specific tag.
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
     * Determine whether the user can update a specific tag.
     *
     * @param  User $user
     * @param  Tag $tag
     *
     * @return bool
     */
    public function update(User $user, Tag $tag)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete a specific tag.
     *
     * @param  User $user
     * @param  Tag $tag
     *
     * @return bool
     */
    public function delete(User $user, Tag $tag)
    {
        return $user->isAdmin();
    }

}
