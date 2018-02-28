<?php

namespace App\Schemas;

use Neomerx\JsonApi\Schema\BaseSchema;

class CategorySchema extends BaseSchema
{
    protected $resourceType = 'categories';

    public function getId($post): ?string
    {
        return $post->getAttribute('slug');
    }

    public function getAttributes($category, array $fieldKeysFilter = null): ?array
    {
        $attributes = [
            'createdAt' => $category->getAttribute('created_at')->toIso8601String(),
            'updatedAt' => $category->getAttribute('updated_at')->toIso8601String(),
            'name'      => $category->getAttribute('name'),
            'slug'      => $category->getAttribute('slug'),
        ];

        $postsCount = $category->getAttribute('posts_count');
        if (!is_null($postsCount)) {
            $attributes['posts'] = $postsCount;
        }

        return $attributes;
    }

    public function getRelationships($category, bool $isPrimary, array $includeList): ?array
    {
        $relationships = [];

        if ($category->relationLoaded('posts')) {
            $relationships['posts'] = [
                self::DATA => $category->getRelation('posts')
            ];
        }

        return $relationships;
    }

    public function getIncludePaths(): array
    {
        return [
            'posts'
        ];
    }
}