<?php

namespace App\Rules;

use Illuminate\Database\DatabaseManager;
use InvalidArgumentException;

class CompositeUnique
{
    public function validate($attributes, $value, $parameters, $validator)
    {
        if (!isset($parameters[0]) || !isset($parameters[1]) || !isset($parameters[2])) {
            throw new InvalidArgumentException(CompositeUnique::class . ' expected three parameters passed to composite_unique validation');
        }

        $db = app()->make(DatabaseManager::class);
        $exists = $db->table($parameters[0])
            ->where($attributes, $value)
            ->where($parameters[1], $parameters[2])
            ->exists();

        return !$exists;
    }
}
