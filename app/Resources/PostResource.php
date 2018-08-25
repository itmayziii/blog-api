<?php

namespace App\Resources;

use App\Contracts\ResourceInterface;
use App\Post;
use App\Repositories\CacheRepository;
use App\Repositories\PostRepository;
use Illuminate\Contracts\Auth\Access\Gate;

class PostResource implements ResourceInterface
{
    /**
     * @var CacheRepository
     */
    private $cacheRepository;
    /**
     * @var PostRepository
     */
    private $postRepository;
    /**
     * @var Gate
     */
    private $gate;

    public function __construct(CacheRepository $cacheRepository, PostRepository $postRepository, Gate $gate)
    {
        $this->cacheRepository = $cacheRepository;
        $this->postRepository = $postRepository;
        $this->gate = $gate;
    }

    /**
     * @return mixed
     */
    public function getResourceType()
    {
        return Post::class;
    }

    public function findResourceObject($slug)
    {
        return $this->postRepository->findBySlug($slug);
    }

    /**
     * @param string|integer $page
     * @param string|integer $size
     *
     * @return mixed
     */
    public function findResourceObjects($page, $size)
    {
        $isAllowedToIndexAllPosts = $this->gate->allows('indexAllPosts', app()->make(Post::class));
        return $isAllowedToIndexAllPosts ? $this->postRepository->paginateAllPosts($page, $size) : $this->postRepository->paginateLivePosts($page, $size);
    }

    /**
     * @param array $attributes
     *
     * @return mixed
     */
    public function storeResourceObject($attributes)
    {
        return $this->postRepository->create($attributes);
    }

    /**
     * @param Post $resource
     *
     * @return bool
     */
    public function skipShowAuthentication($resource)
    {
        return $resource->isLive();
    }

    /**
     * @return bool
     */
    public function skipStoreAuthentication()
    {
        return false;
    }
}
