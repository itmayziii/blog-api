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
    public function findResourceObject($urlSegments, $queryParams)
    {
        if (count($urlSegments) !== 1) {
            return null;
        }

        [$slugOrId] = $urlSegments;
        return is_numeric($slugOrId) ? $this->tagRepository->findById($slugOrId) : $this->tagRepository->findBySlug($slugOrId);
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
        // TODO: Implement storeResourceObject() method.
    }

    /**
     * @inheritdoc
     */
    public function updateResourceObject($resourceObject, $attributes, Authenticatable $user = null)
    {
        // TODO: Implement updateResourceObject() method.
    }

    /**
     * @inheritdoc
     */
    public function deleteResourceObject($resourceObject): bool
    {
        // TODO: Implement deleteResourceObject() method.
    }

    /**
     * @inheritdoc
     */
    public function getStoreValidationRules($attributes): array
    {
        // TODO: Implement getStoreValidationRules() method.
    }

    /**
     * @inheritdoc
     */
    public function getUpdateValidationRules($resourceObject, $attributes): array
    {
        // TODO: Implement getUpdateValidationRules() method.
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