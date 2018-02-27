<?php

namespace App\Schemas;

use Neomerx\JsonApi\Schema\BaseSchema;

class ImageSchema extends BaseSchema
{
    protected $resourceType = 'images';

    public function getId($apiToken): ?string
    {
        return $apiToken->path;
    }

    public function getSelfSubUrl($image = null): string
    {
        return '/images/' . $image->filename;
    }

    public function getAttributes($image, array $fieldKeysFilter = null): ?array
    {
        return [];
    }
}