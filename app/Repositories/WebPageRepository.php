<?php

namespace App\Repositories;

use App\Models\WebPage;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use MongoDB\Database as MongoDB;
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
    /**
     * @var MongoDB
     */
    private $mongoDB;

    public function __construct(WebPage $webPage, Cache $cache, LoggerInterface $logger, MongoDB $mongoDB)
    {
        $this->webPage = $webPage;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->mongoDB = $mongoDB;
    }

    /**
     * @param string | int $page
     * @param string | int $size
     * @param string $categorySlug
     * @param bool $liveOnly
     *
     * @return LengthAwarePaginator
     */
    public function paginateWebPages($page, $size, $categorySlug = null, $liveOnly = false)
    {
        $status = ($liveOnly === true) ? 'live' : 'all';
        $cacheKey = "webPages:$status";
        if (!is_null($categorySlug)) {
            $cacheKey .= ":category.$categorySlug";
        }
        $cacheKey .= ":page.$page:size.$size";

        return $this->cache->remember($cacheKey, 60, function () use ($size, $page, $categorySlug, $liveOnly) {
            $query = $this->webPage
                ->orderBy('created_at', 'desc')
                ->with('category');

            if ($liveOnly === true) {
                $query->where('is_live', true);
            }

            if (!is_null($categorySlug)) {
                $query->whereHas('category', function (Builder $query) use ($categorySlug) {
                    $query->where('slug', $categorySlug);
                });
            }

            return $query->paginate($size, null, 'page', $page);
        });
    }

    /**
     * @param string $categorySlug
     * @param string $slug
     *
     * @return WebPage | null
     */
    public function findBySlug($categorySlug, $slug)
    {
        $webPage = $this->cache->remember("webPage.$categorySlug.$slug", 60, function () use ($categorySlug, $slug) {
            return $this->webPage
                ->where('slug', $slug)
                ->whereHas('category', function (Builder $query) use ($categorySlug) {
                    $query->where('slug', $categorySlug);
                })
                ->with('category')
                ->first();
        });

        if (is_null($webPage)) {
            $this->logger->notice(WebPageRepository::class . ": unable to find web page with category: {$categorySlug}, slug: {$slug}");
            return null;
        }

        return $webPage;
    }

    /**
     * @param int | string $id
     *
     * @return Webpage | null
     */
    public function findById($id)
    {
        $webPage = $this->cache->remember("webPage:id.$id", 60, function () use ($id) {
            return $this->webPage->find($id);
        });

        if (is_null($webPage)) {
            $this->logger->notice(WebPageRepository::class . ": unable to find web page with id: {$id}");
            return null;
        }

        return $webPage;
    }

    /**
     * @param array $attributes
     * @param Authenticatable $user
     *
     * @return WebPage | null
     */
    public function create($attributes, Authenticatable $user)
    {
        $attributes = $this->mapAttributes($attributes);
        $attributes['created_by'] = $user->getAuthIdentifier();
        $attributes['last_updated_by'] = $user->getAuthIdentifier();

        try {
            $webpage = $this->webPage->create($attributes);
        } catch (Exception $exception) {
            $this->logger->error(WebPageRepository::class . ": unable to create web page with exception: {$exception->getMessage()}");
            return null;
        }

        try {
            $this->mongoDB->selectCollection('webpage_modules')->updateOne(['webpage_id' => $webpage->getAttribute('id')], [
                'webpage_id' => $webpage->getAttribute('id'),
                'modules'    => isset($attributes['modules']) ? $attributes['modules'] : []
            ], ['upsert' => true]);
        } catch (Exception $exception) {
            $this->logger->error(WebPageRepository::class . ": webpage may have been partially created with exception: {$exception->getMessage()}");
        }

        $this->cache->clear();
        return $webpage;
    }

    /**
     * @param WebPage $webPage
     * @param array $attributes
     * @param Authenticatable $user
     *
     * @return WebPage | null
     */
    public function update(WebPage $webPage, $attributes, Authenticatable $user)
    {
        $attributes = $this->mapAttributes($attributes);
        $attributes['last_updated_by'] = $user->getAuthIdentifier();
        try {
            $webPage->update($attributes);
        } catch (Exception $exception) {
            $this->logger->error(WebPageRepository::class . ": unable to update web page with exception: {$exception->getMessage()}");
            return null;
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
     *
     * @return array
     */
    private function mapAttributes($attributes)
    {
        return [
            'category_id'       => isset($attributes['category_id']) ? $attributes['category_id'] : null,
            'slug'              => isset($attributes['slug']) ? $attributes['slug'] : null,
            'is_live'           => isset($attributes['is_live']) ? $attributes['is_live'] : null,
            'title'             => isset($attributes['title']) ? $attributes['title'] : null,
            'short_description' => isset($attributes['short_description']) ? $attributes['short_description'] : null,
            'image_path_sm'     => isset($attributes['image_path_sm']) ? $attributes['image_path_sm'] : null,
            'image_path_md'     => isset($attributes['image_path_md']) ? $attributes['image_path_md'] : null,
            'image_path_lg'     => isset($attributes['image_path_lg']) ? $attributes['image_path_lg'] : null,
            'image_path_meta'   => isset($attributes['image_path_meta']) ? $attributes['image_path_meta'] : null
        ];
    }
}