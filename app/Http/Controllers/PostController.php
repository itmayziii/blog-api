<?php

namespace App\Http\Controllers;

use App\Http\JsonApi;
use App\Post;
use App\Repositories\CacheRepository;
use App\Repositories\PostRepository;
use Exception;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Psr\Log\LoggerInterface;

class PostController extends Controller
{
    /**
     * @var JsonApi
     */
    private $jsonApi;
    /**
     * @var PostRepository
     */
    private $postRepository;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Gate
     */
    private $gate;
    /**
     * @var Guard
     */
    private $guard;
    /**
     * @var CacheRepository
     */
    private $cacheRepository;
    /**
     * Validation Rules
     *
     * @var array
     */
    private $validationRules = [
        'user-id'     => 'required',
        'category-id' => 'required',
        'title'       => 'required|max:200|unique:posts',
        'status'      => 'required',
        'slug'        => 'required|max:255|unique:posts',
        'content'     => 'max:10000'
    ];

    public function __construct(
        JsonApi $jsonApi,
        PostRepository $postRepository,
        Gate $gate,
        Guard $guard,
        LoggerInterface $logger,
        ValidationFactory $validationFactory,
        CacheRepository $cacheRepository
    ) {
        parent::__construct($validationFactory);
        $this->jsonApi = $jsonApi;
        $this->postRepository = $postRepository;
        $this->gate = $gate;
        $this->guard = $guard;
        $this->logger = $logger;
        $this->cacheRepository = $cacheRepository;
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

        $isAllowedToIndexAllPosts = $this->gate->allows('indexAllPosts', $post);
        $allowedStatus = $isAllowedToIndexAllPosts ? 'all' : 'live';

        $paginator = $this->cacheRepository->remember("posts.$allowedStatus.page$page.size$size", 60, function () use ($size, $page, $isAllowedToIndexAllPosts) {
            return $isAllowedToIndexAllPosts ? $this->postRepository->paginateAllPosts($page, $size) : $this->postRepository->paginateLivePosts($page, $size);
        });

        return $this->jsonApi->respondResourcesFound($response, $paginator);
    }

    /**
     * Find specific posts by slug.
     *
     * @param Response $response
     * @param string $slug
     *
     * @return Response
     */
    public function show(Response $response, $slug)
    {
        $post = $this->cacheRepository->remember("post.$slug", 60, function () use ($slug) {
            return $this->postRepository->findBySlug($slug);
        });

        if (is_null($post)) {
            $this->logger->debug(PostController::class . " unable to find post with slug: $slug");
            return $this->jsonApi->respondResourceNotFound($response);
        }

        if ($post->isLive()) {
            return $this->jsonApi->respondResourceFound($response, $post);
        }

        if ($this->guard->guest()) {
            return $this->jsonApi->respondUnauthorized($response);
        }

        if ($this->gate->denies('show', $post)) {
            return $this->jsonApi->respondForbidden($response);
        }

        return $this->jsonApi->respondResourceFound($response, $post);
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
            return $this->jsonApi->respondForbidden($response);
        }

        $validation = $this->initializeValidation($request, $this->validationRules);
        if ($validation->fails()) {
            return $this->jsonApi->respondValidationFailed($response, $validation->getMessageBag());
        }

        try {
            $post = $post->create([
                'user_id'         => $request->input('user-id'),
                'category_id'     => $request->input('category-id'),
                'slug'            => $request->input('slug'),
                'status'          => $request->input('status'),
                'title'           => $request->input('title'),
                'content'         => $request->input('content'),
                'preview'         => $request->input('preview'),
                'image_path_sm'   => $request->input('image-path-sm'),
                'image_path_md'   => $request->input('image-path-md'),
                'image_path_lg'   => $request->input('image-path-lg'),
                'image_path_meta' => $request->input('image-path-meta')
            ]);
        } catch (Exception $e) {
            $this->logger->error(PostController::class . " failed to create a post with exception: " . $e->getMessage());
            return $this->jsonApi->respondServerError($response, "Unable to create the post.");
        }

        $this->clearPostsCache();

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
            return $this->jsonApi->respondForbidden($response);
        }

        $post = $this->postRepository->findBySlug($slug);
        if (is_null($post)) {
            return $this->jsonApi->respondResourceNotFound($response);
        }

        // Removing the unique validation on some fields if they have not changed
        if ($post->getAttribute('slug') === $request->input('slug')) {
            $this->validationRules['slug'] = 'required|max:255';
        }
        if ($post->getAttribute('title') === $request->input('title')) {
            $this->validationRules['title'] = 'required|max:200';
        }

        $validation = $this->initializeValidation($request, $this->validationRules);
        if ($validation->fails()) {
            return $this->jsonApi->respondValidationFailed($response, $validation->getMessageBag());
        }

        try {
            $post->update([
                'user_id'         => $request->input('user-id'),
                'category_id'     => $request->input('category-id'),
                'slug'            => $request->input('slug'),
                'status'          => $request->input('status'),
                'title'           => $request->input('title'),
                'content'         => $request->input('content'),
                'preview'         => $request->input('preview'),
                'image_path_sm'   => $request->input('image-path-sm'),
                'image_path_md'   => $request->input('image-path-md'),
                'image_path_lg'   => $request->input('image-path-lg'),
                'image_path_meta' => $request->input('image-path-meta')
            ]);
        } catch (Exception $e) {
            $this->logger->error(PostController::class . " failed to update a post with exception: " . $e->getMessage());
            return $this->jsonApi->respondServerError($response, 'Unable to update post');
        }

        $this->cacheRepository->forget("post.{$slug}");
        $this->clearPostsCache();

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
        if ($this->gate->denies('delete', $post)) {
            return $this->jsonApi->respondForbidden($response);
        }

        $post = $this->postRepository->findBySlug($slug);
        if (is_null($post)) {
            return $this->jsonApi->respondResourceNotFound($response);
        }

        try {
            $post->delete();
        } catch (Exception $e) {
            $this->logger->error(PostController::class . " failed to delete a post with exception: " . $e->getMessage());
            return $this->jsonApi->respondServerError($response, "Unable to delete post");
        }

        $this->cacheRepository->forget("post.{$slug}");
        $this->clearPostsCache();

        return $this->jsonApi->respondResourceDeleted($response);
    }

    private function clearPostsCache()
    {
        $postKeys = $this->cacheRepository->keys('posts*');
        $this->cacheRepository->deleteMultiple($postKeys);

        $categoryPostKeys = $this->cacheRepository->keys('categories-posts*');
        $this->cacheRepository->deleteMultiple($categoryPostKeys);
    }
}