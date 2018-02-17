<?php

namespace App\Schemas;

use Neomerx\JsonApi\Contracts\Document\LinkInterface;
use Neomerx\JsonApi\Schema\BaseSchema;

class PostSchema extends BaseSchema
{
    protected $resourceType = 'posts';

    public function getId($post): ?string
    {
        return $post->getAttribute('slug');
    }

    public function getAttributes($post, array $fieldKeysFilter = null): ?array
    {
        return [
            'createdAt' => $post->getAttribute('created_at')->toIso8601String(),
            'updatedAt' => $post->getAttribute('updated_at')->toIso8601String(),
            'status'    => $post->getAttribute('status'),
            'title'     => $post->getAttribute('title'),
            'content'   => $post->getAttribute('content'),
            'imagePath' => $post->getAttribute('image_path')
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