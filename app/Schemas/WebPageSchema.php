<?php

namespace App\Schemas;

use Illuminate\Support\Str;
use Neomerx\JsonApi\Contracts\Document\LinkInterface;
use Neomerx\JsonApi\Schema\BaseSchema;

class WebPageSchema extends BaseSchema
{
    protected $resourceType = 'webpages';

    public function getId($webpage): ?string
    {
        return $webpage->getAttribute('id');
    }

    public function getSelfSubUrl($webpage = null): string
    {
        return $this->selfSubUrl . Str::start($webpage->getAttribute('slug'), '/');
    }

    public function getAttributes($webpage, array $fieldKeysFilter = null): ?array
    {
        return [
            'created_at'    => $webpage->getAttribute('created_at')->toIso8601String(),
            'updated_at'    => $webpage->getAttribute('updated_at')->toIso8601String(),
            'created_by'    => $webpage->getAttribute('created_by'),
            'updated_by'    => $webpage->getAttribute('last_updated_by'),
            'category_id'   => $webpage->getAttribute('category_id'),
            'slug'          => $webpage->getAttribute('slug'),
            'type_id'       => $webpage->getAttribute('type_id'),
            'is_live'       => $webpage->getAttribute('is_live'),
            'title'         => $webpage->getAttribute('title'),
            'content'       => $webpage->getAttribute('content'),
            'preview'       => $webpage->getAttribute('preview'),
            'image_path_sm' => $webpage->getAttribute('image_path_sm'),
            'image_path_md' => $webpage->getAttribute('image_path_md'),
            'image_path_lg' => $webpage->getAttribute('image_path_lg'),
            'image_path_meta' => $webpage->getAttribute('image_path_meta')
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
