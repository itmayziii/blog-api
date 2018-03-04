<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /**
     * @inheritDoc
     */
    protected $visible = ['created_at', 'updated_at', 'name', 'slug'];

    /**
     * @inheritDoc
     */
    protected $fillable = ['name', 'slug'];

    /**
     * @inheritDoc
     */
    public function getRouteKeyName()
    {
        return 'name';
    }

    /**
     * Get all the posts under this category.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
