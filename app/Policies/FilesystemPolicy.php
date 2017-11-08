<?php

namespace App\Policies;

use App\User;
use Illuminate\Filesystem\Filesystem;

class FilesystemPolicy
{
    /**
     * Determine whether the user can store files.
     *
     * @param User $user
     * @param Filesystem $filesystem
     * @return bool
     */
    public function store(User $user, Filesystem $filesystem)
    {
        return $user->isAdmin();
    }
}
