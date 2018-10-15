<?php

namespace App\Schemas;

use Neomerx\JsonApi\Contracts\Document\LinkInterface;
use Neomerx\JsonApi\Schema\BaseSchema;

class TagSchema extends BaseSchema
{
    protected $resourceType = 'tags';

    public function getId($tag): ?string
    {
        return $tag->getAttribute('id');
    }

    public function getAttributes($tag, array $fieldKeysFilter = null): ?array
    {
        return [
            'created_at'     => $tag->getAttribute('created_at')->toIso8601String(),
            'updated_at'     => $tag->getAttribute('updated_at')->toIso8601String(),
            'name'           => $tag->getAttribute('name'),
            'slug'           => $tag->getAttribute('slug'),
            'webpages_count' => $tag->getAttribute('web_pages_count')
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
