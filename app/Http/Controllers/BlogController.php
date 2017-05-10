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

        $rules = [
            'user_id'     => 'required',
            'category_id' => 'required',
            'title'       => 'required',
            'content'     => 'required'
        ];
        $validation = $this->initializeValidation($request, $rules);

        if ($validation->fails()) {
            return $this->jsonApi->respondValidationFailed($validation->getMessageBag());
        }

        $blog = new Blog();
        $blog->setAttribute('user_id', $request->input('user_id'));
        $blog->setAttribute('category_id', $request->input('category_id'));
        $blog->setAttribute('title', $request->input('title'));
        $blog->setAttribute('content', $request->input('content'));

        return $this->jsonApi->respondResourceCreated($blog);
    }
}