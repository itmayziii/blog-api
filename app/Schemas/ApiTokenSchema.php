<?php

namespace App\Schemas;

use Neomerx\JsonApi\Schema\BaseSchema;

class ApiTokenSchema extends BaseSchema
{
    protected $resourceType = 'api-token';

    public function getId($apiToken): ?string
    {
        return $apiToken->token;
    }

    public function getAttributes($image, array $fieldKeysFilter = null): ?array
    {
        return [];
    }
}