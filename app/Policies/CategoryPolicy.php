<?php

namespace App\Policies;

use App\Category;
use App\User;

class CategoryPolicy
{
    /**
     * Determine whether the user can create a specific category.
     *
     * @param  User $user
     * @param  Category $category
     * @return bool
     */
    public function store(User $user, Category $category)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update a specific category.
     *
     * @param  User $user
     * @param  Category $category
     * @return bool
     */
    public function update(User $user, Category $category)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete a specific category.
     *
     * @param  User $user
     * @param  Category $category
     * @return bool
     */
    public function delete(User $user, Category $category)
    {
        return $user->isAdmin();
    }

}