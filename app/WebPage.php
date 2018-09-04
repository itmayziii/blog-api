<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebPage extends Model
{
    protected $table = 'webpages';

    protected $fillable = [
        'created_by',
        'last_updated_by',
        'category_id',
        'path',
        'is_live',
        'title',
        'content',
        'preview',
        'image_path_sm',
        'image_path_md',
        'image_path_lg',
        'image_path_meta'
    ];

    public function getIsLiveAttribute($value)
    {
        return (bool)$value;
    }

    /**
     * Get all of the tags for the web page
     */
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    /**
     * Get the user the web page belongs to
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category the web page belongs to
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Determine if a web page is live
     *
     * @return bool
     */
    public function isLive()
    {
        return $this->getAttribute('is_live') === true;
    }
}
