<?php

namespace App\Repositories;

use App\Models\Tag;
use Exception;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Psr\Log\LoggerInterface;

class TagRepository
{
    /**
     * @var Tag | Builder
     */
    private $tag;
    /**
     * @var Cache
     */
    private $cache;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Tag $contact, Cache $cache, LoggerInterface $logger)
    {
        $this->tag = $contact;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * @param int $page
     * @param int $size
     *
     * @return LengthAwarePaginator
     */
    public function paginate($page, $size)
    {
        $cacheKey = "tags:page.$page:size.$size";
        return $this->cache->remember($cacheKey, 60, function () use ($page, $size) {
            return $this->tag
                ->orderBy('updated_at', 'desc')
                ->paginate($size, null, 'page', $page);
        });
    }

    /**
     * @param string | int $id
     *
     * @return Tag | null
     */
    public function findById($id)
    {
        $tag = $this->cache->remember("tag:id.$id", 60, function () use ($id) {
            return $this->tag->find($id);
        });

        if (is_null($tag)) {
            $this->logger->notice(TagRepository::class . ": unable to find tag with id: {$id}");
            return null;
        }

        return $tag;
    }

    /**
     * @param string $slug
     *
     * @return Tag | null
     */
    public function findBySlug($slug)
    {
        $tag = $this->cache->remember("tag:slug.$slug", 60, function () use ($slug) {
            return $this->tag
                ->where('slug', $slug)
                ->first();
        });

        if (is_null($tag)) {
            $this->logger->notice(TagRepository::class . ": unable to find tag with slug: {$slug}");
            return null;
        }

        return $tag;
    }

    /**
     * @param array $attributes
     *
     * @return Tag | null
     */
    public function create($attributes)
    {
        $attributes = $this->mapAttributes($attributes);
        try {
            $contact = $this->tag->create($attributes);
        } catch (Exception $exception) {
            $this->logger->error(TagRepository::class . ": unable to create tag with exception: {$exception->getMessage()}");
            return null;
        }

        $this->deleteTagCache();
        return $contact;
    }

    /**
     * @param Tag $tag
     * @param array $attributes
     *
     * @return Tag | null
     */
    public function update(Tag $tag, $attributes)
    {
        $attributes = $this->mapAttributes($attributes);
        try {
            $tag->update($attributes);
        } catch (Exception $exception) {
            $this->logger->error(TagRepository::class . ": unable to update tag with exception: {$exception->getMessage()}");
            return null;
        }

        $this->deleteTagCache();
        return $tag;
    }

    public function delete(Tag $tag)
    {
        try {
            $tag->delete();
        } catch (Exception $exception) {
            $this->logger->error(TagRepository::class . ": unable to delete tag with exception: {$exception->getMessage()}");
            return false;
        }

        return true;
    }

    /**
     * @param array $attributes
     *
     * @return array
     */
    private function mapAttributes($attributes)
    {
        return [
            'name' => isset($attributes['name']) ? $attributes['name'] : null,
            'slug' => isset($attributes['slug']) ? $attributes['slug'] : null
        ];
    }

    /**
     * @return void
     */
    private function deleteTagCache()
    {
        try {
            $laravelCachePrefix = $this->cache->getPrefix();
            $contactCacheKeys = $this->cache->connection()->keys($laravelCachePrefix . 'tag*');
            $this->cache->connection()->del($contactCacheKeys);
        } catch (Exception $exception) {
            $this->logger->error(TagRepository::class . ": unable to delete tag cache with exception {$exception->getMessage()}");
        }
    }
}
