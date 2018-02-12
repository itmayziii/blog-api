<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
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

    public function __construct()
    {
    }

    /**
     * List the existing posts.
     *
     * @param Request $request
     * @param Response $response
     *
     * @return Response
     */
    public function index(Request $request, Response $response)
    {
        return $this->respondResourcesFound($request, $response, new Post);
    }

    /**
     * Find specific posts by slug.
     *
     * @param string $slug
     * @param Request $request
     * @param Response $response
     *
     * @return Response
     */
    public function show($slug, Request $request, Response $response)
    {
        $post = (new Post)->find($slug);

        if (is_null($post)) {
            return $this->respondResourceNotFound($request, $response);
        } else {
            return $this->respondResourceFound($request, $response, $post);
        }
    }

    /**
     * Creates a new post.
     *
     * @param Request $request
     * @param Response $response
     *
     * @return Response
     */
    public function store(Request $request, Response $response)
    {
        if (Gate::denies('store', new Post())) {
            return $this->respondUnauthorized($request, $response);
        }

        $validation = $this->initializeValidation($request, $this->rules);
        if ($validation->fails()) {
            return $this->respondValidationFailed($request, $response, $validation->getMessageBag());
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
            return $this->respondServerError($request, $response, "Unable to create the post");
        }

        return $this->respondResourceCreated($request, $response, $post);
    }


    /**
     * Updates an existing post.
     *
     * @param Request $request
     * @param string $slug
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Response $response, $slug)
    {
        if (Gate::denies('update', new Post())) {
            return $this->respondUnauthorized($request, $response);
        }

        $post = Post::find($slug);
        if (!$post) {
            return $this->respondResourceNotFound($request, $response);
        }

        $validation = $this->initializeValidation($request, $this->rules);
        if ($validation->fails()) {
            return $this->respondValidationFailed($request, $response, $validation->getMessageBag());
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
            return $this->respondServerError($request, $response, 'Unable to update post');
        }

        return $this->respondResourceUpdated($request, $response, $post);
    }

    /**
     * Deletes an existing post.
     *
     * @param Request $request
     * @param Response $response
     * @param string $slug
     *
     * @return Response
     */
    public function delete(Request $request, Response $response, $slug)
    {
        if (Gate::denies('delete', new Post())) {
            return $this->respondUnauthorized($request, $response);
        }

        $post = Post::find($slug);
        if (!$post) {
            return $this->respondResourceNotFound($request, $response);
        }

        try {
            $post->delete();
        } catch (\Exception $e) {
            Log::error("Failed to delete a post with exception: " . $e->getMessage());
            return $this->respondServerError($request, $response, "Unable to delete post");
        }

        return $this->respondResourceDeleted($request, $response);
    }
}