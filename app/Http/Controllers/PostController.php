<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use itmayziii\Laravel\JsonApi;

class PostController extends Controller
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
        'slug'        => 'required|max:255',
        'content'     => 'required|max:10000'
    ];

    public function __construct(JsonApi $jsonApi)
    {
        $this->jsonApi = $jsonApi;
    }

    /**
     * List the existing posts.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->jsonApi->respondResourcesFound(new Post(), $request);
    }

    /**
     * Find specific posts by slug.
     *
     * @param string $slug
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $post = Post::find($slug);

        if ($post) {
            return $this->jsonApi->respondResourceFound($post);
        } else {
            return $this->jsonApi->respondResourceNotFound();
        }
    }

    /**
     * Creates a new post.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::denies('store', new Post())) {
            return $this->jsonApi->respondUnauthorized();
        }

        $validation = $this->initializeValidation($request, $this->rules);
        if ($validation->fails()) {
            return $this->jsonApi->respondValidationFailed($validation->getMessageBag());
        }

        try {
            $post = (new Post)->create([
                'user_id'     => $request->input('user-id'),
                'category_id' => $request->input('category-id'),
                'slug'        => $request->input('slug'),
                'title'       => $request->input('title'),
                'content'     => $request->input('content'),
                'image_path'  => $request->input('image-path')
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to create a post with exception: " . $e->getMessage());
            return $this->jsonApi->respondBadRequest("Unable to create the post");
        }

        return $this->jsonApi->respondResourceCreated($post);
    }

    /**
     * Updates an existing post.
     *
     * @param Request $request
     * @param string $slug
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $slug)
    {
        if (Gate::denies('update', new Post())) {
            return $this->jsonApi->respondUnauthorized();
        }

        $post = Post::find($slug);
        if (!$post) {
            return $this->jsonApi->respondResourceNotFound();
        }

        $validation = $this->initializeValidation($request, $this->rules);
        if ($validation->fails()) {
            return $this->jsonApi->respondValidationFailed($validation->getMessageBag());
        }

        try {
            $post->update([
                'user_id'     => $request->input('user-id'),
                'category_id' => $request->input('category-id'),
                'slug'        => $request->input('slug'),
                'title'       => $request->input('title'),
                'content'     => $request->input('content')
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to update a post with exception: " . $e->getMessage());
            return $this->jsonApi->respondBadRequest("Unable to update post");
        }

        return $this->jsonApi->respondResourceUpdated($post);
    }

    /**
     * Deletes an existing post.
     *
     * @param string $slug
     * @return \Illuminate\Http\Response
     */
    public function delete($slug)
    {
        if (Gate::denies('delete', new Post())) {
            return $this->jsonApi->respondUnauthorized();
        }

        $post = Post::find($slug);
        if (!$post) {
            return $this->jsonApi->respondResourceNotFound();
        }

        try {
            $post->delete();
        } catch (\Exception $e) {
            Log::error("Failed to delete a post with exception: " . $e->getMessage());
            return $this->jsonApi->respondBadRequest("Unable to delete post");
        }

        return $this->jsonApi->respondResourceDeleted($post);
    }
}