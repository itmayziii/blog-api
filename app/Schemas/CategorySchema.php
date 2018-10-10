<?php

namespace App\Schemas;

use Neomerx\JsonApi\Schema\BaseSchema;

class CategorySchema extends BaseSchema
{
    protected $resourceType = 'categories';

    public function getId($category): ?string
    {
        return $category->getAttribute('id');
    }

    public function getSelfSubUrl($category = null): string
    {
        return $this->selfSubUrl . '/' . $category->getAttribute('slug');
    }

    public function getAttributes($category, array $fieldKeysFilter = null): ?array
    {
        $attributes = [
            'created_at'  => $category->getAttribute('created_at')->toIso8601String(),
            'updated_at'  => $category->getAttribute('updated_at')->toIso8601String(),
            'name'        => $category->getAttribute('name'),
            'plural_name' => $category->getAttribute('plural_name'),
            'slug'        => $category->getAttribute('slug')
        ];

        $postsCount = $category->getAttribute('posts_count');
        if (!is_null($postsCount)) {
            $attributes['posts_count'] = $postsCount;
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