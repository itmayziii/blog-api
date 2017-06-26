<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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
        'email'      => 'required|max:100|email|unique:users',
        'password'   => 'required|max:255|confirmed'
    ];

    public function __construct(JsonApi $jsonApi)
    {
        $this->jsonApi = $jsonApi;
    }

    /**
     * List the existing users.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (Gate::denies('index', new User())) {
            return $this->jsonApi->respondUnauthorized();
        }

        return $this->jsonApi->respondResourcesFound(new User(), $request);
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
                'email'      => $request->input('email'),
                'password'   => $request->input('password'),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to create a blog with exception: " . $e->getMessage());
            return $this->jsonApi->respondBadRequest("Unable to create the blog");
        }

        return $this->jsonApi->respondResourceCreated($user);
    }

    public function delete($id)
    {
        if (Gate::denies('delete', new User())) {
            return $this->jsonApi->respondUnauthorized();
        }

        $user = User::find($id);
        if (!$user) {
            return $this->jsonApi->respondResourceNotFound();
        }

        try {
            $user->delete();
        } catch (\Exception $e) {
            Log::error("Failed to delete a user with exception: " . $e->getMessage());
            return $this->jsonApi->respondBadRequest("Unable to delete user");
        }

        return $this->jsonApi->respondResourceDeleted($user);
    }
}