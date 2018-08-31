<?php

use App\Contracts\ResourceInterface;
use App\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PageResource implements ResourceInterface
{
    /**
     * @var \App\Repositories\PageRepository
     */
    private $pageRepository;

    public function __construct(\App\Repositories\PageRepository $pageRepository)
    {
        $this->pageRepository = $pageRepository;
    }

    /**
     * @inheritdoc
     */
    public function getResourceType(): string
    {
        return Page::class;
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
    public function findResourceObject($slug)
    {
        return $this->pageRepository->findBySlug($slug);
    }

    /**
     * @inheritdoc
     */
    public function findResourceObjects($page, $size): LengthAwarePaginator
    {
        return $this->pageRepository->paginateAllPages($page, $size);
    }

    /**
     * @inheritdoc
     */
    public function storeResourceObject($attributes, \Illuminate\Contracts\Auth\Authenticatable $user = null)
    {
        return $this->pageRepository->create($attributes);
    }

    /**
     * @inheritdoc
     */
    public function updateResourceObject($resourceObject, $attributes, \Illuminate\Contracts\Auth\Authenticatable $user = null)
    {
        return $this->pageRepository->update($resourceObject, $attributes);
    }

    /**
     * @inheritdoc
     */
    public function deleteResourceObject($resourceObject): bool
    {
        return $this->pageRepository->delete($resourceObject);
    }

    /**
     * @inheritdoc
     */
    public function getStoreValidationRules(): array
    {
        return [
            'title'   => 'required|max:200|unique:pages',
            'slug'    => 'required|max:255|unique:pages',
            'content' => 'max:100000',
            'is_live' => 'boolean'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getUpdateValidationRules($resourceObject, $attributes): array
    {
        $validationRules = [
            'title'   => 'required|max:200|unique:pages',
            'slug'    => 'required|max:255|unique:pages',
            'content' => 'max:100000',
            'is_live' => 'boolean'
        ];

        // Removing the unique validation on some fields if they have not changed
        if (isset($attributes['slug']) && $resourceObject->getAttribute('slug') === $attributes['slug']) {
            $validationRules['slug'] = 'required|max:255';
        }
        if (isset($attributes['title']) && $resourceObject->getAttribute('title') === $attributes['title']) {
            $validationRules['title'] = 'required|max:200';
        }

        return $validationRules;
    }

    public function requireIndexAuthorization(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function requireShowAuthorization($resourceObject): bool
    {
        return $resourceObject->isLive() === false;
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
