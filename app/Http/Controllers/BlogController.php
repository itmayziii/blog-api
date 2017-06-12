<?php

namespace App\Http\Controllers;

use App\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use itmayziii\Laravel\JsonApi;

class BlogController extends Controller
{
    /**
     * @var JsonApi
     */
    private $jsonApi;

    /**
     * Validation Rules
     *
     * @var array
     */
    private $rules = [
        'user-id'     => 'required',
        'category-id' => 'required',
        'title'       => 'required|max:200',
        'content'     => 'required|max:10000'
    ];

    public function __construct(JsonApi $jsonApi)
    {
        $this->jsonApi = $jsonApi;
    }

    /**
     * List the existing blogs.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->jsonApi->respondResourcesFound(new Blog(), $request);
    }

    /**
     * Find specific blogs by slug.
     *
     * @param string $slug
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $blog = Blog::find($slug);

        if ($blog) {
            return $this->jsonApi->respondResourceFound($blog);
        } else {
            return $this->jsonApi->respondResourceNotFound();
        }
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

        $validation = $this->initializeValidation($request, $this->rules);
        if ($validation->fails()) {
            return $this->jsonApi->respondValidationFailed($validation->getMessageBag());
        }

        try {
            $blog = (new Blog)->create([
                'user_id'     => $request->input('user-id'),
                'category_id' => $request->input('category-id'),
                'slug'        => str_slug($request->input('title')),
                'title'       => $request->input('title'),
                'content'     => $request->input('content')
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to create a blog with exception: " . $e->getMessage());
            return $this->jsonApi->respondBadRequest("Unable to create the blog");
        }

        return $this->jsonApi->respondResourceCreated($blog);
    }

    /**
     * Updates an existing blog.
     *
     * @param Request $request
     * @param String $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (Gate::denies('update', new Blog())) {
            return $this->jsonApi->respondUnauthorized();
        }

        $blog = Blog::find($id);
        if (!$blog) {
            return $this->jsonApi->respondResourceNotFound();
        }

        $validation = $this->initializeValidation($request, $this->rules);
        if ($validation->fails()) {
            return $this->jsonApi->respondValidationFailed($validation->getMessageBag());
        }

        try {
            $blog->update([
                'user_id'     => $request->input('user-id'),
                'category_id' => $request->input('category-id'),
                'title'       => $request->input('title'),
                'content'     => $request->input('content')
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to update a blog with exception: " . $e->getMessage());
            return $this->jsonApi->respondBadRequest("Unable to update blog");
        }

        return $this->jsonApi->respondResourceUpdated($blog);
    }

    /**
     * Deletes an existing blog.
     *
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        if (Gate::denies('delete', new Blog())) {
            return $this->jsonApi->respondUnauthorized();
        }

        $blog = Blog::find($id);
        if (!$blog) {
            return $this->jsonApi->respondResourceNotFound();
        }

        try {
            $blog->delete();
        } catch (\Exception $e) {
            Log::error("Failed to delete a blog with exception: " . $e->getMessage());
            return $this->jsonApi->respondBadRequest("Unable to delete blog");
        }

        return $this->jsonApi->respondResourceDeleted($blog);
    }
}