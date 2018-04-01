<?php

namespace App\Repositories;

use App\Page;

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

    public function paginateAllPages($page, $size)
    {
        return $this->page
            ->orderBy('updated_at', 'desc')
            ->paginate($size, null, 'page', $page);
    }

    public function paginateLivePages($page, $size)
    {
        return $this->page
            ->where('is_live', true)
            ->orderBy('updated_at', 'desc')
            ->paginate($size, null, 'page', $page);
    }
}