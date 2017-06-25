<?php

use App\Blog;
use App\Category;

return [
    'relationship_links' => [
        Blog::class => [
            Category::class => [
                'self'    => '/{related-type}/{related-key}/relationships/{base-type}',
                'related' => '/{related-type}/{related-key}/{base-type}'
            ]
        ]
    ]
];