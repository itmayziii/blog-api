<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use itmayziii\Laravel\Contracts\JsonApiModelInterface;

class Category extends Model implements JsonApiModelInterface
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

    /**
     * Name of the resource (e.g. type = posts for http://localhost/posts/first-post).
     *
     * @return string
     */
    public function getJsonApiType()
    {
        return 'categories';
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
