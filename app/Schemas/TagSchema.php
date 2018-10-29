<?php

namespace App\Schemas;

use Neomerx\JsonApi\Contracts\Document\LinkInterface;
use Neomerx\JsonApi\Schema\BaseSchema;

class TagSchema extends BaseSchema
{
    protected $resourceType = 'tags';

    /**
     * @inheritdoc
     */
    public function getId($tag): ?string
    {
        return $tag->getAttribute('id');
    }

    /**
     * @inheritdoc
     */
    public function getSelfSubUrl($tag = null): string
    {
        return $tag === null ? $this->selfSubUrl : $this->selfSubUrl . '/' . $tag->getAttribute('slug');
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

    /**
     * @inheritdoc
     */
    public function getRelationships($tag, bool $isPrimary, array $includeList): ?array
    {
        $relationships = [
            'webpages' => [
                self::SHOW_DATA    => false,
                self::SHOW_SELF    => false,
                self::SHOW_RELATED => true
            ]
        ];

        if ($tag->relationLoaded('webpages')) {
            $relationships['webpages'] = array_merge($relationships['webpages'], [
                self::SHOW_DATA => true,
                self::DATA      => $tag->getRelationValue('webpages')
            ]);
        }

        return $relationships;
    }

    /**
     * @inheritdoc
     */
    public function getIncludePaths(): array
    {
        return [
            'webpages'
        ];
    }
}
