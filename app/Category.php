<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public function getRouteKeyName()
    {
        return 'name';
    }

    /**
     * Get all the blogs under this category.
     */
    public function blogs()
    {
        return $this->hasMany('App\Blog');
    }
}
