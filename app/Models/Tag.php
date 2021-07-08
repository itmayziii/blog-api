<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    /**
     * @inheritDoc
     */
    protected $fillable = ['name', 'slug'];

    /**
     * @inheritDoc
     */
    protected $visible = ['created_at', 'updated_at', 'name', 'slug'];

    /**
     * Get all of the web pages that are assigned this tag
     */
    public function webPages()
    {
        return $this->morphedByMany(WebPage::class, 'taggable');
    }
}
