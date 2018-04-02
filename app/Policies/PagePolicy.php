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

    /**
     * Determine if a user can view all pages, or only live pages.
     *
     * @param User $user
     * @param Page $page
     *
     * @return bool
     */
    public function showPage(User $user, Page $page)
    {
        return $user->isAdmin() || $page->getAttribute('is_live') == true;
    }

    /**
     * Determine if a user can create a page.
     *
     * @param User $user
     * @param Page $page
     *
     * @return bool
     */
    public function store(User $user, Page $page)
    {
        return $user->isAdmin();
    }

    /**
     * Determine if a user can update a page.
     *
     * @param User $user
     * @param Page $page
     *
     * @return bool
     */
    public function update(User $user, Page $page)
    {
        return $user->isAdmin();
    }

    /**
     * Determine if a user can delete a page.
     *
     * @param User $user
     * @param Page $page
     *
     * @return bool
     */
    public function delete(User $user, Page $page)
    {
        return $user->isAdmin();
    }
}