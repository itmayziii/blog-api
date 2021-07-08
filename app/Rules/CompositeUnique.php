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

        [$table, $column, $columnValue] = $parameters;
        $db = app()->make(DatabaseManager::class);
        $exists = $db->table($table)
            ->where($attributes, $value)
            ->where($column, $columnValue)
            ->exists();

        return !$exists;
    }
}
