<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use itmayziii\Laravel\Contracts\JsonApiModelInterface;

class Category extends Model
{
    /**
     * @inheritDoc
     */
    protected $visible = ['created_at', 'updated_at', 'name'];

    /**
     * @inheritDoc
     */
    protected $fillable = ['name'];

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
