<?php

namespace App\Schemas;

use Neomerx\JsonApi\Contracts\Document\LinkInterface;
use Neomerx\JsonApi\Document\Link;
use Neomerx\JsonApi\Schema\BaseSchema;

class WebPageSchema extends BaseSchema
{
    protected $resourceType = 'webpages';

    public function getId($webpage): ?string
    {
        return $webpage->getAttribute('id');
    }

    public function getSelfSubUrl($webPage = null): string
    {
        $categoryRelationship = $webPage->getRelationValue('category');
        return $this->selfSubUrl . "/{$webPage->getAttribute('slug')}?category={$categoryRelationship->getAttribute('slug')}";
    }

    public function getAttributes($webPage, array $fieldKeysFilter = null): ?array
    {
        return [
            'created_at'        => $webPage->getAttribute('created_at')->toIso8601String(),
            'updated_at'        => $webPage->getAttribute('updated_at')->toIso8601String(),
            'created_by'        => $webPage->getAttribute('created_by'),
            'updated_by'        => $webPage->getAttribute('last_updated_by'),
            'category_id'       => $webPage->getAttribute('category_id'),
            'slug'              => $webPage->getAttribute('slug'),
            'is_live'           => $webPage->getAttribute('is_live'),
            'title'             => $webPage->getAttribute('title'),
            'modules'           => $webPage->getModules(),
            'short_description' => $webPage->getAttribute('short_description'),
            'image_path_sm'     => $webPage->getAttribute('image_path_sm'),
            'image_path_md'     => $webPage->getAttribute('image_path_md'),
            'image_path_lg'     => $webPage->getAttribute('image_path_lg'),
            'image_path_meta'   => $webPage->getAttribute('image_path_meta')
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
