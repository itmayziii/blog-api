<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;

class ContactPolicy
{
    /**
     * Determine whether the user can list contacts.
     *
     * @param  \App\Models\User $user
     *
     * @return bool
     */
    public function index(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view a specific contact.
     *
     * @param  \App\Models\User $user
     * @param  \App\Models\Contact $contact
     *
     * @return bool
     */
    public function show(User $user, Contact $contact)
    {
        return $user->isAdmin();
    }
}
