<?php

use App\Blog;
use App\Category;

return [
    'relationship_links' => [
        Blog::class => [
            Category::class => [
                'self'    => '/{base-type}/{base-key}/relationships/{related-type}',
                'related' => '{base-type}/{base-key}/{related-type}'
            ]
        ]
    ]
];