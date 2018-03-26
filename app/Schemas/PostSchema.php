<?php

namespace App\Schemas;

use Neomerx\JsonApi\Contracts\Document\LinkInterface;
use Neomerx\JsonApi\Schema\BaseSchema;

class PostSchema extends BaseSchema
{
    protected $resourceType = 'posts';

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
            'createdAt'     => $post->getAttribute('created_at')->toIso8601String(),
            'updatedAt'     => $post->getAttribute('updated_at')->toIso8601String(),
            'status'        => $post->getAttribute('status'),
            'title'         => $post->getAttribute('title'),
            'slug'          => $post->getAttribute('slug'),
            'content'       => $post->getAttribute('content'),
            'preview'       => $post->getAttribute('preview'),
            'imagePathSm'   => $post->getAttribute('image_path_sm'),
            'imagePathMd'   => $post->getAttribute('image_path_md'),
            'imagePathLg'   => $post->getAttribute('image_path_lg'),
            'imagePathMeta' => $post->getAttribute('image_path_meta'),
            'categoryId'    => $post->getAttribute('category_id'),
            'userId'        => $post->getAttribute('user_id')
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