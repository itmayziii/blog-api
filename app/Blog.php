<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Blog extends Model
{
    /**
     * @inheritDoc
     */
    protected $primaryKey = 'slug';

    /**
     * @inheritDoc
     */
    protected $fillable = ['user_id', 'category_id', 'status', 'title', 'slug', 'content'];

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
}
