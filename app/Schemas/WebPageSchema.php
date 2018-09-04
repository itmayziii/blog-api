<?php

namespace App\Schemas;

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
        return $webpage->getAttribute('path');
    }

    public function getAttributes($webpage, array $fieldKeysFilter = null): ?array
    {
        return [
            'createdAt'     => $webpage->getAttribute('created_at')->toIso8601String(),
            'updatedAt'     => $webpage->getAttribute('updated_at')->toIso8601String(),
            'createdBy'     => $webpage->getAttribute('created_by'),
            'lastUpdatedBy' => $webpage->getAttribute('last_updated_by'),
            'categoryId'    => $webpage->getAttribute('category_id'),
            'path'          => $webpage->getAttribute('path'),
            'isLive'        => $webpage->getAttribute('is_live'),
            'title'         => $webpage->getAttribute('title'),
            'content'       => $webpage->getAttribute('content'),
            'preview'       => $webpage->getAttribute('preview'),
            'imagePathSm'   => $webpage->getAttribute('image_path_sm'),
            'imagePathMd'   => $webpage->getAttribute('image_path_md'),
            'imagePathLg'   => $webpage->getAttribute('image_path_lg'),
            'imagePathMeta' => $webpage->getAttribute('image_path_meta')
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
