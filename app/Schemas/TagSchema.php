<?php

namespace App\Schemas;

use Neomerx\JsonApi\Contracts\Document\LinkInterface;
use Neomerx\JsonApi\Schema\BaseSchema;

class TagSchema extends BaseSchema
{
    protected $resourceType = 'tags';

    public function getId($user): ?string
    {
        return $user->getAttribute('id');
    }

    public function getAttributes($user, array $fieldKeysFilter = null): ?array
    {
        return [
            'created_at' => $user->getAttribute('created_at')->toIso8601String(),
            'updated_at' => $user->getAttribute('updated_at')->toIso8601String(),
            'name'       => $user->getAttribute('name'),
            'slug'       => $user->getAttribute('slug')
        ];
    }

    public function getIncludedResourceLinks($resource): array
    {
        $links = [
            LinkInterface::SELF => $this->getSelfSubLink($resource),
        ];

        return $links;
    }

    public function getSelfSubUrl($resource = null): string
    {
        return $resource === null ? $this->selfSubUrl : $this->selfSubUrl . '/' . $resource->getAttribute('slug');
    }
}
