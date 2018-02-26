<?php

namespace App\Http\Controllers;

use App\Http\JsonApi;
use App\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Psr\Log\LoggerInterface;

class UserController extends Controller
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
     * @var Hasher
     */
    private $hasher;
    /**
     * Creation Validation Rules
     *
     * @var array
     */
    private $creationRules = [
        'first-name' => 'required|max:100',
        'last-name'  => 'required|max:100',
        'email'      => 'required|max:100|email|unique:users',
        'password'   => 'required|max:255|confirmed'
    ];

    /**
     * Update Validation Rules
     *
     * @var array
     */
    private $updateRules = [
        'first-name' => 'required|max:100',
        'last-name'  => 'required|max:100',
        'email'      => 'required|max:100|email|unique:users'
    ];

    public function __construct(JsonApi $jsonApi, ValidationFactory $validationFactory, Gate $gate, LoggerInterface $logger, Hasher $hasher)
    {
        parent::__construct($validationFactory);
        $this->jsonApi = $jsonApi;
        $this->gate = $gate;
        $this->logger = $logger;
        $this->hasher = $hasher;
    }

    /**
     * List the existing users.
     *
     * @param Request $request
     * @param Response $response
     * @param User $user
     *
     * @return Response
     */
    public function index(Request $request, Response $response, User $user)
    {
        if ($this->gate->denies('index', $user)) {
            return $this->jsonApi->respondForbidden($response);
        }

        $size = $request->query('size', 15);
        $page = $request->query('page', 1);

        $paginator = $user
            ->orderBy('created_at', 'desc')
            ->paginate($size, null, 'page', $page);

        return $this->jsonApi->respondResourcesFound($response, $paginator);
    }

    /**
     * @param Response $response
     * @param User $user
     * @param $id
     *
     * @return Response
     */
    public function show(Response $response, User $user, $id)
    {
        if ($this->gate->denies('show', $user)) {
            return $this->jsonApi->respondForbidden($response);
        }

        $user = $user->find($id);
        if (is_null($user)) {
            return $this->jsonApi->respondResourceNotFound($response);
        }

        return $this->jsonApi->respondResourceFound($response, $user);
    }

    /**
     * Creates a new user.
     *
     * @param Request $request
     * @param Response $response
     * @param User $user
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Response $response, User $user)
    {
        $validator = $this->initializeValidation($request, $this->creationRules);
        if ($validator->fails()) {
            return $this->jsonApi->respondValidationFailed($response, $validator->getMessageBag());
        }

        try {
            $user = $user->create([
                'first_name' => $request->input('first-name'),
                'last_name'  => $request->input('last-name'),
                'email'      => $request->input('email'),
                'password'   => $this->hasher->make($request->input('password'))
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to create a user with exception: " . $e->getMessage());
            return $this->jsonApi->respondServerError($response, "Unable to create the user.");
        }

        return $this->jsonApi->respondResourceCreated($response, $user);
    }

    public function update(Request $request, Response $response, User $user, $id)
    {
        if ($this->gate->denies('update', $user)) {
            return $this->jsonApi->respondForbidden($response);
        }

        $user = $user->find($id);
        if (is_null($user)) {
            return $this->jsonApi->respondResourceNotFound($response);
        }

        $validation = $this->initializeValidation($request, $this->updateRules);
        if ($validation->fails()) {
            return $this->jsonApi->respondValidationFailed($response, $validation->getMessageBag());
        }

        try {
            $user->update([
                'first_name' => $request->input('first-name'),
                'last_name'  => $request->input('last-name'),
                'email'      => $request->input('email'),
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to update a user with exception: " . $e->getMessage());
            return $this->jsonApi->respondServerError($response, "Unable to update user.");
        }

        return $this->jsonApi->respondResourceUpdated($response, $user);
    }

    /**
     * Deletes a user.
     *
     * @param Response $response
     * @param User $user
     * @param int $id
     *
     * @return Response
     */
    public function delete(Response $response, User $user, $id)
    {
        if ($this->gate->denies('delete', $user)) {
            return $this->jsonApi->respondForbidden($response);
        }

        $user = $user->find($id);
        if (is_null($user)) {
            return $this->jsonApi->respondResourceNotFound($response);
        }

        try {
            $user->delete();
        } catch (\Exception $e) {
            $this->logger->error("Failed to delete a user with exception: " . $e->getMessage());
            return $this->jsonApi->respondServerError($response, "Unable to delete user.");
        }

        return $this->jsonApi->respondResourceDeleted($response);
    }
}