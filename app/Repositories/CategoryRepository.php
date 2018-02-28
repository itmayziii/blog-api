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
}