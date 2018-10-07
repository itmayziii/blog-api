<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /**
     * @inheritDoc
     */
    protected $visible = ['created_at', 'updated_at', 'name', 'plural_name', 'slug'];

    /**
     * @inheritDoc
     */
    protected $fillable = ['name', 'plural_name', 'slug'];

    /**
     * Get all the web pages under this category
     */
    public function webPages()
    {
        return $this->hasMany(WebPage::class);
    }
}
