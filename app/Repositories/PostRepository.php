<?php

namespace App\Repositories;

use App\Post;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Psr\Log\LoggerInterface;

class PostRepository
{
    /**
     * @var Post | Builder
     */
    private $post;
    /**
     * @var Cache
     */
    private $cache;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Post $post, Cache $cache, LoggerInterface $logger)
    {
        $this->post = $post;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * @param string | int $page
     * @param string | int $size
     *
     * @return LengthAwarePaginator
     */
    public function paginateAllPosts($page, $size)
    {
        return $this->cache->remember("posts.all.page$page.size$size", 60, function () use ($size, $page) {
            return $this->post
                ->orderBy('created_at', 'desc')
                ->paginate($size, null, 'page', $page);
        });
    }

    /**
     * @param string | int $page
     * @param string | int $size
     *
     * @return LengthAwarePaginator
     */
    public function paginateLivePosts($page, $size)
    {
        return $this->cache->remember("posts.live.page$page.size$size", 60, function () use ($size, $page) {
            return $this->post
                ->where('status', 'live')
                ->orderBy('created_at', 'desc')
                ->paginate($size, null, 'page', $page);
        });
    }

    /**
     * @param string $slug
     *
     * @return Post | bool
     */
    public function findBySlug($slug)
    {
        $post = $this->cache->remember("post.$slug", 60, function () use ($slug) {
            return $this->post
                ->where('slug', $slug)
                ->get()
                ->first();
        });

        if (is_null($post)) {
            $this->logger->notice(PostRepository::class . ": unable to find post by slug: {$slug}");
            return false;
        }

        return $post;
    }

    /**
     * @param array $attributes
     * @param Authenticatable $user
     *
     * @return Post | bool
     */
    public function create($attributes, Authenticatable $user)
    {
        try {
            $post = $this->post->create($this->mapAttributes($attributes, $user));
        } catch (Exception $exception) {
            $this->logger->error(PostRepository::class . ": unable to create post with exception: {$exception->getMessage()}");
            return false;
        }

        $this->cache->clear();
        return $post;
    }

    /**
     * @param Post $post
     * @param array $attributes
     * @param Authenticatable $user
     *
     * @return Post | bool
     */
    public function update(Post $post, $attributes, Authenticatable $user)
    {
        try {
            $post->update($this->mapAttributes($attributes, $user));
        } catch (Exception $exception) {
            $this->logger->error(PostRepository::class . ": unable to update post with exception: {$exception->getMessage()}");
            return false;
        }

        $this->cache->clear();
        return $post;
    }

    /**
     * @param Post $post
     *
     * @return bool
     */
    public function delete(Post $post)
    {
        try {
            $post->delete();
        } catch (Exception $exception) {
            $this->logger->error(PostRepository::class . ": unable to delete post with with exception: {$exception->getMessage()}");
            return false;
        }

        $this->cache->clear();
        return true;
    }

    /**
     * @param array $attributes
     * @param Authenticatable $user
     *
     * @return array
     */
    private function mapAttributes($attributes, Authenticatable $user)
    {
        return [
            'user_id'         => $user->getAuthIdentifier(),
            'category_id'     => isset($attributes['category-id']) ? $attributes['category-id'] : null,
            'slug'            => isset($attributes['slug']) ? $attributes['slug'] : null,
            'status'          => isset($attributes['status']) ? $attributes['status'] : null,
            'title'           => isset($attributes['title']) ? $attributes['title'] : null,
            'content'         => isset($attributes['content']) ? $attributes['content'] : null,
            'preview'         => isset($attributes['preview']) ? $attributes['preview'] : null,
            'image_path_sm'   => isset($attributes['image-path-sm']) ? $attributes['image-path-sm'] : null,
            'image_path_md'   => isset($attributes['image-path-md']) ? $attributes['image-path-md'] : null,
            'image_path_lg'   => isset($attributes['image-path-lg']) ? $attributes['image-path-lg'] : null,
            'image_path_meta' => isset($attributes['image-path-meta']) ? $attributes['image-path-meta'] : null
        ];
    }
}