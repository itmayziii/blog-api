<?php

namespace App\Models;

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
    protected $fillable = ['created_by', 'last_updated_by', 'name', 'plural_name', 'slug'];

    /**
     * Get all the web pages under this category
     */
    public function webPages()
    {
        return $this->hasMany(WebPage::class);
    }
}
