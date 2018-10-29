<?php

namespace App\Repositories;

use App\Models\Tag;
use App\Models\WebPage;
use Exception;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Psr\Log\LoggerInterface;

class TagRepository
{
    /**
     * @var Container
     */
    private $container;
    /**
     * @var Cache
     */
    private $cache;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Gate
     */
    private $gate;

    public function __construct(Container $container, Cache $cache, LoggerInterface $logger, Gate $gate)
    {
        $this->container = $container;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->gate = $gate;
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
        $liveWebPagesOnly = $this->gate->denies('indexAllWebPages', WebPage::class);
        if ($liveWebPagesOnly === true) {
            $cacheKey .= ':liveWebPages';
        }

        return $this->cache->remember($cacheKey, 60, function () use ($page, $size, $liveWebPagesOnly) {
            $tag = $this->container->make(Tag::class);

            return $tag
                ->orderBy('created_at', 'desc')
                ->withCount([
                    'webPages' => function (Builder $query) use ($liveWebPagesOnly) {
                        if ($liveWebPagesOnly === true) {
                            $query->where('is_live', true);
                        }
                    }
                ])
                ->paginate($size, null, 'page', $page);
        });
    }

    /**
     * @param string | int $slugOrId
     * @param bool $withWebPages
     *
     * @return Tag | null
     */
    public function findBySlugOrId($slugOrId, $withWebPages = false)
    {
        $type = is_numeric($slugOrId) ? 'id' : 'slug';
        $cacheKey = "tag:$type.$slugOrId";
        if ($withWebPages === true) {
            $cacheKey .= ':withWebPages';
        }
        $liveWebPagesOnly = $this->gate->denies('indexAllWebPages', WebPage::class);
        if ($liveWebPagesOnly === true) {
            $cacheKey .= ':liveWebPages';
        }

        $tag = $this->cache->remember($cacheKey, 60, function () use ($type, $slugOrId, $withWebPages, $liveWebPagesOnly) {
            $tag = $this->container->make(Tag::class);
            $tag = $type === 'id' ? $tag->where('id', $slugOrId) : $tag->where('slug', $slugOrId);

            if ($withWebPages === true) {
                $tag = $tag->with([
                    'webpages' => function (MorphToMany $query) use ($liveWebPagesOnly) {
                        $query->with('category');
                        if ($liveWebPagesOnly === true) {
                            $query->where('is_live', true);
                        }
                    }
                ]);
            }

            return $tag->withCount([
                'webPages' => function (Builder $query) use ($liveWebPagesOnly) {
                    if ($liveWebPagesOnly === true) {
                        $query->where('is_live', true);
                    }
                }
            ])->first();
        });

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
            $tag = $this->container->make(Tag::class);
            $tag = $tag->create($attributes);
        } catch (Exception $exception) {
            $this->logger->error(TagRepository::class . ": unable to create tag with exception: {$exception->getMessage()}");
            return null;
        }

        $this->deleteTagCache();
        return $tag;
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
            $tagCacheKey = $this->cache->connection()->keys($laravelCachePrefix . 'tag*');
            if (empty($tagCacheKey)) {
                return;
            }
            $this->cache->connection()->del($tagCacheKey);
        } catch (Exception $exception) {
            $this->logger->error(TagRepository::class . ": unable to delete tag cache with exception {$exception->getMessage()}");
        }
    }
}
