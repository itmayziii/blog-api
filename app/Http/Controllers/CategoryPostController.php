<?php

namespace App\Http\Controllers;

use App\Http\JsonApi;
use App\Post;
use App\Repositories\CacheRepository;
use App\Repositories\CategoryRepository;
use App\WebPage;
use Illuminate\Contracts\Auth\Access\Gate;
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
    /**
     * @var Gate
     */
    private $gate;

    public function __construct(JsonApi $jsonApi, CategoryRepository $categoryRepository, CacheRepository $cacheRepository, Gate $gate)
    {
        $this->jsonApi = $jsonApi;
        $this->categoryRepository = $categoryRepository;
        $this->cacheRepository = $cacheRepository;
        $this->gate = $gate;
    }

    /**
     * Find specific category with all related web pages
     *
     * @param Response $response
     * @param WebPage $webPage
     * @param string $slug
     *
     * @return Response
     */
    public function show(Response $response, WebPage $webPage, $slug) // TODO figure out how we can paginate included JSON API resources
    {
        if ($this->gate->denies('indexAllWebPages', $webPage)) {
            $category = $this->cacheRepository->remember("categories-webpages.{$slug}.live", 60, function () use ($slug) {
                return $this->categoryRepository->findBySlugWithPosts($slug, true);
            });
        } else {
            $category = $this->cacheRepository->remember("categories-webpages.{$slug}.all", 60, function () use ($slug) {
                return $this->categoryRepository->findBySlugWithPosts($slug, false);
            });
        }

        if (is_null($category)) {
            return $this->jsonApi->respondResourceNotFound($response);
        }

        return $this->jsonApi->respondResourceFound($response, $category);
    }
}