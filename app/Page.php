<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    /**
     * @inheritDoc
     */
    protected $fillable = ['title', 'slug', 'content', 'is_live'];
}