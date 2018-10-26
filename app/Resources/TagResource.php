<?php

namespace App\Resources;

use App\Contracts\ResourceInterface;
use App\Models\Tag;
use App\Repositories\TagRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class TagResource implements ResourceInterface
{
    /**
     * @var TagRepository
     */
    private $tagRepository;

    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    /**
     * @inheritdoc
     */
    public function getResourceType(): string
    {
        return Tag::class;
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
        return is_numeric($resourceId) ? $this->tagRepository->findById($resourceId) : $this->tagRepository->findBySlug($resourceId);
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

        return $this->tagRepository->paginate($page, $size);
    }

    /**
     * @inheritdoc
     */
    public function storeResourceObject($attributes, Authenticatable $user = null)
    {
        return $this->tagRepository->create($attributes);
    }

    /**
     * @inheritdoc
     */
    public function updateResourceObject($resourceObject, $attributes, Authenticatable $user = null)
    {
        return $this->tagRepository->update($resourceObject, $attributes);
    }

    /**
     * @inheritdoc
     */
    public function deleteResourceObject($resourceObject): bool
    {
        return $this->tagRepository->delete($resourceObject);
    }

    /**
     * @inheritdoc
     */
    public function getStoreValidationRules($attributes): array
    {
        return [
            'name' => 'required|max:50',
            'slug' => 'required|max:255|alpha_dash|unique:tags',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getUpdateValidationRules($resourceObject, $attributes): array
    {
        $validationRules = $this->getStoreValidationRules($attributes);

        $newSlug = Arr::get($attributes, 'slug');
        if ($resourceObject->getAttribute('slug') === $newSlug) {
            $validationRules['slug'] = 'required|max:255|alpha_dash';
        }

        return $validationRules;
    }

    /**
     * @inheritdoc
     */
    public function requireIndexAuthorization(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function requireShowAuthorization($resourceObject): bool
    {
        return false;
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
