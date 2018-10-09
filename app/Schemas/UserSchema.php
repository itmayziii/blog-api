<?php

namespace App\Schemas;

use Neomerx\JsonApi\Contracts\Document\LinkInterface;
use Neomerx\JsonApi\Schema\BaseSchema;

class UserSchema extends BaseSchema
{
    protected $resourceType = 'users';

    public function getId($user): ?string
    {
        return $user->getAttribute('id');
    }

    public function getAttributes($user, array $fieldKeysFilter = null): ?array
    {
        return [
            'created_at' => $user->getAttribute('created_at')->toIso8601String(),
            'updated_at' => $user->getAttribute('updated_at')->toIso8601String(),
            'first_name' => $user->getAttribute('first_name'),
            'last_name'  => $user->getAttribute('last_name'),
            'email'      => $user->getAttribute('email'),
            'role'       => $user->getAttribute('role'),
            'api_token'  => $user->getAttribute('api_token')
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