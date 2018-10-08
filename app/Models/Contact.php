<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    /**
     * @inheritDoc
     */
    protected $fillable = ['first_name', 'last_name', 'email', 'comments'];

    /**
     * @inheritDoc
     */
    protected $visible = ['created_at', 'updated_at', 'first_name', 'last_name', 'email', 'comments'];
}
