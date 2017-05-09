<?php

namespace App\Http\Controllers;


use App\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use itmayziii\Laravel\JsonApi;

class BlogController extends Controller
{
    /**
     * @var JsonApi
     */
    private $jsonApi;

    public function __construct(JsonApi $jsonApi)
    {
        $this->jsonApi = $jsonApi;
    }

    public function index()
    {
        return 'index';
    }

    public function store(Request $request)
    {
        if (Gate::denies('store', new Blog())) {
            return $this->jsonApi->respondUnauthorized();
        }
    }
}