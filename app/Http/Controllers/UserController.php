<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use itmayziii\Laravel\JsonApi;

class UserController extends Controller
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
        'first-name' => 'required|max:100',
        'last-name'  => 'required|max:100',
        'email'      => 'required|max:100',
        'password'   => 'required|max:255|confirmed'
    ];

    public function __construct(JsonApi $jsonApi)
    {
        $this->jsonApi = $jsonApi;
    }

    /**
     * Creates a new user.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::denies('store', new User())) {
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