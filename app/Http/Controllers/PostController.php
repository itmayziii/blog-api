<?php

namespace App\Http\Controllers;

use App\Http\JsonApi;
use App\Post;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Psr\Log\LoggerInterface;

class PostController extends Controller
{
    /**
     * Validation Rules
     *
     * @var array
     */
    private $validationRules = [
        'user-id'     => 'required',
        'category-id' => 'required',
        'title'       => 'required|max:200',
        'slug'        => 'required|max:255',
        'content'     => 'required|max:10000'
    ];
    /**
     * @var JsonApi
     */
    private $jsonApi;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Gate
     */
    private $gate;

    public function __construct(JsonApi $jsonApi, Gate $gate, LoggerInterface $logger)
    {
        $this->jsonApi = $jsonApi;
        $this->gate = $gate;
        $this->logger = $logger;
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
        return $this->jsonApi->respondResourcesFound($request, $response, new Post);
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
            return $this->jsonApi->respondResourceNotFound($request, $response);
        } else {
            return $this->jsonApi->respondResourceFound($request, $response, $post);
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
        if ($this->gate->denies('store', new Post())) {
            return $this->jsonApi->respondUnauthorized($request, $response);
        }

        $validation = $this->initializeValidation($request, $this->validationRules);
        if ($validation->fails()) {
            return $this->jsonApi->respondValidationFailed($request, $response, $validation->getMessageBag());
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
            $this->logger->error("Failed to create a post with exception: " . $e->getMessage());
            return $this->jsonApi->respondServerError($request, $response, "Unable to create the post");
        }

        return $this->jsonApi->respondResourceCreated($request, $response, $post);
    }


    /**
     * Updates an existing post.
     *
     * @param Request $request
     * @param Response $response
     * @param string $slug
     *
     * @return Response
     */
    public function update(Request $request, Response $response, $slug)
    {
        if ($this->gate->denies('update', new Post())) {
            return $this->jsonApi->respondUnauthorized($request, $response);
        }

        $post = (new Post)->find($slug);
        if (!$post) {
            return $this->jsonApi->respondResourceNotFound($request, $response);
        }

        $validation = $this->initializeValidation($request, $this->validationRules);
        if ($validation->fails()) {
            return $this->jsonApi->respondValidationFailed($request, $response, $validation->getMessageBag());
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
            $this->logger->error("Failed to update a post with exception: " . $e->getMessage());
            return $this->jsonApi->respondServerError($request, $response, 'Unable to update post');
        }

        return $this->jsonApi->respondResourceUpdated($request, $response, $post);
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
        if ($this->gate->denies('delete', new Post())) {
            return $this->jsonApi->respondUnauthorized($request, $response);
        }

        $post = (new Post)->find($slug);
        if (!$post) {
            return $this->jsonApi->respondResourceNotFound($request, $response);
        }

        try {
            $post->delete();
        } catch (\Exception $e) {
            $this->logger->error("Failed to delete a post with exception: " . $e->getMessage());
            return $this->jsonApi->respondServerError($request, $response, "Unable to delete post");
        }

        return $this->jsonApi->respondResourceDeleted($request, $response);
    }
}