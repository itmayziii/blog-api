<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebPageType extends Model
{
    protected $table = 'webpage_types';

    protected $fillable = [
        'created_by',
        'last_updated_by',
        'name'
    ];

    /**
     * Get all the web pages under this type
     */
    public function webPages()
    {
        return $this->hasMany(WebPage::class);
    }
}
