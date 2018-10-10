<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Pagination\LengthAwarePaginator;

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

    public function __construct(Category $category, Cache $cache)
    {
        $this->category = $category;
        $this->cache = $cache;
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