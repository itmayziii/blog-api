<?php

namespace App\Http\Controllers;

use App\Category;
use App\Http\JsonApi;
use Illuminate\Http\Response;

class CategoryPostController extends Controller
{
    /**
     * @var JsonApi
     */
    private $jsonApi;

    public function __construct(JsonApi $jsonApi)
    {
        $this->jsonApi = $jsonApi;
    }

    /**
     * Find specific category with all related posts.
     *
     * @param Response $response
     * @param Category $category
     * @param int $id
     *
     * @return Response
     */
    public function show(Response $response, Category $category, $id)
    {
        $category = $category->find($id);
        if (!$category) {
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