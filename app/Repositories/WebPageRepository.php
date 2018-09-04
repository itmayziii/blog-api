<?php

namespace App\Repositories;

use App\WebPage;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Psr\Log\LoggerInterface;

class WebPageRepository
{
    /**
     * @var WebPage | Builder
     */
    private $webPage;
    /**
     * @var Cache
     */
    private $cache;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(WebPage $webPage, Cache $cache, LoggerInterface $logger)
    {
        $this->webPage = $webPage;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * @param string | int $page
     * @param string | int $size
     *
     * @return LengthAwarePaginator
     */
    public function pageinateAllWebPages($page, $size)
    {
        return $this->cache->remember("webPages.all.page$page.size$size", 60, function () use ($size, $page) {
            return $this->webPage
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
    public function paginateLiveWebPages($page, $size)
    {
        return $this->cache->remember("webPages.live.page$page.size$size", 60, function () use ($size, $page) {
            return $this->webPage
                ->where('is_live', 1)
                ->orderBy('created_at', 'desc')
                ->paginate($size, null, 'page', $page);
        });
    }

    /**
     * @param string $path
     *
     * @return WebPage | bool
     */
    public function findByPath($path)
    {
        $webpage = $this->cache->remember("webPage.$path", 60, function () use ($path) {
            return $this->webPage
                ->where('path', $path)
                ->get()
                ->first();
        });

        if (is_null($webpage)) {
            $this->logger->notice(WebPageRepository::class . ": unable to find web page by path: {$path}");
            return false;
        }

        return $webpage;
    }

    /**
     * @param array $attributes
     * @param Authenticatable $user
     *
     * @return WebPage | bool
     */
    public function create($attributes, Authenticatable $user)
    {
        try {
            $webpage = $this->webPage->create($this->mapAttributes($attributes, $user));
        } catch (Exception $exception) {
            $this->logger->error(WebPageRepository::class . ": unable to create web page with exception: {$exception->getMessage()}");
            return false;
        }

        $this->cache->clear();
        return $webpage;
    }

    /**
     * @param WebPage $webPage
     * @param array $attributes
     * @param Authenticatable $user
     *
     * @return WebPage | bool
     */
    public function update(WebPage $webPage, $attributes, Authenticatable $user)
    {
        try {
            $webPage->update($this->mapAttributes($attributes, $user));
        } catch (Exception $exception) {
            $this->logger->error(WebPageRepository::class . ": unable to update web page with exception: {$exception->getMessage()}");
            return false;
        }

        $this->cache->clear();
        return $webPage;
    }

    /**
     * @param WebPage $webPage
     *
     * @return bool
     */
    public function delete(WebPage $webPage)
    {
        try {
            $webPage->delete();
        } catch (Exception $exception) {
            $this->logger->error(WebPageRepository::class . ": unable to delete web page with with exception: {$exception->getMessage()}");
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