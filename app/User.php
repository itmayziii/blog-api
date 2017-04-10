<?php

namespace App;

class User extends ApiModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'password', 'api_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
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
}
