<?php

namespace App\Repositories;

use App\Page;
use App\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PageRepository
{
    /**
     * @var Page
     */
    private $page;

    public function __construct(Page $page)
    {
        $this->page = $page;
    }

    /**
     * @param $page
     * @param $size
     *
     * @return LengthAwarePaginator
     */
    public function paginateAllPages($page, $size)
    {
        return $this->page
            ->orderBy('updated_at', 'desc')
            ->paginate($size, null, 'page', $page);
    }

    /**
     * @param $page
     * @param $size
     *
     * @return LengthAwarePaginator
     */
    public function paginateLivePages($page, $size)
    {
        return $this->page
            ->where('is_live', true)
            ->orderBy('updated_at', 'desc')
            ->paginate($size, null, 'page', $page);
    }

    /**
     * @param string $slug
     *
     * @return Page | null
     */
    public function findBySlug($slug)
    {
        return $this->page
            ->where('slug', $slug)
            ->get()
            ->first();
    }

    /**
     * @param array $attributes
     *
     * @return Post
     */
    public function create($attributes)
    {
        return $this->page->create([
            'title'   => isset($attributes['title']) ? $attributes['title'] : null,
            'slug'    => isset($attributes['slug']) ? $attributes['slug'] : null,
            'content' => isset($attributes['content']) ? $attributes['content'] : null,
            'is_live' => isset($attributes['is-live']) ? (bool)$attributes['is-live'] : null
        ]);
    }

    /**
     * @param Page $page
     * @param array $attributes
     *
     * @return boolean
     */
    public function update($page, $attributes)
    {
        return $page->update([
            'title'   => isset($attributes['title']) ? $attributes['title'] : null,
            'slug'    => isset($attributes['slug']) ? $attributes['slug'] : null,
            'content' => isset($attributes['content']) ? $attributes['content'] : null,
            'is_live' => isset($attributes['is-live']) ? (bool)$attributes['is-live'] : null
        ]);
    }
}