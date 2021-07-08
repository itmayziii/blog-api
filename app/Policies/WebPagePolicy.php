<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WebPage;

class WebPagePolicy
{
    /**
     * Determine whether the user can create web pages
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
     * Determine whether the user can update web pages
     *
     * @param User $user
     * @param WebPage $webPage
     *
     * @return bool
     */
    public function update(User $user, WebPage $webPage)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete web pages
     *
     * @param User $user
     * @param WebPage $webPage
     *
     * @return bool
     */
    public function delete(User $user, WebPage $webPage)
    {
        return $user->isAdmin();
    }

    /**
     * Determine if a user can view all web pages, or only live ones
     *
     * @param User $user
     *
     * @return bool
     */
    public function indexAllWebPages(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Determine if a user can show a web pages
     *
     * @param User $user
     * @param WebPage $webPage
     *
     * @return bool
     */
    public function show(User $user, WebPage $webPage)
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $webPage->getAttribute('is_live');
    }
}
