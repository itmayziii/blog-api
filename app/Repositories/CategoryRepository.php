<?php

namespace App\Repositories;

use App\Models\Category;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Pagination\LengthAwarePaginator;
use Psr\Log\LoggerInterface;

class CategoryRepository
{
    /**
     * @var Category | Builder
     */
    private $category;
    /**
     * @var Cache
     */
    private $cache;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Category $category, Cache $cache, LoggerInterface $logger)
    {
        $this->category = $category;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * @param string $slug
     * @param bool $withWebPages
     * @param bool $liveWebPagesOnly
     *
     * @return Category | null
     */
    public function findBySlug($slug, $withWebPages = false, $liveWebPagesOnly = false)
    {
        $cacheKey = "category:slug.$slug";
        if ($withWebPages === true) {
            $cacheKey .= ':withWebPages';
        }
        if ($liveWebPagesOnly === true) {
            $cacheKey .= ':liveWebpages';
        }

        $category = $this->cache->remember($cacheKey, 60, function () use ($slug, $withWebPages, $liveWebPagesOnly) {
            $category = $this->category
                ->where('slug', $slug);

            if ($withWebPages === true) {
                $category = $category->with([
                    'webpages' => function (HasMany $query) use ($liveWebPagesOnly) {
                        $query->with('category');
                        if ($liveWebPagesOnly === true) {
                            $query->where('is_live', true);
                        }
                    }
                ]);
            }

            return $category->first();
        });

        if (is_null($category)) {
            $this->logger->notice(CategoryRepository::class . ": unable to find category with id: {$slug}");
            return null;
        }

        return $category;
    }

    /**
     * @param string | int $id
     *
     * @return Category | null
     */
    public function findById($id)
    {
        $category = $this->cache->remember("category:id.$id", 60, function () use ($id) {
            return $this->category->find($id);
        });

        if (is_null($category)) {
            $this->logger->notice(CategoryRepository::class . ": unable to find category with id: {$id}");
            return null;
        }

        return $category;
    }

    /**
     * @param int $page
     * @param int $size
     *
     * @return LengthAwarePaginator
     */
    public function paginate($page, $size)
    {
        return $this->cache->remember("categories", 60, function () use ($page, $size) {
            return $this->category->paginate($size, null, 'page', $page);
        });
    }

    /**
     * @param array $attributes
     * @param Authenticatable $user
     *
     * @return Category
     */
    public function create($attributes, Authenticatable $user)
    {
        $attributes = $this->mapAttributes($attributes);
        $userId = $user->getAuthIdentifier();
        $attributes['created_by'] = $userId;
        $attributes['last_updated_by'] = $userId;

        try {
            $category = $this->category->create($attributes);
        } catch (Exception $exception) {
            $this->logger->error(CategoryRepository::class . ": unable to create category with exception: {$exception->getMessage()}");
            return null;
        }

        $this->deleteCategoryCache();
        return $category;
    }

    /**
     * @param Category $category
     * @param array $attributes
     * @param Authenticatable $user
     *
     * @return Category
     */
    public function update(Category $category, $attributes, Authenticatable $user)
    {
        $attributes = $this->mapAttributes($attributes);
        $attributes['last_updated_by'] = $user->getAuthIdentifier();;

        try {
            $category->update($attributes);
        } catch (Exception $exception) {
            $this->logger->error(CategoryRepository::class . ": unable to update category with exception: {$exception->getMessage()}");
            return null;
        }

        $this->deleteCategoryCache();
        return $category;
    }

    /**
     * @param Category $category
     *
     * @return bool
     */
    public function delete(Category $category)
    {
        try {
            $category->delete();
        } catch (Exception $exception) {
            $this->logger->error(CategoryRepository::class . ": unable to delete category with exception: {$exception->getMessage()}");
            return false;
        }

        $this->deleteCategoryCache();
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
            'name'        => isset($attributes['name']) ? $attributes['name'] : null,
            'plural_name' => isset($attributes['plural_name']) ? $attributes['plural_name'] : null,
            'slug'        => isset($attributes['slug']) ? $attributes['slug'] : null
        ];
    }

    /**
     * @return void
     */
    private function deleteCategoryCache()
    {
        try {
            $laravelCachePrefix = $this->cache->getPrefix();
            $categoryCacheKeys = $this->cache->connection()->keys($laravelCachePrefix . 'categor*');
            if (empty($categoryCacheKeys)) {
                return;
            }
            $this->cache->connection()->del($categoryCacheKeys);
        } catch (Exception $exception) {
            $this->logger->error(WebPageRepository::class . ": unable to delete category cache with exception {$exception->getMessage()}");
        }
    }
}
