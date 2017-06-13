<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use itmayziii\Laravel\Contracts\JsonApiModelInterface;
use Laravel\Lumen\Auth\Authorizable;

class User extends Model implements AuthenticatableContract, AuthorizableContract, JsonApiModelInterface
{
    use Authenticatable, Authorizable;

    /**
     * @inheritDoc
     */
    protected $fillable = ['first_name', 'last_name', 'email', 'password', 'api_token', 'role'];

    /**
     * @inheritDoc
     */
    protected $visible = ['first_name', 'last_name', 'email', 'role'];

    /**
     * @inheritDoc
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Get all of the blogs for a user.
     */
    public function blogs()
    {
        return $this->hasMany('App\Blog');
    }

    /**
     * Determine if a user in an admin.
     */
    public function isAdmin()
    {
        return ($this->role === 'Administrator');
    }

    /**
     * Name of the resource (e.g. type = blogs for http://localhost/blogs/first-blog).
     *
     * @return string
     */
    public function getJsonApiType()
    {
        return 'users';
    }

    /**
     * Value of model's primary key.
     *
     * @return mixed
     */
    public function getJsonApiModelPrimaryKey()
    {
        return $this->getKey();
    }
}
