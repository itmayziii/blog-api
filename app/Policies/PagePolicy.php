<?php

namespace App\Policies;

use App\Page;
use App\User;

class PagePolicy
{
    /**
     * Determine if a user can view all pages, or only live pages.
     *
     * @param User $user
     * @param Page $page
     *
     * @return bool
     */
    public function indexAllPages(User $user, Page $page)
    {
        return $user->isAdmin();
    }
}