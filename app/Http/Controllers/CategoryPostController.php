<?php

namespace App\Http\Controllers;

use App\Http\JsonApi;
use App\Repositories\CacheRepository;
use App\Repositories\CategoryRepository;
use Illuminate\Http\Response;

class CategoryPostController
{
    /**
     * @var JsonApi
     */
    private $jsonApi;
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;
    /**
     * @var CacheRepository
     */
    private $cacheRepository;

    public function __construct(JsonApi $jsonApi, CategoryRepository $categoryRepository, CacheRepository $cacheRepository)
    {
        $this->jsonApi = $jsonApi;
        $this->categoryRepository = $categoryRepository;
        $this->cacheRepository = $cacheRepository;
    }

    /**
     * Find specific category with all related posts.
     *
     * @param Response $response
     * @param string $slug
     *
     * @return Response
     */
    public function show(Response $response, $slug) // TODO figure out how we can paginate included JSON API resources
    {
        $category = $this->cacheRepository->remember("categories-posts.$slug", 60, function () use ($slug) {
            return $this->categoryRepository->findBySlugWithPosts($slug);
        });
        if (is_null($category)) {
            return $this->jsonApi->respondResourceNotFound($response);
        }

        return $this->jsonApi->respondResourceFound($response, $category);
    }
}