<?php

namespace App\Repositories;

use App\Page;
use Exception;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Psr\Log\LoggerInterface;

class PageRepository
{
    /**
     * @var Page | Builder
     */
    private $page;
    /**
     * @var Cache
     */
    private $cache;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Page $page, Cache $cache, LoggerInterface $logger)
    {
        $this->page = $page;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * @param string | int $page
     * @param string | int $size
     *
     * @return LengthAwarePaginator
     */
    public function paginateAllPages($page, $size)
    {
        return $this->cache->remember("pages.live.page$page.size$size", 60, function ($size, $page) {
            return $this->page
                ->orderBy('updated_at', 'desc')
                ->paginate($size, null, 'page', $page);
        });
    }

    /**
     * @param string | int $page
     * @param string | int $size
     *
     * @return LengthAwarePaginator
     */
    public function paginateLivePages($page, $size)
    {
        return $this->cache->remember("pages.live.page$page.size$size", 60, function ($size, $page) {
            return $this->page
                ->where('is_live', true)
                ->orderBy('updated_at', 'desc')
                ->paginate($size, null, 'page', $page);
        });
    }

    /**
     * @param string $slug
     *
     * @return Page | bool
     */
    public function findBySlug($slug)
    {
        $page = $this->cache->remember("page.$slug", 60, function ($slug) {
            return $this->page->where('slug', $slug)
                ->get()
                ->first();
        });


        if (is_null($page)) {
            $this->logger->notice(PageRepository::class . ": unable to find page by slug: {$slug}");
            return false;
        }

        return $page;
    }

    /**
     * @param array $attributes
     *
     * @return Page | bool
     */
    public function create($attributes)
    {
        try {
            $page = $this->page->create([
                'title'   => isset($attributes['title']) ? $attributes['title'] : null,
                'slug'    => isset($attributes['slug']) ? $attributes['slug'] : null,
                'content' => isset($attributes['content']) ? $attributes['content'] : null,
                'is_live' => isset($attributes['is-live']) ? (bool)$attributes['is-live'] : null
            ]);
        } catch (Exception $exception) {
            $this->logger->error(PageRepository::class . ": unable to create page with exception: {$exception->getMessage()}");
            return false;
        }

        $this->cache->clear();
        return $page;
    }

    /**
     * @param Page $page
     * @param array $attributes
     *
     * @return Page | bool
     */
    public function update($page, $attributes)
    {
        try {
            $page->update([
                'title'   => isset($attributes['title']) ? $attributes['title'] : null,
                'slug'    => isset($attributes['slug']) ? $attributes['slug'] : null,
                'content' => isset($attributes['content']) ? $attributes['content'] : null,
                'is_live' => isset($attributes['is-live']) ? (bool)$attributes['is-live'] : null
            ]);
        } catch (Exception $exception) {
            $this->logger->error(PageRepository::class . ": unable to update page with exception: {$exception->getMessage()}");
            return false;
        }

        $this->cache->clear();
        return $page;
    }

    /**
     * @param Page $page
     *
     * @return bool
     */
    public function delete(Page $page)
    {
        try {
            $page->delete();
        } catch (Exception $exception) {
            $this->logger->error(PageRepository::class . ": unable to delete page with with exception: {$exception->getMessage()}");
            return false;
        }

        $this->cache->clear();
        return true;
    }
}