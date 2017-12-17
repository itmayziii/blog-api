<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use itmayziii\Laravel\Contracts\JsonApiModelInterface;

class Blog extends Model implements JsonApiModelInterface
{
    /**
     * @inheritDoc
     */
    protected $primaryKey = 'slug';

    /**
     * @inheritDoc
     */
    public $incrementing = false;

    /**
     * @inheritDoc
     */
    protected $fillable = ['user_id', 'category_id', 'status', 'title', 'slug', 'content', 'image_path'];

    /**
     * @inheritDoc
     */
    protected $visible = ['created_at', 'updated_at', 'status', 'title', 'slug', 'content', 'image_path'];

    /**
     * Get all of the tags for the post.
     */
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    /**
     * Get the user the blog belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category the blog belongs to.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @param \App\User $user
     * @return bool
     */
    public function isOwner(User $user)
    {
        return ($this->user_id === $user->id);
    }

    /**
     * Name of the resource (e.g. type = blogs for http://localhost/blogs/first-blog).
     *
     * @return string
     */
    public function getJsonApiType()
    {
        return 'blogs';
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
