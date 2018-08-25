<?php

namespace App\Repositories;

use App\Post;
use App\Repositories\CacheRepository;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Psr\Log\LoggerInterface;

class PostRepository
{
    /**
     * @var Post | Builder
     */
    private $post;
    /**
     * @var \App\Repositories\CacheRepository
     */
    private $cacheRepository;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Post $post, CacheRepository $cacheRepository, LoggerInterface $logger)
    {
        $this->post = $post;
        $this->cacheRepository = $cacheRepository;
        $this->logger = $logger;
    }

    public function paginateAllPosts($page, $size)
    {
        return $this->cacheRepository->remember("posts.all.page$page.size$size", 60, function () use ($size, $page) {
            return $this->post
                ->orderBy('created_at', 'desc')
                ->paginate($size, null, 'page', $page);
        });
    }

    public function paginateLivePosts($page, $size)
    {
        return $this->cacheRepository->remember("posts.live.page$page.size$size", 60, function () use ($size, $page) {
            return $this->post
                ->where('status', 'live')
                ->orderBy('created_at', 'desc')
                ->paginate($size, null, 'page', $page);
        });
    }

    /**
     * @param string $slug
     *
     * @return Post | null
     */
    public function findBySlug($slug)
    {
        $post = $this->cacheRepository->remember("post.$slug", 60, function () use ($slug) {
            return $this->post
                ->where('slug', $slug)
                ->get()
                ->first();
        });

        if (is_null($post)) {
            $this->logger->error(PostRepository::class . ": Unable to find post by slug: {$slug}");
        }

        return $post;
    }

    /**
     * @param array $attributes
     *
     * @return Post | null
     */
    public function create($attributes)
    {
        try {
            $post = $this->post->create([
                'user_id'         => $attributes['user-id'],
                'category_id'     => $attributes['category-id'],
                'slug'            => $attributes['slug'],
                'status'          => $attributes['status'],
                'title'           => $attributes['title'],
                'content'         => $attributes['content'],
                'preview'         => $attributes['preview'],
                'image_path_sm'   => $attributes['image-path-sm'],
                'image_path_md'   => $attributes['image-path-md'],
                'image_path_lg'   => $attributes['image-path-lg'],
                'image_path_meta' => $attributes['image-path-meta']
            ]);
        } catch (Exception $exception) {
            $this->logger->error(PostRepository::class . ": Unable to create post with exception: {$exception->getMessage()}");
            return null;
        }

        $this->clearPostsCache();
        return $post;
    }

    private function clearPostsCache()
    {
        $postKeys = $this->cacheRepository->keys('posts*');
        $this->cacheRepository->deleteMultiple($postKeys);

        $categoryPostKeys = $this->cacheRepository->keys('categories-posts*');
        $this->cacheRepository->deleteMultiple($categoryPostKeys);
    }
}