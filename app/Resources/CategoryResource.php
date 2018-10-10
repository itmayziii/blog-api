<?php

namespace App\Resources;

use App\Contracts\ResourceInterface;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryResource implements ResourceInterface
{
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @return string
     */
    public function getResourceType(): string
    {
        return Category::class;
    }

    /**
     * @return array
     */
    public function getAllowedResourceActions(): array
    {
        return ['index', 'show', 'store', 'update', 'delete'];
    }

    /**
     * @param array $urlSegments
     * @param array $queryParams
     *
     * @return mixed | null
     */
    public function findResourceObject($urlSegments, $queryParams)
    {
        if (count($urlSegments) !== 1) {
            return null;
        }

        [$slugOrId] = $urlSegments;
        return is_numeric($slugOrId) ? $this->categoryRepository->findById($slugOrId) : $this->categoryRepository->findBySlug($slugOrId);
    }

    /**
     * @param array $queryParams
     *
     * @return LengthAwarePaginator
     */
    public function findResourceObjects($queryParams): LengthAwarePaginator
    {
        // TODO: Implement findResourceObjects() method.
    }

    /**
     * @param array $attributes
     * @param Authenticatable $user
     *
     * @return mixed | null
     */
    public function storeResourceObject($attributes, Authenticatable $user = null)
    {
        // TODO: Implement storeResourceObject() method.
    }

    /**
     * @param mixed $resourceObject
     * @param array $attributes
     * @param Authenticatable $user
     *
     * @return mixed | null
     */
    public function updateResourceObject($resourceObject, $attributes, Authenticatable $user = null)
    {
        // TODO: Implement updateResourceObject() method.
    }

    /**
     * @param mixed $resourceObject
     *
     * @return boolean
     */
    public function deleteResourceObject($resourceObject): bool
    {
        // TODO: Implement deleteResourceObject() method.
    }

    /**
     * @param array $attributes
     *
     * @return array
     */
    public function getStoreValidationRules($attributes): array
    {
        // TODO: Implement getStoreValidationRules() method.
    }

    /**
     * @param mixed $resourceObject
     * @param array $attributes
     *
     * @return array
     */
    public function getUpdateValidationRules($resourceObject, $attributes): array
    {
        // TODO: Implement getUpdateValidationRules() method.
    }

    /**
     * Determine if a resource needs authentication / authorization in order to index it
     *
     * @return bool
     */
    public function requireIndexAuthorization(): bool
    {
        // TODO: Implement requireIndexAuthorization() method.
    }

    /**
     * Determine if a resource needs authentication / authorization in order to show it
     *
     * @param mixed $resourceObject
     *
     * @return bool
     */
    public function requireShowAuthorization($resourceObject): bool
    {
        return false;
    }

    /**
     * Determine if a resource needs authentication / authorization in order to create it
     *
     * @return bool
     */
    public function requireStoreAuthorization(): bool
    {
        // TODO: Implement requireStoreAuthorization() method.
    }

    /**
     * Determine if a resource needs authentication / authorization in order to update it
     *
     * @param mixed $resourceObject
     *
     * @return bool
     */
    public function requireUpdateAuthorization($resourceObject): bool
    {
        // TODO: Implement requireUpdateAuthorization() method.
    }

    /**
     * Determine if a resource needs authentication / authorization in order to delete it
     *
     * @param mixed $resourceObject
     *
     * @return bool
     */
    public function requireDeleteAuthorization($resourceObject): bool
    {
        // TODO: Implement requireDeleteAuthorization() method.
    }
}