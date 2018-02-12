<?php

namespace App\Schemas;

use Neomerx\JsonApi\Schema\SchemaProvider;

class PostSchema extends SchemaProvider
{
    protected $resourceType = 'posts';

    public function getId($post)
    {
        return $post->getAttribute('slug');
    }

    public function getAttributes($post)
    {
        return [
            'createdAt' => $post->getAttribute('created_at')->toIso8601String(),
            'updatedAt' => $post->getAttribute('updated_at')->toIso8601String(),
            'status'    => $post->getAttribute('status'),
            'title'     => $post->getAttribute('title'),
            'content'   => $post->getAttribute('content'),
            'imagePath' => $post->getAttribute('image_path')
        ];
    }
}