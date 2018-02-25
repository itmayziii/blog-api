<?php

namespace App\Http\Controllers;

use App\Category;
use App\Http\JsonApi;
use App\Post;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Psr\Log\LoggerInterface;

class CategoryController extends Controller
{
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
     * Validation Rules
     *
     * @var array
     */
    private $rules = [
        'name' => 'required'
    ];

    public function __construct(JsonApi $jsonApi, Gate $gate, LoggerInterface $logger)
    {
        $this->jsonApi = $jsonApi;
        $this->gate = $gate;
        $this->logger = $logger;
    }

    public function index(Request $request, Response $response, Category $category)
    {
        $size = $request->query('size', 15);
        $page = $request->query('page', 1);

        $paginator = $category
            ->withCount('posts')
            ->orderBy('created_at', 'desc')
            ->paginate($size, null, 'page', $page);

        return $this->jsonApi->respondResourcesFound($response, $paginator);
    }

    public function show(Response $response, Category $category, $id)
    {
        $category = $category->find($id);

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
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to create a category with exception: " . $e->getMessage());
            return $this->jsonApi->respondServerError($response, "Unable to create the category");
        }

        return $this->jsonApi->respondResourceCreated($response, $category);
    }

    public function update(Request $request, Response $response, Category $category, $id)
    {
        if ($this->gate->denies('update', $category)) {
            return $this->jsonApi->respondForbidden($response);
        }

        $category = $category->find($id);
        if (is_null($category)) {
            return $this->jsonApi->respondResourceNotFound($response);
        }

        $validation = $this->initializeValidation($request, $this->rules);
        if ($validation->fails()) {
            return $this->jsonApi->respondValidationFailed($response, $validation->getMessageBag());
        }

        try {
            $category->update([
                'name' => $request->input('name'),
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to update a category with exception: " . $e->getMessage());
            return $this->jsonApi->respondServerError($response, "Unable to update category.");
        }

        return $this->jsonApi->respondResourceUpdated($response, $category);
    }

    public function delete(Response $response, Category $category, Post $post, $id)
    {
        if ($this->gate->denies('delete', $category)) {
            return $this->jsonApi->respondForbidden($response);
        }

        $category = $category->find($id);
        if (is_null($category)) {
            return $this->jsonApi->respondResourceNotFound($response);
        }

        try {
            $post->where('category_id', $id)
                ->update(['category_id' => null]);
            $category->delete();
        } catch (\Exception $e) {
            $this->logger->error("Failed to delete a category with exception: " . $e->getMessage());
            return $this->jsonApi->respondServerError($response, "Unable to delete category.");
        }

        return $this->jsonApi->respondResourceDeleted($response);
    }

}