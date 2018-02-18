<?php

namespace App\Http\Controllers;

use App\Http\JsonApi;
use App\Post;
use Exception;
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
     * @param Post $post
     *
     * @return Response
     */
    public function index(Request $request, Response $response, Post $post)
    {
        $size = $request->query('size', 15);
        $page = $request->query('page', 1);

        $paginator = $post->where('status', 'draft')
            ->orderBy('created_at', 'desc')
            ->paginate($size, null, 'page', $page);

        return $this->jsonApi->respondResourcesFound($response, $paginator);
    }

    /**
     * Find specific posts by slug.
     *
     * @param Response $response
     * @param Post $post
     * @param string $slug
     *
     * @return Response
     */
    public function show(Response $response, Post $post, $slug)
    {
        $post = $post->find($slug);

        if (is_null($post)) {
            return $this->jsonApi->respondResourceNotFound($response);
        } else {
            return $this->jsonApi->respondResourceFound($response, $post);
        }
    }

    /**
     * Creates a new post.
     *
     * @param Request $request
     * @param Response $response
     * @param Post $post
     *
     * @return Response
     */
    public function store(Request $request, Response $response, Post $post)
    {
        if ($this->gate->denies('store', $post)) {
            return $this->jsonApi->respondUnauthorized($response);
        }

        $validation = $this->initializeValidation($request, $this->validationRules);
        if ($validation->fails()) {
            return $this->jsonApi->respondValidationFailed($response, $validation->getMessageBag());
        }

        try {
            $post = $post->create([
                'user_id'     => $request->input('user-id'),
                'category_id' => $request->input('category-id'),
                'slug'        => $request->input('slug'),
                'title'       => $request->input('title'),
                'content'     => $request->input('content'),
                'image_path'  => $request->input('image-path')
            ]);
        } catch (Exception $e) {
            $this->logger->error("Failed to create a post with exception: " . $e->getMessage());
            return $this->jsonApi->respondServerError($response, "Unable to create the post");
        }

        return $this->jsonApi->respondResourceCreated($response, $post);
    }

    /**
     * Updates an existing post.
     *
     * @param Request $request
     * @param Response $response
     * @param Post $post
     * @param string $slug
     *
     * @return Response
     */
    public function update(Request $request, Response $response, Post $post, $slug)
    {
        if ($this->gate->denies('update', $post)) {
            return $this->jsonApi->respondUnauthorized($response);
        }

        $post = $post->find($slug);
        if (is_null($post)) {
            return $this->jsonApi->respondResourceNotFound($response);
        }

        $validation = $this->initializeValidation($request, $this->validationRules);
        if ($validation->fails()) {
            return $this->jsonApi->respondValidationFailed($response, $validation->getMessageBag());
        }

        try {
            $post->update([
                'user_id'     => $request->input('user-id'),
                'category_id' => $request->input('category-id'),
                'slug'        => $request->input('slug'),
                'title'       => $request->input('title'),
                'content'     => $request->input('content')
            ]);
        } catch (Exception $e) {
            $this->logger->error("Failed to update a post with exception: " . $e->getMessage());
            return $this->jsonApi->respondServerError($response, 'Unable to update post');
        }

        return $this->jsonApi->respondResourceUpdated($response, $post);
    }

    /**
     * Deletes an existing post.
     *
     * @param Response $response
     * @param Post $post
     * @param string $slug
     *
     * @return Response
     */
    public function delete(Response $response, Post $post, $slug)
    {
        if ($this->gate->denies('delete', new Post())) {
            return $this->jsonApi->respondUnauthorized($response);
        }

        $post = $post->find($slug);
        if (is_null($post)) {
            return $this->jsonApi->respondResourceNotFound($response);
        }

        try {
            $post->delete();
        } catch (Exception $e) {
            $this->logger->error("Failed to delete a post with exception: " . $e->getMessage());
            return $this->jsonApi->respondServerError($response, "Unable to delete post");
        }

        return $this->jsonApi->respondResourceDeleted($response);
    }
}