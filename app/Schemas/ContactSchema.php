<?php

namespace App\Schemas;

use Neomerx\JsonApi\Schema\BaseSchema;

class ContactSchema extends BaseSchema
{
    protected $resourceType = 'contacts';

    public function getId($post): ?string
    {
        return $post->getAttribute('id');
    }

    public function getAttributes($post, array $fieldKeysFilter = null): ?array
    {
        return [
            'createdAt' => $post->getAttribute('created_at')->toIso8601String(),
            'updatedAt' => $post->getAttribute('updated_at')->toIso8601String(),
            'firstName' => $post->getAttribute('first_name'),
            'lastName'  => $post->getAttribute('last_name'),
            'email'     => $post->getAttribute('email'),
            'comments'  => $post->getAttribute('comments')
        ];
    }
}