<?php

namespace App\Policies;

use App\Contact;
use App\User;

class ContactPolicy
{
    /**
     * Determine whether the user can list contacts.
     *
     * @param  \App\User $user
     * @param  \App\Contact $contact
     * @return bool
     */
    public function index(User $user, Contact $contact)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view a specific contact.
     *
     * @param  \App\User $user
     * @param  \App\Contact $contact
     * @return bool
     */
    public function show(User $user, Contact $contact)
    {
        return $user->isAdmin();
    }
}
