<?php

namespace App\Rules;

class CompositeUnique
{
    public function validate($attributes, $value, $parameters, $validator)
    {
        return false;
    }
}
