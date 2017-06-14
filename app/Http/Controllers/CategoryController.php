<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use itmayziii\Laravel\JsonApi;

class CategoryController extends Controller
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
        'name' => 'required'
    ];

    public function __construct(JsonApi $jsonApi)
    {
        $this->jsonApi = $jsonApi;
    }

    public function index(Request $request)
    {
        return $this->jsonApi->respondResourcesFound(new Category(), $request);
    }

    public function show($id)
    {
        $category = Category::find($id);

        if ($category) {
            return $this->jsonApi->respondResourceFound($category);
        } else {
            return $this->jsonApi->respondResourceNotFound();
        }
    }

    public function store(Request $request)
    {
        if (Gate::denies('store', new Category())) {
            return $this->jsonApi->respondUnauthorized();
        }

        $validation = $this->initializeValidation($request, $this->rules);
        if ($validation->fails()) {
            return $this->jsonApi->respondValidationFailed($validation->getMessageBag());
        }

        try {
            $category = (new Category())->create([
                'name' => $request->input('name'),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to create a blog with exception: " . $e->getMessage());
            return $this->jsonApi->respondBadRequest("Unable to create the category");
        }

        return $this->jsonApi->respondResourceCreated($category);
    }

    public function update(Request $request, $id)
    {
        if (Gate::denies('update', new Category())) {
            return $this->jsonApi->respondUnauthorized();
        }

        $category = Category::find($id);
        if (!$category) {
            return $this->jsonApi->respondResourceNotFound();
        }

        $validation = $this->initializeValidation($request, $this->rules);
        if ($validation->fails()) {
            return $this->jsonApi->respondValidationFailed($validation->getMessageBag());
        }

        try {
            $category->update([
                'name' => $request->input('name'),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to update a category with exception: " . $e->getMessage());
            return $this->jsonApi->respondBadRequest("Unable to update category");
        }

        return $this->jsonApi->respondResourceUpdated($category);
    }

    public function delete($id)
    {
        if (Gate::denies('update', new Category())) {
            return $this->jsonApi->respondUnauthorized();
        }

        $category = Category::find($id);
        if (!$category) {
            return $this->jsonApi->respondResourceNotFound();
        }

        try {
            $category->delete();
        } catch (\Exception $e) {
            Log::error("Failed to delete a category with exception " . $e->getMessage());
            return $this->jsonApi->respondBadRequest("Unable to delete category");
        }

        return $this->jsonApi->respondResourceDeleted($category);
    }

}