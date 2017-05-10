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

    /**
     * Creates a new blog.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::denies('store', new Blog())) {
            return $this->jsonApi->respondUnauthorized();
        }

        $rules = [
            'user-id'     => 'required',
            'category-id' => 'required',
            'title'       => 'required|max:200',
            'content'     => 'required|max:10000'
        ];
        $validation = $this->initializeValidation($request, $rules);

        if ($validation->fails()) {
            return $this->jsonApi->respondValidationFailed($validation->getMessageBag());
        }

        $blog = new Blog();
        $blog->setAttribute('user_id', $request->input('user-id'));
        $blog->setAttribute('category_id', $request->input('category-id'));
        $blog->setAttribute('title', $request->input('title'));
        $blog->setAttribute('content', $request->input('content'));

        try {
            $blog->save();
        } catch (\Exception $e) {
            $this->jsonApi->respondBadRequest();
        }

        return $this->jsonApi->respondResourceCreated($blog);
    }
}