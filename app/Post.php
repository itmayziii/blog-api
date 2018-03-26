<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    /**
     * @inheritDoc
     */
    protected $fillable = ['user_id', 'category_id', 'status', 'title', 'slug', 'content', 'preview', 'image_path_sm', 'image_path_md', 'image_path_lg', 'image_path_meta'];

    /**
     * @inheritDoc
     */
    protected $visible = [
        'created_at',
        'updated_at',
        'category_id',
        'user_id',
        'status',
        'title',
        'slug',
        'content',
        'preview',
        'image_path_sm',
        'image_path_md',
        'image_path_lg',
        'image_path_meta'
    ];

    /**
     * Get all of the tags for the post.
     */
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    /**
     * Get the user the post belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category the post belongs to.
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
}
