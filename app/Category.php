<?php

namespace App;

class Category extends ApiModel
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
