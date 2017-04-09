<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * Get all of the posts that are assigned this tag.
     */
    public function blogs()
    {
        return $this->morphedByMany('App\Blog', 'taggable');
    }
}
