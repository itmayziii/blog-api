<?php

namespace App\Resources;

use App\Contracts\ResourceInterface;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class CategoryResource extends BaseResource implements ResourceInterface
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
        return [self::INDEX_ACTION, self::SHOW_ACTION, self::SHOW_RELATED_RESOURCE_ACTION, self::STORE_ACTION, self::UPDATE_ACTION, self::DELETE_ACTION];
    }

    /**
     * @inheritdoc
     */
    public function findResourceObject($resourceId, $queryParams)
    {
        $page = Arr::get($queryParams, 'page', 1);
        $size = Arr::get($queryParams, 'size', 15);

        $shouldLoadWebPages = false;
        if ($this->isRelationshipIncluded($queryParams, 'webpages')) {
            $shouldLoadWebPages = true;
        }

        return $this->categoryRepository->findBySlugOrId($resourceId, $shouldLoadWebPages, $page, $size);
    }

    /**
     * @inheritdoc
     */
    public function findRelatedResource($resourceId, $relationship, $queryParams)
    {
        $category = $this->categoryRepository->findBySlugOrId($resourceId, true);
        if (is_null($category)) {
            return null;
        }

        return $category->getRelationValue('webpages');
    }

    /**
     * @param array $queryParams
     *
     * @return LengthAwarePaginator
     */
    public function findResourceObjects($queryParams): LengthAwarePaginator
    {
        $page = Arr::get($queryParams, 'page', 1);
        $size = Arr::get($queryParams, 'size', 15);

        return $this->categoryRepository->paginate($page, $size);
    }

    /**
     * @param array $attributes
     * @param Authenticatable $user
     *
     * @return mixed | null
     */
    public function storeResourceObject($attributes, Authenticatable $user = null)
    {
        return $this->categoryRepository->create($attributes, $user);
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
        return $this->categoryRepository->update($resourceObject, $attributes, $user);
    }

    /**
     * @param mixed $resourceObject
     *
     * @return boolean
     */
    public function deleteResourceObject($resourceObject): bool
    {
        return $this->categoryRepository->delete($resourceObject);
    }

    /**
     * @param array $attributes
     *
     * @return array
     */
    public function getStoreValidationRules($attributes): array
    {
        return [
            'name'        => 'required|max:255',
            'plural_name' => 'required|max:255',
            'slug'        => 'required|max:255|alpha_dash|unique:categories'
        ];
    }

    /**
     * @param mixed $resourceObject
     * @param array $attributes
     *
     * @return array
     */
    public function getUpdateValidationRules($resourceObject, $attributes): array
    {
        $validationRules = $this->getStoreValidationRules($attributes);

        // Removing the unique validation on some fields if they have not changed
        $newSlug = Arr::get($attributes, 'slug');
        if ($resourceObject->getAttribute('slug') === $newSlug) {
            $validationRules['slug'] = 'required|max:255|alpha_dash';
        }

        return $validationRules;
    }

    /**
     * Determine if a resource needs authentication / authorization in order to index it
     *
     * @return bool
     */
    public function requireIndexAuthorization(): bool
    {
        return false;
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
        return true;
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
        return true;
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
        return true;
    }
}
