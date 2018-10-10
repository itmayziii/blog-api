<?php

namespace App\Repositories;

use App\Models\Category;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Cache\Repository as Cache;
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
     *
     * @return Category | null
     */
    public function findBySlug($slug)
    {
        return $this->cache->remember("category:slug.$slug", 60, function () use ($slug) {
            return $this->category
                ->where('slug', $slug)
                ->first();
        });
    }

    /**
     * @param string | int $id
     *
     * @return Category | null
     */
    public function findById($id)
    {
        return $this->cache->remember("category:id.$id", 60, function () use ($id) {
            return $this->category->find($id);
        });
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

        $this->cache->clear();
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

        $this->cache->clear();
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
            'name'        => isset($attributes['name']) ? $attributes['name'] : null,
            'plural_name' => isset($attributes['plural_name']) ? $attributes['plural_name'] : null,
            'slug'        => isset($attributes['slug']) ? $attributes['slug'] : null
        ];
    }

//    /**
//     * @param string $slug
//     * @param bool $livePostsOnly
//     *
//     * @return Category | null
//     */
//    public function findBySlugWithPosts($slug, $livePostsOnly = true)
//    {
//        return $this->category
//            ->where('slug', $slug)
//            ->with([
//                'posts' => function ($query) use ($livePostsOnly) {
//                    if ($livePostsOnly) {
//                        $query->where('status', 'live');
//                    }
//
//                    $query->orderBy('created_at', 'desc');
//                }
//            ])
//            ->get()
//            ->first();
//    }
}