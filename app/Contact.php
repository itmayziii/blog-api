<?php

namespace App;

class Contact extends ApiModel
{
    protected $table = 'contacts';
    protected $resourceName = 'contacts';
    protected $fillable = ['first_name', 'last_id', 'email', 'comments'];
}
