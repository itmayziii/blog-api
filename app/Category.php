<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /**
     * @inheritDoc
     */
    protected $visible = ['created_at', 'updated_at', 'name', 'slug'];

    /**
     * @inheritDoc
     */
    protected $fillable = ['name', 'slug'];

    /**
     * @inheritDoc
     */
    public function getRouteKeyName()
    {
        return 'name';
    }

    /**
     * Get all the web pages under this category
     */
    public function webPages()
    {
        return $this->hasMany(WebPage::class);
    }
}
