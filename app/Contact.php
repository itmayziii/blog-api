<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $table = 'contacts';
    protected $resourceName = 'contacts';
    protected $fillable = ['first_name', 'last_id', 'email', 'comments'];
}
