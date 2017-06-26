<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        $validation = $this->initializeValidation($request, $this->rules);
        if ($validation->fails()) {
            return $this->jsonApi->respondValidationFailed($validation->getMessageBag());
        }

        try {
            $user = (new User())->create([
                'first_name' => $request->input('first-name'),
                'last_name'  => $request->input('last-name'),
                'email'      => str_slug($request->input('email')),
                'password'   => $request->input('password'),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to create a blog with exception: " . $e->getMessage());
            return $this->jsonApi->respondBadRequest("Unable to create the blog");
        }

        return $this->jsonApi->respondResourceCreated($user);
    }
}