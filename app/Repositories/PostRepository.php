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

    public function paginateAllPosts($page, $size)
    {
        return $this->post
            ->orderBy('updated_at', 'desc')
            ->paginate($size, null, 'page', $page);
    }

    public function paginateLivePosts($page, $size)
    {
        return $this->post
            ->where('status', 'live')
            ->orderBy('updated_at', 'desc')
            ->paginate($size, null, 'page', $page);
    }

    /**
     * @param string $slug
     *
     * @return Post | null
     */
    public function findBySlug($slug)
    {
        return $this->post
            ->where('slug', $slug)
            ->get()
            ->first();
    }
}