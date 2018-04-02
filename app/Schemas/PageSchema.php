<?php

namespace App\Schemas;

use Neomerx\JsonApi\Contracts\Document\LinkInterface;
use Neomerx\JsonApi\Schema\BaseSchema;

class PageSchema extends BaseSchema
{
    protected $resourceType = 'pages';

    public function getId($post): ?string
    {
        return $post->getAttribute('id');
    }

    public function getSelfSubUrl($post = null): string
    {
        return $this->selfSubUrl . '/' . $post->getAttribute('slug');
    }

    public function getAttributes($post, array $fieldKeysFilter = null): ?array
    {
        return [
            'createdAt' => $post->getAttribute('created_at')->toIso8601String(),
            'updatedAt' => $post->getAttribute('updated_at')->toIso8601String(),
            'title'     => $post->getAttribute('title'),
            'slug'      => $post->getAttribute('slug'),
            'content'   => $post->getAttribute('content'),
            'isLive'    => $post->getAttribute('is_live')
        ];
    }

    public function getIncludedResourceLinks($resource): array
    {
        $links = [
            LinkInterface::SELF => $this->getSelfSubLink($resource),
        ];

        return $links;
    }
}