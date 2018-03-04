<?php

namespace App\Repositories;

use App\Category;

class CategoryRepository
{
    /**
     * @var Category
     */
    private $category;

    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    /**
     * @param string $slug
     *
     * @return Category | null
     */
    public function findBySlug($slug)
    {
        return $this->category
            ->where('slug', $slug)
            ->get()
            ->first();
    }

    /**
     * @param string $slug
     *
     * @return Category | null
     */
    public function findBySlugWithPosts($slug)
    {
        return $this->category
            ->where('slug', $slug)
            ->with([
                'posts' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                }
            ])
            ->get()
            ->first();
    }
}