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
    /**
     * @var WebPageRepository
     */
    private $webPageRepository;

    public function __construct(Container $container, Cache $cache, LoggerInterface $logger, Gate $gate, WebPageRepository $webPageRepository)
    {
        $this->container = $container;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->gate = $gate;
        $this->webPageRepository = $webPageRepository;
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
            $tagBuilder = $this->container->make(Tag::class);

            return $tagBuilder
                ->orderBy('created_at', 'desc')
                ->withCount([
                    'webPages' => function (Builder $query) use ($liveWebPagesOnly) {
                        if ($liveWebPagesOnly === true) {
                            $query->where('is_live', true);
                        }
                    }
                ])->paginate($size, null, 'page', $page);
        });
    }

    /**
     * @param string | int $slugOrId
     * @param bool $withWebPages
     * @param string | int $relatedResourcePage
     * @param string | int $relatedResourceSize
     *
     * @return Tag | null
     */
    public function findBySlugOrId($slugOrId, $withWebPages = false, $relatedResourcePage = 1, $relatedResourceSize = 15)
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

        $tag = $this->cache->remember($cacheKey, 60, function () use ($type, $slugOrId, $withWebPages, $liveWebPagesOnly, $relatedResourcePage, $relatedResourceSize) {
            $tagBuilder = $this->container->make(Tag::class);
            $tagBuilder = $type === 'id' ? $tagBuilder->where('id', $slugOrId) : $tagBuilder->where('slug', $slugOrId);
            $tag = $tagBuilder->withCount([
                'webPages' => function (Builder $query) use ($liveWebPagesOnly) {
                    if ($liveWebPagesOnly === true) {
                        $query->where('is_live', true);
                    }
                }
            ])->first();

            if ($withWebPages === true) {
                $webPages = $this->webPageRepository->paginateWebPages($relatedResourcePage, $relatedResourceSize, null, $liveWebPagesOnly);
                $tag->setRelation('webpages', $webPages);
            }

            return $tag;
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
