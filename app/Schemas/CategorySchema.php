<?php

namespace App\Schemas;

use Neomerx\JsonApi\Contracts\Document\LinkInterface;
use Neomerx\JsonApi\Schema\BaseSchema;

class CategorySchema extends BaseSchema
{
    protected $resourceType = 'categories';

    public function getId($post): ?string
    {
        return $post->getAttribute('id');
    }

    public function getAttributes($category, array $fieldKeysFilter = null): ?array
    {
        return [
            'createdAt' => $category->getAttribute('created_at')->toIso8601String(),
            'updatedAt' => $category->getAttribute('updated_at')->toIso8601String(),
            'name'      => $category->getAttribute('name')
        ];
    }

    public function getRelationships($category, bool $isPrimary, array $includeList): ?array
    {
        return [
            'posts' => [
                self::DATA  => $category->getRelation('posts'),
                self::LINKS => [
                    LinkInterface::SELF => $this->getRelationshipSelfLink($category, 'posts')
                ]
            ]
        ];
    }
}