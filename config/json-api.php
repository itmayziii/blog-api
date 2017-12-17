<?php

use App\Post;
use App\Category;

return [
    'relationship_links' => [
        Post::class => [
            Category::class => [
                'self'    => '/{related-type}/{related-key}/relationships/{base-type}',
                'related' => '/{related-type}/{related-key}/{base-type}'
            ]
        ]
    ]
];