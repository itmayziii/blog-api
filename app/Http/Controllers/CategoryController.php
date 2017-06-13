<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;
use itmayziii\Laravel\JsonApi;

class CategoryController extends Controller
{
    private $jsonApi;

    public function __construct(JsonApi $jsonApi)
    {
        $this->jsonApi = $jsonApi;
    }

    public function index(Request $request)
    {
        return $this->jsonApi->respondResourcesFound(new Category(), $request);
    }
}