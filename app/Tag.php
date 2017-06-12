<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use itmayziii\Laravel\Contracts\JsonApiModelInterface;

class Tag extends Model implements JsonApiModelInterface
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * Get all of the posts that are assigned this tag.
     */
    public function blogs()
    {
        return $this->morphedByMany('App\Blog', 'taggable');
    }

    /**
     * Name of the resource (e.g. type = blogs for http://localhost/blogs/first-blog).
     *
     * @return string
     */
    public function getJsonApiType()
    {
        return 'tags';
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
