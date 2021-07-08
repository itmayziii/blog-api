<?php

namespace App\Schemas;

use Neomerx\JsonApi\Contracts\Document\LinkInterface;
use Neomerx\JsonApi\Schema\BaseSchema;

class UserSchema extends BaseSchema
{
    protected $resourceType = 'users';

    public function getId($tag): ?string
    {
        return $tag->getAttribute('id');
    }

    public function getAttributes($tag, array $fieldKeysFilter = null): ?array
    {
        return [
            'created_at' => $tag->getAttribute('created_at')->toIso8601String(),
            'updated_at' => $tag->getAttribute('updated_at')->toIso8601String(),
            'first_name' => $tag->getAttribute('first_name'),
            'last_name'  => $tag->getAttribute('last_name'),
            'email'      => $tag->getAttribute('email'),
            'role'       => $tag->getAttribute('role'),
            'api_token'  => $tag->getAttribute('api_token')
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
