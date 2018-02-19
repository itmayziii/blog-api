<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    /**
     * @inheritDoc
     */
    protected $fillable = ['name'];

    /**
     * @inheritDoc
     */
    protected $visible = ['created_at', 'updated_at', 'name'];

    /**
     * Get all of the posts that are assigned this tag.
     */
    public function posts()
    {
        return $this->morphedByMany(Post::class, 'taggable');
    }
}
