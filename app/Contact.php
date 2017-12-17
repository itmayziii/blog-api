<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use itmayziii\Laravel\Contracts\JsonApiModelInterface;

class Contact extends Model implements JsonApiModelInterface
{
    /**
     * @inheritDoc
     */
    protected $fillable = ['first_name', 'last_name', 'email', 'comments'];

    /**
     * @inheritDoc
     */
    protected $visible = ['created_at', 'updated_at', 'first_name', 'last_name', 'email', 'comments'];

    /**
     * Name of the resource (i.e. type = posts for http://localhost/posts/first-post).
     *
     * @return string
     */
    public function getJsonApiType()
    {
        return 'contacts';
    }

    /**
     * Value of model's primary key.
     *
     * @return mixed
     */
    public function getJsonApiModelPrimaryKey()
    {
        return $this->getKey();
    }
}
