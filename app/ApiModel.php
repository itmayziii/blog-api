<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

abstract class ApiModel extends Model
{
    /**
     * @var String
     */
    protected $resourceName;

    /**
     * @return String
     */
    public function getResourceName()
    {
        return ($this->resourceName) ? $this->resourceName : self::class;
    }

    public function getResourceUrl()
    {
        $key = $this->getKey();
        $url = env('APP_URI') . '/' . $this->getResourceName() . '/' . $key;
        return $url;
    }
}