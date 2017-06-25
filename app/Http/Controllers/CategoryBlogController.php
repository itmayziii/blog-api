<?php

namespace App\Http\Controllers;

use App\Category;
use itmayziii\Laravel\JsonApi;

class CategoryBlogController extends Controller
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
     * Find specific category with all related blogs.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return $this->jsonApi->respondResourceNotFound();
        }

        $category->load(['blogs' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }]);

        return $this->jsonApi->respondResourceFound($category);
    }
}