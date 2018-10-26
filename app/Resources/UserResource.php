<?php

namespace App\Resources;

use App\Contracts\ResourceInterface;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class UserResource implements ResourceInterface
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @inheritdoc
     */
    public function getResourceType(): string
    {
        return User::class;
    }

    /**
     * @inheritdoc
     */
    public function getAllowedResourceActions(): array
    {
        return ['index', 'show', 'store', 'update', 'delete'];
    }

    /**
     * @inheritdoc
     */
    public function findResourceObject($resourceId, $queryParams)
    {
        return $this->userRepository->findById($resourceId);
    }

    /**
     * @inheritdoc
     */
    public function findRelatedResource($resourceId, $relationship)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function findResourceObjects($queryParams): LengthAwarePaginator
    {
        $page = Arr::get($queryParams, 'page', 1);
        $size = Arr::get($queryParams, 'size', 15);

        return $this->userRepository->paginate($page, $size);
    }

    /**
     * @inheritdoc
     */
    public function storeResourceObject($attributes, Authenticatable $user = null)
    {
        return $this->userRepository->create($attributes);
    }

    /**
     * @inheritdoc
     */
    public function updateResourceObject($resourceObject, $attributes, Authenticatable $user = null)
    {
        return $this->userRepository->update($resourceObject, $attributes);
    }

    /**
     * @inheritdoc
     */
    public function deleteResourceObject($resourceObject): bool
    {
        return $this->userRepository->delete($resourceObject);
    }

    /**
     * @inheritdoc
     */
    public function getStoreValidationRules($attributes): array
    {
        return [
            'first_name' => 'required|max:255',
            'last_name'  => 'required|max:255',
            'email'      => 'required|max:255|email|unique:users',
            'password'   => 'required|max:255|confirmed',
            'api_limit'  => 'digits_between:1,5',
            'role'       => 'max:255'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getUpdateValidationRules($resourceObject, $attributes): array
    {
        $validationRules = $this->getStoreValidationRules($attributes);

        if (isset($attributes['email']) && $attributes['email'] === $resourceObject->getAttribute('email')) {
            $validationRules['email'] = 'required|max:255|email';
        }

        return $validationRules;
    }

    /**
     * @inheritdoc
     */
    public function requireIndexAuthorization(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function requireShowAuthorization($resourceObject): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function requireStoreAuthorization(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function requireUpdateAuthorization($resourceObject): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function requireDeleteAuthorization($resourceObject): bool
    {
        return true;
    }
}
