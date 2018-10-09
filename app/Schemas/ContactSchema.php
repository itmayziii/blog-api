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
            'created_at' => $post->getAttribute('created_at')->toIso8601String(),
            'updated_at' => $post->getAttribute('updated_at')->toIso8601String(),
            'first_name' => $post->getAttribute('first_name'),
            'last_name'  => $post->getAttribute('last_name'),
            'email'     => $post->getAttribute('email'),
            'comments'  => $post->getAttribute('comments')
        ];
    }
}