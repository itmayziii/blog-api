<?php

namespace App\Policies;

use App\Category;
use App\User;

class CategoryPolicy
{
    /**
     * Determine whether the user can view a specific contact.
     *
     * @param  User $user
     * @param  Category $category
     * @return bool
     */
    public function store(User $user, Category $category)
    {
        return $user->isAdmin();
    }
}