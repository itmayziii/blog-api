<?php

namespace App\Http\Controllers;

use App\Http\JsonApi;
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

    public function __construct(JsonApi $jsonApi, CategoryRepository $categoryRepository)
    {
        $this->jsonApi = $jsonApi;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Find specific category with all related posts.
     *
     * @param Response $response
     * @param string $slug
     *
     * @return Response
     */
    public function show(Response $response, $slug)
    {
        $category = $this->categoryRepository->findBySlug($slug);
        if (is_null($category)) {
            return $this->jsonApi->respondResourceNotFound($response);
        }

        $category->load([
            'posts' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }
        ]);

        return $this->jsonApi->respondResourceFound($response, $category);
    }
}