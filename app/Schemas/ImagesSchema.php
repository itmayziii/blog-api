<?php

namespace App\Schemas;

use Neomerx\JsonApi\Schema\BaseSchema;

class ImagesSchema extends BaseSchema
{
    protected $resourceType = 'images';

    public function getId($image): ?string
    {
        return $image->path;
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