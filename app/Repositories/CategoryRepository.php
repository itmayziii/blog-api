<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\WebPage;
use Exception;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Pagination\LengthAwarePaginator;
use Psr\Log\LoggerInterface;

class CategoryRepository
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
     * @param string | int $slugOrId
     * @param bool $withWebPages
     *
     * @return Category | null
     */
    public function findBySlugOrId($slugOrId, $withWebPages = false)
    {
        $type = is_numeric($slugOrId) ? 'id' : 'slug';
        $cacheKey = "category:$type.$slugOrId";
        if ($withWebPages === true) {
            $cacheKey .= ':withWebPages';
        }
        $liveWebPagesOnly = $this->gate->denies('indexAllWebPages', WebPage::class);
        if ($liveWebPagesOnly === true) {
            $cacheKey .= ':liveWebPages';
        }

        $category = $this->cache->remember($cacheKey, 60, function () use ($type, $slugOrId, $withWebPages, $liveWebPagesOnly) {
            $category = $this->container->make(Category::class);
            $category = $type === 'id' ? $category->where('id', $slugOrId) : $category->where('slug', $slugOrId);

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
            $categoryBuilder = $this->container->make(Category::class);

            return $categoryBuilder
                ->orderBy('created_at', 'desc')
                ->paginate($size, null, 'page', $page);
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
            $category = $this->container->make(Category::class)->create($attributes);
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
