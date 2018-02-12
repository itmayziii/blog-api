<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use itmayziii\Laravel\Contracts\JsonApiModelInterface;
use Laravel\Lumen\Auth\Authorizable;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    /**
     * @inheritDoc
     */
    protected $fillable = ['first_name', 'last_name', 'email', 'password', 'api_token', 'api_token_expiration', 'role'];

    /**
     * @inheritDoc
     */
    protected $visible = ['created_at', 'updated_at', 'first_name', 'last_name', 'email', 'role', 'api_token'];

    /**
     * @inheritDoc
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Get all of the posts for a user.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Determine if a user in an admin.
     */
    public function isAdmin()
    {
        return ($this->role === 'Administrator');
    }

    public function isUser(User $user)
    {
        return ($this->id === $user->id);
    }
}
