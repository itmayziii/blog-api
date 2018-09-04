<?php

namespace App\Repositories;

use App\WebPage;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
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
     * @param string $id
     *
     * @return WebPage | bool
     */
    public function findById($id)
    {
        $webpage = $this->cache->remember("webPage.$id", 60, function () use ($id) {
            return $this->webPage->find($id);
        });

        if (is_null($webpage)) {
            $this->logger->notice(WebPageRepository::class . ": unable to find web page by ID: {$id}");
            return false;
        }

        return $webpage;
    }

    /**
     * @param string $path
     *
     * @return WebPage | bool
     */
    public function findByPath($path)
    {
        $path = Str::start($path, '/');
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
        $attributes = $this->mapAttributes($attributes, $user);
        $attributes['created_by'] = $user->getAuthIdentifier();
        $attributes['last_updated_by'] = $user->getAuthIdentifier();
        try {
            $webpage = $this->webPage->create($attributes);
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
        $attributes = $this->mapAttributes($attributes, $user);
        $attributes['last_updated_by'] = $user->getAuthIdentifier();
        try {
            $webPage->update($attributes);
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
            'category_id'     => isset($attributes['category_id']) ? $attributes['category_id'] : null,
            'path'            => isset($attributes['path']) ? $attributes['path'] : null,
            'is_live'         => isset($attributes['is_live']) ? $attributes['is_live'] : null,
            'title'           => isset($attributes['title']) ? $attributes['title'] : null,
            'content'         => isset($attributes['content']) ? $attributes['content'] : null,
            'preview'         => isset($attributes['preview']) ? $attributes['preview'] : null,
            'image_path_sm'   => isset($attributes['image_path_sm']) ? $attributes['image_path_sm'] : null,
            'image_path_md'   => isset($attributes['image_path_md']) ? $attributes['image_path_md'] : null,
            'image_path_lg'   => isset($attributes['image_path_lg']) ? $attributes['image_path_lg'] : null,
            'image_path_meta' => isset($attributes['image_path_meta']) ? $attributes['image_path_meta'] : null
        ];
    }
}