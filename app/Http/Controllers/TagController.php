<?php

namespace App\Http\Controllers;

use App\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use itmayziii\Laravel\JsonApi;

class TagController extends Controller
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
        return $this->jsonApi->respondResourcesFound(new Tag(), $request);
    }

    public function show($id)
    {
        $tag = tag::find($id);

        if ($tag) {
            return $this->jsonApi->respondResourceFound($tag);
        } else {
            return $this->jsonApi->respondResourceNotFound();
        }
    }

    public function store(Request $request)
    {
        if (Gate::denies('store', new Tag())) {
            return $this->jsonApi->respondUnauthorized();
        }

        $validation = $this->initializeValidation($request, $this->rules);
        if ($validation->fails()) {
            return $this->jsonApi->respondValidationFailed($validation->getMessageBag());
        }

        try {
            $tag = (new Tag())->create([
                'name' => $request->input('name'),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to create a tat with exception: " . $e->getMessage());
            return $this->jsonApi->respondBadRequest("Unable to create the tag");
        }

        return $this->jsonApi->respondResourceCreated($tag);
    }

    public function update(Request $request, $id)
    {
        if (Gate::denies('update', new Tag())) {
            return $this->jsonApi->respondUnauthorized();
        }

        $tag = tag::find($id);
        if (!$tag) {
            return $this->jsonApi->respondResourceNotFound();
        }

        $validation = $this->initializeValidation($request, $this->rules);
        if ($validation->fails()) {
            return $this->jsonApi->respondValidationFailed($validation->getMessageBag());
        }

        try {
            $tag->update([
                'name' => $request->input('name'),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to update a tag with exception: " . $e->getMessage());
            return $this->jsonApi->respondBadRequest("Unable to update tag");
        }

        return $this->jsonApi->respondResourceUpdated($tag);
    }

    public function delete($id)
    {
        if (Gate::denies('update', new Tag())) {
            return $this->jsonApi->respondUnauthorized();
        }

        $tag = tag::find($id);
        if (!$tag) {
            return $this->jsonApi->respondResourceNotFound();
        }

        try {
            $tag->delete();
        } catch (\Exception $e) {
            Log::error("Failed to delete a tag with exception " . $e->getMessage());
            return $this->jsonApi->respondBadRequest("Unable to delete tag");
        }

        return $this->jsonApi->respondResourceDeleted($tag);
    }

}