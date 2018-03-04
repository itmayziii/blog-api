<?php

namespace App\Repositories;

use App\Post;

class PostRepository
{
    /**
     * @var Post
     */
    private $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    /**
     * @param string $slug
     * @param boolean $liveOnly
     *
     * @return Post | null
     */
    public function findBySlug($slug, $liveOnly = true)
    {
        $post = $this->post
            ->where('slug', $slug);

        if ($liveOnly) {
            $post = $post->where('status', 'live');
        }

        return $post->get()->first();
    }
}