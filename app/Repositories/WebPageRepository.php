<?php

namespace App\Repositories;

use App\WebPage;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use MongoDB\Database as MongoDB;
use MongoDB\Driver\Exception\Exception as MongoException;
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
     * @param string $type
     * @param bool $liveOnly
     *
     * @return LengthAwarePaginator
     */
    public function paginateWebPages($page, $size, $type = '', $liveOnly = false)
    {
        $status = ($liveOnly === true) ? 'live' : 'all';
        $cacheKey = "webPages:$status";
        if (!empty($type)) {
            $cacheKey .= ":$type";
        }
        $cacheKey .= ":page.$page:size.$size";

        return $this->cache->remember($cacheKey, 60, function () use ($size, $page, $type, $liveOnly) {
            $query = $this->webPage
                ->orderBy('created_at', 'desc');

            if ($liveOnly) {
                $query->where('is_live', true);
            }

            if (!empty($type)) {
                $query->whereHas('type', function ($query) use ($type) {
                    $query->where('name', $type);
                });
            }

            return $query->paginate($size, null, 'page', $page);
        });
    }

    /**
     * @param string $type
     * @param string $slug
     *
     * @return WebPage | null
     */
    public function findBySlug($type, $slug)
    {
        $webpage = $this->cache->remember("webPage.$type.$slug", 60, function () use ($type, $slug) {
            return $this->webPage
                ->where('slug', $slug)
                ->whereHas('type', function ($query) use ($type) {
                    $query->where('name', $type);
                })
                ->with('type')
                ->first();
        });

        if (is_null($webpage)) {
            $this->logger->notice(WebPageRepository::class . ": unable to find web page with type: {$type}, slug: {$slug}");
            return null;
        }

        return $webpage;
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
            'type_id'           => isset($attributes['type_id']) ? $attributes['type_id'] : null,
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