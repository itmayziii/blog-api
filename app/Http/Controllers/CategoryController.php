<?php

namespace App\Http\Controllers;

use App\Category;
use App\Http\JsonApi;
use App\Post;
use App\Repositories\CacheRepository;
use App\Repositories\CategoryRepository;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Psr\Log\LoggerInterface;

class CategoryController extends Controller
{
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;
    /**
     * @var JsonApi
     */
    private $jsonApi;
    /**
     * @var Gate
     */
    private $gate;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var CacheRepository
     */
    private $cacheRepository;
    /**
     * Validation Rules
     *
     * @var array
     */
    private $rules = [
        'name' => 'required|unique:categories',
        'slug' => 'required|unique:categories'
    ];

    public function __construct(
        CategoryRepository $categoryRepository,
        JsonApi $jsonApi,
        Gate $gate,
        LoggerInterface $logger,
        ValidationFactory $validationFactory,
        CacheRepository $cacheRepository
    )
    {
        parent::__construct($validationFactory);
        $this->categoryRepository = $categoryRepository;
        $this->jsonApi = $jsonApi;
        $this->gate = $gate;
        $this->logger = $logger;
        $this->cacheRepository = $cacheRepository;
    }

    public function index(Request $request, Response $response, Category $category, Post $post)
    {
        $size = $request->query('size', 15);
        $page = $request->query('page', 1);

        if ($this->gate->denies('indexAllWebPages', $post)) {
            $paginator = $this->cacheRepository->remember("categories.live.page$page.size$size", 60, function () use ($size, $page, $category) {
                $paginator = $category
                    ->withCount([
                        'posts' => function ($query) {
                            $query->where('status', 'live')
                                ->orderBy('created_at', 'desc');
                        }
                    ])
                    ->orderBy('created_at', 'desc')
                    ->paginate($size, null, 'page', $page);

                return $paginator;
            });
        } else {
            $paginator = $this->cacheRepository->remember("categories.all.page$page.size$size", 60, function () use ($size, $page, $category) {
                $paginator = $category
                    ->withCount([
                        'posts' => function ($query) {
                            $query->orderBy('created_at', 'desc');
                        }
                    ])
                    ->orderBy('created_at', 'desc')
                    ->paginate($size, null, 'page', $page);

                return $paginator;
            });
        }

        return $this->jsonApi->respondResourcesFound($response, $paginator);
    }

    public function show(Response $response, $slug)
    {
        $category = $this->cacheRepository->remember("category.$slug", 60, function () use ($slug) {
            return $this->categoryRepository->findBySlug($slug);
        });

        if (is_null($category)) {
            return $this->jsonApi->respondResourceNotFound($response);
        }

        return $this->jsonApi->respondResourceFound($response, $category);
    }

    public function store(Request $request, Response $response, Category $category)
    {
        if ($this->gate->denies('store', $category)) {
            return $this->jsonApi->respondForbidden($response);
        }

        $validation = $this->initializeValidation($request, $this->rules);
        if ($validation->fails()) {
            return $this->jsonApi->respondValidationFailed($response, $validation->getMessageBag());
        }

        try {
            $category = $category->create([
                'name' => $request->input('name'),
                'slug' => $request->input('slug')
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to create a category with exception: " . $e->getMessage());
            return $this->jsonApi->respondServerError($response, "Unable to create the category");
        }

        $this->clearCategoriesCache();

        return $this->jsonApi->respondResourceCreated($response, $category);
    }

    public function update(Request $request, Response $response, Category $category, $slug)
    {
        if ($this->gate->denies('update', $category)) {
            return $this->jsonApi->respondForbidden($response);
        }

        $category = $this->categoryRepository->findBySlug($slug);
        if (is_null($category)) {
            return $this->jsonApi->respondResourceNotFound($response);
        }

        if ($category->getAttribute('name') === $request->input('name')) {
            $this->rules['name'] = 'required';
        }
        if ($category->getAttribute('slug') === $request->input('slug')) {
            $this->rules['slug'] = 'required';
        }

        $validation = $this->initializeValidation($request, $this->rules);
        if ($validation->fails()) {
            return $this->jsonApi->respondValidationFailed($response, $validation->getMessageBag());
        }

        try {
            $category->update([
                'name' => $request->input('name'),
                'slug' => $request->input('slug')
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to update a category with exception: " . $e->getMessage());
            return $this->jsonApi->respondServerError($response, "Unable to update category.");
        }

        $this->cacheRepository->forget("category.$slug");
        $this->clearCategoriesCache();

        return $this->jsonApi->respondResourceUpdated($response, $category);
    }

    public function delete(Response $response, Category $category, Post $post, $slug)
    {
        if ($this->gate->denies('delete', $category)) {
            return $this->jsonApi->respondForbidden($response);
        }

        $category = $this->categoryRepository->findBySlug($slug);
        if (is_null($category)) {
            return $this->jsonApi->respondResourceNotFound($response);
        }

        try {
            $post->where('category_id', $category->getAttribute('id'))
                ->update(['category_id' => null]);
            $category->delete();
        } catch (\Exception $e) {
            $this->logger->error("Failed to delete a category with exception: " . $e->getMessage());
            return $this->jsonApi->respondServerError($response, "Unable to delete category.");
        }

        $this->cacheRepository->forget("category.$slug");
        $this->clearCategoriesCache();

        return $this->jsonApi->respondResourceDeleted($response);
    }

    private function clearCategoriesCache()
    {
        $categoryKeys = $this->cacheRepository->keys('categories.all*');
        $this->cacheRepository->deleteMultiple($categoryKeys);

        $categoryKeys = $this->cacheRepository->keys('categories.live*');
        $this->cacheRepository->deleteMultiple($categoryKeys);

        $categoryPostKeys = $this->cacheRepository->keys('categories-posts*');
        $this->cacheRepository->deleteMultiple($categoryPostKeys);
    }
}