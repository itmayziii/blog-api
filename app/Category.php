<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use itmayziii\Laravel\Contracts\JsonApiModelInterface;

class Category extends Model implements JsonApiModelInterface
{
    public function getRouteKeyName()
    {
        return 'name';
    }

    /**
     * Get all the blogs under this category.
     */
    public function blogs()
    {
        return $this->hasMany('App\Blog');
    }

    /**
     * Name of the resource (e.g. type = blogs for http://localhost/blogs/first-blog).
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
